<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
            ALTER TABLE assets
            CHANGE category category
            ENUM(
                'AC',
                'OTHER',
                'COMPUTER',
                'BUILDING_INFRASTRUCTURE',
                'ELECTRONIC',
                'ROOM_INVENTORY'
            ) NOT NULL
        ");
    }

    public function down(): void
    {
        DB::statement("
            UPDATE assets
            SET category = 'OTHER'
            WHERE category IN ('BUILDING_INFRASTRUCTURE', 'ELECTRONIC', 'ROOM_INVENTORY')
        ");

        DB::statement("
            ALTER TABLE assets
            CHANGE category category
            ENUM('AC', 'OTHER', 'COMPUTER') NOT NULL
        ");
    }
};
