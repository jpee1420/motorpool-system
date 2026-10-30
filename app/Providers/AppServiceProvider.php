<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\ActivityLog;
use App\Models\ApprovalRequest;
use App\Models\MaintenanceRecord;
use App\Models\MaintenanceMaterial;
use App\Models\NotificationLog;
use App\Models\RepairRecord;
use App\Models\RepairMaterial;
use App\Models\TripTicket;
use App\Models\User;
use App\Models\Vehicle;
use App\Observers\ActivityLogObserver;
use App\Policies\ActivityLogPolicy;
use App\Policies\ApprovalRequestPolicy;
use App\Policies\MaintenanceRecordPolicy;
use App\Policies\NotificationLogPolicy;
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
        Gate::policy(NotificationLog::class, NotificationLogPolicy::class);
        Gate::policy(ActivityLog::class, ActivityLogPolicy::class);
        Gate::policy(ApprovalRequest::class, ApprovalRequestPolicy::class);

        Vehicle::observe(ActivityLogObserver::class);
        MaintenanceRecord::observe(ActivityLogObserver::class);
        RepairRecord::observe(ActivityLogObserver::class);
        MaintenanceMaterial::observe(ActivityLogObserver::class);
        RepairMaterial::observe(ActivityLogObserver::class);
        TripTicket::observe(ActivityLogObserver::class);
        User::observe(ActivityLogObserver::class);
    }
}
