<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <meta name="description" content="Internal employee work management dashboard.">
    <title>@yield('title', 'Dashboard') · {{ config('app.name') }}</title>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" crossorigin="anonymous">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body>
@php
    $user = auth()->user();
@endphp

{{-- Desktop sidebar --}}
<aside class="ewm-sidebar ewm-sidebar-fixed d-none d-lg-block">
    <div class="p-3">
        <a class="ewm-brand d-block mb-3" href="{{ route('dashboard') }}">{{ config('app.name') }}</a>
        @include('partials.nav')
    </div>
</aside>

{{-- Mobile navigation drawer --}}
<div class="offcanvas offcanvas-start" tabindex="-1" id="mobileNav" aria-labelledby="mobileNavLabel" style="background: var(--ewm-sidebar-bg); color:#fff;">
    <div class="offcanvas-header">
        <h2 class="offcanvas-title h6 mb-0" id="mobileNavLabel" style="color:#fff;">{{ config('app.name') }}</h2>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close navigation"></button>
    </div>
    <div class="offcanvas-body">
        @include('partials.nav')
    </div>
</div>

<div class="ewm-main">
    {{-- Top bar --}}
    <header class="d-flex align-items-center justify-content-between gap-2 mb-3">
        <div class="d-flex align-items-center gap-2">
            <button class="btn btn-outline-secondary btn-sm d-lg-none" type="button"
                    data-bs-toggle="offcanvas" data-bs-target="#mobileNav" aria-controls="mobileNav">
                Menu
            </button>
            <span class="text-muted small d-none d-md-inline">@yield('title', 'Dashboard')</span>
        </div>
        <div class="d-flex align-items-center gap-2">
            <span class="small text-end">
                <strong>{{ $user->name }}</strong><br>
                <span class="text-muted">{{ $user->role->label() }}</span>
            </span>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="btn btn-outline-secondary btn-sm">Log out</button>
            </form>
        </div>
    </header>

    <main>
        @include('partials.flash')
        @yield('content')
    </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
</body>
</html>
