<?php

namespace App\Http\Controllers\Finance;

use App\DTOs\Finance\StatementFilterDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\Finance\FinanceStatementFilterRequest;
use App\Services\Finance\FinancialStatementDocumentService;
use App\Services\Finance\FinancialStatementService;
use Carbon\Carbon;
use Throwable;

class FinanceStatementController extends Controller
{
    public function __construct(
        private FinancialStatementService $financialStatementService,
        private FinancialStatementDocumentService $financialStatementDocumentService
    ) {}

    public function balanceSheet(FinanceStatementFilterRequest $request)
    {
        try {
            $filter = StatementFilterDTO::fromArray($request->validated());

            return view('finance.balance-sheet', [
                'report' => $this->financialStatementService->getBalanceSheetReport($filter),
                'filters' => $this->buildFilterPayload($filter),
                'periodLabel' => $this->buildPeriodLabel($filter),
                'filterQuery' => $filter->toQueryArray(),
            ]);
        } catch (Throwable $exception) {
            report($exception);

            return redirect()
                ->route('finance.dashboard')
                ->with('error', 'Gagal memuat laporan lembar saldo.');
        }
    }

    public function profitLoss(FinanceStatementFilterRequest $request)
    {
        try {
            $filter = StatementFilterDTO::fromArray($request->validated());

            return view('finance.profit-loss-statement', [
                'report' => $this->financialStatementService->getProfitLossReport($filter),
                'filters' => $this->buildFilterPayload($filter),
                'periodLabel' => $this->buildPeriodLabel($filter),
                'filterQuery' => $filter->toQueryArray(),
            ]);
        } catch (Throwable $exception) {
            report($exception);

            return redirect()
                ->route('finance.dashboard')
                ->with('error', 'Gagal memuat laporan laba rugi.');
        }
    }

    public function generalLedger(FinanceStatementFilterRequest $request)
    {
        try {
            $filter = StatementFilterDTO::fromArray($request->validated());

            return view('finance.general-ledger', [
                'report' => $this->financialStatementService->getGeneralLedgerReport($filter),
                'filters' => $this->buildFilterPayload($filter),
                'periodLabel' => $this->buildPeriodLabel($filter),
                'filterQuery' => $filter->toQueryArray(),
            ]);
        } catch (Throwable $exception) {
            report($exception);

            return redirect()
                ->route('finance.dashboard')
                ->with('error', 'Gagal memuat buku besar.');
        }
    }

    public function downloadBalanceSheet(FinanceStatementFilterRequest $request)
    {
        return $this->downloadStatementDocument($request, 'balance_sheet');
    }

    public function downloadProfitLoss(FinanceStatementFilterRequest $request)
    {
        return $this->downloadStatementDocument($request, 'profit_loss');
    }

    public function downloadGeneralLedger(FinanceStatementFilterRequest $request)
    {
        return $this->downloadStatementDocument($request, 'general_ledger');
    }

    /**
     * @return array<string, int|string|null>
     */
    private function buildFilterPayload(StatementFilterDTO $filter): array
    {
        return [
            'period_type' => $filter->periodType ?? 'ALL',
            'report_date' => $filter->reportDate,
            'month' => $filter->month,
            'year' => $filter->year,
            'per_page' => $filter->perPage,
        ];
    }

    private function buildPeriodLabel(StatementFilterDTO $filter): string
    {
        $periodType = $filter->periodType ?? 'ALL';

        if ($periodType === 'DAILY' && !empty($filter->reportDate)) {
            return Carbon::parse($filter->reportDate)->translatedFormat('d F Y');
        }

        if ($periodType === 'MONTHLY' && $filter->year !== null && $filter->month !== null) {
            return Carbon::create($filter->year, $filter->month, 1)->translatedFormat('F Y');
        }

        if ($periodType === 'YEARLY' && $filter->year !== null) {
            return (string) $filter->year;
        }

        return 'Semua Periode';
    }

    private function downloadStatementDocument(FinanceStatementFilterRequest $request, string $statementType)
    {
        try {
            $filter = StatementFilterDTO::fromArray($request->validated());

            $exported = match ($statementType) {
                'balance_sheet' => $this->financialStatementDocumentService->exportBalanceSheet(
                    $this->financialStatementService->getBalanceSheetReport($filter),
                    $filter
                ),
                'profit_loss' => $this->financialStatementDocumentService->exportProfitLoss(
                    $this->financialStatementService->getProfitLossReport($filter),
                    $filter
                ),
                'general_ledger' => $this->financialStatementDocumentService->exportGeneralLedger(
                    $this->financialStatementService->getGeneralLedgerReport(
                        new StatementFilterDTO(
                            periodType: $filter->periodType,
                            reportDate: $filter->reportDate,
                            year: $filter->year,
                            month: $filter->month,
                            page: 1,
                            perPage: 5000
                        ),
                        false
                    ),
                    $filter
                ),
                default => null,
            };

            if ($exported === null) {
                return redirect()
                    ->route('finance.dashboard')
                    ->with('error', 'Format dokumen laporan tidak dikenali.');
            }

            return response($exported['content'], 200, [
                'Content-Type' => $exported['mime'],
                'Content-Disposition' => 'attachment; filename="' . $exported['filename'] . '"',
                'X-Content-Type-Options' => 'nosniff',
            ]);
        } catch (Throwable $exception) {
            report($exception);

            return redirect()
                ->back()
                ->with('error', 'Gagal mengunduh dokumen laporan finance.');
        }
    }
}
