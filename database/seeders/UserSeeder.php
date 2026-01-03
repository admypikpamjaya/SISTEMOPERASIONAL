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
                'username' => 'ariya',
                'password' => bcrypt('password'),
                'role' => 'IT Support'
            ],
            [
                'name' => 'Erick',
                'username' => 'erick',
                'password' => bcrypt('password'),
                'role' => 'Asset Manager'
            ],
            [
                'name' => 'Dio',
                'username' => 'dio',
                'password' => bcrypt('password'),
                'role' => 'Finance'
            ]
        ];

        foreach($users as $user)
            User::create($user);

        $this->command->info('👥 User data successfully seeded.');
    }
}
