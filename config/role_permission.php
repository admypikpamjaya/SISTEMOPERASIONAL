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

        // REMINDER
        PortalPermission::ADMIN_REMINDER_READ,
        PortalPermission::ADMIN_REMINDER_SEND,

        // BLAST
        PortalPermission::ADMIN_BLAST_READ,
        PortalPermission::ADMIN_BLAST_SEND,

        // BLAST RECIPIENT
        PortalPermission::BLAST_RECIPIENT_READ,
        PortalPermission::BLAST_RECIPIENT_CREATE,
        PortalPermission::BLAST_RECIPIENT_UPDATE,
        PortalPermission::BLAST_RECIPIENT_IMPORT,
        PortalPermission::BLAST_RECIPIENT_DELETE,

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
        PortalPermission::MAINTENANCE_REPORT_READ,
        PortalPermission::MAINTENANCE_REPORT_UPDATE,
        PortalPermission::MAINTENANCE_REPORT_UPDATE_STATUS,
        PortalPermission::MAINTENANCE_REPORT_DELETE,
    ],

    /* ===============================
     | FINANCE
     =============================== */
    UserRole::FINANCE->value => [
        PortalPermission::FINANCE_DEPRECIATION_READ,
        PortalPermission::FINANCE_DEPRECIATION_CALCULATE,
        PortalPermission::FINANCE_REPORT_READ,
        PortalPermission::FINANCE_REPORT_GENERATE,
        PortalPermission::FINANCE_INVOICE_READ,
        PortalPermission::FINANCE_INVOICE_CREATE,
        PortalPermission::FINANCE_INVOICE_UPDATE,
        PortalPermission::FINANCE_INVOICE_DELETE,
        PortalPermission::FINANCE_INVOICE_NOTE,
    ],

    /* ===============================
     | PEMBINA (READ-ONLY ALL FEATURES)
     =============================== */
    UserRole::PEMBINA->value => [
        PortalPermission::ASSET_MANAGEMENT_READ,
        PortalPermission::MAINTENANCE_REPORT_READ,
        PortalPermission::USER_MANAGEMENT_READ,
        PortalPermission::FINANCE_DEPRECIATION_READ,
        PortalPermission::FINANCE_REPORT_READ,
        PortalPermission::FINANCE_INVOICE_READ,
        PortalPermission::ADMIN_ANNOUNCEMENT_READ,
        PortalPermission::ADMIN_REMINDER_READ,
        PortalPermission::ADMIN_BLAST_READ,
        PortalPermission::BLAST_RECIPIENT_READ,
        PortalPermission::BLAST_TEMPLATE_READ,
    ],

    /* ===============================
     | QC (READ-ONLY ASSET & MAINTENANCE REPORT)
     =============================== */
    UserRole::QC->value => [
        PortalPermission::ASSET_MANAGEMENT_READ,
        PortalPermission::MAINTENANCE_REPORT_READ,
    ],
];
