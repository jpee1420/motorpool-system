<?php

declare(strict_types=1);

namespace App\Livewire\ActivityLogs;

use App\Models\ActivityLog;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use AuthorizesRequests;
    use WithPagination;

    public ?string $search = null;
    public ?string $action = null;
    public ?string $subjectType = null;
    public ?string $fromDate = null;
    public ?string $toDate = null;

    public bool $showDetailsModal = false;
    public ?int $detailsLogId = null;

    protected function queryString(): array
    {
        return [
            'search' => ['except' => null],
            'action' => ['except' => null],
            'subjectType' => ['except' => null],
            'fromDate' => ['except' => null],
            'toDate' => ['except' => null],
            'page' => ['except' => 1],
        ];
    }

    public function mount(): void
    {
        $this->authorize('viewAny', ActivityLog::class);
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingAction(): void
    {
        $this->resetPage();
    }

    public function updatingSubjectType(): void
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

    public function clearFilters(): void
    {
        $this->reset(['search', 'action', 'subjectType', 'fromDate', 'toDate']);
        $this->resetPage();
    }

    public function openDetails(int $logId): void
    {
        $this->authorize('viewAny', ActivityLog::class);

        $this->detailsLogId = $logId;
        $this->showDetailsModal = true;
    }

    public function closeDetails(): void
    {
        $this->showDetailsModal = false;
        $this->detailsLogId = null;
    }

    #[Layout('layouts.app')]
    public function render(): View
    {
        return view('livewire.activity-logs.index', [
            'logs' => $this->getLogs(),
            'subjectTypes' => $this->getSubjectTypes(),
            'selectedLog' => $this->getSelectedLog(),
        ]);
    }

    private function getSelectedLog(): ?ActivityLog
    {
        if (! $this->showDetailsModal || $this->detailsLogId === null) {
            return null;
        }

        return ActivityLog::query()
            ->with('actor')
            ->whereKey($this->detailsLogId)
            ->first();
    }

    private function getLogs(): LengthAwarePaginator
    {
        return ActivityLog::query()
            ->with('actor')
            ->when($this->action, function ($query): void {
                $query->where('action', $this->action);
            })
            ->when($this->subjectType, function ($query): void {
                $query->where('subject_type', $this->subjectType);
            })
            ->when($this->search, function ($query): void {
                $search = '%' . $this->search . '%';

                $query->where(function ($subQuery) use ($search): void {
                    $subQuery
                        ->where('subject_label', 'like', $search)
                        ->orWhere('subject_type', 'like', $search)
                        ->orWhereHas('actor', function ($actorQuery) use ($search): void {
                            $actorQuery
                                ->where('name', 'like', $search)
                                ->orWhere('email', 'like', $search);
                        });
                });
            })
            ->when($this->fromDate, function ($query): void {
                $query->whereDate('created_at', '>=', $this->fromDate);
            })
            ->when($this->toDate, function ($query): void {
                $query->whereDate('created_at', '<=', $this->toDate);
            })
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate(20);
    }

    private function getSubjectTypes(): array
    {
        return ActivityLog::query()
            ->select('subject_type')
            ->distinct()
            ->orderBy('subject_type')
            ->pluck('subject_type')
            ->all();
    }
}
