<?php

namespace App\Services\Finance;

use App\DTOs\Finance\FinanceReportSnapshotDTO;
use App\DTOs\Finance\GenerateFinanceReportDTO;
use App\Repositories\Finance\FinanceReportRepository;

class ReportService
{
    public function __construct(
        private FinanceReportRepository $financeReportRepository
    ) {}

    public function generateSnapshot(GenerateFinanceReportDTO $dto): FinanceReportSnapshotDTO
    {
        $reportType = strtoupper($dto->reportType);
        $latestVersion = $this->financeReportRepository->getLatestVersion(
            $dto->periodId,
            $reportType
        );

        $nextVersion = ($latestVersion?->version_no ?? 0) + 1;
        $summary = $dto->summary->toArray();

        $report = $this->financeReportRepository->create([
            'period_id' => $dto->periodId,
            'report_type' => $reportType,
            'version_no' => $nextVersion,
            'reconciliation_snapshot_id' => $dto->reconciliationSnapshotId,
            'summary' => $summary,
            'generated_by' => $dto->generatedBy,
            'generated_at' => now(),
            'is_read_only' => $dto->isReadOnly,
        ]);

        return FinanceReportSnapshotDTO::fromModel($report);
    }
}
