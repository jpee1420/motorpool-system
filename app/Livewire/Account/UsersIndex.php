<?php

declare(strict_types=1);

namespace App\Livewire\Account;

use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
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

    protected function queryString(): array
    {
        return [
            'search' => ['except' => null],
            'role' => ['except' => null],
            'status' => ['except' => null],
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

    #[Layout('layouts.app')]
    public function render(): View
    {
        return view('livewire.account.users-index', [
            'users' => $this->getUsers(),
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
            ->orderBy('name')
            ->paginate(15);
    }
}
