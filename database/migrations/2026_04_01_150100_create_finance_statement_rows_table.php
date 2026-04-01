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
        Schema::create('finance_statement_rows', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('batch_id')->constrained('finance_statement_batches')->cascadeOnDelete();
            $table->string('section_key', 60)->nullable();
            $table->string('section_label', 120)->nullable();
            $table->string('group_label', 255)->nullable();
            $table->string('account_code', 64)->nullable();
            $table->string('account_name', 255);
            $table->string('finance_type', 60)->nullable();
            $table->decimal('amount', 18, 2)->default(0);
            $table->unsignedInteger('sort_order')->default(0);
            $table->unsignedInteger('sheet_row_number')->nullable();
            $table->boolean('is_manual')->default(false);
            $table->json('meta')->nullable();
            $table->foreignUuid('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignUuid('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['batch_id', 'section_key']);
            $table->index(['batch_id', 'account_code']);
            $table->index(['batch_id', 'is_manual']);
            $table->index(['batch_id', 'sort_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('finance_statement_rows');
    }
};
