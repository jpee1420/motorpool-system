<?php

namespace Database\Factories;

use App\Models\TripTicket;
use App\Models\Vehicle;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TripTicket>
 */
class TripTicketFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    protected $model = TripTicket::class;

    public function definition(): array
    {
        $departure = $this->faker->dateTimeBetween('-1 month', '+1 week');
        $odometerStart = $this->faker->numberBetween(1000, 200000);
        $odometerEnd = $odometerStart + $this->faker->numberBetween(5, 300);

        return [
            'vehicle_id' => Vehicle::query()->inRandomOrder()->value('id') ?? Vehicle::factory(),
            'requested_by_user_id' => User::query()->inRandomOrder()->value('id') ?? User::factory(),
            'driver_name' => $this->faker->name(),
            'destination' => $this->faker->city(),
            'purpose' => $this->faker->sentence(6),
            'departure_at' => $departure,
            'odometer_start' => $odometerStart,
            'odometer_end' => $odometerEnd,
            'status' => 'pending',
        ];
    }
}
