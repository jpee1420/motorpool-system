<div class="py-10" x-data="{ confirmOpen: false }">
    {{-- Delete Confirmation Modal --}}
    @can('delete', $record)
        <x-confirm-modal
            :title="__('Delete repair record')"
            :description="__('Are you sure you want to delete this repair record? This action cannot be undone.')"
            confirm-wire-click="delete"
        />
    @endcan

    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900">
                    {{ __('Repair detail') }}
                </h1>
                <p class="mt-1 text-sm text-gray-500 hidden sm:block">
                    {{ __('Review work done, costs, and materials for this repair record.') }}
                </p>
            </div>

            <div class="flex items-center gap-2">
                @can('delete', $record)
                    <button
                        type="button"
                        x-on:click.prevent="confirmOpen = true"
                        class="inline-flex items-center rounded-lg border border-red-200 bg-red-50 px-3 py-1.5 text-sm font-medium text-red-700 hover:bg-red-100"
                    >
                        {{ __('Delete') }}
                    </button>
                @endcan

                <a
                    href="{{ route('repair.index') }}"
                    class="inline-flex items-center rounded-lg border border-gray-300 px-3 py-1.5 text-sm font-medium text-gray-700 hover:bg-gray-50"
                >
                    {{ __('Back') }}
                </a>
            </div>
        </div>

        <div class="bg-white shadow-sm rounded-xl border border-gray-100 p-6 space-y-4">
            <h2 class="text-sm font-semibold text-gray-900">
                {{ __('Vehicle') }}
            </h2>

            <dl class="grid gap-4 sm:grid-cols-2 text-sm">
                <div>
                    <dt class="font-medium text-gray-500">{{ __('Plate number') }}</dt>
                    <dd class="mt-1 text-gray-900">{{ $record->vehicle?->plate_number ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="font-medium text-gray-500">{{ __('Make / Model') }}</dt>
                    <dd class="mt-1 text-gray-900">
                        {{ trim(($record->vehicle?->make ?? '').' '.($record->vehicle?->model ?? '')) ?: '—' }}
                    </dd>
                </div>
                <div>
                    <dt class="font-medium text-gray-500">{{ __('Year') }}</dt>
                    <dd class="mt-1 text-gray-900">{{ $record->vehicle?->year ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="font-medium text-gray-500">{{ __('Driver / Operator') }}</dt>
                    <dd class="mt-1 text-gray-900">
                        {{ $record->assignedDriver?->name ?? $record->vehicle?->driver_operator ?? '—' }}
                    </dd>
                </div>
                <div>
                    <dt class="font-medium text-gray-500">{{ __('Current odometer') }}</dt>
                    <dd class="mt-1 text-gray-900">
                        @if ($record->vehicle?->current_odometer)
                            {{ number_format($record->vehicle->current_odometer) }} km
                        @else
                            —
                        @endif
                    </dd>
                </div>
                <div>
                    <dt class="font-medium text-gray-500">{{ __('Status') }}</dt>
                    <dd class="mt-1 text-gray-900 capitalize">{{ $record->vehicle?->status ?? '—' }}</dd>
                </div>
            </dl>
        </div>

        <div class="bg-white shadow-sm rounded-xl border border-gray-100 p-6 space-y-4">
            <h2 class="text-sm font-semibold text-gray-900">
                {{ __('Repair details') }}
            </h2>

            <dl class="grid gap-4 sm:grid-cols-2 text-sm">
                <div>
                    <dt class="font-medium text-gray-500">{{ __('Performed at') }}</dt>
                    <dd class="mt-1 text-gray-900">
                        {{ $record->performed_at?->format('M d, Y H:i') ?? '—' }}
                    </dd>
                </div>
                <div>
                    <dt class="font-medium text-gray-500">{{ __('Performed by') }}</dt>
                    <dd class="mt-1 text-gray-900">{{ $record->performedBy?->name ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="font-medium text-gray-500">{{ __('Odometer reading') }}</dt>
                    <dd class="mt-1 text-gray-900">
                        {{ $record->odometer_reading ? number_format($record->odometer_reading) . ' km' : '—' }}
                    </dd>
                </div>
                <div>
                    <dt class="font-medium text-gray-500">{{ __('Labor cost') }}</dt>
                    <dd class="mt-1 text-gray-900">
                        ₱{{ number_format($record->personnel_labor_cost, 2) }}
                    </dd>
                </div>
                <div>
                    <dt class="font-medium text-gray-500">{{ __('Materials cost') }}</dt>
                    <dd class="mt-1 text-gray-900">
                        ₱{{ number_format($record->materials_cost_total, 2) }}
                    </dd>
                </div>
                <div>
                    <dt class="font-medium text-gray-500">{{ __('Total cost') }}</dt>
                    <dd class="mt-1 text-gray-900 font-semibold">
                        ₱{{ number_format($record->total_cost, 2) }}
                    </dd>
                </div>
            </dl>

            @if (! empty($record->description_of_work))
                <div class="pt-4 border-t border-gray-100">
                    <h3 class="text-sm font-semibold text-gray-900">
                        {{ __('Description of work done') }}
                    </h3>
                    <p class="mt-2 text-sm text-gray-700 whitespace-pre-line">
                        {{ $record->description_of_work }}
                    </p>
                </div>
            @endif
        </div>

        <div class="bg-white shadow-sm rounded-xl border border-gray-100 p-6">
            <div class="flex items-center justify-between mb-3">
                <h2 class="text-sm font-semibold text-gray-900">
                    {{ __('Materials used') }}
                </h2>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-3 py-2 text-left font-medium text-gray-500 uppercase tracking-wider" style="width: 30%">
                                {{ __('Item') }}
                            </th>
                            <th class="px-3 py-2 text-left font-medium text-gray-500 uppercase tracking-wider" style="width: 25%">
                                {{ __('Part number') }}
                            </th>
                            <th class="px-3 py-2 text-right font-medium text-gray-500 uppercase tracking-wider" style="width: 10%">
                                {{ __('Quantity') }}
                            </th>
                            <th class="px-3 py-2 text-right font-medium text-gray-500 uppercase tracking-wider" style="width: 15%">
                                {{ __('Cost') }}
                            </th>
                            <th class="px-3 py-2 text-right font-medium text-gray-500 uppercase tracking-wider" style="width: 20%">
                                {{ __('Total cost') }}
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white">
                        @forelse ($record->materials as $material)
                            <tr>
                                <td class="px-3 py-2 whitespace-nowrap text-gray-900">
                                    {{ $material->name }}
                                </td>
                                <td class="px-3 py-2 whitespace-nowrap text-gray-700">
                                    {{ $material->unit ?? '—' }}
                                </td>
                                <td class="px-3 py-2 whitespace-nowrap text-right text-gray-700">
                                    {{ $material->quantity === null ? '—' : rtrim(rtrim((string) $material->quantity, '0'), '.') }}
                                </td>
                                <td class="px-3 py-2 whitespace-nowrap text-right text-gray-700">
                                    ₱{{ number_format($material->unit_cost, 2) }}
                                </td>
                                <td class="px-3 py-2 whitespace-nowrap text-right text-gray-900 font-medium">
                                    ₱{{ number_format($material->total_cost, 2) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-3 py-4 text-center text-gray-500">
                                    {{ __('No materials recorded for this repair.') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
