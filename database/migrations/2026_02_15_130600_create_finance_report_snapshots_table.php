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
        Schema::create('finance_report_snapshots', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('period_id')->constrained('finance_periods')->cascadeOnDelete();
            $table->enum('report_type', ['MONTHLY', 'YEARLY']);
            $table->unsignedInteger('version_no');
            $table->foreignUuid('reconciliation_snapshot_id')->constrained('finance_reconciliation_snapshots')->cascadeOnDelete();
            $table->json('summary');
            $table->foreignUuid('generated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('generated_at')->useCurrent();
            $table->boolean('is_read_only')->default(true);
            $table->timestamps();

            $table->unique(['report_type', 'period_id', 'version_no'], 'finance_report_snapshots_unique_version');
            $table->index(['report_type', 'period_id']);
            $table->index(['is_read_only']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('finance_report_snapshots');
    }
};
