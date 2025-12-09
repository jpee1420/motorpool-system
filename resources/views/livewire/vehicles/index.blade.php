<div class="py-10">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
        @if (session()->has('success'))
            <div class="rounded-lg bg-green-50 p-4 border border-green-200">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
                    </div>
                </div>
            </div>
        @endif
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900">
                    {{ $isStaffOrAbove ? __('Vehicles/Equipments') : __('My Vehicles') }}
                </h1>
                <p class="mt-1 text-sm text-gray-500">
                    {{ $isStaffOrAbove ? __('Manage your motorpool vehicles and their current status.') : __('View the vehicles assigned to you.') }}
                </p>
            </div>

            <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                    <div class="relative">
                        <input
                            type="text"
                            wire:model.live.debounce.300ms="search"
                            placeholder="{{ __('Search by plate, make, or model...') }}"
                            class="w-full sm:w-64 rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                        >
                    </div>

                    <div>
                        <select
                            wire:model.live="statusFilter"
                            class="w-full sm:w-48 rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        >
                            <option value="">{{ __('All statuses') }}</option>
                            <option value="operational">{{ __('Operational') }}</option>
                            <option value="non-operational">{{ __('Non-operational') }}</option>
                            <option value="maintenance">{{ __('Maintenance') }}</option>
                        </select>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    @if ($canExport)
                        <button
                            type="button"
                            wire:click="exportCsv"
                            wire:loading.attr="disabled"
                            wire:target="exportCsv"
                            class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 disabled:opacity-50"
                        >
                            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-3-3v6m7 5H5a2 2 0 01-2-2V5a2 2 0 012-2h7.586a1 1 0 01.707.293l5.414 5.414A1 1 0 0119 9.414V19a2 2 0 01-2 2z" />
                            </svg>
                            <span wire:loading.remove wire:target="exportCsv">{{ __('CSV') }}</span>
                            <span wire:loading wire:target="exportCsv">{{ __('...') }}</span>
                        </button>

                        <button
                            type="button"
                            wire:click="exportExcel"
                            wire:loading.attr="disabled"
                            wire:target="exportExcel"
                            class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 disabled:opacity-50"
                        >
                            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414A1 1 0 0119 9.414V19a2 2 0 01-2 2z" />
                            </svg>
                            <span wire:loading.remove wire:target="exportExcel">{{ __('Excel') }}</span>
                            <span wire:loading wire:target="exportExcel">{{ __('...') }}</span>
                        </button>
                    @endif

                    @if ($canCreate)
                        <button
                            type="button"
                            wire:click="openCreateModal"
                            wire:loading.attr="disabled"
                            class="inline-flex items-center justify-center rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-offset-2 disabled:opacity-50"
                        >
                            {{ __('Add vehicle') }}
                        </button>
                    @endif
                </div>
            </div>
        </div>

        <div class="bg-white shadow-sm rounded-xl border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-3 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                {{ __('Photo') }}
                            </th>
                            <th class="px-3 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                <button type="button" wire:click="sortBy('plate_number')" class="flex items-center gap-1">
                                    <span>{{ __('Plate number') }}</span>
                                    @if ($sortField === 'plate_number')
                                        <span class="text-[10px]">{{ $sortDirection === 'asc' ? '▲' : '▼' }}</span>
                                    @endif
                                </button>
                            </th>
                            <th class="px-3 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                <button type="button" wire:click="sortBy('make')" class="flex items-center gap-1">
                                    <span>{{ __('Make / Model') }}</span>
                                    @if ($sortField === 'make')
                                        <span class="text-[10px]">{{ $sortDirection === 'asc' ? '▲' : '▼' }}</span>
                                    @endif
                                </button>
                            </th>
                            <th class="px-3 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                <button type="button" wire:click="sortBy('year')" class="flex items-center gap-1">
                                    <span>{{ __('Year') }}</span>
                                    @if ($sortField === 'year')
                                        <span class="text-[10px]">{{ $sortDirection === 'asc' ? '▲' : '▼' }}</span>
                                    @endif
                                </button>
                            </th>
                            <th class="px-3 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                {{ __('Current odometer') }}
                            </th>
                            <th class="px-3 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                {{ __('Next maintenance') }}
                            </th>
                            <th class="px-3 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                <button type="button" wire:click="sortBy('status')" class="flex items-center gap-1">
                                    <span>{{ __('Status') }}</span>
                                    @if ($sortField === 'status')
                                        <span class="text-[10px]">{{ $sortDirection === 'asc' ? '▲' : '▼' }}</span>
                                    @endif
                                </button>
                            </th>
                            <th class="px-3 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                {{ __('Actions') }}
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white">
                        @forelse ($vehicles as $vehicle)
                            <tr>
                                <td class="px-3 py-3 whitespace-nowrap">
                                    @if ($vehicle->photo_path)
                                        <img
                                            src="{{ asset('storage/'.$vehicle->photo_path) }}"
                                            alt="{{ $vehicle->plate_number }}"
                                            class="h-10 w-16 rounded object-cover border border-gray-200"
                                        >
                                    @else
                                        <div class="flex h-10 w-16 items-center justify-center rounded border border-dashed border-gray-300 text-xs text-gray-400">
                                            {{ __('No photo') }}
                                        </div>
                                    @endif
                                </td>
                                <td class="px-3 py-3 whitespace-nowrap text-gray-900 font-medium">
                                    {{ $vehicle->plate_number }}
                                </td>
                                <td class="px-3 py-3 whitespace-nowrap text-gray-700">
                                    {{ trim($vehicle->make.' '.$vehicle->model) ?: '—' }}
                                </td>
                                <td class="px-3 py-3 whitespace-nowrap text-gray-700">
                                    {{ $vehicle->year ?? '—' }}
                                </td>
                                <td class="px-3 py-3 whitespace-nowrap text-gray-700">
                                    {{ number_format($vehicle->current_odometer) }} km
                                </td>
                                <td class="px-3 py-3 whitespace-nowrap text-gray-700">
                                    <div class="flex flex-col">
                                        <span>
                                            {{ $vehicle->next_maintenance_due_at?->format('M d, Y') ?? '—' }}
                                        </span>
                                        <span class="text-xs text-gray-500">
                                            {{ $vehicle->next_maintenance_due_odometer ? number_format($vehicle->next_maintenance_due_odometer).' km' : '' }}
                                        </span>
                                    </div>
                                </td>
                                <td class="px-3 py-3 whitespace-nowrap">
                                    @php
                                        $statusClasses = [
                                            'operational' => 'bg-green-100 text-green-800',
                                            'non-operational' => 'bg-red-100 text-red-800',
                                            'maintenance' => 'bg-yellow-100 text-yellow-800',
                                        ];
                                        $statusClass = $statusClasses[$vehicle->status] ?? 'bg-gray-100 text-gray-800';
                                    @endphp
                                    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $statusClass }}">
                                        {{ ucfirst(str_replace('-', ' ', $vehicle->status ?? 'unknown')) }}
                                    </span>
                                </td>
                                <td class="px-3 py-3 text-gray-700" x-data="{ confirmOpen: false }">
                                    <div class="flex items-center justify-end gap-2">
                                        <a
                                            href="{{ route('maintenance.index', ['vehicleFilter' => $vehicle->id]) }}"
                                            class="text-indigo-600 hover:text-indigo-900 text-xs font-medium"
                                        >
                                            {{ __('History') }}
                                        </a>

                                        @if ($canCreate)
                                            <button
                                                type="button"
                                                wire:click="edit({{ $vehicle->id }})"
                                                class="text-gray-600 hover:text-gray-900 text-xs font-medium"
                                            >
                                                {{ __('Edit') }}
                                            </button>
                                        @endif

                                        @if ($canDelete)
                                            <button
                                                type="button"
                                                x-on:click.prevent="confirmOpen = true"
                                                class="text-red-600 hover:text-red-800 text-xs font-medium"
                                            >
                                                {{ __('Delete') }}
                                            </button>

                                            {{-- Delete Confirmation Modal --}}
                                            <x-confirm-modal
                                                :title="__('Delete vehicle')"
                                                :description="__('Are you sure you want to delete this vehicle? This action cannot be undone.')"
                                                confirm-wire-click="delete({{ $vehicle->id }})"
                                            />
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-3 py-6 text-center text-gray-500">
                                    @if ($isStaffOrAbove)
                                        {{ __('No vehicles found. Add your first vehicle to get started.') }}
                                    @else
                                        {{ __('No vehicles have been assigned to you yet.') }}
                                    @endif
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="border-t border-gray-100 bg-gray-50 px-4 py-3 sm:px-6">
                {{ $vehicles->links() }}
            </div>
        </div>
    </div>

    @if ($showModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 px-4 py-6 overflow-y-auto">
            <div class="w-full max-w-2xl rounded-xl bg-white shadow-xl max-h-[90vh] overflow-y-auto">
                <div class="flex items-center justify-between border-b border-gray-100 px-6 py-4">
                    <h2 class="text-lg font-semibold text-gray-900">
                        {{ $editingId ? __('Edit vehicle') : __('Add vehicle') }}
                    </h2>
                    <button
                        type="button"
                        wire:click="$set('showModal', false)"
                        class="text-gray-400 hover:text-gray-600"
                    >
                        <span class="sr-only">{{ __('Close') }}</span>
                        ✕
                    </button>
                </div>

                <form wire:submit.prevent="save" class="px-6 py-5 space-y-5">
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">
                                {{ __('Vehicle type') }}
                            </label>
                            <input
                                type="text"
                                wire:model="vehicle_type"
                                class="mt-1 block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            >
                            @error('vehicle_type')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">
                                {{ __('Plate number') }}
                            </label>
                            <input
                                type="text"
                                wire:model.blur="plate_number"
                                maxlength="15"
                                class="mt-1 block w-full rounded-lg border-gray-300 text-sm shadow-sm uppercase focus:border-indigo-500 focus:ring-indigo-500"
                            >
                            @error('plate_number')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">
                                {{ __('Chassis number') }}
                            </label>
                            <input
                                type="text"
                                wire:model="chassis_number"
                                class="mt-1 block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            >
                            @error('chassis_number')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">
                                {{ __('Make') }}
                            </label>
                            <input
                                type="text"
                                wire:model="make"
                                class="mt-1 block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            >
                            @error('make')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">
                                {{ __('Year model') }}
                            </label>
                            <input
                                type="number"
                                wire:model="year"
                                class="mt-1 block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            >
                            @error('year')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">
                                {{ __('Engine number') }}
                            </label>
                            <input
                                type="text"
                                wire:model="engine_number"
                                class="mt-1 block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            >
                            @error('engine_number')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">
                                {{ __('Driver / Operator') }}
                            </label>
                            <select
                                wire:model="assigned_user_id"
                                class="mt-1 block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            >
                                <option value="">{{ __('No driver assigned') }}</option>
                                @foreach ($drivers as $driver)
                                    <option value="{{ $driver->id }}">{{ $driver->name }} ({{ $driver->email }})</option>
                                @endforeach
                            </select>
                            @error('assigned_user_id')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">
                                {{ __('Contact number') }}
                            </label>
                            <input
                                type="text"
                                wire:model="contact_number"
                                class="mt-1 block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            >
                            @error('contact_number')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">
                                {{ __('Status') }}
                            </label>
                            <select
                                wire:model="status"
                                class="mt-1 block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            >
                                <option value="operational">{{ __('Operational') }}</option>
                                <option value="non-operational">{{ __('Non-operational') }}</option>
                                <option value="maintenance">{{ __('Maintenance') }}</option>
                            </select>
                            @error('status')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">
                                {{ __('Photo (optional)') }}
                            </label>
                            <input
                                type="file"
                                wire:model="photo"
                                class="mt-1 block w-full text-sm text-gray-900 file:mr-4 file:rounded-lg file:border-0 file:bg-indigo-50 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-indigo-700 hover:file:bg-indigo-100"
                            >
                            @error('photo')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            <p class="mt-1 text-xs text-gray-500">
                                {{ __('JPEG or PNG, up to 5MB.') }}
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-3 border-t border-gray-100 pt-4">
                        <button
                            type="button"
                            wire:click="$set('showModal', false)"
                            class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
                        >
                            {{ __('Cancel') }}
                        </button>

                        <button
                            type="submit"
                            wire:loading.attr="disabled"
                            wire:target="save"
                            class="inline-flex items-center justify-center rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-offset-2 disabled:opacity-50"
                        >
                            <span wire:loading.remove wire:target="save">
                                {{ $editingId ? __('Update vehicle') : __('Save vehicle') }}
                            </span>
                            <span wire:loading wire:target="save">
                                {{ __('Saving...') }}
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
