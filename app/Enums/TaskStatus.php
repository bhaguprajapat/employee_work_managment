<?php

namespace App\Enums;

enum TaskStatus: string
{
    case ToDo = 'todo';
    case InProgress = 'in_progress';
    case Completed = 'completed';

    public function label(): string
    {
        return match ($this) {
            self::ToDo => 'To Do',
            self::InProgress => 'In Progress',
            self::Completed => 'Completed',
        };
    }

    /** CSS class suffix used for status badges. */
    public function badgeClass(): string
    {
        return match ($this) {
            self::ToDo => 'secondary',
            self::InProgress => 'primary',
            self::Completed => 'success',
        };
    }
}
