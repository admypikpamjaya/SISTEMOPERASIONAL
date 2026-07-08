<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('blast_general_recipients', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('nama');
            $table->string('whatsapp')->nullable();
            $table->text('catatan')->nullable();
            $table->string('source')->nullable();
            $table->boolean('is_valid')->default(false);
            $table->text('validation_error')->nullable();
            $table->timestamps();

            $table->index('whatsapp');
            $table->index('is_valid');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blast_general_recipients');
    }
};
