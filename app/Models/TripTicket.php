<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TripTicket extends Model
{
    use HasFactory;

    protected $fillable = [
        'vehicle_id',
        'requested_by_user_id',
        'driver_name',
        'destination',
        'purpose',
        'departure_at',
        'return_at',
        'odometer_start',
        'odometer_end',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'departure_at' => 'datetime',
            'return_at' => 'datetime',
        ];
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function requestedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by_user_id');
    }
}
