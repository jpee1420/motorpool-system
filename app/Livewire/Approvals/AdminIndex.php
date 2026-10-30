<?php

declare(strict_types=1);

namespace App\Livewire\Approvals;

use App\Models\ApprovalRequest;
use App\Services\ApprovalRequestService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

class AdminIndex extends Component
{
    use AuthorizesRequests;
    use WithPagination;

    public ?string $status = 'pending';
    public ?string $subjectType = null;
    public ?string $search = null;

    public bool $showReviewModal = false;
    public ?int $reviewingId = null;
    public string $reviewAction = '';
    public string $reviewNotes = '';

    protected function queryString(): array
    {
        return [
            'status' => ['except' => 'pending'],
            'subjectType' => ['except' => null],
            'search' => ['except' => null],
            'page' => ['except' => 1],
        ];
    }

    public function mount(): void
    {
        $this->authorize('viewAny', ApprovalRequest::class);

        if (! auth()->user()?->isAdmin()) {
            abort(403);
        }
    }

    public function updatingStatus(): void
    {
        $this->resetPage();
    }

    public function updatingSubjectType(): void
    {
        $this->resetPage();
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function openReview(int $id, string $action): void
    {
        $this->reviewingId = $id;
        $this->reviewAction = $action;
        $this->reviewNotes = '';
        $this->showReviewModal = true;
    }

    public function closeReview(): void
    {
        $this->showReviewModal = false;
        $this->reviewingId = null;
        $this->reviewAction = '';
        $this->reviewNotes = '';
    }

    public function confirmReview(ApprovalRequestService $service): void
    {
        if ($this->reviewingId === null || ! in_array($this->reviewAction, ['approve', 'reject'], true)) {
            return;
        }

        $request = ApprovalRequest::find($this->reviewingId);

        if ($request === null) {
            session()->flash('error', __('Approval request not found.'));
            $this->closeReview();

            return;
        }

        $this->authorize('review', $request);

        $user = auth()->user();

        if ($this->reviewAction === 'approve') {
            $result = $service->approve($request, $user, $this->reviewNotes ?: null);
            $message = $result ? __('Request approved successfully.') : __('Failed to approve request.');
        } else {
            $result = $service->reject($request, $user, $this->reviewNotes ?: null);
            $message = $result ? __('Request rejected.') : __('Failed to reject request.');
        }

        session()->flash($result ? 'success' : 'error', $message);
        $this->closeReview();
    }

    #[Layout('layouts.app')]
    public function render(): View
    {
        return view('livewire.approvals.admin-index', [
            'requests' => $this->getRequests(),
            'subjectTypes' => $this->getSubjectTypes(),
            'reviewingRequest' => $this->getReviewingRequest(),
        ]);
    }

    private function getRequests(): LengthAwarePaginator
    {
        return ApprovalRequest::query()
            ->with(['requester', 'reviewer'])
            ->when($this->status, fn ($q) => $q->where('status', $this->status))
            ->when($this->subjectType, fn ($q) => $q->where('subject_type', $this->subjectType))
            ->when($this->search, function ($q): void {
                $search = '%' . $this->search . '%';
                $q->where(function ($sub) use ($search): void {
                    $sub->where('subject_label', 'like', $search)
                        ->orWhereHas('requester', fn ($r) => $r->where('name', 'like', $search));
                });
            })
            ->orderByDesc('created_at')
            ->paginate(15);
    }

    private function getSubjectTypes(): array
    {
        return ApprovalRequest::query()
            ->select('subject_type')
            ->distinct()
            ->orderBy('subject_type')
            ->pluck('subject_type')
            ->all();
    }

    private function getReviewingRequest(): ?ApprovalRequest
    {
        if (! $this->showReviewModal || $this->reviewingId === null) {
            return null;
        }

        return ApprovalRequest::with('requester')->find($this->reviewingId);
    }
}
