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
        if (Schema::hasTable('maintenance_records') && Schema::hasColumn('maintenance_records', 'type')) {
            $repairIds = DB::table('maintenance_records')
                ->where('type', 'repair')
                ->pluck('id');

            if ($repairIds->isNotEmpty()) {
                if (Schema::hasTable('maintenance_materials')) {
                    DB::table('maintenance_materials')
                        ->whereIn('maintenance_record_id', $repairIds)
                        ->delete();
                }

                DB::table('maintenance_records')
                    ->whereIn('id', $repairIds)
                    ->delete();
            }

            Schema::table('maintenance_records', function (Blueprint $table): void {
                $table->dropIndex(['type']);
                $table->dropColumn('type');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('maintenance_records') && ! Schema::hasColumn('maintenance_records', 'type')) {
            Schema::table('maintenance_records', function (Blueprint $table): void {
                $table->string('type')->default('maintenance')->after('id');
                $table->index('type');
            });
        }
    }
};
