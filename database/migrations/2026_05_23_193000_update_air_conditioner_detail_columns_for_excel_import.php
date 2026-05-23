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
        Schema::table('air_conditioner_details', function (Blueprint $table) {
            $table->string('dimension', 100)->change();
            $table->string('power_rating', 100)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('air_conditioner_details', function (Blueprint $table) {
            $table->unsignedDecimal('dimension', 4, 1)->change();
            $table->unsignedInteger('power_rating')->change();
        });
    }
};
