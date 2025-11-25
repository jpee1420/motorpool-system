<div class="py-10">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900">
                    {{ __('Trip ticket details') }}
                </h1>
                <p class="mt-1 text-sm text-gray-500">
                    {{ __('View trip ticket information and status.') }}
                </p>
            </div>

            <a
                href="{{ route('trip-tickets.index') }}"
                class="inline-flex items-center rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
            >
                ← {{ __('Back to trip tickets') }}
            </a>
        </div>

        <div class="bg-white shadow-sm rounded-xl border border-gray-100 overflow-hidden">
            <div class="px-6 py-5 border-b border-gray-100">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-gray-900">
                        {{ $ticket->vehicle?->plate_number ?? __('Unknown vehicle') }}
                    </h2>
                    @php
                        $statusClasses = [
                            'pending' => 'bg-yellow-100 text-yellow-800',
                            'approved' => 'bg-blue-100 text-blue-800',
                            'ongoing' => 'bg-indigo-100 text-indigo-800',
                            'completed' => 'bg-green-100 text-green-800',
                            'cancelled' => 'bg-red-100 text-red-800',
                        ];
                        $statusClass = $statusClasses[$ticket->status] ?? 'bg-gray-100 text-gray-800';
                    @endphp
                    <span class="inline-flex items-center rounded-full px-3 py-1 text-sm font-medium {{ $statusClass }}">
                        {{ ucfirst($ticket->status) }}
                    </span>
                </div>
            </div>

            <div class="px-6 py-5 space-y-6">
                <div class="grid gap-6 sm:grid-cols-2">
                    <div>
                        <h3 class="text-xs font-medium uppercase tracking-wider text-gray-500">
                            {{ __('Driver') }}
                        </h3>
                        <p class="mt-1 text-sm text-gray-900">
                            {{ $ticket->driver_name ?: '—' }}
                        </p>
                    </div>

                    <div>
                        <h3 class="text-xs font-medium uppercase tracking-wider text-gray-500">
                            {{ __('Requested by') }}
                        </h3>
                        <p class="mt-1 text-sm text-gray-900">
                            {{ $ticket->requestedBy?->name ?? '—' }}
                        </p>
                    </div>

                    <div>
                        <h3 class="text-xs font-medium uppercase tracking-wider text-gray-500">
                            {{ __('Destination') }}
                        </h3>
                        <p class="mt-1 text-sm text-gray-900">
                            {{ $ticket->destination ?: '—' }}
                        </p>
                    </div>

                    <div>
                        <h3 class="text-xs font-medium uppercase tracking-wider text-gray-500">
                            {{ __('Purpose') }}
                        </h3>
                        <p class="mt-1 text-sm text-gray-900">
                            {{ $ticket->purpose ?: '—' }}
                        </p>
                    </div>

                    <div>
                        <h3 class="text-xs font-medium uppercase tracking-wider text-gray-500">
                            {{ __('Departure') }}
                        </h3>
                        <p class="mt-1 text-sm text-gray-900">
                            {{ $ticket->departure_at?->format('M d, Y H:i') ?? '—' }}
                        </p>
                    </div>

                    <div>
                        <h3 class="text-xs font-medium uppercase tracking-wider text-gray-500">
                            {{ __('Return') }}
                        </h3>
                        <p class="mt-1 text-sm text-gray-900">
                            {{ $ticket->return_at?->format('M d, Y H:i') ?? '—' }}
                        </p>
                    </div>

                    <div>
                        <h3 class="text-xs font-medium uppercase tracking-wider text-gray-500">
                            {{ __('Odometer start') }}
                        </h3>
                        <p class="mt-1 text-sm text-gray-900">
                            {{ $ticket->odometer_start ? number_format($ticket->odometer_start) . ' km' : '—' }}
                        </p>
                    </div>

                    <div>
                        <h3 class="text-xs font-medium uppercase tracking-wider text-gray-500">
                            {{ __('Odometer end') }}
                        </h3>
                        <p class="mt-1 text-sm text-gray-900">
                            {{ $ticket->odometer_end ? number_format($ticket->odometer_end) . ' km' : '—' }}
                        </p>
                    </div>

                    @if ($ticket->odometer_start && $ticket->odometer_end)
                        <div>
                            <h3 class="text-xs font-medium uppercase tracking-wider text-gray-500">
                                {{ __('Distance traveled') }}
                            </h3>
                            <p class="mt-1 text-sm font-semibold text-indigo-600">
                                {{ number_format($ticket->odometer_end - $ticket->odometer_start) }} km
                            </p>
                        </div>
                    @endif
                </div>

                @if ($ticket->notes)
                    <div class="border-t border-gray-100 pt-6">
                        <h3 class="text-xs font-medium uppercase tracking-wider text-gray-500">
                            {{ __('Notes') }}
                        </h3>
                        <p class="mt-1 text-sm text-gray-900 whitespace-pre-line">
                            {{ $ticket->notes }}
                        </p>
                    </div>
                @endif
            </div>

            <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex items-center justify-between text-xs text-gray-500">
                <span>{{ __('Created') }}: {{ $ticket->created_at?->format('M d, Y H:i') }}</span>
                <span>{{ __('Updated') }}: {{ $ticket->updated_at?->format('M d, Y H:i') }}</span>
            </div>
        </div>
    </div>
</div>
