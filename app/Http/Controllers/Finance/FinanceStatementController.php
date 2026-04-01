<?php

namespace App\Http\Controllers\Finance;

use App\DTOs\Finance\StatementFilterDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\Finance\FinanceGeneralLedgerEntryStoreRequest;
use App\Http\Requests\Finance\FinanceGeneralLedgerEntryUpdateRequest;
use App\Http\Requests\Finance\FinanceGeneralLedgerImportRequest;
use App\Http\Requests\Finance\FinanceStatementAccountMappingRequest;
use App\Http\Requests\Finance\FinanceStatementFilterRequest;
use App\Http\Requests\Finance\FinanceStatementImportRequest;
use App\Http\Requests\Finance\FinanceStatementRowStoreRequest;
use App\Http\Requests\Finance\FinanceStatementRowUpdateRequest;
use App\Models\FinanceAccount;
use App\Models\FinanceAccountLog;
use App\Models\FinanceGeneralLedgerEntry;
use App\Models\FinanceStatementBatch;
use App\Models\FinanceStatementRow;
use App\Services\Finance\FinanceGeneralLedgerService;
use App\Services\Finance\FinanceImportedStatementService;
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
        private FinanceGeneralLedgerService $financeGeneralLedgerService,
        private FinanceImportedStatementService $financeImportedStatementService,
        private FinancialStatementDocumentService $financialStatementDocumentService,
        private FinancialStatementSpreadsheetService $financialStatementSpreadsheetService
    ) {}

    public function balanceSheet(FinanceStatementFilterRequest $request)
    {
        return $this->renderBalanceSheetPage($request, false);
    }

    public function profitLoss(FinanceStatementFilterRequest $request)
    {
        return $this->renderProfitLossPage($request, false);
    }

    public function manageBalanceSheet(FinanceStatementFilterRequest $request)
    {
        return $this->renderBalanceSheetPage($request, true);
    }

    public function manageProfitLoss(FinanceStatementFilterRequest $request)
    {
        return $this->renderProfitLossPage($request, true);
    }

    private function renderBalanceSheetPage(FinanceStatementFilterRequest $request, bool $isManageMode)
    {
        try {
            $filter = StatementFilterDTO::fromArray($request->validated());
            [$statementDataSource, $resolvedBatchId, $batchOptions] = $this->resolveStatementDataContext(
                $request,
                $filter,
                FinanceStatementBatch::TYPE_BALANCE_SHEET,
                $isManageMode
            );

            $filter->statementDataSource = $statementDataSource;
            $filter->statementBatchId = $resolvedBatchId;

            $report = $statementDataSource === 'imported'
                ? $this->financeImportedStatementService->getImportedBalanceSheetReport($filter, $resolvedBatchId)
                : $this->financialStatementService->getBalanceSheetReport($filter);

            $resolvedBatchId = $resolvedBatchId ?? data_get($report, 'batch.id');
            $filter->statementBatchId = $resolvedBatchId;
            $editRow = $statementDataSource === 'imported'
                ? $this->financeImportedStatementService->findRow((string) $request->query('edit_row', ''))
                : null;

            return view('finance.balance-sheet', [
                'report' => $report,
                'filters' => array_merge($this->buildFilterPayload($filter), [
                    'statement_data_source' => $statementDataSource,
                    'statement_batch_id' => $resolvedBatchId,
                ]),
                'periodLabel' => $this->buildPeriodLabel($filter),
                'filterQuery' => array_filter(array_merge($filter->toQueryArray(), [
                    'statement_data_source' => $statementDataSource,
                    'statement_batch_id' => $resolvedBatchId,
                ]), static fn ($value): bool => $value !== null && $value !== ''),
                'baseFilterQuery' => $this->buildBaseFilterQuery($filter),
                'statementDataSource' => $statementDataSource,
                'selectedBatchId' => $resolvedBatchId,
                'batchOptions' => $statementDataSource === 'imported'
                    ? ($report['batches'] ?? $batchOptions->all())
                    : $batchOptions->all(),
                'selectedBatch' => $report['batch'] ?? null,
                'importedRows' => $report['imported_rows'] ?? [],
                'editImportedRow' => $editRow !== null ? $this->serializeImportedStatementRow($editRow) : null,
                'isManageMode' => $isManageMode,
                'pageRouteName' => $isManageMode
                    ? 'finance.report.balance-sheet.manage'
                    : 'finance.report.balance-sheet',
                'mainStatementRouteName' => 'finance.report.balance-sheet',
                'manageStatementRouteName' => 'finance.report.balance-sheet.manage',
            ]);
        } catch (Throwable $exception) {
            report($exception);

            return redirect()
                ->route('finance.dashboard')
                ->with('error', 'Gagal memuat laporan lembar saldo.');
        }
    }

    private function renderProfitLossPage(FinanceStatementFilterRequest $request, bool $isManageMode)
    {
        try {
            $filter = StatementFilterDTO::fromArray($request->validated());
            [$statementDataSource, $resolvedBatchId, $batchOptions] = $this->resolveStatementDataContext(
                $request,
                $filter,
                FinanceStatementBatch::TYPE_PROFIT_LOSS,
                $isManageMode
            );

            $filter->statementDataSource = $statementDataSource;
            $filter->statementBatchId = $resolvedBatchId;

            $report = $statementDataSource === 'imported'
                ? $this->financeImportedStatementService->getImportedProfitLossReport($filter, $resolvedBatchId)
                : $this->financialStatementService->getProfitLossReport($filter);

            $resolvedBatchId = $resolvedBatchId ?? data_get($report, 'batch.id');
            $filter->statementBatchId = $resolvedBatchId;
            $editRow = $statementDataSource === 'imported'
                ? $this->financeImportedStatementService->findRow((string) $request->query('edit_row', ''))
                : null;

            return view('finance.profit-loss-statement', [
                'report' => $report,
                'filters' => array_merge($this->buildFilterPayload($filter), [
                    'statement_data_source' => $statementDataSource,
                    'statement_batch_id' => $resolvedBatchId,
                ]),
                'periodLabel' => $this->buildPeriodLabel($filter),
                'filterQuery' => array_filter(array_merge($filter->toQueryArray(), [
                    'statement_data_source' => $statementDataSource,
                    'statement_batch_id' => $resolvedBatchId,
                ]), static fn ($value): bool => $value !== null && $value !== ''),
                'baseFilterQuery' => $this->buildBaseFilterQuery($filter),
                'statementDataSource' => $statementDataSource,
                'selectedBatchId' => $resolvedBatchId,
                'batchOptions' => $statementDataSource === 'imported'
                    ? ($report['batches'] ?? $batchOptions->all())
                    : $batchOptions->all(),
                'selectedBatch' => $report['batch'] ?? null,
                'importedRows' => $report['imported_rows'] ?? [],
                'editImportedRow' => $editRow !== null ? $this->serializeImportedStatementRow($editRow) : null,
                'isManageMode' => $isManageMode,
                'pageRouteName' => $isManageMode
                    ? 'finance.report.profit-loss.manage'
                    : 'finance.report.profit-loss',
                'mainStatementRouteName' => 'finance.report.profit-loss',
                'manageStatementRouteName' => 'finance.report.profit-loss.manage',
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
        return $this->renderGeneralLedgerPage($request, false);
    }

    public function manageGeneralLedger(FinanceStatementFilterRequest $request)
    {
        return $this->renderGeneralLedgerPage($request, true);
    }

    private function renderGeneralLedgerPage(FinanceStatementFilterRequest $request, bool $isManageMode)
    {
        try {
            $filter = StatementFilterDTO::fromArray($request->validated());
            $batchOptions = $this->financeGeneralLedgerService->getBatchOptions();
            $hasImportedBatches = $batchOptions->isNotEmpty();
            $hasExplicitSource = $request->query->has('ledger_source') && $request->query('ledger_source') !== '';
            $ledgerSource = $hasExplicitSource
                ? ($filter->ledgerSource ?? ($isManageMode ? 'imported' : 'system'))
                : ($isManageMode ? 'imported' : ($hasImportedBatches ? 'imported' : 'system'));

            $filter->ledgerSource = $ledgerSource;
            if ($ledgerSource === 'imported' && !$this->hasExplicitGeneralLedgerPeriodInput($request)) {
                $this->applyAllPeriodToGeneralLedgerFilter($filter);
            }

            $report = $ledgerSource === 'imported'
                ? $this->financeGeneralLedgerService->getImportedGeneralLedgerReport($filter, $filter->ledgerBatchId)
                : $this->financialStatementService->getGeneralLedgerReport($filter);
            $resolvedBatchId = $filter->ledgerBatchId ?? data_get($report, 'batch.id');
            $filter->ledgerBatchId = $resolvedBatchId;
            $editEntry = $ledgerSource === 'imported'
                ? $this->financeGeneralLedgerService->findEntry((string) $request->query('edit_entry', ''))
                : null;
            $filters = array_merge($this->buildFilterPayload($filter), [
                'ledger_source' => $ledgerSource,
                'ledger_batch_id' => $resolvedBatchId,
            ]);
            $filterQuery = array_filter(array_merge($filter->toQueryArray(), [
                'ledger_source' => $ledgerSource,
                'ledger_batch_id' => $resolvedBatchId,
            ]), static fn ($value): bool => $value !== null && $value !== '');

            return view('finance.general-ledger', [
                'report' => $report,
                'filters' => $filters,
                'periodLabel' => $this->buildPeriodLabel($filter),
                'filterQuery' => $filterQuery,
                'baseFilterQuery' => $this->buildBaseFilterQuery($filter),
                'selectedAccountCode' => $filter->accountCode,
                'ledgerSource' => $ledgerSource,
                'selectedBatchId' => $resolvedBatchId,
                'batchOptions' => $ledgerSource === 'imported'
                    ? ($report['batches'] ?? $batchOptions->all())
                    : $batchOptions->all(),
                'selectedBatch' => $report['batch'] ?? null,
                'editEntry' => $editEntry !== null ? $this->serializeImportedLedgerEntry($editEntry) : null,
                'isManageMode' => $isManageMode,
                'pageRouteName' => $isManageMode
                    ? 'finance.report.general-ledger.manage'
                    : 'finance.report.general-ledger',
                'mainLedgerRouteName' => 'finance.report.general-ledger',
                'manageLedgerRouteName' => 'finance.report.general-ledger.manage',
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

    public function importBalanceSheetExcel(FinanceStatementImportRequest $request)
    {
        return $this->importStatementExcel($request, FinanceStatementBatch::TYPE_BALANCE_SHEET);
    }

    public function importProfitLossExcel(FinanceStatementImportRequest $request)
    {
        return $this->importStatementExcel($request, FinanceStatementBatch::TYPE_PROFIT_LOSS);
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

    public function importGeneralLedgerExcel(FinanceGeneralLedgerImportRequest $request)
    {
        $uploadedFile = $request->file('file');
        if ($uploadedFile === null) {
            return redirect()
                ->route('finance.report.general-ledger.manage', ['ledger_source' => 'imported'])
                ->with('error', 'File import buku besar tidak ditemukan.');
        }

        try {
            $summary = $this->financeGeneralLedgerService->importFromExcel(
                $uploadedFile->getPathname(),
                $uploadedFile->getClientOriginalName(),
                $request->validated()['batch_name'] ?? null,
                $request->validated()['notes'] ?? null,
                auth()->id() ? (string) auth()->id() : null
            );

            return redirect()
                ->route('finance.report.general-ledger.manage', [
                    'ledger_source' => 'imported',
                    'ledger_batch_id' => (string) $summary['batch']->id,
                    'period_type' => 'ALL',
                ])
                ->with(
                    'success',
                    'Import buku besar selesai. '
                    . $summary['inserted'] . ' baris dan '
                    . $summary['account_count'] . ' akun berhasil dibaca.'
                );
        } catch (Throwable $exception) {
            report($exception);

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Import Excel buku besar gagal: ' . $exception->getMessage());
        }
    }

    public function storeGeneralLedgerEntry(FinanceGeneralLedgerEntryStoreRequest $request)
    {
        try {
            $entry = $this->financeGeneralLedgerService->createEntry(
                $request->validated(),
                auth()->id() ? (string) auth()->id() : null
            );

            return redirect()
                ->route('finance.report.general-ledger.manage', array_merge($this->extractGeneralLedgerRedirectQuery($request), [
                    'ledger_source' => 'imported',
                    'ledger_batch_id' => (string) $entry->batch_id,
                ]))
                ->with('success', 'Baris buku besar berhasil ditambahkan.');
        } catch (Throwable $exception) {
            report($exception);

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Gagal menambahkan baris buku besar.');
        }
    }

    public function updateGeneralLedgerEntry(
        FinanceGeneralLedgerEntryUpdateRequest $request,
        FinanceGeneralLedgerEntry $entry
    ) {
        try {
            $updatedEntry = $this->financeGeneralLedgerService->updateEntry(
                $entry,
                $request->validated(),
                auth()->id() ? (string) auth()->id() : null
            );

            return redirect()
                ->route('finance.report.general-ledger.manage', array_merge($this->extractGeneralLedgerRedirectQuery($request), [
                    'ledger_source' => 'imported',
                    'ledger_batch_id' => (string) $updatedEntry->batch_id,
                ]))
                ->with('success', 'Baris buku besar berhasil diperbarui.');
        } catch (Throwable $exception) {
            report($exception);

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Gagal memperbarui baris buku besar.');
        }
    }

    public function destroyGeneralLedgerEntry(FinanceStatementFilterRequest $request, FinanceGeneralLedgerEntry $entry)
    {
        try {
            $batchId = (string) $entry->batch_id;
            $filter = StatementFilterDTO::fromArray($request->validated());
            $this->financeGeneralLedgerService->deleteEntry($entry);

            return redirect()
                ->route('finance.report.general-ledger.manage', array_merge(
                    $filter->toQueryArray(),
                    [
                        'ledger_source' => 'imported',
                        'ledger_batch_id' => $batchId,
                    ]
                ))
                ->with('success', 'Baris buku besar berhasil dihapus.');
        } catch (Throwable $exception) {
            report($exception);

            return redirect()
                ->back()
                ->with('error', 'Gagal menghapus baris buku besar.');
        }
    }

    public function storeBalanceSheetRow(FinanceStatementRowStoreRequest $request)
    {
        return $this->storeStatementRow($request, FinanceStatementBatch::TYPE_BALANCE_SHEET);
    }

    public function updateBalanceSheetRow(FinanceStatementRowUpdateRequest $request, FinanceStatementRow $row)
    {
        return $this->updateStatementRow($request, $row, FinanceStatementBatch::TYPE_BALANCE_SHEET);
    }

    public function destroyBalanceSheetRow(FinanceStatementFilterRequest $request, FinanceStatementRow $row)
    {
        return $this->destroyStatementRow($request, $row, FinanceStatementBatch::TYPE_BALANCE_SHEET);
    }

    public function storeProfitLossRow(FinanceStatementRowStoreRequest $request)
    {
        return $this->storeStatementRow($request, FinanceStatementBatch::TYPE_PROFIT_LOSS);
    }

    public function updateProfitLossRow(FinanceStatementRowUpdateRequest $request, FinanceStatementRow $row)
    {
        return $this->updateStatementRow($request, $row, FinanceStatementBatch::TYPE_PROFIT_LOSS);
    }

    public function destroyProfitLossRow(FinanceStatementFilterRequest $request, FinanceStatementRow $row)
    {
        return $this->destroyStatementRow($request, $row, FinanceStatementBatch::TYPE_PROFIT_LOSS);
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

    private function importStatementExcel(FinanceStatementImportRequest $request, string $statementType)
    {
        $uploadedFile = $request->file('file');
        $routeName = $statementType === FinanceStatementBatch::TYPE_BALANCE_SHEET
            ? 'finance.report.balance-sheet.manage'
            : 'finance.report.profit-loss.manage';

        if ($uploadedFile === null) {
            return redirect()
                ->route($routeName, ['statement_data_source' => 'imported'])
                ->with('error', 'File import laporan tidak ditemukan.');
        }

        try {
            $summary = $this->financeImportedStatementService->importFromExcel(
                $statementType,
                $uploadedFile->getPathname(),
                $uploadedFile->getClientOriginalName(),
                $request->validated()['batch_name'] ?? null,
                $request->validated()['notes'] ?? null,
                auth()->id() ? (string) auth()->id() : null
            );

            return redirect()
                ->route($routeName, [
                    'statement_data_source' => 'imported',
                    'statement_batch_id' => (string) $summary['batch']->id,
                    'period_type' => 'ALL',
                ])
                ->with('success', 'Import laporan selesai. ' . $summary['row_count'] . ' baris berhasil dibaca.');
        } catch (Throwable $exception) {
            report($exception);

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Import Excel laporan gagal: ' . $exception->getMessage());
        }
    }

    private function storeStatementRow(FinanceStatementRowStoreRequest $request, string $statementType)
    {
        try {
            $row = $this->financeImportedStatementService->createRow(
                $statementType,
                $request->validated(),
                auth()->id() ? (string) auth()->id() : null
            );

            return redirect()
                ->route($this->statementManageRoute($statementType), array_merge(
                    $this->extractStatementRedirectQuery($request),
                    [
                        'statement_data_source' => 'imported',
                        'statement_batch_id' => (string) $row->batch_id,
                    ]
                ))
                ->with('success', 'Baris laporan berhasil ditambahkan.');
        } catch (Throwable $exception) {
            report($exception);

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Gagal menambahkan baris laporan.');
        }
    }

    private function updateStatementRow(
        FinanceStatementRowUpdateRequest $request,
        FinanceStatementRow $row,
        string $statementType
    ) {
        try {
            if ((string) $row->batch?->statement_type !== $statementType) {
                throw new \RuntimeException('Baris laporan tidak cocok dengan jenis laporan.');
            }

            $updatedRow = $this->financeImportedStatementService->updateRow(
                $row,
                $statementType,
                $request->validated(),
                auth()->id() ? (string) auth()->id() : null
            );

            return redirect()
                ->route($this->statementManageRoute($statementType), array_merge(
                    $this->extractStatementRedirectQuery($request),
                    [
                        'statement_data_source' => 'imported',
                        'statement_batch_id' => (string) $updatedRow->batch_id,
                    ]
                ))
                ->with('success', 'Baris laporan berhasil diperbarui.');
        } catch (Throwable $exception) {
            report($exception);

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Gagal memperbarui baris laporan.');
        }
    }

    private function destroyStatementRow(
        FinanceStatementFilterRequest $request,
        FinanceStatementRow $row,
        string $statementType
    ) {
        try {
            if ((string) $row->batch?->statement_type !== $statementType) {
                throw new \RuntimeException('Baris laporan tidak cocok dengan jenis laporan.');
            }

            $batchId = (string) $row->batch_id;
            $filter = StatementFilterDTO::fromArray($request->validated());
            $this->financeImportedStatementService->deleteRow($row);

            return redirect()
                ->route($this->statementManageRoute($statementType), array_merge(
                    $filter->toQueryArray(),
                    [
                        'statement_data_source' => 'imported',
                        'statement_batch_id' => $batchId,
                    ]
                ))
                ->with('success', 'Baris laporan berhasil dihapus.');
        } catch (Throwable $exception) {
            report($exception);

            return redirect()
                ->back()
                ->with('error', 'Gagal menghapus baris laporan.');
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
            'statement_data_source' => $filter->statementDataSource,
            'statement_batch_id' => $filter->statementBatchId,
            'ledger_source' => $filter->ledgerSource,
            'ledger_batch_id' => $filter->ledgerBatchId,
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
                statementDataSource: $filter->statementDataSource,
                statementBatchId: $filter->statementBatchId,
                ledgerSource: $filter->ledgerSource,
                ledgerBatchId: $filter->ledgerBatchId,
                selectedIds: $filter->selectedIds,
                page: 1,
                perPage: 5000
            );

            $exported = match ($statementType) {
                'balance_sheet' => $format === 'excel'
                    ? $this->financialStatementSpreadsheetService->exportBalanceSheet(
                        ($filter->statementDataSource ?? 'system') === 'imported'
                            ? $this->financeImportedStatementService->getImportedBalanceSheetReport(
                                $exportFilter,
                                $filter->statementBatchId
                            )
                            : $this->financialStatementService->getBalanceSheetReport($exportFilter),
                        $exportFilter
                    )
                    : $this->financialStatementDocumentService->exportBalanceSheet(
                        ($filter->statementDataSource ?? 'system') === 'imported'
                            ? $this->financeImportedStatementService->getImportedBalanceSheetReport(
                                $exportFilter,
                                $filter->statementBatchId
                            )
                            : $this->financialStatementService->getBalanceSheetReport($exportFilter),
                        $exportFilter
                    ),
                'profit_loss' => $format === 'excel'
                    ? $this->financialStatementSpreadsheetService->exportProfitLoss(
                        ($filter->statementDataSource ?? 'system') === 'imported'
                            ? $this->financeImportedStatementService->getImportedProfitLossReport(
                                $exportFilter,
                                $filter->statementBatchId
                            )
                            : $this->financialStatementService->getProfitLossReport($exportFilter),
                        $exportFilter
                    )
                    : $this->financialStatementDocumentService->exportProfitLoss(
                        ($filter->statementDataSource ?? 'system') === 'imported'
                            ? $this->financeImportedStatementService->getImportedProfitLossReport(
                                $exportFilter,
                                $filter->statementBatchId
                            )
                            : $this->financialStatementService->getProfitLossReport($exportFilter),
                        $exportFilter
                    ),
                'general_ledger' => $format === 'excel'
                    ? $this->financialStatementSpreadsheetService->exportGeneralLedger(
                        ($filter->ledgerSource ?? 'system') === 'imported'
                            ? $this->financeGeneralLedgerService->getImportedGeneralLedgerReport(
                                $exportFilter,
                                $filter->ledgerBatchId,
                                false
                            )
                            : $this->financialStatementService->getGeneralLedgerReport($exportFilter, false),
                        $exportFilter
                    )
                    : $this->financialStatementDocumentService->exportGeneralLedger(
                        ($filter->ledgerSource ?? 'system') === 'imported'
                            ? $this->financeGeneralLedgerService->getImportedGeneralLedgerReport(
                                $exportFilter,
                                $filter->ledgerBatchId,
                                false
                            )
                            : $this->financialStatementService->getGeneralLedgerReport($exportFilter, false),
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

    /**
     * @return array{0:string,1:?string,2:\Illuminate\Support\Collection<int, array<string, mixed>>}
     */
    private function resolveStatementDataContext(
        FinanceStatementFilterRequest $request,
        StatementFilterDTO $filter,
        string $statementType,
        bool $isManageMode
    ): array {
        $batchOptions = $this->financeImportedStatementService->getBatchOptions($statementType);
        $hasImportedBatches = $batchOptions->isNotEmpty();
        $hasExplicitSource = $request->query->has('statement_data_source')
            && $request->query('statement_data_source') !== '';
        $statementDataSource = $hasExplicitSource
            ? ($filter->statementDataSource ?? ($isManageMode ? 'imported' : 'system'))
            : ($isManageMode ? 'imported' : ($hasImportedBatches ? 'imported' : 'system'));

        $resolvedBatchId = $filter->statementBatchId;

        if ($statementDataSource === 'imported' && !$this->hasExplicitStatementPeriodInput($request)) {
            $this->applyAllPeriodToStatementFilter($filter);
        }

        return [$statementDataSource, $resolvedBatchId, $batchOptions];
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

    /**
     * @return array<string, int|string>
     */
    private function extractGeneralLedgerRedirectQuery(
        FinanceGeneralLedgerEntryStoreRequest|FinanceGeneralLedgerEntryUpdateRequest $request
    ): array {
        return array_filter([
            'period_type' => $request->input('period_type'),
            'start_date' => $request->input('start_date'),
            'end_date' => $request->input('end_date'),
            'start_month' => $request->input('start_month'),
            'end_month' => $request->input('end_month'),
            'start_year' => $request->input('start_year'),
            'end_year' => $request->input('end_year'),
            'report_date' => $request->input('report_date'),
            'month' => $request->input('month'),
            'year' => $request->input('year'),
            'account_code' => $request->input('account_code_filter'),
            'search' => $request->input('search_filter'),
            'per_page' => $request->input('per_page'),
        ], static fn ($value): bool => $value !== null && $value !== '');
    }

    /**
     * @return array<string, mixed>
     */
    private function serializeImportedLedgerEntry(FinanceGeneralLedgerEntry $entry): array
    {
        return [
            'id' => (string) $entry->id,
            'batch_id' => (string) $entry->batch_id,
            'row_type' => (string) $entry->row_type,
            'entry_date' => $entry->entry_date?->toDateString(),
            'account_code' => (string) $entry->account_code,
            'account_name' => (string) $entry->account_name,
            'transaction_no' => $entry->transaction_no !== null ? (string) $entry->transaction_no : null,
            'communication' => $entry->communication !== null ? (string) $entry->communication : null,
            'partner_name' => $entry->partner_name !== null ? (string) $entry->partner_name : null,
            'currency' => $entry->currency !== null ? (string) $entry->currency : 'IDR',
            'label' => $entry->label !== null ? (string) $entry->label : null,
            'reference' => $entry->reference !== null ? (string) $entry->reference : null,
            'analytic_distribution' => $entry->analytic_distribution !== null
                ? (string) $entry->analytic_distribution
                : null,
            'opening_balance' => round((float) $entry->opening_balance, 2),
            'debit' => round((float) $entry->debit, 2),
            'credit' => round((float) $entry->credit, 2),
            'is_manual' => (bool) $entry->is_manual,
        ];
    }

    private function hasExplicitGeneralLedgerPeriodInput(FinanceStatementFilterRequest $request): bool
    {
        return $request->filled('period_type')
            || $request->filled('report_date')
            || $request->filled('month')
            || $request->filled('year')
            || $request->filled('start_date')
            || $request->filled('end_date')
            || $request->filled('start_month')
            || $request->filled('end_month')
            || $request->filled('start_year')
            || $request->filled('end_year');
    }

    private function applyAllPeriodToGeneralLedgerFilter(StatementFilterDTO $filter): void
    {
        $filter->periodType = null;
        $filter->reportDate = null;
        $filter->year = null;
        $filter->month = null;
        $filter->startDate = null;
        $filter->endDate = null;
        $filter->startMonth = null;
        $filter->endMonth = null;
        $filter->startYear = null;
        $filter->endYear = null;
    }

    /**
     * @return array<string, int|string>
     */
    private function extractStatementRedirectQuery(
        FinanceStatementRowStoreRequest|FinanceStatementRowUpdateRequest $request
    ): array {
        return array_filter([
            'period_type' => $request->input('period_type'),
            'start_date' => $request->input('start_date'),
            'end_date' => $request->input('end_date'),
            'start_month' => $request->input('start_month'),
            'end_month' => $request->input('end_month'),
            'start_year' => $request->input('start_year'),
            'end_year' => $request->input('end_year'),
            'report_date' => $request->input('report_date'),
            'month' => $request->input('month'),
            'year' => $request->input('year'),
            'account_code' => $request->input('account_code_filter'),
            'search' => $request->input('search_filter'),
        ], static fn ($value): bool => $value !== null && $value !== '');
    }

    /**
     * @return array<string, mixed>
     */
    private function serializeImportedStatementRow(FinanceStatementRow $row): array
    {
        return [
            'id' => (string) $row->id,
            'batch_id' => (string) $row->batch_id,
            'section_key' => $row->section_key !== null ? (string) $row->section_key : null,
            'section_label' => $row->section_label !== null ? (string) $row->section_label : null,
            'group_label' => $row->group_label !== null ? (string) $row->group_label : null,
            'account_code' => $row->account_code !== null ? (string) $row->account_code : null,
            'account_name' => (string) $row->account_name,
            'finance_type' => $row->finance_type !== null ? (string) $row->finance_type : null,
            'amount' => round((float) $row->amount, 2),
            'is_manual' => (bool) $row->is_manual,
        ];
    }

    private function statementManageRoute(string $statementType): string
    {
        return match ($statementType) {
            FinanceStatementBatch::TYPE_BALANCE_SHEET => 'finance.report.balance-sheet.manage',
            FinanceStatementBatch::TYPE_PROFIT_LOSS => 'finance.report.profit-loss.manage',
            default => 'finance.dashboard',
        };
    }

    private function hasExplicitStatementPeriodInput(FinanceStatementFilterRequest $request): bool
    {
        return $request->filled('period_type')
            || $request->filled('report_date')
            || $request->filled('month')
            || $request->filled('year')
            || $request->filled('start_date')
            || $request->filled('end_date')
            || $request->filled('start_month')
            || $request->filled('end_month')
            || $request->filled('start_year')
            || $request->filled('end_year');
    }

    private function applyAllPeriodToStatementFilter(StatementFilterDTO $filter): void
    {
        $filter->periodType = null;
        $filter->reportDate = null;
        $filter->year = null;
        $filter->month = null;
        $filter->startDate = null;
        $filter->endDate = null;
        $filter->startMonth = null;
        $filter->endMonth = null;
        $filter->startYear = null;
        $filter->endYear = null;
    }
}
