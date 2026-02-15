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
        Schema::create('finance_periods', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->enum('period_type', ['MONTHLY', 'YEARLY']);
            $table->unsignedSmallInteger('year');
            $table->unsignedTinyInteger('month')->default(0);
            $table->date('start_date');
            $table->date('end_date');
            $table->enum('status', ['OPEN', 'CLOSED', 'LOCKED'])->default('OPEN');
            $table->foreignUuid('locked_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('locked_at')->nullable();
            $table->timestamps();

            $table->unique(['period_type', 'year', 'month'], 'finance_periods_unique_period');
            $table->index(['status']);
            $table->index(['year', 'month']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('finance_periods');
    }
};
