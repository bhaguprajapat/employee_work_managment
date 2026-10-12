@extends('layouts.app')

@section('title', $employee->name)

@section('content')
<div class="ewm-page-header">
    <h1 class="ewm-page-title">{{ $employee->name }}</h1>
    <div class="d-flex gap-2">
        @can('update', $employee)
            <a href="{{ route('employees.edit', $employee) }}" class="btn btn-outline-primary btn-sm">Edit</a>
        @endcan
        @can('toggleActive', $employee)
            <form method="POST" action="{{ route('employees.toggle-active', $employee) }}">
                @csrf
                <button type="submit" class="btn btn-sm {{ $employee->is_active ? 'btn-outline-danger' : 'btn-outline-success' }}">
                    {{ $employee->is_active ? 'Deactivate account' : 'Activate account' }}
                </button>
            </form>
        @endcan
    </div>
</div>

<div class="row g-3">
    <div class="col-12 col-lg-5">
        <section class="ewm-section">
            <h2 class="ewm-section-title">Profile</h2>
            <dl class="row mb-0 small">
                <dt class="col-5 text-muted">Email</dt>
                <dd class="col-7">{{ $employee->email }}</dd>
                <dt class="col-5 text-muted">Role</dt>
                <dd class="col-7">{{ $employee->role->label() }}</dd>
                <dt class="col-5 text-muted">Job title</dt>
                <dd class="col-7">{{ $employee->job_title ?? '—' }}</dd>
                <dt class="col-5 text-muted">Manager</dt>
                <dd class="col-7">{{ $employee->manager?->name ?? '—' }}</dd>
                <dt class="col-5 text-muted">Status</dt>
                <dd class="col-7">{{ $employee->is_active ? 'Active' : 'Inactive' }}</dd>
            </dl>
            @if($employee->isEmployee())
                <a href="{{ route('performance.index', ['employee_id' => $employee->id]) }}" class="btn btn-outline-secondary btn-sm mt-3">View performance</a>
            @endif
        </section>

        @if($teamMembers->isNotEmpty())
            <section class="ewm-section">
                <h2 class="ewm-section-title">Team members</h2>
                <ul class="list-unstyled small mb-0">
                    @foreach($teamMembers as $member)
                        <li class="py-1 border-bottom">
                            <a href="{{ route('employees.show', $member) }}">{{ $member->name }}</a>
                            @unless($member->is_active)<span class="badge text-bg-secondary ms-1">Inactive</span>@endunless
                        </li>
                    @endforeach
                </ul>
            </section>
        @endif
    </div>

    <div class="col-12 col-lg-7">
        <section class="ewm-section">
            <h2 class="ewm-section-title">Recent tasks</h2>
            <div class="ewm-table-wrap">
                <table class="table table-sm table-hover ewm-table mb-0">
                    <thead>
                        <tr>
                            <th scope="col">Title</th>
                            <th scope="col">Due</th>
                            <th scope="col">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentTasks as $task)
                            <tr>
                                <td class="ewm-wrap"><a href="{{ route('tasks.show', $task) }}">{{ $task->title }}</a></td>
                                <td class="{{ $task->isOverdue() ? 'ewm-overdue' : '' }}">{{ $task->due_date->format('d M Y') }}</td>
                                <td><span class="badge text-bg-{{ $task->status->badgeClass() }}">{{ $task->status->label() }}</span></td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="ewm-empty">No tasks assigned yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</div>
@endsection
