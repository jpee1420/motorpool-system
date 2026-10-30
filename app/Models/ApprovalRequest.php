<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

class ApprovalRequest extends Model
{
    use HasFactory;

    public const ACTION_CREATE = 'create';
    public const ACTION_UPDATE = 'update';

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

    protected static function booted(): void
    {
        static::created(function (self $request): void {
            static::logApprovalEvent(
                $request,
                'approval_request_created',
                null,
                [
                    'requester_user_id' => $request->requester_user_id,
                    'action' => $request->action,
                    'subject_type' => $request->subject_type,
                    'subject_id' => $request->subject_id,
                    'subject_label' => $request->subject_label,
                    'status' => $request->status ?? self::STATUS_PENDING,
                    'original_data' => $request->original_data,
                    'proposed_data' => $request->proposed_data,
                ],
            );
        });

        static::updated(function (self $request): void {
            if (! $request->wasChanged('status')) {
                return;
            }

            $status = $request->status;

            if ($status === self::STATUS_APPROVED) {
                static::logApprovalEvent(
                    $request,
                    'approval_request_approved',
                    [
                        'status' => $request->getOriginal('status'),
                        'reviewer_user_id' => $request->getOriginal('reviewer_user_id'),
                        'review_notes' => $request->getOriginal('review_notes'),
                        'reviewed_at' => $request->getOriginal('reviewed_at')?->toDateTimeString(),
                    ],
                    [
                        'requester_user_id' => $request->requester_user_id,
                        'reviewer_user_id' => $request->reviewer_user_id,
                        'subject_type' => $request->subject_type,
                        'subject_id' => $request->subject_id,
                        'subject_label' => $request->subject_label,
                        'status' => $status,
                        'review_notes' => $request->review_notes,
                        'reviewed_at' => $request->reviewed_at?->toDateTimeString(),
                    ],
                );

                return;
            }

            if ($status === self::STATUS_REJECTED) {
                static::logApprovalEvent(
                    $request,
                    'approval_request_rejected',
                    [
                        'status' => $request->getOriginal('status'),
                        'reviewer_user_id' => $request->getOriginal('reviewer_user_id'),
                        'review_notes' => $request->getOriginal('review_notes'),
                        'reviewed_at' => $request->getOriginal('reviewed_at')?->toDateTimeString(),
                    ],
                    [
                        'requester_user_id' => $request->requester_user_id,
                        'reviewer_user_id' => $request->reviewer_user_id,
                        'subject_type' => $request->subject_type,
                        'subject_id' => $request->subject_id,
                        'subject_label' => $request->subject_label,
                        'status' => $status,
                        'review_notes' => $request->review_notes,
                        'reviewed_at' => $request->reviewed_at?->toDateTimeString(),
                    ],
                );
            }
        });
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

    private static function logApprovalEvent(self $request, string $action, ?array $oldValues, array $newValues): void
    {
        if (! app()->bound('request')) {
            return;
        }

        $actorId = Auth::id() ?? $request->reviewer_user_id ?? $request->requester_user_id;

        ActivityLog::create([
            'actor_user_id' => $actorId,
            'action' => $action,
            'subject_type' => self::class,
            'subject_id' => $request->getKey(),
            'subject_label' => $request->subject_label ?: $request->subject_type,
            'old_values' => $oldValues,
            'new_values' => $newValues !== [] ? $newValues : null,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}
