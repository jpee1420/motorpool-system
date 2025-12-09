<?php

declare(strict_types=1);

namespace App\Livewire\Account;

use App\Models\User;
use App\Notifications\AccountApprovedNotification;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

class UsersIndex extends Component
{
    use AuthorizesRequests;
    use WithPagination;

    public ?string $search = null;
    public ?string $role = null;
    public ?string $status = null;
    public string $sortField = 'created_at';
    public string $sortDirection = 'desc';

    public ?int $confirmingUserId = null;
    public ?string $confirmingAction = null; // approve or reject
    public ?string $confirmingUserName = null;

    // Create user
    public bool $showCreateUserModal = false;
    public string $newUserName = '';
    public string $newUserEmail = '';
    public string $newUserRole = User::ROLE_USER;
    public string $newUserStatus = User::STATUS_ACTIVE;
    public string $newUserPassword = '';
    public string $newUserPasswordConfirmation = '';

    // Password reset
    public bool $showPasswordResetModal = false;
    public ?int $resetPasswordUserId = null;
    public ?string $resetPasswordUserName = null;
    public string $newPassword = '';
    public string $newPasswordConfirmation = '';

    protected function queryString(): array
    {
        return [
            'search' => ['except' => null],
            'role' => ['except' => null],
            'status' => ['except' => null],
            'sortField' => ['except' => 'created_at'],
            'sortDirection' => ['except' => 'desc'],
            'page' => ['except' => 1],
        ];
    }

    public function mount(): void
    {
        $this->authorize('viewAny', User::class);
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingRole(): void
    {
        $this->resetPage();
    }

    public function updatingStatus(): void
    {
        $this->resetPage();
    }

    public function sortBy(string $field): void
    {
        $allowed = ['name', 'email', 'role', 'status', 'created_at'];

        if (! in_array($field, $allowed, true)) {
            return;
        }

        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = $field === 'created_at' ? 'desc' : 'asc';
        }

        $this->resetPage();
    }

    public function confirmApprove(int $userId): void
    {
        $user = User::query()->find($userId);

        if ($user === null) {
            return;
        }

        $this->confirmingUserId = $user->id;
        $this->confirmingUserName = $user->name;
        $this->confirmingAction = 'approve';
    }

    public function confirmReject(int $userId): void
    {
        $user = User::query()->find($userId);

        if ($user === null) {
            return;
        }

        $this->confirmingUserId = $user->id;
        $this->confirmingUserName = $user->name;
        $this->confirmingAction = 'reject';
    }

    public function cancelConfirmation(): void
    {
        $this->reset(['confirmingUserId', 'confirmingAction', 'confirmingUserName']);
    }

    public function performConfirmation(): void
    {
        if ($this->confirmingUserId === null || $this->confirmingAction === null) {
            return;
        }

        if ($this->confirmingAction === 'approve') {
            $this->approveUser($this->confirmingUserId);
        } elseif ($this->confirmingAction === 'reject') {
            $this->rejectUser($this->confirmingUserId);
        }

        $this->cancelConfirmation();
    }

    public function setRole(int $userId, string $role): void
    {
        $this->authorize('manage', User::class);

        if (! in_array($role, [User::ROLE_ADMIN, User::ROLE_STAFF, User::ROLE_USER], true)) {
            return;
        }

        $currentUser = Auth::user();

        // Prevent admin from demoting themselves
        if ($currentUser?->id === $userId && $role !== User::ROLE_ADMIN) {
            return;
        }

        $user = User::query()->find($userId);

        if ($user === null) {
            return;
        }

        $user->update(['role' => $role]);
    }

    public function toggleStatus(int $userId): void
    {
        $this->authorize('manage', User::class);

        $currentUser = Auth::user();

        // Prevent admin from disabling themselves
        if ($currentUser?->id === $userId) {
            return;
        }

        $user = User::query()->find($userId);

        if ($user === null) {
            return;
        }

        $user->update([
            'status' => $user->status === User::STATUS_ACTIVE ? User::STATUS_DISABLED : User::STATUS_ACTIVE,
        ]);
    }

    public function approveUser(int $userId): void
    {
        $this->authorize('manage', User::class);

        $user = User::query()->find($userId);

        if ($user === null || ! $user->isPending()) {
            return;
        }

        $user->update([
            'status' => User::STATUS_ACTIVE,
            'approved_at' => now(),
            'approved_by' => Auth::id(),
        ]);

        // Notify the user that their account has been approved
        $user->notify(new AccountApprovedNotification());

        session()->flash('success', __('User :name has been approved.', ['name' => $user->name]));
    }

