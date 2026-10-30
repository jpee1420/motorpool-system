<?php

declare(strict_types=1);

namespace App\Livewire\TripTickets;

use App\Models\TripTicket;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

class Show extends Component
{
    use AuthorizesRequests;

    public TripTicket $ticket;

    public function mount(TripTicket $ticket): void
    {
        $this->ticket = TripTicket::query()->findOrFail($ticket->id)->load(['vehicle', 'requestedBy']);

        $this->authorize('view', $this->ticket);
    }

    #[Layout('layouts.app')]
    public function render(): View
    {
        return view('livewire.trip-tickets.show', [
            'ticket' => $this->ticket,
        ]);
    }
}
