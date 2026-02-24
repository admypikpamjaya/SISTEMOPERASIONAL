<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        if (Schema::hasTable('portal_permissions')) {
            $this->call(PortalPermissionSeeder::class);
        }

        if (Schema::hasTable('users')) {
            $this->call(UserSeeder::class);
        }
    }
}
