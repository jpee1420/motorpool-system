<?php

declare(strict_types=1);

namespace App\Livewire\Notifications;

use App\Models\NotificationLog;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Livewire\Attributes\On;
use Livewire\Component;

class Bell extends Component
{
    public bool $showDropdown = false;

    public function render(): View
    {
        $user = auth()->user();

        $unreadCount = 0;
        $recentNotifications = collect();

        if ($user !== null) {
            $unreadCount = NotificationLog::query()
                ->where('user_id', $user->id)
                ->where('channel', NotificationLog::CHANNEL_IN_APP)
                ->whereNull('read_at')
                ->count();

            $recentNotifications = NotificationLog::query()
                ->with('vehicle')
                ->where('user_id', $user->id)
                ->where('channel', NotificationLog::CHANNEL_IN_APP)
                ->orderByRaw('read_at IS NULL DESC')
                ->orderByDesc('created_at')
                ->limit(5)
                ->get();
        }

        return view('livewire.notifications.bell', [
            'unreadCount' => $unreadCount,
            'notifications' => $recentNotifications,
        ]);
    }

    public function toggleDropdown(): void
    {
        $this->showDropdown = ! $this->showDropdown;
    }

    public function closeDropdown(): void
    {
        $this->showDropdown = false;
    }

    public function markAsRead(int $id): void
    {
        $log = NotificationLog::find($id);

        if ($log !== null && $log->user_id === auth()->id()) {
            $log->markAsRead();
        }
    }

    public function markAllAsRead(): void
    {
        $user = auth()->user();

        if ($user === null) {
            return;
        }

        NotificationLog::query()
            ->where('user_id', $user->id)
            ->where('channel', NotificationLog::CHANNEL_IN_APP)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }

    #[On('notification-created')]
    public function refresh(): void
    {
        // This will trigger a re-render when new notifications are created
    }
}
