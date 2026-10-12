@extends('layouts.guest')

@section('title', 'Sign in')

@section('content')
    <h1 class="h5 mb-3 text-center">{{ config('app.name') }}</h1>

    @if (session('status'))
        <div class="alert alert-info py-2 small">{{ session('status') }}</div>
    @endif

    <form method="POST" action="{{ route('login.store') }}" novalidate>
        @csrf

        <div class="mb-3">
            <label for="email" class="form-label">Email address</label>
            <input type="email" id="email" name="email" value="{{ old('email') }}"
                   class="form-control @error('email') is-invalid @enderror"
                   autocomplete="username" required autofocus>
            @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label for="password" class="form-label">Password</label>
            <input type="password" id="password" name="password"
                   class="form-control @error('password') is-invalid @enderror"
                   autocomplete="current-password" required>
            @error('password')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-check mb-3">
            <input class="form-check-input" type="checkbox" id="remember" name="remember" value="1" @checked(old('remember'))>
            <label class="form-check-label" for="remember">Keep me signed in on this device</label>
        </div>

        <button type="submit" class="btn btn-primary w-100">Sign in</button>
    </form>
@endsection
