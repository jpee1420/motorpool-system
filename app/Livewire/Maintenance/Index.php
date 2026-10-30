<?php

declare(strict_types=1);

namespace App\Livewire\Maintenance;

use App\Models\MaintenanceMaterial;
use App\Models\MaintenanceRecord;
use App\Models\Vehicle;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;
use Symfony\Component\HttpFoundation\StreamedResponse;

class Index extends Component
{
    use WithPagination;

    public ?int $vehicleFilter = null;

    public bool $showModal = false;

    public ?int $vehicle_id = null;
    public ?string $performed_at = null;
    public ?int $odometer_reading = null;
    public string $description_of_work = '';
    public float $personnel_labor_cost = 0.0;

    public ?string $next_maintenance_due_at = null;
    public ?int $next_maintenance_due_odometer = null;

    public array $materials = [];

    protected function rules(): array
    {
        return [
            'vehicle_id' => ['required', 'integer', 'exists:vehicles,id'],
            'performed_at' => ['required', 'date'],
            'odometer_reading' => ['required', 'integer'],
            'description_of_work' => ['required', 'string'],
            'personnel_labor_cost' => ['required', 'numeric', 'min:0'],
            'next_maintenance_due_at' => ['nullable', 'date'],
            'next_maintenance_due_odometer' => ['nullable', 'integer'],
            'materials.*.name' => ['nullable', 'string', 'max:255'],
            'materials.*.description' => ['nullable', 'string'],
            'materials.*.quantity' => ['nullable', 'numeric', 'min:0'],
            'materials.*.unit' => ['nullable', 'string', 'max:50'],
            'materials.*.unit_cost' => ['nullable', 'numeric', 'min:0'],
        ];
    }

    #[Layout('layouts.app')]
    public function render(): View
    {
        $records = $this->getRecords();
        $vehicles = Vehicle::orderBy('plate_number')->get();

        return view('livewire.maintenance.index', [
            'records' => $records,
            'vehicles' => $vehicles,
        ]);
    }

    public function updatingVehicleFilter(): void
    {
        $this->resetPage();
    }

    public function openCreateModal(): void
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function addMaterialRow(): void
    {
        $this->materials[] = [
            'name' => '',
            'description' => '',
            'quantity' => null,
            'unit' => '',
            'unit_cost' => null,
        ];
    }

    public function removeMaterialRow(int $index): void
    {
        unset($this->materials[$index]);
        $this->materials = array_values($this->materials);
    }

    public function save(): void
    {
        $this->validate();

        $performedAt = $this->performed_at
            ? Carbon::parse($this->performed_at)
            : now();

        $materialsCostTotal = 0.0;

        foreach ($this->materials as $material) {
            $quantity = (float) ($material['quantity'] ?? 0);
            $unitCost = (float) ($material['unit_cost'] ?? 0);
            $materialsCostTotal += $quantity * $unitCost;
        }

        $totalCost = $this->personnel_labor_cost + $materialsCostTotal;

        $record = MaintenanceRecord::create([
            'vehicle_id' => $this->vehicle_id,
            'performed_by_user_id' => Auth::id(),
            'performed_at' => $performedAt,
            'odometer_reading' => $this->odometer_reading,
            'description_of_work' => $this->description_of_work,
            'personnel_labor_cost' => $this->personnel_labor_cost,
            'materials_cost_total' => $materialsCostTotal,
            'total_cost' => $totalCost,
            'next_maintenance_due_at' => $this->next_maintenance_due_at,
            'next_maintenance_due_odometer' => $this->next_maintenance_due_odometer,
        ]);

        $vehicle = Vehicle::find($this->vehicle_id);

        if ($vehicle !== null) {
            $vehicle->update([
                'current_odometer' => $this->odometer_reading,
                'last_maintenance_at' => $performedAt,
                'last_maintenance_odometer' => $this->odometer_reading,
                'next_maintenance_due_at' => $this->next_maintenance_due_at ?? $vehicle->next_maintenance_due_at,
                'next_maintenance_due_odometer' => $this->next_maintenance_due_odometer ?? $vehicle->next_maintenance_due_odometer,
            ]);
        }

        foreach ($this->materials as $material) {
            if (! ($material['name'] ?? null)) {
                continue;
            }

            $quantity = (float) ($material['quantity'] ?? 0);
            $unitCost = (float) ($material['unit_cost'] ?? 0);

            MaintenanceMaterial::create([
                'maintenance_record_id' => $record->id,
                'name' => $material['name'],
                'description' => $material['description'] ?? null,
                'quantity' => $quantity,
                'unit' => $material['unit'] ?? null,
                'unit_cost' => $unitCost,
                'total_cost' => $quantity * $unitCost,
            ]);
        }

        $this->resetForm();
        $this->showModal = false;
        $this->resetPage();

        session()->flash('success', __('Maintenance record created successfully.'));
    }

    private function resetForm(): void
    {
        $this->vehicle_id = null;
        $this->performed_at = null;
        $this->odometer_reading = null;
        $this->description_of_work = '';
        $this->personnel_labor_cost = 0.0;
        $this->next_maintenance_due_at = null;
        $this->next_maintenance_due_odometer = null;
        $this->materials = [];
        $this->addMaterialRow();
    }

    public function exportCsv(): StreamedResponse
    {
        $records = MaintenanceRecord::with(['vehicle', 'materials', 'performedBy'])
            ->when($this->vehicleFilter, function ($query): void {
                $query->where('vehicle_id', $this->vehicleFilter);
            })
            ->orderByDesc('performed_at')
            ->get();

        $filename = 'maintenance_records_' . now()->format('Y-m-d_His') . '.csv';

        return response()->streamDownload(function () use ($records): void {
            $handle = fopen('php://output', 'w');

            // CSV Headers
            fputcsv($handle, [
                'ID',
                'Vehicle',
                'Performed At',
                'Performed By',
                'Odometer Reading',
                'Description',
                'Labor Cost',
                'Materials Cost',
                'Total Cost',
                'Next Due Date',
                'Next Due Odometer',
                'Materials Used',
            ]);

            // CSV Data
            foreach ($records as $record) {
                $materialsText = $record->materials->map(function ($m) {
                    return $m->name . ' (' . $m->quantity . ' ' . $m->unit . ')';
                })->implode('; ');

                fputcsv($handle, [
                    $record->id,
                    $record->vehicle?->plate_number ?? '',
                    $record->performed_at?->format('Y-m-d H:i'),
                    $record->performedBy?->name ?? '',
                    $record->odometer_reading,
                    $record->description_of_work,
                    $record->personnel_labor_cost,
                    $record->materials_cost_total,
                    $record->total_cost,
                    $record->next_maintenance_due_at?->format('Y-m-d'),
                    $record->next_maintenance_due_odometer,
                    $materialsText,
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    private function getRecords(): LengthAwarePaginator
    {
        return MaintenanceRecord::with('vehicle')
            ->when($this->vehicleFilter, function ($query): void {
                $query->where('vehicle_id', $this->vehicleFilter);
            })
            ->orderByDesc('performed_at')
            ->paginate(10);
    }
}
