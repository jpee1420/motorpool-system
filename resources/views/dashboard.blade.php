<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ $isStaffOrAbove ? __('Motorpool Dashboard') : __('My Dashboard') }}
                </h2>
                <p class="mt-1 text-sm text-gray-500">
                    {{ $isStaffOrAbove ? __('Overview of vehicles and maintenance activity.') : __('Overview of your assigned vehicles and maintenance.') }}
                </p>
            </div>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                <div class="bg-white shadow-sm rounded-xl border border-gray-100 p-6">
                    <p class="text-sm font-medium text-gray-500">{{ $isStaffOrAbove ? __('Total vehicles') : __('My vehicles') }}</p>
                    <p class="mt-3 text-3xl font-semibold text-gray-900">{{ $totalVehicles }}</p>
                </div>
                <div class="bg-white shadow-sm rounded-xl border border-gray-100 p-6">
                    <p class="text-sm font-medium text-gray-500">{{ __('Maintenance records') }}</p>
                    <p class="mt-3 text-3xl font-semibold text-gray-900">{{ $maintenanceRecordCount }}</p>
                </div>
                <div class="bg-white shadow-sm rounded-xl border border-gray-100 p-6">
                    <p class="text-sm font-medium text-gray-500">{{ __('Upcoming maintenance') }}</p>
                    <p class="mt-3 text-3xl font-semibold text-gray-900">{{ $upcomingMaintenanceCount }}</p>
                </div>
                <div class="bg-white shadow-sm rounded-xl border border-gray-100 p-6">
                    <p class="text-sm font-medium text-gray-500">{{ __('Overdue maintenance') }}</p>
                    <p class="mt-3 text-3xl font-semibold {{ $overdueMaintenanceCount > 0 ? 'text-red-600' : 'text-gray-900' }}">{{ $overdueMaintenanceCount }}</p>
                </div>
            </div>

            <div class="grid gap-6 lg:grid-cols-2">
                <div class="bg-white shadow-sm rounded-xl border border-gray-100 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                        <h3 class="text-sm font-semibold text-gray-900">{{ __('Upcoming maintenance') }}</h3>
                    </div>
                    <div class="px-6 py-4">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 text-sm">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-3 py-2 text-left font-medium text-gray-500 uppercase tracking-wider">{{ __('Plate') }}</th>
                                        <th class="px-3 py-2 text-left font-medium text-gray-500 uppercase tracking-wider">{{ __('Next date') }}</th>
                                        <th class="px-3 py-2 text-left font-medium text-gray-500 uppercase tracking-wider">{{ __('Next odometer') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 bg-white">
                                    @forelse ($upcomingMaintenance as $vehicle)
                                        <tr>
                                            <td class="px-3 py-2 whitespace-nowrap text-gray-900">{{ $vehicle->plate_number }}</td>
                                            <td class="px-3 py-2 whitespace-nowrap text-gray-700">
                                                {{ $vehicle->next_maintenance_due_at?->format('M d, Y') ?? '—' }}
                                            </td>
                                            <td class="px-3 py-2 whitespace-nowrap text-gray-700">
                                                {{ $vehicle->next_maintenance_due_odometer ? number_format($vehicle->next_maintenance_due_odometer) : '—' }}
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3" class="px-3 py-4 text-center text-gray-500">
                                                {{ __('No upcoming maintenance scheduled.') }}
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="bg-white shadow-sm rounded-xl border border-gray-100 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                        <h3 class="text-sm font-semibold text-gray-900">{{ __('Recent maintenance') }}</h3>
                    </div>
                    <div class="px-6 py-4">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 text-sm">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-3 py-2 text-left font-medium text-gray-500 uppercase tracking-wider">{{ __('Vehicle') }}</th>
                                        <th class="px-3 py-2 text-left font-medium text-gray-500 uppercase tracking-wider">{{ __('Performed at') }}</th>
                                        <th class="px-3 py-2 text-left font-medium text-gray-500 uppercase tracking-wider">{{ __('Odometer') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 bg-white">
                                    @forelse ($recentMaintenance as $record)
                                        <tr>
                                            <td class="px-3 py-2 whitespace-nowrap text-gray-900">
                                                {{ $record->vehicle?->plate_number ?? '—' }}
                                            </td>
                                            <td class="px-3 py-2 whitespace-nowrap text-gray-700">
                                                {{ $record->performed_at?->format('M d, Y') ?? '—' }}
                                            </td>
                                            <td class="px-3 py-2 whitespace-nowrap text-gray-700">
                                                {{ $record->odometer_reading ? number_format($record->odometer_reading) : '—' }}
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3" class="px-3 py-4 text-center text-gray-500">
                                                {{ __('No maintenance records yet.') }}
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            @if ($isStaffOrAbove)
                <div class="bg-white shadow-sm rounded-xl border border-gray-100 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                        <h3 class="text-sm font-semibold text-gray-900">{{ __('Recent notifications') }}</h3>
                    </div>
                    <div class="px-6 py-4">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 text-sm">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-3 py-2 text-left font-medium text-gray-500 uppercase tracking-wider">{{ __('Time') }}</th>
                                        <th class="px-3 py-2 text-left font-medium text-gray-500 uppercase tracking-wider">{{ __('Vehicle') }}</th>
                                        <th class="px-3 py-2 text-left font-medium text-gray-500 uppercase tracking-wider">{{ __('Type') }}</th>
                                        <th class="px-3 py-2 text-left font-medium text-gray-500 uppercase tracking-wider">{{ __('Channel') }}</th>
                                        <th class="px-3 py-2 text-left font-medium text-gray-500 uppercase tracking-wider">{{ __('Status') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 bg-white">
                                    @forelse ($recentNotifications as $log)
                                        <tr>
                                            <td class="px-3 py-2 whitespace-nowrap text-gray-700">
                                                {{ $log->sent_at?->diffForHumans() ?? '—' }}
                                            </td>
                                            <td class="px-3 py-2 whitespace-nowrap text-gray-900">
                                                {{ $log->vehicle?->plate_number ?? '—' }}
                                            </td>
                                            <td class="px-3 py-2 whitespace-nowrap text-gray-700">
                                                {{ $log->type }}
                                            </td>
                                            <td class="px-3 py-2 whitespace-nowrap text-gray-700">
                                                {{ strtoupper($log->channel) }}
                                            </td>
                                            <td class="px-3 py-2 whitespace-nowrap">
                                                @php
                                                    $statusClasses = [
                                                        'sent' => 'bg-green-100 text-green-800',
                                                        'failed' => 'bg-red-100 text-red-800',
                                                        'pending' => 'bg-yellow-100 text-yellow-800',
                                                    ];
                                                    $statusClass = $statusClasses[$log->status] ?? 'bg-gray-100 text-gray-800';
                                                @endphp
                                                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $statusClass }}">
                                                    {{ ucfirst($log->status) }}
                                                </span>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="px-3 py-4 text-center text-gray-500">
                                                {{ __('No notifications have been sent yet.') }}
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
