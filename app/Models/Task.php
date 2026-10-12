<?php

namespace App\Models;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'start_date',
        'due_date',
        'priority',
        'status',
        'assigned_to',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'due_date' => 'date',
            'priority' => TaskPriority::class,
            'status' => TaskStatus::class,
            'assigned_to' => 'integer',
            'created_by' => 'integer',
        ];
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function workUpdates(): HasMany
    {
        return $this->hasMany(DailyWorkUpdate::class);
    }

    /**
     * Overdue = past due date and not completed. Completed tasks are never overdue.
     */
    public function scopeOverdue(Builder $query): Builder
    {
        return $query
            ->where('status', '!=', TaskStatus::Completed->value)
            ->whereNotNull('due_date')
            ->whereDate('due_date', '<', Carbon::today());
    }

    /**
     * Limit tasks to what the given user is allowed to see.
     * Admins: all. Managers: tasks assigned to their team or created by them.
     * Employees: only tasks assigned to them.
     */
    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        if ($user->isAdmin()) {
            return $query;
        }

        if ($user->isManager()) {
            return $query->where(function (Builder $q) use ($user): void {
                $q->where('created_by', $user->id)
                    ->orWhereIn('assigned_to', $user->teamMembers()->select('id'));
            });
        }

        return $query->where('assigned_to', $user->id);
    }

    public function isOverdue(): bool
    {
        return $this->status !== TaskStatus::Completed
            && $this->due_date !== null
            && $this->due_date->lt(Carbon::today());
    }
}
