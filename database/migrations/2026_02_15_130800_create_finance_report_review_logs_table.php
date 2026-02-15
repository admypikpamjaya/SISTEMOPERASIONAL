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
        Schema::create('finance_report_review_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('report_snapshot_id')->constrained('finance_report_snapshots')->cascadeOnDelete();
            $table->foreignUuid('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->useCurrent();
            $table->text('note')->nullable();
            $table->timestamps();

            $table->index(['report_snapshot_id', 'reviewed_at'], 'finance_report_review_logs_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('finance_report_review_logs');
    }
};
