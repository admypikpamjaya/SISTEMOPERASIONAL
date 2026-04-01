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
        Schema::create('finance_general_ledger_batches', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->enum('source_type', ['IMPORT', 'MANUAL'])->default('IMPORT');
            $table->string('batch_name', 255);
            $table->string('source_filename', 255)->nullable();
            $table->string('sheet_name', 120)->nullable();
            $table->unsignedSmallInteger('imported_year')->nullable();
            $table->text('notes')->nullable();
            $table->json('meta')->nullable();
            $table->timestamp('imported_at')->nullable();
            $table->foreignUuid('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignUuid('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['source_type']);
            $table->index(['imported_year']);
            $table->index(['imported_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('finance_general_ledger_batches');
    }
};
