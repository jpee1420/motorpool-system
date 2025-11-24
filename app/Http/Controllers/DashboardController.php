<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\MaintenanceRecord;
use App\Models\NotificationLog;
use App\Models\Vehicle;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $today = today();

        $totalVehicles = Vehicle::count();
        $maintenanceRecordCount = MaintenanceRecord::count();

        $upcomingMaintenanceCount = Vehicle::whereDate('next_maintenance_due_at', '>=', $today)->count();
        $overdueMaintenanceCount = Vehicle::whereDate('next_maintenance_due_at', '<', $today)->count();

        $upcomingMaintenance = Vehicle::whereNotNull('next_maintenance_due_at')
            ->orderBy('next_maintenance_due_at')
            ->take(5)
            ->get();

        $recentMaintenance = MaintenanceRecord::with('vehicle')
            ->orderByDesc('performed_at')
            ->take(5)
            ->get();

        $recentNotifications = NotificationLog::with('vehicle')
            ->whereIn('status', ['sent', 'failed'])
            ->orderByDesc('sent_at')
            ->take(5)
            ->get();

        return view('dashboard', compact(
            'totalVehicles',
            'maintenanceRecordCount',
            'upcomingMaintenanceCount',
            'overdueMaintenanceCount',
            'upcomingMaintenance',
            'recentMaintenance',
            'recentNotifications',
        ));
    }
}
