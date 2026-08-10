<?php

namespace Database\Seeders;

use App\Enums\User\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class SystemManagementRidoSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::firstOrNew([
            'email' => 'ridodwikurniawan@gmail.com',
        ]);

        if (!$user->exists) {
            $user->id = (string) Str::uuid();
        }

        $user->name = 'Rido Dwi Kurniawan';
        $user->password = Hash::make('Password-123');
        $user->role = UserRole::SYSTEM_MANAGEMENT->value;
        $user->save();
    }
}
