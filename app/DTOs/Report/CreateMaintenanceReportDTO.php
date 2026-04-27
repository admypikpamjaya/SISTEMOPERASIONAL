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
        $cost = array_key_exists('cost', $data) && $data['cost'] !== null && $data['cost'] !== ''
            ? (float) $data['cost']
            : 0.0;

        return new self(
            $data['asset_id'],
            $data['worker_name'],
            Carbon::parse($data['working_date']),
            $data['issue_description'],
            $data['working_description'],
            $data['pic'],
            $cost,
            $data['evidence_photo']
        );
    }
}
