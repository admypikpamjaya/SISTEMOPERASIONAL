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
        Schema::create('finance_depreciation_runs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('period_id')->constrained('finance_periods')->cascadeOnDelete();
            $table->unsignedInteger('run_no');
            $table->enum('status', ['DRAFT', 'POSTED', 'VOID'])->default('DRAFT');
            $table->unsignedInteger('assets_count')->default(0);
            $table->decimal('total_depreciation', 18, 2)->default(0);
            $table->foreignUuid('generated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('generated_at')->useCurrent();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['period_id', 'run_no'], 'finance_depreciation_runs_unique_run');
            $table->index(['status']);
            $table->index(['generated_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('finance_depreciation_runs');
    }
};
