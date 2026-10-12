<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReportFilterRequest;
use App\Http\Requests\StoreWorkUpdateRequest;
use App\Http\Requests\UpdateWorkUpdateRequest;
use App\Models\DailyWorkUpdate;
use App\Models\Task;
use App\Models\User;
use App\Services\WorkReportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class WorkUpdateController extends Controller
{
    private const PER_PAGE = 15;

    public function __construct(private readonly WorkReportService $reports)
    {
    }

    /**
     * Employees see their own history. Managers and admins see updates
     * from employees within their scope.
     */
    public function index(ReportFilterRequest $request): View
    {
        $user = $request->user();
        $filters = $request->validated();

        $updates = DailyWorkUpdate::query()
            ->with(['task:id,title', 'employee:id,name'])
            ->when($user->isEmployee(), fn ($q) => $q->where('employee_id', $user->id))
            ->when(! $user->isEmployee(), fn ($q) => $q->whereIn('task_id', $this->reports->filteredTasks($user)->select('tasks.id')))
            ->when(! empty($filters['employee_id']), fn ($q) => $q->where('employee_id', $filters['employee_id']))
            ->when(! empty($filters['from']), fn ($q) => $q->whereDate('work_date', '>=', $filters['from']))
            ->when(! empty($filters['to']), fn ($q) => $q->whereDate('work_date', '<=', $filters['to']))
            ->latest('work_date')
            ->latest('created_at')
            ->paginate(self::PER_PAGE)
            ->withQueryString();

        return view('work-updates.index', [
            'updates' => $updates,
            'filters' => $filters,
            'today' => Carbon::today(),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', DailyWorkUpdate::class);

        return view('work-updates.create', [
            'tasks' => $this->assignedTasks(auth()->user()),
            'maxBackdate' => StoreWorkUpdateRequest::MAX_BACKDATE_DAYS,
        ]);
    }

    public function store(StoreWorkUpdateRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['employee_id'] = $request->user()->id;

        $update = DailyWorkUpdate::create($data);

        return redirect()->route('work-updates.index')
            ->with('success', 'Work update submitted for '.$update->work_date->format('d M Y').'.');
    }

    public function edit(DailyWorkUpdate $workUpdate): View
    {
        $this->authorize('update', $workUpdate);

        return view('work-updates.edit', [
            'update' => $workUpdate->load('task:id,title'),
        ]);
    }

    public function update(UpdateWorkUpdateRequest $request, DailyWorkUpdate $workUpdate): RedirectResponse
    {
        $workUpdate->update($request->validated());

        return redirect()->route('work-updates.index')
            ->with('success', 'Work update saved.');
    }

    /** Tasks the employee is allowed to report against: only their own assignments. */
    private function assignedTasks(User $user)
    {
        return Task::query()
            ->where('assigned_to', $user->id)
            ->orderBy('due_date')
            ->get(['id', 'title', 'status', 'due_date']);
    }
}
