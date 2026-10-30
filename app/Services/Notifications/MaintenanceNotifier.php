<?php

declare(strict_types=1);

namespace App\Services\Notifications;

use App\Models\NotificationLog;
use App\Notifications\MaintenanceDueNotification;
use Illuminate\Support\Facades\Notification;

class MaintenanceNotifier
{
    public function send(NotificationLog $log): void
    {
        $vehicle = $log->vehicle;
        $maintenanceRecord = $log->maintenanceRecord;

        // Determine which mailer to use from config
        $mailer = $this->resolveMailer();

        // Primary recipient (e.g., driver or staff)
        if (! empty($log->recipient_contact)) {
            Notification::route('mail', $log->recipient_contact)
                ->notify(new MaintenanceDueNotification($vehicle, $maintenanceRecord, $mailer));
        }

        // Always send a copy to the admin
        $adminEmail = $this->resolveAdminEmail($mailer);

        if ($adminEmail !== '' && $adminEmail !== $log->recipient_contact) {
            Notification::route('mail', $adminEmail)
                ->notify(new MaintenanceDueNotification($vehicle, $maintenanceRecord, $mailer));
        }
    }

    /**
     * Resolve which mailer to use from config.
     */
    private function resolveMailer(): ?string
    {
        $mailer = (string) config('motorpool.notifications.mailer', 'default');

        if ($mailer === '' || $mailer === 'default') {
            return null; // Use Laravel's default mailer
        }

        return $mailer;
    }

    /**
     * Get admin email, preferring the mailer-specific from address if configured.
     */
    private function resolveAdminEmail(?string $mailer): string
    {
        if ($mailer !== null) {
            // Check if mailer has its own from address configured
            $mailerFrom = config("mail.mailers.{$mailer}.from.address");

            if ($mailerFrom !== null && $mailerFrom !== '') {
                return (string) $mailerFrom;
            }
        }

        return (string) config('mail.from.address', '');
    }
}
