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
                    {{ __('Maintenance records') }}
                </h1>
                <p class="mt-1 text-sm text-gray-500">
                    {{ __('Log and review maintenance work and materials used for your vehicles.') }}
                </p>
            </div>

            <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
                    <select
                        wire:model.live="vehicleFilter"
                        class="w-full sm:w-48 rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    >
                        <option value="">{{ __('All vehicles') }}</option>
                        @foreach ($vehicles as $vehicle)
                            <option value="{{ $vehicle->id }}">{{ $vehicle->plate_number }}</option>
                        @endforeach
                    </select>

                    @auth
                        @if (auth()->user()?->isStaffOrAbove())
                            <button
                                type="button"
                                wire:click="runMaintenanceCheck"
                                wire:loading.attr="disabled"
                                wire:target="runMaintenanceCheck"
                                class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 disabled:opacity-50"
                            >
                                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h4l2 2h8a1 1 0 011 1v1M4 7h16M4 11h16M4 15h10M4 19h6" />
                                </svg>
                                <span wire:loading.remove wire:target="runMaintenanceCheck">{{ __('Run maintenance check') }}</span>
                                <span wire:loading wire:target="runMaintenanceCheck">{{ __('Running...') }}</span>
                            </button>
                        @endif
                    @endauth
                </div>

                @if ($canExport || $canCreate)
                    <div class="flex flex-wrap items-center gap-2 sm:justify-end">
                        @if ($canExport)
                            <button
                                type="button"
                                wire:click="exportCsv"
                                wire:loading.attr="disabled"
                                wire:target="exportCsv"
                                class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 disabled:opacity-50"
                            >
                                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
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
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
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
                                class="inline-flex items-center justify-center rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-offset-2 disabled:opacity-50 w-full sm:w-auto sm:ml-2"
                            >
                                {{ __('Add maintenance record') }}
                            </button>
                        @endif
                    </div>
                @endif
            </div>
        </div>

        <div class="bg-white shadow-sm rounded-xl border border-gray-100 overflow-hidden">
            <div class="sm:hidden bg-gray-50 p-4">
                <div class="space-y-3">
                    @forelse ($records as $record)
                        <div class="bg-white rounded-lg shadow-sm p-4">
                            <div class="flex items-start justify-between mb-3">
                                <div>
                                    <h3 class="text-base font-semibold text-gray-900">{{ $record->vehicle?->plate_number ?? '—' }}</h3>
                                    <p class="text-sm text-gray-600 mt-0.5">{{ optional($record->performed_at)->format('M d, Y') ?? '—' }}</p>
                                </div>
                                <a
                                    href="{{ route('maintenance.show', $record) }}"
                                    class="text-blue-600 text-sm font-medium"
                                >
                                    {{ __('View') }}
                                </a>
                            </div>

                            <div class="space-y-2 border-t border-gray-100 pt-3">
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-600">{{ __('Odometer') }}</span>
                                    <span class="font-medium text-gray-900">{{ number_format($record->odometer_reading) }} km</span>
                                </div>
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-600">{{ __('Labor cost') }}</span>
                                    <span class="font-medium text-gray-900">₱{{ number_format($record->personnel_labor_cost, 2) }}</span>
                                </div>
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-600">{{ __('Materials cost') }}</span>
                                    <span class="font-medium text-gray-900">₱{{ number_format($record->materials_cost_total, 2) }}</span>
                                </div>
                                <div class="flex justify-between text-sm pt-2 border-t border-gray-100">
                                    <span class="text-gray-900 font-medium">{{ __('Total cost') }}</span>
                                    <span class="font-semibold text-gray-900">₱{{ number_format($record->total_cost, 2) }}</span>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="bg-white rounded-lg shadow-sm p-8 text-center">
                            <p class="text-gray-500">{{ __('No maintenance records yet.') }}</p>
                        </div>
                    @endforelse
                </div>
            </div>

            <div class="hidden sm:block overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-3 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                {{ __('Vehicle') }}
                            </th>
                            <th class="px-3 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                <button type="button" wire:click="sortBy('performed_at')" class="flex items-center gap-1">
                                    <span>{{ __('Performed at') }}</span>
                                    @if ($sortField === 'performed_at')
                                        <span class="text-[10px]">{{ $sortDirection === 'asc' ? '▲' : '▼' }}</span>
                                    @endif
                                </button>
                            </th>
                            <th class="px-3 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                <button type="button" wire:click="sortBy('odometer_reading')" class="flex items-center gap-1">
                                    <span>{{ __('Odometer') }}</span>
                                    @if ($sortField === 'odometer_reading')
                                        <span class="text-[10px]">{{ $sortDirection === 'asc' ? '▲' : '▼' }}</span>
                                    @endif
                                </button>
                            </th>
                            <th class="px-3 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                {{ __('Labor cost') }}
                            </th>
                            <th class="px-3 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                {{ __('Materials cost') }}
                            </th>
                            <th class="px-3 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                <button type="button" wire:click="sortBy('total_cost')" class="flex items-center gap-1">
                                    <span>{{ __('Total cost') }}</span>
                                    @if ($sortField === 'total_cost')
                                        <span class="text-[10px]">{{ $sortDirection === 'asc' ? '▲' : '▼' }}</span>
                                    @endif
                                </button>
                            </th>
                            <th class="px-3 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">
                                {{ __('Actions') }}
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white">
                        @forelse ($records as $record)
                            <tr>
                                <td class="px-3 py-3 whitespace-nowrap text-gray-900 font-medium">
                                    {{ $record->vehicle?->plate_number ?? '—' }}
                                </td>
                                <td class="px-3 py-3 whitespace-nowrap text-gray-700">
                                    {{ optional($record->performed_at)->format('M d, Y') ?? '—' }}
                                </td>
                                <td class="px-3 py-3 whitespace-nowrap text-gray-700">
                                    {{ number_format($record->odometer_reading) }} km
                                </td>
                                <td class="px-3 py-3 whitespace-nowrap text-gray-700">
                                    ₱{{ number_format($record->personnel_labor_cost, 2) }}
                                </td>
                                <td class="px-3 py-3 whitespace-nowrap text-gray-700">
                                    ₱{{ number_format($record->materials_cost_total, 2) }}
                                </td>
                                <td class="px-3 py-3 whitespace-nowrap text-gray-900 font-medium">
                                    ₱{{ number_format($record->total_cost, 2) }}
                                </td>
                                <td class="px-3 py-3 whitespace-nowrap text-right">
                                    <a
                                        href="{{ route('maintenance.show', $record) }}"
                                        class="text-sm font-medium text-indigo-600 hover:text-indigo-900"
                                    >
                                        {{ __('View') }}
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-3 py-6 text-center text-gray-500">
                                    {{ __('No maintenance records yet.') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="border-t border-gray-100 bg-gray-50 px-4 py-3 sm:px-6">
                {{ $records->links() }}
            </div>
        </div>
    </div>

    @if ($showModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 px-4 py-6 overflow-y-auto">
            <div class="w-full max-w-3xl rounded-xl bg-white shadow-xl max-h-[90vh] overflow-y-auto">
                <div class="flex items-center justify-between border-b border-gray-100 px-6 py-4">
                    <h2 class="text-lg font-semibold text-gray-900">
                        {{ __('Add maintenance record') }}
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
                                {{ __('Vehicle') }}
                            </label>
                            <select
                                wire:model="vehicle_id"
                                class="mt-1 block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            >
                                <option value="">{{ __('Select vehicle') }}</option>
                                @foreach ($vehicles as $vehicle)
                                    <option value="{{ $vehicle->id }}">{{ $vehicle->plate_number }}</option>
                                @endforeach
                            </select>
                            @error('vehicle_id')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">
                                {{ __('Performed at') }}
                            </label>
                            <input
                                type="date"
                                wire:model="performed_at"
                                class="mt-1 block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            >
                            @error('performed_at')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">
                                {{ __('Odometer reading') }}
                            </label>
                            <input
                                type="number"
                                wire:model="odometer_reading"
                                class="mt-1 block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            >
                            @error('odometer_reading')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">
                                {{ __('Labor cost') }}
                            </label>
                            <input
                                type="number"
                                step="0.01"
                                wire:model="personnel_labor_cost"
                                class="mt-1 block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            >
                            @error('personnel_labor_cost')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">
                                {{ __('Next maintenance date') }}
                            </label>
                            <input
                                type="date"
                                wire:model="next_maintenance_due_at"
                                class="mt-1 block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            >
                            @error('next_maintenance_due_at')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">
                                {{ __('Next maintenance odometer') }}
                            </label>
                            <input
                                type="number"
                                wire:model="next_maintenance_due_odometer"
                                class="mt-1 block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            >
                            @error('next_maintenance_due_odometer')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">
                                {{ __('Performed by') }}
                            </label>
                            <input
                                type="text"
                                wire:model="performed_by"
                                class="mt-1 block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            >
                            @error('performed_by')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="sm:col-span-2">
                            <label class="block text-sm font-medium text-gray-700">
                                {{ __('Description of work done') }}
                            </label>
                            <textarea
                                wire:model="description_of_work"
                                rows="3"
                                class="mt-1 block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            ></textarea>
                            @error('description_of_work')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <h3 class="text-sm font-semibold text-gray-900">
                                {{ __('22 Point Checklist') }}
                            </h3>
                        </div>

                        <div class="border border-gray-200 rounded-lg overflow-hidden">
                            <table class="min-w-full divide-y divide-gray-200 text-sm">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-3 py-2 text-left text-xs font-medium uppercase tracking-wider text-gray-500 w-8">#</th>
                                        <th class="px-3 py-2 text-left text-xs font-medium uppercase tracking-wider text-gray-500">{{ __('Item') }}</th>
                                        <th class="px-3 py-2 text-center text-xs font-medium uppercase tracking-wider text-gray-500 w-16">{{ __('Good') }}</th>
                                        <th class="px-3 py-2 text-center text-xs font-medium uppercase tracking-wider text-gray-500 w-16">{{ __('Fair') }}</th>
                                        <th class="px-3 py-2 text-center text-xs font-medium uppercase tracking-wider text-gray-500 w-16">{{ __('Bad') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 bg-white">
                                    @foreach ($checklistItems as $index => $item)
                                        <tr class="{{ $index % 2 === 0 ? 'bg-white' : 'bg-gray-50' }}">
                                            <td class="px-3 py-2 text-gray-500 text-center">{{ $index + 1 }}</td>
                                            <td class="px-3 py-2 text-gray-900">{{ $item }}</td>
                                            <td class="px-3 py-2 text-center">
                                                <button
                                                    type="button"
                                                    wire:click="setChecklistStatus('{{ $item }}', 'good')"
                                                    class="w-5 h-5 rounded border-2 flex items-center justify-center transition-colors {{ ($checklist[$item] ?? null) === 'good' ? 'bg-green-500 border-green-500 text-white' : 'border-gray-300 hover:border-green-400' }}"
                                                >
                                                    @if (($checklist[$item] ?? null) === 'good')
                                                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                                        </svg>
                                                    @endif
                                                </button>
                                            </td>
                                            <td class="px-3 py-2 text-center">
                                                <button
                                                    type="button"
                                                    wire:click="setChecklistStatus('{{ $item }}', 'fair')"
                                                    class="w-5 h-5 rounded border-2 flex items-center justify-center transition-colors {{ ($checklist[$item] ?? null) === 'fair' ? 'bg-yellow-500 border-yellow-500 text-white' : 'border-gray-300 hover:border-yellow-400' }}"
                                                >
                                                    @if (($checklist[$item] ?? null) === 'fair')
                                                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                                        </svg>
                                                    @endif
                                                </button>
                                            </td>
                                            <td class="px-3 py-2 text-center">
                                                <button
                                                    type="button"
                                                    wire:click="setChecklistStatus('{{ $item }}', 'bad')"
                                                    class="w-5 h-5 rounded border-2 flex items-center justify-center transition-colors {{ ($checklist[$item] ?? null) === 'bad' ? 'bg-red-500 border-red-500 text-white' : 'border-gray-300 hover:border-red-400' }}"
                                                >
                                                    @if (($checklist[$item] ?? null) === 'bad')
                                                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                                        </svg>
                                                    @endif
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <h3 class="text-sm font-semibold text-gray-900">
                                {{ __('Materials used') }}
                            </h3>
                            <button
                                type="button"
                                wire:click="addMaterialRow"
                                class="text-xs font-medium text-indigo-600 hover:text-indigo-800"
                            >
                                {{ __('Add material') }}
                            </button>
                        </div>

                        <div class="space-y-2">
                            @foreach ($materials as $index => $material)
                                <div class="grid gap-3 sm:grid-cols-12 rounded-lg border border-gray-200 p-3">
                                    <div class="sm:col-span-4">
                                        <input
                                            type="text"
                                            wire:model="materials.{{ $index }}.name"
                                            placeholder="{{ __('Material name') }}"
                                            class="block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        >
                                    </div>
                                    <div class="sm:col-span-3">
                                        <input
                                            type="text"
                                            wire:model="materials.{{ $index }}.unit"
                                            placeholder="{{ __('Part number') }}"
                                            class="block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        >
                                    </div>
                                    <div class="sm:col-span-2">
                                        <input
                                            type="number"
                                            step="0.01"
                                            wire:model="materials.{{ $index }}.quantity"
                                            placeholder="{{ __('Qty') }}"
                                            class="block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        >
                                    </div>
                                    <div class="sm:col-span-3 flex gap-2">
                                        <input
                                            type="number"
                                            step="0.01"
                                            wire:model="materials.{{ $index }}.unit_cost"
                                            placeholder="{{ __('Cost') }}"
                                            class="block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        >
                                        <button
                                            type="button"
                                            wire:click="removeMaterialRow({{ $index }})"
                                            class="text-xs text-red-600 hover:text-red-800"
                                        >
                                            {{ __('Remove') }}
                                        </button>
                                    </div>
                                </div>
                            @endforeach
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
                                {{ __('Save maintenance record') }}
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
