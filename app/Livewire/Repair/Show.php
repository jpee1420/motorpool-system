<?php

declare(strict_types=1);

namespace App\Livewire\Repair;

use App\Models\RepairRecord;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

class Show extends Component
{
    use AuthorizesRequests;

    public RepairRecord $record;

    public function mount(RepairRecord $record): void
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

        session()->flash('success', __('Repair record deleted successfully.'));

        $this->redirectRoute('repair.index');
    }

    #[Layout('layouts.app')]
    public function render(): View
    {
        return view('livewire.repair.show', [
            'record' => $this->record,
        ]);
    }
}
