<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Vehicle extends Model
{
    use HasFactory;

    protected $fillable = [
        'vehicle_type',
        'plate_number',
        'chassis_number',
        'make',
        'model',
        'year',
        'engine_number',
        'driver_operator',
        'contact_number',
        'status',
        'photo_path',
        'current_odometer',
        'last_maintenance_at',
        'last_maintenance_odometer',
        'next_maintenance_due_at',
        'next_maintenance_due_odometer',
    ];

    protected function casts(): array
    {
        return [
            'last_maintenance_at' => 'datetime',
            'next_maintenance_due_at' => 'date',
        ];
    }

    public function maintenanceRecords(): HasMany
    {
        return $this->hasMany(MaintenanceRecord::class);
    }

    public function notificationLogs(): HasMany
    {
        return $this->hasMany(NotificationLog::class);
    }
}
