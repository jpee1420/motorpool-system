<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RepairRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'vehicle_id',
        'assigned_driver_id',
        'performed_by_user_id',
        'performed_at',
        'odometer_reading',
        'description_of_work',
        'personnel_labor_cost',
        'materials_cost_total',
        'total_cost',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'performed_at' => 'datetime',
        ];
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function performedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'performed_by_user_id');
    }

    /**
     * The driver assigned to the vehicle at the time this record was created.
     */
    public function assignedDriver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_driver_id');
    }

    public function materials(): HasMany
    {
        return $this->hasMany(RepairMaterial::class);
    }
}
