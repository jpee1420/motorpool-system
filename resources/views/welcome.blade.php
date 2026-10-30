<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Motorpool System') }} – Welcome</title>
        <meta name="description" content="Motorpool System – Vehicle fleet management, maintenance tracking, trip tickets, and more.">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-gray-900 antialiased">
        <div class="min-h-screen flex flex-col justify-center items-center px-4 py-0 sm:py-4 bg-gray-50">
            {{-- Logo --}}
            <div class="mb-2">
                <x-application-logo variant="auth" class="h-16 w-auto mx-auto" />
            </div>

            {{-- Title --}}
            <h1 class="text-xl sm:text-2xl font-semibold text-gray-900 text-center mb-2">
                {{ __('Welcome to Motorpool System') }}
            </h1>

            {{-- Card --}}
            <div class="w-full sm:max-w-md px-6 py-6 bg-white shadow-sm border border-gray-200 sm:rounded-xl">
                <p class="text-sm text-gray-600 text-center mb-6">
                    {{ __('Manage your fleet vehicles, track maintenance schedules, handle trip tickets, and more — all in one place.') }}
                </p>

                {{-- Feature highlights --}}
                <div class="space-y-3 mb-6">
                    <div class="flex items-start gap-3">
                        <div class="flex-shrink-0 mt-0.5">
                            <svg class="h-5 w-5 text-indigo-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 0 0-3.213-9.193 2.056 2.056 0 0 0-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 0 0-10.026 0 1.106 1.106 0 0 0-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-800">{{ __('Vehicle Management') }}</p>
                            <p class="text-xs text-gray-500">{{ __('Track your fleet, odometers, and vehicle status') }}</p>
                        </div>
                    </div>

                    <div class="flex items-start gap-3">
                        <div class="flex-shrink-0 mt-0.5">
                            <svg class="h-5 w-5 text-indigo-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M11.42 15.17 17.25 21A2.652 2.652 0 0 0 21 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 1 1-3.586-3.586l6.837-5.63m5.108-.233c.55-.164 1.163-.188 1.743-.14a4.5 4.5 0 0 0 4.486-6.336l-3.276 3.277a3.004 3.004 0 0 1-2.25-2.25l3.276-3.276a4.5 4.5 0 0 0-6.336 4.486c.048.58.024 1.194-.14 1.743" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-800">{{ __('Maintenance & Repairs') }}</p>
                            <p class="text-xs text-gray-500">{{ __('Schedule and record maintenance with cost tracking') }}</p>
                        </div>
                    </div>

                    <div class="flex items-start gap-3">
                        <div class="flex-shrink-0 mt-0.5">
                            <svg class="h-5 w-5 text-indigo-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-800">{{ __('Trip Tickets & Calendar') }}</p>
                            <p class="text-xs text-gray-500">{{ __('Plan trips and view everything on a calendar') }}</p>
                        </div>
                    </div>
                </div>

                {{-- Action buttons --}}
                <div class="space-y-3">
                    <a
                        href="{{ route('login') }}"
                        class="w-full flex justify-center py-2.5 px-4 bg-blue-400 border border-gray-300 rounded-full text-sm font-medium text-gray-900 hover:bg-blue-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition"
                        wire:navigate
                    >
                        {{ __('Log in') }}
                    </a>

                    @if (Route::has('register'))
                        <a
                            href="{{ route('register') }}"
                            class="w-full flex justify-center py-2.5 px-4 bg-white border border-gray-300 rounded-full text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition"
                            wire:navigate
                        >
                            {{ __('Create an account') }}
                        </a>
                    @endif
                </div>
            </div>

            {{-- Footer --}}
            <p class="mt-6 text-xs text-gray-400 text-center">
                &copy; {{ date('Y') }} {{ config('app.name', 'Motorpool System') }}. {{ __('All rights reserved.') }}
            </p>
        </div>
    </body>
</html>
