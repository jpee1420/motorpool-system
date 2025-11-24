<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\NotificationLog;
use App\Services\Notifications\MaintenanceNotifier;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

class SendMaintenanceNotificationJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(public int $notificationLogId)
    {
    }

    public function handle(MaintenanceNotifier $notifier): void
    {
        $log = NotificationLog::query()->find($this->notificationLogId);

        if ($log === null || $log->status !== 'pending') {
            return;
        }

        try {
            $notifier->send($log);

            $log->update([
                'status' => 'sent',
                'sent_at' => now(),
                'error_message' => null,
            ]);
        } catch (Throwable $exception) {
            $log->update([
                'status' => 'failed',
                'sent_at' => now(),
                'error_message' => $exception->getMessage(),
            ]);

            throw $exception;
        }
    }
}
