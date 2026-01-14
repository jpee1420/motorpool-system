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

        @if (session()->has('error'))
            <div class="rounded-lg bg-red-50 p-4 border border-red-200">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-red-800">{{ session('error') }}</p>
                    </div>
                </div>
            </div>
        @endif

        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900">
                    {{ __('Activity log') }}
                </h1>
                <p class="mt-1 text-sm text-gray-500">
                    {{ __('Audit trail of staff/admin create & edit actions.') }}
                </p>
            </div>

            <div class="flex items-center gap-2">
                @if ($search || $action || $subjectType || $fromDate || $toDate)
                    <button
                        type="button"
                        wire:click="clearFilters"
                        class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium rounded-full bg-gray-100 text-gray-600 hover:bg-gray-200 transition"
                    >
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                        {{ __('Clear') }}
                    </button>
                @endif
            </div>
        </div>

        <div class="bg-white shadow-sm rounded-xl border border-gray-100 p-4 sm:p-5 space-y-4">
            <div class="grid gap-3 md:grid-cols-4 lg:grid-cols-6">
                <div class="md:col-span-2">
                    <label class="block text-xs font-medium text-gray-600">
                        {{ __('Search (actor, subject)') }}
                    </label>
                    <input
                        type="text"
                        wire:model.live="search"
                        placeholder="{{ __('Name / email / subject') }}"
                        class="mt-1 block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    >
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-600">
                        {{ __('Action') }}
                    </label>
                    <select
                        wire:model.live="action"
                        class="mt-1 block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    >
                        <option value="">{{ __('All') }}</option>
                        <option value="created">{{ __('Created') }}</option>
                        <option value="updated">{{ __('Updated') }}</option>
                    </select>
                </div>

                <div class="md:col-span-2">
                    <label class="block text-xs font-medium text-gray-600">
                        {{ __('Subject type') }}
                    </label>
                    <select
                        wire:model.live="subjectType"
                        class="mt-1 block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    >
                        <option value="">{{ __('All') }}</option>
                        @foreach ($subjectTypes as $type)
                            <option value="{{ $type }}">{{ class_basename($type) }}</option>
                        @endforeach
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
                <table class="min-w-full table-fixed divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-3 py-3 w-40 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                {{ __('When') }}
                            </th>
                            <th class="px-3 py-3 w-56 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                {{ __('Actor') }}
                            </th>
                            <th class="px-3 py-3 w-28 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                {{ __('Action') }}
                            </th>
                            <th class="px-3 py-3 w-64 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                {{ __('Subject') }}
                            </th>
                            <th class="px-3 py-3 w-28 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                {{ __('Details') }}
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white">
                        @forelse ($logs as $log)
                            <tr>
                                <td class="px-3 py-3 whitespace-nowrap text-gray-700">
                                    <div class="flex flex-col">
                                        <span>{{ $log->created_at?->format('M d, Y') }}</span>
                                        <span class="text-xs text-gray-500">{{ $log->created_at?->format('h:i A') }}</span>
                                    </div>
                                </td>
                                <td class="px-3 py-3 whitespace-nowrap text-gray-700">
                                    <div class="flex flex-col">
                                        <span class="font-medium text-gray-900">{{ $log->actor?->name ?? '—' }}</span>
                                        @if ($log->actor?->email)
                                            <span class="text-xs text-gray-500">{{ $log->actor->email }}</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-3 py-3 whitespace-nowrap">
                                    @php
                                        $actionClasses = [
                                            'created' => 'bg-green-100 text-green-800',
                                            'updated' => 'bg-amber-100 text-amber-800',
                                        ];
                                        $actionClass = $actionClasses[$log->action] ?? 'bg-gray-100 text-gray-800';
                                    @endphp
                                    <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium {{ $actionClass }}">
                                        {{ ucfirst($log->action) }}
                                    </span>
                                </td>
                                <td class="px-3 py-3 whitespace-nowrap text-gray-700">
                                    <div class="flex flex-col">
                                        <span class="font-medium text-gray-900">{{ $log->subject_label ?? '—' }}</span>
                                        <span class="text-xs text-gray-500">{{ class_basename($log->subject_type) }}</span>
                                    </div>
                                </td>
                                <td class="px-3 py-3 whitespace-nowrap">
                                    <button
                                        type="button"
                                        wire:click="openDetails({{ $log->id }})"
                                        class="inline-flex items-center justify-center rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-xs font-medium text-gray-700 shadow-sm hover:bg-gray-50"
                                        aria-haspopup="dialog"
                                    >
                                        {{ __('View') }}
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-3 py-6 text-center text-gray-500">
                                    {{ __('No activity found for the selected filters.') }}
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

        @if ($showDetailsModal)
            <div
                class="fixed inset-0 z-50 overflow-y-auto"
                aria-labelledby="activity-log-details-title"
                role="dialog"
                aria-modal="true"
                wire:keydown.escape.window="closeDetails"
            >
                <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-6">
                    <div class="fixed inset-0 bg-gray-900/50" wire:click="closeDetails"></div>

                    <div class="relative w-full max-w-3xl transform overflow-hidden rounded-xl bg-white text-left shadow-xl transition-all">
                        <div class="flex items-start justify-between border-b border-gray-100 px-5 py-4">
                            <div class="min-w-0">
                                <h2 id="activity-log-details-title" class="text-base font-semibold text-gray-900">
                                    {{ __('Activity details') }}
                                </h2>
                                @if ($selectedLog)
                                    <p class="mt-1 text-sm text-gray-500">
                                        {{ $selectedLog->created_at?->format('M d, Y h:i A') }}
                                        <span class="text-gray-300">•</span>
                                        {{ $selectedLog->actor?->name ?? '—' }}
                                        <span class="text-gray-300">•</span>
                                        {{ ucfirst($selectedLog->action) }}
                                        <span class="text-gray-300">•</span>
                                        {{ $selectedLog->subject_label ?? '—' }}
                                    </p>
                                @endif
                            </div>
                            <button
                                type="button"
                                wire:click="closeDetails"
                                class="ml-4 inline-flex h-9 w-9 items-center justify-center rounded-lg text-gray-500 hover:bg-gray-100 hover:text-gray-700"
                            >
                                <span class="sr-only">{{ __('Close') }}</span>
                                <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </button>
                        </div>

                        <div class="px-5 py-4">
                            @if (! $selectedLog)
                                <div class="text-sm text-gray-500">
                                    {{ __('Unable to load activity details.') }}
                                </div>
                            @else
                                @php
                                    $old = $selectedLog->old_values ?? [];
                                    $new = $selectedLog->new_values ?? [];
                                @endphp

                                <div class="max-h-[70vh] overflow-auto rounded-lg border border-gray-100">
                                    <div class="divide-y divide-gray-100">
                                        @if ($selectedLog->action === 'created')
                                            @if (empty($new))
                                                <div class="p-4 text-sm text-gray-500">—</div>
                                            @else
                                                @foreach ($new as $key => $value)
                                                    <div class="grid grid-cols-12 gap-3 p-4 text-sm">
                                                        <div class="col-span-4 font-medium text-gray-700 break-words">{{ $key }}</div>
                                                        <div class="col-span-8 text-gray-900 break-words">
                                                            {{ is_array($value) ? json_encode($value) : (string) $value }}
                                                        </div>
                                                    </div>
                                                @endforeach
                                            @endif
                                        @elseif ($selectedLog->action === 'updated')
                                            @if (empty($new))
                                                <div class="p-4 text-sm text-gray-500">—</div>
                                            @else
                                                @foreach ($new as $key => $value)
                                                    @php
                                                        $oldValue = $old[$key] ?? null;
                                                    @endphp
                                                    <div class="grid grid-cols-12 gap-3 p-4 text-sm">
                                                        <div class="col-span-3 font-medium text-gray-700 break-words">{{ $key }}</div>
                                                        <div class="col-span-4 text-gray-900 break-words">
                                                            {{ is_array($oldValue) ? json_encode($oldValue) : (string) $oldValue }}
                                                        </div>
                                                        <div class="col-span-1 text-center text-gray-400">→</div>
                                                        <div class="col-span-4 text-gray-900 break-words">
                                                            {{ is_array($value) ? json_encode($value) : (string) $value }}
                                                        </div>
                                                    </div>
                                                @endforeach
                                            @endif
                                        @else
                                            <div class="p-4 text-sm text-gray-500">—</div>
                                        @endif
                                    </div>
                                </div>
                            @endif
                        </div>

                        <div class="flex justify-end gap-2 border-t border-gray-100 bg-gray-50 px-5 py-3">
                            <button
                                type="button"
                                wire:click="closeDetails"
                                class="inline-flex items-center justify-center rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50"
                            >
                                {{ __('Close') }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
