<?php 

namespace App\Enums\Report\Maintenance;

enum AssetMaintenanceReportStatus: string 
{
    case PENDING = 'Pending';
    case APPROVED = 'Approved';
    case REJECTED = 'Rejected';
}