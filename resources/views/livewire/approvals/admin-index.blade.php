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
                <h1 class="text-2xl font-semibold text-gray-900">{{ __('Approval requests') }}</h1>
                <p class="mt-1 text-sm text-gray-500">{{ __('Review and approve/reject staff submissions.') }}</p>
            </div>
        </div>

        <div class="bg-white shadow-sm rounded-xl border border-gray-100 p-4 sm:p-5 space-y-4">
            <div class="grid gap-3 md:grid-cols-3">
                <div>
                    <label class="block text-xs font-medium text-gray-600">{{ __('Status') }}</label>
                    <select wire:model.live="status" class="mt-1 block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">{{ __('All') }}</option>
                        <option value="pending">{{ __('Pending') }}</option>
                        <option value="approved">{{ __('Approved') }}</option>
                        <option value="rejected">{{ __('Rejected') }}</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600">{{ __('Type') }}</label>
                    <select wire:model.live="subjectType" class="mt-1 block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">{{ __('All') }}</option>
                        @foreach ($subjectTypes as $type)
                            <option value="{{ $type }}">{{ $type }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600">{{ __('Search') }}</label>
                    <input type="text" wire:model.live.debounce.300ms="search" placeholder="{{ __('Subject or requester') }}" class="mt-1 block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>
            </div>
        </div>

        <div class="bg-white shadow-sm rounded-xl border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-3 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">{{ __('Date') }}</th>
                            <th class="px-3 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">{{ __('Requester') }}</th>
                            <th class="px-3 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">{{ __('Action') }}</th>
                            <th class="px-3 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">{{ __('Subject') }}</th>
                            <th class="px-3 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">{{ __('Status') }}</th>
                            <th class="px-3 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white">
                        @forelse ($requests as $req)
                            <tr>
                                <td class="px-3 py-3 whitespace-nowrap text-gray-700">
                                    <div class="flex flex-col">
                                        <span>{{ $req->created_at?->format('M d, Y') }}</span>
                                        <span class="text-xs text-gray-500">{{ $req->created_at?->format('h:i A') }}</span>
                                    </div>
                                </td>
                                <td class="px-3 py-3 whitespace-nowrap text-gray-700">
                                    <div class="flex flex-col">
                                        <span class="font-medium text-gray-900">{{ $req->requester?->name ?? '—' }}</span>
                                        <span class="text-xs text-gray-500">{{ $req->requester?->email }}</span>
                                    </div>
                                </td>
                                <td class="px-3 py-3 whitespace-nowrap">
                                    @php
                                        $actionClass = $req->action === 'create' ? 'bg-blue-100 text-blue-800' : 'bg-amber-100 text-amber-800';
                                    @endphp
                                    <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium {{ $actionClass }}">
                                        {{ ucfirst($req->action) }}
                                    </span>
                                </td>
                                <td class="px-3 py-3 whitespace-nowrap text-gray-700">
                                    <div class="flex flex-col">
                                        <span class="font-medium text-gray-900">{{ $req->subject_label ?? '—' }}</span>
                                        <span class="text-xs text-gray-500">{{ $req->subject_type }}</span>
                                    </div>
                                </td>
                                <td class="px-3 py-3 whitespace-nowrap">
                                    @php
                                        $statusClasses = [
                                            'pending' => 'bg-yellow-100 text-yellow-800',
                                            'approved' => 'bg-green-100 text-green-800',
                                            'rejected' => 'bg-red-100 text-red-800',
                                        ];
                                        $statusClass = $statusClasses[$req->status] ?? 'bg-gray-100 text-gray-800';
                                    @endphp
                                    <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium {{ $statusClass }}">
                                        {{ ucfirst($req->status) }}
                                    </span>
                                </td>
                                <td class="px-3 py-3 whitespace-nowrap">
                                    @if ($req->isPending())
                                        <div class="flex items-center gap-2">
                                            <button type="button" wire:click="openReview({{ $req->id }}, 'approve')" class="inline-flex items-center rounded-lg bg-green-600 px-2.5 py-1.5 text-xs font-medium text-white hover:bg-green-700">
                                                {{ __('Approve') }}
                                            </button>
                                            <button type="button" wire:click="openReview({{ $req->id }}, 'reject')" class="inline-flex items-center rounded-lg bg-red-600 px-2.5 py-1.5 text-xs font-medium text-white hover:bg-red-700">
                                                {{ __('Reject') }}
                                            </button>
                                        </div>
                                    @else
                                        <span class="text-xs text-gray-500">
                                            {{ $req->reviewer?->name ?? '—' }}
                                            @if ($req->reviewed_at)
                                                <br>{{ $req->reviewed_at->format('M d, Y') }}
                                            @endif
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-3 py-6 text-center text-gray-500">
                                    {{ __('No approval requests found.') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="border-t border-gray-100 bg-gray-50 px-4 py-3 sm:px-6">
                {{ $requests->links() }}
            </div>
        </div>

        @if ($showReviewModal && $reviewingRequest)
            <div class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true" wire:keydown.escape.window="closeReview">
                <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-6">
                    <div class="fixed inset-0 bg-gray-900/50" wire:click="closeReview"></div>
                    <div class="relative w-full max-w-3xl transform overflow-hidden rounded-xl bg-white text-left shadow-xl">
                        <div class="flex items-start justify-between border-b border-gray-100 px-5 py-4">
                            <div>
                                <h2 class="text-base font-semibold text-gray-900">
                                    {{ $reviewAction === 'approve' ? __('Approve request') : __('Reject request') }}
                                </h2>
                                <p class="mt-1 text-sm text-gray-500">
                                    {{ ucfirst($reviewingRequest->action) }} {{ $reviewingRequest->subject_type }}
                                    <span class="text-gray-300">•</span>
                                    {{ $reviewingRequest->subject_label }}
                                    <span class="text-gray-300">•</span>
                                    {{ __('by') }} {{ $reviewingRequest->requester?->name }}
                                </p>
                            </div>
                            <button type="button" wire:click="closeReview" class="ml-4 inline-flex h-9 w-9 items-center justify-center rounded-lg text-gray-500 hover:bg-gray-100">
                                <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </button>
                        </div>

                        <div class="px-5 py-4 space-y-4">
                            <div class="max-h-[40vh] overflow-auto rounded-lg border border-gray-100">
                                <div class="divide-y divide-gray-100">
                                    @php
                                        $proposed = $reviewingRequest->proposed_data ?? [];
                                        $original = $reviewingRequest->original_data ?? [];
                                    @endphp
                                    @if ($reviewingRequest->action === 'create')
                                        @foreach ($proposed as $key => $value)
                                            @if ($key !== '_materials')
                                                <div class="grid grid-cols-12 gap-3 p-3 text-sm">
                                                    <div class="col-span-4 font-medium text-gray-700">{{ $key }}</div>
                                                    <div class="col-span-8 text-gray-900">{{ is_array($value) ? json_encode($value) : $value }}</div>
                                                </div>
                                            @endif
                                        @endforeach
                                    @else
                                        @foreach ($proposed as $key => $value)
                                            @if ($key !== '_materials')
                                                <div class="grid grid-cols-12 gap-3 p-3 text-sm">
                                                    <div class="col-span-3 font-medium text-gray-700">{{ $key }}</div>
                                                    <div class="col-span-4 text-gray-500">{{ is_array($original[$key] ?? null) ? json_encode($original[$key]) : ($original[$key] ?? '—') }}</div>
                                                    <div class="col-span-1 text-center text-gray-400">→</div>
                                                    <div class="col-span-4 text-gray-900">{{ is_array($value) ? json_encode($value) : $value }}</div>
                                                </div>
                                            @endif
                                        @endforeach
                                    @endif
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">{{ __('Notes (optional)') }}</label>
                                <textarea wire:model="reviewNotes" rows="2" class="mt-1 block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="{{ __('Add a note for the requester...') }}"></textarea>
                            </div>
                        </div>

                        <div class="flex justify-end gap-2 border-t border-gray-100 bg-gray-50 px-5 py-3">
                            <button type="button" wire:click="closeReview" class="inline-flex items-center rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                                {{ __('Cancel') }}
                            </button>
                            <button
                                type="button"
                                wire:click="confirmReview"
                                class="inline-flex items-center rounded-lg px-4 py-2 text-sm font-medium text-white {{ $reviewAction === 'approve' ? 'bg-green-600 hover:bg-green-700' : 'bg-red-600 hover:bg-red-700' }}"
                            >
                                {{ $reviewAction === 'approve' ? __('Approve') : __('Reject') }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
