<div class="py-10">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900">
                    {{ __('Maintenance calendar') }}
                </h1>
                <p class="mt-1 text-sm text-gray-500">
                    {{ __('View maintenance performed and upcoming due dates in a monthly view.') }}
                </p>
            </div>

            <div class="flex items-center gap-2">
                <button
                    type="button"
                    wire:click="goToPreviousMonth"
                    class="inline-flex items-center rounded-lg border border-gray-300 px-3 py-1.5 text-sm text-gray-700 hover:bg-gray-50"
                >
                    ‹ {{ __('Previous') }}
                </button>

                <div class="px-3 py-1.5 text-sm font-semibold text-gray-900 bg-white rounded-lg border border-gray-200">
                    {{ $month->format('F Y') }}
                </div>

                <button
                    type="button"
                    wire:click="goToNextMonth"
                    class="inline-flex items-center rounded-lg border border-gray-300 px-3 py-1.5 text-sm text-gray-700 hover:bg-gray-50"
                >
                    {{ __('Next') }} ›
                </button>
            </div>
        </div>

        <div class="bg-white shadow-sm rounded-xl border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-3 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                {{ __('Date') }}
                            </th>
                            <th class="px-3 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                {{ __('Performed maintenance') }}
                            </th>
                            <th class="px-3 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                {{ __('Upcoming due') }}
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white">
                        @forelse ($days as $day)
                            <tr>
                                <td class="px-3 py-3 whitespace-nowrap text-gray-900 font-medium">
                                    {{ $day['date']->format('M d, Y') }}
                                </td>
                                <td class="px-3 py-3 text-gray-700">
                                    @if ($day['performed']->isEmpty())
                                        <span class="text-gray-400 text-xs">{{ __('None') }}</span>
                                    @else
                                        <ul class="space-y-1">
                                            @foreach ($day['performed'] as $record)
                                                <li class="flex items-center justify-between gap-2">
                                                    <div class="flex items-center gap-1">
                                                        <a
                                                            href="{{ route('maintenance.show', $record) }}"
                                                            class="font-medium text-indigo-600 hover:text-indigo-900"
                                                        >
                                                            {{ $record->vehicle?->plate_number ?? '—' }}
                                                        </a>
                                                        <span class="text-xs text-gray-500 ml-1">
                                                            {{ $record->performed_at?->format('H:i') }}
                                                        </span>
                                                    </div>
                                                    <a
                                                        href="{{ route('maintenance.show', $record) }}"
                                                        class="text-xs text-gray-500 truncate max-w-xs hover:text-gray-700"
                                                    >
                                                        {{ Str::limit($record->description_of_work, 60) }}
                                                    </a>
                                                </li>
                                            @endforeach
                                        </ul>
                                    @endif
                                </td>
                                <td class="px-3 py-3 text-gray-700">
                                    @if ($day['due']->isEmpty())
                                        <span class="text-gray-400 text-xs">{{ __('None') }}</span>
                                    @else
                                        <ul class="space-y-1">
                                            @foreach ($day['due'] as $record)
                                                <li class="flex items-center justify-between gap-2">
                                                    <div>
                                                        <a
                                                            href="{{ route('maintenance.show', $record) }}"
                                                            class="font-medium text-indigo-600 hover:text-indigo-900"
                                                        >
                                                            {{ $record->vehicle?->plate_number ?? '—' }}
                                                        </a>
                                                    </div>
                                                    <a
                                                        href="{{ route('maintenance.show', $record) }}"
                                                        class="text-xs text-gray-500 hover:text-gray-700"
                                                    >
                                                        {{ $record->next_maintenance_due_odometer ? number_format($record->next_maintenance_due_odometer) . ' km' : '' }}
                                                    </a>
                                                </li>
                                            @endforeach
                                        </ul>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-3 py-6 text-center text-gray-500">
                                    {{ __('No maintenance data for this month.') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
