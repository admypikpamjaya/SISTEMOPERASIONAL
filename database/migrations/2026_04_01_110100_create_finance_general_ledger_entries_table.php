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
        Schema::create('finance_general_ledger_entries', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('batch_id')->constrained('finance_general_ledger_batches')->cascadeOnDelete();
            $table->enum('row_type', ['OPENING', 'ENTRY'])->default('ENTRY');
            $table->date('entry_date')->nullable();
            $table->string('account_code', 64);
            $table->string('account_name', 255);
            $table->string('transaction_no', 120)->nullable();
            $table->string('communication', 255)->nullable();
            $table->string('partner_name', 255)->nullable();
            $table->string('currency', 20)->nullable();
            $table->string('label', 255)->nullable();
            $table->string('reference', 255)->nullable();
            $table->string('analytic_distribution', 255)->nullable();
            $table->decimal('opening_balance', 18, 2)->default(0);
            $table->decimal('debit', 18, 2)->default(0);
            $table->decimal('credit', 18, 2)->default(0);
            $table->decimal('balance_amount', 18, 2)->default(0);
            $table->unsignedInteger('sort_order')->default(0);
            $table->unsignedInteger('sheet_row_number')->nullable();
            $table->boolean('is_manual')->default(false);
            $table->json('meta')->nullable();
            $table->foreignUuid('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignUuid('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['batch_id', 'account_code']);
            $table->index(['batch_id', 'entry_date']);
            $table->index(['batch_id', 'row_type']);
            $table->index(['batch_id', 'is_manual']);
            $table->index(['batch_id', 'account_code', 'entry_date', 'sort_order'], 'finance_gl_entries_account_order_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('finance_general_ledger_entries');
    }
};
