<?php

namespace App\Services\Finance;

use App\DTOs\Finance\StatementFilterDTO;
use App\Models\FinanceAccount;
use App\Models\FinanceGeneralLedgerBatch;
use App\Models\FinanceGeneralLedgerEntry;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as SpreadsheetDate;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use RuntimeException;

class FinanceGeneralLedgerService
{
    /**
     * @return array{
     *   groups: array<int, array<string, mixed>>,
     *   accounts: mixed,
     *   summary: array{account_count:int,entry_count:int,total_debit:float,total_credit:float,balance_gap:float},
     *   batch: array<string, mixed>|null,
     *   batches: array<int, array<string, mixed>>
     * }
     */
    public function getImportedGeneralLedgerReport(
        StatementFilterDTO $filter,
        ?string $batchId = null,
        bool $paginate = true
    ): array {
        $batches = $this->getBatchOptions();
        $selectedBatch = $this->resolveSelectedBatch($batchId);

        if ($selectedBatch === null) {
            return [
                'groups' => [],
                'accounts' => $paginate ? collect([]) : [],
                'summary' => $this->emptySummary(),
                'batch' => null,
                'batches' => $batches->all(),
            ];
        }

        $baseQuery = FinanceGeneralLedgerEntry::query()
            ->where('batch_id', $selectedBatch->id);

        if (!empty($filter->accountCode)) {
            $baseQuery->where('account_code', $filter->accountCode);
        }

        $visibleQuery = clone $baseQuery;
        $this->applyImportedLedgerSearch($visibleQuery, $filter->search);
        $hasDateFilter = $this->applyImportedLedgerPeriodFilter($visibleQuery, $filter);

        $summary = $this->buildImportedSummary($selectedBatch->id, $filter);
        $accountQuery = $this->buildImportedAccountQuery($visibleQuery, $hasDateFilter);

        $accounts = $paginate
            ? $accountQuery->paginate($filter->perPage, ['*'], 'page', $filter->page)
            : $accountQuery->get();

        $accountRows = $paginate
            ? collect(($accounts instanceof LengthAwarePaginator) ? $accounts->items() : [])
            : collect($accounts);

        $accountCodes = $accountRows
            ->map(static fn ($row): string => (string) ($row->account_code ?? ''))
            ->filter()
            ->values()
            ->all();

        if (empty($accountCodes)) {
            return [
                'groups' => [],
                'accounts' => $accounts,
                'summary' => $summary,
                'batch' => $this->serializeBatch($selectedBatch),
                'batches' => $batches->all(),
            ];
        }

        $visibleEntries = $this->buildVisibleEntryQuery($selectedBatch->id, $filter, $accountCodes)
            ->get()
            ->groupBy('account_code');

        $openingRows = collect();
        if (!empty($filter->startDate)) {
            $openingRows = FinanceGeneralLedgerEntry::query()
                ->where('batch_id', $selectedBatch->id)
                ->whereIn('account_code', $accountCodes)
                ->whereDate('entry_date', '<', $filter->startDate)
                ->orderBy('account_code')
                ->orderBy('entry_date')
                ->orderByRaw("CASE WHEN row_type = 'OPENING' THEN 0 ELSE 1 END")
                ->orderBy('sort_order')
                ->orderBy('id')
                ->get()
                ->groupBy('account_code')
                ->map(static fn (Collection $rows) => $rows->last());
        }

        $groups = $accountRows
            ->map(function ($accountRow) use ($visibleEntries, $openingRows, $filter): array {
                $accountCode = (string) ($accountRow->account_code ?? '');
                $rows = $visibleEntries->get($accountCode, collect());
                $entries = [];

                $openingRow = $openingRows->get($accountCode);
                if ($openingRow !== null && $rows->isNotEmpty()) {
                    $entries[] = [
                        'entry_id' => null,
                        'accounting_date' => $filter->startDate,
                        'invoice_id' => null,
                        'invoice_no' => 'SALDO AWAL',
                        'journal_name' => 'Saldo Awal',
                        'reference' => null,
                        'entry_type' => 'OPENING',
                        'label' => 'Saldo sebelum periode terpilih',
                        'partner_name' => null,
                        'analytic_distribution' => null,
                        'debit' => 0.0,
                        'credit' => 0.0,
                        'running_balance' => round((float) $openingRow->balance_amount, 2),
                        'row_type' => FinanceGeneralLedgerEntry::ROW_TYPE_OPENING,
                        'currency' => $openingRow->currency ?: 'IDR',
                        'is_manual' => false,
                        'can_edit' => false,
                    ];
                }

                foreach ($rows as $row) {
                    $entries[] = [
                        'entry_id' => (string) $row->id,
                        'accounting_date' => $row->entry_date !== null
                            ? $row->entry_date->toDateString()
                            : null,
                        'invoice_id' => null,
                        'invoice_no' => $row->row_type === FinanceGeneralLedgerEntry::ROW_TYPE_OPENING
                            ? 'SALDO AWAL'
                            : ((string) ($row->transaction_no ?: '-')),
                        'journal_name' => (string) ($row->communication ?: ($row->row_type === FinanceGeneralLedgerEntry::ROW_TYPE_OPENING ? 'Saldo Awal' : '-')),
                        'reference' => $row->reference !== null ? (string) $row->reference : null,
                        'entry_type' => $row->row_type,
                        'label' => (string) ($row->label ?: ($row->row_type === FinanceGeneralLedgerEntry::ROW_TYPE_OPENING ? 'Saldo Awal' : '-')),
                        'partner_name' => $row->partner_name !== null ? (string) $row->partner_name : null,
                        'analytic_distribution' => $row->analytic_distribution !== null
                            ? (string) $row->analytic_distribution
                            : null,
                        'debit' => $row->row_type === FinanceGeneralLedgerEntry::ROW_TYPE_OPENING
                            ? 0.0
                            : round((float) $row->debit, 2),
                        'credit' => $row->row_type === FinanceGeneralLedgerEntry::ROW_TYPE_OPENING
                            ? 0.0
                            : round((float) $row->credit, 2),
                        'running_balance' => round((float) $row->balance_amount, 2),
                        'row_type' => (string) $row->row_type,
                        'currency' => (string) ($row->currency ?: 'IDR'),
                        'is_manual' => (bool) $row->is_manual,
                        'can_edit' => true,
                    ];
                }

                $closingBalance = !empty($entries)
                    ? round((float) data_get(end($entries), 'running_balance', 0), 2)
                    : 0.0;

                return [
                    'account_code' => $accountCode,
                    'account_name' => (string) ($accountRow->account_name ?? '-'),
                    'finance_type' => (string) ($accountRow->finance_type ?? ''),
                    'normal_side' => $this->resolveDisplayNormalSide((string) ($accountRow->finance_type ?? '')),
                    'total_debit' => round((float) ($accountRow->total_debit ?? 0), 2),
                    'total_credit' => round((float) ($accountRow->total_credit ?? 0), 2),
                    'closing_balance' => $closingBalance,
                    'entries' => $entries,
                ];
            })
            ->values()
            ->all();

        return [
            'groups' => $groups,
            'accounts' => $accounts,
            'summary' => $summary,
            'batch' => $this->serializeBatch($selectedBatch),
            'batches' => $batches->all(),
        ];
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function getBatchOptions(): Collection
    {
        return FinanceGeneralLedgerBatch::query()
            ->leftJoin('finance_general_ledger_entries as e', 'e.batch_id', '=', 'finance_general_ledger_batches.id')
            ->select('finance_general_ledger_batches.*')
            ->selectRaw('COUNT(e.id) as entry_count')
            ->selectRaw('COUNT(DISTINCT e.account_code) as account_count')
            ->selectRaw('SUM(CASE WHEN e.is_manual = 1 THEN 1 ELSE 0 END) as manual_count')
            ->groupBy([
                'finance_general_ledger_batches.id',
                'finance_general_ledger_batches.source_type',
                'finance_general_ledger_batches.batch_name',
                'finance_general_ledger_batches.source_filename',
                'finance_general_ledger_batches.sheet_name',
                'finance_general_ledger_batches.imported_year',
                'finance_general_ledger_batches.notes',
                'finance_general_ledger_batches.meta',
                'finance_general_ledger_batches.imported_at',
                'finance_general_ledger_batches.created_by',
                'finance_general_ledger_batches.updated_by',
                'finance_general_ledger_batches.created_at',
                'finance_general_ledger_batches.updated_at',
            ])
            ->orderByDesc('finance_general_ledger_batches.imported_at')
            ->orderByDesc('finance_general_ledger_batches.created_at')
            ->get()
            ->map(fn ($batch): array => $this->serializeBatch($batch, [
                'entry_count' => (int) ($batch->entry_count ?? 0),
                'account_count' => (int) ($batch->account_count ?? 0),
                'manual_count' => (int) ($batch->manual_count ?? 0),
            ]));
    }

    /**
     * @return array{batch:FinanceGeneralLedgerBatch,inserted:int,account_count:int}
     */
    public function importFromExcel(
        string $path,
        string $originalName,
        ?string $batchName,
        ?string $notes,
        ?string $actorId
    ): array {
        $spreadsheet = IOFactory::load($path);
        $sheet = $spreadsheet->getSheet(0);
        $parsed = $this->parseWorkbookSheet($sheet);

        if (empty($parsed['rows'])) {
            throw new RuntimeException('Tidak ada baris buku besar yang bisa dibaca dari file Excel.');
        }

        $resolvedBatchName = $batchName ?: pathinfo($originalName, PATHINFO_FILENAME);
        $now = now();

        $batch = DB::transaction(function () use (
            $resolvedBatchName,
            $originalName,
            $notes,
            $actorId,
            $parsed,
            $sheet,
            $now
        ) {
            $batch = FinanceGeneralLedgerBatch::query()->create([
                'source_type' => FinanceGeneralLedgerBatch::SOURCE_IMPORT,
                'batch_name' => $resolvedBatchName,
                'source_filename' => $originalName,
                'sheet_name' => $sheet->getTitle(),
                'imported_year' => $parsed['imported_year'],
                'notes' => $notes,
                'meta' => [
                    'sheet_title' => $sheet->getTitle(),
                    'parsed_accounts' => $parsed['account_count'],
                ],
                'imported_at' => $now,
                'created_by' => $actorId,
                'updated_by' => $actorId,
            ]);

            $chunks = array_chunk($parsed['rows'], 300);
            foreach ($chunks as $chunk) {
                DB::table('finance_general_ledger_entries')->insert(
                    array_map(function (array $row) use ($batch, $actorId, $now): array {
                        return [
                            'id' => (string) Str::uuid(),
                            'batch_id' => (string) $batch->id,
                            'row_type' => $row['row_type'],
                            'entry_date' => $row['entry_date'],
                            'account_code' => $row['account_code'],
                            'account_name' => $row['account_name'],
                            'transaction_no' => $row['transaction_no'],
                            'communication' => $row['communication'],
                            'partner_name' => $row['partner_name'],
                            'currency' => $row['currency'],
                            'label' => $row['label'],
                            'reference' => $row['reference'],
                            'analytic_distribution' => $row['analytic_distribution'],
                            'opening_balance' => $row['opening_balance'],
                            'debit' => $row['debit'],
                            'credit' => $row['credit'],
                            'balance_amount' => $row['balance_amount'],
                            'sort_order' => $row['sort_order'],
                            'sheet_row_number' => $row['sheet_row_number'],
                            'is_manual' => false,
                            'meta' => json_encode($row['meta'], JSON_UNESCAPED_UNICODE),
                            'created_by' => $actorId,
                            'updated_by' => $actorId,
                            'created_at' => $now,
                            'updated_at' => $now,
                        ];
                    }, $chunk)
                );
            }

            return $batch;
        });

        $this->recalculateBatchBalances((string) $batch->id);

        return [
            'batch' => $batch->fresh(),
            'inserted' => count($parsed['rows']),
            'account_count' => (int) $parsed['account_count'],
        ];
    }

    public function createEntry(array $payload, ?string $actorId): FinanceGeneralLedgerEntry
    {
        $batch = $this->resolveTargetBatch($payload['batch_id'] ?? null, $actorId);
        $sortOrder = $this->nextSortOrder((string) $batch->id, (string) $payload['account_code']);

        $entry = FinanceGeneralLedgerEntry::query()->create([
            'batch_id' => (string) $batch->id,
            'row_type' => (string) $payload['row_type'],
            'entry_date' => (string) $payload['entry_date'],
            'account_code' => (string) $payload['account_code'],
            'account_name' => (string) $payload['account_name'],
            'transaction_no' => $payload['transaction_no'] ?? null,
            'communication' => $payload['communication'] ?? null,
            'partner_name' => $payload['partner_name'] ?? null,
            'currency' => $payload['currency'] ?? 'IDR',
            'label' => $payload['label'] ?? null,
            'reference' => $payload['reference'] ?? null,
            'analytic_distribution' => $payload['analytic_distribution'] ?? null,
            'opening_balance' => strtoupper((string) $payload['row_type']) === FinanceGeneralLedgerEntry::ROW_TYPE_OPENING
                ? round((float) ($payload['opening_balance'] ?? 0), 2)
                : 0,
            'debit' => strtoupper((string) $payload['row_type']) === FinanceGeneralLedgerEntry::ROW_TYPE_ENTRY
                ? round((float) ($payload['debit'] ?? 0), 2)
                : 0,
            'credit' => strtoupper((string) $payload['row_type']) === FinanceGeneralLedgerEntry::ROW_TYPE_ENTRY
                ? round((float) ($payload['credit'] ?? 0), 2)
                : 0,
            'balance_amount' => strtoupper((string) $payload['row_type']) === FinanceGeneralLedgerEntry::ROW_TYPE_OPENING
                ? round((float) ($payload['opening_balance'] ?? 0), 2)
                : 0,
            'sort_order' => $sortOrder,
            'is_manual' => true,
            'meta' => [
                'manually_created' => true,
            ],
            'created_by' => $actorId,
            'updated_by' => $actorId,
        ]);

        $this->recalculateBatchBalances((string) $batch->id);

        return $entry->fresh() ?? $entry;
    }

    public function updateEntry(FinanceGeneralLedgerEntry $entry, array $payload, ?string $actorId): FinanceGeneralLedgerEntry
    {
        $meta = is_array($entry->meta) ? $entry->meta : [];
        $meta['edited_manually'] = true;
        $meta['edited_at'] = now()->toDateTimeString();

        $entry->update([
            'row_type' => (string) $payload['row_type'],
            'entry_date' => (string) $payload['entry_date'],
            'account_code' => (string) $payload['account_code'],
            'account_name' => (string) $payload['account_name'],
            'transaction_no' => $payload['transaction_no'] ?? null,
            'communication' => $payload['communication'] ?? null,
            'partner_name' => $payload['partner_name'] ?? null,
            'currency' => $payload['currency'] ?? 'IDR',
            'label' => $payload['label'] ?? null,
            'reference' => $payload['reference'] ?? null,
            'analytic_distribution' => $payload['analytic_distribution'] ?? null,
            'opening_balance' => strtoupper((string) $payload['row_type']) === FinanceGeneralLedgerEntry::ROW_TYPE_OPENING
                ? round((float) ($payload['opening_balance'] ?? 0), 2)
                : 0,
            'debit' => strtoupper((string) $payload['row_type']) === FinanceGeneralLedgerEntry::ROW_TYPE_ENTRY
                ? round((float) ($payload['debit'] ?? 0), 2)
                : 0,
            'credit' => strtoupper((string) $payload['row_type']) === FinanceGeneralLedgerEntry::ROW_TYPE_ENTRY
                ? round((float) ($payload['credit'] ?? 0), 2)
                : 0,
            'balance_amount' => strtoupper((string) $payload['row_type']) === FinanceGeneralLedgerEntry::ROW_TYPE_OPENING
                ? round((float) ($payload['opening_balance'] ?? 0), 2)
                : 0,
            'is_manual' => true,
            'meta' => $meta,
            'updated_by' => $actorId,
        ]);

        $this->recalculateBatchBalances((string) $entry->batch_id);

        return $entry->fresh() ?? $entry;
    }

    public function deleteEntry(FinanceGeneralLedgerEntry $entry): void
    {
        $batchId = (string) $entry->batch_id;
        $entry->delete();

        $this->recalculateBatchBalances($batchId);
    }

    public function findEntry(?string $entryId): ?FinanceGeneralLedgerEntry
    {
        if (empty($entryId)) {
            return null;
        }

        return FinanceGeneralLedgerEntry::query()->find($entryId);
    }

    public function findBatch(?string $batchId): ?FinanceGeneralLedgerBatch
    {
        if (empty($batchId)) {
            return null;
        }

        return FinanceGeneralLedgerBatch::query()->find($batchId);
    }

    public function recalculateBatchBalances(string $batchId): void
    {
        $entries = FinanceGeneralLedgerEntry::query()
            ->where('batch_id', $batchId)
            ->orderBy('account_code')
            ->orderByRaw("CASE WHEN row_type = 'OPENING' THEN 0 ELSE 1 END")
            ->orderBy('entry_date')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->groupBy('account_code');

        if ($entries->isEmpty()) {
            return;
        }

        $accountTypes = FinanceAccount::query()
            ->whereIn('code', $entries->keys()->all())
            ->pluck('type', 'code');

        foreach ($entries as $accountCode => $accountEntries) {
            $normalSide = $this->resolveImportedNormalSide(
                (string) $accountCode,
                $accountEntries,
                $accountTypes->get($accountCode)
            );

            $runningBalance = 0.0;
            foreach ($accountEntries as $entry) {
                if ($entry->row_type === FinanceGeneralLedgerEntry::ROW_TYPE_OPENING) {
                    $runningBalance = round((float) $entry->opening_balance, 2);
                } else {
                    $runningBalance = $normalSide === 'CREDIT'
                        ? round($runningBalance + ((float) $entry->credit - (float) $entry->debit), 2)
                        : round($runningBalance + ((float) $entry->debit - (float) $entry->credit), 2);
                }

                $entry->forceFill([
                    'balance_amount' => $runningBalance,
                ])->saveQuietly();
            }
        }
    }

    /**
     * @return array{rows:array<int, array<string, mixed>>,account_count:int,imported_year:?int}
     */
    private function parseWorkbookSheet(Worksheet $sheet): array
    {
        $highestRow = $sheet->getHighestRow();
        $importedYear = $this->extractImportedYear($sheet);
        $rows = [];
        $currentAccountCode = null;
        $currentAccountName = null;
        $sortOrder = 0;
        $parsedAccounts = [];

        for ($row = 1; $row <= $highestRow; $row++) {
            $cells = $this->readSheetRow($sheet, $row);
            $accountHeader = $this->parseAccountHeader($cells['A'], $cells['B'], $cells['C']);

            if ($accountHeader !== null) {
                $currentAccountCode = $accountHeader['account_code'];
                $currentAccountName = $accountHeader['account_name'];
                $sortOrder = 0;
                $parsedAccounts[$currentAccountCode] = true;
                continue;
            }

            if ($currentAccountCode === null || $currentAccountName === null) {
                continue;
            }

            if ($this->isOpeningRow($cells['A'], $cells['C'])) {
                $sortOrder++;

                $openingBalance = $this->parseMoneyValue($cells['H']);
                $rows[] = [
                    'row_type' => FinanceGeneralLedgerEntry::ROW_TYPE_OPENING,
                    'entry_date' => $importedYear !== null
                        ? Carbon::create($importedYear, 1, 1)->toDateString()
                        : null,
                    'account_code' => $currentAccountCode,
                    'account_name' => $currentAccountName,
                    'transaction_no' => null,
                    'communication' => 'Saldo Awal',
                    'partner_name' => null,
                    'currency' => $cells['E'] !== '' ? $cells['E'] : 'IDR',
                    'label' => 'Saldo Awal',
                    'reference' => null,
                    'analytic_distribution' => null,
                    'opening_balance' => $openingBalance,
                    'debit' => 0.0,
                    'credit' => 0.0,
                    'balance_amount' => $openingBalance,
                    'sort_order' => $sortOrder,
                    'sheet_row_number' => $row,
                    'meta' => [
                        'source_balance' => $openingBalance,
                    ],
                ];
                continue;
            }

            if (!$this->isTransactionRow($cells)) {
                continue;
            }

            $sortOrder++;
            $sourceBalance = $this->parseMoneyValue($cells['H']);
            $date = $this->parseSpreadsheetDate(
                $sheet->getCell('B' . $row)->getValue(),
                $cells['B'],
                $importedYear
            );

            $rows[] = [
                'row_type' => FinanceGeneralLedgerEntry::ROW_TYPE_ENTRY,
                'entry_date' => $date,
                'account_code' => $currentAccountCode,
                'account_name' => $currentAccountName,
                'transaction_no' => $cells['A'] !== '' ? $cells['A'] : null,
                'communication' => $cells['C'] !== '' ? $cells['C'] : null,
                'partner_name' => $cells['D'] !== '' ? $cells['D'] : null,
                'currency' => $cells['E'] !== '' ? $cells['E'] : 'IDR',
                'label' => $cells['C'] !== '' ? $cells['C'] : $currentAccountName,
                'reference' => null,
                'analytic_distribution' => null,
                'opening_balance' => 0.0,
                'debit' => $this->parseMoneyValue($cells['F']),
                'credit' => $this->parseMoneyValue($cells['G']),
                'balance_amount' => $sourceBalance,
                'sort_order' => $sortOrder,
                'sheet_row_number' => $row,
                'meta' => [
                    'source_balance' => $sourceBalance,
                ],
            ];
        }

        return [
            'rows' => $rows,
            'account_count' => count($parsedAccounts),
            'imported_year' => $importedYear,
        ];
    }

    /**
     * @return array<string, string>
     */
    private function readSheetRow(Worksheet $sheet, int $row): array
    {
        return [
            'A' => trim((string) $sheet->getCell('A' . $row)->getFormattedValue()),
            'B' => trim((string) $sheet->getCell('B' . $row)->getFormattedValue()),
            'C' => trim((string) $sheet->getCell('C' . $row)->getFormattedValue()),
            'D' => trim((string) $sheet->getCell('D' . $row)->getFormattedValue()),
            'E' => trim((string) $sheet->getCell('E' . $row)->getFormattedValue()),
            'F' => trim((string) $sheet->getCell('F' . $row)->getFormattedValue()),
            'G' => trim((string) $sheet->getCell('G' . $row)->getFormattedValue()),
            'H' => trim((string) $sheet->getCell('H' . $row)->getFormattedValue()),
        ];
    }

    /**
     * @return array{account_code:string,account_name:string}|null
     */
    private function parseAccountHeader(string $columnA, string $columnB, string $columnC): ?array
    {
        if ($columnA === '' || $columnB !== '' || $columnC !== '') {
            return null;
        }

        if (!preg_match('/^(?<code>\d{2,3}(?:\.\d{2})+)\s+(?<name>.+)$/', $columnA, $matches)) {
            return null;
        }

        return [
            'account_code' => strtoupper(trim((string) $matches['code'])),
            'account_name' => trim((string) $matches['name']),
        ];
    }

    private function isOpeningRow(string $columnA, string $columnC): bool
    {
        $haystacks = [
            strtolower(trim($columnA)),
            strtolower(trim($columnC)),
        ];

        return in_array('saldo awal', $haystacks, true);
    }

    /**
     * @param array<string, string> $cells
     */
    private function isTransactionRow(array $cells): bool
    {
        if (($cells['A'] ?? '') === '') {
            return false;
        }

        if ($this->isOpeningRow($cells['A'] ?? '', $cells['C'] ?? '')) {
            return false;
        }

        $hasDate = ($cells['B'] ?? '') !== '';
        $hasAmount = $this->parseMoneyValue($cells['F'] ?? '') !== 0.0
            || $this->parseMoneyValue($cells['G'] ?? '') !== 0.0
            || $this->parseMoneyValue($cells['H'] ?? '') !== 0.0;

        return $hasDate || $hasAmount;
    }

    private function extractImportedYear(Worksheet $sheet): ?int
    {
        for ($row = 1; $row <= min(10, $sheet->getHighestRow()); $row++) {
            foreach (['A', 'B', 'C'] as $column) {
                $value = trim((string) $sheet->getCell($column . $row)->getFormattedValue());
                if (preg_match('/\b(19|20)\d{2}\b/', $value, $matches) === 1) {
                    return (int) $matches[0];
                }
            }
        }

        return null;
    }

    private function parseSpreadsheetDate(mixed $rawValue, string $formattedValue, ?int $fallbackYear): ?string
    {
        if (is_numeric($rawValue)) {
            try {
                return SpreadsheetDate::excelToDateTimeObject((float) $rawValue)->format('Y-m-d');
            } catch (\Throwable) {
                // continue below
            }
        }

        $value = trim($formattedValue);
        if ($value === '') {
            return $fallbackYear !== null ? Carbon::create($fallbackYear, 1, 1)->toDateString() : null;
        }

        foreach (['d/m/Y', 'd/m/y', 'Y-m-d', 'd-m-Y'] as $format) {
            try {
                return Carbon::createFromFormat($format, $value)->toDateString();
            } catch (\Throwable) {
                continue;
            }
        }

        try {
            return Carbon::parse($value)->toDateString();
        } catch (\Throwable) {
            return $fallbackYear !== null ? Carbon::create($fallbackYear, 1, 1)->toDateString() : null;
        }
    }

    private function parseMoneyValue(mixed $value): float
    {
        if ($value === null || $value === '') {
            return 0.0;
        }

        if (is_numeric($value)) {
            return round((float) $value, 2);
        }

        $normalized = strtoupper(trim((string) $value));
        if ($normalized === '') {
            return 0.0;
        }

        $isNegative = str_starts_with($normalized, '(') && str_ends_with($normalized, ')');
        $normalized = trim($normalized, '()');
        $normalized = str_replace(['RP', 'IDR', ' '], '', $normalized);
        $normalized = preg_replace('/[^0-9,\.\-]/', '', $normalized) ?? '0';

        if (str_contains($normalized, ',') && str_contains($normalized, '.')) {
            $normalized = str_replace('.', '', $normalized);
            $normalized = str_replace(',', '.', $normalized);
        } elseif (str_contains($normalized, ',')) {
            if (preg_match('/^-?\d{1,3}(,\d{3})+(\.\d+)?$/', $normalized) === 1) {
                $normalized = str_replace(',', '', $normalized);
            } else {
                $normalized = str_replace(',', '.', $normalized);
            }
        }

        $amount = (float) $normalized;

        return round($isNegative ? $amount * -1 : $amount, 2);
    }

    private function resolveSelectedBatch(?string $batchId): ?FinanceGeneralLedgerBatch
    {
        if (!empty($batchId)) {
            $selectedBatch = FinanceGeneralLedgerBatch::query()->find($batchId);
            if ($selectedBatch !== null) {
                return $selectedBatch;
            }
        }

        return FinanceGeneralLedgerBatch::query()
            ->orderByDesc('imported_at')
            ->orderByDesc('created_at')
            ->first();
    }

    private function resolveTargetBatch(?string $batchId, ?string $actorId): FinanceGeneralLedgerBatch
    {
        $batch = $this->findBatch($batchId);
        if ($batch !== null) {
            return $batch;
        }

        return FinanceGeneralLedgerBatch::query()->create([
            'source_type' => FinanceGeneralLedgerBatch::SOURCE_MANUAL,
            'batch_name' => 'Manual Buku Besar',
            'notes' => 'Batch otomatis untuk entry buku besar manual.',
            'imported_at' => now(),
            'created_by' => $actorId,
            'updated_by' => $actorId,
        ]);
    }

    private function nextSortOrder(string $batchId, string $accountCode): int
    {
        $currentMax = FinanceGeneralLedgerEntry::query()
            ->where('batch_id', $batchId)
            ->where('account_code', $accountCode)
            ->max('sort_order');

        return ((int) $currentMax) + 1;
    }

    /**
     * @return array{account_count:int,entry_count:int,total_debit:float,total_credit:float,balance_gap:float}
     */
    private function buildImportedSummary(string $batchId, StatementFilterDTO $filter): array
    {
        $baseQuery = FinanceGeneralLedgerEntry::query()
            ->where('batch_id', $batchId);

        if (!empty($filter->accountCode)) {
            $baseQuery->where('account_code', $filter->accountCode);
        }

        $this->applyImportedLedgerSearch($baseQuery, $filter->search);
        $hasDateFilter = $this->applyImportedLedgerPeriodFilter($baseQuery, $filter);

        $accountCount = (clone $baseQuery)
            ->selectRaw('COUNT(DISTINCT account_code) as total_accounts')
            ->value('total_accounts');

        if ($hasDateFilter) {
            $accountCount = (int) FinanceGeneralLedgerEntry::query()
                ->where('batch_id', $batchId)
                ->when(!empty($filter->accountCode), fn (Builder $query) => $query->where('account_code', $filter->accountCode))
                ->tap(fn (Builder $query) => $this->applyImportedLedgerSearch($query, $filter->search))
                ->tap(fn (Builder $query) => $this->applyImportedLedgerPeriodFilter($query, $filter))
                ->where('row_type', FinanceGeneralLedgerEntry::ROW_TYPE_ENTRY)
                ->selectRaw('COUNT(DISTINCT account_code) as total_accounts')
                ->value('total_accounts');
        }

        $entryCount = (int) (clone $baseQuery)
            ->where('row_type', FinanceGeneralLedgerEntry::ROW_TYPE_ENTRY)
            ->count();
        $totalDebit = round((float) (clone $baseQuery)
            ->where('row_type', FinanceGeneralLedgerEntry::ROW_TYPE_ENTRY)
            ->sum('debit'), 2);
        $totalCredit = round((float) (clone $baseQuery)
            ->where('row_type', FinanceGeneralLedgerEntry::ROW_TYPE_ENTRY)
            ->sum('credit'), 2);

        return [
            'account_count' => (int) $accountCount,
            'entry_count' => $entryCount,
            'total_debit' => $totalDebit,
            'total_credit' => $totalCredit,
            'balance_gap' => round($totalDebit - $totalCredit, 2),
        ];
    }

    private function buildImportedAccountQuery(Builder $visibleQuery, bool $hasDateFilter): Builder
    {
        $query = (clone $visibleQuery)
            ->selectRaw('finance_general_ledger_entries.account_code as account_code')
            ->selectRaw('MAX(finance_general_ledger_entries.account_name) as account_name')
            ->selectRaw("UPPER(COALESCE(MAX(fa.type), '')) as finance_type")
            ->selectRaw('SUM(CASE WHEN finance_general_ledger_entries.row_type = ? THEN finance_general_ledger_entries.debit ELSE 0 END) as total_debit', [FinanceGeneralLedgerEntry::ROW_TYPE_ENTRY])
            ->selectRaw('SUM(CASE WHEN finance_general_ledger_entries.row_type = ? THEN finance_general_ledger_entries.credit ELSE 0 END) as total_credit', [FinanceGeneralLedgerEntry::ROW_TYPE_ENTRY])
            ->leftJoin('finance_accounts as fa', 'fa.code', '=', 'finance_general_ledger_entries.account_code')
            ->groupBy('finance_general_ledger_entries.account_code')
            ->orderBy('finance_general_ledger_entries.account_code');

        if ($hasDateFilter) {
            $query->havingRaw(
                'SUM(CASE WHEN finance_general_ledger_entries.row_type = ? THEN 1 ELSE 0 END) > 0',
                [FinanceGeneralLedgerEntry::ROW_TYPE_ENTRY]
            );
        }

        return $query;
    }

    private function buildVisibleEntryQuery(string $batchId, StatementFilterDTO $filter, array $accountCodes): Builder
    {
        $query = FinanceGeneralLedgerEntry::query()
            ->where('batch_id', $batchId)
            ->whereIn('account_code', $accountCodes);

        $this->applyImportedLedgerSearch($query, $filter->search);
        $this->applyImportedLedgerPeriodFilter($query, $filter);

        return $query
            ->orderBy('account_code')
            ->orderByRaw("CASE WHEN row_type = 'OPENING' THEN 0 ELSE 1 END")
            ->orderBy('entry_date')
            ->orderBy('sort_order')
            ->orderBy('id');
    }

    private function applyImportedLedgerSearch(Builder $query, ?string $search): void
    {
        $keyword = trim((string) $search);
        if ($keyword === '') {
            return;
        }

        $query->where(function (Builder $nestedQuery) use ($keyword): void {
            $likeKeyword = '%' . $keyword . '%';

            $nestedQuery
                ->where('account_code', 'like', $likeKeyword)
                ->orWhere('account_name', 'like', $likeKeyword)
                ->orWhere('transaction_no', 'like', $likeKeyword)
                ->orWhere('communication', 'like', $likeKeyword)
                ->orWhere('partner_name', 'like', $likeKeyword)
                ->orWhere('label', 'like', $likeKeyword)
                ->orWhere('reference', 'like', $likeKeyword)
                ->orWhere('analytic_distribution', 'like', $likeKeyword);
        });
    }

    private function applyImportedLedgerPeriodFilter(Builder $query, StatementFilterDTO $filter): bool
    {
        $hasStartDate = !empty($filter->startDate);
        $hasEndDate = !empty($filter->endDate);

        if (!$hasStartDate && !$hasEndDate) {
            return false;
        }

        if ($hasStartDate) {
            $query->whereDate('entry_date', '>=', $filter->startDate);
        }

        if ($hasEndDate) {
            $query->whereDate('entry_date', '<=', $filter->endDate);
        }

        return true;
    }

    /**
     * @param EloquentCollection<int, FinanceGeneralLedgerEntry> $entries
     */
    private function resolveImportedNormalSide(
        string $accountCode,
        EloquentCollection $entries,
        mixed $financeType
    ): string {
        $normalizedType = strtoupper(trim((string) $financeType));
        if (
            in_array($normalizedType, FinanceAccount::liabilityTypes(), true)
            || in_array($normalizedType, FinanceAccount::incomeTypes(), true)
            || $normalizedType === FinanceAccount::TYPE_EKUITAS
        ) {
            return 'CREDIT';
        }

        if ($normalizedType !== '') {
            return 'DEBIT';
        }

        $sourceRunning = 0.0;
        foreach ($entries as $entry) {
            if ($entry->row_type === FinanceGeneralLedgerEntry::ROW_TYPE_OPENING) {
                $sourceRunning = round((float) $entry->opening_balance, 2);
                continue;
            }

            $sourceBalance = round((float) data_get($entry->meta, 'source_balance', $entry->balance_amount), 2);
            $debitCandidate = round($sourceRunning + ((float) $entry->debit - (float) $entry->credit), 2);
            $creditCandidate = round($sourceRunning + ((float) $entry->credit - (float) $entry->debit), 2);

            if (abs($debitCandidate - $sourceBalance) <= 0.01 && abs($creditCandidate - $sourceBalance) > 0.01) {
                return 'DEBIT';
            }

            if (abs($creditCandidate - $sourceBalance) <= 0.01 && abs($debitCandidate - $sourceBalance) > 0.01) {
                return 'CREDIT';
            }

            $sourceRunning = $sourceBalance;
        }

        return 'DEBIT';
    }

    private function resolveDisplayNormalSide(string $financeType): string
    {
        $normalizedType = strtoupper(trim($financeType));

        if (
            in_array($normalizedType, FinanceAccount::liabilityTypes(), true)
            || in_array($normalizedType, FinanceAccount::incomeTypes(), true)
            || $normalizedType === FinanceAccount::TYPE_EKUITAS
        ) {
            return 'CREDIT';
        }

        return 'DEBIT';
    }

    /**
     * @param array<string, mixed> $extra
     * @return array<string, mixed>
     */
    private function serializeBatch(FinanceGeneralLedgerBatch $batch, array $extra = []): array
    {
        return array_merge([
            'id' => (string) $batch->id,
            'source_type' => (string) $batch->source_type,
            'batch_name' => (string) $batch->batch_name,
            'source_filename' => $batch->source_filename !== null ? (string) $batch->source_filename : null,
            'sheet_name' => $batch->sheet_name !== null ? (string) $batch->sheet_name : null,
            'imported_year' => $batch->imported_year !== null ? (int) $batch->imported_year : null,
            'notes' => $batch->notes !== null ? (string) $batch->notes : null,
            'imported_at' => $batch->imported_at?->toDateTimeString(),
        ], $extra);
    }

    /**
     * @return array{account_count:int,entry_count:int,total_debit:float,total_credit:float,balance_gap:float}
     */
    private function emptySummary(): array
    {
        return [
            'account_count' => 0,
            'entry_count' => 0,
            'total_debit' => 0.0,
            'total_credit' => 0.0,
            'balance_gap' => 0.0,
        ];
    }
}
