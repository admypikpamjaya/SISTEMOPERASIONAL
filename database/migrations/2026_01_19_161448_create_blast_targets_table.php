<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('blast_targets', function (Blueprint $table) {
            $table->id();

            $table->uuid('blast_message_id');
            $table->string('target'); // email / phone
            $table->timestamps();

            $table->foreign('blast_message_id')
                ->references('id')
                ->on('blast_messages')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blast_targets');
    }
};
