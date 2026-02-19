<?php

namespace App\Enums\Portal;

enum PortalPermission: string
{
    /*
    |----------------------------------------------------------------------
    | ASSET MANAGEMENT
    |----------------------------------------------------------------------
    */
    case ASSET_MANAGEMENT_READ   = 'asset_management.read';
    case ASSET_MANAGEMENT_CREATE = 'asset_management.write';
    case ASSET_MANAGEMENT_UPDATE = 'asset_management.update';
    case ASSET_MANAGEMENT_DELETE = 'asset_management.delete';

    /*
    |----------------------------------------------------------------------
    | MAINTENANCE REPORT
    |----------------------------------------------------------------------
    */
    case MAINTENANCE_REPORT_READ          = 'maintenance_report.read';
    case MAINTENANCE_REPORT_CREATE        = 'maintenance_report.write';
    case MAINTENANCE_REPORT_UPDATE        = 'maintenance_report.update';
    case MAINTENANCE_REPORT_UPDATE_STATUS = 'maintenance_report.update_status';
    case MAINTENANCE_REPORT_DELETE        = 'maintenance_report.delete';

    /*
    |----------------------------------------------------------------------
    | USER MANAGEMENT
    |----------------------------------------------------------------------
    */
    case USER_MANAGEMENT_READ   = 'user_management.read';
    case USER_MANAGEMENT_CREATE = 'user_management.write';
    case USER_MANAGEMENT_UPDATE = 'user_management.update';
    case USER_MANAGEMENT_DELETE = 'user_management.delete';

    /*
    |--------------------------------------------------------------------------
    | FINANCE
    |--------------------------------------------------------------------------
    */
    case FINANCE_DEPRECIATION_CALCULATE = 'finance_depreciation.calculate';
    case FINANCE_REPORT_READ            = 'finance_report.read';
    case FINANCE_REPORT_GENERATE        = 'finance_report.generate';
    case FINANCE_INVOICE_READ           = 'finance_invoice.read';
    case FINANCE_INVOICE_CREATE         = 'finance_invoice.create';
    case FINANCE_INVOICE_UPDATE         = 'finance_invoice.update';
    case FINANCE_INVOICE_DELETE         = 'finance_invoice.delete';
    case FINANCE_INVOICE_NOTE           = 'finance_invoice.note';

    /*
    |----------------------------------------------------------------------
    | ADMIN – COMMUNICATION & BILLING (PHASE 6)
    |----------------------------------------------------------------------
    */

    // Announcements
    case ADMIN_ANNOUNCEMENT_READ   = 'admin_announcement.read';
    case ADMIN_ANNOUNCEMENT_CREATE = 'admin_announcement.create';

    // Billing
    case ADMIN_BILLING_READ    = 'admin_billing.read';
    case ADMIN_BILLING_CONFIRM = 'admin_billing.confirm';

    // Reminder
    case ADMIN_REMINDER_READ = 'admin_reminder.read';
    case ADMIN_REMINDER_SEND = 'admin_reminder.send';

    // Blast
    case ADMIN_BLAST_READ = 'admin_blast.read';
    case ADMIN_BLAST_SEND = 'admin_blast.send';

    /*
    |----------------------------------------------------------------------
    | BLAST RECIPIENT MANAGEMENT (PHASE 9)
    |----------------------------------------------------------------------
    */
    case BLAST_RECIPIENT_READ   = 'blast_recipient.read';
    case BLAST_RECIPIENT_CREATE = 'blast_recipient.create';
    case BLAST_RECIPIENT_UPDATE = 'blast_recipient.update';
    case BLAST_RECIPIENT_IMPORT = 'blast_recipient.import';
    case BLAST_RECIPIENT_DELETE = 'blast_recipient.delete';

// Template blasting massage
case BLAST_TEMPLATE_READ   = 'blast_template.read';
case BLAST_TEMPLATE_CREATE = 'blast_template.create';
case BLAST_TEMPLATE_UPDATE = 'blast_template.update';
case BLAST_TEMPLATE_DELETE = 'blast_template.delete';

}
