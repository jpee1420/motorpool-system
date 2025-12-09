<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\MaintenanceRecord;
use App\Models\NotificationLog;
use App\Models\Vehicle;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $user = Auth::user();
        $isStaffOrAbove = $user?->isStaffOrAbove() ?? false;
        $today = today();

        // For drivers, only show their assigned vehicles
        $vehicleQuery = Vehicle::query();
        if (! $isStaffOrAbove && $user !== null) {
            $vehicleQuery->where('user_id', $user->id);
        }

        $totalVehicles = (clone $vehicleQuery)->count();

        // Get vehicle IDs for filtering maintenance records
        $vehicleIds = (clone $vehicleQuery)->pluck('id');

        $maintenanceRecordCount = MaintenanceRecord::whereIn('vehicle_id', $vehicleIds)->count();

        $upcomingMaintenanceCount = (clone $vehicleQuery)
            ->whereDate('next_maintenance_due_at', '>=', $today)
            ->count();

        $overdueMaintenanceCount = (clone $vehicleQuery)
            ->whereDate('next_maintenance_due_at', '<', $today)
            ->count();

        $upcomingMaintenance = (clone $vehicleQuery)
            ->whereNotNull('next_maintenance_due_at')
            ->orderBy('next_maintenance_due_at')
            ->take(5)
            ->get();

        $recentMaintenance = MaintenanceRecord::with('vehicle')
            ->whereIn('vehicle_id', $vehicleIds)
            ->orderByDesc('performed_at')
            ->take(5)
            ->get();

        // Notifications only shown to staff
        $recentNotifications = collect();
        if ($isStaffOrAbove) {
            $recentNotifications = NotificationLog::with('vehicle')
                ->whereIn('status', ['sent', 'failed'])
                ->orderByDesc('sent_at')
                ->take(5)
                ->get();
        }

        return view('dashboard', compact(
            'totalVehicles',
            'maintenanceRecordCount',
            'upcomingMaintenanceCount',
            'overdueMaintenanceCount',
            'upcomingMaintenance',
            'recentMaintenance',
            'recentNotifications',
            'isStaffOrAbove',
        ));
    }
}