    public function rejectUser(int $userId): void
    {
        $this->authorize('manage', User::class);

        $user = User::query()->find($userId);

        if ($user === null || ! $user->isPending()) {
            return;
        }

        // Delete the pending user
        $user->delete();

        session()->flash('success', __('Registration request has been rejected and removed.'));
    }

    public function getPendingCount(): int
    {
        return User::where('status', User::STATUS_PENDING)->count();
    }

    public function openCreateUserModal(): void
    {
        $this->authorize('manage', User::class);

        $this->reset([
            'newUserName',
            'newUserEmail',
            'newUserRole',
            'newUserStatus',
            'newUserPassword',
            'newUserPasswordConfirmation',
        ]);

        $this->newUserRole = User::ROLE_USER;
        $this->newUserStatus = User::STATUS_ACTIVE;
        $this->showCreateUserModal = true;
    }

    public function closeCreateUserModal(): void
    {
        $this->reset([
            'showCreateUserModal',
            'newUserName',
            'newUserEmail',
            'newUserRole',
            'newUserStatus',
            'newUserPassword',
            'newUserPasswordConfirmation',
        ]);
    }

    public function generateNewUserPassword(): void
    {
        $password = Str::random(12);
        $this->newUserPassword = $password;
        $this->newUserPasswordConfirmation = $password;
    }

    public function createUser(): void
    {
        $this->authorize('manage', User::class);

        $this->validate([
            'newUserName' => ['required', 'string', 'max:255'],
            'newUserEmail' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'newUserRole' => ['required', Rule::in([User::ROLE_ADMIN, User::ROLE_STAFF, User::ROLE_USER])],
            'newUserStatus' => ['required', Rule::in([User::STATUS_ACTIVE, User::STATUS_DISABLED])],
            'newUserPassword' => ['required', 'string', 'min:8', 'same:newUserPasswordConfirmation'],
        ], [
            'newUserPassword.same' => __('The passwords do not match.'),
            'newUserPassword.min' => __('The password must be at least 8 characters.'),
        ]);

        $user = User::create([
            'name' => $this->newUserName,
            'email' => $this->newUserEmail,
            'password' => Hash::make($this->newUserPassword),
            'role' => $this->newUserRole,
            'status' => $this->newUserStatus,
            'approved_at' => now(),
            'approved_by' => Auth::id(),
        ]);

        $this->closeCreateUserModal();

        $this->resetPage();

        session()->flash('success', __('User :name has been created successfully.', ['name' => $user->name]));
    }

    public function openPasswordResetModal(int $userId): void
    {
        $user = User::query()->find($userId);

        if ($user === null) {
            return;
        }

        $this->resetPasswordUserId = $user->id;
        $this->resetPasswordUserName = $user->name;
        $this->newPassword = '';
        $this->newPasswordConfirmation = '';
        $this->showPasswordResetModal = true;
    }

    public function closePasswordResetModal(): void
    {
        $this->reset(['showPasswordResetModal', 'resetPasswordUserId', 'resetPasswordUserName', 'newPassword', 'newPasswordConfirmation']);
    }

    public function generateRandomPassword(): void
    {
        $password = Str::random(12);
        $this->newPassword = $password;
        $this->newPasswordConfirmation = $password;
    }

    public function resetUserPassword(): void
    {
        $this->authorize('manage', User::class);

        $this->validate([
            'newPassword' => ['required', 'string', 'min:8', 'same:newPasswordConfirmation'],
        ], [
            'newPassword.same' => __('The passwords do not match.'),
            'newPassword.min' => __('The password must be at least 8 characters.'),
        ]);

        $user = User::query()->find($this->resetPasswordUserId);

        if ($user === null) {
            return;
        }

        $user->update([
            'password' => Hash::make($this->newPassword),
        ]);

        $this->closePasswordResetModal();

        session()->flash('success', __('Password for :name has been reset successfully.', ['name' => $user->name]));
    }

    #[Layout('layouts.app')]
    public function render(): View
    {
        return view('livewire.account.users-index', [
            'users' => $this->getUsers(),
            'pendingCount' => $this->getPendingCount(),
        ]);
    }

    private function getUsers()
    {
        return User::query()
            ->when($this->search, function ($query): void {
                $search = '%' . $this->search . '%';

                $query->where(function ($subQuery) use ($search): void {
                    $subQuery
                        ->where('name', 'like', $search)
                        ->orWhere('email', 'like', $search);
                });
            })
            ->when($this->role, function ($query): void {
                $query->where('role', $this->role);
            })
            ->when($this->status, function ($query): void {
                $query->where('status', $this->status);
            })
            ->orderByRaw('CASE WHEN status = ? THEN 0 ELSE 1 END', [User::STATUS_PENDING])
            ->orderBy($this->sortField, $this->sortDirection)
            ->orderBy('name')
            ->paginate(15);
    }
}
