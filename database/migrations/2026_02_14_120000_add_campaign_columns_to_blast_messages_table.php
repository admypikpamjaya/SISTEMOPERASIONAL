<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('blast_messages', function (Blueprint $table) {
            $table->string('campaign_status')
                ->default('QUEUED')
                ->after('meta')
                ->index();

            $table->string('priority')
                ->default('normal')
                ->after('campaign_status');

            $table->timestamp('scheduled_at')
                ->nullable()
                ->after('priority')
                ->index();

            $table->timestamp('started_at')
                ->nullable()
                ->after('scheduled_at');

            $table->timestamp('paused_at')
                ->nullable()
                ->after('started_at');

            $table->timestamp('completed_at')
                ->nullable()
                ->after('paused_at');
        });
    }

    public function down(): void
    {
        Schema::table('blast_messages', function (Blueprint $table) {
            $table->dropIndex(['campaign_status']);
            $table->dropIndex(['scheduled_at']);
            $table->dropColumn([
                'campaign_status',
                'priority',
                'scheduled_at',
                'started_at',
                'paused_at',
                'completed_at',
            ]);
        });
    }
};
