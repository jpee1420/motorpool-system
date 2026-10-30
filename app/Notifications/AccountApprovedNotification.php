<?php

declare(strict_types=1);

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AccountApprovedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct()
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Your Account Has Been Approved')
            ->greeting('Welcome to Motorpool!')
            ->line('Great news! Your account has been approved by an administrator.')
            ->line('You can now log in and start using the system.')
            ->action('Log In Now', url(route('login')))
            ->line('Thank you for your patience!');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'account_approved',
            'message' => 'Your account has been approved.',
        ];
    }
}
