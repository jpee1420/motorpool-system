<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApprovalRequest extends Model
{
    use HasFactory;

    public const ACTION_CREATE = 'create';
    public const ACTION_UPDATE = 'update';
    public const ACTION_CANCEL = 'cancel';

    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';

    protected $fillable = [
        'requester_user_id',
        'action',
        'subject_type',
        'subject_id',
        'subject_label',
        'original_data',
        'proposed_data',
        'status',
        'reviewer_user_id',
        'review_notes',
        'reviewed_at',
    ];

    protected function casts(): array
    {
        return [
            'original_data' => 'array',
            'proposed_data' => 'array',
            'reviewed_at' => 'datetime',
        ];
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requester_user_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewer_user_id');
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    public function isRejected(): bool
    {
        return $this->status === self::STATUS_REJECTED;
    }

    public function getSubjectModelClass(): ?string
    {
        return match ($this->subject_type) {
            'Vehicle' => Vehicle::class,
            'MaintenanceRecord' => MaintenanceRecord::class,
            'RepairRecord' => RepairRecord::class,
            'TripTicket' => TripTicket::class,
            default => null,
        };
    }
}
