<?php

namespace Tests\Feature\Security;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Livewire\Volt\Volt;
use Tests\TestCase;

class RegressionPreventionTest extends TestCase
{
    use RefreshDatabase;

    public function test_pending_users_cannot_authenticate(): void
    {
        $user = User::factory()->create([
            'status' => User::STATUS_PENDING,
            'role' => User::ROLE_USER,
        ]);

        $component = Volt::test('pages.auth.login')
            ->set('form.email', $user->email)
            ->set('form.password', 'password');

        $component->call('login');

        $component->assertHasErrors(['form.email']);
        $this->assertGuest();
    }

    public function test_disabled_users_cannot_authenticate(): void
    {
        $user = User::factory()->create([
            'status' => User::STATUS_DISABLED,
            'role' => User::ROLE_USER,
        ]);

        $component = Volt::test('pages.auth.login')
            ->set('form.email', $user->email)
            ->set('form.password', 'password');

        $component->call('login');

        $component->assertHasErrors(['form.email']);
        $this->assertGuest();
    }

    public function test_login_is_rate_limited_after_repeated_failures(): void
    {
        $user = User::factory()->create();
        $key = Str::lower($user->email) . '|127.0.0.1';

        RateLimiter::clear($key);

        for ($attempt = 0; $attempt < 5; $attempt++) {
            $component = Volt::test('pages.auth.login')
                ->set('form.email', $user->email)
                ->set('form.password', 'wrong-password');

            $component->call('login');
        }

        $component = Volt::test('pages.auth.login')
            ->set('form.email', $user->email)
            ->set('form.password', 'wrong-password');

        $component->call('login');

        $component->assertHasErrors(['form.email']);
    }

    public function test_logged_out_users_are_terminated_and_redirected(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $component = Volt::test('layout.navigation');

        $component->call('logout');

        $component
            ->assertHasNoErrors()
            ->assertRedirect('/');

        $this->assertGuest();
    }

    public function test_staff_users_cannot_access_admin_only_user_management(): void
    {
        $staff = User::factory()->create([
            'role' => User::ROLE_STAFF,
            'status' => User::STATUS_ACTIVE,
        ]);

        $this->actingAs($staff)
            ->get('/account/users')
            ->assertForbidden();
    }

    public function test_regular_users_cannot_access_admin_only_approval_pages(): void
    {
        $user = User::factory()->create([
            'role' => User::ROLE_USER,
            'status' => User::STATUS_ACTIVE,
        ]);

        $this->actingAs($user)
            ->get('/approvals')
            ->assertForbidden();
    }
}
