<?php

use App\Http\Controllers\DashboardController;
use App\Livewire\ActivityLogs\Index as ActivityLogsIndex;
use App\Livewire\Approvals\AdminIndex as ApprovalsAdminIndex;
use App\Livewire\Approvals\StaffIndex as ApprovalsStaffIndex;
use App\Livewire\Account\UsersIndex as AccountUsersIndex;
use App\Livewire\Calendar\Index as CalendarIndex;
use App\Livewire\Maintenance\Index as MaintenanceIndex;
use App\Livewire\Maintenance\Show as MaintenanceShow;
use App\Livewire\Notifications\Index as NotificationsIndex;
use App\Livewire\Repair\Index as RepairIndex;
use App\Livewire\Repair\Show as RepairShow;
use App\Livewire\TripTickets\Index as TripTicketsIndex;
use App\Livewire\TripTickets\Show as TripTicketsShow;
use App\Livewire\Vehicles\Index as VehiclesIndex;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

Route::get('/', function () {
    if (Auth::check()) {
        return redirect()->route('dashboard');
    }
    return view('welcome');
});

Route::post('/logout', function () {
    Auth::logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect('/');
})->name('logout');

Route::middleware(['auth', 'active'])->group(function (): void {
    Route::get('dashboard', DashboardController::class)
        ->name('dashboard');

    Route::get('vehicles', VehiclesIndex::class)
        ->name('vehicles.index');

    Route::get('maintenance', MaintenanceIndex::class)
        ->name('maintenance.index');

    Route::get('maintenance/{record}', MaintenanceShow::class)
        ->name('maintenance.show');

    Route::get('repair', RepairIndex::class)
        ->name('repair.index');

    Route::get('repair/{record}', RepairShow::class)
        ->name('repair.show');

    Route::get('notifications', NotificationsIndex::class)
        ->name('notifications.index');

    Route::get('calendar', CalendarIndex::class)
        ->name('calendar.index');

    Route::get('trip-tickets', TripTicketsIndex::class)
        ->name('trip-tickets.index');

    Route::get('trip-tickets/{ticket}', TripTicketsShow::class)
        ->name('trip-tickets.show');

    Route::get('account/users', AccountUsersIndex::class)
        ->name('account.users');

    Route::get('activity-logs', ActivityLogsIndex::class)
        ->name('activity-logs.index');

    Route::get('approvals', ApprovalsAdminIndex::class)
        ->name('approvals.admin');

    Route::get('my-submissions', ApprovalsStaffIndex::class)
        ->name('approvals.staff');

    Route::view('profile', 'profile')
        ->name('profile');
});

require __DIR__.'/auth.php';
