<?php

declare(strict_types=1);

namespace App\Livewire\Maintenance;

use App\Models\MaintenanceRecord;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

class Show extends Component
{
    public MaintenanceRecord $record;

    public function mount(MaintenanceRecord $record): void
    {
        $this->record = $record->load([
            'vehicle',
            'materials',
            'performedBy',
        ]);
    }

    #[Layout('layouts.app')]
    public function render(): View
    {
        return view('livewire.maintenance.show', [
            'record' => $this->record,
        ]);
    }
}
