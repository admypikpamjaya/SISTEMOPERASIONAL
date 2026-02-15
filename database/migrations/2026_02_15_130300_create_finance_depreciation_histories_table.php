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
        Schema::create('finance_depreciation_histories', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('depreciation_run_id')->constrained('finance_depreciation_runs')->cascadeOnDelete();
            $table->foreignUuid('period_id')->constrained('finance_periods')->cascadeOnDelete();
            $table->foreignUuid('asset_id')->constrained('assets')->restrictOnDelete();
            $table->foreignUuid('policy_id')->constrained('finance_asset_policies')->restrictOnDelete();
            $table->enum('method', ['STRAIGHT_LINE'])->default('STRAIGHT_LINE');
            $table->decimal('acquisition_cost_snapshot', 18, 2);
            $table->decimal('residual_value_snapshot', 18, 2)->default(0);
            $table->unsignedSmallInteger('useful_life_months_snapshot');
            $table->unsignedInteger('sequence_month');
            $table->decimal('accumulated_before', 18, 2)->default(0);
            $table->decimal('depreciation_amount', 18, 2);
            $table->decimal('accumulated_after', 18, 2);
            $table->decimal('book_value_end', 18, 2);
            $table->timestamp('created_at')->useCurrent();

            $table->unique(['depreciation_run_id', 'asset_id'], 'finance_depr_histories_unique_asset_per_run');
            $table->index(['period_id', 'asset_id']);
            $table->index(['policy_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('finance_depreciation_histories');
    }
};
