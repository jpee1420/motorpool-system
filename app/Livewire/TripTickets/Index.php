<?php

declare(strict_types=1);

namespace App\Livewire\TripTickets;

use App\Models\TripTicket;
use App\Models\Vehicle;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use AuthorizesRequests;
    use WithPagination;

    public ?int $vehicleFilter = null;
    public ?string $status = null;
    public ?string $search = null;

    public bool $showModal = false;

    #[Locked]
    public ?int $editingId = null;

    public ?int $vehicle_id = null;
    public ?string $driver_name = null;
    public ?string $destination = null;
    public ?string $purpose = null;
    public ?string $departure_at = null;
    public ?string $return_at = null;
    public ?int $odometer_start = null;
    public ?int $odometer_end = null;
    public string $form_status = 'pending';

    protected function rules(): array
    {
        return [
            'vehicle_id' => ['required', 'integer', 'exists:vehicles,id'],
            'driver_name' => ['required', 'string', 'max:255'],
            'destination' => ['required', 'string', 'max:255'],
            'purpose' => ['required', 'string', 'max:255'],
            'departure_at' => ['required', 'date'],
            'return_at' => ['nullable', 'date'],
            'odometer_start' => ['nullable', 'integer', 'min:0'],
            'odometer_end' => ['nullable', 'integer', 'min:0'],
            'form_status' => ['required', 'string'],
        ];
    }

    public function updatingVehicleFilter(): void
    {
        $this->resetPage();
    }

    public function updatingStatus(): void
    {
        $this->resetPage();
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function openCreateModal(): void
    {
        $this->authorize('create', TripTicket::class);

        $this->resetForm();
        $this->showModal = true;
    }

    public function edit(int $id): void
    {
        $ticket = TripTicket::findOrFail($id);

        $this->authorize('update', $ticket);

        $this->editingId = $ticket->id;
        $this->vehicle_id = $ticket->vehicle_id;
        $this->driver_name = $ticket->driver_name;
        $this->destination = $ticket->destination;
        $this->purpose = $ticket->purpose;
        $this->departure_at = $ticket->departure_at?->format('Y-m-d\TH:i');
        $this->return_at = $ticket->return_at?->format('Y-m-d\TH:i');
        $this->odometer_start = $ticket->odometer_start;
        $this->odometer_end = $ticket->odometer_end;
        $this->form_status = $ticket->status;

        $this->showModal = true;
    }

    public function save(): void
    {
        if ($this->editingId) {
            $ticket = TripTicket::findOrFail($this->editingId);
            $this->authorize('update', $ticket);
        } else {
            $this->authorize('create', TripTicket::class);
        }

        $this->validate();

        $departureAt = Carbon::parse($this->departure_at);
        $returnAt = $this->return_at ? Carbon::parse($this->return_at) : null;

        $data = [
            'vehicle_id' => $this->vehicle_id,
            'driver_name' => $this->driver_name ?? '',
            'destination' => $this->destination ?? '',
            'purpose' => $this->purpose ?? '',
            'departure_at' => $departureAt,
            'return_at' => $returnAt,
            'odometer_start' => $this->odometer_start,
            'odometer_end' => $this->odometer_end,
            'status' => $this->form_status,
        ];

        if ($this->editingId) {
            $ticket = TripTicket::findOrFail($this->editingId);
            $ticket->update($data);

            session()->flash('success', __('Trip ticket updated successfully.'));
        } else {
            TripTicket::create(array_merge($data, [
                'requested_by_user_id' => Auth::id(),
                'notes' => null,
            ]));

            session()->flash('success', __('Trip ticket created successfully.'));
        }

        $this->resetForm();
        $this->showModal = false;
        $this->resetPage();
    }

    private function resetForm(): void
    {
        $this->editingId = null;
        $this->vehicle_id = null;
        $this->driver_name = null;
        $this->destination = null;
        $this->purpose = null;
        $this->departure_at = null;
        $this->return_at = null;
        $this->odometer_start = null;
        $this->odometer_end = null;
        $this->form_status = 'pending';
    }

    public function mount(): void
    {
        $this->authorize('viewAny', TripTicket::class);
    }

    #[Layout('layouts.app')]
    public function render(): View
    {
        return view('livewire.trip-tickets.index', [
            'tickets' => $this->getTickets(),
            'vehicles' => Vehicle::orderBy('plate_number')->get(),
            'canCreate' => auth()->user()?->can('create', TripTicket::class) ?? false,
        ]);
    }

    private function getTickets(): LengthAwarePaginator
    {
        return TripTicket::with(['vehicle', 'requestedBy'])
            ->when($this->vehicleFilter, function ($query): void {
                $query->where('vehicle_id', $this->vehicleFilter);
            })
            ->when($this->status, function ($query): void {
                $query->where('status', $this->status);
            })
            ->when($this->search, function ($query): void {
                $search = '%' . $this->search . '%';

                $query->where(function ($subQuery) use ($search): void {
                    $subQuery
                        ->where('driver_name', 'like', $search)
                        ->orWhere('destination', 'like', $search)
                        ->orWhere('purpose', 'like', $search)
                        ->orWhereHas('vehicle', function ($vehicleQuery) use ($search): void {
                            $vehicleQuery->where('plate_number', 'like', $search);
                        });
                });
            })
            ->orderByDesc('departure_at')
            ->paginate(15);
    }
}
