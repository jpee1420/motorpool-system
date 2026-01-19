<?php

namespace Database\Factories;

use App\Models\RepairMaterial;
use App\Models\RepairRecord;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\RepairRecord>
 */
class RepairRecordFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    protected $model = RepairRecord::class;
    public function definition(): array
    {
        $labor = (float) $this->faker->randomFloat(2, 0, 5000);
        return [
            'vehicle_id' => Vehicle::query()->inRandomOrder()->value('id') ?? Vehicle::factory(),
            'assigned_driver_id' => User::query()->inRandomOrder()->value('id'),
            'performed_by_user_id' => User::query()->inRandomOrder()->value('id'),
            'performed_by' => $this->faker->name(),
            'performed_at' => $this->faker->dateTimeBetween('-12 months', 'now'),
            'odometer_reading' => (int) $this->faker->numberBetween(1_000, 300_000),
            'description_of_work' => $this->faker->paragraph(),
            'personnel_labor_cost' => $labor,
            'materials_cost_total' => 0,
            'total_cost' => $labor,
            'notes' => $this->faker->optional()->sentence(),
        ];
    }
    public function configure(): static
    {
        return $this->afterCreating(function (RepairRecord $record): void {
            $count = random_int(0, 4);
            $materialsTotal = 0.0;
            for ($i = 0; $i < $count; $i++) {
                $material = RepairMaterial::factory()->create([
                    'repair_record_id' => $record->id,
                ]);
                $materialsTotal += (float) $material->total_cost;
            }
            $labor = (float) $record->personnel_labor_cost;
            $record->update([
                'materials_cost_total' => $materialsTotal,
                'total_cost' => $labor + $materialsTotal,
            ]);
        });
    }
}
