<?php

namespace Database\Seeders;

use App\Enums\User\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Seeding user data...');
        $this->syncRoleEnumForMySql();

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
            [
                'email' => 'pembina@ypik.local',
                'name' => 'Pembina YPIK',
                'password' => Hash::make('Pembina123!'),
                'role' => UserRole::PEMBINA,
            ],
            [
                'email' => 'qc@ypik.local',
                'name' => 'QC YPIK',
                'password' => Hash::make('Qc12345!'),
                'role' => UserRole::QC,
            ],
        ];

        foreach ($users as $user) {
            $record = User::firstOrNew(['email' => $user['email']]);

            if (!$record->exists) {
                $record->id = (string) Str::uuid();
            }

            $record->name = $user['name'];
            $record->password = $user['password'];
            $record->role = $user['role']->value;
            $record->save();
        }

        $this->command->info('User data successfully seeded.');
    }

    private function syncRoleEnumForMySql(): void
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
                'QC'
            ) NOT NULL DEFAULT 'User'
        ");
    }
}
