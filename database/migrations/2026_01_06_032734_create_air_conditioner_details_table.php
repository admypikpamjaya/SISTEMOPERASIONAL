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
        Schema::create('air_conditioner_details', function (Blueprint $table) {
            $table->foreignUuid('asset_id')->primary()->constrained('assets')->cascadeOnDelete();
            $table->string('brand');
            $table->unsignedDecimal('dimension', 4, 1);
            $table->unsignedInteger('power_rating');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('air_conditioner_details');
    }
};
