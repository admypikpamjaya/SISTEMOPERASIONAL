<?php

use App\Enums\Portal\PortalPermission;
use App\Enums\User\UserRole;

return [

    // SUPERADMIN / IT SUPPORT
    UserRole::IT_SUPPORT->value => PortalPermission::cases(),

    // ADMIN (boleh komunikasi & billing)
    UserRole::ADMIN->value => [
        PortalPermission::ADMIN_ANNOUNCEMENT_READ,
        PortalPermission::ADMIN_ANNOUNCEMENT_CREATE,

        PortalPermission::ADMIN_BILLING_READ,
        PortalPermission::ADMIN_BILLING_CONFIRM,

        PortalPermission::ADMIN_REMINDER_PREVIEW,
        PortalPermission::ADMIN_REMINDER_SEND,

        PortalPermission::ADMIN_BLAST_SEND,
    ],

    // ASSET MANAGER
    UserRole::ASSET_MANAGER->value => [
        PortalPermission::ASSET_MANAGEMENT_READ,
        PortalPermission::ASSET_MANAGEMENT_CREATE,
        PortalPermission::ASSET_MANAGEMENT_UPDATE,
        PortalPermission::ASSET_MANAGEMENT_DELETE,
    ],

    // FINANCE
    UserRole::FINANCE->value => [
        PortalPermission::MAINTENANCE_REPORT_READ,
        PortalPermission::MAINTENANCE_REPORT_UPDATE_STATUS,
    ],
];
