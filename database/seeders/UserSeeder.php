<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('👥 Seeding user data...');
        
        User::truncate();
        $users = [
            [
                'name' => 'Ariya',
                'email' => 'ariyapanna@outlook.com',
                'password' => bcrypt('password'),
                'role' => 'IT Support'
            ],
        ];

        foreach($users as $user)
            User::create($user);

        $this->command->info('👥 User data successfully seeded.');
    }
}
