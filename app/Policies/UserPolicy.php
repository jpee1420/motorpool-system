<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    /**
     * Determine whether the user can view any users.
     */
    public function viewAny(User $user): bool
    {
        return $user->isActive() && $user->isAdmin();
    }

    /**
     * Determine whether the user can view a user.
     */
    public function view(User $user, User $model): bool
    {
        // Users can view their own profile; admins can view any
        return $user->isActive() && ($user->id === $model->id || $user->isAdmin());
    }

    /**
     * Determine whether the user can create users.
     */
    public function create(User $user): bool
    {
        return $user->isActive() && $user->isAdmin();
    }

    /**
     * Determine whether the user can update a user.
     */
    public function update(User $user, User $model): bool
    {
        // Users can update their own profile; admins can update any
        return $user->isActive() && ($user->id === $model->id || $user->isAdmin());
    }

    /**
     * Determine whether the user can delete a user.
     */
    public function delete(User $user, User $model): bool
    {
        // Only admins can delete, and not themselves
        return $user->isActive() && $user->isAdmin() && $user->id !== $model->id;
    }

    /**
     * Determine whether the user can manage users (change roles, status).
     */
    public function manage(User $user): bool
    {
        return $user->isActive() && $user->isAdmin();
    }
}
