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
                <div>
                    <select
                        wire:model.live="vehicleFilter"
                        class="w-full sm:w-64 rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    >
                        <option value="">{{ __('All vehicles') }}</option>
                        @foreach ($vehicles as $vehicle)
                            <option value="{{ $vehicle->id }}">{{ $vehicle->plate_number }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex items-center gap-2">
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
                        <span wire:loading.remove wire:target="exportCsv">{{ __('Export CSV') }}</span>
                        <span wire:loading wire:target="exportCsv">{{ __('Exporting...') }}</span>
                    </button>

                    <button
                        type="button"
                        wire:click="openCreateModal"
                        wire:loading.attr="disabled"
                        class="inline-flex items-center justify-center rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-offset-2 disabled:opacity-50"
                    >
                        {{ __('Add maintenance record') }}
                    </button>
                </div>
            </div>
        </div>

        <div class="bg-white shadow-sm rounded-xl border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-3 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                {{ __('Vehicle') }}
                            </th>
                            <th class="px-3 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                {{ __('Performed at') }}
                            </th>
                            <th class="px-3 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                {{ __('Odometer') }}
                            </th>
                            <th class="px-3 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                {{ __('Labor cost') }}
                            </th>
                            <th class="px-3 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                {{ __('Materials cost') }}
                            </th>
                            <th class="px-3 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                {{ __('Total cost') }}
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
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40">
            <div class="w-full max-w-3xl rounded-xl bg-white shadow-xl">
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
                                <div class="grid gap-3 sm:grid-cols-5 rounded-lg border border-gray-200 p-3">
                                    <div class="sm:col-span-2">
                                        <input
                                            type="text"
                                            wire:model="materials.{{ $index }}.name"
                                            placeholder="{{ __('Material name') }}"
                                            class="block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        >
                                    </div>
                                    <div>
                                        <input
                                            type="text"
                                            wire:model="materials.{{ $index }}.unit"
                                            placeholder="{{ __('Unit') }}"
                                            class="block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        >
                                    </div>
                                    <div>
                                        <input
                                            type="number"
                                            step="0.01"
                                            wire:model="materials.{{ $index }}.quantity"
                                            placeholder="{{ __('Qty') }}"
                                            class="block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        >
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <input
                                            type="number"
                                            step="0.01"
                                            wire:model="materials.{{ $index }}.unit_cost"
                                            placeholder="{{ __('Unit cost') }}"
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
