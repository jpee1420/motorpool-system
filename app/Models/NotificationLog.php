<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

class NotificationLog extends Model
{
    use HasFactory;

    // Channels
    public const CHANNEL_EMAIL = 'email';
    public const CHANNEL_IN_APP = 'in_app';

    // Types (severity levels)
    public const TYPE_MAINTENANCE_UPCOMING = 'maintenance_upcoming';
    public const TYPE_MAINTENANCE_DUE = 'maintenance_due';
    public const TYPE_MAINTENANCE_OVERDUE = 'maintenance_overdue';
    public const TYPE_TRIP_TICKET_CREATED = 'trip_ticket_created';

    // Trigger reasons
    public const TRIGGER_DATE_DUE = 'date_due';
    public const TRIGGER_ODOMETER_DUE = 'odometer_due';
    public const TRIGGER_BOTH = 'both';

    // Statuses
    public const STATUS_PENDING = 'pending';
    public const STATUS_SENT = 'sent';
    public const STATUS_FAILED = 'failed';

    protected $fillable = [
        'vehicle_id',
        'user_id',
        'maintenance_record_id',
        'channel',
        'type',
        'trigger_reason',
        'meta',
        'recipient_name',
        'recipient_contact',
        'sent_at',
        'read_at',
        'archived_at',
        'status',
        'error_message',
        'retry_count',
        'max_retries_reached',
    ];

    protected function casts(): array
    {
        return [
            'sent_at' => 'datetime',
            'read_at' => 'datetime',
            'archived_at' => 'datetime',
            'meta' => 'array',
            'retry_count' => 'integer',
            'max_retries_reached' => 'boolean',
        ];
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function maintenanceRecord(): BelongsTo
    {
        return $this->belongsTo(MaintenanceRecord::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if this log can be retried.
     */
    public function canRetry(): bool
    {
        if ($this->status === self::STATUS_SENT) {
            return false;
        }

        if ($this->max_retries_reached) {
            return false;
        }

        $maxRetries = (int) config('motorpool.notifications.max_retries', 3);

        return $this->retry_count < $maxRetries;
    }

    /**
     * Increment retry count and check if max reached.
     */
    public function incrementRetry(): void
    {
        $this->retry_count++;

        $maxRetries = (int) config('motorpool.notifications.max_retries', 3);

        if ($this->retry_count >= $maxRetries) {
            $this->max_retries_reached = true;
        }

        $this->save();
    }

    /**
     * Mark in-app notification as read.
     */
    public function markAsRead(): void
    {
        if ($this->channel === self::CHANNEL_IN_APP && $this->read_at === null) {
            $this->update(['read_at' => now()]);
        }
    }

    /**
     * Get formatted type label.
     */
    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            self::TYPE_MAINTENANCE_UPCOMING => __('Upcoming'),
            self::TYPE_MAINTENANCE_DUE => __('Due'),
            self::TYPE_MAINTENANCE_OVERDUE => __('Overdue'),
            default => ucfirst(str_replace('_', ' ', $this->type)),
        };
    }

    /**
     * Get formatted trigger reason label.
     */
    public function getTriggerLabelAttribute(): string
    {
        return match ($this->trigger_reason) {
            self::TRIGGER_DATE_DUE => __('Date'),
            self::TRIGGER_ODOMETER_DUE => __('Odometer'),
            self::TRIGGER_BOTH => __('Date & Odometer'),
            default => '—',
        };
    }

    /**
     * Computed number of days late (0 = on time or early, null = unknown).
     */
    public function getDaysLateAttribute(): ?int
    {
        $maintenanceRecord = $this->maintenanceRecord;
        $dueAtRaw = $this->meta['next_maintenance_due_at'] ?? null;

        if ($maintenanceRecord === null || $dueAtRaw === null || $maintenanceRecord->performed_at === null) {
            return null;
        }

        $dueAt = Carbon::parse($dueAtRaw);
        if ($maintenanceRecord->performed_at->lte($dueAt)) {
            return 0;
        }

        return $maintenanceRecord->performed_at->diffInDays($dueAt);
    }

    /**
     * Resolution status relative to due date (on_time, late, or null).
     */
    public function getResolutionStatusAttribute(): ?string
    {
        $maintenanceRecord = $this->maintenanceRecord;
        $dueAtRaw = $this->meta['next_maintenance_due_at'] ?? null;

        if ($maintenanceRecord === null || $dueAtRaw === null || $maintenanceRecord->performed_at === null) {
            return null;
        }

        $dueAt = Carbon::parse($dueAtRaw);
        return $maintenanceRecord->performed_at->lte($dueAt) ? 'on_time' : 'late';
    }

    /**
     * Human-readable resolution label.
     */
    public function getResolutionLabelAttribute(): string
    {
        $status = $this->resolution_status;

        return match ($status) {
            'on_time' => __('On time'),
            'late' => __('Late by :days days', ['days' => $this->days_late ?? 0]),
            default => __('Unresolved'),
        };
    }
}
