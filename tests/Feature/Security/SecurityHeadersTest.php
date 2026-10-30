<?php

namespace Tests\Feature\Security;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Livewire\Livewire;
use Tests\TestCase;

class SecurityHeadersTest extends TestCase
{
    use RefreshDatabase;

    public function test_protected_pages_include_security_headers(): void
    {
        $user = User::factory()->create([
            'role' => User::ROLE_ADMIN,
            'status' => User::STATUS_ACTIVE,
        ]);

        $response = $this->actingAs($user)->get('/dashboard');

        $response
            ->assertOk()
            ->assertHeader('x-content-type-options', 'nosniff')
            ->assertHeader('x-frame-options', 'SAMEORIGIN')
            ->assertHeader('referrer-policy', 'strict-origin-when-cross-origin');

        $contentSecurityPolicy = $response->headers->get('Content-Security-Policy');

        $this->assertNotNull($contentSecurityPolicy);
        $this->assertStringContainsString("default-src 'self'", $contentSecurityPolicy);
        $this->assertStringContainsString('http://127.0.0.1:5173', $contentSecurityPolicy);
        $this->assertStringContainsString('ws://127.0.0.1:5173', $contentSecurityPolicy);
        $this->assertStringContainsString('worker-src \'self\' blob: data:', $contentSecurityPolicy);
        $this->assertStringContainsString('https://fonts.bunny.net', $contentSecurityPolicy);
    }

    public function test_vehicle_upload_validation_rejects_invalid_image_content(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
            'status' => User::STATUS_ACTIVE,
        ]);

        $this->actingAs($admin);

        Livewire::test('vehicles.index')
            ->set('vehicle_type', 'Truck')
            ->set('plate_number', 'BAD-123')
            ->set('status', 'operational')
            ->set('photo', UploadedFile::fake()->create('fake.png', 10))
            ->call('save')
            ->assertHasErrors(['photo']);
    }
}
