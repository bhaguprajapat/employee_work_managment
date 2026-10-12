<?php

namespace App\Policies;

use App\Models\Task;
use App\Models\User;

class TaskPolicy
{
    /** Admins and managers get the full task list; employees use their own task view. */
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->isManager();
    }

    public function view(User $user, Task $task): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($task->assigned_to === $user->id) {
            return true;
        }

        return $user->isManager() && $this->managerControlsTask($user, $task);
    }

    public function create(User $user): bool
    {
        return $user->isAdmin() || $user->isManager();
    }

    public function update(User $user, Task $task): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $user->isManager() && $this->managerControlsTask($user, $task);
    }

    public function delete(User $user, Task $task): bool
    {
        return $this->update($user, $task);
    }

    /** Manager controls a task if they created it or the assignee is on their team. */
    private function managerControlsTask(User $manager, Task $task): bool
    {
        if ($task->created_by === $manager->id) {
            return true;
        }

        return $task->assignee !== null && $manager->managesEmployee($task->assignee);
    }
}
