<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tunggakan_blast_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->uuid('blast_message_id')->nullable();
            $table->foreign('blast_message_id')
                ->references('id')
                ->on('blast_messages')
                ->nullOnDelete();

            $table->uuid('triggered_by')->nullable();
            $table->foreign('triggered_by')
                ->references('id')
                ->on('users')
                ->nullOnDelete();

            $table->unsignedInteger('total_candidate_records')->default(0);
            $table->unsignedInteger('total_candidate_groups')->default(0);
            $table->unsignedInteger('total_processed_groups')->default(0);
            $table->unsignedInteger('total_sent_groups')->default(0);
            $table->unsignedInteger('total_failed_groups')->default(0);
            $table->unsignedInteger('total_skipped_groups')->default(0);
            $table->unsignedInteger('total_targets')->default(0);
            $table->unsignedInteger('total_sent_targets')->default(0);
            $table->unsignedInteger('total_failed_targets')->default(0);
            $table->unsignedInteger('total_queued_targets')->default(0);

            $table->json('details')->nullable();

            $table->timestamps();

            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tunggakan_blast_logs');
    }
};

