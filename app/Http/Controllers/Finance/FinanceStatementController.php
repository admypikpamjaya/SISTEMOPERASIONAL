<?php

namespace App\Http\Controllers\Finance;

use App\DTOs\Finance\StatementFilterDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\Finance\FinanceStatementAccountMappingRequest;
use App\Http\Requests\Finance\FinanceStatementFilterRequest;
use App\Models\FinanceAccount;
use App\Models\FinanceAccountLog;
use App\Services\Finance\FinancialStatementDocumentService;
use App\Services\Finance\FinancialStatementSpreadsheetService;
use App\Services\Finance\FinancialStatementService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Schema;
use Throwable;

class FinanceStatementController extends Controller
{
    public function __construct(
        private FinancialStatementService $financialStatementService,
        private FinancialStatementDocumentService $financialStatementDocumentService,
        private FinancialStatementSpreadsheetService $financialStatementSpreadsheetService
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
                'baseFilterQuery' => $this->buildBaseFilterQuery($filter),
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
                'baseFilterQuery' => $this->buildBaseFilterQuery($filter),
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
                'baseFilterQuery' => $this->buildBaseFilterQuery($filter),
                'selectedAccountCode' => $filter->accountCode,
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

    public function journalItems(FinanceStatementFilterRequest $request)
    {
        try {
            $filter = StatementFilterDTO::fromArray($request->validated());

            return view('finance.journal-items', [
                'report' => $this->financialStatementService->getJournalItemsReport($filter),
                'filters' => $this->buildFilterPayload($filter),
                'periodLabel' => $this->buildPeriodLabel($filter),
                'filterQuery' => $filter->toQueryArray(),
                'baseFilterQuery' => $this->buildBaseFilterQuery($filter),
                'selectedAccountCode' => $filter->accountCode,
                'statementSource' => $filter->statementSource,
                'statementSourceLabel' => $this->resolveStatementSourceLabel($filter->statementSource),
                'statementBackUrl' => route(
                    $this->resolveStatementSourceRoute($filter->statementSource),
                    $this->buildBaseFilterQuery($filter)
                ),
            ]);
        } catch (Throwable $exception) {
            report($exception);

            return redirect()
                ->route('finance.dashboard')
                ->with('error', 'Gagal memuat item jurnal laporan finance.');
        }
    }

    public function downloadJournalItems(FinanceStatementFilterRequest $request)
    {
        return $this->downloadStatementDocument($request, 'journal_items');
    }

    public function saveAccountMapping(FinanceStatementAccountMappingRequest $request)
    {
        try {
            $validated = $request->validated();
            $actorId = auth()->id() ? (string) auth()->id() : null;
            $type = (string) $validated['statement_type'];
            $classNo = FinanceAccount::classForType($type);

            $account = FinanceAccount::query()
                ->where('code', (string) $validated['account_code'])
                ->first();

            $beforeData = $account?->exists ? $this->serializeAccount($account) : null;

            if ($account === null) {
                $account = FinanceAccount::query()->create([
                    'code' => (string) $validated['account_code'],
                    'name' => (string) ($validated['account_name'] !== '' ? $validated['account_name'] : $validated['account_code']),
                    'type' => $type,
                    'class_no' => $classNo,
                    'is_active' => true,
                    'created_by' => $actorId,
                    'updated_by' => $actorId,
                ]);

                $this->writeAccountLog(
                    account: $account,
                    action: FinanceAccountLog::ACTION_CREATED,
                    actorId: $actorId,
                    beforeData: null,
                    afterData: $this->serializeAccount($account)
                );
            } else {
                $account->update([
                    'name' => (string) ($validated['account_name'] !== '' ? $validated['account_name'] : $account->name),
                    'type' => $type,
                    'class_no' => $classNo,
                    'is_active' => true,
                    'updated_by' => $actorId,
                ]);

                $account->refresh();

                $this->writeAccountLog(
                    account: $account,
                    action: FinanceAccountLog::ACTION_UPDATED,
                    actorId: $actorId,
                    beforeData: $beforeData,
                    afterData: $this->serializeAccount($account)
                );
            }

            $targetLabel = FinanceAccount::TYPE_LABELS[$type] ?? $type;

            return redirect()
                ->back()
                ->with('success', 'Akun ' . $account->code . ' berhasil dipetakan ke kategori ' . $targetLabel . '.');
        } catch (Throwable $exception) {
            report($exception);

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Gagal menyimpan pemetaan akun laporan.');
        }
    }

    /**
     * @return array<string, int|string|null>
     */
    private function buildFilterPayload(StatementFilterDTO $filter): array
    {
        return [
            'period_type' => $filter->periodType ?? 'ALL',
            'start_date' => $filter->startDate,
            'end_date' => $filter->endDate,
            'start_month' => $filter->startMonth,
            'end_month' => $filter->endMonth,
            'start_year' => $filter->startYear,
            'end_year' => $filter->endYear,
            'report_date' => $filter->reportDate,
            'month' => $filter->month,
            'year' => $filter->year,
            'account_code' => $filter->accountCode,
            'search' => $filter->search,
            'statement_source' => $filter->statementSource,
            'per_page' => $filter->perPage,
        ];
    }

    /**
     * @return array<string, int|string>
     */
    private function buildBaseFilterQuery(StatementFilterDTO $filter): array
    {
        return array_filter([
            'period_type' => $filter->periodType ?? 'ALL',
            'start_date' => $filter->startDate,
            'end_date' => $filter->endDate,
            'start_month' => $filter->startMonth,
            'end_month' => $filter->endMonth,
            'start_year' => $filter->startYear,
            'end_year' => $filter->endYear,
            'report_date' => $filter->reportDate,
            'month' => $filter->month,
            'year' => $filter->year,
            'per_page' => $filter->perPage,
        ], static fn ($value): bool => $value !== null && $value !== '');
    }

    private function buildPeriodLabel(StatementFilterDTO $filter): string
    {
        $periodType = $filter->periodType ?? 'ALL';

        if ($periodType === 'DAILY' && !empty($filter->startDate)) {
            $startDate = Carbon::parse($filter->startDate);
            $endDate = !empty($filter->endDate)
                ? Carbon::parse($filter->endDate)
                : $startDate->copy();

            if ($startDate->equalTo($endDate)) {
                return $startDate->translatedFormat('d F Y');
            }

            return $startDate->translatedFormat('d F Y') . ' s.d. ' . $endDate->translatedFormat('d F Y');
        }

        if (
            $periodType === 'MONTHLY'
            && $filter->startYear !== null
            && $filter->startMonth !== null
        ) {
            $startMonth = Carbon::create($filter->startYear, $filter->startMonth, 1);
            $endMonth = Carbon::create(
                $filter->endYear ?? $filter->startYear,
                $filter->endMonth ?? $filter->startMonth,
                1
            );

            if ($startMonth->equalTo($endMonth)) {
                return $startMonth->translatedFormat('F Y');
            }

            return $startMonth->translatedFormat('F Y') . ' s.d. ' . $endMonth->translatedFormat('F Y');
        }

        if ($periodType === 'YEARLY' && $filter->startYear !== null) {
            if (($filter->endYear ?? $filter->startYear) === $filter->startYear) {
                return (string) $filter->startYear;
            }

            return $filter->startYear . ' s.d. ' . ($filter->endYear ?? $filter->startYear);
        }

        return 'Semua Periode';
    }

    private function downloadStatementDocument(FinanceStatementFilterRequest $request, string $statementType)
    {
        try {
            $filter = StatementFilterDTO::fromArray($request->validated());
            $format = strtolower((string) $request->query('format', 'pdf'));
            if ($format === 'xlsx') {
                $format = 'excel';
            }

            if (!in_array($format, ['pdf', 'excel'], true)) {
                return redirect()
                    ->back()
                    ->with('error', 'Format dokumen laporan tidak dikenali.');
            }

            $exportFilter = new StatementFilterDTO(
                periodType: $filter->periodType,
                reportDate: $filter->reportDate,
                year: $filter->year,
                month: $filter->month,
                startDate: $filter->startDate,
                endDate: $filter->endDate,
                startMonth: $filter->startMonth,
                endMonth: $filter->endMonth,
                startYear: $filter->startYear,
                endYear: $filter->endYear,
                accountCode: $filter->accountCode,
                search: $filter->search,
                statementSource: $filter->statementSource,
                selectedIds: $filter->selectedIds,
                page: 1,
                perPage: 5000
            );

            $exported = match ($statementType) {
                'balance_sheet' => $format === 'excel'
                    ? $this->financialStatementSpreadsheetService->exportBalanceSheet(
                        $this->financialStatementService->getBalanceSheetReport($exportFilter),
                        $exportFilter
                    )
                    : $this->financialStatementDocumentService->exportBalanceSheet(
                        $this->financialStatementService->getBalanceSheetReport($exportFilter),
                        $exportFilter
                    ),
                'profit_loss' => $format === 'excel'
                    ? $this->financialStatementSpreadsheetService->exportProfitLoss(
                        $this->financialStatementService->getProfitLossReport($exportFilter),
                        $exportFilter
                    )
                    : $this->financialStatementDocumentService->exportProfitLoss(
                        $this->financialStatementService->getProfitLossReport($exportFilter),
                        $exportFilter
                    ),
                'general_ledger' => $format === 'excel'
                    ? $this->financialStatementSpreadsheetService->exportGeneralLedger(
                        $this->financialStatementService->getGeneralLedgerReport($exportFilter, false),
                        $exportFilter
                    )
                    : $this->financialStatementDocumentService->exportGeneralLedger(
                        $this->financialStatementService->getGeneralLedgerReport($exportFilter, false),
                        $exportFilter
                    ),
                'journal_items' => $format === 'excel'
                    ? $this->financialStatementSpreadsheetService->exportJournalItems(
                        $this->financialStatementService->getJournalItemsReport($exportFilter, false),
                        $exportFilter
                    )
                    : $this->financialStatementDocumentService->exportJournalItems(
                        $this->financialStatementService->getJournalItemsReport($exportFilter, false),
                        $exportFilter
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

    private function resolveStatementSourceLabel(?string $statementSource): string
    {
        return match ($statementSource) {
            'balance_sheet' => 'Lembar Saldo',
            'profit_loss' => 'Laba Rugi',
            'general_ledger' => 'Buku Besar',
            default => 'Laporan Finance',
        };
    }

    private function resolveStatementSourceRoute(?string $statementSource): string
    {
        return match ($statementSource) {
            'balance_sheet' => 'finance.report.balance-sheet',
            'profit_loss' => 'finance.report.profit-loss',
            default => 'finance.report.general-ledger',
        };
    }

    private function writeAccountLog(
        FinanceAccount $account,
        string $action,
        ?string $actorId,
        ?array $beforeData,
        ?array $afterData
    ): void {
        if (!Schema::hasTable('finance_account_logs')) {
            return;
        }

        FinanceAccountLog::query()->create([
            'finance_account_id' => (string) $account->id,
            'action' => $action,
            'before_data' => $beforeData,
            'after_data' => $afterData,
            'actor_id' => $actorId,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function serializeAccount(FinanceAccount $account): array
    {
        return [
            'id' => (string) $account->id,
            'code' => (string) $account->code,
            'name' => (string) $account->name,
            'type' => (string) $account->type,
            'type_label' => (string) $account->type_label,
            'class_no' => (int) $account->class_no,
            'is_active' => (bool) $account->is_active,
        ];
    }
}
