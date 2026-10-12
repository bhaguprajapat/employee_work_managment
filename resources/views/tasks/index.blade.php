@extends('layouts.app')

@section('title', 'Tasks')

@section('content')
<div class="ewm-page-header">
    <h1 class="ewm-page-title">Tasks</h1>
    @can('create', \App\Models\Task::class)
        <a href="{{ route('tasks.create') }}" class="btn btn-primary btn-sm">Allocate task</a>
    @endcan
</div>

<form method="GET" action="{{ route('tasks.index') }}" class="ewm-section">
    <div class="row g-2 align-items-end">
        <div class="col-12 col-md-3">
            <label for="employee_id" class="form-label small mb-1">Employee</label>
            <select id="employee_id" name="employee_id" class="form-select form-select-sm">
                <option value="">All employees</option>
                @foreach($employees as $employee)
                    <option value="{{ $employee->id }}" @selected((string) ($filters['employee_id'] ?? '') === (string) $employee->id)>{{ $employee->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-6 col-md-2">
            <label for="status" class="form-label small mb-1">Status</label>
            <select id="status" name="status" class="form-select form-select-sm">
                <option value="">All statuses</option>
                @foreach($statusOptions as $option)
                    <option value="{{ $option->value }}" @selected(($filters['status'] ?? '') === $option->value)>{{ $option->label() }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-6 col-md-2">
            <label for="from" class="form-label small mb-1">Due from</label>
            <input type="date" id="from" name="from" value="{{ $filters['from'] ?? '' }}" class="form-control form-control-sm">
        </div>
        <div class="col-6 col-md-2">
            <label for="to" class="form-label small mb-1">Due to</label>
            <input type="date" id="to" name="to" value="{{ $filters['to'] ?? '' }}" class="form-control form-control-sm">
        </div>
        <div class="col-6 col-md-3 d-flex gap-2">
            <button type="submit" class="btn btn-primary btn-sm">Filter</button>
            <a href="{{ route('tasks.index') }}" class="btn btn-outline-secondary btn-sm">Reset</a>
        </div>
    </div>
</form>

<section class="ewm-section">
    <div class="ewm-table-wrap">
        <table class="table table-sm table-hover ewm-table mb-0">
            <thead>
                <tr>
                    <th scope="col">Title</th>
                    <th scope="col">Assignee</th>
                    <th scope="col">Priority</th>
                    <th scope="col">Due</th>
                    <th scope="col">Status</th>
                    <th scope="col"><span class="visually-hidden">Actions</span></th>
                </tr>
            </thead>
            <tbody>
                @forelse($tasks as $task)
                    <tr>
                        <td class="ewm-wrap"><a href="{{ route('tasks.show', $task) }}">{{ $task->title }}</a></td>
                        <td>{{ $task->assignee->name }}</td>
                        <td><span class="badge text-bg-{{ $task->priority->badgeClass() }}">{{ $task->priority->label() }}</span></td>
                        <td class="{{ $task->isOverdue() ? 'ewm-overdue' : '' }}">
                            {{ $task->due_date->format('d M Y') }}
                            @if($task->isOverdue()) <span class="badge text-bg-danger ms-1">Overdue</span>@endif
                        </td>
                        <td><span class="badge text-bg-{{ $task->status->badgeClass() }}">{{ $task->status->label() }}</span></td>
                        <td class="text-end">
                            @can('update', $task)
                                <a href="{{ route('tasks.edit', $task) }}" class="btn btn-outline-primary btn-sm">Edit</a>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="ewm-empty">No tasks match these filters.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-2">{{ $tasks->links() }}</div>
</section>
@endsection
