<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-gray-900 antialiased">
        <div class="min-h-screen flex flex-col justify-center items-center px-4 py-0 sm:py-4 bg-gray-50">
            {{-- Logo --}}
            <div class="mb-2">
                <a href="/" wire:navigate>
                    <x-application-logo variant="auth" class="h-16 w-auto mx-auto" />
                </a>
            </div>

            {{-- Title & Subtitle (provided via $title and $subtitle slots, or defaults) --}}
            @isset($title)
                <h1 class="text-xl sm:text-2xl font-semibold text-gray-900 text-center mb-2">
                    {{ $title }}
                </h1>
            @endisset
{{-- 
            @isset($subtitle)
                <p class="text-sm text-gray-500 text-center mb-6 max-w-sm">
                    {{ $subtitle }}
                </p>
            @endisset --}}

            {{-- Card --}}
            <div class="w-full sm:max-w-md px-6 py-4 bg-white shadow-sm border border-gray-200 sm:rounded-xl">
                {{ $slot }}
            </div>
        </div>
    </body>
</html>
