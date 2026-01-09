<?php 

namespace App\DTOs\Report;

use App\Enums\Report\Maintenance\AssetMaintenanceReportStatus;

class UpdateMaintenanceReportStatusDTO
{
    public function __construct(
        public string $id,
        public AssetMaintenanceReportStatus $status
    ) {}

    public static function fromArray(array $data): self 
    {
        return new self(
            $data['id'],
            AssetMaintenanceReportStatus::from($data['status'])
        );
    }
}