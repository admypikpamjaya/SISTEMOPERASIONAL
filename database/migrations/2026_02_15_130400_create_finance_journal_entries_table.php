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
        Schema::create('finance_journal_entries', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('period_id')->constrained('finance_periods')->cascadeOnDelete();
            $table->enum('entry_type', ['INCOME', 'EXPENSE']);
            $table->string('source_table', 64);
            $table->string('source_id', 64);
            $table->date('source_date');
            $table->decimal('amount', 18, 2);
            $table->enum('status', ['FINAL', 'VOID'])->default('FINAL');
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->unique(['entry_type', 'source_table', 'source_id'], 'finance_journal_entries_unique_source');
            $table->index(['period_id', 'entry_type']);
            $table->index(['source_date']);
            $table->index(['status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('finance_journal_entries');
    }
};
