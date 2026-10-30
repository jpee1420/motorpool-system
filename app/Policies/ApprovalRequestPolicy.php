<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\ApprovalRequest;
use App\Models\User;

class ApprovalRequestPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isActive() && $user->isStaffOrAbove();
    }

    public function view(User $user, ApprovalRequest $request): bool
    {
        if (! $user->isActive()) {
            return false;
        }

        if ($user->isAdmin()) {
            return true;
        }

        return $request->requester_user_id === $user->id;
    }

    public function review(User $user, ApprovalRequest $request): bool
    {
        return $user->isActive() && $user->isAdmin() && $request->isPending();
    }
}
