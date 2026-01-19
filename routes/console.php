<?php

use App\Services\MaintenanceNotificationService;
use Illuminate\Foundation\Inspiring;
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
