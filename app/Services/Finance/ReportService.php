<?php

namespace App\Services\Finance;

use App\DTOs\Finance\FinanceSnapshotFilterDTO;
use App\DTOs\Finance\GenerateProfitLossReportDTO;
use App\DTOs\Finance\FinanceReportSnapshotDTO;
use App\DTOs\Finance\GenerateFinanceReportDTO;
use App\DTOs\Finance\ProfitLossLineDTO;
use App\DTOs\Finance\ProfitLossReportDetailDTO;
use App\DTOs\Finance\ReportSummaryDTO;
use App\Models\FinanceReport;
use App\Models\FinancePeriod;
use App\Repositories\AuditLogRepository;
use App\Repositories\Finance\FinanceReportRepository;
use App\Repositories\Finance\FinanceDepreciationRunRepository;
use App\Repositories\Finance\FinancePeriodRepository;
use App\Repositories\Finance\FinanceReconciliationSnapshotRepository;
use App\Repositories\Finance\FinanceReportItemRepository;
use Carbon\Carbon;
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
        private ReconciliationService $reconciliationService,
        private AuditLogRepository $auditLogRepository
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
        ?int $year = null,
        ?int $month = null,
        ?string $periodType = null,
        ?string $reportDate = null,
        int $page = 1,
        int $perPage = 20
    ) {
        return $this->financeReportRepository->paginateByFilters(
            periodType: $periodType ? strtoupper($periodType) : null,
            reportDate: $reportDate,
            year: $year,
            month: $month,
            readOnlyOnly: true,
            page: $page,
            perPage: $perPage
        );
    }

    public function getSuggestedOpeningBalance(
        string $reportType,
        int $year,
        ?int $month,
        ?int $day = null
    ): float
    {
        $normalizedType = strtoupper($reportType);
        $periodMonth = $normalizedType === 'YEARLY' ? 0 : (int) $month;
        $periodDay = $normalizedType === 'DAILY' ? (int) ($day ?? 1) : 0;

        $latestClosingBalance = $this->financePeriodRepository->getLatestClosingBalanceBefore(
            $normalizedType,
            $year,
            $periodMonth,
            $periodDay
        );

        return $latestClosingBalance ?? 0.0;
    }

    /**
     * @return array{
     *   reports: \Illuminate\Contracts\Pagination\LengthAwarePaginator,
     *   comparisons: array<string, array<string, mixed>|null>,
     *   totals: array{count:int,total_opening_balance:float,total_ending_balance:float,total_net_result:float}
     * }
     */
    public function getSnapshots(FinanceSnapshotFilterDTO $filter): array
    {
        $reports = $this->financeReportRepository->paginateByFilters(
            periodType: $filter->periodType,
            reportDate: $filter->reportDate,
            year: $filter->year,
            month: $filter->month,
            readOnlyOnly: true,
            page: $filter->page,
            perPage: $filter->perPage
        );

        $comparisons = [];
        $allowComparison = $filter->comparisonType !== 'NONE'
            && !empty($filter->periodType);

        foreach ($reports->items() as $report) {
            $comparisons[$report->id] = $allowComparison
                ? $this->buildComparisonData($report, $filter)
                : null;
        }

        $allReports = $this->financeReportRepository->getByFilters(
            periodType: $filter->periodType,
            reportDate: $filter->reportDate,
            year: $filter->year,
            month: $filter->month,
            readOnlyOnly: true
        );

        $totals = [
            'count' => $allReports->count(),
            'total_opening_balance' => round($allReports->sum(
                static fn (FinanceReport $report): float => (float) data_get($report->summary, 'opening_balance', 0)
            ), 2),
            'total_ending_balance' => round($allReports->sum(
                static fn (FinanceReport $report): float => (float) data_get($report->summary, 'ending_balance', 0)
            ), 2),
            'total_net_result' => round($allReports->sum(
                static fn (FinanceReport $report): float => (float) data_get($report->summary, 'net_result', 0)
            ), 2),
        ];

        return [
            'reports' => $reports,
            'comparisons' => $comparisons,
            'totals' => $totals,
        ];
    }

    public function createProfitLossReport(GenerateProfitLossReportDTO $dto): FinanceReportSnapshotDTO
    {
        $period = $this->resolveOrCreatePeriod(
            $dto->reportType,
            $dto->year,
            $dto->month,
            $dto->day,
            $dto->openingBalance
        );

        $periodOpeningBalance = (float) ($period->opening_balance ?? 0);
        $openingBalance = round($dto->openingBalance, 2);
        if ($openingBalance == 0.0 && $periodOpeningBalance > 0) {
            $openingBalance = $periodOpeningBalance;
        }

        $totalIncome = 0.0;
        $totalExpense = 0.0;
        $totalDepreciation = 0.0;

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
        $endingBalance = round($openingBalance + $totalIncome - $totalExpense, 2);

        return DB::transaction(function () use ($dto, $period, $reconciliation, $openingBalance, $endingBalance) {
            $this->financePeriodRepository->updateBalances(
                $period->id,
                $openingBalance,
                $endingBalance
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
                day: (int) ($period->day ?? 0),
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
            $runningBalance = $openingBalance;
            foreach ($dto->entries as $index => $entry) {
                $balanceBefore = $runningBalance;
                if ($entry->type === 'INCOME') {
                    $runningBalance += $entry->amount;
                } elseif (!$entry->isDepreciation) {
                    $runningBalance -= $entry->amount;
                }

                $rows[] = [
                    'report_snapshot_id' => $reportSnapshot->id,
                    'line_code' => $entry->lineCode,
                    'line_label' => $entry->lineLabel,
                    'amount' => $entry->amount,
                    'sort_order' => $index + 1,
                    'meta' => json_encode([
                        'type' => $entry->type,
                        'is_depreciation' => $entry->isDepreciation,
                        'invoice_number' => $entry->invoiceNumber,
                        'description' => $entry->description,
                        'balance_before' => round($balanceBefore, 2),
                        'balance_after' => round($runningBalance, 2),
                    ], JSON_THROW_ON_ERROR),
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            if (!empty($rows)) {
                $this->financeReportItemRepository->createMany($rows);
            }

            if (!empty($dto->generatedBy)) {
                $this->writeGenerateReportAuditLog($reportSnapshot->id, $period->id, $dto);
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
            $invoiceNumber = data_get($item->meta, 'invoice_number');
            $line = new ProfitLossLineDTO(
                lineCode: (string) $item->line_code,
                lineLabel: (string) $item->line_label,
                description: $description !== null ? (string) $description : null,
                invoiceNumber: $invoiceNumber !== null ? (string) $invoiceNumber : null,
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
        $day = (int) data_get($report->summary, 'day', (int) ($report->period?->day ?? 0));
        $openingBalance = (float) data_get(
            $report->summary,
            'opening_balance',
            (float) ($report->period?->opening_balance ?? 0)
        );
        $surplusDeficit = (float) data_get($report->summary, 'net_result', 0);
        $endingBalance = (float) data_get(
            $report->summary,
            'ending_balance',
            (float) ($report->period?->closing_balance ?? ($openingBalance + $surplusDeficit))
        );

        return new ProfitLossReportDetailDTO(
            reportId: $report->id,
            reportType: (string) $report->report_type,
            year: (int) data_get($report->summary, 'year', (int) ($report->period?->year ?? now()->year)),
            month: $month > 0 ? $month : null,
            day: $day > 0 ? $day : null,
            periodDate: $report->period?->start_date?->format('Y-m-d'),
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

    /**
     * @return array<string, mixed>|null
     */
    private function buildComparisonData(FinanceReport $baseReport, FinanceSnapshotFilterDTO $filter): ?array
    {
        if ($filter->comparisonType === 'NONE') {
            return null;
        }

        $basePeriod = $baseReport->period;
        if ($basePeriod === null) {
            return null;
        }

        $target = $this->resolveComparisonTarget($basePeriod, $filter);
        if ($target === null) {
            return null;
        }

        $comparisonReport = $this->financeReportRepository->findLatestByPeriodKey(
            $target['period_type'],
            $target['year'],
            $target['month'],
            $target['day']
        );

        $baseNet = (float) data_get($baseReport->summary, 'net_result', 0);
        $baseEnding = (float) data_get($baseReport->summary, 'ending_balance', 0);

        if ($comparisonReport === null) {
            return [
                'label' => $target['label'],
                'available' => false,
                'message' => 'Data pembanding tidak ditemukan.',
            ];
        }

        $comparisonNet = (float) data_get($comparisonReport->summary, 'net_result', 0);
        $comparisonEnding = (float) data_get($comparisonReport->summary, 'ending_balance', 0);

        return [
            'label' => $target['label'],
            'available' => true,
            'comparison_report_id' => $comparisonReport->id,
            'comparison_net_result' => $comparisonNet,
            'comparison_ending_balance' => $comparisonEnding,
            'difference_net_result' => round($baseNet - $comparisonNet, 2),
            'difference_ending_balance' => round($baseEnding - $comparisonEnding, 2),
        ];
    }

    /**
     * @return array{period_type:string,year:int,month:int,day:int,label:string}|null
     */
    private function resolveComparisonTarget(FinancePeriod $basePeriod, FinanceSnapshotFilterDTO $filter): ?array
    {
        $type = strtoupper((string) $basePeriod->period_type);
        $offset = max(1, $filter->comparisonOffset);

        if ($filter->comparisonType === 'PREVIOUS_PERIOD') {
            if ($type === 'DAILY') {
                $date = Carbon::parse($basePeriod->start_date)->subDays($offset);
                return [
                    'period_type' => 'DAILY',
                    'year' => (int) $date->year,
                    'month' => (int) $date->month,
                    'day' => (int) $date->day,
                    'label' => 'Periode Sebelumnya (' . $offset . ')',
                ];
            }

            if ($type === 'MONTHLY') {
                $date = Carbon::create((int) $basePeriod->year, (int) $basePeriod->month, 1)->subMonthsNoOverflow($offset);
                return [
                    'period_type' => 'MONTHLY',
                    'year' => (int) $date->year,
                    'month' => (int) $date->month,
                    'day' => 0,
                    'label' => 'Periode Sebelumnya (' . $offset . ')',
                ];
            }

            $date = Carbon::create((int) $basePeriod->year, 1, 1)->subYearsNoOverflow($offset);
            return [
                'period_type' => 'YEARLY',
                'year' => (int) $date->year,
                'month' => 0,
                'day' => 0,
                'label' => 'Periode Sebelumnya (' . $offset . ')',
            ];
        }

        if ($filter->comparisonType === 'SAME_PERIOD_LAST_YEAR') {
            if ($type === 'DAILY') {
                $date = Carbon::parse($basePeriod->start_date)->subYearNoOverflow();
                return [
                    'period_type' => 'DAILY',
                    'year' => (int) $date->year,
                    'month' => (int) $date->month,
                    'day' => (int) $date->day,
                    'label' => 'Periode Sama Tahun Lalu',
                ];
            }

            if ($type === 'MONTHLY') {
                return [
                    'period_type' => 'MONTHLY',
                    'year' => (int) $basePeriod->year - 1,
                    'month' => (int) $basePeriod->month,
                    'day' => 0,
                    'label' => 'Periode Sama Tahun Lalu',
                ];
            }

            return [
                'period_type' => 'YEARLY',
                'year' => (int) $basePeriod->year - 1,
                'month' => 0,
                'day' => 0,
                'label' => 'Periode Sama Tahun Lalu',
            ];
        }

        if ($filter->comparisonType === 'SPECIFIC_DATE') {
            if (empty($filter->comparisonDate)) {
                return null;
            }

            $date = Carbon::parse($filter->comparisonDate);
            if ($type === 'DAILY') {
                return [
                    'period_type' => 'DAILY',
                    'year' => (int) $date->year,
                    'month' => (int) $date->month,
                    'day' => (int) $date->day,
                    'label' => 'Tanggal Pembanding',
                ];
            }

            if ($type === 'MONTHLY') {
                return [
                    'period_type' => 'MONTHLY',
                    'year' => (int) $date->year,
                    'month' => (int) $date->month,
                    'day' => 0,
                    'label' => 'Bulan Pembanding',
                ];
            }

            return [
                'period_type' => 'YEARLY',
                'year' => (int) $date->year,
                'month' => 0,
                'day' => 0,
                'label' => 'Tahun Pembanding',
            ];
        }

        return null;
    }

    private function resolveOrCreatePeriod(
        string $reportType,
        int $year,
        ?int $month,
        ?int $day,
        float $openingBalance = 0.0
    ): FinancePeriod
    {
        $normalizedType = strtoupper($reportType);

        if ($normalizedType === 'MONTHLY' && ($month === null || $month < 1 || $month > 12)) {
            throw new RuntimeException('Bulan wajib diisi untuk laporan bulanan.');
        }

        if ($normalizedType === 'DAILY' && (
            $month === null || $month < 1 || $month > 12 || $day === null || $day < 1 || $day > 31
        )) {
            throw new RuntimeException('Tanggal wajib diisi untuk laporan harian.');
        }

        $periodMonth = $normalizedType === 'YEARLY' ? 0 : (int) $month;
        $periodDay = $normalizedType === 'DAILY' ? (int) $day : 0;

        $existing = $this->financePeriodRepository->findByTypeYearMonthDay(
            $normalizedType,
            $year,
            $periodMonth,
            $periodDay
        );

        if ($existing !== null) {
            return $existing;
        }

        if ($normalizedType === 'YEARLY') {
            $startDate = Carbon::create($year, 1, 1)->startOfDay();
            $endDate = Carbon::create($year, 12, 31)->endOfDay();
        } elseif ($normalizedType === 'DAILY') {
            $startDate = Carbon::create($year, $periodMonth, $periodDay)->startOfDay();
            $endDate = $startDate->copy()->endOfDay();
        } else {
            $startDate = Carbon::create($year, (int) $periodMonth, 1)->startOfDay();
            $endDate = $startDate->copy()->endOfMonth()->endOfDay();
        }

        return $this->financePeriodRepository->create(
            $normalizedType,
            $year,
            $periodMonth,
            $periodDay,
            $startDate,
            $endDate,
            $openingBalance,
            $openingBalance
        );
    }

    private function writeGenerateReportAuditLog(
        string $reportSnapshotId,
        string $periodId,
        GenerateProfitLossReportDTO $dto
    ): void {
        try {
            $this->auditLogRepository->create([
                'user_id' => $dto->generatedBy,
                'action' => 'finance_report.generate',
                'entity' => 'finance_report_snapshot',
                'entity_id' => 0,
                'payload' => [
                    'report_snapshot_id' => $reportSnapshotId,
                    'period_id' => $periodId,
                    'report_type' => $dto->reportType,
                    'year' => $dto->year,
                    'month' => $dto->month,
                    'day' => $dto->day,
                    'opening_balance' => $dto->openingBalance,
                    'entries_count' => count($dto->entries),
                ],
            ]);
        } catch (\Throwable $exception) {
            report($exception);
        }
    }
}
