<?php

namespace Database\Factories;

use App\Models\Vehicle;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Vehicle>
 */
class VehicleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    protected $model = Vehicle::class;
    public function definition(): array
    {
        return [
            'plate_number' => $this->faker->unique()->bothify('???-####'),
            'make' => $this->faker->randomElement(['Toyota', 'Nissan', 'Mitsubishi', 'Honda', 'Isuzu']),
            'model' => $this->faker->word(),
            'year' => (int) $this->faker->numberBetween(2005, (int) now()->format('Y')),
            'current_odometer' => (int) $this->faker->numberBetween(1_000, 250_000),

            'last_maintenance_at' => null,
            'last_maintenance_odometer' => null,
            'next_maintenance_due_at' => null,
            'next_maintenance_due_odometer' => null,
        ];
    }
}
