<?php

namespace Database\Seeders;

use App\Enums\User\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('👥 Seeding user data...');

        // ❌ JANGAN TRUNCATE USERS
        // User::truncate(); // ← INI PENYEBAB ERROR FK (HARUS DIHAPUS)

        $users = [
            [
                'email' => 'ariyapanna@outlook.com',
                'name'  => 'Ariya',
                'password' => Hash::make('password'),
                'role' => UserRole::IT_SUPPORT, // aman + konsisten
            ],
        ];

        foreach ($users as $user) {
            User::firstOrCreate(
                ['email' => $user['email']], // unique key
                array_merge($user, [
                    'id' => Str::uuid(), // UUID wajib
                ])
            );
        }

        $this->command->info('👥 User data successfully seeded.');
    }
}
