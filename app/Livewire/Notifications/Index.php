<?php

declare(strict_types=1);

namespace App\Livewire\Notifications;

use App\Exports\NotificationLogsExport;
use App\Jobs\SendMaintenanceNotificationJob;
use App\Models\NotificationLog;
use App\Services\MaintenanceNotificationService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class Index extends Component
{
    use WithPagination;

    public ?string $status = null;
    public ?string $channel = null;
    public ?string $type = null;
    public ?string $search = null;
    public ?string $fromDate = null;
    public ?string $toDate = null;

    /**
     * Archived filter: active, archived, or all.
     */
    public string $archived = 'active';

    /**
     * Selected log IDs for bulk actions.
     *
     * @var array<int>
     */
    public array $selected = [];

    protected function queryString(): array
    {
        return [
            'status' => ['except' => null],
            'channel' => ['except' => null],
            'type' => ['except' => null],
            'search' => ['except' => null],
            'fromDate' => ['except' => null],
            'toDate' => ['except' => null],
            'archived' => ['except' => 'active'],
            'page' => ['except' => 1],
        ];
    }

    public function updatingStatus(): void
    {
        $this->resetPage();
    }

    public function updatingChannel(): void
    {
        $this->resetPage();
    }

    public function updatingType(): void
    {
        $this->resetPage();
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingFromDate(): void
    {
        $this->resetPage();
    }

    public function updatingToDate(): void
    {
        $this->resetPage();
    }

    public function updatingArchived(): void
    {
        $this->resetPage();
    }

    #[Layout('layouts.app')]
    public function render(): View
    {
        return view('livewire.notifications.index', [
            'logs' => $this->getLogs(),
            'statusCounts' => $this->getStatusCounts(),
            'metrics' => $this->getMetrics(),
        ]);
    }

    /**
     * Quick filter: set status.
     */
    public function filterStatus(?string $status): void
    {
        $this->status = $status;
        $this->resetPage();
    }

    /**
     * Quick filter: set to today's date.
     */
    public function filterToday(): void
    {
        $this->fromDate = now()->toDateString();
        $this->toDate = now()->toDateString();
        $this->resetPage();
    }

    /**
     * Clear all filters.
     */
    public function clearFilters(): void
    {
        $this->reset(['status', 'channel', 'type', 'search', 'fromDate', 'toDate']);
        $this->resetPage();
    }

    public function runMaintenanceCheck(MaintenanceNotificationService $service): void
    {
        $result = $service->run();

        session()->flash('success', __(
            'Maintenance check completed. Vehicles checked: :vehicles, notifications created: :created, duplicates skipped: :skipped',
            [
                'vehicles' => $result['vehicles_checked'],
                'created' => $result['created_logs'],
                'skipped' => $result['skipped_duplicates'],
            ],
        ));
    }

    /**
     * Export notifications as CSV with current filters applied.
     */
    public function exportCsv(): StreamedResponse
    {
        $logs = $this->buildBaseQuery()
            ->when($this->status, function ($query): void {
                $query->where('status', $this->status);
            })
            ->when($this->channel, function ($query): void {
                $query->where('channel', $this->channel);
            })
            ->when($this->type, function ($query): void {
                $query->where('type', $this->type);
            })
            ->when($this->search, function ($query): void {
                $search = '%' . $this->search . '%';

                $query->where(function ($subQuery) use ($search): void {
                    $subQuery
                        ->where('recipient_name', 'like', $search)
                        ->orWhere('recipient_contact', 'like', $search)
                        ->orWhereHas('vehicle', function ($vehicleQuery) use ($search): void {
                            $vehicleQuery->where('plate_number', 'like', $search);
                        });
                });
            })
            ->when($this->fromDate, function ($query): void {
                $query->whereDate('sent_at', '>=', $this->fromDate);
            })
            ->when($this->toDate, function ($query): void {
                $query->whereDate('sent_at', '<=', $this->toDate);
            })
            ->orderByDesc('sent_at')
            ->orderByDesc('id')
            ->get();

        $filename = 'notification_logs_' . now()->format('Y-m-d_His') . '.csv';

        return response()->streamDownload(function () use ($logs): void {
            $handle = fopen('php://output', 'w');

            // CSV headers
            fputcsv($handle, [
                'Created At',
                'Vehicle',
                'Channel',
                'Type',
                'Severity',
                'Trigger',
                'Due Date',
                'Due Odometer',
                'Status',
                'Retry Count',
                'Error',
            ]);

            foreach ($logs as $log) {
                $meta = $log->meta ?? [];

                fputcsv($handle, [
                    $log->created_at?->format('Y-m-d H:i'),
                    $log->vehicle?->plate_number ?? '',
                    $log->channel,
                    $log->type,
                    $log->type_label,
                    $log->trigger_label,
                    $meta['next_maintenance_due_at'] ?? '',
                    $meta['next_maintenance_due_odometer'] ?? '',
                    $log->status,
                    $log->retry_count,
                    $log->error_message,
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    /**
     * Export notifications as Excel with current filters applied.
     */
    public function exportExcel(): BinaryFileResponse
    {
        $logs = $this->buildBaseQuery()
            ->when($this->status, function ($query): void {
                $query->where('status', $this->status);
            })
            ->when($this->channel, function ($query): void {
                $query->where('channel', $this->channel);
            })
            ->when($this->type, function ($query): void {
                $query->where('type', $this->type);
            })
            ->when($this->search, function ($query): void {
                $search = '%' . $this->search . '%';

                $query->where(function ($subQuery) use ($search): void {
                    $subQuery
                        ->where('recipient_name', 'like', $search)
                        ->orWhere('recipient_contact', 'like', $search)
                        ->orWhereHas('vehicle', function ($vehicleQuery) use ($search): void {
                            $vehicleQuery->where('plate_number', 'like', $search);
                        });
                });
            })
            ->when($this->fromDate, function ($query): void {
                $query->whereDate('sent_at', '>=', $this->fromDate);
            })
            ->when($this->toDate, function ($query): void {
                $query->whereDate('sent_at', '<=', $this->toDate);
            })
            ->orderByDesc('sent_at')
            ->orderByDesc('id')
            ->get();

        $filename = 'notification_logs_' . now()->format('Y-m-d_His') . '.xlsx';

        return Excel::download(new NotificationLogsExport($logs), $filename);
    }

    public function retry(int $id): void
    {
        $log = NotificationLog::find($id);

        if ($log === null) {
            session()->flash('error', __('Notification log not found.'));
            return;
        }

        if (! $log->canRetry()) {
            if ($log->status === NotificationLog::STATUS_SENT) {
                session()->flash('error', __('This notification has already been sent.'));
            } elseif ($log->max_retries_reached) {
                session()->flash('error', __('Maximum retry attempts reached for this notification.'));
            }
            return;
        }

        // Increment retry count
        $log->incrementRetry();

        $log->update([
            'status' => NotificationLog::STATUS_PENDING,
            'error_message' => null,
        ]);

        // Only dispatch job for email channel
        if ($log->channel === NotificationLog::CHANNEL_EMAIL) {
            SendMaintenanceNotificationJob::dispatch($log->id);
        } else {
            // For in_app, just mark as sent (it's visible in the UI)
            $log->update([
                'status' => NotificationLog::STATUS_SENT,
                'sent_at' => now(),
            ]);
        }

        session()->flash('success', __('Notification has been queued for retry.'));
    }

    /**
     * Mark an in-app notification as read.
     */
    public function markAsRead(int $id): void
    {
        $log = NotificationLog::find($id);

        if ($log === null) {
            return;
        }

        $log->markAsRead();
    }

    /**
     * Bulk retry selected notifications.
     */
    public function bulkRetry(): void
    {
        if ($this->selected === []) {
            return;
        }

        $logs = NotificationLog::whereIn('id', $this->selected)->get();

        $retried = 0;

        foreach ($logs as $log) {
            if (! $log->canRetry()) {
                continue;
            }

            $log->incrementRetry();

            $log->update([
                'status' => NotificationLog::STATUS_PENDING,
                'error_message' => null,
            ]);

            if ($log->channel === NotificationLog::CHANNEL_EMAIL) {
                SendMaintenanceNotificationJob::dispatch($log->id);
            } else {
                $log->update([
                    'status' => NotificationLog::STATUS_SENT,
                    'sent_at' => now(),
                ]);
            }

            $retried++;
        }

        $this->selected = [];

        if ($retried > 0) {
            session()->flash('success', __(':count notifications queued for retry.', ['count' => $retried]));
        }
    }

    /**
     * Bulk archive selected notifications.
     */
    public function bulkArchive(): void
    {
        if ($this->selected === []) {
            return;
        }

        $count = NotificationLog::whereIn('id', $this->selected)
            ->whereNull('archived_at')
            ->update(['archived_at' => now()]);

        $this->selected = [];
        $this->resetPage();

        if ($count > 0) {
            session()->flash('success', __(':count notifications archived.', ['count' => $count]));
        }
    }

    private function getLogs(): LengthAwarePaginator
    {
        return $this->buildBaseQuery()
            ->when($this->status, function ($query): void {
                $query->where('status', $this->status);
            })
            ->when($this->channel, function ($query): void {
                $query->where('channel', $this->channel);
            })
            ->when($this->type, function ($query): void {
                $query->where('type', $this->type);
            })
            ->when($this->search, function ($query): void {
                $search = '%' . $this->search . '%';

                $query->where(function ($subQuery) use ($search): void {
                    $subQuery
                        ->where('recipient_name', 'like', $search)
                        ->orWhere('recipient_contact', 'like', $search)
                        ->orWhereHas('vehicle', function ($vehicleQuery) use ($search): void {
                            $vehicleQuery->where('plate_number', 'like', $search);
                        });
                });
            })
            ->when($this->fromDate, function ($query): void {
                $query->whereDate('sent_at', '>=', $this->fromDate);
            })
            ->when($this->toDate, function ($query): void {
                $query->whereDate('sent_at', '<=', $this->toDate);
            })
            ->orderByDesc('sent_at')
            ->orderByDesc('id')
            ->paginate(15);
    }

    /**
     * Get counts for quick filter badges.
     */
    private function getStatusCounts(): array
    {
        return [
            'pending' => NotificationLog::whereNull('archived_at')->where('status', NotificationLog::STATUS_PENDING)->count(),
            'sent' => NotificationLog::whereNull('archived_at')->where('status', NotificationLog::STATUS_SENT)->count(),
            'failed' => NotificationLog::whereNull('archived_at')->where('status', NotificationLog::STATUS_FAILED)->count(),
        ];
    }

    /**
     * Base query with relations and archived filter.
     */
    private function buildBaseQuery()
    {
        return NotificationLog::with(['vehicle', 'maintenanceRecord'])
            ->when($this->archived === 'active', function ($query): void {
                $query->whereNull('archived_at');
            })
            ->when($this->archived === 'archived', function ($query): void {
                $query->whereNotNull('archived_at');
            });
    }

    /**
     * Simple KPI metrics for the notifications page.
     *
     * @return array{overdue_last_30:int,avg_days_late_last_30:?float}
     */
    private function getMetrics(): array
    {
        $from = now()->subDays(30);

        $overdueQuery = NotificationLog::whereNull('archived_at')
            ->where('type', NotificationLog::TYPE_MAINTENANCE_OVERDUE)
            ->where('created_at', '>=', $from);

        $overdueCount = (clone $overdueQuery)->count();

        $resolvedLogs = (clone $overdueQuery)
            ->whereNotNull('maintenance_record_id')
            ->get();

        $totalDaysLate = 0;
        $resolvedCount = 0;

        foreach ($resolvedLogs as $log) {
            $daysLate = $log->days_late;

            if ($daysLate !== null) {
                $totalDaysLate += $daysLate;
                $resolvedCount++;
            }
        }

        $avgDaysLate = $resolvedCount > 0
            ? round($totalDaysLate / $resolvedCount, 1)
            : null;

        return [
            'overdue_last_30' => $overdueCount,
            'avg_days_late_last_30' => $avgDaysLate,
        ];
    }
}
