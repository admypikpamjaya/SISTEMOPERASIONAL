<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('blast_message_templates', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->string('name');
            $table->enum('channel', ['email', 'whatsapp']);
            $table->text('content');

            $table->boolean('is_active')->default(true);

            $table->uuid('created_by')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blast_message_templates');
    }
};
