<?php

declare(strict_types=1);

namespace App\Livewire\Repair;

use App\Models\MaintenanceMaterial;
use App\Models\MaintenanceRecord;
use App\Models\Vehicle;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;
use Symfony\Component\HttpFoundation\StreamedResponse;

class Index extends Component
{
    use AuthorizesRequests;
    use WithPagination;

    public ?int $vehicleFilter = null;

    public bool $showModal = false;

    public ?int $vehicle_id = null;
    public ?string $performed_at = null;
    public ?int $odometer_reading = null;
    public string $description_of_work = '';
    public float $personnel_labor_cost = 0.0;

    public array $materials = [];

    protected function rules(): array
    {
        return [
            'vehicle_id' => ['required', 'integer', 'exists:vehicles,id'],
            'performed_at' => ['required', 'date'],
            'odometer_reading' => ['required', 'integer'],
            'description_of_work' => ['required', 'string'],
            'personnel_labor_cost' => ['required', 'numeric', 'min:0'],
            'materials.*.name' => ['nullable', 'string', 'max:255'],
            'materials.*.description' => ['nullable', 'string'],
            'materials.*.quantity' => ['nullable', 'numeric', 'min:0'],
            'materials.*.unit' => ['nullable', 'string', 'max:50'],
            'materials.*.unit_cost' => ['nullable', 'numeric', 'min:0'],
        ];
    }

    public function mount(): void
    {
        $this->authorize('viewAny', MaintenanceRecord::class);
    }

    #[Layout('layouts.app')]
    public function render(): View
    {
        $records = $this->getRecords();
        $vehicles = Vehicle::query()->orderBy('plate_number')->get();

        return view('livewire.repair.index', [
            'records' => $records,
            'vehicles' => $vehicles,
            'canCreate' => auth()->user()?->can('create', MaintenanceRecord::class) ?? false,
            'canExport' => auth()->user()?->can('export', MaintenanceRecord::class) ?? false,
        ]);
    }

    public function updatingVehicleFilter(): void
    {
        $this->resetPage();
    }

    public function openCreateModal(): void
    {
        $this->authorize('create', MaintenanceRecord::class);

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
        $this->authorize('create', MaintenanceRecord::class);

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
            'type' => MaintenanceRecord::TYPE_REPAIR,
            'vehicle_id' => $this->vehicle_id,
            'performed_by_user_id' => Auth::id(),
            'performed_at' => $performedAt,
            'odometer_reading' => $this->odometer_reading,
            'description_of_work' => $this->description_of_work,
            'personnel_labor_cost' => $this->personnel_labor_cost,
            'materials_cost_total' => $materialsCostTotal,
            'total_cost' => $totalCost,
            'next_maintenance_due_at' => null,
            'next_maintenance_due_odometer' => null,
        ]);

        // Update vehicle's current odometer if this repair reading is higher
        $vehicle = Vehicle::find($this->vehicle_id);

        if ($vehicle !== null && $this->odometer_reading > $vehicle->current_odometer) {
            $vehicle->update(['current_odometer' => $this->odometer_reading]);
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

        session()->flash('success', __('Repair record created successfully.'));
    }

    private function resetForm(): void
    {
        $this->vehicle_id = null;
        $this->performed_at = null;
        $this->odometer_reading = null;
        $this->description_of_work = '';
        $this->personnel_labor_cost = 0.0;
        $this->materials = [];
        $this->addMaterialRow();
    }

    public function exportCsv(): StreamedResponse
    {
        $this->authorize('export', MaintenanceRecord::class);

        $records = MaintenanceRecord::with(['vehicle', 'materials', 'performedBy'])
            ->where('type', MaintenanceRecord::TYPE_REPAIR)
            ->when($this->vehicleFilter, function ($query): void {
                $query->where('vehicle_id', $this->vehicleFilter);
            })
            ->orderByDesc('performed_at')
            ->get();

        $filename = 'repair_records_' . now()->format('Y-m-d_His') . '.csv';

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
            ->where('type', MaintenanceRecord::TYPE_REPAIR)
            ->when($this->vehicleFilter, function ($query): void {
                $query->where('vehicle_id', $this->vehicleFilter);
            })
            ->orderByDesc('performed_at')
            ->paginate(10);
    }
}
