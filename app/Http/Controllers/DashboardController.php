<?php

namespace App\Http\Controllers;

use App\Enums\TaskStatus;
use App\Http\Requests\ReportFilterRequest;
use App\Models\Task;
use App\Models\User;
use App\Services\WorkReportService;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(private readonly WorkReportService $reports)
    {
    }

    public function index(ReportFilterRequest $request): View
    {
        $user = $request->user();
        $filters = $request->validated();

        if ($user->isEmployee()) {
            return $this->employeeDashboard($user);
        }

        return view('dashboard.index', [
            'counts' => $this->reports->taskCounts($user, $filters),
            'summaries' => $this->reports->employeeUpdateSummaries($user, $filters),
            'recentUpdates' => $this->reports->recentWorkUpdates($user),
            'employees' => $this->employeeOptions($user),
            'filters' => $filters,
            'statusOptions' => TaskStatus::cases(),
        ]);
    }

    /** Employees see only their own assigned tasks and their own counts. */
    private function employeeDashboard(User $user): View
    {
        $tasks = Task::query()
            ->visibleTo($user)
            ->with('creator:id,name')
            ->orderBy('due_date')
            ->paginate(10);

        return view('dashboard.employee', [
            'tasks' => $tasks,
            'counts' => $this->reports->taskCounts($user),
        ]);
    }

    /** Employees the viewer may filter by: admins see all, managers their team. */
    private function employeeOptions(User $user)
    {
        return User::query()
            ->where('role', 'employee')
            ->when($user->isManager(), fn ($q) => $q->where('manager_id', $user->id))
            ->orderBy('name')
            ->get(['id', 'name']);
    }
}
