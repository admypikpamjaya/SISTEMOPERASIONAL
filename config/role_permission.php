<?php

use App\Enums\Portal\PortalPermission;
use App\Enums\User\UserRole;

return [
    UserRole::IT_SUPPORT->value => PortalPermission::cases(),
    UserRole::ASSET_MANAGER->value => [
        PortalPermission::ASSET_MANAGEMENT_READ,
        PortalPermission::ASSET_MANAGEMENT_CREATE,
        PortalPermission::ASSET_MANAGEMENT_UPDATE,
        PortalPermission::ASSET_MANAGEMENT_DELETE
    ],
    UserRole::FINANCE->value => [
        PortalPermission::MAINTENANCE_REPORT_READ,
        PortalPermission::MAINTENANCE_REPORT_UPDATE_STATUS
    ]
];