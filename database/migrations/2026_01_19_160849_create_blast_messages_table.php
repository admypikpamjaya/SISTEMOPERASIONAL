<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('blast_messages', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->enum('channel', ['EMAIL', 'WHATSAPP']);
            $table->string('subject')->nullable();
            $table->text('message');

            $table->string('attachment_path')->nullable();

            $table->uuid('created_by');
            $table->timestamps();

            $table->foreign('created_by')
                ->references('id')
                ->on('users')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blast_messages');
    }
};
