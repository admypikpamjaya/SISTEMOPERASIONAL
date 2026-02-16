<?php

namespace App\Services\Finance;

use App\DTOs\Finance\GenerateProfitLossReportDTO;
use App\DTOs\Finance\FinanceReportSnapshotDTO;
use App\DTOs\Finance\GenerateFinanceReportDTO;
use App\DTOs\Finance\ProfitLossLineDTO;
use App\DTOs\Finance\ProfitLossReportDetailDTO;
use App\DTOs\Finance\ReportSummaryDTO;
use App\Models\FinancePeriod;
use App\Repositories\Finance\FinanceReportRepository;
use App\Repositories\Finance\FinanceDepreciationRunRepository;
use App\Repositories\Finance\FinancePeriodRepository;
use App\Repositories\Finance\FinanceReconciliationSnapshotRepository;
use App\Repositories\Finance\FinanceReportItemRepository;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class ReportService
{
    public function __construct(
        private FinanceReportRepository $financeReportRepository,
        private FinancePeriodRepository $financePeriodRepository,
        private FinanceDepreciationRunRepository $financeDepreciationRunRepository,
        private FinanceReconciliationSnapshotRepository $financeReconciliationSnapshotRepository,
        private FinanceReportItemRepository $financeReportItemRepository,
        private ReconciliationService $reconciliationService
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

    public function getReports(
        int $year,
        ?int $month = null,
        ?string $reportType = null,
        int $page = 1,
        int $perPage = 20
    ): LengthAwarePaginator {
        $normalizedType = $reportType ? strtoupper($reportType) : null;

        return $this->financeReportRepository->paginateByYearAndMonth(
            $year,
            $month,
            $normalizedType,
            true,
            $page,
            $perPage
        );
    }

    public function createProfitLossReport(GenerateProfitLossReportDTO $dto): FinanceReportSnapshotDTO
    {
        $totalIncome = 0.0;
        $totalExpense = 0.0;
        $totalDepreciation = 0.0;
        $openingBalance = $dto->openingBalance;

        foreach ($dto->entries as $entry) {
            if ($entry->type === 'INCOME') {
                $totalIncome += $entry->amount;
                continue;
            }

            if ($entry->isDepreciation) {
                $totalDepreciation += $entry->amount;
                continue;
            }

            $totalExpense += $entry->amount;
        }

        $reconciliation = $this->reconciliationService->calculate(
            $totalIncome,
            $totalExpense,
            $totalDepreciation
        );
        $endingBalance = round($openingBalance + $reconciliation->netResult, 2);

        return DB::transaction(function () use ($dto, $reconciliation, $openingBalance, $endingBalance) {
            $period = $this->resolveOrCreatePeriod(
                $dto->reportType,
                $dto->year,
                $dto->month
            );

            $runNo = $this->financeDepreciationRunRepository->getNextRunNumber($period->id);
            $depreciationRun = $this->financeDepreciationRunRepository->create([
                'period_id' => $period->id,
                'run_no' => $runNo,
                'status' => 'POSTED',
                'assets_count' => 0,
                'total_depreciation' => $reconciliation->totalDepreciation,
                'generated_by' => $dto->generatedBy,
                'generated_at' => now(),
                'notes' => 'Generated from manual profit-loss input.',
            ]);

            $reconciliationSnapshot = $this->financeReconciliationSnapshotRepository->create([
                'period_id' => $period->id,
                'depreciation_run_id' => $depreciationRun->id,
                'income_total' => $reconciliation->totalIncome,
                'expense_total' => $reconciliation->totalExpense,
                'depreciation_total' => $reconciliation->totalDepreciation,
                'net_result' => $reconciliation->netResult,
                'generated_by' => $dto->generatedBy,
                'generated_at' => now(),
                'notes' => 'Generated from manual profit-loss input.',
            ]);

            $summary = new ReportSummaryDTO(
                month: (int) $period->month,
                year: (int) $period->year,
                totalIncome: $reconciliation->totalIncome,
                totalExpense: $reconciliation->totalExpense,
                totalDepreciation: $reconciliation->totalDepreciation,
                netResult: $reconciliation->netResult,
                openingBalance: $openingBalance,
                endingBalance: $endingBalance
            );

            $reportSnapshot = $this->generateSnapshot(new GenerateFinanceReportDTO(
                periodId: $period->id,
                reportType: $dto->reportType,
                reconciliationSnapshotId: $reconciliationSnapshot->id,
                summary: $summary,
                generatedBy: $dto->generatedBy,
                isReadOnly: true
            ));

            $now = now();
            $rows = [];
            foreach ($dto->entries as $index => $entry) {
                $rows[] = [
                    'report_snapshot_id' => $reportSnapshot->id,
                    'line_code' => $entry->lineCode,
                    'line_label' => $entry->lineLabel,
                    'amount' => $entry->amount,
                    'sort_order' => $index + 1,
                    'meta' => json_encode([
                        'type' => $entry->type,
                        'is_depreciation' => $entry->isDepreciation,
                        'description' => $entry->description,
                    ], JSON_THROW_ON_ERROR),
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            if (!empty($rows)) {
                $this->financeReportItemRepository->createMany($rows);
            }

            return $reportSnapshot;
        });
    }

    public function getProfitLossReportDetail(string $reportId): ProfitLossReportDetailDTO
    {
        $report = $this->financeReportRepository->getByIdWithItems($reportId);
        if ($report === null) {
            throw new RuntimeException('Laporan finance tidak ditemukan.');
        }

        $incomeLines = [];
        $expenseLines = [];
        $depreciationLines = [];

        foreach ($report->items as $item) {
            $type = strtoupper((string) data_get($item->meta, 'type', 'EXPENSE'));
            $description = data_get($item->meta, 'description');
            $line = new ProfitLossLineDTO(
                lineCode: (string) $item->line_code,
                lineLabel: (string) $item->line_label,
                description: $description !== null ? (string) $description : null,
                amount: (float) $item->amount,
                isDepreciation: (bool) data_get($item->meta, 'is_depreciation', false)
            );

            if ($type === 'INCOME') {
                $incomeLines[] = $line;
            } elseif ($line->isDepreciation) {
                $depreciationLines[] = $line;
            } else {
                $expenseLines[] = $line;
            }
        }

        $month = (int) data_get($report->summary, 'month', (int) ($report->period?->month ?? 0));
        $openingBalance = (float) data_get($report->summary, 'opening_balance', 0);
        $surplusDeficit = (float) data_get($report->summary, 'net_result', 0);
        $endingBalance = (float) data_get($report->summary, 'ending_balance', $openingBalance + $surplusDeficit);

        return new ProfitLossReportDetailDTO(
            reportId: $report->id,
            reportType: (string) $report->report_type,
            year: (int) data_get($report->summary, 'year', (int) ($report->period?->year ?? now()->year)),
            month: $month > 0 ? $month : null,
            openingBalance: $openingBalance,
            endingBalance: $endingBalance,
            generatedAt: Carbon::parse($report->generated_at),
            generatedByName: $report->user?->name,
            incomeLines: $incomeLines,
            expenseLines: $expenseLines,
            depreciationLines: $depreciationLines,
            totalIncome: (float) data_get($report->summary, 'total_income', 0),
            totalExpense: (float) data_get($report->summary, 'total_expense', 0),
            totalDepreciation: (float) data_get($report->summary, 'total_depreciation', 0),
            surplusDeficit: $surplusDeficit
        );
    }

    private function resolveOrCreatePeriod(string $reportType, int $year, ?int $month): FinancePeriod
    {
        $normalizedType = strtoupper($reportType);

        if ($normalizedType === 'MONTHLY' && ($month === null || $month < 1 || $month > 12)) {
            throw new RuntimeException('Bulan wajib diisi untuk laporan bulanan.');
        }

        $periodMonth = $normalizedType === 'YEARLY' ? 0 : (int) $month;

        $existing = $this->financePeriodRepository->findByTypeYearMonth(
            $normalizedType,
            $year,
            $periodMonth
        );

        if ($existing !== null) {
            return $existing;
        }

        if ($normalizedType === 'YEARLY') {
            $startDate = Carbon::create($year, 1, 1)->startOfDay();
            $endDate = Carbon::create($year, 12, 31)->endOfDay();
        } else {
            $startDate = Carbon::create($year, (int) $periodMonth, 1)->startOfDay();
            $endDate = $startDate->copy()->endOfMonth()->endOfDay();
        }

        return $this->financePeriodRepository->create(
            $normalizedType,
            $year,
            $periodMonth,
            $startDate,
            $endDate
        );
    }
}
