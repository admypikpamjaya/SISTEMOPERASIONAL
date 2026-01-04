<?php

namespace App\Services\AccessControl;

use App\Models\User;

class PermissionService
{
    public function getAccessForUser(User $user): array 
    {
        return collect(config('role_permission')[$user->role] ?? [])
            ->map(fn ($p) => $p->value)
            ->toArray();
    }

    public function checkAccess(User $user, string $permission): bool 
    {
        return in_array($permission, $this->getAccessForUser($user));
    }
}