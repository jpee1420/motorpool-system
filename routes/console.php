<?php

use App\Jobs\SendMaintenanceNotificationJob;
use App\Models\MaintenanceRecord;
use App\Models\NotificationLog;
use App\Models\Vehicle;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('motorpool:check-maintenance', function () {
    $today = Carbon::today();

    $vehicles = Vehicle::query()
        ->where(function ($query) use ($today) {
            $query->whereDate('next_maintenance_due_at', '<=', $today)
                ->orWhere(function ($sub) {
                    $sub->whereNotNull('next_maintenance_due_odometer')
                        ->whereColumn('current_odometer', '>=', 'next_maintenance_due_odometer');
                });
        })
        ->get();

    $createdLogs = 0;

    foreach ($vehicles as $vehicle) {
        $latestRecord = MaintenanceRecord::where('vehicle_id', $vehicle->id)
            ->latest('performed_at')
            ->first();

        NotificationLog::create([
            'vehicle_id' => $vehicle->id,
            'maintenance_record_id' => $latestRecord?->id,
            'channel' => 'email',
            'type' => 'maintenance_due',
            'recipient_name' => $vehicle->driver_operator ?? '',
            'recipient_contact' => $vehicle->contact_number ?? '',
            'sent_at' => now(),
            'status' => 'pending',
            'error_message' => null,
        ]);

        $createdLogs++;
    }
 
    $this->info('motorpool:check-maintenance');
    $this->info('  Vehicles due for maintenance: '.$vehicles->count());
    $this->info('  Notification logs created: '.$createdLogs);
})->purpose('Check vehicles due for maintenance and log notifications');

Artisan::command('motorpool:send-maintenance-notifications', function () {
    $dispatched = 0;

    NotificationLog::query()
        ->where('status', 'pending')
        ->orderBy('id')
        ->chunkById(50, function ($logs) use (&$dispatched): void {
            foreach ($logs as $log) {
                SendMaintenanceNotificationJob::dispatch($log->id);
                $dispatched++;
            }
        });

    $this->info('motorpool:send-maintenance-notifications');
    $this->info('  Jobs dispatched: '.$dispatched);
})->purpose('Dispatch jobs to send pending maintenance notifications');
