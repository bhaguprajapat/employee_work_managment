<?php

namespace App\Models;

use App\Enums\UserRole;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * Only these attributes can be filled via mass assignment.
     * Role, manager and active flags are set explicitly by admins in controllers.
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'manager_id',
        'job_title',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'role' => UserRole::class,
            'manager_id' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function manager(): BelongsTo
    {
        return $this->belongsTo(self::class, 'manager_id');
    }

    /** Employees assigned to this manager. */
    public function teamMembers(): HasMany
    {
        return $this->hasMany(self::class, 'manager_id');
    }

    /** Tasks allocated to this user as an assignee. */
    public function assignedTasks(): HasMany
    {
        return $this->hasMany(Task::class, 'assigned_to');
    }

    public function workUpdates(): HasMany
    {
        return $this->hasMany(DailyWorkUpdate::class, 'employee_id');
    }

    public function isAdmin(): bool
    {
        return $this->role === UserRole::Admin;
    }

    public function isManager(): bool
    {
        return $this->role === UserRole::Manager;
    }

    public function isEmployee(): bool
    {
        return $this->role === UserRole::Employee;
    }

    /**
     * Management scope: admins manage everyone, managers manage their direct
     * team members, employees manage nobody.
     */
    public function managesEmployee(self $employee): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        return $this->isManager() && $employee->manager_id === $this->id;
    }
}
