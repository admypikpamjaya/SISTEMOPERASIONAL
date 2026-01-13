<?php 

namespace App\Services\Report;

use App\DTOs\Report\CreateMaintenanceReportDTO;
use App\DTOs\Report\MaintenanceReportDataDTO;
use App\DTOs\Report\UpdateMaintenanceReportDTO;
use App\DTOs\Report\UpdateMaintenanceReportStatusDTO;
use App\Enums\Report\Maintenance\AssetMaintenanceReportStatus;
use App\Models\Log\MaintenanceDocumentation;
use App\Models\Log\MaintenanceLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MaintenanceReportService
{
    public function getLogs(?string $keyword = null, ?AssetMaintenanceReportStatus $status = null, ?int $page = 1)
    {
        $query = MaintenanceLog::query();
        if($keyword)
        {
            $query->where(function($q) use ($keyword) {
                $q->where('worker_name', 'like', "%{$keyword}%")
                ->orWhere('issue_description', 'like', "%{$keyword}%")
                ->orWhere('working_description', 'like', "%{$keyword}%");
            });
        }

        if($status)
        {
            $query->where('status', $status->value);
        }


        return $query
            ->orderBy('created_at', 'desc')
            ->paginate(10, ['*'], 'page', $page)
            ->appends(array_filter([
                'keyword' => $keyword,
                'status' => $status?->value
            ]));
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
        $log = DB::transaction(function () use ($dto) {
            $path = MaintenanceDocumentation::store($dto->evidencePhoto);    

            $log = MaintenanceLog::create([
                'asset_id' => $dto->assetId,
                'worker_name' => $dto->workerName,
                'date' => $dto->workingDate,
                'issue_description' => $dto->issueDescription,
                'working_description' => $dto->workingDescription,
                'pic' => $dto->pic
            ]);

            $log->maintenanceDocumentations()->create([
                'document_path' => $path
            ]);

            return $log->refresh();
        });

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