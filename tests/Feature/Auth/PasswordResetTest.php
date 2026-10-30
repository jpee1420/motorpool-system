<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Notifications\PasswordResetRequestNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Volt\Volt;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_reset_password_link_screen_can_be_rendered(): void
    {
        $response = $this->get('/forgot-password');

        $response
            ->assertSeeVolt('pages.auth.forgot-password')
            ->assertStatus(200);
    }

    public function test_reset_password_request_can_be_submitted(): void
    {
        Notification::fake();

        $user = User::factory()->create();
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN, 'status' => User::STATUS_ACTIVE]);

        Volt::test('pages.auth.forgot-password')
            ->set('email', $user->email)
            ->call('sendPasswordResetRequest');

        Notification::assertSentTo($admin, PasswordResetRequestNotification::class);
    }

    public function test_reset_password_requests_are_rate_limited(): void
    {
        Notification::fake();

        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';

        $user = User::factory()->create();
        $key = strtolower($user->email) . '|127.0.0.1';

        RateLimiter::clear($key);

        for ($attempt = 0; $attempt < 5; $attempt++) {
            Volt::test('pages.auth.forgot-password')
                ->set('email', $user->email)
                ->call('sendPasswordResetRequest');
        }

        Volt::test('pages.auth.forgot-password')
            ->set('email', $user->email)
            ->call('sendPasswordResetRequest')
            ->assertHasErrors('email');
    }
}
