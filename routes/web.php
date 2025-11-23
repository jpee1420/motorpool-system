<?php

use App\Http\Controllers\DashboardController;
use App\Livewire\Maintenance\Index as MaintenanceIndex;
use App\Livewire\Vehicles\Index as VehiclesIndex;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome');

Route::get('dashboard', DashboardController::class)
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::get('vehicles', VehiclesIndex::class)
    ->middleware(['auth'])
    ->name('vehicles.index');

Route::get('maintenance', MaintenanceIndex::class)
    ->middleware(['auth'])
    ->name('maintenance.index');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

require __DIR__.'/auth.php';
