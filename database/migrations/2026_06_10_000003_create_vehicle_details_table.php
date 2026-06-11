<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicle_details', function (Blueprint $table) {
            $table->foreignUuid('asset_id')->primary()->constrained('assets')->cascadeOnDelete();
            $table->string('vehicle_type')->nullable();
            $table->string('vehicle_name')->nullable();
            $table->string('brand')->nullable();
            $table->string('model_type')->nullable();
            $table->string('vehicle_year', 20)->nullable();
            $table->string('color')->nullable();
            $table->string('license_plate')->nullable()->index();
            $table->string('chassis_number')->nullable()->index();
            $table->string('engine_number')->nullable()->index();
            $table->string('bpkb_name')->nullable();
            $table->date('stnk_valid_until')->nullable();
            $table->date('tax_valid_until')->nullable();
            $table->unsignedBigInteger('kilometer')->nullable();
            $table->date('acquisition_date')->nullable();
            $table->string('asset_account_code')->nullable();
            $table->unsignedInteger('useful_life_years')->nullable();
            $table->decimal('accumulated_depreciation', 18, 2)->nullable();
            $table->decimal('book_value', 18, 2)->nullable();
            $table->string('pic')->nullable();
            $table->string('condition')->nullable();
            $table->string('status')->nullable();
            $table->text('notes')->nullable();
            $table->string('source_data')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicle_details');
    }
};
