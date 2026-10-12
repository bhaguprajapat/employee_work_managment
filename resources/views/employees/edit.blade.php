@extends('layouts.app')

@section('title', 'Edit employee')

@section('content')
<div class="ewm-page-header">
    <h1 class="ewm-page-title">Edit {{ $employee->name }}</h1>
    <a href="{{ route('employees.show', $employee) }}" class="btn btn-outline-secondary btn-sm">Back to profile</a>
</div>

<form method="POST" action="{{ route('employees.update', $employee) }}" class="ewm-section">
    @csrf
    @method('PUT')
    @include('employees._form')
    <div class="d-flex gap-2 mt-3">
        <button type="submit" class="btn btn-primary btn-sm">Save changes</button>
        <a href="{{ route('employees.show', $employee) }}" class="btn btn-outline-secondary btn-sm">Cancel</a>
    </div>
</form>
@endsection
