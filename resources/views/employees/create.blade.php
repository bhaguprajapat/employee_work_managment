@extends('layouts.app')

@section('title', 'Add employee')

@section('content')
<div class="ewm-page-header">
    <h1 class="ewm-page-title">Add employee</h1>
    <a href="{{ route('employees.index') }}" class="btn btn-outline-secondary btn-sm">Back to list</a>
</div>

<form method="POST" action="{{ route('employees.store') }}" class="ewm-section">
    @csrf
    @include('employees._form')
    <div class="d-flex gap-2 mt-3">
        <button type="submit" class="btn btn-primary btn-sm">Create account</button>
        <a href="{{ route('employees.index') }}" class="btn btn-outline-secondary btn-sm">Cancel</a>
    </div>
</form>
@endsection
