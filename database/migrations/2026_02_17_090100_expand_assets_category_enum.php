<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE assets MODIFY category ENUM('AC','OTHER') NOT NULL");
    }

    public function down(): void
    {
        DB::statement("UPDATE assets SET category = 'AC' WHERE category = 'OTHER'");
        DB::statement("ALTER TABLE assets MODIFY category ENUM('AC') NOT NULL");
    }
};
