<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\PerformanceController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\WorkUpdateController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('dashboard'))->name('home');

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.store');
});

Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

Route::middleware(['auth', 'active'])->group(function (): void {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/performance', [PerformanceController::class, 'index'])->name('performance.index');

    // Work updates: employees submit and edit their own; managers and admins view within scope.
    Route::resource('work-updates', WorkUpdateController::class)
        ->parameters(['work-updates' => 'workUpdate'])
        ->only(['index', 'create', 'store', 'edit', 'update']);

    Route::resource('tasks', TaskController::class)
        ->only(['index', 'create', 'store', 'show', 'edit', 'update']);

    // Employee management is restricted to admins and managers at route level;
    // record-level scope is enforced again in UserPolicy.
    Route::middleware('role:admin,manager')->group(function (): void {
        Route::resource('employees', EmployeeController::class)
            ->only(['index', 'create', 'store', 'show', 'edit', 'update']);

        Route::post('/employees/{employee}/toggle-active', [EmployeeController::class, 'toggleActive'])
            ->name('employees.toggle-active');
    });
});
