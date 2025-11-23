<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vehicles', function (Blueprint $table): void {
            $table->string('vehicle_type')->nullable();
            $table->string('chassis_number')->nullable();
            $table->string('engine_number')->nullable();
            $table->string('driver_operator')->nullable();
            $table->string('contact_number')->nullable();
            $table->string('status')->default('operational');
        });
    }

    public function down(): void
    {
        Schema::table('vehicles', function (Blueprint $table): void {
            $table->dropColumn([
                'vehicle_type',
                'chassis_number',
                'engine_number',
                'driver_operator',
                'contact_number',
                'status',
            ]);
        });
    }
};
