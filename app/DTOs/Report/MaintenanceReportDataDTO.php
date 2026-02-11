<?php 

namespace App\DTOs\Report;

use App\DTOs\Asset\MinimalAssetDataDTO;
use App\Models\Log\MaintenanceLog;
use App\Enums\Report\Maintenance\AssetMaintenanceReportStatus;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class MaintenanceReportDataDTO
{
    public function __construct(
        public string $id,
        public MinimalAssetDataDTO $asset,
        public string $workerName,
        public Carbon $workingDate,
        public string $issueDescription,
        public string $workingDescription,
        public string $pic,
        public float $cost,
        public string $costFormatted,
        public AssetMaintenanceReportStatus $status,
        public array $evidencePhotos
    ) {}

    public static function fromModel(MaintenanceLog $data): self 
    {
        return new self(
            $data->id,
            MinimalAssetDataDTO::fromModel($data->asset),
            $data->worker_name,
            $data->date,
            $data->issue_description,
            $data->working_description,
            $data->pic,
            $data->cost,
            $data->cost_formatted,
            $data->status,
            $data->maintenanceDocumentations
                ->map(fn ($doc) => $doc->url)
                ->toArray()
        );
    }
}