<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\TripTicket;
use App\Models\User;

class TripTicketPolicy
{
    /**
     * Determine whether the user can view any trip tickets.
     */
    public function viewAny(User $user): bool
    {
        return $user->isActive();
    }

    /**
     * Determine whether the user can view the trip ticket.
     */
    public function view(User $user, TripTicket $tripTicket): bool
    {
        if (! $user->isActive()) {
            return false;
        }

        if ($user->isStaffOrAbove()) {
            return true;
        }

        return (int) $tripTicket->requested_by_user_id === (int) $user->id
            || (int) optional($tripTicket->vehicle)->user_id === (int) $user->id;
    }

    /**
     * Determine whether the user can create trip tickets.
     */
    public function create(User $user): bool
    {
        return $user->isActive() && $user->isStaffOrAbove();
    }

    /**
     * Determine whether the user can update the trip ticket.
     */
    public function update(User $user, TripTicket $tripTicket): bool
    {
        return $user->isActive() && $user->isStaffOrAbove();
    }

    /**
     * Determine whether the user can delete the trip ticket.
     */
    public function delete(User $user, TripTicket $tripTicket): bool
    {
        return $user->isActive() && $user->isAdmin();
    }

    /**
     * Determine whether the user can export trip tickets.
     */
    public function export(User $user): bool
    {
        return $user->isActive() && $user->isStaffOrAbove();
    }
}
