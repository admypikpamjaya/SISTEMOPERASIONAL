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
       Schema::create('billings', function (Blueprint $table) {
    $table->id();
    $table->foreignId('parent_id')->constrained('parent_users');
    $table->decimal('amount', 12, 2);
    $table->date('due_date');

    // STATUS TURUNAN (derived)
    $table->enum('status', ['PENDING', 'PAID', 'EXPIRED'])->default('PENDING');

    $table->timestamps();
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('billings');
    }
};
