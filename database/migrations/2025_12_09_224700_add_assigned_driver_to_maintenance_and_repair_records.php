<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('maintenance_records', function (Blueprint $table) {
            $table->foreignId('assigned_driver_id')
                ->nullable()
                ->after('vehicle_id')
                ->constrained('users')
                ->nullOnDelete();
        });

        Schema::table('repair_records', function (Blueprint $table) {
            $table->foreignId('assigned_driver_id')
                ->nullable()
                ->after('vehicle_id')
                ->constrained('users')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('maintenance_records', function (Blueprint $table) {
            $table->dropConstrainedForeignId('assigned_driver_id');
        });

        Schema::table('repair_records', function (Blueprint $table) {
            $table->dropConstrainedForeignId('assigned_driver_id');
        });
    }
};
