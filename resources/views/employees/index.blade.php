@extends('layouts.app')

@section('title', 'Employees')

@section('content')
<div class="ewm-page-header">
    <h1 class="ewm-page-title">Employees</h1>
    <a href="{{ route('employees.create') }}" class="btn btn-primary btn-sm">Add employee</a>
</div>

<form method="GET" action="{{ route('employees.index') }}" class="ewm-section">
    <div class="row g-2 align-items-end">
        <div class="col-12 col-md-6">
            <label for="search" class="form-label small mb-1">Search by name or email</label>
            <input type="search" id="search" name="search" value="{{ $search }}" class="form-control form-control-sm" maxlength="100">
        </div>
        <div class="col-6 col-md-3">
            <label for="status" class="form-label small mb-1">Account status</label>
            <select id="status" name="status" class="form-select form-select-sm">
                <option value="">All</option>
                <option value="active" @selected($status === 'active')>Active</option>
                <option value="inactive" @selected($status === 'inactive')>Inactive</option>
            </select>
        </div>
        <div class="col-6 col-md-3 d-flex gap-2">
            <button type="submit" class="btn btn-primary btn-sm">Filter</button>
            <a href="{{ route('employees.index') }}" class="btn btn-outline-secondary btn-sm">Reset</a>
        </div>
    </div>
</form>

<section class="ewm-section">
    <div class="ewm-table-wrap">
        <table class="table table-sm table-hover ewm-table mb-0">
            <thead>
                <tr>
                    <th scope="col">Name</th>
                    <th scope="col">Email</th>
                    <th scope="col">Role</th>
                    <th scope="col">Manager</th>
                    <th scope="col">Status</th>
                    <th scope="col"><span class="visually-hidden">Actions</span></th>
                </tr>
            </thead>
            <tbody>
                @forelse($employees as $employee)
                    <tr>
                        <td><a href="{{ route('employees.show', $employee) }}">{{ $employee->name }}</a></td>
                        <td>{{ $employee->email }}</td>
                        <td>{{ $employee->role->label() }}</td>
                        <td>{{ $employee->manager?->name ?? '—' }}</td>
                        <td>
                            @if($employee->is_active)
                                <span class="badge text-bg-success">Active</span>
                            @else
                                <span class="badge text-bg-secondary">Inactive</span>
                            @endif
                        </td>
                        <td class="text-end">
                            @can('update', $employee)
                                <a href="{{ route('employees.edit', $employee) }}" class="btn btn-outline-primary btn-sm">Edit</a>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="ewm-empty">No employees found. Try a different search or add a new employee.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-2">{{ $employees->links() }}</div>
</section>
@endsection
