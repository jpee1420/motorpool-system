<?php

namespace Tests\Feature\Security;

use App\Models\ApprovalRequest;
use App\Models\User;
use App\Services\ApprovalRequestService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApprovalWorkflowHardeningTest extends TestCase
{
    use RefreshDatabase;

    public function test_non_admins_cannot_approve_or_reject_pending_requests(): void
    {
        $staff = User::factory()->create([
            'role' => User::ROLE_STAFF,
            'status' => User::STATUS_ACTIVE,
        ]);

        $request = ApprovalRequest::create([
            'requester_user_id' => $staff->id,
            'action' => ApprovalRequest::ACTION_CREATE,
            'subject_type' => 'Vehicle',
            'subject_id' => null,
            'subject_label' => 'Vehicle approval',
            'original_data' => null,
            'proposed_data' => [
                'plate_number' => 'ABC-123',
                'status' => 'operational',
            ],
        ]);

        $service = app(ApprovalRequestService::class);

        $this->assertFalse($service->approve($request, $staff, 'Nope'));
        $this->assertFalse($service->reject($request, $staff, 'Nope'));
        $this->assertSame(ApprovalRequest::STATUS_PENDING, $request->fresh()->status);
    }

    public function test_approval_service_rejects_already_processed_requests(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
            'status' => User::STATUS_ACTIVE,
        ]);

        $request = ApprovalRequest::create([
            'requester_user_id' => $admin->id,
            'action' => ApprovalRequest::ACTION_CREATE,
            'subject_type' => 'Vehicle',
            'subject_id' => null,
            'subject_label' => 'Vehicle approval',
            'original_data' => null,
            'proposed_data' => [
                'plate_number' => 'XYZ-999',
                'status' => 'operational',
            ],
            'status' => ApprovalRequest::STATUS_APPROVED,
            'reviewer_user_id' => $admin->id,
            'review_notes' => 'Approved earlier',
            'reviewed_at' => now(),
        ]);

        $service = app(ApprovalRequestService::class);

        $this->assertFalse($service->approve($request, $admin, 'Again'));
        $this->assertFalse($service->reject($request, $admin, 'Again'));
        $this->assertSame(ApprovalRequest::STATUS_APPROVED, $request->fresh()->status);
    }
}
