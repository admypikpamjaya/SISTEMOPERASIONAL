<?php

namespace App\DTOs\Finance;

class GenerateFinanceReportDTO
{
    public function __construct(
        public string $periodId,
        public string $reportType,
        public string $reconciliationSnapshotId,
        public ReportSummaryDTO $summary,
        public ?string $generatedBy = null,
        public bool $isReadOnly = true
    ) {}
}
