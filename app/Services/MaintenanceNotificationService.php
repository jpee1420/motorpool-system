<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\MaintenanceRecord;
use App\Models\NotificationLog;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Support\Carbon;

final class MaintenanceNotificationService
{
    /**
     * Run maintenance check and create notification logs.
     *
     * @return array{vehicles_checked:int,created_logs:int,skipped_duplicates:int}
     */
    public function run(): array
    {
        $today = Carbon::today();
        $daysBeforeDue = (int) config('motorpool.notifications.days_before_due', 7);
        $cooldownHours = (int) config('motorpool.notifications.cooldown_hours', 24);
        $channels = (array) config('motorpool.notifications.channels', ['email', 'in_app']);

        $upcomingDate = $today->copy()->addDays($daysBeforeDue);

        $vehicles = Vehicle::query()
            ->where(function ($query) use ($upcomingDate) {
                $query->whereNotNull('next_maintenance_due_at')
                    ->whereDate('next_maintenance_due_at', '<=', $upcomingDate);
            })
            ->orWhere(function ($query) {
                $query->whereNotNull('next_maintenance_due_odometer')
                    ->whereColumn('current_odometer', '>=', 'next_maintenance_due_odometer');
            })
            ->get();

        $createdLogs = 0;
        $skippedDuplicates = 0;

        $notifiableUsers = User::query()
            ->where('status', User::STATUS_ACTIVE)
            ->where(function ($query) {
                $query->where('role', User::ROLE_ADMIN)
                    ->orWhere('role', User::ROLE_STAFF);
            })
            ->get();

        foreach ($vehicles as $vehicle) {
            $dateDue = $vehicle->next_maintenance_due_at !== null
                && $vehicle->next_maintenance_due_at->lte($upcomingDate);
            $odometerDue = $vehicle->next_maintenance_due_odometer !== null
                && $vehicle->current_odometer >= $vehicle->next_maintenance_due_odometer;

            $triggerReason = match (true) {
                $dateDue && $odometerDue => NotificationLog::TRIGGER_BOTH,
                $dateDue => NotificationLog::TRIGGER_DATE_DUE,
                $odometerDue => NotificationLog::TRIGGER_ODOMETER_DUE,
                default => null,
            };

            if ($triggerReason === null) {
                continue;
            }

            $type = NotificationLog::TYPE_MAINTENANCE_UPCOMING;

            if ($vehicle->next_maintenance_due_at !== null) {
                if ($vehicle->next_maintenance_due_at->lt($today)) {
                    $type = NotificationLog::TYPE_MAINTENANCE_OVERDUE;
                } elseif ($vehicle->next_maintenance_due_at->eq($today)) {
                    $type = NotificationLog::TYPE_MAINTENANCE_DUE;
                }
            }

            if ($odometerDue && $type === NotificationLog::TYPE_MAINTENANCE_UPCOMING) {
                $type = NotificationLog::TYPE_MAINTENANCE_DUE;
            }

            $meta = [
                'next_maintenance_due_at' => $vehicle->next_maintenance_due_at?->toDateString(),
                'next_maintenance_due_odometer' => $vehicle->next_maintenance_due_odometer,
                'current_odometer' => $vehicle->current_odometer,
                'checked_at' => now()->toDateTimeString(),
            ];

            $latestRecord = MaintenanceRecord::where('vehicle_id', $vehicle->id)
                ->latest('performed_at')
                ->first();

            foreach ($channels as $channel) {
                $existingLog = NotificationLog::query()
                    ->where('vehicle_id', $vehicle->id)
                    ->where('channel', $channel)
                    ->where('type', $type)
                    ->where('created_at', '>=', now()->subHours($cooldownHours))
                    ->whereIn('status', [NotificationLog::STATUS_PENDING, NotificationLog::STATUS_SENT])
                    ->exists();

                if ($existingLog) {
                    $skippedDuplicates++;
                    continue;
                }

                $logData = [
                    'vehicle_id' => $vehicle->id,
                    'maintenance_record_id' => $latestRecord?->id,
                    'channel' => $channel,
                    'type' => $type,
                    'trigger_reason' => $triggerReason,
                    'meta' => $meta,
                    'sent_at' => now(),
                    'status' => NotificationLog::STATUS_PENDING,
                    'error_message' => null,
                    'retry_count' => 0,
                    'max_retries_reached' => false,
                ];

                if ($channel === NotificationLog::CHANNEL_EMAIL) {
                    NotificationLog::create(array_merge($logData, [
                        'recipient_name' => $vehicle->driver_operator ?? '',
                        'recipient_contact' => $vehicle->contact_number ?? '',
                    ]));
                    $createdLogs++;
                } elseif ($channel === NotificationLog::CHANNEL_IN_APP) {
                    foreach ($notifiableUsers as $user) {
                        NotificationLog::create(array_merge($logData, [
                            'user_id' => $user->id,
                            'recipient_name' => $user->name,
                            'recipient_contact' => null,
                            'status' => NotificationLog::STATUS_SENT,
                        ]));
                        $createdLogs++;
                    }
                }
            }
        }

        return [
            'vehicles_checked' => $vehicles->count(),
            'created_logs' => $createdLogs,
            'skipped_duplicates' => $skippedDuplicates,
        ];
    }
}
