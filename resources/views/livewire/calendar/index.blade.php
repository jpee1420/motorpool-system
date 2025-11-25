<div class="py-10">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900">
                    {{ __('Calendar') }}
                </h1>
                <p class="mt-1 text-sm text-gray-500">
                    {{ __('View maintenance schedules, trip tickets, and upcoming due dates.') }}
                </p>
            </div>

            <div class="flex items-center gap-2">
                <button
                    type="button"
                    wire:click="goToPreviousMonth"
                    class="inline-flex items-center rounded-lg border border-gray-300 px-3 py-1.5 text-sm text-gray-700 hover:bg-gray-50"
                >
                    ‹
                </button>

                <button
                    type="button"
                    wire:click="goToToday"
                    class="px-3 py-1.5 text-sm font-semibold text-gray-900 bg-white rounded-lg border border-gray-200 hover:bg-gray-50 min-w-[140px]"
                >
                    {{ $month->format('F Y') }}
                </button>

                <button
                    type="button"
                    wire:click="goToNextMonth"
                    class="inline-flex items-center rounded-lg border border-gray-300 px-3 py-1.5 text-sm text-gray-700 hover:bg-gray-50"
                >
                    ›
                </button>

                <div class="ml-4 flex items-center rounded-lg border border-gray-200 p-0.5 bg-gray-50">
                    <button
                        type="button"
                        wire:click="setView('calendar')"
                        class="px-3 py-1 text-xs font-medium rounded-md {{ $view === 'calendar' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-600 hover:text-gray-900' }}"
                    >
                        {{ __('Calendar') }}
                    </button>
                    <button
                        type="button"
                        wire:click="setView('list')"
                        class="px-3 py-1 text-xs font-medium rounded-md {{ $view === 'list' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-600 hover:text-gray-900' }}"
                    >
                        {{ __('List') }}
                    </button>
                </div>
            </div>
        </div>

        {{-- Legend --}}
        <div class="flex flex-wrap items-center gap-4 text-xs">
            <div class="flex items-center gap-1.5">
                <span class="w-3 h-3 rounded-full bg-green-500"></span>
                <span class="text-gray-600">{{ __('Maintenance performed') }}</span>
            </div>
            <div class="flex items-center gap-1.5">
                <span class="w-3 h-3 rounded-full bg-amber-500"></span>
                <span class="text-gray-600">{{ __('Maintenance due') }}</span>
            </div>
            <div class="flex items-center gap-1.5">
                <span class="w-3 h-3 rounded-full bg-indigo-500"></span>
                <span class="text-gray-600">{{ __('Trip scheduled') }}</span>
            </div>
        </div>

        @if ($view === 'calendar')
            {{-- Calendar Grid View --}}
            <div class="bg-white shadow-sm rounded-xl border border-gray-100 overflow-hidden">
                {{-- Weekday Headers --}}
                <div class="grid grid-cols-7 border-b border-gray-100 bg-gray-50">
                    @foreach (['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'] as $dayName)
                        <div class="py-2 text-center text-xs font-medium uppercase tracking-wider text-gray-500">
                            {{ __($dayName) }}
                        </div>
                    @endforeach
                </div>

                {{-- Calendar Days --}}
                <div class="grid grid-cols-7">
                    @foreach ($days as $day)
                        <div class="min-h-[100px] border-b border-r border-gray-100 p-1 {{ !$day['isCurrentMonth'] ? 'bg-gray-50' : '' }} {{ $day['isToday'] ? 'bg-indigo-50' : '' }}">
                            <div class="flex items-center justify-between mb-1">
                                <span class="text-xs font-medium {{ $day['isCurrentMonth'] ? ($day['isToday'] ? 'text-indigo-600' : 'text-gray-900') : 'text-gray-400' }}">
                                    {{ $day['date']->format('j') }}
                                </span>
                                @if ($day['isToday'])
                                    <span class="text-[10px] font-medium text-indigo-600 uppercase">{{ __('Today') }}</span>
                                @endif
                            </div>

                            <div class="space-y-0.5">
                                {{-- Performed Maintenance --}}
                                @foreach ($day['performed']->take(2) as $record)
                                    <a
                                        href="{{ route('maintenance.show', $record) }}"
                                        class="block px-1 py-0.5 text-[10px] rounded bg-green-100 text-green-800 truncate hover:bg-green-200"
                                        title="{{ $record->vehicle?->plate_number }} - {{ $record->description_of_work }}"
                                    >
                                        ✓ {{ $record->vehicle?->plate_number }}
                                    </a>
                                @endforeach

                                {{-- Maintenance Due from Records --}}
                                @foreach ($day['due']->take(2) as $record)
                                    <a
                                        href="{{ route('maintenance.show', $record) }}"
                                        class="block px-1 py-0.5 text-[10px] rounded bg-amber-100 text-amber-800 truncate hover:bg-amber-200"
                                        title="{{ $record->vehicle?->plate_number }} - Due"
                                    >
                                        ⏰ {{ $record->vehicle?->plate_number }}
                                    </a>
                                @endforeach

                                {{-- Vehicles Due --}}
                                @foreach ($day['vehiclesDue']->take(2) as $vehicle)
                                    <a
                                        href="{{ route('maintenance.index', ['vehicleFilter' => $vehicle->id]) }}"
                                        class="block px-1 py-0.5 text-[10px] rounded bg-amber-100 text-amber-800 truncate hover:bg-amber-200"
                                        title="{{ $vehicle->plate_number }} - Maintenance due"
                                    >
                                        ⚠ {{ $vehicle->plate_number }}
                                    </a>
                                @endforeach

                                {{-- Trip Tickets --}}
                                @foreach ($day['trips']->take(2) as $trip)
                                    <a
                                        href="{{ route('trip-tickets.show', $trip) }}"
                                        class="block px-1 py-0.5 text-[10px] rounded bg-indigo-100 text-indigo-800 truncate hover:bg-indigo-200"
                                        title="{{ $trip->vehicle?->plate_number }} - {{ $trip->destination }}"
                                    >
                                        🚗 {{ $trip->vehicle?->plate_number }}
                                    </a>
                                @endforeach

                                {{-- More indicator --}}
                                @php
                                    $totalEvents = $day['performed']->count() + $day['due']->count() + $day['vehiclesDue']->count() + $day['trips']->count();
                                    $shown = min($day['performed']->count(), 2) + min($day['due']->count(), 2) + min($day['vehiclesDue']->count(), 2) + min($day['trips']->count(), 2);
                                @endphp
                                @if ($totalEvents > $shown)
                                    <span class="text-[10px] text-gray-500 pl-1">+{{ $totalEvents - $shown }} {{ __('more') }}</span>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @else
            {{-- List View --}}
            <div class="bg-white shadow-sm rounded-xl border border-gray-100 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-3 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                    {{ __('Date') }}
                                </th>
                                <th class="px-3 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                    {{ __('Type') }}
                                </th>
                                <th class="px-3 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                    {{ __('Vehicle') }}
                                </th>
                                <th class="px-3 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                    {{ __('Details') }}
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            @forelse ($maintenanceRecords as $record)
                                <tr>
                                    <td class="px-3 py-3 whitespace-nowrap text-gray-900">
                                        {{ $record->performed_at?->format('M d, Y') }}
                                    </td>
                                    <td class="px-3 py-3 whitespace-nowrap">
                                        <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium bg-green-100 text-green-800">
                                            {{ __('Performed') }}
                                        </span>
                                    </td>
                                    <td class="px-3 py-3 whitespace-nowrap text-gray-900 font-medium">
                                        <a href="{{ route('maintenance.show', $record) }}" class="text-indigo-600 hover:text-indigo-900">
                                            {{ $record->vehicle?->plate_number ?? '—' }}
                                        </a>
                                    </td>
                                    <td class="px-3 py-3 text-gray-700">
                                        {{ Str::limit($record->description_of_work, 60) }}
                                    </td>
                                </tr>
                            @empty
                            @endforelse

                            @foreach ($vehiclesDue as $vehicle)
                                <tr>
                                    <td class="px-3 py-3 whitespace-nowrap text-gray-900">
                                        {{ $vehicle->next_maintenance_due_at?->format('M d, Y') }}
                                    </td>
                                    <td class="px-3 py-3 whitespace-nowrap">
                                        <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium bg-amber-100 text-amber-800">
                                            {{ __('Due') }}
                                        </span>
                                    </td>
                                    <td class="px-3 py-3 whitespace-nowrap text-gray-900 font-medium">
                                        <a href="{{ route('maintenance.index', ['vehicleFilter' => $vehicle->id]) }}" class="text-indigo-600 hover:text-indigo-900">
                                            {{ $vehicle->plate_number }}
                                        </a>
                                    </td>
                                    <td class="px-3 py-3 text-gray-700">
                                        {{ $vehicle->next_maintenance_due_odometer ? 'Due at ' . number_format($vehicle->next_maintenance_due_odometer) . ' km' : '' }}
                                    </td>
                                </tr>
                            @endforeach

                            @foreach ($tripTickets as $trip)
                                <tr>
                                    <td class="px-3 py-3 whitespace-nowrap text-gray-900">
                                        {{ $trip->departure_at?->format('M d, Y H:i') }}
                                    </td>
                                    <td class="px-3 py-3 whitespace-nowrap">
                                        <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium bg-indigo-100 text-indigo-800">
                                            {{ __('Trip') }}
                                        </span>
                                    </td>
                                    <td class="px-3 py-3 whitespace-nowrap text-gray-900 font-medium">
                                        <a href="{{ route('trip-tickets.show', $trip) }}" class="text-indigo-600 hover:text-indigo-900">
                                            {{ $trip->vehicle?->plate_number ?? '—' }}
                                        </a>
                                    </td>
                                    <td class="px-3 py-3 text-gray-700">
                                        {{ $trip->destination }} - {{ $trip->driver_name }}
                                    </td>
                                </tr>
                            @endforeach

                            @if ($maintenanceRecords->isEmpty() && $vehiclesDue->isEmpty() && $tripTickets->isEmpty())
                                <tr>
                                    <td colspan="4" class="px-3 py-6 text-center text-gray-500">
                                        {{ __('No events for this month.') }}
                                    </td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>
</div>
