<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\NotificationLog;
use App\Models\User;

class NotificationLogPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isActive() && $user->isStaffOrAbove();
    }

    public function view(User $user, NotificationLog $notificationLog): bool
    {
        return $user->isActive() && ($user->isStaffOrAbove() || $notificationLog->user_id === $user->id);
    }

    public function manage(User $user): bool
    {
        return $user->isActive() && $user->isStaffOrAbove();
    }

    public function export(User $user): bool
    {
        return $this->manage($user);
    }

    public function retry(User $user, NotificationLog $notificationLog): bool
    {
        return $this->manage($user) && $notificationLog->user_id !== null;
    }

    public function archive(User $user, NotificationLog $notificationLog): bool
    {
        return $this->manage($user) && $notificationLog->user_id !== null;
    }

    public function markAsRead(User $user, NotificationLog $notificationLog): bool
    {
        return $user->isActive() && $notificationLog->user_id === $user->id;
    }
}
