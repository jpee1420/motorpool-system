<?php

use App\Http\Controllers\DashboardController;
use App\Livewire\Account\UsersIndex as AccountUsersIndex;
use App\Livewire\Calendar\Index as CalendarIndex;
use App\Livewire\Maintenance\Index as MaintenanceIndex;
use App\Livewire\Maintenance\Show as MaintenanceShow;
use App\Livewire\Notifications\Index as NotificationsIndex;
use App\Livewire\TripTickets\Index as TripTicketsIndex;
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
    ->middleware(['auth'])
    ->name('vehicles.index');

Route::get('maintenance', MaintenanceIndex::class)
    ->middleware(['auth'])
    ->name('maintenance.index');

Route::get('maintenance/{record}', MaintenanceShow::class)
    ->middleware(['auth'])
    ->name('maintenance.show');

Route::get('notifications', NotificationsIndex::class)
    ->middleware(['auth'])
    ->name('notifications.index');

Route::get('calendar', CalendarIndex::class)
    ->middleware(['auth'])
    ->name('calendar.index');

Route::get('trip-tickets', TripTicketsIndex::class)
    ->middleware(['auth'])
    ->name('trip-tickets.index');

Route::get('account/users', AccountUsersIndex::class)
    ->middleware(['auth'])
    ->name('account.users');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

require __DIR__.'/auth.php';
