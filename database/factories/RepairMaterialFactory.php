<?php

namespace Database\Factories;

use App\Models\RepairMaterial;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\RepairMaterial>
 */
class RepairMaterialFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    protected $model = RepairMaterial::class;
    public function definition(): array
    {
        $qty = (float) $this->faker->randomFloat(2, 1, 5);
        $unitCost = (float) $this->faker->randomFloat(2, 1, 5000);
        $total = $qty * $unitCost;
        return [
            'name' => $this->faker->randomElement([
                'Oil Filter', 'Brake Pads', 'Fan Belt', 'Battery', 'Radiator Hose', 'Spark Plug',
            ]),
            'description' => $this->faker->optional()->sentence(),
            'quantity' => $qty,
            'unit' => $this->faker->optional()->randomElement(['pc', 'set', 'L']),
            'unit_cost' => $unitCost,
            'total_cost' => $total,
        ];
    }
}
