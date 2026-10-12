<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    /** Admins and managers can browse the employee directory. Employees cannot. */
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->isManager();
    }

    /** Own profile, or a profile within the viewer's management scope. */
    public function view(User $user, User $employee): bool
    {
        return $user->is($employee) || $user->managesEmployee($employee);
    }

    /** Admins and managers can create accounts; managers are restricted to employees in the controller. */
    public function create(User $user): bool
    {
        return $user->isAdmin() || $user->isManager();
    }

    /** Admins edit anyone; managers edit only their own team members. */
    public function update(User $user, User $employee): bool
    {
        return $user->managesEmployee($employee);
    }

    /** Activation toggling is limited to the management scope and never applies to yourself. */
    public function toggleActive(User $user, User $employee): bool
    {
        if ($user->is($employee)) {
            return false;
        }

        return $user->managesEmployee($employee);
    }
}
