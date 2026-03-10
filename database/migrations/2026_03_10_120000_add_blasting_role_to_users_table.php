<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::statement("ALTER TABLE `users` MODIFY COLUMN `role` ENUM('User','Admin','IT Support','Asset Manager','Finance','Pembina','Blasting','Qc') NOT NULL DEFAULT 'User'");
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::statement("ALTER TABLE `users` MODIFY COLUMN `role` ENUM('User','Admin','IT Support','Asset Manager','Finance','Pembina','Qc') NOT NULL DEFAULT 'User'");
    }
};
