<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('users') || DB::connection()->getDriverName() !== 'mysql') {
            return;
        }

        DB::statement("
            ALTER TABLE users
            MODIFY COLUMN role ENUM(
                'User',
                'Admin',
                'IT Support',
                'Asset Manager',
                'Finance',
                'Pembina',
                'Blasting',
                'QC',
                'Sistem Management'
            ) NOT NULL DEFAULT 'User'
        ");
    }

    public function down(): void
    {
        if (!Schema::hasTable('users') || DB::connection()->getDriverName() !== 'mysql') {
            return;
        }

        DB::table('users')
            ->where('role', 'Sistem Management')
            ->update(['role' => 'IT Support']);

        DB::statement("
            ALTER TABLE users
            MODIFY COLUMN role ENUM(
                'User',
                'Admin',
                'IT Support',
                'Asset Manager',
                'Finance',
                'Pembina',
                'Blasting',
                'QC'
            ) NOT NULL DEFAULT 'User'
        ");
    }
};
