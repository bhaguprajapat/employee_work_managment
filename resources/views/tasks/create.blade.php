@extends('layouts.app')

@section('title', 'Allocate task')

@section('content')
<div class="ewm-page-header">
    <h1 class="ewm-page-title">Allocate task</h1>
    <a href="{{ route('tasks.index') }}" class="btn btn-outline-secondary btn-sm">Back to tasks</a>
</div>

<form method="POST" action="{{ route('tasks.store') }}" class="ewm-section">
    @csrf
    @include('tasks._form')
    <div class="d-flex gap-2 mt-3">
        <button type="submit" class="btn btn-primary btn-sm">Allocate task</button>
        <a href="{{ route('tasks.index') }}" class="btn btn-outline-secondary btn-sm">Cancel</a>
    </div>
</form>
@endsection
