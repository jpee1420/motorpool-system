<?php

use App\Models\User;
use App\Notifications\PasswordResetRequestNotification;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public string $email = '';

    /**
     * Send a password reset request to admins/staff.
     *
     * Uses generic responses so we do not reveal whether an email exists.
     */
    public function sendPasswordResetRequest(): void
    {
        $this->validate([
            'email' => ['required', 'string', 'email', 'max:255'],
        ]);

        $this->ensureIsNotRateLimited();

        $user = User::where('email', $this->email)->first();

        if ($user !== null) {
            // Notify all admins and staff about the password reset request
            $adminsAndStaff = User::whereIn('role', [User::ROLE_ADMIN, User::ROLE_STAFF])
                ->where('status', User::STATUS_ACTIVE)
                ->get();

            foreach ($adminsAndStaff as $admin) {
                $admin->notify(new PasswordResetRequestNotification($user));
            }
        }

        RateLimiter::hit($this->throttleKey());

        $this->reset('email');

        // Generic message that does not confirm whether the email exists
        session()->flash('status', __('If an account exists for that email, our staff will review your request and contact you.'));
    }

    protected function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout(request()));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    protected function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->email).'|'.request()->ip());
    }
}; ?>

<div>
    <div class="mb-4 text-sm text-gray-600">
        {{ __('Forgot your password? Enter your email address below and our staff will reset your password for you.') }}
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form wire:submit="sendPasswordResetRequest">
        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input wire:model="email" id="email" class="block mt-1 w-full" type="email" name="email" required autofocus />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="flex items-center justify-between mt-4">
            <a href="{{ route('login') }}" class="text-sm text-indigo-600 hover:text-indigo-500 font-medium" wire:navigate>
                {{ __('Back to login') }}
            </a>
            <x-primary-button>
                {{ __('Request Password Reset') }}
            </x-primary-button>
        </div>
    </form>
</div>
