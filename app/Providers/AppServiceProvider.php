<?php

namespace App\Providers;

use App\Models\DailyWorkUpdate;
use App\Models\Task;
use App\Models\User;
use App\Policies\TaskPolicy;
use App\Policies\UserPolicy;
use App\Policies\WorkUpdatePolicy;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Gate::policy(User::class, UserPolicy::class);
        Gate::policy(Task::class, TaskPolicy::class);
        Gate::policy(DailyWorkUpdate::class, WorkUpdatePolicy::class);

        Paginator::useBootstrapFive();
    }
}
