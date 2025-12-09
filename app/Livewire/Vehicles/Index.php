<?php

declare(strict_types=1);

namespace App\Livewire\Vehicles;

use App\Exports\VehiclesExport;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class Index extends Component
{
    use AuthorizesRequests;
    use WithFileUploads;
    use WithPagination;

    public string $search = '';
    public string $statusFilter = '';
    public string $sortField = 'plate_number';
    public string $sortDirection = 'asc';

    public bool $showModal = false;

    #[Locked]
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
    public ?int $assigned_user_id = null;

    public $photo = null;

    protected function rules(): array
    {
        return [
            'vehicle_type' => ['required', 'string', 'max:255'],
            'plate_number' => [
                'required',
                'string',
                'max:15', // Up to 10 chars + spaces for formatting
                function (string $attribute, mixed $value, \Closure $fail): void {
                    $withoutSpaces = preg_replace('/\s+/', '', $value);
                    if (strlen($withoutSpaces) > 10) {
                        $fail(__('The plate number must not exceed 10 characters (excluding spaces).'));
                    }
                },
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
            'photo' => ['nullable', 'image', 'max:5120', 'mimes:jpg,jpeg,png,webp'],
            'assigned_user_id' => ['nullable', 'integer', 'exists:users,id'],
        ];
    }

    public function mount(): void
    {
        $this->authorize('viewAny', Vehicle::class);
    }

    #[Layout('layouts.app')]
    public function render(): View
    {
        $user = auth()->user();
        $isStaffOrAbove = $user?->isStaffOrAbove() ?? false;
        $vehicles = $this->getVehicles();
        $drivers = $this->getDrivers();

        return view('livewire.vehicles.index', [
            'vehicles' => $vehicles,
            'drivers' => $drivers,
            'canCreate' => $user?->can('create', Vehicle::class) ?? false,
            'canExport' => $user?->can('export', Vehicle::class) ?? false,
            'canDelete' => $user?->isAdmin() ?? false,
            'isStaffOrAbove' => $isStaffOrAbove,
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

    public function updatedPlateNumber(string $value): void
    {
        $this->plate_number = $this->formatPlateNumber($value);
    }

    /**
     * Format plate number: remove extra spaces, auto-add space between letters and digits.
     */
    private function formatPlateNumber(string $value): string
    {
        // Remove all spaces and convert to uppercase
        $clean = strtoupper(preg_replace('/\s+/', '', $value));

        // Add space between letters and digits (e.g., ABC123 → ABC 123, ABC1234DEF → ABC 1234 DEF)
        $formatted = preg_replace('/([A-Z]+)(\d+)/', '$1 $2', $clean);
        $formatted = preg_replace('/(\d+)([A-Z]+)/', '$1 $2', $formatted);

        return trim($formatted);
    }

    public function sortBy(string $field): void
    {
        $allowed = ['plate_number', 'vehicle_type', 'make', 'year', 'status', 'created_at'];

        if (! in_array($field, $allowed, true)) {
            return;
        }

        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }

        $this->resetPage();
    }

    public function openCreateModal(): void
    {
        $this->authorize('create', Vehicle::class);

        $this->resetForm();
        $this->editingId = null;
        $this->showModal = true;
    }

    public function edit(int $vehicleId): void
    {
        $vehicle = Vehicle::findOrFail($vehicleId);

        $this->authorize('update', $vehicle);

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
        $this->assigned_user_id = $vehicle->user_id;

        $this->photo = null;
        $this->showModal = true;
    }

    public function save(): void
    {
        if ($this->editingId) {
            $vehicle = Vehicle::findOrFail($this->editingId);
            $this->authorize('update', $vehicle);
        } else {
            $this->authorize('create', Vehicle::class);
        }

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
                'user_id' => $this->assigned_user_id,
            ];

            if ($photoPath) {
                $data['photo_path'] = $photoPath;
            }

            $vehicle->update($data);

            session()->flash('success', __('Vehicle updated successfully.'));
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
                'user_id' => $this->assigned_user_id,
            ]);

            session()->flash('success', __('Vehicle created successfully.'));
        }

        $this->resetForm();
        $this->showModal = false;
        $this->resetPage();
    }

    public function delete(int $vehicleId): void
    {
        $vehicle = Vehicle::findOrFail($vehicleId);

        $this->authorize('delete', $vehicle);

        $vehicle->delete();

        session()->flash('success', __('Vehicle deleted successfully.'));

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
        $this->assigned_user_id = null;
        $this->photo = null;
    }

    public function exportCsv(): StreamedResponse
    {
        $this->authorize('export', Vehicle::class);

        $vehicles = Vehicle::query()
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
            ->get();

        $filename = 'vehicles_' . now()->format('Y-m-d_His') . '.csv';

        return response()->streamDownload(function () use ($vehicles): void {
            $handle = fopen('php://output', 'w');

            // CSV Headers
            fputcsv($handle, [
                'ID',
                'Plate Number',
                'Vehicle Type',
                'Make',
                'Model',
                'Year',
                'Chassis Number',
                'Engine Number',
                'Driver/Operator',
                'Contact Number',
                'Status',
                'Current Odometer',
                'Next Maintenance Due',
                'Next Maintenance Odometer',
            ]);

            // CSV Data
            foreach ($vehicles as $vehicle) {
                fputcsv($handle, [
                    $vehicle->id,
                    $vehicle->plate_number,
                    $vehicle->vehicle_type,
                    $vehicle->make,
                    $vehicle->model,
                    $vehicle->year,
                    $vehicle->chassis_number,
                    $vehicle->engine_number,
                    $vehicle->driver_operator,
                    $vehicle->contact_number,
                    $vehicle->status,
                    $vehicle->current_odometer,
                    $vehicle->next_maintenance_due_at?->format('Y-m-d'),
                    $vehicle->next_maintenance_due_odometer,
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    public function exportExcel(): BinaryFileResponse
    {
        $this->authorize('export', Vehicle::class);

        $vehicles = Vehicle::query()
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
            ->get();

        $filename = 'vehicles_' . now()->format('Y-m-d_His') . '.xlsx';

        return Excel::download(new VehiclesExport($vehicles), $filename);
    }

    private function getVehicles(): LengthAwarePaginator
    {
        $user = auth()->user();

        return Vehicle::query()
            // Drivers only see their assigned vehicles
            ->when($user !== null && ! $user->isStaffOrAbove(), function ($query) use ($user): void {
                $query->where('user_id', $user->id);
            })
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
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate(10);
    }

    private function getDrivers(): Collection
    {
        return User::query()
            ->where('role', User::ROLE_USER)
            ->where('status', User::STATUS_ACTIVE)
            ->orderBy('name')
            ->get(['id', 'name', 'email']);
    }
}
