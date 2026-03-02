<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tunggakan_import_batches', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->enum('source_type', ['excel', 'manual', 'database']);
            $table->string('source_reference')->nullable();
            $table->text('notes')->nullable();

            $table->uuid('imported_by')->nullable();
            $table->foreign('imported_by')
                ->references('id')
                ->on('users')
                ->nullOnDelete();

            $table->unsignedInteger('total_rows')->default(0);
            $table->unsignedInteger('matched_rows')->default(0);
            $table->unsignedInteger('unmatched_rows')->default(0);

            $table->timestamps();

            $table->index('source_type');
            $table->index('imported_by');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tunggakan_import_batches');
    }
};
