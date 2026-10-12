<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Http\Requests\StoreEmployeeRequest;
use App\Http\Requests\UpdateEmployeeRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class EmployeeController extends Controller
{
    private const PER_PAGE = 15;

    public function index(Request $request): View
    {
        $this->authorize('viewAny', User::class);

        $actor = $request->user();
        $search = trim((string) $request->query('search', ''));
        $status = $request->query('status');

        $employees = User::query()
            ->with('manager:id,name')
            ->when(! $actor->isAdmin(), fn ($q) => $q->where('manager_id', $actor->id)->where('role', UserRole::Employee->value))
            ->when($search !== '', function ($q) use ($search): void {
                $q->where(function ($inner) use ($search): void {
                    $inner->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when($status === 'active', fn ($q) => $q->where('is_active', true))
            ->when($status === 'inactive', fn ($q) => $q->where('is_active', false))
            ->orderBy('name')
            ->paginate(self::PER_PAGE)
            ->withQueryString();

        return view('employees.index', [
            'employees' => $employees,
            'search' => $search,
            'status' => $status,
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', User::class);

        return view('employees.create', [
            'managers' => $this->managerOptions(),
            'roles' => UserRole::cases(),
        ]);
    }

    public function store(StoreEmployeeRequest $request): RedirectResponse
    {
        $data = $request->validated();

        // Managers can only create employees placed under themselves.
        if ($request->user()->isManager()) {
            $data['role'] = UserRole::Employee->value;
            $data['manager_id'] = $request->user()->id;
        }

        $data['is_active'] = $request->boolean('is_active', true);

        $employee = User::create($data);

        return redirect()->route('employees.show', $employee)
            ->with('success', 'Employee account created.');
    }

    public function show(User $employee): View
    {
        $this->authorize('view', $employee);

        return view('employees.show', [
            'employee' => $employee->load('manager:id,name'),
            'recentTasks' => $employee->assignedTasks()->latest('due_date')->limit(10)->get(),
            'teamMembers' => $employee->teamMembers()->orderBy('name')->get(['id', 'name', 'is_active']),
        ]);
    }

    public function edit(User $employee): View
    {
        $this->authorize('update', $employee);

        return view('employees.edit', [
            'employee' => $employee,
            'managers' => $this->managerOptions(),
            'roles' => UserRole::cases(),
        ]);
    }

    public function update(UpdateEmployeeRequest $request, User $employee): RedirectResponse
    {
        $data = $request->validated();

        if ($request->user()->isManager()) {
            $data['role'] = UserRole::Employee->value;
            $data['manager_id'] = $request->user()->id;
        }

        // Password is optional on edit; keep the existing hash when left blank.
        if (empty($data['password'])) {
            unset($data['password']);
        }

        $data['is_active'] = $request->boolean('is_active', $employee->is_active);

        DB::transaction(fn () => $employee->update($data));

        return redirect()->route('employees.show', $employee)
            ->with('success', 'Employee details updated.');
    }

    public function toggleActive(User $employee): RedirectResponse
    {
        $this->authorize('toggleActive', $employee);

        $employee->update(['is_active' => ! $employee->is_active]);

        return back()->with('success', $employee->is_active
            ? 'Account activated.'
            : 'Account deactivated. The user can no longer sign in.');
    }

    /** Managers available as reporting managers (admins only choose among these). */
    private function managerOptions()
    {
        return User::query()
            ->where('role', UserRole::Manager->value)
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);
    }
}
