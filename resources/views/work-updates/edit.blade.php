@extends('layouts.app')

@section('title', 'Edit work update')

@section('content')
<div class="ewm-page-header">
    <h1 class="ewm-page-title">Edit work update</h1>
    <a href="{{ route('work-updates.index') }}" class="btn btn-outline-secondary btn-sm">Back to updates</a>
</div>

<div class="alert alert-light border small py-2">
    Updates can only be edited on the day they were submitted. Task and work date are fixed.
</div>

<form method="POST" action="{{ route('work-updates.update', $update) }}" class="ewm-section">
    @csrf
    @method('PUT')
    @include('work-updates._form', ['update' => $update])
    <div class="d-flex gap-2 mt-3">
        <button type="submit" class="btn btn-primary btn-sm">Save changes</button>
        <a href="{{ route('work-updates.index') }}" class="btn btn-outline-secondary btn-sm">Cancel</a>
    </div>
</form>
@endsection
