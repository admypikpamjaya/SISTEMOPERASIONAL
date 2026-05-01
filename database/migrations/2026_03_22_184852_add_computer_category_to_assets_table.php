<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('assets', function (Blueprint $table) {
            DB::statement("ALTER TABLE assets CHANGE category category ENUM('AC', 'OTHER', 'COMPUTER') NOT NULL");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('assets', function (Blueprint $table) {
            DB::statement("ALTER TABLE assets CHANGE category category ENUM('AC', 'OTHER') NOT NULL");
        });
    }
};
