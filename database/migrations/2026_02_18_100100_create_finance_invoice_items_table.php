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
        Schema::create('finance_invoice_items', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignUuid('invoice_id')->constrained('finance_invoices')->cascadeOnDelete();
            $table->string('asset_category', 120)->nullable();
            $table->string('account_code', 64);
            $table->string('partner_name', 255)->nullable();
            $table->string('label', 255);
            $table->string('analytic_distribution', 255)->nullable();
            $table->decimal('debit', 18, 2)->default(0);
            $table->decimal('credit', 18, 2)->default(0);
            $table->unsignedInteger('sort_order')->default(0);
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index(['invoice_id', 'sort_order']);
            $table->index(['account_code']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('finance_invoice_items');
    }
};
