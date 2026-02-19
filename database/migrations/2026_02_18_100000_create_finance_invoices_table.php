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
        Schema::create('finance_invoices', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('invoice_no', 64)->unique();
            $table->date('accounting_date');
            $table->enum('entry_type', ['INCOME', 'EXPENSE']);
            $table->string('journal_name', 255);
            $table->string('reference', 255)->nullable();
            $table->enum('status', ['DRAFT', 'POSTED', 'CANCELLED'])->default('DRAFT');
            $table->decimal('total_debit', 18, 2)->default(0);
            $table->decimal('total_credit', 18, 2)->default(0);
            $table->foreignUuid('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignUuid('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignUuid('posted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('posted_at')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index(['status']);
            $table->index(['accounting_date']);
            $table->index(['entry_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('finance_invoices');
    }
};
