<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('repair_records', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('vehicle_id')->constrained()->cascadeOnDelete();
            $table->foreignId('performed_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('performed_at');
            $table->unsignedInteger('odometer_reading');
            $table->text('description_of_work');
            $table->decimal('personnel_labor_cost', 10, 2)->default(0);
            $table->decimal('materials_cost_total', 10, 2)->default(0);
            $table->decimal('total_cost', 10, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // Optionally copy any existing repair-type rows from maintenance_records
        if (Schema::hasTable('maintenance_records')) {
            $repairs = DB::table('maintenance_records')
                ->where('type', 'repair')
                ->get();

            foreach ($repairs as $repair) {
                DB::table('repair_records')->updateOrInsert([
                    'id' => $repair->id,
                ], [
                    'vehicle_id' => $repair->vehicle_id,
                    'performed_by_user_id' => $repair->performed_by_user_id,
                    'performed_at' => $repair->performed_at,
                    'odometer_reading' => $repair->odometer_reading,
                    'description_of_work' => $repair->description_of_work,
                    'personnel_labor_cost' => $repair->personnel_labor_cost,
                    'materials_cost_total' => $repair->materials_cost_total,
                    'total_cost' => $repair->total_cost,
                    'notes' => $repair->notes,
                    'created_at' => $repair->created_at,
                    'updated_at' => $repair->updated_at,
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('repair_records');
    }
};
