<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicles', function (Blueprint $table): void {
            $table->id();
            $table->string('plate_number')->unique();
            $table->string('make')->nullable();
            $table->string('model')->nullable();
            $table->smallInteger('year')->nullable();
            $table->unsignedInteger('current_odometer')->default(0);
            $table->dateTime('last_maintenance_at')->nullable();
            $table->unsignedInteger('last_maintenance_odometer')->nullable();
            $table->date('next_maintenance_due_at')->nullable();
            $table->unsignedInteger('next_maintenance_due_odometer')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicles');
    }
};
