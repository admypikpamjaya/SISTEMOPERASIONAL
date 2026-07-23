<?php

namespace App\Services\AccessControl;

use App\Enums\Portal\PortalPermission;
use App\Enums\User\UserRole;
use App\Models\RolePermissionOverride;
use App\Models\User;
use Illuminate\Support\Facades\Schema;

class PermissionService
{
    public function getAccessForUser(User $user): array 
    {
        if ($user->role === UserRole::SYSTEM_MANAGEMENT->value) {
            return collect(PortalPermission::cases())
                ->map(fn ($p) => $p->value)
                ->toArray();
        }

        $permissions = collect(config('role_permission')[$user->role] ?? [])
            ->map(fn ($p) => $p->value)
            ->values();

        if (Schema::hasTable('role_permission_overrides')) {
            $overrides = RolePermissionOverride::query()
                ->where('role', $user->role)
                ->get(['permission', 'allowed']);

            foreach ($overrides as $override) {
                if ($override->allowed) {
                    $permissions->push((string) $override->permission);
                    continue;
                }

                $permissions = $permissions->reject(
                    fn (string $permission): bool => $permission === (string) $override->permission
                );
            }
        }

        return $permissions
            ->unique()
            ->values()
            ->toArray();
    }

    public function checkAccess(User $user, string $permission): bool 
    {
        return in_array($permission, $this->getAccessForUser($user));
    }
}
