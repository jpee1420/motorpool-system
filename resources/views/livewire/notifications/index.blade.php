<div class="py-10">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900">
                    {{ __('Notification logs') }}
                </h1>
                <p class="mt-1 text-sm text-gray-500">
                    {{ __('Review all maintenance reminder notifications that have been logged and sent.') }}
                </p>
            </div>
        </div>

        <div class="bg-white shadow-sm rounded-xl border border-gray-100 p-4 sm:p-5 space-y-4">
            <div class="grid gap-3 md:grid-cols-4 lg:grid-cols-6">
                <div class="md:col-span-2">
                    <label class="block text-xs font-medium text-gray-600">
                        {{ __('Search (vehicle, recipient)') }}
                    </label>
                    <input
                        type="text"
                        wire:model.live="search"
                        placeholder="{{ __('Plate / name / contact') }}"
                        class="mt-1 block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    >
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-600">
                        {{ __('Status') }}
                    </label>
                    <select
                        wire:model.live="status"
                        class="mt-1 block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    >
                        <option value="">{{ __('All') }}</option>
                        <option value="sent">{{ __('Sent') }}</option>
                        <option value="pending">{{ __('Pending') }}</option>
                        <option value="failed">{{ __('Failed') }}</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-600">
                        {{ __('Channel') }}
                    </label>
                    <select
                        wire:model.live="channel"
                        class="mt-1 block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    >
                        <option value="">{{ __('All') }}</option>
                        <option value="email">{{ __('Email') }}</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-600">
                        {{ __('Type') }}
                    </label>
                    <select
                        wire:model.live="type"
                        class="mt-1 block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    >
                        <option value="">{{ __('All') }}</option>
                        <option value="maintenance_due">{{ __('Maintenance due') }}</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-600">
                        {{ __('From date') }}
                    </label>
                    <input
                        type="date"
                        wire:model.live="fromDate"
                        class="mt-1 block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    >
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-600">
                        {{ __('To date') }}
                    </label>
                    <input
                        type="date"
                        wire:model.live="toDate"
                        class="mt-1 block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    >
                </div>
            </div>
        </div>

        <div class="bg-white shadow-sm rounded-xl border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-3 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                {{ __('Sent at') }}
                            </th>
                            <th class="px-3 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                {{ __('Vehicle') }}
                            </th>
                            <th class="px-3 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                {{ __('Recipient') }}
                            </th>
                            <th class="px-3 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                {{ __('Channel') }}
                            </th>
                            <th class="px-3 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                {{ __('Type') }}
                            </th>
                            <th class="px-3 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                {{ __('Status') }}
                            </th>
                            <th class="px-3 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                {{ __('Error') }}
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white">
                        @forelse ($logs as $log)
                            <tr>
                                <td class="px-3 py-3 whitespace-nowrap text-gray-700">
                                    {{ $log->sent_at?->format('M d, Y H:i') ?? '—' }}
                                </td>
                                <td class="px-3 py-3 whitespace-nowrap text-gray-900">
                                    {{ $log->vehicle?->plate_number ?? '—' }}
                                </td>
                                <td class="px-3 py-3 whitespace-nowrap text-gray-700">
                                    <div class="flex flex-col">
                                        <span>{{ $log->recipient_name ?: '—' }}</span>
                                        <span class="text-xs text-gray-500">{{ $log->recipient_contact }}</span>
                                    </div>
                                </td>
                                <td class="px-3 py-3 whitespace-nowrap text-gray-700">
                                    {{ strtoupper($log->channel) }}
                                </td>
                                <td class="px-3 py-3 whitespace-nowrap text-gray-700">
                                    {{ $log->type }}
                                </td>
                                <td class="px-3 py-3 whitespace-nowrap">
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
                                <td class="px-3 py-3 whitespace-nowrap text-gray-700 max-w-xs">
                                    @if ($log->error_message)
                                        <span class="block truncate" title="{{ $log->error_message }}">
                                            {{ $log->error_message }}
                                        </span>
                                    @else
                                        —
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-3 py-6 text-center text-gray-500">
                                    {{ __('No notifications found for the selected filters.') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="border-t border-gray-100 bg-gray-50 px-4 py-3 sm:px-6">
                {{ $logs->links() }}
            </div>
        </div>
    </div>
</div>
