<?php

use App\Jobs\SendMaintenanceNotificationJob;
use App\Models\MaintenanceRecord;
use App\Models\NotificationLog;
use App\Models\User;
use App\Models\Vehicle;
use App\Services\MaintenanceNotificationService;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('motorpool:check-maintenance', function (MaintenanceNotificationService $service): void {
    $result = $service->run();

    $this->info('motorpool:check-maintenance');
    $this->info('  Vehicles checked: ' . $result['vehicles_checked']);
    $this->info('  Notification logs created: ' . $result['created_logs']);
    $this->info('  Skipped (duplicates within cooldown): ' . $result['skipped_duplicates']);
})->purpose('Check vehicles due for maintenance and log notifications with severity levels');

Artisan::command('motorpool:send-maintenance-notifications', function () {
    $dispatched = 0;

    // Only dispatch jobs for email channel (in_app is immediately visible)
    NotificationLog::query()
        ->where('status', NotificationLog::STATUS_PENDING)
        ->where('channel', NotificationLog::CHANNEL_EMAIL)
        ->orderBy('id')
        ->chunkById(50, function ($logs) use (&$dispatched): void {
            foreach ($logs as $log) {
                SendMaintenanceNotificationJob::dispatch($log->id);
                $dispatched++;
            }
        });

    $this->info('motorpool:send-maintenance-notifications');
    $this->info('  Email jobs dispatched: ' . $dispatched);
})->purpose('Dispatch jobs to send pending email maintenance notifications');
