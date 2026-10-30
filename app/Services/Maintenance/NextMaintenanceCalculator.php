<?php

declare(strict_types=1);

namespace App\Services\Maintenance;

use App\Models\MaintenanceRecord;
use App\Models\Vehicle;
use Illuminate\Support\Carbon;

class NextMaintenanceCalculator
{
    /**
     * Default interval in months for time-based maintenance.
     */
    protected int $defaultMonthsInterval;

    /**
     * Default interval in kilometers for odometer-based maintenance.
     */
    protected int $defaultKilometersInterval;

    public function __construct()
    {
        $this->defaultMonthsInterval = (int) config('motorpool.maintenance.default_months_interval', 6);
        $this->defaultKilometersInterval = (int) config('motorpool.maintenance.default_kilometers_interval', 5000);
    }

    /**
     * Calculate the next maintenance due date.
     *
     * If a specific date is provided, use it.
     * Otherwise, calculate based on the performed date + default interval.
     */
    public function calculateNextDueDate(
        ?string $providedDate,
        Carbon $performedAt
    ): ?Carbon {
        if ($providedDate !== null && $providedDate !== '') {
            return Carbon::parse($providedDate);
        }

        // Auto-calculate: performed date + default months interval
        return $performedAt->copy()->addMonths($this->defaultMonthsInterval);
    }

    /**
     * Calculate the next maintenance due odometer.
     *
     * If a specific odometer is provided, use it.
     * Otherwise, calculate based on current odometer + default interval.
     */
    public function calculateNextDueOdometer(
        ?int $providedOdometer,
        ?int $currentOdometer
    ): ?int {
        if ($providedOdometer !== null && $providedOdometer > 0) {
            return $providedOdometer;
        }

        if ($currentOdometer === null) {
            return null;
        }

        // Auto-calculate: current odometer + default kilometers interval
        return $currentOdometer + $this->defaultKilometersInterval;
    }

    /**
     * Update a vehicle's maintenance tracking fields after a maintenance record is created.
     */
    public function updateVehicleAfterMaintenance(
        Vehicle $vehicle,
        MaintenanceRecord $record,
        ?string $providedNextDueDate = null,
        ?int $providedNextDueOdometer = null
    ): void {
        $performedAt = $record->performed_at ?? now();

        $nextDueDate = $this->calculateNextDueDate($providedNextDueDate, $performedAt);
        $nextDueOdometer = $this->calculateNextDueOdometer(
            $providedNextDueOdometer,
            $record->odometer_reading
        );

        $vehicle->update([
            'current_odometer' => $record->odometer_reading ?? $vehicle->current_odometer,
            'last_maintenance_at' => $performedAt,
            'last_maintenance_odometer' => $record->odometer_reading,
            'next_maintenance_due_at' => $nextDueDate,
            'next_maintenance_due_odometer' => $nextDueOdometer,
        ]);
    }

    /**
     * Get the default months interval.
     */
    public function getDefaultMonthsInterval(): int
    {
        return $this->defaultMonthsInterval;
    }

    /**
     * Get the default kilometers interval.
     */
    public function getDefaultKilometersInterval(): int
    {
        return $this->defaultKilometersInterval;
    }
}
