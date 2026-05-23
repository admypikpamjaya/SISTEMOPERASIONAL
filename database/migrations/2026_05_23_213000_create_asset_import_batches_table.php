<?php

use App\Enums\Asset\AssetCategory;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asset_import_batches', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->enum('category', array_column(AssetCategory::cases(), 'value'));
            $table->enum('source_type', ['excel', 'csv']);
            $table->string('source_file_name');
            $table->unsignedInteger('processed_rows')->default(0);
            $table->unsignedInteger('imported_rows')->default(0);
            $table->unsignedInteger('sheet_count')->default(0);
            $table->json('sheet_names')->nullable();
            $table->json('metadata')->nullable();

            $table->foreignUuid('imported_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();

            $table->index(['category', 'source_type']);
            $table->index('imported_by');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asset_import_batches');
    }
};
