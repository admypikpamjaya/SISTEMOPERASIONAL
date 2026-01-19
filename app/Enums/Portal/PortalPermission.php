<?php

namespace App\Enums\Portal;

enum PortalPermission: string
{
    /* ===============================
     | ASSET MANAGEMENT
     =============================== */
    case ASSET_MANAGEMENT_READ   = 'asset_management.read';
    case ASSET_MANAGEMENT_CREATE = 'asset_management.write';
    case ASSET_MANAGEMENT_UPDATE = 'asset_management.update';
    case ASSET_MANAGEMENT_DELETE = 'asset_management.delete';

    /* ===============================
     | MAINTENANCE REPORT
     =============================== */
    case MAINTENANCE_REPORT_READ          = 'maintenance_report.read';
    case MAINTENANCE_REPORT_CREATE        = 'maintenance_report.write';
    case MAINTENANCE_REPORT_UPDATE        = 'maintenance_report.update';
    case MAINTENANCE_REPORT_UPDATE_STATUS = 'maintenance_report.update_status';
    case MAINTENANCE_REPORT_DELETE        = 'maintenance_report.delete';

    /* ===============================
     | USER MANAGEMENT
     =============================== */
    case USER_MANAGEMENT_READ   = 'user_management.read';
    case USER_MANAGEMENT_CREATE = 'user_management.write';
    case USER_MANAGEMENT_UPDATE = 'user_management.update';
    case USER_MANAGEMENT_DELETE = 'user_management.delete';

    /* ===============================
     | ADMIN COMMUNICATION (PHASE 6.2+)
     =============================== */
    case ADMIN_ANNOUNCEMENT_READ   = 'admin.announcement.read';
    case ADMIN_ANNOUNCEMENT_CREATE = 'admin.announcement.create';
    case ADMIN_COMMUNICATION_READ = 'admin_communication.read';
    case ADMIN_COMMUNICATION_SEND = 'admin_communication.send';

    case ADMIN_BLAST_SEND = 'admin.blast.send';

    case ADMIN_REMINDER_PREVIEW = 'admin.reminder.preview';
    case ADMIN_REMINDER_SEND    = 'admin.reminder.send';

    case ADMIN_BILLING_READ    = 'admin.billing.read';
    case ADMIN_BILLING_CONFIRM = 'admin.billing.confirm';
}
