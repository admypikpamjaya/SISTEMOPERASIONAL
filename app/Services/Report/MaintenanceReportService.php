<?php 

namespace App\Services\Report;

use App\DTOs\Report\CreateMaintenanceReportDTO;
use App\DTOs\Report\MaintenanceReportDataDTO;
use App\Models\Log\MaintenanceLog;
use Illuminate\Support\Facades\Log;

class MaintenanceReportService
{
    public function createLog(CreateMaintenanceReportDTO $dto)
    {
        $log = MaintenanceLog::create([
            'asset_id' => $dto->assetId,
            'worker_name' => $dto->workerName,
            'date' => $dto->workingDate,
            'issue_description' => $dto->issueDescription,
            'working_description' => $dto->workingDescription
        ])->refresh();
        return MaintenanceReportDataDTO::fromModel($log);
    }
}