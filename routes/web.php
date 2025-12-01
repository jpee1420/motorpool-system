<?php

use App\Http\Controllers\DashboardController;
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

Route::view('/', 'welcome');

Route::post('/logout', function () {
    Auth::logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect('/');
})->name('logout');

Route::get('dashboard', DashboardController::class)
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::get('vehicles', VehiclesIndex::class)
    ->middleware(['auth', 'verified'])
    ->name('vehicles.index');

Route::get('maintenance', MaintenanceIndex::class)
    ->middleware(['auth', 'verified'])
    ->name('maintenance.index');

Route::get('maintenance/{record}', MaintenanceShow::class)
    ->middleware(['auth', 'verified'])
    ->name('maintenance.show');

Route::get('repair', RepairIndex::class)
    ->middleware(['auth', 'verified'])
    ->name('repair.index');

Route::get('repair/{record}', RepairShow::class)
    ->middleware(['auth', 'verified'])
    ->name('repair.show');

Route::get('notifications', NotificationsIndex::class)
    ->middleware(['auth', 'verified'])
    ->name('notifications.index');

Route::get('calendar', CalendarIndex::class)
    ->middleware(['auth', 'verified'])
    ->name('calendar.index');

Route::get('trip-tickets', TripTicketsIndex::class)
    ->middleware(['auth', 'verified'])
    ->name('trip-tickets.index');

Route::get('trip-tickets/{ticket}', TripTicketsShow::class)
    ->middleware(['auth', 'verified'])
    ->name('trip-tickets.show');

Route::get('account/users', AccountUsersIndex::class)
    ->middleware(['auth', 'verified'])
    ->name('account.users');

Route::view('profile', 'profile')
    ->middleware(['auth', 'verified'])
    ->name('profile');

require __DIR__.'/auth.php';
