<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

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
                'ROOM_INVENTORY',
                'VEHICLE'
            ) NOT NULL
        ");

        if (Schema::hasTable('asset_import_batches')) {
            DB::statement("
                ALTER TABLE asset_import_batches
                CHANGE category category
                ENUM(
                    'AC',
                    'OTHER',
                    'COMPUTER',
                    'BUILDING_INFRASTRUCTURE',
                    'ELECTRONIC',
                    'ROOM_INVENTORY',
                    'VEHICLE'
                ) NOT NULL
            ");
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('asset_import_batches')) {
            DB::statement("
                UPDATE asset_import_batches
                SET category = 'OTHER'
                WHERE category = 'VEHICLE'
            ");

            DB::statement("
                ALTER TABLE asset_import_batches
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

        DB::statement("
            UPDATE assets
            SET category = 'OTHER'
            WHERE category = 'VEHICLE'
        ");

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
};
