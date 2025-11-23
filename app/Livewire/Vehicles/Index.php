<?php

declare(strict_types=1);

namespace App\Livewire\Vehicles;

use App\Models\Vehicle;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class Index extends Component
{
    use WithFileUploads;
    use WithPagination;

    public string $search = '';
    public string $statusFilter = '';

    public bool $showModal = false;

    public ?int $editingId = null;

    public string $vehicle_type = '';
    public string $plate_number = '';
    public string $chassis_number = '';
    public string $make = '';
    public string $model = '';
    public ?int $year = null;
    public string $engine_number = '';
    public string $driver_operator = '';
    public string $contact_number = '';
    public string $status = 'operational';

    public $photo = null;

    protected function rules(): array
    {
        return [
            'vehicle_type' => ['required', 'string', 'max:255'],
            'plate_number' => [
                'required',
                'string',
                'max:255',
                Rule::unique('vehicles', 'plate_number')->ignore($this->editingId),
            ],
            'chassis_number' => ['nullable', 'string', 'max:255'],
            'make' => ['nullable', 'string', 'max:255'],
            'model' => ['nullable', 'string', 'max:255'],
            'year' => ['nullable', 'integer'],
            'engine_number' => ['nullable', 'string', 'max:255'],
            'driver_operator' => ['nullable', 'string', 'max:255'],
            'contact_number' => ['nullable', 'string', 'max:255'],
            'status' => ['required', 'string', 'in:operational,non-operational,maintenance'],
            'photo' => ['nullable', 'image', 'max:2048'],
        ];
    }

    #[Layout('layouts.app')]
    public function render(): View
    {
        $vehicles = $this->getVehicles();

        return view('livewire.vehicles.index', [
            'vehicles' => $vehicles,
        ]);
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function openCreateModal(): void
    {
        $this->resetForm();
        $this->editingId = null;
        $this->showModal = true;
    }

    public function edit(int $vehicleId): void
    {
        $vehicle = Vehicle::findOrFail($vehicleId);

        $this->editingId = $vehicle->id;
        $this->vehicle_type = (string) $vehicle->vehicle_type;
        $this->plate_number = (string) $vehicle->plate_number;
        $this->chassis_number = (string) ($vehicle->chassis_number ?? '');
        $this->make = (string) ($vehicle->make ?? '');
        $this->model = (string) ($vehicle->model ?? '');
        $this->year = $vehicle->year;
        $this->engine_number = (string) ($vehicle->engine_number ?? '');
        $this->driver_operator = (string) ($vehicle->driver_operator ?? '');
        $this->contact_number = (string) ($vehicle->contact_number ?? '');
        $this->status = (string) $vehicle->status;

        $this->photo = null;
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate();

        $photoPath = null;

        if ($this->photo) {
            $photoPath = $this->photo->store('vehicles', 'public');
        }

        if ($this->editingId) {
            $vehicle = Vehicle::findOrFail($this->editingId);

            $data = [
                'vehicle_type' => $this->vehicle_type,
                'plate_number' => $this->plate_number,
                'chassis_number' => $this->chassis_number,
                'make' => $this->make,
                'model' => $this->model,
                'year' => $this->year,
                'engine_number' => $this->engine_number,
                'driver_operator' => $this->driver_operator,
                'contact_number' => $this->contact_number,
                'status' => $this->status,
            ];

            if ($photoPath) {
                $data['photo_path'] = $photoPath;
            }

            $vehicle->update($data);
        } else {
            Vehicle::create([
                'vehicle_type' => $this->vehicle_type,
                'plate_number' => $this->plate_number,
                'chassis_number' => $this->chassis_number,
                'make' => $this->make,
                'model' => $this->model,
                'year' => $this->year,
                'engine_number' => $this->engine_number,
                'driver_operator' => $this->driver_operator,
                'contact_number' => $this->contact_number,
                'status' => $this->status,
                'photo_path' => $photoPath,
            ]);
        }

        $this->resetForm();
        $this->showModal = false;
        $this->resetPage();
    }

    private function resetForm(): void
    {
        $this->vehicle_type = '';
        $this->plate_number = '';
        $this->chassis_number = '';
        $this->make = '';
        $this->model = '';
        $this->year = null;
        $this->engine_number = '';
        $this->driver_operator = '';
        $this->contact_number = '';
        $this->status = 'operational';
        $this->photo = null;
    }

    private function getVehicles(): LengthAwarePaginator
    {
        return Vehicle::query()
            ->when($this->search !== '', function ($query): void {
                $query->where(function ($sub): void {
                    $sub->where('plate_number', 'like', "%{$this->search}%")
                        ->orWhere('make', 'like', "%{$this->search}%")
                        ->orWhere('model', 'like', "%{$this->search}%");
                });
            })
            ->when($this->statusFilter !== '', function ($query): void {
                $query->where('status', $this->statusFilter);
            })
            ->orderBy('plate_number')
            ->paginate(10);
    }
}
