<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\MaintenanceRecord;
use App\Models\User;

class MaintenanceRecordPolicy
{
    /**
     * Determine whether the user can view any maintenance records.
     */
    public function viewAny(User $user): bool
    {
        return $user->isActive();
    }

    /**
     * Determine whether the user can view the maintenance record.
     */
    public function view(User $user, MaintenanceRecord $record): bool
    {
        return $user->isActive();
    }

    /**
     * Determine whether the user can create maintenance records.
     */
    public function create(User $user): bool
    {
        return $user->isActive() && $user->isStaffOrAbove();
    }

    /**
     * Determine whether the user can update the maintenance record.
     */
    public function update(User $user, MaintenanceRecord $record): bool
    {
        return $user->isActive() && $user->isStaffOrAbove();
    }

    /**
     * Determine whether the user can delete the maintenance record.
     */
    public function delete(User $user, MaintenanceRecord $record): bool
    {
        return $user->isActive() && $user->isAdmin();
    }

    /**
     * Determine whether the user can export maintenance records.
     */
    public function export(User $user): bool
    {
        return $user->isActive() && $user->isStaffOrAbove();
    }
}
