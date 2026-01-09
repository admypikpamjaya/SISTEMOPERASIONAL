<?php 

namespace App\DTOs\Report;

use Carbon\Carbon;

class UpdateMaintenanceReportDTO
{
    public function __construct(
        public string $id,
        public string $workerName,
        public Carbon $workingDate,
        public string $issueDescription,
        public string $workingDescription
    ) {}

    public static function fromArray(array $data): self 
    {
        return new self(
            $data['id'],
            $data['worker_name'],
            Carbon::parse($data['working_date']),
            $data['issue_description'],
            $data['working_description']
        );
    }
}