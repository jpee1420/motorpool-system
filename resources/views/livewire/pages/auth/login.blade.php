<?php

use App\Livewire\Forms\LoginForm;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public LoginForm $form;

    /**
     * Handle an incoming authentication request.
     */
    public function login(): void
    {
        $this->validate();

        $this->form->authenticate();

        Session::regenerate();

        $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);
    }
}; ?>

<div>
    {{-- Title & Subtitle for guest layout --}}
    <x-slot name="title">
        {{ __('Log in to your account') }}
    </x-slot>

    <x-slot name="subtitle">
        {{ __('Enter your email and password below to log in') }}
    </x-slot>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form wire:submit="login" class="space-y-4">
        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Email address')" class="text-gray-700" />
            <x-text-input
                wire:model="form.email"
                id="email"
                class="block mt-1.5 w-full"
                type="email"
                name="email"
                placeholder="Email"
                required
                autofocus
                autocomplete="username"
            />
            <x-input-error :messages="$errors->get('form.email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div>
            <div class="flex items-center justify-between">
                <x-input-label for="password" :value="__('Password')" class="text-gray-700" />
                @if (Route::has('password.request'))
                    <a
                        class="text-sm text-indigo-600 hover:text-indigo-500 font-medium"
                        href="{{ route('password.request') }}"
                        wire:navigate
                    >
                        {{ __('Forgot your password?') }}
                    </a>
                @endif
            </div>

            <x-text-input
                wire:model="form.password"
                id="password"
                class="block mt-1.5 w-full"
                type="password"
                name="password"
                placeholder="Password"
                required
                autocomplete="current-password"
            />
            <x-input-error :messages="$errors->get('form.password')" class="mt-2" />
        </div>

        <!-- Remember Me -->
        <div class="flex items-center">
            <input
                wire:model="form.remember"
                id="remember"
                type="checkbox"
                class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                name="remember"
            >
            <label for="remember" class="ms-2 text-sm text-gray-600">
                {{ __('Remember me') }}
            </label>
        </div>

        <!-- Submit Button -->
        <div>
            <button
                type="submit"
                class="w-full flex justify-center py-2.5 px-4 bg-blue-400 border border-gray-300 rounded-full text-sm font-medium text-gray-1000 hover:bg-blue-500 hover:text-black-400 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition"
            >
                {{ __('Log in') }}
            </button>
        </div>

        <!-- Sign Up Link -->
        <p class="text-center text-sm text-gray-600">
            {{ __("Don't have an account?") }}
            <a
                href="{{ route('register') }}"
                class="font-medium text-indigo-600 hover:text-indigo-500"
                wire:navigate
            >
                {{ __('Sign up') }}
            </a>
        </p>
    </form>
</div>
