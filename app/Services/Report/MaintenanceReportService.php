<?php 

namespace App\Services\Report;

use App\DTOs\Report\CreateMaintenanceReportDTO;
use App\DTOs\Report\MaintenanceReportDataDTO;
use App\DTOs\Report\UpdateMaintenanceReportDTO;
use App\DTOs\Report\UpdateMaintenanceReportStatusDTO;
use App\Enums\Report\Maintenance\AssetMaintenanceReportStatus;
use App\Models\Log\MaintenanceLog;
use Illuminate\Support\Facades\Log;

class MaintenanceReportService
{
    public function getLogs(?string $keyword = null, ?int $page = 1)
    {
        $logs = MaintenanceLog::where(function ($query) use ($keyword) {
                $query->where('worker_name', 'like', "%{$keyword}%")
                    ->orWhere('issue_description', 'like', "%{$keyword}%")
                    ->orWhere('working_description', 'like', "%{$keyword}%");
            })
            ->orWhereHas('asset', function ($query) use ($keyword) {
                $query->where('account_code', 'like', "%{$keyword}%");
            })
            ->paginate(5, ['*'], 'page', $page);

        return $logs;
    }

    public function getLog(string $id)
    {
        $log = MaintenanceLog::with('asset')->find($id);
        if(empty($log))
            throw new \Exception('Laporan tidak ditemukan.', 404);

        return MaintenanceReportDataDTO::fromModel($log);
    }

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

    public function updateLog(UpdateMaintenanceReportDTO $dto)
    {
        $log = MaintenanceLog::find($dto->id);
        if(empty($log))
            throw new \Exception('Laporan tidak ditemukan.', 404);

        $log->update([
            'worker_name' => $dto->workerName,
            'date' => $dto->workingDate,
            'issue_description' => $dto->issueDescription,
            'working_description' => $dto->workingDescription
        ]);

        return MaintenanceReportDataDTO::fromModel($log);
    }

    public function updateStatus(UpdateMaintenanceReportStatusDTO $dto)
    {
        $log = MaintenanceLog::find($dto->id);
        if(empty($log))
            throw new \Exception('Laporan tidak ditemukan.', 404);

        if($dto->status === AssetMaintenanceReportStatus::PENDING)
            throw new \Exception('Status tidak valid.', 400);
        
        $log->update([
            'status' => $dto->status
        ]);

        return MaintenanceReportDataDTO::fromModel($log);
    }

    public function deleteLog(string $id)
    {
        $log = MaintenanceLog::find($id);
        if(empty($log))
            throw new \Exception('Laporan tidak ditemukan.', 404);

        $log->delete();
    }
}