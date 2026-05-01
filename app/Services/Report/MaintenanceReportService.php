<?php 

namespace App\Services\Report;

use App\DTOs\Report\CreateMaintenanceReportDTO;
use App\DTOs\Report\MaintenanceReportDataDTO;
use App\DTOs\Report\UpdateMaintenanceReportDTO;
use App\DTOs\Report\UpdateMaintenanceReportStatusDTO;
use App\Enums\Report\Maintenance\AssetMaintenanceReportStatus;
use App\Models\Log\MaintenanceDocumentation;
use App\Models\Log\MaintenanceLog;
use App\Services\Asset\AssetFactory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class MaintenanceReportService
{
    public function __construct(
        private MaintenanceNotificationService $maintenanceNotificationService
    ) {}

    public function getLogs(
        ?string $keyword = null, 
        ?AssetMaintenanceReportStatus $status = null, 
        ?int $page = 1,     
        ?string $dateFrom = null,
        ?string $dateTo = null
    )
    {
        $query = MaintenanceLog::query();
        if($keyword)
        {
            $query->where(function($q) use ($keyword) {
                $q->where('worker_name', 'like', "%{$keyword}%")
                ->orWhere('issue_description', 'like', "%{$keyword}%")
                ->orWhere('working_description', 'like', "%{$keyword}%")
                ->orWhere('pic', 'like', "%{$keyword}%");
            });
        }

        if($status)
        {
            $query->where('status', $status->value);
        }

        if ($dateFrom && $dateTo) {
            $query->whereBetween('date', [$dateFrom, $dateTo]);
        } elseif ($dateFrom) {
            $query->whereDate('date', '>=', $dateFrom);
        } elseif ($dateTo) {
            $query->whereDate('date', '<=', $dateTo);
        }

        return $query
            ->orderBy('created_at', 'desc')
            ->paginate(10, ['*'], 'page', $page)
            ->appends(array_filter([
                'keyword'   => $keyword,
                'status'    => $status?->value,
                'date_from' => $dateFrom,
                'date_to'   => $dateTo,
            ]));
    }

    public function getLog(string $id)
    {
        $log = $this->findLogOrFail($id, ['asset', 'maintenanceDocumentations']);

        $asset = $log->asset;
        $relation = AssetFactory::createHandler($asset->category)
            ->getRelationName();

        if ($relation) {
            $asset->load($relation);
        }

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
                'pic' => $dto->pic,
                'cost' => $dto->cost
            ]);

            $log->maintenanceDocumentations()->create([
                'document_path' => $path
            ]);

            return $log->refresh()->load(['asset', 'maintenanceDocumentations']);
        });

        try {
            $this->maintenanceNotificationService->sendForLog($log);
        } catch (\Throwable $exception) {
            Log::warning('[MAINTENANCE EMAIL AUTO SEND FAILED]', [
                'maintenance_log_id' => (string) $log->id,
                'error' => $exception->getMessage(),
            ]);
            report($exception);
        }

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
            'working_description' => $dto->workingDescription,
            'pic' => $dto->pic,
            'cost' => $dto->cost
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

    public function exportLogToExcel(array $ids)
    {
        $logs = MaintenanceLog::with('asset', 'maintenanceDocumentations')
            ->whereIn('id', $ids)
            ->get();

        Log::info($logs);
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setCellValue('A1', 'Kode Akun');
        $sheet->setCellValue('B1', 'Lokasi');
        $sheet->setCellValue('C1', 'Nama Pekerja');
        $sheet->setCellValue('D1', 'Tanggal Pengerjaan');
        $sheet->setCellValue('E1', 'Deskripsi Masalah');
        $sheet->setCellValue('F1', 'Deskripsi Pekerjaan');
        $sheet->setCellValue('G1', 'PIC (Penanggung Jawab)');
        $sheet->setCellValue('H1', 'Status');
        $sheet->setCellValue('I1', 'Biaya');
        $sheet->setCellValue('J1', 'Kategori');
        $sheet->setCellValue('K1', 'Dokumentasi Pemeliharaan');

        $row = 2; 
        foreach ($logs as $log) {
            $documentationPath = $log->maintenanceDocumentations->isNotEmpty() 
                ? $log->maintenanceDocumentations[0]->document_path
                : null;
                
            $documentationUrl = $documentationPath 
                ? env('APP_URL') . '/storage/' . $documentationPath 
                : 'No Documentation';

            $sheet->setCellValue('A' . $row, $log->asset->account_code);
            $sheet->setCellValue('B' . $row, $log->asset->location);
            $sheet->setCellValue('C' . $row, $log->worker_name);
            $sheet->setCellValue('D' . $row, \Carbon\Carbon::parse($log->date)->format('Y-m-d H:i:s'));
            $sheet->setCellValue('E' . $row, $log->issue_description);
            $sheet->setCellValue('F' . $row, $log->working_description);
            $sheet->setCellValue('G' . $row, $log->pic);
            $sheet->setCellValue('H' . $row, $log->status->value);
            $sheet->setCellValue('I' . $row, $log->cost_formatted);
            $sheet->setCellValue('J' . $row, $log->asset->category->value);
            $sheet->setCellValue('K' . $row, $documentationUrl);

            $row++;
        }

        $writer = new Xlsx($spreadsheet);
        return $writer;
    }

    public function sendNotification(string $id, bool $manuallyTriggered = true): string
    {
        $log = $this->findLogOrFail($id, ['asset', 'maintenanceDocumentations']);

        $this->maintenanceNotificationService->sendForLog($log, $manuallyTriggered);

        return $this->maintenanceNotificationService->getRecipient();
    }

    private function findLogOrFail(string $id, array $relations = []): MaintenanceLog
    {
        $query = MaintenanceLog::query();

        if ($relations !== []) {
            $query->with($relations);
        }

        $log = $query->find($id);
        if (empty($log)) {
            throw new \Exception('Laporan tidak ditemukan.', 404);
        }

        return $log;
    }
}
