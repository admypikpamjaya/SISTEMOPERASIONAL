<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('building_infrastructure_details', function (Blueprint $table) {
            $table->foreignUuid('asset_id')->primary()->constrained('assets')->cascadeOnDelete();
            $table->string('asset_code')->nullable()->index();
            $table->string('asset_name')->nullable();
            $table->string('asset_type')->nullable();
            $table->string('land_area')->nullable();
            $table->string('building_area')->nullable();
            $table->string('volume_size')->nullable();
            $table->string('document_number')->nullable()->index();
            $table->date('acquisition_date')->nullable();
            $table->string('asset_account_code')->nullable();
            $table->unsignedInteger('useful_life_years')->nullable();
            $table->decimal('initial_accumulated_depreciation', 18, 2)->nullable();
            $table->decimal('current_year_depreciation', 18, 2)->nullable();
            $table->decimal('accumulated_depreciation', 18, 2)->nullable();
            $table->decimal('book_value', 18, 2)->nullable();
            $table->string('condition')->nullable();
            $table->string('status')->nullable();
            $table->string('responsible_person')->nullable();
            $table->text('notes')->nullable();
            $table->string('source_data')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('building_infrastructure_details');
    }
};
