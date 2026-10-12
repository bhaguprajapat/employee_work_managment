@extends('layouts.app')

@section('title', 'Performance')

@section('content')
<div class="ewm-page-header">
    <h1 class="ewm-page-title">Performance overview</h1>
</div>

@if(! auth()->user()->isEmployee())
    <form method="GET" action="{{ route('performance.index') }}" class="ewm-section">
        <div class="row g-2 align-items-end">
            <div class="col-12 col-md-4">
                <label for="employee_id" class="form-label small mb-1">Employee</label>
                <select id="employee_id" name="employee_id" class="form-select form-select-sm" required>
                    <option value="">Select an employee</option>
                    @foreach($employees as $employee)
                        <option value="{{ $employee->id }}" @selected((string) ($filters['employee_id'] ?? '') === (string) $employee->id)>{{ $employee->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-md-2">
                <label for="from" class="form-label small mb-1">From</label>
                <input type="date" id="from" name="from" value="{{ $filters['from'] ?? '' }}" class="form-control form-control-sm">
            </div>
            <div class="col-6 col-md-2">
                <label for="to" class="form-label small mb-1">To</label>
                <input type="date" id="to" name="to" value="{{ $filters['to'] ?? '' }}" class="form-control form-control-sm">
            </div>
            <div class="col-12 col-md-4 d-flex gap-2">
                <button type="submit" class="btn btn-primary btn-sm">Show</button>
                <a href="{{ route('performance.index') }}" class="btn btn-outline-secondary btn-sm">Reset</a>
            </div>
        </div>
    </form>
@endif

@if($target && $metrics)
    <div class="ewm-section">
        <div class="small text-muted mb-2">
            {{ $target->name }} · {{ \Illuminate\Support\Carbon::parse($metrics['range_from'])->format('d M Y') }}
            to {{ \Illuminate\Support\Carbon::parse($metrics['range_to'])->format('d M Y') }}
        </div>
        <div class="row g-3">
            @php
                $cards = [
                    ['Tasks due in range', $metrics['tasks_total'], null],
                    ['Tasks completed', $metrics['tasks_completed'], null],
                    ['Tasks overdue', $metrics['tasks_overdue'], 'text-danger'],
                    ['Completion rate', $metrics['completion_rate'] !== null ? $metrics['completion_rate'].'%' : 'No tasks', null],
                    ['Updates submitted', $metrics['updates_submitted'], null],
                    ['Update consistency', $metrics['submission_consistency'] !== null ? $metrics['submission_consistency'].'%' : 'No working days', null],
                    ['Average reported progress', $metrics['average_reported_progress'] !== null ? $metrics['average_reported_progress'].'%' : 'Not reported', null],
                ];
            @endphp
            @foreach($cards as [$label, $value, $class])
                <div class="col-6 col-md-4 col-xl-3">
                    <div class="ewm-card">
                        <div class="ewm-stat-label">{{ $label }}</div>
                        <div class="ewm-stat-value {{ $class }}">{{ $value }}</div>
                    </div>
                </div>
            @endforeach
        </div>

        <p class="small text-muted mt-3 mb-0">
            Completion rate = completed tasks ÷ tasks due in range.
            Update consistency = days with at least one update ÷ weekdays in range (capped at 100%).
            These are factual indicators for review and do not constitute an evaluation decision.
        </p>
    </div>
@elseif(! auth()->user()->isEmployee())
    <div class="ewm-section ewm-empty">Select an employee to view their performance indicators.</div>
@endif
@endsection
