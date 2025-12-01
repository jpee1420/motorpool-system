<?php

declare(strict_types=1);

namespace App\Livewire\TripTickets;

use App\Exports\TripTicketsExport;
use App\Models\TripTicket;
use App\Models\Vehicle;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

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
    public ?string $travel_date = null;
    public ?int $odometer_start = null;
    public ?int $odometer_end = null;

    protected function rules(): array
    {
        return [
            'vehicle_id' => ['required', 'integer', 'exists:vehicles,id'],
            'driver_name' => ['required', 'string', 'max:255'],
            'destination' => ['required', 'string', 'max:255'],
            'purpose' => ['required', 'string', 'max:255'],
            'travel_date' => ['required', 'date'],
            'odometer_start' => ['nullable', 'integer', 'min:0'],
            'odometer_end' => ['nullable', 'integer', 'min:0'],
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
        $this->travel_date = $ticket->departure_at?->format('Y-m-d');
        $this->odometer_start = $ticket->odometer_start;
        $this->odometer_end = $ticket->odometer_end;

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

        $travelDate = Carbon::parse($this->travel_date);

        $data = [
            'vehicle_id' => $this->vehicle_id,
            'driver_name' => $this->driver_name ?? '',
            'destination' => $this->destination ?? '',
            'purpose' => $this->purpose ?? '',
            'departure_at' => $travelDate,
            'odometer_start' => $this->odometer_start,
            'odometer_end' => $this->odometer_end,
        ];

        if ($this->editingId) {
            $ticket = TripTicket::findOrFail($this->editingId);
            $ticket->update($data);

            session()->flash('success', __('Trip ticket updated successfully.'));
        } else {
            TripTicket::create(array_merge($data, [
                'requested_by_user_id' => Auth::id(),
                'status' => 'pending',
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
        $this->travel_date = null;
        $this->odometer_start = null;
        $this->odometer_end = null;
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
            'canExport' => auth()->user()?->can('export', TripTicket::class) ?? false,
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

    public function exportCsv(): StreamedResponse
    {
        $this->authorize('export', TripTicket::class);

        $tickets = $this->getExportQuery()->get();

        $filename = 'trip_tickets_' . now()->format('Y-m-d_His') . '.csv';

        return response()->streamDownload(function () use ($tickets): void {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, [
                'ID',
                'Vehicle',
                'Driver',
                'Destination',
                'Purpose',
                'Departure',
                'Return',
                'Odometer Start',
                'Odometer End',
                'Distance',
                'Status',
                'Requested By',
            ]);

            foreach ($tickets as $ticket) {
                $distance = ($ticket->odometer_end && $ticket->odometer_start)
                    ? $ticket->odometer_end - $ticket->odometer_start
                    : null;

                fputcsv($handle, [
                    $ticket->id,
                    $ticket->vehicle?->plate_number ?? '',
                    $ticket->driver_name,
                    $ticket->destination,
                    $ticket->purpose,
                    $ticket->departure_at?->format('Y-m-d H:i'),
                    $ticket->return_at?->format('Y-m-d H:i'),
                    $ticket->odometer_start,
                    $ticket->odometer_end,
                    $distance,
                    $ticket->status,
                    $ticket->requestedBy?->name ?? '',
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    public function exportPdf(): Response
    {
        $this->authorize('export', TripTicket::class);

        $tickets = $this->getExportQuery()->get();

        $filename = 'trip_tickets_' . now()->format('Y-m-d_His') . '.pdf';

        $pdf = Pdf::loadView('exports.trip-tickets-pdf', [
            'tickets' => $tickets,
        ]);

        return $pdf->download($filename);
    }

    public function exportExcel(): BinaryFileResponse
    {
        $this->authorize('export', TripTicket::class);

        $tickets = $this->getExportQuery()->get();

        $filename = 'trip_tickets_' . now()->format('Y-m-d_His') . '.xlsx';

        return Excel::download(new TripTicketsExport($tickets), $filename);
    }

    private function getExportQuery()
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
            ->orderByDesc('departure_at');
    }
}
