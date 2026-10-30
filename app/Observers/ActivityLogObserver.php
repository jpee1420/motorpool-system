<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;

class ActivityLogObserver
{
    private const EXCLUDED_KEYS = [
        'id',
        'created_at',
        'updated_at',
        'deleted_at',
        'remember_token',
        'password',
    ];

    public function created(Model $model): void
    {
        $this->logCreated($model);
    }

    public function updated(Model $model): void
    {
        $this->logUpdated($model);
    }

    private function logCreated(Model $model): void
    {
        if (! $this->shouldLog($model)) {
            return;
        }

        $newValues = $this->filterAttributes($model, $model->getAttributes());

        [$ipAddress, $userAgent] = $this->getRequestMeta();

        ActivityLog::create([
            'actor_user_id' => Auth::id(),
            'action' => 'created',
            'subject_type' => $model::class,
            'subject_id' => $model->getKey(),
            'subject_label' => $this->makeSubjectLabel($model),
            'old_values' => null,
            'new_values' => $newValues !== [] ? $newValues : null,
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
        ]);
    }

    private function logUpdated(Model $model): void
    {
        if (! $this->shouldLog($model)) {
            return;
        }

        $newValues = $this->filterAttributes($model, $model->getChanges());

        if (array_key_exists('password', $model->getChanges())) {
            $newValues['password'] = '[REDACTED]';
        }

        if ($newValues === []) {
            return;
        }

        $oldValues = [];

        foreach (array_keys($newValues) as $key) {
            $oldValues[$key] = $key === 'password'
                ? '[REDACTED]'
                : $model->getOriginal($key);
        }

        $oldValues = $this->filterAttributes($model, $oldValues);

        if (array_key_exists('password', $model->getChanges())) {
            $oldValues['password'] = '[REDACTED]';
        }

        [$ipAddress, $userAgent] = $this->getRequestMeta();

        ActivityLog::create([
            'actor_user_id' => Auth::id(),
            'action' => 'updated',
            'subject_type' => $model::class,
            'subject_id' => $model->getKey(),
            'subject_label' => $this->makeSubjectLabel($model),
            'old_values' => $oldValues !== [] ? $oldValues : null,
            'new_values' => $newValues !== [] ? $newValues : null,
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
        ]);
    }

    private function shouldLog(Model $model): bool
    {
        if ($model instanceof ActivityLog) {
            return false;
        }

        $user = Auth::user();

        if ($user === null) {
            return false;
        }

        return $user->isStaffOrAbove();
    }

    private function filterAttributes(Model $model, array $attributes): array
    {
        $fillable = $model->getFillable();

        if ($fillable !== []) {
            $attributes = Arr::only($attributes, $fillable);
        }

        foreach (self::EXCLUDED_KEYS as $key) {
            unset($attributes[$key]);
        }

        return $attributes;
    }

    private function makeSubjectLabel(Model $model): ?string
    {
        $class = $model::class;

        return match ($class) {
            \App\Models\Vehicle::class => (string) ($model->getAttribute('plate_number') ?? ('Vehicle #' . $model->getKey())),
            \App\Models\MaintenanceRecord::class => 'Maintenance #' . $model->getKey(),
            \App\Models\RepairRecord::class => 'Repair #' . $model->getKey(),
            \App\Models\MaintenanceMaterial::class => (string) ($model->getAttribute('name') ?? ('Maintenance material #' . $model->getKey())),
            \App\Models\RepairMaterial::class => (string) ($model->getAttribute('name') ?? ('Repair material #' . $model->getKey())),
            \App\Models\TripTicket::class => 'Trip ticket #' . $model->getKey(),
            \App\Models\User::class => (string) ($model->getAttribute('name') ?? ('User #' . $model->getKey())),
            default => class_basename($class) . ' #' . $model->getKey(),
        };
    }

    /**
     * @return array{0:?string,1:?string}
     */
    private function getRequestMeta(): array
    {
        if (! app()->bound('request')) {
            return [null, null];
        }

        $request = request();

        return [
            $request->ip(),
            $request->userAgent(),
        ];
    }
}
