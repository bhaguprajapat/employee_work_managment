@extends('layouts.app')

@section('title', 'Submit work update')

@section('content')
<div class="ewm-page-header">
    <h1 class="ewm-page-title">Submit daily work update</h1>
    <a href="{{ route('work-updates.index') }}" class="btn btn-outline-secondary btn-sm">Back to updates</a>
</div>

<form method="POST" action="{{ route('work-updates.store') }}" class="ewm-section">
    @csrf
    @include('work-updates._form', ['tasks' => $tasks, 'maxBackdate' => $maxBackdate])
    <div class="d-flex gap-2 mt-3">
        <button type="submit" class="btn btn-primary btn-sm" @disabled($tasks->isEmpty())>Submit update</button>
        <a href="{{ route('work-updates.index') }}" class="btn btn-outline-secondary btn-sm">Cancel</a>
    </div>
</form>
@endsection
