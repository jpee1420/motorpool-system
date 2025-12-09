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
                    {{ __('Notification logs') }}
                </h1>
                <p class="mt-1 text-sm text-gray-500">
                    {{ __('Review all maintenance reminder notifications that have been logged and sent.') }}
                </p>
            </div>

            <!-- Export buttons -->
            <div class="flex items-center gap-2">
                @auth
                    @if (auth()->user()?->isStaffOrAbove())
                        <button
                            type="button"
                            wire:click="runMaintenanceCheck"
                            wire:loading.attr="disabled"
                            wire:target="runMaintenanceCheck"
                            class="inline-flex items-center gap-1.5 rounded-full border border-gray-300 bg-white px-3 py-1.5 text-xs font-medium text-gray-700 shadow-sm hover:bg-gray-50 disabled:opacity-50"
                        >
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h4l2 2h8a1 1 0 011 1v1M4 7h16M4 11h16M4 15h10M4 19h6" />
                            </svg>
                            <span wire:loading.remove wire:target="runMaintenanceCheck">
                                {{ __('Run maintenance check') }}
                            </span>
                            <span wire:loading wire:target="runMaintenanceCheck">
                                {{ __('Running...') }}
                            </span>
                        </button>
                    @endif
                @endauth

                <button
                    type="button"
                    wire:click="exportCsv"
                    wire:loading.attr="disabled"
                    wire:target="exportCsv,exportExcel"
                    class="inline-flex items-center gap-1.5 rounded-full border border-gray-300 bg-white px-3 py-1.5 text-xs font-medium text-gray-700 shadow-sm hover:bg-gray-50 disabled:opacity-50"
                >
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a2 2 0 002 2h12a2 2 0 002-2v-1M7 10l5 5m0 0l5-5m-5 5V4" />
                    </svg>
                    <span>CSV</span>
                </button>

                <button
                    type="button"
                    wire:click="exportExcel"
                    wire:loading.attr="disabled"
                    wire:target="exportCsv,exportExcel"
                    class="inline-flex items-center gap-1.5 rounded-full border border-indigo-500 bg-indigo-500 px-3 py-1.5 text-xs font-medium text-white shadow-sm hover:bg-indigo-600 disabled:opacity-50"
                >
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h4l2 2h8a1 1 0 011 1v1M4 7h16M4 11h16M4 15h10M4 19h6" />
                    </svg>
                    <span>Excel</span>
                </button>
            </div>
        </div>

        <!-- KPI metrics -->
        <div class="grid gap-3 sm:grid-cols-2">
            <div class="rounded-xl border border-gray-100 bg-white px-4 py-3 shadow-sm flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">
                        {{ __('Overdue (last 30 days)') }}
                    </p>
                    <p class="mt-1 text-2xl font-semibold text-gray-900">
                        {{ $metrics['overdue_last_30'] ?? 0 }}
                    </p>
                </div>
            </div>

            <div class="rounded-xl border border-gray-100 bg-white px-4 py-3 shadow-sm flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">
                        {{ __('Avg days late (resolved overdue, last 30 days)') }}
                    </p>
                    <p class="mt-1 text-2xl font-semibold text-gray-900">
                        {{ $metrics['avg_days_late_last_30'] !== null ? $metrics['avg_days_late_last_30'] : '—' }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Quick filter chips -->
        <div class="flex flex-wrap items-center gap-2">
            <span class="text-sm font-medium text-gray-600">{{ __('Quick filters:') }}</span>

            <button
                type="button"
                wire:click="filterStatus(null)"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium rounded-full transition {{ $status === null ? 'bg-indigo-100 text-indigo-700' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}"
            >
                {{ __('All') }}
            </button>

            <button
                type="button"
                wire:click="filterStatus('pending')"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium rounded-full transition {{ $status === 'pending' ? 'bg-yellow-100 text-yellow-700' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}"
            >
                {{ __('Pending') }}
                @if ($statusCounts['pending'] > 0)
                    <span class="inline-flex items-center justify-center w-5 h-5 text-xs font-semibold rounded-full bg-yellow-200 text-yellow-800">
                        {{ $statusCounts['pending'] }}
                    </span>
                @endif
            </button>

            <button
                type="button"
                wire:click="filterStatus('failed')"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium rounded-full transition {{ $status === 'failed' ? 'bg-red-100 text-red-700' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}"
            >
                {{ __('Failed') }}
                @if ($statusCounts['failed'] > 0)
                    <span class="inline-flex items-center justify-center w-5 h-5 text-xs font-semibold rounded-full bg-red-200 text-red-800">
                        {{ $statusCounts['failed'] }}
                    </span>
                @endif
            </button>

            <button
                type="button"
                wire:click="filterStatus('sent')"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium rounded-full transition {{ $status === 'sent' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}"
            >
                {{ __('Sent') }}
            </button>

            <button
                type="button"
                wire:click="filterToday"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium rounded-full transition bg-gray-100 text-gray-600 hover:bg-gray-200"
            >
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                {{ __('Today') }}
            </button>

            @if ($status || $channel || $type || $search || $fromDate || $toDate || $archived !== 'active')
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

        <!-- Archived scope filter -->
        <div class="flex flex-wrap items-center gap-2 text-xs text-gray-600">
            <span class="font-medium">{{ __('Scope:') }}</span>
            <button
                type="button"
                wire:click="$set('archived', 'active')"
                class="inline-flex items-center px-2.5 py-1 rounded-full border text-xs font-medium transition {{ $archived === 'active' ? 'border-indigo-500 bg-indigo-50 text-indigo-700' : 'border-gray-300 bg-white text-gray-600 hover:bg-gray-50' }}"
            >
                {{ __('Active') }}
            </button>
            <button
                type="button"
                wire:click="$set('archived', 'archived')"
                class="inline-flex items-center px-2.5 py-1 rounded-full border text-xs font-medium transition {{ $archived === 'archived' ? 'border-amber-500 bg-amber-50 text-amber-700' : 'border-gray-300 bg-white text-gray-600 hover:bg-gray-50' }}"
            >
                {{ __('Archived') }}
            </button>
            <button
                type="button"
                wire:click="$set('archived', 'all')"
                class="inline-flex items-center px-2.5 py-1 rounded-full border text-xs font-medium transition {{ $archived === 'all' ? 'border-gray-900 bg-gray-900 text-white' : 'border-gray-300 bg-white text-gray-600 hover:bg-gray-50' }}"
            >
                {{ __('All') }}
            </button>
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
                        <option value="in_app">{{ __('In-App') }}</option>
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
                        <option value="maintenance_upcoming">{{ __('Upcoming') }}</option>
                        <option value="maintenance_due">{{ __('Due') }}</option>
                        <option value="maintenance_overdue">{{ __('Overdue') }}</option>
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
                <!-- Bulk actions bar -->
                @if (! empty($selected))
                    <div class="flex items-center justify-between px-3 py-2 border-b border-gray-100 bg-gray-50 text-xs text-gray-700">
                        <div>
                            {{ __('Selected :count notifications', ['count' => count($selected)]) }}
                        </div>
                        <div class="flex items-center gap-2">
                            <button
                                type="button"
                                wire:click="bulkRetry"
                                wire:loading.attr="disabled"
                                wire:target="bulkRetry,bulkArchive"
                                class="inline-flex items-center gap-1 rounded-full border border-indigo-500 bg-white px-2.5 py-1 text-xs font-medium text-indigo-600 hover:bg-indigo-50 disabled:opacity-50"
                            >
                                {{ __('Retry') }}
                            </button>
                            <button
                                type="button"
                                wire:click="bulkArchive"
                                wire:loading.attr="disabled"
                                wire:target="bulkRetry,bulkArchive"
                                class="inline-flex items-center gap-1 rounded-full border border-gray-300 bg-white px-2.5 py-1 text-xs font-medium text-gray-600 hover:bg-gray-50 disabled:opacity-50"
                            >
                                {{ __('Archive') }}
                            </button>
                        </div>
                    </div>
                @endif

                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-3 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                <span class="sr-only">{{ __('Select') }}</span>
                            </th>
                            <th class="px-3 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                {{ __('Created') }}
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
                                {{ __('Severity') }}
                            </th>
                            <th class="px-3 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                {{ __('Trigger') }}
                            </th>
                            <th class="px-3 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                {{ __('Due Info') }}
                            </th>
                            <th class="px-3 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                {{ __('Status') }}
                            </th>
                            <th class="px-3 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                {{ __('Resolution') }}
                            </th>
                            <th class="px-3 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">
                                {{ __('Actions') }}
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white">
                        @forelse ($logs as $log)
                            @php
                                $meta = $log->meta ?? [];
                            @endphp
                            <tr class="{{ $log->channel === 'in_app' && $log->read_at === null ? 'bg-blue-50/50' : '' }}">
                                <td class="px-3 py-3 whitespace-nowrap">
                                    <input
                                        type="checkbox"
                                        class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                        value="{{ $log->id }}"
                                        wire:model.live="selected"
                                    >
                                </td>
                                <td class="px-3 py-3 whitespace-nowrap text-gray-700">
                                    <div class="flex flex-col">
                                        <span>{{ $log->created_at?->format('M d, Y') }}</span>
                                        <span class="text-xs text-gray-500">{{ $log->created_at?->format('h:i A') }}</span>
                                    </div>
                                </td>
                                <td class="px-3 py-3 whitespace-nowrap">
                                    @if ($log->vehicle)
                                        <a
                                            href="{{ route('vehicles.index', ['search' => $log->vehicle->plate_number]) }}"
                                            class="text-indigo-600 hover:text-indigo-900 font-medium"
                                        >
                                            {{ $log->vehicle->plate_number }}
                                        </a>
                                    @else
                                        <span class="text-gray-500">—</span>
                                    @endif
                                </td>
                                <td class="px-3 py-3 whitespace-nowrap text-gray-700">
                                    <div class="flex flex-col">
                                        <span>{{ $log->recipient_name ?: '—' }}</span>
                                        @if ($log->recipient_contact)
                                            <span class="text-xs text-gray-500">{{ $log->recipient_contact }}</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-3 py-3 whitespace-nowrap">
                                    @php
                                        $channelClasses = [
                                            'email' => 'bg-blue-100 text-blue-800',
                                            'in_app' => 'bg-purple-100 text-purple-800',
                                        ];
                                        $channelClass = $channelClasses[$log->channel] ?? 'bg-gray-100 text-gray-800';
                                        $channelLabel = $log->channel === 'in_app' ? 'In-App' : strtoupper($log->channel);
                                    @endphp
                                    <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium {{ $channelClass }}">
                                        {{ $channelLabel }}
                                    </span>
                                </td>
                                <td class="px-3 py-3 whitespace-nowrap">
                                    @php
                                        $typeClasses = [
                                            'maintenance_upcoming' => 'bg-sky-100 text-sky-800',
                                            'maintenance_due' => 'bg-amber-100 text-amber-800',
                                            'maintenance_overdue' => 'bg-red-100 text-red-800',
                                        ];
                                        $typeClass = $typeClasses[$log->type] ?? 'bg-gray-100 text-gray-800';
                                    @endphp
                                    <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium {{ $typeClass }}">
                                        {{ $log->type_label }}
                                    </span>
                                </td>
                                <td class="px-3 py-3 whitespace-nowrap text-gray-700 text-xs">
                                    {{ $log->trigger_label }}
                                </td>
                                <td class="px-3 py-3 whitespace-nowrap text-gray-700">
                                    <div class="flex flex-col text-xs">
                                        @if (!empty($meta['next_maintenance_due_at']))
                                            <span title="{{ __('Due Date') }}">
                                                <span class="text-gray-500">{{ __('Date:') }}</span>
                                                {{ $meta['next_maintenance_due_at'] }}
                                            </span>
                                        @endif
                                        @if (!empty($meta['next_maintenance_due_odometer']))
                                            <span title="{{ __('Due Odometer') }}">
                                                <span class="text-gray-500">{{ __('Odo:') }}</span>
                                                {{ number_format($meta['next_maintenance_due_odometer']) }} km
                                            </span>
                                        @endif
                                        @if (empty($meta['next_maintenance_due_at']) && empty($meta['next_maintenance_due_odometer']))
                                            <span class="text-gray-400">—</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-3 py-3 whitespace-nowrap">
                                    <div class="flex flex-col items-start gap-1">
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
                                        @if ($log->retry_count > 0)
                                            <span class="text-xs text-gray-500" title="{{ __('Retry attempts') }}">
                                                {{ $log->retry_count }}/{{ config('motorpool.notifications.max_retries', 3) }} {{ __('retries') }}
                                            </span>
                                        @endif
                                        @if ($log->max_retries_reached)
                                            <span class="text-xs text-red-600 font-medium">{{ __('Max reached') }}</span>
                                        @endif
                                        @if ($log->error_message)
                                            <span class="text-xs text-red-600 truncate max-w-[120px]" title="{{ $log->error_message }}">
                                                {{ Str::limit($log->error_message, 20) }}
                                            </span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-3 py-3 whitespace-nowrap">
                                    <div class="flex flex-col items-start gap-1 text-xs">
                                        @if ($log->maintenanceRecord)
                                            <a
                                                href="{{ route('maintenance.show', $log->maintenance_record_id) }}"
                                                class="text-indigo-600 hover:text-indigo-900 font-medium"
                                            >
                                                {{ __('Maintenance #:id', ['id' => $log->maintenance_record_id]) }}
                                            </a>
                                            @php
                                                $resolutionStatus = $log->resolution_status;
                                                $resolutionClasses = [
                                                    'on_time' => 'bg-green-100 text-green-800',
                                                    'late' => 'bg-red-100 text-red-800',
                                                ];
                                                $resolutionClass = $resolutionStatus ? ($resolutionClasses[$resolutionStatus] ?? 'bg-gray-100 text-gray-800') : 'bg-gray-100 text-gray-800';
                                            @endphp
                                            <span class="inline-flex items-center rounded-full px-2 py-0.5 text-[11px] font-medium {{ $resolutionClass }}">
                                                {{ $log->resolution_label }}
                                            </span>
                                        @else
                                            <span class="text-gray-400">{{ __('Unresolved') }}</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-3 py-3 whitespace-nowrap text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        @if ($log->channel === 'in_app' && $log->read_at === null)
                                            <button
                                                type="button"
                                                wire:click="markAsRead({{ $log->id }})"
                                                class="text-xs font-medium text-gray-600 hover:text-gray-900"
                                                title="{{ __('Mark as read') }}"
                                            >
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                                </svg>
                                            </button>
                                        @endif
                                        @if ($log->canRetry())
                                            <button
                                                type="button"
                                                wire:click="retry({{ $log->id }})"
                                                wire:loading.attr="disabled"
                                                wire:target="retry({{ $log->id }})"
                                                class="text-xs font-medium text-indigo-600 hover:text-indigo-900 disabled:opacity-50"
                                            >
                                                <span wire:loading.remove wire:target="retry({{ $log->id }})">{{ __('Retry') }}</span>
                                                <span wire:loading wire:target="retry({{ $log->id }})">{{ __('Retrying...') }}</span>
                                            </button>
                                        @elseif ($log->status !== 'sent')
                                            <span class="text-xs text-gray-400">—</span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="px-3 py-6 text-center text-gray-500">
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
