<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DailyWorkUpdate extends Model
{
    use HasFactory;

    protected $fillable = [
        'task_id',
        'employee_id',
        'work_date',
        'completed_work',
        'todays_progress',
        'pending_work',
        'blockers',
        'progress_percentage',
    ];

    protected function casts(): array
    {
        return [
            'work_date' => 'date',
            'task_id' => 'integer',
            'employee_id' => 'integer',
            'progress_percentage' => 'integer',
        ];
    }

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'employee_id');
    }
}
