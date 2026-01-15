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
        Schema::create('payments', function (Blueprint $table) {
    $table->id();

    $table->foreignId('billing_id')
          ->constrained('billings')
          ->cascadeOnDelete();

    $table->enum('method', ['MANUAL_TRANSFER']);
    $table->enum('status', ['PENDING', 'CONFIRMED', 'REJECTED'])
          ->default('PENDING');

    $table->string('proof_path')->nullable();

    // FK ke users.id (UUID)
    $table->uuid('confirmed_by')->nullable();
    $table->foreign('confirmed_by')
          ->references('id')
          ->on('users')
          ->nullOnDelete();

    $table->timestamp('confirmed_at')->nullable();
    $table->timestamps();
});


    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
