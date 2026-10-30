@props([
    'title',
    'description' => null,
    'confirmText' => __('Delete'),
    'cancelText' => __('Cancel'),
    'confirmWireClick' => null,
])

<div
    x-cloak
    x-show="confirmOpen"
    x-transition
    class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 px-4"
>
    <div class="w-full max-w-md rounded-xl bg-white shadow-xl border border-gray-200">
        <div class="px-4 py-3 border-b border-gray-100">
            <h2 class="text-sm font-semibold text-gray-900">
                {{ $title }}
            </h2>
        </div>

        <div class="px-4 py-3 text-sm text-gray-700 space-y-2">
            @if ($description !== null)
                <p>
                    {{ $description }}
                </p>
            @else
                {{ $slot }}
            @endif
        </div>

        <div class="px-4 py-3 flex justify-end gap-2 border-t border-gray-100">
            <button
                type="button"
                class="rounded-lg border border-gray-300 px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-50"
                x-on:click="confirmOpen = false"
            >
                {{ $cancelText }}
            </button>

            <button
                type="button"
                @if ($confirmWireClick)
                    wire:click="{{ $confirmWireClick }}"
                @endif
                class="inline-flex items-center rounded-lg bg-red-600 px-3 py-1.5 text-xs font-semibold text-white shadow-sm hover:bg-red-700"
                x-on:click="confirmOpen = false"
            >
                {{ $confirmText }}
            </button>
        </div>
    </div>
</div>
