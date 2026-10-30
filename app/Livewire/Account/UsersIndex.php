<?php

declare(strict_types=1);

namespace App\Livewire\Account;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

class UsersIndex extends Component
{
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
        $user = Auth::user();

        if ($user === null || $user->role !== 'admin') {
            abort(403);
        }
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
        if (! in_array($role, ['admin', 'staff', 'user'], true)) {
            return;
        }

        $currentUser = Auth::user();

        if ($currentUser === null || $currentUser->role !== 'admin') {
            abort(403);
        }

        if ($currentUser->id === $userId && $role !== 'admin') {
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
        $currentUser = Auth::user();

        if ($currentUser === null || $currentUser->role !== 'admin') {
            abort(403);
        }

        if ($currentUser->id === $userId) {
            return;
        }

        $user = User::query()->find($userId);

        if ($user === null) {
            return;
        }

        $user->update([
            'status' => $user->status === 'active' ? 'disabled' : 'active',
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
