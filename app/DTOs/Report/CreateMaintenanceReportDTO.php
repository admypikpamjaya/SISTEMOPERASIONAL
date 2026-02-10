<?php 

namespace App\DTOs\Report;

use Carbon\Carbon;
use Illuminate\Http\UploadedFile;

class CreateMaintenanceReportDTO
{
    public function __construct(
        public string $assetId,
        public string $workerName,
        public Carbon $workingDate,
        public string $issueDescription,
        public string $workingDescription,
        public string $pic,
        public float $cost,
        public UploadedFile $evidencePhoto
    ) {}

    public static function fromArray(array $data): self 
    {
        return new self(
            $data['asset_id'],
            $data['worker_name'],
            Carbon::parse($data['working_date']),
            $data['issue_description'],
            $data['working_description'],
            $data['pic'],
            $data['cost'],
            $data['evidence_photo']
        );
    }
}