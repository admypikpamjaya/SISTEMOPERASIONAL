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
        $this->command->info('Seeding user data...');

        $users = [
            [
                'email' => 'ariyapanna@outlook.com',
                'name' => 'Ariya',
                'password' => Hash::make('password'),
                'role' => UserRole::IT_SUPPORT,
            ],
            [
                'email' => 'asset.management@ypik.local',
                'name' => 'Asset Management',
                'password' => Hash::make('password'),
                'role' => UserRole::ASSET_MANAGER,
            ],
        ];

        foreach ($users as $user) {
            User::firstOrCreate(
                ['email' => $user['email']],
                array_merge($user, [
                    'id' => Str::uuid(),
                ])
            );
        }

        $this->command->info('User data successfully seeded.');
    }
}
