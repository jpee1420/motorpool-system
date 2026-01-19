<?php

declare(strict_types=1);

namespace App\Livewire\Notifications;

use App\Exports\NotificationLogsExport;
use App\Models\NotificationLog;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
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

    public ?string $read = null;
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
            'read' => ['except' => null],
            'type' => ['except' => null],
            'search' => ['except' => null],
            'fromDate' => ['except' => null],
            'toDate' => ['except' => null],
            'archived' => ['except' => 'active'],
            'page' => ['except' => 1],
        ];
    }

    public function updatingRead(): void
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
            'readCounts' => $this->getReadCounts(),
            'metrics' => $this->getMetrics(),
        ]);
    }

    /**
     * Quick filter: set read status.
     */
    public function filterRead(?string $read): void
    {
        $this->read = $read;
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
        $this->reset(['read', 'type', 'search', 'fromDate', 'toDate']);
        $this->resetPage();
    }

    /**
     * Export notifications as CSV with current filters applied.
     */
    public function exportCsv(): StreamedResponse
    {
        $logs = $this->buildBaseQuery()
            ->when($this->type, function ($query): void {
                $query->where('type', $this->type);
            })
            ->when($this->read, function ($query): void {
                if ($this->read === 'unread') {
                    $query->whereNull('read_at');
                }

                if ($this->read === 'read') {
                    $query->whereNotNull('read_at');
                }
            })
            ->when($this->search, function ($query): void {
                $search = '%' . $this->search . '%';

                $query->where(function ($subQuery) use ($search): void {
                    $subQuery
                        ->where('recipient_name', 'like', $search)
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
            ->when($this->type, function ($query): void {
                $query->where('type', $this->type);
            })
            ->when($this->read, function ($query): void {
                if ($this->read === 'unread') {
                    $query->whereNull('read_at');
                }

                if ($this->read === 'read') {
                    $query->whereNotNull('read_at');
                }
            })
            ->when($this->search, function ($query): void {
                $search = '%' . $this->search . '%';

                $query->where(function ($subQuery) use ($search): void {
                    $subQuery
                        ->where('recipient_name', 'like', $search)
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

    public function markAsRead(int $id): void
    {
        $log = NotificationLog::find($id);

        if ($log === null) {
            return;
        }

        if (! (auth()->user()?->isAdmin() ?? false) && $log->user_id !== auth()->id()) {
            return;
        }

        $log->markAsRead();
    }

    /**
     * Bulk archive selected notifications.
     */
    public function bulkArchive(): void
    {
        if ($this->selected === []) {
            return;
        }

        $query = NotificationLog::whereIn('id', $this->selected)
            ->whereNull('archived_at')
            ->where('channel', NotificationLog::CHANNEL_IN_APP);

        if (! (auth()->user()?->isAdmin() ?? false)) {
            $query->where('user_id', auth()->id());
        }

        $count = $query->update(['archived_at' => now()]);

        $this->selected = [];
        $this->resetPage();

        if ($count > 0) {
            session()->flash('success', __(':count notifications archived.', ['count' => $count]));
        }
    }

    private function getLogs(): LengthAwarePaginator
    {
        return $this->buildBaseQuery()
            ->when($this->type, function ($query): void {
                $query->where('type', $this->type);
            })
            ->when($this->read, function ($query): void {
                if ($this->read === 'unread') {
                    $query->whereNull('read_at');
                }

                if ($this->read === 'read') {
                    $query->whereNotNull('read_at');
                }
            })
            ->when($this->search, function ($query): void {
                $search = '%' . $this->search . '%';

                $query->where(function ($subQuery) use ($search): void {
                    $subQuery
                        ->where('recipient_name', 'like', $search)
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
     * Get read/unread counts for quick filter badges.
     */
    private function getReadCounts(): array
    {
        $base = $this->baseQuery();

        return [
            'unread' => (clone $base)->whereNull('archived_at')->whereNull('read_at')->count(),
            'read' => (clone $base)->whereNull('archived_at')->whereNotNull('read_at')->count(),
        ];
    }

    /**
     * Base query with relations and access scope.
     */
    private function baseQuery(): Builder
    {
        $query = NotificationLog::query()
            ->with(['vehicle', 'maintenanceRecord', 'user'])
            ->where('channel', NotificationLog::CHANNEL_IN_APP);

        if (! (auth()->user()?->isAdmin() ?? false)) {
            $query->where('user_id', auth()->id());
        }

        return $query;
    }

    /**
     * Base query with relations and archived filter.
     */
    private function buildBaseQuery(): Builder
    {
        return $this->baseQuery()
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

        $overdueQuery = $this->baseQuery()
            ->whereNull('archived_at')
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
