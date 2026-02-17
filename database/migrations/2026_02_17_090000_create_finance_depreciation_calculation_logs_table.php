<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('finance_depreciation_calculation_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('asset_id')->constrained('assets')->cascadeOnDelete();
            $table->unsignedTinyInteger('period_month');
            $table->unsignedSmallInteger('period_year');
            $table->decimal('acquisition_cost', 18, 2);
            $table->unsignedInteger('useful_life_months');
            $table->decimal('depreciation_per_month', 18, 2);
            $table->foreignUuid('calculated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('calculated_at')->useCurrent();
            $table->timestamps();

            $table->index(['asset_id', 'period_year', 'period_month'], 'finance_depr_calc_logs_asset_period_index');
            $table->index('calculated_at', 'finance_depr_calc_logs_calculated_at_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('finance_depreciation_calculation_logs');
    }
};
