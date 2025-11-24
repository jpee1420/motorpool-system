<?php

declare(strict_types=1);

namespace App\Livewire\Calendar;

use App\Models\MaintenanceRecord;
use Illuminate\Support\Carbon;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

class Index extends Component
{
    public string $currentMonth;

    public function mount(): void
    {
        $this->currentMonth = now()->startOfMonth()->toDateString();
    }

    public function goToPreviousMonth(): void
    {
        $this->currentMonth = Carbon::parse($this->currentMonth)
            ->subMonthNoOverflow()
            ->startOfMonth()
            ->toDateString();
    }

    public function goToNextMonth(): void
    {
        $this->currentMonth = Carbon::parse($this->currentMonth)
            ->addMonthNoOverflow()
            ->startOfMonth()
            ->toDateString();
    }

    #[Layout('layouts.app')]
    public function render(): View
    {
        $month = Carbon::parse($this->currentMonth);

        $start = $month->copy()->startOfMonth();
        $end = $month->copy()->endOfMonth();

        $records = MaintenanceRecord::with('vehicle')
            ->whereBetween('performed_at', [$start, $end])
            ->orWhereBetween('next_maintenance_due_at', [$start, $end])
            ->orderBy('performed_at')
            ->get();

        $days = [];

        for ($date = $start->copy(); $date <= $end; $date->addDay()) {
            $dateKey = $date->toDateString();

            $days[$dateKey] = [
                'date' => $date->copy(),
                'performed' => $records->filter(fn (MaintenanceRecord $record): bool => optional($record->performed_at)?->toDateString() === $dateKey),
                'due' => $records->filter(fn (MaintenanceRecord $record): bool => optional($record->next_maintenance_due_at)?->toDateString() === $dateKey),
            ];
        }

        return view('livewire.calendar.index', [
            'month' => $month,
            'days' => $days,
        ]);
    }
}
