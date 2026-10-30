<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\MaintenanceMaterial;
use App\Models\MaintenanceRecord;
use App\Models\Vehicle;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class ImportMaintenanceFromFirestore extends Command
{
    protected $signature = 'motorpool:import-maintenance
                            {file : Path to the JSON file exported from Firestore}
                            {--dry-run : Run without actually inserting data}';

    protected $description = 'Import maintenance records from a Firestore JSON export file';

    public function handle(): int
    {
        $filePath = $this->argument('file');
        $dryRun = $this->option('dry-run');

        if (!File::exists($filePath)) {
            $this->error("File not found: {$filePath}");
            return self::FAILURE;
        }

        $this->info('Reading Firestore export file...');

        $json = File::get($filePath);
        $data = json_decode($json, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->error('Invalid JSON file: ' . json_last_error_msg());
            return self::FAILURE;
        }

        // Handle both array of documents and Firestore export format
        $records = $this->normalizeFirestoreData($data);

        if (empty($records)) {
            $this->warn('No maintenance records found in the file.');
            return self::SUCCESS;
        }

        $this->info('Found ' . count($records) . ' maintenance records to import.');

        if ($dryRun) {
            $this->warn('DRY RUN - No data will be inserted.');
        }

        // Cache vehicles by plate number for lookup
        $vehicleCache = Vehicle::pluck('id', 'plate_number')->toArray();

        $imported = 0;
        $skipped = 0;
        $errors = 0;

        $progressBar = $this->output->createProgressBar(count($records));
        $progressBar->start();

        DB::beginTransaction();

        try {
            foreach ($records as $recordData) {
                $progressBar->advance();

                // Find vehicle by plate number
                $plateNumber = $this->extractField($recordData, ['plate_number', 'plateNumber', 'vehicle_plate', 'vehiclePlate', 'vehicle']);
                $vehicleId = $vehicleCache[$plateNumber] ?? null;

                if (!$vehicleId) {
                    $skipped++;
                    continue;
                }

                $performedAt = $this->extractDateField($recordData, ['performed_at', 'performedAt', 'date', 'maintenance_date', 'maintenanceDate']);

                if (!$performedAt) {
                    $skipped++;
                    continue;
                }

                $maintenanceRecord = [
                    'vehicle_id' => $vehicleId,
                    'performed_by_user_id' => null, // Will be set manually if needed
                    'performed_at' => $performedAt,
                    'odometer_reading' => $this->extractNumericField($recordData, ['odometer_reading', 'odometerReading', 'odometer', 'mileage']),
                    'description_of_work' => $this->extractField($recordData, ['description_of_work', 'descriptionOfWork', 'description', 'work', 'notes']) ?? '',
                    'personnel_labor_cost' => $this->extractDecimalField($recordData, ['personnel_labor_cost', 'personnelLaborCost', 'labor_cost', 'laborCost', 'labor']),
                    'materials_cost_total' => $this->extractDecimalField($recordData, ['materials_cost_total', 'materialsCostTotal', 'materials_cost', 'materialsCost', 'parts_cost']),
                    'total_cost' => $this->extractDecimalField($recordData, ['total_cost', 'totalCost', 'cost', 'total']),
                    'next_maintenance_due_at' => $this->extractDateField($recordData, ['next_maintenance_due_at', 'nextMaintenanceDueAt', 'next_due']),
                    'next_maintenance_due_odometer' => $this->extractNumericField($recordData, ['next_maintenance_due_odometer', 'nextMaintenanceDueOdometer', 'next_due_odometer']),
                ];

                // Calculate total if not provided
                if (!$maintenanceRecord['total_cost']) {
                    $maintenanceRecord['total_cost'] = ($maintenanceRecord['personnel_labor_cost'] ?? 0) + ($maintenanceRecord['materials_cost_total'] ?? 0);
                }

                if (!$dryRun) {
                    $record = MaintenanceRecord::create($maintenanceRecord);

                    // Import materials if present
                    $materials = $this->extractArray($recordData, ['materials', 'parts', 'items']);
                    foreach ($materials as $materialData) {
                        $this->importMaterial($record->id, $materialData);
                    }
                }

                $imported++;
            }

            if (!$dryRun) {
                DB::commit();
            }

            $progressBar->finish();
            $this->newLine(2);

            $this->info("Import complete!");
            $this->table(
                ['Metric', 'Count'],
                [
                    ['Imported', $imported],
                    ['Skipped (no vehicle or date)', $skipped],
                    ['Errors', $errors],
                ]
            );

            return self::SUCCESS;
        } catch (\Throwable $e) {
            DB::rollBack();
            $progressBar->finish();
            $this->newLine();
            $this->error('Import failed: ' . $e->getMessage());
            return self::FAILURE;
        }
    }

    /**
     * Import a single material for a maintenance record.
     */
    private function importMaterial(int $recordId, array $materialData): void
    {
        $name = $this->extractField($materialData, ['name', 'part_name', 'partName', 'item']);

        if (!$name) {
            return;
        }

        $quantity = $this->extractDecimalField($materialData, ['quantity', 'qty']);
        $unitCost = $this->extractDecimalField($materialData, ['unit_cost', 'unitCost', 'price', 'cost']);

        MaintenanceMaterial::create([
            'maintenance_record_id' => $recordId,
            'name' => $name,
            'description' => $this->extractField($materialData, ['description', 'desc']),
            'quantity' => $quantity ?? 1,
            'unit' => $this->extractField($materialData, ['unit', 'uom']) ?? 'pc',
            'unit_cost' => $unitCost ?? 0,
            'total_cost' => ($quantity ?? 1) * ($unitCost ?? 0),
        ]);
    }

    /**
     * Normalize Firestore export data to a simple array of documents.
     */
    private function normalizeFirestoreData(array $data): array
    {
        if (isset($data[0])) {
            return $data;
        }

        if (isset($data['documents'])) {
            return array_map(function ($doc) {
                return $doc['fields'] ?? $doc;
            }, $data['documents']);
        }

        return [$data];
    }

    private function extractField(array $data, array $possibleKeys): ?string
    {
        foreach ($possibleKeys as $key) {
            if (isset($data[$key])) {
                $value = $data[$key];

                if (is_array($value)) {
                    return $value['stringValue'] ?? $value['value'] ?? null;
                }

                return (string) $value;
            }
        }

        return null;
    }

    private function extractNumericField(array $data, array $possibleKeys): ?int
    {
        foreach ($possibleKeys as $key) {
            if (isset($data[$key])) {
                $value = $data[$key];

                if (is_array($value)) {
                    $value = $value['integerValue'] ?? $value['doubleValue'] ?? $value['value'] ?? null;
                }

                if ($value !== null) {
                    return (int) $value;
                }
            }
        }

        return null;
    }

    private function extractDecimalField(array $data, array $possibleKeys): ?float
    {
        foreach ($possibleKeys as $key) {
            if (isset($data[$key])) {
                $value = $data[$key];

                if (is_array($value)) {
                    $value = $value['doubleValue'] ?? $value['integerValue'] ?? $value['value'] ?? null;
                }

                if ($value !== null) {
                    return (float) $value;
                }
            }
        }

        return null;
    }

    private function extractDateField(array $data, array $possibleKeys): ?string
    {
        foreach ($possibleKeys as $key) {
            if (isset($data[$key])) {
                $value = $data[$key];

                if (is_array($value) && isset($value['timestampValue'])) {
                    return $value['timestampValue'];
                }

                if (is_array($value)) {
                    $value = $value['stringValue'] ?? $value['value'] ?? null;
                }

                if ($value !== null) {
                    try {
                        return \Carbon\Carbon::parse($value)->toDateTimeString();
                    } catch (\Exception) {
                        return null;
                    }
                }
            }
        }

        return null;
    }

    private function extractArray(array $data, array $possibleKeys): array
    {
        foreach ($possibleKeys as $key) {
            if (isset($data[$key]) && is_array($data[$key])) {
                $value = $data[$key];

                // Handle Firestore arrayValue
                if (isset($value['arrayValue']['values'])) {
                    return array_map(function ($item) {
                        return $item['mapValue']['fields'] ?? $item;
                    }, $value['arrayValue']['values']);
                }

                return $value;
            }
        }

        return [];
    }
}
