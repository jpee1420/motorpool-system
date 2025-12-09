<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\MaintenanceRecord;
use App\Models\RepairRecord;
use App\Models\TripTicket;
use App\Models\User;
use App\Models\Vehicle;
use App\Policies\MaintenanceRecordPolicy;
use App\Policies\RepairRecordPolicy;
use App\Policies\TripTicketPolicy;
use App\Policies\UserPolicy;
use App\Policies\VehiclePolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register authorization policies
        Gate::policy(Vehicle::class, VehiclePolicy::class);
        Gate::policy(MaintenanceRecord::class, MaintenanceRecordPolicy::class);
        Gate::policy(RepairRecord::class, RepairRecordPolicy::class);
        Gate::policy(TripTicket::class, TripTicketPolicy::class);
        Gate::policy(User::class, UserPolicy::class);
    }
}
