<?php 

namespace App\Enums\Portal;

enum PortalPermission: string 
{
    case ASSET_MANAGEMENT_READ = 'asset_management.read';
    case ASSET_MANAGEMENT_CREATE = 'asset_management.write';
    case ASSET_MANAGEMENT_UPDATE = 'asset_management.update';
    case ASSET_MANAGEMENT_DELETE = 'asset_management.delete';

    case MAINTENANCE_REPORT_READ = 'maintenance_report.read';
    case MAINTENANCE_REPORT_CREATE = 'maintenance_report.write';
    case MAINTENANCE_REPORT_UPDATE = 'maintenance_report.update';
    case MAINTENANCE_REPORT_UPDATE_STATUS = 'maintenance_report.update_status';
    case MAINTENANCE_REPORT_DELETE = 'maintenance_report.delete';

    case USER_MANAGEMENT_READ = 'user_management.read';
    case USER_MANAGEMENT_CREATE = 'user_management.write';
    case USER_MANAGEMENT_UPDATE = 'user_management.update';
    case USER_MANAGEMENT_DELETE = 'user_management.delete';
}