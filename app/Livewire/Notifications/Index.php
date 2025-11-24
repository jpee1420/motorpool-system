<?php

declare(strict_types=1);

namespace App\Livewire\Notifications;

use App\Models\NotificationLog;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public ?string $status = null;
    public ?string $channel = null;
    public ?string $type = null;
    public ?string $search = null;
    public ?string $fromDate = null;
    public ?string $toDate = null;

    protected function queryString(): array
    {
        return [
            'status' => ['except' => null],
            'channel' => ['except' => null],
            'type' => ['except' => null],
            'search' => ['except' => null],
            'fromDate' => ['except' => null],
            'toDate' => ['except' => null],
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

    #[Layout('layouts.app')]
    public function render(): View
    {
        return view('livewire.notifications.index', [
            'logs' => $this->getLogs(),
        ]);
    }

    private function getLogs(): LengthAwarePaginator
    {
        return NotificationLog::with('vehicle')
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
}
