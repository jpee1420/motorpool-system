<div class="relative" x-data="{ open: @entangle('showDropdown') }" @click.away="open = false">
    <!-- Bell Button -->
    <button
        type="button"
        @click="open = !open"
        class="relative inline-flex items-center justify-center p-2 rounded-full text-gray-500 hover:text-gray-700 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition"
        aria-label="{{ __('Notifications') }}"
    >
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
        </svg>

        <!-- Unread Badge -->
        @if ($unreadCount > 0)
            <span class="absolute -top-0.5 -right-0.5 inline-flex items-center justify-center w-5 h-5 text-xs font-bold text-white bg-red-500 rounded-full">
                {{ $unreadCount > 99 ? '99+' : $unreadCount }}
            </span>
        @endif
    </button>

    <!-- Dropdown Panel -->
    <div
        x-show="open"
        x-cloak
        x-transition:enter="transition ease-out duration-100"
        x-transition:enter-start="transform opacity-0 scale-95"
        x-transition:enter-end="transform opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-75"
        x-transition:leave-start="transform opacity-100 scale-100"
        x-transition:leave-end="transform opacity-0 scale-95"
        class="absolute right-0 z-50 mt-2 w-80 sm:w-96 origin-top-right rounded-xl bg-white shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none"
    >
        <!-- Header -->
        <div class="flex items-center justify-between px-4 py-3 border-b border-gray-100">
            <h3 class="text-sm font-semibold text-gray-900">{{ __('Notifications') }}</h3>
            @if ($unreadCount > 0)
                <button
                    type="button"
                    wire:click="markAllAsRead"
                    class="text-xs font-medium text-indigo-600 hover:text-indigo-800"
                >
                    {{ __('Mark all as read') }}
                </button>
            @endif
        </div>

        <!-- Notifications List -->
        <div class="max-h-80 overflow-y-auto">
            @forelse ($notifications as $notification)
                @php
                    $meta = $notification->meta ?? [];
                    $isUnread = $notification->read_at === null;
                @endphp
                <div
                    wire:click="markAsRead({{ $notification->id }})"
                    class="block px-4 py-3 border-b border-gray-50 hover:bg-gray-50 transition cursor-pointer {{ $isUnread ? 'bg-blue-50/50' : '' }}"
                >
                    <div class="flex items-start gap-3">
                        <!-- Icon based on type -->
                        <div class="flex-shrink-0 mt-0.5">
                            @if (str_starts_with($notification->type, 'trip_ticket'))
                                <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-indigo-100">
                                    <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2a2 2 0 012-2h2m4-2a2 2 0 012 2v6h-3m-6 0H5a2 2 0 01-2-2v-4a2 2 0 012-2h1m3-4h3m0 0l2-2m-2 2l-2-2" />
                                    </svg>
                                </span>
                            @elseif ($notification->type === 'maintenance_overdue')
                                <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-red-100">
                                    <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                    </svg>
                                </span>
                            @elseif ($notification->type === 'maintenance_due')
                                <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-amber-100">
                                    <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </span>
                            @else
                                <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-sky-100">
                                    <svg class="w-4 h-4 text-sky-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                </span>
                            @endif
                        </div>

                        <!-- Content -->
                        <div class="flex-1 min-w-0">
                            @if (str_starts_with($notification->type, 'trip_ticket'))
                                <p class="text-sm font-medium text-gray-900">
                                    {{ $notification->vehicle?->plate_number ?? __('Vehicle') }}
                                    <span class="font-normal text-gray-600">{{ __('trip ticket') }}</span>
                                    <span class="text-indigo-600">
                                        {{ $notification->type_label }}
                                    </span>
                                </p>
                                <p class="text-xs text-gray-500 mt-0.5">
                                    @if (!empty($meta['destination']))
                                        {{ $meta['destination'] }}
                                    @endif
                                    @if (!empty($meta['departure_at']))
                                        @if (!empty($meta['destination'])) · @endif
                                        {{ $meta['departure_at'] }}
                                    @endif
                                </p>
                            @else
                                <p class="text-sm font-medium text-gray-900">
                                    {{ $notification->vehicle?->plate_number ?? __('Vehicle') }}
                                    <span class="font-normal text-gray-600">{{ __('maintenance') }}</span>
                                    <span class="{{ $notification->type === 'maintenance_overdue' ? 'text-red-600' : ($notification->type === 'maintenance_due' ? 'text-amber-600' : 'text-sky-600') }}">
                                        {{ $notification->type_label }}
                                    </span>
                                </p>
                                <p class="text-xs text-gray-500 mt-0.5">
                                    @if (!empty($meta['next_maintenance_due_at']))
                                        {{ __('Due:') }} {{ $meta['next_maintenance_due_at'] }}
                                    @endif
                                    @if (!empty($meta['next_maintenance_due_odometer']))
                                        @if (!empty($meta['next_maintenance_due_at'])) · @endif
                                        {{ number_format($meta['next_maintenance_due_odometer']) }} km
                                    @endif
                                </p>
                            @endif
                            <p class="text-xs text-gray-400 mt-1">
                                {{ $notification->created_at?->diffForHumans() }}
                            </p>
                        </div>

                        <!-- Unread indicator -->
                        @if ($isUnread)
                            <div class="flex-shrink-0">
                                <span class="inline-block w-2 h-2 bg-indigo-500 rounded-full"></span>
                            </div>
                        @endif
                    </div>
                </div>
            @empty
                <div class="px-4 py-8 text-center">
                    <svg class="mx-auto w-12 h-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                    </svg>
                    <p class="mt-2 text-sm text-gray-500">{{ __('No notifications yet') }}</p>
                </div>
            @endforelse
        </div>

        <!-- Footer -->
        <div class="px-4 py-3 border-t border-gray-100 bg-gray-50 rounded-b-xl">
            <a
                href="{{ route('notifications.index') }}"
                class="block text-center text-sm font-medium text-indigo-600 hover:text-indigo-800"
                @click="open = false"
            >
                {{ __('View all notifications') }}
            </a>
        </div>
    </div>
</div>
