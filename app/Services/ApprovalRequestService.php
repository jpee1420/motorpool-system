<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\ApprovalRequest;
use App\Models\MaintenanceMaterial;
use App\Models\MaintenanceRecord;
use App\Models\RepairMaterial;
use App\Models\RepairRecord;
use App\Models\TripTicket;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ApprovalRequestService
{
    public function approve(ApprovalRequest $request, User $reviewer, ?string $notes = null): bool
    {
        return DB::transaction(function () use ($request, $reviewer, $notes): bool {
            $model = $this->applyProposedData($request);

            if ($model === null) {
                return false;
            }

            $request->update([
                'status' => ApprovalRequest::STATUS_APPROVED,
                'reviewer_user_id' => $reviewer->id,
                'review_notes' => $notes,
                'reviewed_at' => now(),
                'subject_id' => $model->getKey(),
            ]);

            return true;
        });
    }

    public function reject(ApprovalRequest $request, User $reviewer, ?string $notes = null): bool
    {
        $request->update([
            'status' => ApprovalRequest::STATUS_REJECTED,
            'reviewer_user_id' => $reviewer->id,
            'review_notes' => $notes,
            'reviewed_at' => now(),
        ]);

        return true;
    }

    private function applyProposedData(ApprovalRequest $request): ?Model
    {
        $modelClass = $request->getSubjectModelClass();

        if ($modelClass === null) {
            return null;
        }

        $proposedData = $request->proposed_data ?? [];

        if ($request->action === ApprovalRequest::ACTION_CREATE) {
            return $this->createModel($modelClass, $proposedData);
        }

        if ($request->action === ApprovalRequest::ACTION_UPDATE && $request->subject_id !== null) {
            return $this->updateModel($modelClass, $request->subject_id, $proposedData);
        }

        if ($request->action === ApprovalRequest::ACTION_CANCEL && $request->subject_id !== null) {
            return $this->updateModel($modelClass, $request->subject_id, $proposedData);
        }

        return null;
    }

    private function createModel(string $modelClass, array $data): ?Model
    {
        $materials = $data['_materials'] ?? null;
        unset($data['_materials']);

        // For TripTicket, set status to 'approved' since it was approved
        if ($modelClass === TripTicket::class) {
            $data['status'] = 'approved';
        }

        $model = $modelClass::create($data);

        if ($materials !== null && method_exists($model, 'materials')) {
            $this->syncMaterials($model, $materials);
        }

        return $model;
    }

    private function updateModel(string $modelClass, int $id, array $data): ?Model
    {
        $model = $modelClass::find($id);

        if ($model === null) {
            return null;
        }

        $materials = $data['_materials'] ?? null;
        unset($data['_materials']);

        $model->update($data);

        if ($materials !== null && method_exists($model, 'materials')) {
            $this->syncMaterials($model, $materials);
        }

        return $model;
    }

    private function syncMaterials(Model $parent, array $materials): void
    {
        $materialClass = match ($parent::class) {
            MaintenanceRecord::class => MaintenanceMaterial::class,
            RepairRecord::class => RepairMaterial::class,
            default => null,
        };

        if ($materialClass === null) {
            return;
        }

        $foreignKey = match ($parent::class) {
            MaintenanceRecord::class => 'maintenance_record_id',
            RepairRecord::class => 'repair_record_id',
            default => null,
        };

        if ($foreignKey === null) {
            return;
        }

        $parent->materials()->delete();

        foreach ($materials as $materialData) {
            $materialData[$foreignKey] = $parent->getKey();
            $materialClass::create($materialData);
        }
    }

    public function makeSubjectLabel(string $subjectType, array $data): string
    {
        return match ($subjectType) {
            'Vehicle' => $data['plate_number'] ?? 'New vehicle',
            'MaintenanceRecord' => 'Maintenance record',
            'RepairRecord' => 'Repair record',
            'TripTicket' => 'Trip ticket',
            default => $subjectType,
        };
    }
}
