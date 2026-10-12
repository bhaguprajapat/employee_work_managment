@extends('layouts.app')

@section('title', 'My Dashboard')

@section('content')
<div class="ewm-page-header">
    <h1 class="ewm-page-title">My tasks</h1>
    <a href="{{ route('work-updates.create') }}" class="btn btn-primary btn-sm">Submit daily update</a>
</div>

<div class="row g-3 mb-3">
    @php
        $cards = [
            ['Assigned', $counts['total'], 'text-dark'],
            ['To do', $counts['todo'], 'text-secondary'],
            ['In progress', $counts['in_progress'], 'text-primary'],
            ['Completed', $counts['completed'], 'text-success'],
            ['Overdue', $counts['overdue'], 'text-danger'],
        ];
    @endphp
    @foreach($cards as [$label, $value, $class])
        <div class="col-6 col-md-4 col-xl">
            <div class="ewm-card">
                <div class="ewm-stat-label">{{ $label }}</div>
                <div class="ewm-stat-value {{ $class }}">{{ $value }}</div>
            </div>
        </div>
    @endforeach
</div>

<section class="ewm-section">
    <h2 class="ewm-section-title">Assigned tasks</h2>
    <div class="ewm-table-wrap">
        <table class="table table-sm table-hover ewm-table mb-0">
            <thead>
                <tr>
                    <th scope="col">Title</th>
                    <th scope="col">Due</th>
                    <th scope="col">Priority</th>
                    <th scope="col">Status</th>
                    <th scope="col"><span class="visually-hidden">Actions</span></th>
                </tr>
            </thead>
            <tbody>
                @forelse($tasks as $task)
                    <tr>
                        <td class="ewm-wrap">{{ $task->title }}</td>
                        <td class="{{ $task->isOverdue() ? 'ewm-overdue' : '' }}">
                            {{ $task->due_date->format('d M Y') }}
                            @if($task->isOverdue()) <span class="badge text-bg-danger ms-1">Overdue</span>@endif
                        </td>
                        <td><span class="badge text-bg-{{ $task->priority->badgeClass() }}">{{ $task->priority->label() }}</span></td>
                        <td><span class="badge text-bg-{{ $task->status->badgeClass() }}">{{ $task->status->label() }}</span></td>
                        <td class="text-end"><a href="{{ route('tasks.show', $task) }}" class="btn btn-outline-primary btn-sm">View</a></td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="ewm-empty">No tasks have been assigned to you yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-2">{{ $tasks->links() }}</div>
</section>
@endsection
