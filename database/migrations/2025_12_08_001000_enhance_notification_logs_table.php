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
        Schema::table('notification_logs', function (Blueprint $table): void {
            // 1. Make logs more informative
            $table->string('trigger_reason')->nullable()->after('type');
            $table->json('meta')->nullable()->after('trigger_reason');

            // 6. Smarter retry behavior
            $table->unsignedInteger('retry_count')->default(0)->after('error_message');
            $table->boolean('max_retries_reached')->default(false)->after('retry_count');

            // Add user_id for in_app notifications (who should see it)
            $table->foreignId('user_id')->nullable()->after('vehicle_id')->constrained()->nullOnDelete();

            // Track if in_app notification has been read
            $table->timestamp('read_at')->nullable()->after('sent_at');

            // Index for quick lookups
            $table->index(['channel', 'status']);
            $table->index(['user_id', 'channel', 'read_at']);
        });

        // 7. Support for in_app - make recipient_contact nullable (using raw SQL to avoid doctrine/dbal)
        DB::statement('ALTER TABLE notification_logs MODIFY recipient_contact VARCHAR(255) NULL');
    }

    public function down(): void
    {
        // Revert recipient_contact to NOT NULL
        DB::statement("UPDATE notification_logs SET recipient_contact = '' WHERE recipient_contact IS NULL");
        DB::statement('ALTER TABLE notification_logs MODIFY recipient_contact VARCHAR(255) NOT NULL');

        Schema::table('notification_logs', function (Blueprint $table): void {
            $table->dropIndex(['channel', 'status']);
            $table->dropIndex(['user_id', 'channel', 'read_at']);

            $table->dropForeign(['user_id']);
            $table->dropColumn([
                'trigger_reason',
                'meta',
                'retry_count',
                'max_retries_reached',
                'user_id',
                'read_at',
            ]);
        });
    }
};
