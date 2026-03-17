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

        $defaultPassword = Hash::make('Password-123!');

        $users = [
            [
                'email' => 'user@ypik.local',
                'name' => 'User YPIK',
                'password' => $defaultPassword,
                'role' => UserRole::USER,
            ],
            [
                'email' => 'admin@ypik.local',
                'name' => 'Admin YPIK',
                'password' => $defaultPassword,
                'role' => UserRole::ADMIN,
            ],
            [
                'email' => 'it.support@ypik.local',
                'name' => 'IT Support YPIK',
                'password' => $defaultPassword,
                'role' => UserRole::IT_SUPPORT,
            ],
            [
                'email' => 'asset.manager@ypik.local',
                'name' => 'Asset Manager YPIK',
                'password' => $defaultPassword,
                'role' => UserRole::ASSET_MANAGER,
            ],
            [
                'email' => 'finance@ypik.local',
                'name' => 'Finance YPIK',
                'password' => $defaultPassword,
                'role' => UserRole::FINANCE,
            ],
            [
                'email' => 'pembina@ypik.local',
                'name' => 'Pembina YPIK',
                'password' => $defaultPassword,
                'role' => UserRole::PEMBINA,
            ],
            [
                'email' => 'blasting@ypik.local',
                'name' => 'Blasting YPIK',
                'password' => $defaultPassword,
                'role' => UserRole::BLASTING,
            ],
            [
                'email' => 'qc@ypik.local',
                'name' => 'QC YPIK',
                'password' => $defaultPassword,
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
                'Blasting',
                'QC'
            ) NOT NULL DEFAULT 'User'
        ");
    }
}
