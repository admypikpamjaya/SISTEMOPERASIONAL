<?php

use App\Enums\Portal\PortalPermission;
use App\Enums\User\UserRole;

return [

    /* ===============================
     | IT SUPPORT (SUPERADMIN)
     =============================== */
    UserRole::IT_SUPPORT->value => PortalPermission::cases(),

    /* ===============================
     | ADMIN
     =============================== */
    UserRole::ADMIN->value => [
        // ANNOUNCEMENT
        PortalPermission::ADMIN_ANNOUNCEMENT_READ,
        PortalPermission::ADMIN_ANNOUNCEMENT_CREATE,

        // BILLING
        PortalPermission::ADMIN_BILLING_READ,
        PortalPermission::ADMIN_BILLING_CONFIRM,

        // REMINDER
        PortalPermission::ADMIN_REMINDER_READ,
        PortalPermission::ADMIN_REMINDER_SEND,

        // BLAST
        PortalPermission::ADMIN_BLAST_READ,
        PortalPermission::ADMIN_BLAST_SEND,

        // BLAST RECIPIENT
        PortalPermission::BLAST_RECIPIENT_READ,
        PortalPermission::BLAST_RECIPIENT_CREATE,
        PortalPermission::BLAST_RECIPIENT_IMPORT,

        // BLAST TEMPLATE
        PortalPermission::BLAST_TEMPLATE_READ,
        PortalPermission::BLAST_TEMPLATE_CREATE,
        PortalPermission::BLAST_TEMPLATE_UPDATE,
        PortalPermission::BLAST_TEMPLATE_DELETE,
    ],

    /* ===============================
     | ASSET MANAGER
     =============================== */
    UserRole::ASSET_MANAGER->value => [
        PortalPermission::ASSET_MANAGEMENT_READ,
        PortalPermission::ASSET_MANAGEMENT_CREATE,
        PortalPermission::ASSET_MANAGEMENT_UPDATE,
        PortalPermission::ASSET_MANAGEMENT_DELETE,
    ],

    /* ===============================
     | FINANCE
     =============================== */
    UserRole::FINANCE->value => [
        PortalPermission::FINANCE_DEPRECIATION_CALCULATE,
        PortalPermission::FINANCE_REPORT_READ,
        PortalPermission::FINANCE_REPORT_GENERATE,
        PortalPermission::FINANCE_INVOICE_READ,
        PortalPermission::FINANCE_INVOICE_CREATE,
        PortalPermission::FINANCE_INVOICE_UPDATE,
        PortalPermission::FINANCE_INVOICE_DELETE,
        PortalPermission::FINANCE_INVOICE_NOTE,
    ],
];
