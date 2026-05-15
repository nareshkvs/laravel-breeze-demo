<?php

namespace App\Policies;

use App\Models\User;
use App\Models\UserLeave;

class LeavePolicy
{
    public function approve(User $user, UserLeave $leave): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, UserLeave $leave): bool
    {
        return $user->isAdmin() || $leave->user_id === $user->id;
    }
}
