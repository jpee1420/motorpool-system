<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('maintenance_records', function (Blueprint $table): void {
            if (! Schema::hasColumn('maintenance_records', 'performed_by')) {
                $table->string('performed_by')->nullable();
            }
        });

        Schema::table('repair_records', function (Blueprint $table): void {
            if (! Schema::hasColumn('repair_records', 'performed_by')) {
                $table->string('performed_by')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('maintenance_records', function (Blueprint $table): void {
            if (Schema::hasColumn('maintenance_records', 'performed_by')) {
                $table->dropColumn('performed_by');
            }
        });

        Schema::table('repair_records', function (Blueprint $table): void {
            if (Schema::hasColumn('repair_records', 'performed_by')) {
                $table->dropColumn('performed_by');
            }
        });
    }
};