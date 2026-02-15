<?php

namespace App\DTOs\Finance;

use App\Models\FinanceReport;
use Carbon\Carbon;

class FinanceReportSnapshotDTO
{
    public function __construct(
        public string $id,
        public string $periodId,
        public string $reportType,
        public int $versionNo,
        public array $summary,
        public ?string $generatedBy,
        public Carbon $generatedAt,
        public bool $isReadOnly
    ) {}

    public static function fromModel(FinanceReport $report): self
    {
        return new self(
            $report->id,
            $report->period_id,
            $report->report_type,
            (int) $report->version_no,
            (array) $report->summary,
            $report->generated_by,
            Carbon::parse($report->generated_at),
            (bool) $report->is_read_only
        );
    }
}
