<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\RepairRecord;
use App\Models\Vehicle;
use App\Models\TripTicket;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;


class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(30)->create();
        // Vehicle::factory()->count(10)->create();
        // RepairRecord::factory()->count(50)->create();
        TripTicket::factory()->count(10)->create();
    }
}
