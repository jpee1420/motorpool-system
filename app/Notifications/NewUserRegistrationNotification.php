<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewUserRegistrationNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public User $newUser
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('New User Registration Pending Approval')
            ->greeting('New Registration Request')
            ->line('A new user has registered and is awaiting approval:')
            ->line('**Name:** ' . $this->newUser->name)
            ->line('**Email:** ' . $this->newUser->email)
            ->line('**Registered:** ' . $this->newUser->created_at->format('M d, Y H:i'))
            ->action('Review Pending Users', url(route('users.index')))
            ->line('Please review and approve or reject this registration.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'new_user_registration',
            'user_id' => $this->newUser->id,
            'user_name' => $this->newUser->name,
            'user_email' => $this->newUser->email,
            'message' => "New user registration: {$this->newUser->name} ({$this->newUser->email})",
        ];
    }
}
