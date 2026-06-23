<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('blast_logs', function (Blueprint $table) {
            $table->string('provider_status', 32)->nullable()->after('status');
            $table->string('provider_reference', 128)->nullable()->after('provider_status');
            $table->string('provider_message_id', 191)->nullable()->after('provider_reference');
            $table->string('provider_sender_phone', 32)->nullable()->after('provider_message_id');
            $table->timestamp('provider_checked_at')->nullable()->after('provider_sender_phone');

            $table->index(['provider_status', 'provider_checked_at'], 'blast_logs_provider_status_checked_idx');
            $table->index('provider_reference', 'blast_logs_provider_reference_idx');
        });

        DB::table('blast_logs')
            ->whereRaw("LOWER(COALESCE(response, '')) LIKE ?", ['%queued%'])
            ->update([
                'provider_status' => 'legacy_queued',
            ]);
    }

    public function down(): void
    {
        Schema::table('blast_logs', function (Blueprint $table) {
            $table->dropIndex('blast_logs_provider_status_checked_idx');
            $table->dropIndex('blast_logs_provider_reference_idx');
            $table->dropColumn([
                'provider_status',
                'provider_reference',
                'provider_message_id',
                'provider_sender_phone',
                'provider_checked_at',
            ]);
        });
    }
};
