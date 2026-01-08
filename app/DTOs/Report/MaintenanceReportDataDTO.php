<?php 

namespace App\DTOs\Report;

use App\Models\Log\MaintenanceLog;
use App\Enums\Report\Maintenance\AssetMaintenanceReportStatus;
use Carbon\Carbon;

class MaintenanceReportDataDTO
{
    public function __construct(
        public string $id,
        public string $assetId,
        public string $workerName,
        public Carbon $workingDate,
        public string $issueDescription,
        public string $workingDescription,
        public AssetMaintenanceReportStatus $status
    ) {}

    public static function fromModel(MaintenanceLog $data): self 
    {
        return new self(
            $data->id,
            $data->asset_id,
            $data->worker_name,
            $data->date,
            $data->issue_description,
            $data->working_description,
            $data->status
        );
    }
}