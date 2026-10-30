<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Vehicle;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class ImportVehiclesFromFirestore extends Command
{
    protected $signature = 'motorpool:import-vehicles
                            {file : Path to the JSON file exported from Firestore}
                            {--dry-run : Run without actually inserting data}';

    protected $description = 'Import vehicles from a Firestore JSON export file';

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
        $vehicles = $this->normalizeFirestoreData($data);

        if (empty($vehicles)) {
            $this->warn('No vehicles found in the file.');
            return self::SUCCESS;
        }

        $this->info('Found ' . count($vehicles) . ' vehicles to import.');

        if ($dryRun) {
            $this->warn('DRY RUN - No data will be inserted.');
        }

        $imported = 0;
        $skipped = 0;
        $errors = 0;

        $progressBar = $this->output->createProgressBar(count($vehicles));
        $progressBar->start();

        DB::beginTransaction();

        try {
            foreach ($vehicles as $vehicleData) {
                $progressBar->advance();

                $plateNumber = $this->extractField($vehicleData, ['plate_number', 'plateNumber', 'plate']);

                if (empty($plateNumber)) {
                    $this->newLine();
                    $this->warn('Skipping vehicle with no plate number.');
                    $skipped++;
                    continue;
                }

                // Check if vehicle already exists
                if (Vehicle::where('plate_number', $plateNumber)->exists()) {
                    $skipped++;
                    continue;
                }

                $vehicle = [
                    'plate_number' => $plateNumber,
                    'vehicle_type' => $this->extractField($vehicleData, ['vehicle_type', 'vehicleType', 'type']) ?? '',
                    'chassis_number' => $this->extractField($vehicleData, ['chassis_number', 'chassisNumber', 'chassis']) ?? '',
                    'make' => $this->extractField($vehicleData, ['make', 'brand']) ?? '',
                    'model' => $this->extractField($vehicleData, ['model']) ?? '',
                    'year' => $this->extractNumericField($vehicleData, ['year']),
                    'engine_number' => $this->extractField($vehicleData, ['engine_number', 'engineNumber', 'engine']) ?? '',
                    'driver_operator' => $this->extractField($vehicleData, ['driver_operator', 'driverOperator', 'driver', 'operator']) ?? '',
                    'contact_number' => $this->extractField($vehicleData, ['contact_number', 'contactNumber', 'contact', 'phone']) ?? '',
                    'status' => $this->mapStatus($this->extractField($vehicleData, ['status'])),
                    'current_odometer' => $this->extractNumericField($vehicleData, ['current_odometer', 'currentOdometer', 'odometer', 'mileage']),
                    'last_maintenance_at' => $this->extractDateField($vehicleData, ['last_maintenance_at', 'lastMaintenanceAt', 'lastMaintenance']),
                    'last_maintenance_odometer' => $this->extractNumericField($vehicleData, ['last_maintenance_odometer', 'lastMaintenanceOdometer']),
                    'next_maintenance_due_at' => $this->extractDateField($vehicleData, ['next_maintenance_due_at', 'nextMaintenanceDueAt', 'nextMaintenance']),
                    'next_maintenance_due_odometer' => $this->extractNumericField($vehicleData, ['next_maintenance_due_odometer', 'nextMaintenanceDueOdometer']),
                ];

                if (!$dryRun) {
                    Vehicle::create($vehicle);
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
                    ['Skipped (duplicates or invalid)', $skipped],
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
     * Normalize Firestore export data to a simple array of documents.
     */
    private function normalizeFirestoreData(array $data): array
    {
        // If it's already an array of objects, return as-is
        if (isset($data[0])) {
            return $data;
        }

        // If it's a Firestore export with documents
        if (isset($data['documents'])) {
            return array_map(function ($doc) {
                return $doc['fields'] ?? $doc;
            }, $data['documents']);
        }

        // If it's a single document, wrap it
        return [$data];
    }

    /**
     * Extract a field value from Firestore data, handling different field name formats.
     */
    private function extractField(array $data, array $possibleKeys): ?string
    {
        foreach ($possibleKeys as $key) {
            if (isset($data[$key])) {
                $value = $data[$key];

                // Handle Firestore typed values
                if (is_array($value)) {
                    return $value['stringValue'] ?? $value['value'] ?? null;
                }

                return (string) $value;
            }
        }

        return null;
    }

    /**
     * Extract a numeric field value.
     */
    private function extractNumericField(array $data, array $possibleKeys): ?int
    {
        foreach ($possibleKeys as $key) {
            if (isset($data[$key])) {
                $value = $data[$key];

                // Handle Firestore typed values
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

    /**
     * Extract a date field value.
     */
    private function extractDateField(array $data, array $possibleKeys): ?string
    {
        foreach ($possibleKeys as $key) {
            if (isset($data[$key])) {
                $value = $data[$key];

                // Handle Firestore timestamp
                if (is_array($value) && isset($value['timestampValue'])) {
                    return $value['timestampValue'];
                }

                // Handle Firestore typed values
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

    /**
     * Map status values to valid system statuses.
     */
    private function mapStatus(?string $status): string
    {
        if ($status === null) {
            return 'operational';
        }

        $status = strtolower(trim($status));

        return match ($status) {
            'operational', 'active', 'available' => 'operational',
            'non-operational', 'inactive', 'disabled' => 'non-operational',
            'maintenance', 'repair', 'service' => 'maintenance',
            default => 'operational',
        };
    }
}
