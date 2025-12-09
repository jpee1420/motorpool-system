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
        Schema::create('repair_materials', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('repair_record_id')->constrained('repair_records')->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('quantity', 10, 2)->default(1);
            $table->string('unit')->nullable();
            $table->decimal('unit_cost', 10, 2)->default(0);
            $table->decimal('total_cost', 10, 2)->default(0);
            $table->timestamps();
        });

        // Optionally copy any existing repair materials linked to repair-type maintenance records
        if (Schema::hasTable('maintenance_materials') && Schema::hasTable('maintenance_records')) {
            $repairIds = DB::table('maintenance_records')
                ->where('type', 'repair')
                ->pluck('id');

            if ($repairIds->isNotEmpty()) {
                $materials = DB::table('maintenance_materials')
                    ->whereIn('maintenance_record_id', $repairIds)
                    ->get();

                foreach ($materials as $material) {
                    DB::table('repair_materials')->updateOrInsert([
                        'id' => $material->id,
                    ], [
                        'repair_record_id' => $material->maintenance_record_id,
                        'name' => $material->name,
                        'description' => $material->description,
                        'quantity' => $material->quantity,
                        'unit' => $material->unit,
                        'unit_cost' => $material->unit_cost,
                        'total_cost' => $material->total_cost,
                        'created_at' => $material->created_at,
                        'updated_at' => $material->updated_at,
                    ]);
                }
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('repair_materials');
    }
};
