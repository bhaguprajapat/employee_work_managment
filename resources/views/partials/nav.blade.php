@php
    $user = auth()->user();
@endphp
<nav class="nav flex-column gap-1" aria-label="Main navigation">
    <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">Dashboard</a>

    @if($user->role->value !== 'employee')
        <a class="nav-link {{ request()->routeIs('employees.*') ? 'active' : '' }}" href="{{ route('employees.index') }}">Employees</a>
    @endif

    <a class="nav-link {{ request()->routeIs('tasks.*') ? 'active' : '' }}" href="{{ route('tasks.index') }}">
        {{ $user->isEmployee() ? 'My Tasks' : 'Tasks' }}
    </a>

    <a class="nav-link {{ request()->routeIs('work-updates.*') ? 'active' : '' }}" href="{{ route('work-updates.index') }}">Work Updates</a>

    @if($user->isEmployee())
        <a class="nav-link {{ request()->routeIs('work-updates.create') ? 'active' : '' }}" href="{{ route('work-updates.create') }}">Submit Update</a>
    @endif

    <a class="nav-link {{ request()->routeIs('performance.*') ? 'active' : '' }}" href="{{ route('performance.index') }}">Performance</a>
</nav>
