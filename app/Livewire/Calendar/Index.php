<?php

declare(strict_types=1);

namespace App\Livewire\Calendar;

use App\Models\MaintenanceRecord;
use App\Models\TripTicket;
use App\Models\Vehicle;
use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

class Index extends Component
{
    public string $currentMonth;
    public string $view = 'calendar'; // 'calendar' or 'list'

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

    public function goToToday(): void
    {
        $this->currentMonth = now()->startOfMonth()->toDateString();
    }

    public function setView(string $view): void
    {
        $this->view = $view;
    }

    #[Layout('layouts.app')]
    public function render(): View
    {
        $month = Carbon::parse($this->currentMonth);

        $start = $month->copy()->startOfMonth();
        $end = $month->copy()->endOfMonth();

        // Get maintenance records
        $maintenanceRecords = MaintenanceRecord::with('vehicle')
            ->where(function ($query) use ($start, $end): void {
                $query->whereBetween('performed_at', [$start, $end])
                    ->orWhereBetween('next_maintenance_due_at', [$start, $end]);
            })
            ->orderBy('performed_at')
            ->get();

        // Get trip tickets
        $tripTickets = TripTicket::with('vehicle')
            ->where(function ($query) use ($start, $end): void {
                $query->whereBetween('departure_at', [$start, $end])
                    ->orWhereBetween('return_at', [$start, $end]);
            })
            ->orderBy('departure_at')
            ->get();

        // Get vehicles with upcoming maintenance due
        $vehiclesDue = Vehicle::whereNotNull('next_maintenance_due_at')
            ->whereBetween('next_maintenance_due_at', [$start, $end])
            ->orderBy('next_maintenance_due_at')
            ->get();

        // Build calendar grid
        $calendarStart = $start->copy()->startOfWeek(CarbonInterface::SUNDAY);
        $calendarEnd = $end->copy()->endOfWeek(CarbonInterface::SATURDAY);

        $days = [];

        for ($date = $calendarStart->copy(); $date <= $calendarEnd; $date->addDay()) {
            $dateKey = $date->toDateString();
            $isCurrentMonth = $date->month === $month->month;

            $days[$dateKey] = [
                'date' => $date->copy(),
                'isCurrentMonth' => $isCurrentMonth,
                'isToday' => $date->isToday(),
                'performed' => $maintenanceRecords->filter(fn (MaintenanceRecord $record): bool => optional($record->performed_at)?->toDateString() === $dateKey),
                'due' => $maintenanceRecords->filter(fn (MaintenanceRecord $record): bool => optional($record->next_maintenance_due_at)?->toDateString() === $dateKey),
                'vehiclesDue' => $vehiclesDue->filter(fn (Vehicle $vehicle): bool => optional($vehicle->next_maintenance_due_at)?->toDateString() === $dateKey),
                'trips' => $tripTickets->filter(fn (TripTicket $ticket): bool => optional($ticket->departure_at)?->toDateString() === $dateKey),
            ];
        }

        return view('livewire.calendar.index', [
            'month' => $month,
            'days' => $days,
            'maintenanceRecords' => $maintenanceRecords,
            'tripTickets' => $tripTickets,
            'vehiclesDue' => $vehiclesDue,
        ]);
    }
}
