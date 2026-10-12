<?php

namespace App\Services;

use App\Enums\TaskStatus;
use App\Models\DailyWorkUpdate;
use App\Models\Task;
use App\Models\User;
use Carbon\CarbonPeriod;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * All dashboard and performance figures are computed from database records here.
 * Controllers pass in the already-validated filters and the authenticated user.
 */
class WorkReportService
{
    /**
     * Base task query for a user, narrowed by the optional report filters.
     * Date range applies to task due dates.
     *
     * @param  array{employee_id?: int|null, status?: string|null, from?: string|null, to?: string|null}  $filters
     */
    public function filteredTasks(User $viewer, array $filters = []): Builder
    {
        $query = Task::query()->visibleTo($viewer);

        if (! empty($filters['employee_id'])) {
            $query->where('assigned_to', (int) $filters['employee_id']);
        }

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['from'])) {
            $query->whereDate('due_date', '>=', $filters['from']);
        }

        if (! empty($filters['to'])) {
            $query->whereDate('due_date', '<=', $filters['to']);
        }

        return $query;
    }

    /**
     * Counts per status plus overdue, computed in a single aggregate query.
     *
     * @return array{total: int, todo: int, in_progress: int, completed: int, overdue: int}
     */
    public function taskCounts(User $viewer, array $filters = []): array
    {
        $row = $this->filteredTasks($viewer, $filters)
            ->selectRaw('COUNT(*) AS total')
            ->selectRaw('SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) AS todo', [TaskStatus::ToDo->value])
            ->selectRaw('SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) AS in_progress', [TaskStatus::InProgress->value])
            ->selectRaw('SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) AS completed', [TaskStatus::Completed->value])
            ->selectRaw(
                'SUM(CASE WHEN status <> ? AND due_date IS NOT NULL AND due_date < ? THEN 1 ELSE 0 END) AS overdue',
                [TaskStatus::Completed->value, Carbon::today()->toDateString()]
            )
            ->first();

        return [
            'total' => (int) ($row->total ?? 0),
            'todo' => (int) ($row->todo ?? 0),
            'in_progress' => (int) ($row->in_progress ?? 0),
            'completed' => (int) ($row->completed ?? 0),
            'overdue' => (int) ($row->overdue ?? 0),
        ];
    }

    /**
     * Per-employee work-update summary for the employees visible to the viewer.
     *
     * @return Collection<int, object{id: int, name: string, tasks_total: int, updates_count: int, last_update: string|null}>
     */
    public function employeeUpdateSummaries(User $viewer, array $filters = []): Collection
    {
        $employeesQuery = User::query()
            ->where('role', 'employee')
            ->where('is_active', true);

        if ($viewer->isManager()) {
            $employeesQuery->where('manager_id', $viewer->id);
        }

        if (! empty($filters['employee_id'])) {
            $employeesQuery->whereKey((int) $filters['employee_id']);
        }

        return $employeesQuery
            ->select('users.id', 'users.name')
            ->selectSub(
                Task::query()->selectRaw('COUNT(*)')->whereColumn('tasks.assigned_to', 'users.id'),
                'tasks_total'
            )
            ->selectSub(
                DailyWorkUpdate::query()->selectRaw('COUNT(*)')->whereColumn('daily_work_updates.employee_id', 'users.id')
                    ->when(! empty($filters['from']), fn ($q) => $q->whereDate('work_date', '>=', $filters['from']))
                    ->when(! empty($filters['to']), fn ($q) => $q->whereDate('work_date', '<=', $filters['to'])),
                'updates_count'
            )
            ->selectSub(
                DailyWorkUpdate::query()->selectRaw('MAX(work_date)')->whereColumn('daily_work_updates.employee_id', 'users.id'),
                'last_update'
            )
            ->orderBy('users.name')
            ->get();
    }

    /**
     * Most recent work updates and task changes visible to the viewer.
     */
    public function recentWorkUpdates(User $viewer, int $limit = 8): Collection
    {
        return DailyWorkUpdate::query()
            ->with(['task:id,title,assigned_to', 'employee:id,name'])
            ->whereIn('task_id', $this->filteredTasks($viewer)->select('tasks.id'))
            ->latest('created_at')
            ->limit($limit)
            ->get();
    }

    /**
     * Factual performance indicators for one employee. Empty data yields zeros or null,
     * never a division by zero. No employment or disciplinary judgement is made here.
     *
     * @return array{
     *     tasks_total: int, tasks_completed: int, tasks_overdue: int,
     *     completion_rate: float|null, updates_submitted: int,
     *     working_days_in_range: int, submission_consistency: float|null,
     *     average_reported_progress: float|null, range_from: string, range_to: string
     * }
     */
    public function performanceFor(User $employee, ?string $from = null, ?string $to = null): array
    {
        $rangeTo = $to ? Carbon::parse($to)->startOfDay() : Carbon::today();
        $rangeFrom = $from ? Carbon::parse($from)->startOfDay() : $rangeTo->copy()->subDays(29);

        $tasksQuery = Task::query()->where('assigned_to', $employee->id)
            ->whereDate('due_date', '>=', $rangeFrom)
            ->whereDate('due_date', '<=', $rangeTo);

        $tasksTotal = (clone $tasksQuery)->count();
        $tasksCompleted = (clone $tasksQuery)->where('status', TaskStatus::Completed->value)->count();
        $tasksOverdue = (clone $tasksQuery)->overdue()->count();

        $updatesQuery = DailyWorkUpdate::query()
            ->where('employee_id', $employee->id)
            ->whereDate('work_date', '>=', $rangeFrom)
            ->whereDate('work_date', '<=', $rangeTo);

        $updatesSubmitted = (clone $updatesQuery)->count();
        $distinctUpdateDays = (clone $updatesQuery)->distinct('work_date')->count('work_date');
        $workingDays = $this->countWorkingDays($rangeFrom, $rangeTo);
        $averageProgress = (clone $updatesQuery)->whereNotNull('progress_percentage')->avg('progress_percentage');

        return [
            'tasks_total' => $tasksTotal,
            'tasks_completed' => $tasksCompleted,
            'tasks_overdue' => $tasksOverdue,
            'completion_rate' => $tasksTotal > 0 ? round($tasksCompleted / $tasksTotal * 100, 1) : null,
            'updates_submitted' => $updatesSubmitted,
            'working_days_in_range' => $workingDays,
            'submission_consistency' => $workingDays > 0 ? round(min($distinctUpdateDays, $workingDays) / $workingDays * 100, 1) : null,
            'average_reported_progress' => $averageProgress !== null ? round((float) $averageProgress, 1) : null,
            'range_from' => $rangeFrom->toDateString(),
            'range_to' => $rangeTo->toDateString(),
        ];
    }

    /** Monday to Friday days in the range, inclusive. */
    private function countWorkingDays(Carbon $from, Carbon $to): int
    {
        if ($from->gt($to)) {
            return 0;
        }

        $count = 0;
        foreach (CarbonPeriod::create($from, $to) as $day) {
            if ($day->isWeekday()) {
                $count++;
            }
        }

        return $count;
    }
}
