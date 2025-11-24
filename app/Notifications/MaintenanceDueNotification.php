<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\MaintenanceRecord;
use App\Models\Vehicle;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Carbon;

class MaintenanceDueNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public ?Vehicle $vehicle, public ?MaintenanceRecord $maintenanceRecord)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $vehicle = $this->vehicle;
        $maintenanceRecord = $this->maintenanceRecord;

        $plate = $vehicle?->plate_number ?? 'Unknown vehicle';

        $mailMessage = new MailMessage();

        $mailMessage->subject('Vehicle maintenance due');
        $mailMessage->greeting('Maintenance Reminder');
        $mailMessage->line('Vehicle: '.$plate);

        if ($vehicle !== null) {
            // if ($vehicle->next_maintenance_due_at !== null) {
            //     $mailMessage->line('Due by date: '.$vehicle->next_maintenance_due_at->format('M d, Y'));
            // }
            if ($vehicle->next_maintenance_due_at instanceof \DateTime) {
                $mailMessage->line(
                'Due by date: ' . $vehicle->next_maintenance_due_at->format('M d, Y')
                );
            }
            if ($vehicle->next_maintenance_due_odometer !== null) {
                $mailMessage->line('Due at odometer: '.$vehicle->next_maintenance_due_odometer.' km');
            }

            if ($vehicle->current_odometer !== null) {
                $mailMessage->line('Current odometer: '.$vehicle->current_odometer.' km');
            }
        }

        if ($maintenanceRecord !== null) {
            if ($maintenanceRecord->performed_at !== null) {
                $mailMessage->line('Last maintenance at: '.$maintenanceRecord->performed_at->toDateTimeString());
            }

            if ($maintenanceRecord->description_of_work !== null) {
                $mailMessage->line('Work done: '.$maintenanceRecord->description_of_work);
            }
        }

        $mailMessage->line('Please schedule the maintenance as soon as possible.');

        return $mailMessage;
    }

    public function toArray(object $notifiable): array
    {
        return [];
    }
}
