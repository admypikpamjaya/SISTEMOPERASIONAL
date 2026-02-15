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
        Schema::create('finance_asset_policies', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('asset_id')->constrained('assets')->cascadeOnDelete();
            $table->unsignedInteger('revision_no');
            $table->enum('method', ['STRAIGHT_LINE'])->default('STRAIGHT_LINE');
            $table->decimal('acquisition_cost', 18, 2);
            $table->decimal('residual_value', 18, 2)->default(0);
            $table->unsignedSmallInteger('useful_life_months');
            $table->date('depreciation_start_date');
            $table->foreignUuid('effective_period_id')->nullable()->constrained('finance_periods')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->foreignUuid('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('created_at')->useCurrent();

            $table->unique(['asset_id', 'revision_no'], 'finance_asset_policies_unique_revision');
            $table->index(['asset_id']);
            $table->index(['effective_period_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('finance_asset_policies');
    }
};
