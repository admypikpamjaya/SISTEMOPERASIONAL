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
        Schema::create('finance_report_snapshot_items', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignUuid('report_snapshot_id')->constrained('finance_report_snapshots')->cascadeOnDelete();
            $table->string('line_code', 64);
            $table->string('line_label', 255);
            $table->decimal('amount', 18, 2);
            $table->unsignedInteger('sort_order')->default(0);
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->unique(['report_snapshot_id', 'line_code'], 'finance_report_snapshot_items_unique_line');
            $table->index(['report_snapshot_id', 'sort_order'], 'finance_report_snapshot_items_idx_sort');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('finance_report_snapshot_items');
    }
};
