@extends('layouts.app')

@section('title', 'Work updates')

@section('content')
<div class="ewm-page-header">
    <h1 class="ewm-page-title">Work updates</h1>
    @can('create', \App\Models\DailyWorkUpdate::class)
        <a href="{{ route('work-updates.create') }}" class="btn btn-primary btn-sm">Submit update</a>
    @endcan
</div>

<form method="GET" action="{{ route('work-updates.index') }}" class="ewm-section">
    <div class="row g-2 align-items-end">
        <div class="col-6 col-md-3">
            <label for="from" class="form-label small mb-1">From</label>
            <input type="date" id="from" name="from" value="{{ $filters['from'] ?? '' }}" class="form-control form-control-sm">
        </div>
        <div class="col-6 col-md-3">
            <label for="to" class="form-label small mb-1">To</label>
            <input type="date" id="to" name="to" value="{{ $filters['to'] ?? '' }}" class="form-control form-control-sm">
        </div>
        <div class="col-12 col-md-3 d-flex gap-2">
            <button type="submit" class="btn btn-primary btn-sm">Filter</button>
            <a href="{{ route('work-updates.index') }}" class="btn btn-outline-secondary btn-sm">Reset</a>
        </div>
    </div>
</form>

<section class="ewm-section">
    <div class="ewm-table-wrap">
        <table class="table table-sm table-hover ewm-table mb-0">
            <thead>
                <tr>
                    <th scope="col">Work date</th>
                    <th scope="col">Employee</th>
                    <th scope="col">Task</th>
                    <th scope="col">Progress</th>
                    <th scope="col">Submitted</th>
                    <th scope="col"><span class="visually-hidden">Actions</span></th>
                </tr>
            </thead>
            <tbody>
                @forelse($updates as $update)
                    <tr>
                        <td>{{ $update->work_date->format('d M Y') }}</td>
                        <td>{{ $update->employee->name }}</td>
                        <td class="ewm-wrap">{{ $update->task->title }}</td>
                        <td>{{ $update->progress_percentage !== null ? $update->progress_percentage.'%' : '—' }}</td>
                        <td>{{ $update->created_at->format('d M Y, H:i') }}</td>
                        <td class="text-end">
                            @can('update', $update)
                                <a href="{{ route('work-updates.edit', $update) }}" class="btn btn-outline-primary btn-sm">Edit</a>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="ewm-empty">No work updates found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-2">{{ $updates->links() }}</div>
</section>
@endsection
