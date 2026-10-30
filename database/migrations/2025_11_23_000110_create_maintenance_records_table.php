<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('maintenance_records', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('vehicle_id')->constrained()->cascadeOnDelete();
            $table->foreignId('performed_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('performed_at');
            $table->unsignedInteger('odometer_reading');
            $table->text('description_of_work');
            $table->decimal('personnel_labor_cost', 10, 2)->default(0);
            $table->decimal('materials_cost_total', 10, 2)->default(0);
            $table->decimal('total_cost', 10, 2)->default(0);
            $table->date('next_maintenance_due_at')->nullable();
            $table->unsignedInteger('next_maintenance_due_odometer')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('maintenance_records');
    }
};
