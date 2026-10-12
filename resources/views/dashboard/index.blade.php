@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="ewm-page-header">
    <h1 class="ewm-page-title">Dashboard</h1>
</div>

{{-- Filters --}}
<form method="GET" action="{{ route('dashboard') }}" class="ewm-section">
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
            <button type="submit" class="btn btn-primary btn-sm">Apply</button>
            <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary btn-sm">Reset</a>
        </div>
    </div>
</form>

{{-- Task counts --}}
<div class="row g-3 mb-3">
    @php
        $cards = [
            ['Total tasks', $counts['total'], 'text-dark'],
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

<div class="row g-3">
    {{-- Employee update summaries --}}
    <div class="col-12 col-xl-7">
        <section class="ewm-section h-100">
            <h2 class="ewm-section-title">Employee work-update summary</h2>
            <div class="ewm-table-wrap">
                <table class="table table-sm table-hover ewm-table mb-0">
                    <thead>
                        <tr>
                            <th scope="col">Employee</th>
                            <th scope="col" class="text-end">Tasks</th>
                            <th scope="col" class="text-end">Updates</th>
                            <th scope="col">Last update</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($summaries as $row)
                            <tr>
                                <td><a href="{{ route('employees.show', $row->id) }}">{{ $row->name }}</a></td>
                                <td class="text-end">{{ $row->tasks_total }}</td>
                                <td class="text-end">{{ $row->updates_count }}</td>
                                <td>{{ $row->last_update ? \Illuminate\Support\Carbon::parse($row->last_update)->format('d M Y') : 'No updates yet' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="ewm-empty">No employees match these filters.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>

    {{-- Recent activity --}}
    <div class="col-12 col-xl-5">
        <section class="ewm-section h-100">
            <h2 class="ewm-section-title">Recent work updates</h2>
            @forelse($recentUpdates as $update)
                <div class="border-bottom py-2">
                    <div class="small fw-semibold">{{ $update->employee->name }}</div>
                    <div class="small text-muted">
                        {{ $update->task->title }} · {{ $update->work_date->format('d M Y') }}
                    </div>
                    <div class="small">{{ \Illuminate\Support\Str::limit($update->todays_progress, 120) }}</div>
                </div>
            @empty
                <div class="ewm-empty">No work updates have been submitted yet.</div>
            @endforelse
        </section>
    </div>
</div>
@endsection
