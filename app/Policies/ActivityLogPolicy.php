<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\ActivityLog;
use App\Models\User;

class ActivityLogPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isActive() && $user->isStaffOrAbove();
    }

    public function view(User $user, ActivityLog $log): bool
    {
        return $user->isActive() && $user->isStaffOrAbove();
    }

    public function delete(User $user, ActivityLog $log): bool
    {
        return $user->isActive() && $user->isAdmin();
    }
}
