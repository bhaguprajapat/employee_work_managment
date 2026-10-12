<?php

namespace App\Policies;

use App\Models\DailyWorkUpdate;
use App\Models\User;
use Illuminate\Support\Carbon;

class WorkUpdatePolicy
{
    /** Only employees submit updates against their own assigned tasks. */
    public function create(User $user): bool
    {
        return $user->isEmployee();
    }

    /** Owner or anyone who manages the owner can view an update. */
    public function view(User $user, DailyWorkUpdate $update): bool
    {
        if ($update->employee_id === $user->id) {
            return true;
        }

        return $user->isAdmin()
            || ($user->isManager() && $user->managesEmployee($update->employee));
    }

    /**
     * Employees may edit their own updates only on the day they were submitted
     * (work_date equals today). Managers and admins cannot rewrite employee updates.
     */
    public function update(User $user, DailyWorkUpdate $update): bool
    {
        return $update->employee_id === $user->id
            && $update->work_date->isSameDay(Carbon::today());
    }
}
