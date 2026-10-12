@extends('layouts.app')

@section('title', $task->title)

@section('content')
<div class="ewm-page-header">
    <h1 class="ewm-page-title">{{ $task->title }}</h1>
    @can('update', $task)
        <a href="{{ route('tasks.edit', $task) }}" class="btn btn-outline-primary btn-sm">Edit task</a>
    @endcan
</div>

<div class="row g-3">
    <div class="col-12 col-lg-5">
        <section class="ewm-section">
            <h2 class="ewm-section-title">Details</h2>
            <dl class="row mb-0 small">
                <dt class="col-5 text-muted">Status</dt>
                <dd class="col-7"><span class="badge text-bg-{{ $task->status->badgeClass() }}">{{ $task->status->label() }}</span></dd>
                <dt class="col-5 text-muted">Priority</dt>
                <dd class="col-7"><span class="badge text-bg-{{ $task->priority->badgeClass() }}">{{ $task->priority->label() }}</span></dd>
                <dt class="col-5 text-muted">Assignee</dt>
                <dd class="col-7">{{ $task->assignee->name }}</dd>
                <dt class="col-5 text-muted">Allocated by</dt>
                <dd class="col-7">{{ $task->creator->name }}</dd>
                <dt class="col-5 text-muted">Start date</dt>
                <dd class="col-7">{{ $task->start_date->format('d M Y') }}</dd>
                <dt class="col-5 text-muted">Due date</dt>
                <dd class="col-7 {{ $task->isOverdue() ? 'ewm-overdue' : '' }}">
                    {{ $task->due_date->format('d M Y') }}
                    @if($task->isOverdue()) (overdue)@endif
                </dd>
            </dl>
        </section>

        <section class="ewm-section">
            <h2 class="ewm-section-title">Description</h2>
            <p class="mb-0 ewm-text-pre">{{ $task->description ?: 'No description provided.' }}</p>
        </section>
    </div>

    <div class="col-12 col-lg-7">
        <section class="ewm-section">
            <h2 class="ewm-section-title">Recent progress updates</h2>
            @forelse($updates as $update)
                <div class="border-bottom py-2">
                    <div class="small fw-semibold">
                        {{ $update->employee->name }} · {{ $update->work_date->format('d M Y') }}
                        @if($update->progress_percentage !== null)
                            <span class="badge text-bg-light border ms-1">{{ $update->progress_percentage }}%</span>
                        @endif
                    </div>
                    <div class="small ewm-text-pre">{{ $update->todays_progress }}</div>
                    @if($update->blockers)
                        <div class="small text-danger">Blocker: {{ $update->blockers }}</div>
                    @endif
                </div>
            @empty
                <div class="ewm-empty">No progress updates have been submitted for this task.</div>
            @endforelse
        </section>
    </div>
</div>
@endsection
