<?php

declare(strict_types=1);

namespace App\Livewire\TripTickets;

use App\Models\TripTicket;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

class Show extends Component
{
    public TripTicket $ticket;

    public function mount(TripTicket $ticket): void
    {
        $this->ticket = $ticket->load(['vehicle', 'requestedBy']);
    }

    #[Layout('layouts.app')]
    public function render(): View
    {
        return view('livewire.trip-tickets.show');
    }
}
