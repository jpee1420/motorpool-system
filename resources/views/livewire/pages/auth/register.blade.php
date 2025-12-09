<?php

use App\Models\User;
use App\Notifications\NewUserRegistrationNotification;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\Rules;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';
    public bool $registrationComplete = false;

    /**
     * Handle an incoming registration request.
     */
    public function register(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
        ]);

        $validated['password'] = Hash::make($validated['password']);
        $validated['status'] = User::STATUS_PENDING;

        $user = User::create($validated);

        event(new Registered($user));

        // Notify admins/staff about the new registration
        $admins = User::whereIn('role', [User::ROLE_ADMIN, User::ROLE_STAFF])
            ->where('status', User::STATUS_ACTIVE)
            ->get();

        if ($admins->isNotEmpty()) {
            Notification::send($admins, new NewUserRegistrationNotification($user));
        }

        // Don't log in - show pending message instead
        $this->registrationComplete = true;
    }
}; ?>

<div>
    {{-- Title & Subtitle for guest layout --}}
    <x-slot name="title">
        {{ $registrationComplete ? __('Registration submitted') : __('Create your account') }}
    </x-slot>

    @if ($registrationComplete)
        {{-- Registration pending approval message --}}
        <div class="text-center space-y-4">
            <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-yellow-100">
                <svg class="h-8 w-8 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <h3 class="text-lg font-medium text-gray-900">{{ __('Awaiting approval') }}</h3>
            <p class="text-sm text-gray-600">
                {{ __('Your registration has been submitted successfully. An administrator will review your request and approve your account shortly.') }}
            </p>
            <p class="text-sm text-gray-500">
                {{ __('You will receive an email notification once your account has been approved.') }}
            </p>
            <div class="pt-4">
                <a
                    href="{{ route('login') }}"
                    class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
                    wire:navigate
                >
                    {{ __('Back to login') }}
                </a>
            </div>
        </div>
    @else
    <form wire:submit="register" class="space-y-4">
        <!-- Name -->
        <div>
            <x-input-label for="name" :value="__('Name')" class="text-gray-700" />
            <x-text-input
                wire:model="name"
                id="name"
                class="block mt-1.5 w-full"
                type="text"
                name="name"
                placeholder="Your full name"
                required
                autofocus
                autocomplete="name"
            />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Email address')" class="text-gray-700" />
            <x-text-input
                wire:model="email"
                id="email"
                class="block mt-1.5 w-full"
                type="email"
                name="email"
                placeholder="Email"
                required
                autocomplete="username"
            />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div>
            <x-input-label for="password" :value="__('Password')" class="text-gray-700" />
            <x-text-input
                wire:model="password"
                id="password"
                class="block mt-1.5 w-full"
                type="password"
                name="password"
                placeholder="Password"
                required
                autocomplete="new-password"
            />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Confirm Password -->
        <div>
            <x-input-label for="password_confirmation" :value="__('Confirm Password')" class="text-gray-700" />
            <x-text-input
                wire:model="password_confirmation"
                id="password_confirmation"
                class="block mt-1.5 w-full"
                type="password"
                name="password_confirmation"
                placeholder="Confirm your password"
                required
                autocomplete="new-password"
            />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <!-- Submit Button -->
        <div>
            <button
                type="submit"
                class="w-full flex justify-center py-2.5 px-4 bg-blue-400 border border-gray-300 rounded-full text-sm font-medium text-gray-1000 hover:bg-blue-500 hover:text-black-400 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition"
            >
                {{ __('Register') }}
            </button>
        </div>

        <!-- Login Link -->
        <p class="text-center text-sm text-gray-600">
            {{ __('Already have an account?') }}
            <a
                href="{{ route('login') }}"
                class="font-medium text-indigo-600 hover:text-indigo-500"
                wire:navigate
            >
                {{ __('Log in') }}
            </a>
        </p>
    </form>
    @endif
</div>
