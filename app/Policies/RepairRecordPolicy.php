<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\RepairRecord;
use App\Models\User;

class RepairRecordPolicy
{
    /**
     * Determine whether the user can view any repair records.
     */
    public function viewAny(User $user): bool
    {
        return $user->isActive();
    }

    /**
     * Determine whether the user can view the repair record.
     */
    public function view(User $user, RepairRecord $record): bool
    {
        return $user->isActive();
    }

    /**
     * Determine whether the user can create repair records.
     */
    public function create(User $user): bool
    {
        return $user->isActive() && $user->isStaffOrAbove();
    }

    /**
     * Determine whether the user can update the repair record.
     */
    public function update(User $user, RepairRecord $record): bool
    {
        return $user->isActive() && $user->isStaffOrAbove();
    }

    /**
     * Determine whether the user can delete the repair record.
     */
    public function delete(User $user, RepairRecord $record): bool
    {
        return $user->isActive() && $user->isAdmin();
    }

    /**
     * Determine whether the user can export repair records.
     */
    public function export(User $user): bool
    {
        return $user->isActive() && $user->isStaffOrAbove();
    }
}
