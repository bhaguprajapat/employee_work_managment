<?php

namespace App\Http\Controllers;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Enums\UserRole;
use App\Http\Requests\ReportFilterRequest;
use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Models\Task;
use App\Models\User;
use App\Services\WorkReportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class TaskController extends Controller
{
    private const PER_PAGE = 15;

    public function __construct(private readonly WorkReportService $reports)
    {
    }

    public function index(ReportFilterRequest $request): View|RedirectResponse
    {
        // Employees manage their tasks from the dashboard; the list is for managers and admins.
        if ($request->user()->isEmployee()) {
            return redirect()->route('dashboard');
        }

        $this->authorize('viewAny', Task::class);

        $filters = $request->validated();

        $tasks = $this->reports->filteredTasks($request->user(), $filters)
            ->with(['assignee:id,name', 'creator:id,name'])
            ->orderBy('due_date')
            ->paginate(self::PER_PAGE)
            ->withQueryString();

        return view('tasks.index', [
            'tasks' => $tasks,
            'filters' => $filters,
            'employees' => $this->assignableEmployees($request->user()),
            'statusOptions' => TaskStatus::cases(),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Task::class);

        return view('tasks.create', [
            'employees' => $this->assignableEmployees(auth()->user()),
            'statuses' => TaskStatus::cases(),
            'priorities' => TaskPriority::cases(),
        ]);
    }

    public function store(StoreTaskRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['created_by'] = $request->user()->id;

        $task = Task::create($data);

        return redirect()->route('tasks.show', $task)
            ->with('success', 'Task allocated successfully.');
    }

    public function show(Task $task): View
    {
        $this->authorize('view', $task);

        return view('tasks.show', [
            'task' => $task->load(['assignee:id,name', 'creator:id,name']),
            'updates' => $task->workUpdates()->with('employee:id,name')->latest('work_date')->limit(20)->get(),
        ]);
    }

    public function edit(Task $task): View
    {
        $this->authorize('update', $task);

        return view('tasks.edit', [
            'task' => $task,
            'employees' => $this->assignableEmployees(auth()->user()),
            'statuses' => TaskStatus::cases(),
            'priorities' => TaskPriority::cases(),
        ]);
    }

    public function update(UpdateTaskRequest $request, Task $task): RedirectResponse
    {
        $task->update($request->validated());

        return redirect()->route('tasks.show', $task)
            ->with('success', 'Task updated.');
    }

    /**
     * Employees a user may allocate tasks to: admins see all active employees,
     * managers only their own team.
     */
    private function assignableEmployees(User $actor)
    {
        return User::query()
            ->where('role', UserRole::Employee->value)
            ->where('is_active', true)
            ->when($actor->isManager(), fn ($q) => $q->where('manager_id', $actor->id))
            ->orderBy('name')
            ->get(['id', 'name']);
    }
}
