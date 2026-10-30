<?php

declare(strict_types=1);

namespace App\Livewire\Maintenance;

use App\Models\MaintenanceRecord;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

class Show extends Component
{
    use AuthorizesRequests;

    public MaintenanceRecord $record;

    public function mount(MaintenanceRecord $record): void
    {
        $this->record = $record->load([
            'vehicle',
            'assignedDriver',
            'materials',
            'performedBy',
        ]);
    }

    public function delete(): void
    {
        $this->authorize('delete', $this->record);

        $this->record->delete();

        session()->flash('success', __('Maintenance record deleted successfully.'));

        $this->redirectRoute('maintenance.index');
    }

    #[Layout('layouts.app')]
    public function render(): View
    {
        return view('livewire.maintenance.show', [
            'record' => $this->record,
        ]);
    }
}
