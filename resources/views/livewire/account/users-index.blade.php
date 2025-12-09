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
                    {{ __('User management') }}
                    @if ($pendingCount > 0)
                        <span class="ml-2 inline-flex items-center rounded-full bg-yellow-100 px-2.5 py-0.5 text-sm font-medium text-yellow-800">
                            {{ $pendingCount }} {{ __('pending') }}
                        </span>
                    @endif
                </h1>
                <p class="mt-1 text-sm text-gray-500">
                    {{ __('Manage user accounts, roles, and statuses.') }}
                </p>
            </div>

            @can('manage', \App\Models\User::class)
                <div class="flex items-center">
                    <button
                        type="button"
                        wire:click="openCreateUserModal"
                        class="inline-flex items-center rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700"
                    >
                        {{ __('Create user') }}
                    </button>
                </div>
            @endcan
        </div>

        <div class="bg-white shadow-sm rounded-xl border border-gray-100 p-4 sm:p-5 space-y-4">
            <div class="grid gap-3 md:grid-cols-4 lg:grid-cols-6">
                <div class="md:col-span-2">
                    <label class="block text-xs font-medium text-gray-600">
                        {{ __('Search') }}
                    </label>
                    <input
                        type="text"
                        wire:model.live="search"
                        placeholder="{{ __('Name or email') }}"
                        class="mt-1 block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    >
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-600">
                        {{ __('Role') }}
                    </label>
                    <select
                        wire:model.live="role"
                        class="mt-1 block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    >
                        <option value="">{{ __('All') }}</option>
                        <option value="admin">{{ __('Admin') }}</option>
                        <option value="staff">{{ __('Staff') }}</option>
                        <option value="user">{{ __('User') }}</option>
                    </select>
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
                        <option value="pending">{{ __('Pending') }}</option>
                        <option value="active">{{ __('Active') }}</option>
                        <option value="disabled">{{ __('Disabled') }}</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="bg-white shadow-sm rounded-xl border border-gray-100 overflow-hidden">
            @php
                $collection = $users->getCollection();
                $staffAdmins = $collection->filter(fn ($user) => in_array($user->role, ['admin', 'staff'], true));
                $regularUsers = $collection->filter(fn ($user) => $user->role === 'user');
            @endphp

            {{-- Admins & Staff table --}}
            <div class="border-b border-gray-100">
                <div class="px-4 py-3 bg-gray-50 flex items-center justify-between">
                    <h2 class="text-sm font-semibold text-gray-900">{{ __('Admins & staff') }}</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-3 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                    <button type="button" wire:click="sortBy('name')" class="flex items-center gap-1">
                                        <span>{{ __('Name') }}</span>
                                        @if ($sortField === 'name')
                                            <span class="text-[10px]">{{ $sortDirection === 'asc' ? '▲' : '▼' }}</span>
                                        @endif
                                    </button>
                                </th>
                                <th class="px-3 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                    <button type="button" wire:click="sortBy('email')" class="flex items-center gap-1">
                                        <span>{{ __('Email') }}</span>
                                        @if ($sortField === 'email')
                                            <span class="text-[10px]">{{ $sortDirection === 'asc' ? '▲' : '▼' }}</span>
                                        @endif
                                    </button>
                                </th>
                                <th class="px-3 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                    <button type="button" wire:click="sortBy('role')" class="flex items-center gap-1">
                                        <span>{{ __('Role') }}</span>
                                        @if ($sortField === 'role')
                                            <span class="text-[10px]">{{ $sortDirection === 'asc' ? '▲' : '▼' }}</span>
                                        @endif
                                    </button>
                                </th>
                                <th class="px-3 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                    <button type="button" wire:click="sortBy('status')" class="flex items-center gap-1">
                                        <span>{{ __('Status') }}</span>
                                        @if ($sortField === 'status')
                                            <span class="text-[10px]">{{ $sortDirection === 'asc' ? '▲' : '▼' }}</span>
                                        @endif
                                    </button>
                                </th>
                                <th class="px-3 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                    <button type="button" wire:click="sortBy('created_at')" class="flex items-center gap-1">
                                        <span>{{ __('Created') }}</span>
                                        @if ($sortField === 'created_at')
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
                            @forelse ($staffAdmins as $user)
                                <tr>
                                    <td class="px-3 py-3 whitespace-nowrap text-gray-900 font-medium">
                                        {{ $user->name }}
                                    </td>
                                    <td class="px-3 py-3 whitespace-nowrap text-gray-700">
                                        {{ $user->email }}
                                    </td>
                                    <td class="px-3 py-3 whitespace-nowrap text-gray-700">
                                        <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium bg-gray-100 text-gray-800">
                                            {{ ucfirst($user->role) }}
                                        </span>
                                    </td>
                                    <td class="px-3 py-3 whitespace-nowrap">
                                        @php
                                            $statusClasses = [
                                                'pending' => 'bg-yellow-100 text-yellow-800',
                                                'active' => 'bg-green-100 text-green-800',
                                                'disabled' => 'bg-red-100 text-red-800',
                                            ];
                                            $statusClass = $statusClasses[$user->status] ?? 'bg-gray-100 text-gray-800';
                                        @endphp
                                        <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $statusClass }}">
                                            {{ ucfirst($user->status) }}
                                        </span>
                                    </td>
                                    <td class="px-3 py-3 whitespace-nowrap text-gray-700">
                                        {{ $user->created_at?->format('M d, Y') ?? '—' }}
                                    </td>
                                    <td class="px-3 py-3 whitespace-nowrap text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <div class="relative inline-block text-left">
                                                <div class="flex items-center gap-1">
                                                    <span class="text-xs text-gray-500 mr-1">{{ __('Role') }}</span>
                                                    <select
                                                        wire:change="setRole({{ $user->id }}, $event.target.value)"
                                                        class="rounded-md border-gray-300 text-xs shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                                    >
                                                        <option value="admin" @selected($user->role === 'admin')>{{ __('Admin') }}</option>
                                                        <option value="staff" @selected($user->role === 'staff')>{{ __('Staff') }}</option>
                                                        <option value="user" @selected($user->role === 'user')>{{ __('User') }}</option>
                                                    </select>
                                                </div>
                                            </div>

                                            @if (auth()->id() !== $user->id)
                                                @if ($user->status === 'pending')
                                                    <button
                                                        type="button"
                                                        wire:click="confirmApprove({{ $user->id }})"
                                                        class="inline-flex items-center rounded-lg bg-green-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-green-700"
                                                    >
                                                        {{ __('Approve') }}
                                                    </button>
                                                    <button
                                                        type="button"
                                                        wire:click="confirmReject({{ $user->id }})"
                                                        class="inline-flex items-center rounded-lg bg-red-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-red-700"
                                                    >
                                                        {{ __('Reject') }}
                                                    </button>
                                                @else
                                                    <button
                                                        type="button"
                                                        wire:click="toggleStatus({{ $user->id }})"
                                                        class="inline-flex items-center rounded-lg border border-gray-300 px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-50"
                                                    >
                                                        @if ($user->status === 'active')
                                                            {{ __('Disable') }}
                                                        @else
                                                            {{ __('Activate') }}
                                                        @endif
                                                    </button>
                                                    <button
                                                        type="button"
                                                        wire:click="openPasswordResetModal({{ $user->id }})"
                                                        class="inline-flex items-center rounded-lg border border-gray-300 px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-50"
                                                    >
                                                        {{ __('Reset Password') }}
                                                    </button>
                                                @endif
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-3 py-4 text-center text-gray-500">
                                        {{ __('No admins or staff found.') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Regular users table --}}
            <div>
                <div class="px-4 py-3 bg-gray-50 border-t border-gray-100 flex items-center justify-between">
                    <h2 class="text-sm font-semibold text-gray-900">{{ __('Users') }}</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-3 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                    <button type="button" wire:click="sortBy('name')" class="flex items-center gap-1">
                                        <span>{{ __('Name') }}</span>
                                        @if ($sortField === 'name')
                                            <span class="text-[10px]">{{ $sortDirection === 'asc' ? '▲' : '▼' }}</span>
                                        @endif
                                    </button>
                                </th>
                                <th class="px-3 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                    <button type="button" wire:click="sortBy('email')" class="flex items-center gap-1">
                                        <span>{{ __('Email') }}</span>
                                        @if ($sortField === 'email')
                                            <span class="text-[10px]">{{ $sortDirection === 'asc' ? '▲' : '▼' }}</span>
                                        @endif
                                    </button>
                                </th>
                                <th class="px-3 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                    <button type="button" wire:click="sortBy('role')" class="flex items-center gap-1">
                                        <span>{{ __('Role') }}</span>
                                        @if ($sortField === 'role')
                                            <span class="text-[10px]">{{ $sortDirection === 'asc' ? '▲' : '▼' }}</span>
                                        @endif
                                    </button>
                                </th>
                                <th class="px-3 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                    <button type="button" wire:click="sortBy('status')" class="flex items-center gap-1">
                                        <span>{{ __('Status') }}</span>
                                        @if ($sortField === 'status')
                                            <span class="text-[10px]">{{ $sortDirection === 'asc' ? '▲' : '▼' }}</span>
                                        @endif
                                    </button>
                                </th>
                                <th class="px-3 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                    <button type="button" wire:click="sortBy('created_at')" class="flex items-center gap-1">
                                        <span>{{ __('Created') }}</span>
                                        @if ($sortField === 'created_at')
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
                            @forelse ($regularUsers as $user)
                                <tr>
                                    <td class="px-3 py-3 whitespace-nowrap text-gray-900 font-medium">
                                        {{ $user->name }}
                                    </td>
                                    <td class="px-3 py-3 whitespace-nowrap text-gray-700">
                                        {{ $user->email }}
                                    </td>
                                    <td class="px-3 py-3 whitespace-nowrap text-gray-700">
                                        <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium bg-gray-100 text-gray-800">
                                            {{ ucfirst($user->role) }}
                                        </span>
                                    </td>
                                    <td class="px-3 py-3 whitespace-nowrap">
                                        @php
                                            $statusClasses = [
                                                'pending' => 'bg-yellow-100 text-yellow-800',
                                                'active' => 'bg-green-100 text-green-800',
                                                'disabled' => 'bg-red-100 text-red-800',
                                            ];
                                            $statusClass = $statusClasses[$user->status] ?? 'bg-gray-100 text-gray-800';
                                        @endphp
                                        <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $statusClass }}">
                                            {{ ucfirst($user->status) }}
                                        </span>
                                    </td>
                                    <td class="px-3 py-3 whitespace-nowrap text-gray-700">
                                        {{ $user->created_at?->format('M d, Y') ?? '—' }}
                                    </td>
                                    <td class="px-3 py-3 whitespace-nowrap text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <div class="relative inline-block text-left">
                                                <div class="flex items-center gap-1">
                                                    <span class="text-xs text-gray-500 mr-1">{{ __('Role') }}</span>
                                                    <select
                                                        wire:change="setRole({{ $user->id }}, $event.target.value)"
                                                        class="rounded-md border-gray-300 text-xs shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                                    >
                                                        <option value="admin" @selected($user->role === 'admin')>{{ __('Admin') }}</option>
                                                        <option value="staff" @selected($user->role === 'staff')>{{ __('Staff') }}</option>
                                                        <option value="user" @selected($user->role === 'user')>{{ __('User') }}</option>
                                                    </select>
                                                </div>
                                            </div>

                                            @if (auth()->id() !== $user->id)
                                                @if ($user->status === 'pending')
                                                    <button
                                                        type="button"
                                                        wire:click="confirmApprove({{ $user->id }})"
                                                        class="inline-flex items-center rounded-lg bg-green-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-green-700"
                                                    >
                                                        {{ __('Approve') }}
                                                    </button>
                                                    <button
                                                        type="button"
                                                        wire:click="confirmReject({{ $user->id }})"
                                                        class="inline-flex items-center rounded-lg bg-red-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-red-700"
                                                    >
                                                        {{ __('Reject') }}
                                                    </button>
                                                @else
                                                    <button
                                                        type="button"
                                                        wire:click="toggleStatus({{ $user->id }})"
                                                        class="inline-flex items-center rounded-lg border border-gray-300 px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-50"
                                                    >
                                                        @if ($user->status === 'active')
                                                            {{ __('Disable') }}
                                                        @else
                                                            {{ __('Activate') }}
                                                        @endif
                                                    </button>
                                                    <button
                                                        type="button"
                                                        wire:click="openPasswordResetModal({{ $user->id }})"
                                                        class="inline-flex items-center rounded-lg border border-gray-300 px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-50"
                                                    >
                                                        {{ __('Reset Password') }}
                                                    </button>
                                                @endif
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-3 py-4 text-center text-gray-500">
                                        {{ __('No users found.') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="border-t border-gray-100 bg-gray-50 px-4 py-3 sm:px-6">
                {{ $users->links() }}
            </div>
        </div>
    </div>

    {{-- Create user modal --}}
    @if ($showCreateUserModal)
        <div class="fixed inset-0 z-40 flex items-center justify-center bg-gray-900/50">
            <div class="bg-white rounded-xl shadow-xl max-w-md w-full mx-4 p-6 border border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900 mb-2">
                    {{ __('Create user account') }}
                </h2>
                <p class="text-sm text-gray-700 mb-4">
                    {{ __('Use this form to create an account for users who cannot register themselves.') }}
                </p>

                <form wire:submit="createUser" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">{{ __('Name') }}</label>
                        <input
                            type="text"
                            wire:model="newUserName"
                            class="mt-1 block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        >
                        @error('newUserName')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">{{ __('Email') }}</label>
                        <input
                            type="email"
                            wire:model="newUserEmail"
                            class="mt-1 block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        >
                        @error('newUserEmail')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">{{ __('Role') }}</label>
                            <select
                                wire:model="newUserRole"
                                class="mt-1 block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            >
                                <option value="admin">{{ __('Admin') }}</option>
                                <option value="staff">{{ __('Staff') }}</option>
                                <option value="user">{{ __('User') }}</option>
                            </select>
                            @error('newUserRole')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">{{ __('Status') }}</label>
                            <select
                                wire:model="newUserStatus"
                                class="mt-1 block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            >
                                <option value="active">{{ __('Active') }}</option>
                                <option value="disabled">{{ __('Disabled') }}</option>
                            </select>
                            @error('newUserStatus')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">{{ __('Password') }}</label>
                        <div class="flex gap-2 mt-1">
                            <input
                                type="text"
                                wire:model="newUserPassword"
                                class="block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                placeholder="{{ __('Enter password') }}"
                            >
                            <button
                                type="button"
                                wire:click="generateNewUserPassword"
                                class="inline-flex items-center rounded-lg border border-gray-300 px-3 py-2 text-xs font-medium text-gray-700 hover:bg-gray-50 whitespace-nowrap"
                            >
                                {{ __('Generate') }}
                            </button>
                        </div>
                        @error('newUserPassword')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">{{ __('Confirm Password') }}</label>
                        <input
                            type="text"
                            wire:model="newUserPasswordConfirmation"
                            class="mt-1 block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            placeholder="{{ __('Confirm password') }}"
                        >
                    </div>

                    <p class="text-xs text-gray-500">
                        {{ __('The password is shown in plain text so you can share it securely with the user (e.g., in person or via phone).') }}
                    </p>

                    <div class="mt-6 flex justify-end gap-3">
                        <button
                            type="button"
                            wire:click="closeCreateUserModal"
                            class="inline-flex items-center rounded-lg border border-gray-300 px-4 py-2 text-xs font-medium text-gray-700 hover:bg-gray-50"
                        >
                            {{ __('Cancel') }}
                        </button>

                        <button
                            type="submit"
                            class="inline-flex items-center rounded-lg bg-indigo-600 px-4 py-2 text-xs font-medium text-white hover:bg-indigo-700"
                        >
                            {{ __('Create user') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- Approval / rejection confirmation modal --}}
    @if ($confirmingUserId)
        <div class="fixed inset-0 z-40 flex items-center justify-center bg-gray-900/50">
            <div class="bg-white rounded-xl shadow-xl max-w-md w-full mx-4 p-6 border border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900 mb-2">
                    @if ($confirmingAction === 'approve')
                        {{ __('Approve user') }}
                    @elseif ($confirmingAction === 'reject')
                        {{ __('Reject registration') }}
                    @endif
                </h2>
                <p class="text-sm text-gray-700 mb-4">
                    @if ($confirmingAction === 'approve')
                        {{ __('Are you sure you want to approve :name? They will be able to log in to the system.', ['name' => $confirmingUserName]) }}
                    @elseif ($confirmingAction === 'reject')
                        {{ __('Are you sure you want to reject the registration for :name? This user record will be deleted.', ['name' => $confirmingUserName]) }}
                    @endif
                </p>

                <div class="mt-6 flex justify-end gap-3">
                    <button
                        type="button"
                        wire:click="cancelConfirmation"
                        class="inline-flex items-center rounded-lg border border-gray-300 px-4 py-2 text-xs font-medium text-gray-700 hover:bg-gray-50"
                    >
                        {{ __('Cancel') }}
                    </button>

                    <button
                        type="button"
                        wire:click="performConfirmation"
                        class="inline-flex items-center rounded-lg px-4 py-2 text-xs font-medium text-white @if ($confirmingAction === 'approve') bg-green-600 hover:bg-green-700 @else bg-red-600 hover:bg-red-700 @endif"
                    >
                        @if ($confirmingAction === 'approve')
                            {{ __('Yes, approve') }}
                        @elseif ($confirmingAction === 'reject')
                            {{ __('Yes, reject') }}
                        @endif
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- Password reset modal --}}
    @if ($showPasswordResetModal)
        <div class="fixed inset-0 z-40 flex items-center justify-center bg-gray-900/50">
            <div class="bg-white rounded-xl shadow-xl max-w-md w-full mx-4 p-6 border border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900 mb-2">
                    {{ __('Reset Password') }}
                </h2>
                <p class="text-sm text-gray-700 mb-4">
                    {{ __('Set a new password for :name.', ['name' => $resetPasswordUserName]) }}
                </p>

                <form wire:submit="resetUserPassword" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">{{ __('New Password') }}</label>
                        <div class="flex gap-2 mt-1">
                            <input
                                type="text"
                                wire:model="newPassword"
                                class="block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                placeholder="{{ __('Enter new password') }}"
                            >
                            <button
                                type="button"
                                wire:click="generateRandomPassword"
                                class="inline-flex items-center rounded-lg border border-gray-300 px-3 py-2 text-xs font-medium text-gray-700 hover:bg-gray-50 whitespace-nowrap"
                            >
                                {{ __('Generate') }}
                            </button>
                        </div>
                        @error('newPassword')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">{{ __('Confirm Password') }}</label>
                        <input
                            type="text"
                            wire:model="newPasswordConfirmation"
                            class="mt-1 block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            placeholder="{{ __('Confirm new password') }}"
                        >
                    </div>

                    <p class="text-xs text-gray-500">
                        {{ __('Note: The password is shown in plain text so you can share it with the user.') }}
                    </p>

                    <div class="mt-6 flex justify-end gap-3">
                        <button
                            type="button"
                            wire:click="closePasswordResetModal"
                            class="inline-flex items-center rounded-lg border border-gray-300 px-4 py-2 text-xs font-medium text-gray-700 hover:bg-gray-50"
                        >
                            {{ __('Cancel') }}
                        </button>

                        <button
                            type="submit"
                            class="inline-flex items-center rounded-lg bg-indigo-600 px-4 py-2 text-xs font-medium text-white hover:bg-indigo-700"
                        >
                            {{ __('Reset Password') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
