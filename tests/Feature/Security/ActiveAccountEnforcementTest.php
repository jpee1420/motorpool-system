<?php

namespace Tests\Feature\Security;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ActiveAccountEnforcementTest extends TestCase
{
    use RefreshDatabase;

    public function test_pending_users_are_logged_out_and_redirected_to_login(): void
    {
        $user = User::factory()->create([
            'status' => User::STATUS_PENDING,
            'role' => User::ROLE_USER,
        ]);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertRedirect('/login');
        $this->assertGuest();
        $this->assertSame('Your account is pending approval. Please wait for an administrator to approve your registration.', session('status'));
    }

    public function test_disabled_users_are_logged_out_and_redirected_to_login(): void
    {
        $user = User::factory()->create([
            'status' => User::STATUS_DISABLED,
            'role' => User::ROLE_USER,
        ]);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertRedirect('/login');
        $this->assertGuest();
        $this->assertSame('Your account has been disabled. Please contact an administrator.', session('status'));
    }
}
