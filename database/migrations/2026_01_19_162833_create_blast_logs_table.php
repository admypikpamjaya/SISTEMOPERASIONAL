<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('blast_logs', function (Blueprint $table) {
            $table->id();

            $table->uuid('blast_message_id');
            $table->unsignedBigInteger('blast_target_id');

            $table->enum('status', ['PENDING', 'SENT', 'FAILED']);
            $table->text('response')->nullable();
            $table->unsignedTinyInteger('attempt')->default(0);
            $table->timestamp('sent_at')->nullable();

            $table->timestamps();

            $table->foreign('blast_message_id')
                ->references('id')
                ->on('blast_messages')
                ->cascadeOnDelete();

            $table->foreign('blast_target_id')
                ->references('id')
                ->on('blast_targets')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blast_logs');
    }
};
