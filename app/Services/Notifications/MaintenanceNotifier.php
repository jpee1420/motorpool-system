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

        // Primary recipient (e.g., driver or staff)
        if (! empty($log->recipient_contact)) {
            Notification::route('mail', $log->recipient_contact)
                ->notify(new MaintenanceDueNotification($vehicle, $maintenanceRecord));
        }

        // Always send a copy to the admin (Mailpit in local)
        $adminEmail = (string) config('mail.from.address', '');

        if ($adminEmail !== '' && $adminEmail !== $log->recipient_contact) {
            Notification::route('mail', $adminEmail)
                ->notify(new MaintenanceDueNotification($vehicle, $maintenanceRecord));
        }
    }
}
