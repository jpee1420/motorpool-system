<?php

declare(strict_types=1);

namespace App\Livewire\Approvals;

use App\Models\ApprovalRequest;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

class StaffIndex extends Component
{
    use AuthorizesRequests;
    use WithPagination;

    public ?string $status = null;
    public ?string $subjectType = null;

    public bool $showDetailsModal = false;
    public ?int $detailsId = null;

    protected function queryString(): array
    {
        return [
            'status' => ['except' => null],
            'subjectType' => ['except' => null],
            'page' => ['except' => 1],
        ];
    }

    public function mount(): void
    {
        $this->authorize('viewAny', ApprovalRequest::class);
    }

    public function updatingStatus(): void
    {
        $this->resetPage();
    }

    public function updatingSubjectType(): void
    {
        $this->resetPage();
    }

    public function openDetails(int $id): void
    {
        $request = ApprovalRequest::whereKey($id)
            ->where('requester_user_id', auth()->id())
            ->first();

        if ($request === null) {
            abort(403);
        }

        $this->authorize('view', $request);

        $this->detailsId = $id;
        $this->showDetailsModal = true;
    }

    public function closeDetails(): void
    {
        $this->showDetailsModal = false;
        $this->detailsId = null;
    }

    #[Layout('layouts.app')]
    public function render(): View
    {
        return view('livewire.approvals.staff-index', [
            'requests' => $this->getRequests(),
            'subjectTypes' => $this->getSubjectTypes(),
            'selectedRequest' => $this->getSelectedRequest(),
        ]);
    }

    private function getRequests(): LengthAwarePaginator
    {
        return ApprovalRequest::query()
            ->with('reviewer')
            ->where('requester_user_id', auth()->id())
            ->when($this->status, fn ($q) => $q->where('status', $this->status))
            ->when($this->subjectType, fn ($q) => $q->where('subject_type', $this->subjectType))
            ->orderByDesc('created_at')
            ->paginate(15);
    }

    private function getSubjectTypes(): array
    {
        return ApprovalRequest::query()
            ->where('requester_user_id', auth()->id())
            ->select('subject_type')
            ->distinct()
            ->orderBy('subject_type')
            ->pluck('subject_type')
            ->all();
    }

    private function getSelectedRequest(): ?ApprovalRequest
    {
        if (! $this->showDetailsModal || $this->detailsId === null) {
            return null;
        }

        return ApprovalRequest::with('reviewer')
            ->where('requester_user_id', auth()->id())
            ->find($this->detailsId);
    }
}
