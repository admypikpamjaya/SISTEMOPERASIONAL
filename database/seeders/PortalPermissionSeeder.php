<?php

namespace Database\Seeders;

use App\Enums\Portal\PortalPermission;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PortalPermissionSeeder extends Seeder
{
    public function run(): void
    {
        foreach (PortalPermission::cases() as $permission) {
            DB::table('portal_permissions')->updateOrInsert(
                ['name' => $permission->value],
                ['created_at' => now(), 'updated_at' => now()]
            );
        }
    }
}
