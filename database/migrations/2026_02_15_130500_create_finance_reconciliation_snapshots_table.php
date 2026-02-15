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
        Schema::create('finance_reconciliation_snapshots', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('period_id')->constrained('finance_periods')->cascadeOnDelete();
            $table->foreignUuid('depreciation_run_id')->constrained('finance_depreciation_runs')->cascadeOnDelete();
            $table->decimal('income_total', 18, 2)->default(0);
            $table->decimal('expense_total', 18, 2)->default(0);
            $table->decimal('depreciation_total', 18, 2)->default(0);
            $table->decimal('net_result', 18, 2)->default(0);
            $table->foreignUuid('generated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('generated_at')->useCurrent();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['period_id', 'depreciation_run_id'], 'finance_recon_snapshots_unique_period_run');
            $table->index(['generated_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('finance_reconciliation_snapshots');
    }
};
