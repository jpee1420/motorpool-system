<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PasswordResetRequestNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public User $requestingUser
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject(__('Password Reset Request'))
            ->greeting(__('Hello :name,', ['name' => $notifiable->name]))
            ->line(__('A user has requested a password reset.'))
            ->line(__('**User Details:**'))
            ->line(__('Name: :name', ['name' => $this->requestingUser->name]))
            ->line(__('Email: :email', ['email' => $this->requestingUser->email]))
            ->action(__('Go to User Management'), route('account.users'))
            ->line(__('Please reset the password for this user and contact them with the new password.'));
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'password_reset_request',
            'user_id' => $this->requestingUser->id,
            'user_name' => $this->requestingUser->name,
            'user_email' => $this->requestingUser->email,
            'message' => __(':name (:email) has requested a password reset.', [
                'name' => $this->requestingUser->name,
                'email' => $this->requestingUser->email,
            ]),
        ];
    }
}
