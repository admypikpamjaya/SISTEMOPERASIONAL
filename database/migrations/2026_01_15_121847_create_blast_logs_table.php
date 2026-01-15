<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('blast_logs', function (Blueprint $table) {
    $table->id();
    $table->enum('channel', ['EMAIL', 'WHATSAPP']);
    $table->string('target');
    $table->enum('status', ['SENT', 'FAILED']);

    $table->string('reference_type'); // announcement | billing
    $table->unsignedBigInteger('reference_id');

    $table->text('response')->nullable();
    $table->timestamps();

    $table->index(['reference_type', 'reference_id']);
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('blast_logs');
    }
};
