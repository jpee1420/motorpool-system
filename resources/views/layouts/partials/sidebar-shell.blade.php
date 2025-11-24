<div x-data="{ sidebarOpen: false }" class="min-h-screen bg-gray-100 flex">
    <!-- Desktop sidebar -->
    <div class="hidden md:flex md:flex-col md:w-64 bg-white border-r border-gray-200">
        <div class="h-16 flex items-center px-4 border-b border-gray-200">
            <a href="{{ route('dashboard') }}" class="flex items-center gap-2" wire:navigate>
                <x-application-logo class="block h-8 w-auto fill-current text-indigo-600" />
                <span class="text-base font-semibold text-gray-900">
                    {{ config('app.name', 'Motorpool') }}
                </span>
            </a>
        </div>

        @php
            $navItems = [
                ['label' => __('Dashboard'), 'route' => 'dashboard', 'pattern' => 'dashboard'],
                ['label' => __('Vehicles'), 'route' => 'vehicles.index', 'pattern' => 'vehicles.*'],
                ['label' => __('Maintenance'), 'route' => 'maintenance.index', 'pattern' => 'maintenance.*'],
                ['label' => __('Notifications'), 'route' => 'notifications.index', 'pattern' => 'notifications.*'],
                ['label' => __('Calendar'), 'route' => 'calendar.index', 'pattern' => 'calendar.*'],
            ];
        @endphp

        <nav class="flex-1 px-2 py-4 space-y-1">
            @foreach ($navItems as $item)
                @php
                    $isActive = request()->routeIs($item['pattern']);
                @endphp
                <a
                    href="{{ route($item['route']) }}"
                    wire:navigate
                    class="group flex items-center px-3 py-2 text-sm font-medium rounded-lg border-l-4 {{ $isActive ? 'bg-indigo-50 border-indigo-500 text-indigo-700' : 'border-transparent text-gray-700 hover:bg-gray-50 hover:border-gray-300 hover:text-gray-900' }}"
                >
                    <span>{{ $item['label'] }}</span>
                </a>
            @endforeach
        </nav>

        <div class="border-t border-gray-200 px-4 py-4 text-sm text-gray-700">
            @auth
                <div class="font-medium text-gray-900">{{ auth()->user()->name }}</div>
                <div class="text-xs text-gray-500">{{ auth()->user()->email }}</div>

                <form method="POST" action="{{ route('logout') }}" class="mt-3">
                    @csrf
                    <button
                        type="submit"
                        class="inline-flex items-center rounded-lg border border-gray-300 px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-50"
                    >
                        {{ __('Log Out') }}
                    </button>
                </form>
            @endauth
        </div>
    </div>

    <!-- Mobile sidebar -->
    <div
        x-show="sidebarOpen"
        x-transition.opacity
        class="fixed inset-0 z-40 flex md:hidden"
    >
        <div
            x-on:click="sidebarOpen = false"
            class="fixed inset-0 bg-black/40"
        ></div>

        <div
            x-transition:enter="transform transition ease-in-out duration-200"
            x-transition:enter-start="-translate-x-full"
            x-transition:enter-end="translate-x-0"
            x-transition:leave="transform transition ease-in-out duration-200"
            x-transition:leave-start="translate-x-0"
            x-transition:leave-end="-translate-x-full"
            class="relative z-50 flex flex-col w-64 bg-white border-r border-gray-200"
        >
            <div class="h-16 flex items-center justify-between px-4 border-b border-gray-200">
                <span class="text-base font-semibold text-gray-900">
                    {{ config('app.name', 'Motorpool') }}
                </span>
                <button
                    type="button"
                    x-on:click="sidebarOpen = false"
                    class="text-gray-400 hover:text-gray-600"
                >
                    ✕
                </button>
            </div>

            <nav class="flex-1 px-2 py-4 space-y-1">
                @foreach ($navItems as $item)
                    @php
                        $isActive = request()->routeIs($item['pattern']);
                    @endphp
                    <a
                        href="{{ route($item['route']) }}"
                        wire:navigate
                        class="group flex items-center px-3 py-2 text-sm font-medium rounded-lg border-l-4 {{ $isActive ? 'bg-indigo-50 border-indigo-500 text-indigo-700' : 'border-transparent text-gray-700 hover:bg-gray-50 hover:border-gray-300 hover:text-gray-900' }}"
                        x-on:click="sidebarOpen = false"
                    >
                        <span>{{ $item['label'] }}</span>
                    </a>
                @endforeach
            </nav>
        </div>
    </div>

    <!-- Main content area -->
    <div class="flex-1 flex flex-col min-w-0">
        <header class="h-16 bg-white border-b border-gray-200 flex items-center justify-between px-4 sm:px-6 lg:px-8">
            <div class="flex items-center gap-3">
                <button
                    type="button"
                    class="md:hidden inline-flex items-center justify-center rounded-md p-2 text-gray-500 hover:text-gray-700 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-700"
                    x-on:click="sidebarOpen = true"
                >
                    <span class="sr-only">{{ __('Open navigation') }}</span>
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>

                <span class="text-base font-semibold text-gray-900 hidden sm:inline">
                    {{ config('app.name', 'Motorpool') }}
                </span>
            </div>

            @auth
                <div class="flex items-center gap-3">
                    <div class="hidden sm:flex flex-col text-right">
                        <span class="text-sm font-medium text-gray-900">{{ auth()->user()->name }}</span>
                        <span class="text-xs text-gray-500">{{ auth()->user()->email }}</span>
                    </div>
                </div>
            @endauth
        </header>

        <main class="flex-1 flex flex-col min-w-0">
            @if (isset($header))
                <div class="bg-gray-50 border-b border-gray-200">
                    <div class="max-w-7xl mx-auto py-4 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </div>
            @endif

            <div class="flex-1 max-w-7xl mx-auto w-full px-4 sm:px-6 lg:px-8 py-6">
                {{ $slot }}
            </div>
        </main>
    </div>
</div>
