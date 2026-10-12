@extends('layouts.app')

@section('title', 'Edit task')

@section('content')
<div class="ewm-page-header">
    <h1 class="ewm-page-title">Edit task</h1>
    <a href="{{ route('tasks.show', $task) }}" class="btn btn-outline-secondary btn-sm">Back to task</a>
</div>

<form method="POST" action="{{ route('tasks.update', $task) }}" class="ewm-section">
    @csrf
    @method('PUT')
    @include('tasks._form')
    <div class="d-flex gap-2 mt-3">
        <button type="submit" class="btn btn-primary btn-sm">Save changes</button>
        <a href="{{ route('tasks.show', $task) }}" class="btn btn-outline-secondary btn-sm">Cancel</a>
    </div>
</form>
@endsection
