<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('system_blast_log_archives', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('original_log_id')->nullable();
            $table->uuid('blast_message_id')->nullable();
            $table->unsignedBigInteger('blast_target_id')->nullable();
            $table->string('channel', 20)->nullable();
            $table->string('target', 191)->nullable();
            $table->string('device_id', 80)->nullable();
            $table->string('status', 30)->nullable();
            $table->string('provider_status', 40)->nullable();
            $table->string('provider_reference', 128)->nullable();
            $table->string('provider_message_id', 191)->nullable();
            $table->string('provider_sender_phone', 32)->nullable();
            $table->longText('message_snapshot')->nullable();
            $table->longText('response')->nullable();
            $table->longText('error_message')->nullable();
            $table->json('payload')->nullable();
            $table->uuid('archived_by')->nullable();
            $table->string('archive_reason', 80)->nullable();
            $table->timestamp('original_created_at')->nullable();
            $table->timestamp('original_updated_at')->nullable();
            $table->timestamps();

            $table->foreign('archived_by')
                ->references('id')
                ->on('users')
                ->nullOnDelete();
            $table->index(['channel', 'created_at']);
            $table->index(['original_log_id', 'created_at']);
            $table->index('blast_message_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('system_blast_log_archives');
    }
};
