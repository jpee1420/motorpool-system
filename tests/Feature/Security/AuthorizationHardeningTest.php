<?php

namespace Tests\Feature\Security;

use App\Models\MaintenanceRecord;
use App\Models\NotificationLog;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AuthorizationHardeningTest extends TestCase
{
    use RefreshDatabase;

    public function test_regular_users_cannot_access_notification_admin_pages(): void
    {
        $user = User::factory()->create([
            'role' => User::ROLE_USER,
            'status' => User::STATUS_ACTIVE,
        ]);

        $this->actingAs($user)
            ->get('/notifications')
            ->assertForbidden();
    }

    public function test_users_cannot_mark_another_users_notification_as_read(): void
    {
        $owner = User::factory()->create([
            'role' => User::ROLE_USER,
            'status' => User::STATUS_ACTIVE,
        ]);
        $otherUser = User::factory()->create([
            'role' => User::ROLE_USER,
            'status' => User::STATUS_ACTIVE,
        ]);

        $vehicle = Vehicle::create([
            'user_id' => $otherUser->id,
            'plate_number' => 'NOTIF-001',
            'current_odometer' => 1000,
        ]);

        $log = NotificationLog::create([
            'vehicle_id' => $vehicle->id,
            'user_id' => $otherUser->id,
            'channel' => NotificationLog::CHANNEL_IN_APP,
            'type' => NotificationLog::TYPE_MAINTENANCE_UPCOMING,
            'trigger_reason' => NotificationLog::TRIGGER_DATE_DUE,
            'meta' => [],
            'recipient_name' => $otherUser->name,
            'recipient_contact' => $otherUser->email,
            'sent_at' => now(),
            'status' => NotificationLog::STATUS_SENT,
            'error_message' => null,
            'retry_count' => 0,
            'max_retries_reached' => false,
        ]);

        $this->actingAs($owner);

        Livewire::test('notifications.bell')
            ->call('markAsRead', $log->id);

        $this->assertDatabaseHas('notification_logs', [
            'id' => $log->id,
            'read_at' => null,
        ]);
    }

    public function test_driver_cannot_view_another_drivers_maintenance_record(): void
    {
        $driver = User::factory()->create([
            'role' => User::ROLE_USER,
            'status' => User::STATUS_ACTIVE,
        ]);
        $otherDriver = User::factory()->create([
            'role' => User::ROLE_USER,
            'status' => User::STATUS_ACTIVE,
        ]);

        $vehicle = Vehicle::create([
            'user_id' => $otherDriver->id,
            'plate_number' => 'ABC-123',
            'current_odometer' => 15000,
        ]);

        $record = MaintenanceRecord::create([
            'vehicle_id' => $vehicle->id,
            'assigned_driver_id' => $otherDriver->id,
            'performed_by_user_id' => $otherDriver->id,
            'performed_by' => $otherDriver->name,
            'performed_at' => now(),
            'odometer_reading' => 15000,
            'description_of_work' => 'Scheduled service',
            'personnel_labor_cost' => 0,
            'materials_cost_total' => 0,
            'total_cost' => 0,
            'next_maintenance_due_at' => now()->addMonth()->toDateString(),
            'next_maintenance_due_odometer' => 20000,
        ]);

        $this->actingAs($driver)
            ->get('/maintenance/' . $record->id)
            ->assertForbidden();
    }
}
