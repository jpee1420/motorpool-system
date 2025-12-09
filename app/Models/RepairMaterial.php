<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RepairMaterial extends Model
{
    use HasFactory;

    protected $fillable = [
        'repair_record_id',
        'name',
        'description',
        'quantity',
        'unit',
        'unit_cost',
        'total_cost',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:2',
            'unit_cost' => 'decimal:2',
            'total_cost' => 'decimal:2',
        ];
    }

    public function repairRecord(): BelongsTo
    {
        return $this->belongsTo(RepairRecord::class);
    }
}
