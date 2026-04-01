<?php

namespace App\Services\Finance;

use App\DTOs\Finance\StatementFilterDTO;
use App\Models\FinanceStatementBatch;
use App\Models\FinanceStatementRow;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use RuntimeException;

class FinanceImportedStatementService
{
    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function getBatchOptions(string $statementType): Collection
    {
        return FinanceStatementBatch::query()
            ->where('statement_type', strtoupper($statementType))
            ->leftJoin('finance_statement_rows as r', 'r.batch_id', '=', 'finance_statement_batches.id')
            ->select('finance_statement_batches.*')
            ->selectRaw('COUNT(r.id) as row_count')
            ->selectRaw('SUM(CASE WHEN r.is_manual = 1 THEN 1 ELSE 0 END) as manual_count')
            ->groupBy([
                'finance_statement_batches.id',
                'finance_statement_batches.statement_type',
                'finance_statement_batches.source_type',
                'finance_statement_batches.batch_name',
                'finance_statement_batches.source_filename',
                'finance_statement_batches.sheet_name',
                'finance_statement_batches.imported_year',
                'finance_statement_batches.notes',
                'finance_statement_batches.meta',
                'finance_statement_batches.imported_at',
                'finance_statement_batches.created_by',
                'finance_statement_batches.updated_by',
                'finance_statement_batches.created_at',
                'finance_statement_batches.updated_at',
            ])
            ->orderByDesc('finance_statement_batches.imported_at')
            ->orderByDesc('finance_statement_batches.created_at')
            ->get()
            ->map(fn ($batch): array => $this->serializeBatch($batch, [
                'row_count' => (int) ($batch->row_count ?? 0),
                'manual_count' => (int) ($batch->manual_count ?? 0),
            ]));
    }

    /**
     * @return array<string, mixed>
     */
    public function getImportedBalanceSheetReport(
        StatementFilterDTO $filter,
        ?string $batchId = null
    ): array {
        $batches = $this->getBatchOptions(FinanceStatementBatch::TYPE_BALANCE_SHEET);
        $selectedBatch = $this->resolveSelectedBatch(FinanceStatementBatch::TYPE_BALANCE_SHEET, $batchId, $filter);

        if ($selectedBatch === null) {
            return [
                'sections' => $this->emptyBalanceSections(),
                'summary' => [
                    'kas_total' => 0,
                    'piutang_total' => 0,
                    'aset_total' => 0,
                    'liabilitas_total' => 0,
                    'asset_side_total' => 0,
                    'account_count' => 0,
                ],
                'uncategorized_count' => 0,
                'uncategorized_rows' => [],
                'uncategorized_summary' => [
                    'profit_loss_count' => 0,
                    'other_count' => 0,
                    'unmapped_count' => 0,
                ],
                'batch' => null,
                'batches' => $batches->all(),
                'imported_rows' => [],
            ];
        }

        $rows = $this->buildRowQuery($selectedBatch->id, $filter)->get();
        $sections = collect($this->emptyBalanceSections())->keyBy('key')->all();
        $uncategorizedRows = [];
        $uncategorizedSummary = [
            'profit_loss_count' => 0,
            'other_count' => 0,
            'unmapped_count' => 0,
        ];

        foreach ($rows as $row) {
            $sectionKey = strtolower((string) ($row->section_key ?? ''));
            $amount = round((float) ($row->amount ?? 0), 2);
            $displayRow = $this->serializeRow($row);

            if (in_array($sectionKey, ['liabilitas', 'piutang', 'kas', 'aset'], true)) {
                if (!$this->hasMeaningfulAmount($amount)) {
                    continue;
                }

                $sections[$sectionKey]['rows'][] = [
                    'id' => $displayRow['id'],
                    'account_code' => $displayRow['account_code'] ?: '-',
                    'account_name' => $displayRow['account_name'],
                    'finance_type' => $displayRow['finance_type'],
                    'balance' => $amount,
                    'group_label' => $displayRow['group_label'],
                    'is_manual' => $displayRow['is_manual'],
                ];
                $sections[$sectionKey]['total'] = round($sections[$sectionKey]['total'] + $amount, 2);
                continue;
            }

            $summaryKey = $sectionKey === '' ? 'unmapped_count' : 'other_count';
            $uncategorizedSummary[$summaryKey]++;
            $uncategorizedRows[] = [
                'id' => $displayRow['id'],
                'account_code' => $displayRow['account_code'] ?: '-',
                'account_name' => $displayRow['account_name'],
                'finance_type' => $displayRow['finance_type'],
                'entry_count' => 1,
                'total_debit' => 0.0,
                'total_credit' => 0.0,
                'current_statement' => 'Import Excel',
                'current_section' => $displayRow['section_label'] ?: ($displayRow['group_label'] ?: 'Belum terpetakan'),
                'summary_key' => $summaryKey,
                'reason' => 'Baris import ini belum masuk kategori liabilitas, piutang, kas, atau aset.',
                'amount' => $amount,
                'group_label' => $displayRow['group_label'],
                'is_manual' => $displayRow['is_manual'],
            ];
        }

        $kasTotal = (float) ($sections['kas']['total'] ?? 0);
        $piutangTotal = (float) ($sections['piutang']['total'] ?? 0);
        $asetTotal = (float) ($sections['aset']['total'] ?? 0);
        $liabilitasTotal = (float) ($sections['liabilitas']['total'] ?? 0);

        return [
            'sections' => array_values($sections),
            'summary' => [
                'kas_total' => $kasTotal,
                'piutang_total' => $piutangTotal,
                'aset_total' => $asetTotal,
                'liabilitas_total' => $liabilitasTotal,
                'asset_side_total' => round($kasTotal + $piutangTotal + $asetTotal, 2),
                'account_count' => collect($sections)->sum(
                    static fn (array $section): int => count($section['rows'] ?? [])
                ),
            ],
            'uncategorized_count' => count($uncategorizedRows),
            'uncategorized_rows' => $uncategorizedRows,
            'uncategorized_summary' => $uncategorizedSummary,
            'batch' => $this->serializeBatch($selectedBatch),
            'batches' => $batches->all(),
            'imported_rows' => $rows->map(fn (FinanceStatementRow $row): array => $this->serializeRow($row))->all(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function getImportedProfitLossReport(
        StatementFilterDTO $filter,
        ?string $batchId = null
    ): array {
        $batches = $this->getBatchOptions(FinanceStatementBatch::TYPE_PROFIT_LOSS);
        $selectedBatch = $this->resolveSelectedBatch(FinanceStatementBatch::TYPE_PROFIT_LOSS, $batchId, $filter);

        if ($selectedBatch === null) {
            return [
                'income_rows' => [],
                'expense_rows' => [],
                'totals' => [
                    'income' => 0,
                    'expense' => 0,
                    'net_result' => 0,
                ],
                'batch' => null,
                'batches' => $batches->all(),
                'imported_rows' => [],
            ];
        }

        $rows = $this->buildRowQuery($selectedBatch->id, $filter)->get();
        $incomeRows = [];
        $expenseRows = [];
        $totalIncome = 0.0;
        $totalExpense = 0.0;

        foreach ($rows as $row) {
            $serialized = $this->serializeRow($row);
            $amount = round((float) ($row->amount ?? 0), 2);
            $displayRow = [
                'id' => $serialized['id'],
                'account_code' => $serialized['account_code'] ?: '-',
                'account_name' => $serialized['account_name'],
                'finance_type' => $serialized['finance_type'],
                'amount' => $amount,
                'group_label' => $serialized['group_label'],
                'is_manual' => $serialized['is_manual'],
            ];

            if (strtolower((string) ($row->section_key ?? '')) === 'expense') {
                $expenseRows[] = $displayRow;
                $totalExpense = round($totalExpense + abs($amount), 2);
                continue;
            }

            $incomeRows[] = $displayRow;
            $totalIncome = round($totalIncome + $amount, 2);
        }

        return [
            'income_rows' => $incomeRows,
            'expense_rows' => $expenseRows,
            'totals' => [
                'income' => $totalIncome,
                'expense' => $totalExpense,
                'net_result' => round($totalIncome - $totalExpense, 2),
            ],
            'batch' => $this->serializeBatch($selectedBatch),
            'batches' => $batches->all(),
            'imported_rows' => $rows->map(fn (FinanceStatementRow $row): array => $this->serializeRow($row))->all(),
        ];
    }

    /**
     * @return array{batch:FinanceStatementBatch,inserted:int,row_count:int}
     */
    public function importFromExcel(
        string $statementType,
        string $path,
        string $originalName,
        ?string $batchName,
        ?string $notes,
        ?string $actorId
    ): array {
        $spreadsheet = IOFactory::load($path);
        $sheet = $spreadsheet->getSheet(0);
        $statementType = strtoupper($statementType);

        $parsed = match ($statementType) {
            FinanceStatementBatch::TYPE_BALANCE_SHEET => $this->parseBalanceSheetWorkbook($sheet),
            FinanceStatementBatch::TYPE_PROFIT_LOSS => $this->parseProfitLossWorkbook($sheet),
            default => throw new RuntimeException('Jenis laporan import tidak dikenali.'),
        };

        if (empty($parsed['rows'])) {
            throw new RuntimeException('Tidak ada baris laporan yang bisa dibaca dari file Excel.');
        }

        $resolvedBatchName = $batchName ?: pathinfo($originalName, PATHINFO_FILENAME);
        $now = now();

        $batch = DB::transaction(function () use (
            $statementType,
            $resolvedBatchName,
            $originalName,
            $notes,
            $actorId,
            $parsed,
            $sheet,
            $now
        ) {
            $batch = FinanceStatementBatch::query()->create([
                'statement_type' => $statementType,
                'source_type' => FinanceStatementBatch::SOURCE_IMPORT,
                'batch_name' => $resolvedBatchName,
                'source_filename' => $originalName,
                'sheet_name' => $sheet->getTitle(),
                'imported_year' => $parsed['imported_year'],
                'notes' => $notes,
                'meta' => [
                    'sheet_title' => $sheet->getTitle(),
                    'parsed_rows' => count($parsed['rows']),
                ],
                'imported_at' => $now,
                'created_by' => $actorId,
                'updated_by' => $actorId,
            ]);

            $chunks = array_chunk($parsed['rows'], 300);
            foreach ($chunks as $chunk) {
                DB::table('finance_statement_rows')->insert(
                    array_map(function (array $row) use ($batch, $actorId, $now): array {
                        return [
                            'id' => (string) Str::uuid(),
                            'batch_id' => (string) $batch->id,
                            'section_key' => $row['section_key'],
                            'section_label' => $row['section_label'],
                            'group_label' => $row['group_label'],
                            'account_code' => $row['account_code'],
                            'account_name' => $row['account_name'],
                            'finance_type' => $row['finance_type'],
                            'amount' => $row['amount'],
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

        return [
            'batch' => $batch->fresh(),
            'inserted' => count($parsed['rows']),
            'row_count' => count($parsed['rows']),
        ];
    }

    public function createRow(string $statementType, array $payload, ?string $actorId): FinanceStatementRow
    {
        $statementType = strtoupper($statementType);
        $batch = $this->resolveTargetBatch($statementType, $payload['batch_id'] ?? null, $actorId);

        $row = FinanceStatementRow::query()->create([
            'batch_id' => (string) $batch->id,
            'section_key' => strtolower((string) $payload['section_key']),
            'section_label' => $payload['section_label'] ?: $this->resolveSectionLabel($statementType, (string) $payload['section_key']),
            'group_label' => $payload['group_label'] ?? null,
            'account_code' => $payload['account_code'] ?? null,
            'account_name' => (string) $payload['account_name'],
            'finance_type' => $payload['finance_type'] ?? null,
            'amount' => round((float) ($payload['amount'] ?? 0), 2),
            'sort_order' => $this->nextSortOrder((string) $batch->id),
            'is_manual' => true,
            'meta' => [
                'manually_created' => true,
            ],
            'created_by' => $actorId,
            'updated_by' => $actorId,
        ]);

        return $row->fresh() ?? $row;
    }

    public function updateRow(FinanceStatementRow $row, string $statementType, array $payload, ?string $actorId): FinanceStatementRow
    {
        $statementType = strtoupper($statementType);
        $meta = is_array($row->meta) ? $row->meta : [];
        $meta['edited_manually'] = true;
        $meta['edited_at'] = now()->toDateTimeString();

        $row->update([
            'section_key' => strtolower((string) $payload['section_key']),
            'section_label' => $payload['section_label'] ?: $this->resolveSectionLabel($statementType, (string) $payload['section_key']),
            'group_label' => $payload['group_label'] ?? null,
            'account_code' => $payload['account_code'] ?? null,
            'account_name' => (string) $payload['account_name'],
            'finance_type' => $payload['finance_type'] ?? null,
            'amount' => round((float) ($payload['amount'] ?? 0), 2),
            'is_manual' => true,
            'meta' => $meta,
            'updated_by' => $actorId,
        ]);

        return $row->fresh() ?? $row;
    }

    public function deleteRow(FinanceStatementRow $row): void
    {
        $row->delete();
    }

    public function findRow(?string $rowId): ?FinanceStatementRow
    {
        if (empty($rowId)) {
            return null;
        }

        return FinanceStatementRow::query()->find($rowId);
    }

    public function findBatch(?string $batchId): ?FinanceStatementBatch
    {
        if (empty($batchId)) {
            return null;
        }

        return FinanceStatementBatch::query()->find($batchId);
    }

    private function resolveTargetBatch(string $statementType, ?string $batchId, ?string $actorId): FinanceStatementBatch
    {
        if (!empty($batchId)) {
            $batch = FinanceStatementBatch::query()
                ->where('statement_type', $statementType)
                ->find($batchId);

            if ($batch !== null) {
                return $batch;
            }
        }

        return FinanceStatementBatch::query()->create([
            'statement_type' => $statementType,
            'source_type' => FinanceStatementBatch::SOURCE_MANUAL,
            'batch_name' => $this->defaultManualBatchName($statementType),
            'notes' => 'Dibuat otomatis dari input manual.',
            'imported_year' => now()->year,
            'meta' => [
                'auto_created' => true,
            ],
            'imported_at' => now(),
            'created_by' => $actorId,
            'updated_by' => $actorId,
        ]);
    }

    private function defaultManualBatchName(string $statementType): string
    {
        return match ($statementType) {
            FinanceStatementBatch::TYPE_BALANCE_SHEET => 'Manual Lembar Saldo ' . now()->format('Y-m-d H:i'),
            FinanceStatementBatch::TYPE_PROFIT_LOSS => 'Manual Laba Rugi ' . now()->format('Y-m-d H:i'),
            default => 'Manual Statement ' . now()->format('Y-m-d H:i'),
        };
    }

    private function nextSortOrder(string $batchId): int
    {
        return ((int) FinanceStatementRow::query()
            ->where('batch_id', $batchId)
            ->max('sort_order')) + 1;
    }

    private function resolveSelectedBatch(
        string $statementType,
        ?string $batchId,
        StatementFilterDTO $filter
    ): ?FinanceStatementBatch {
        $query = FinanceStatementBatch::query()
            ->where('statement_type', $statementType);

        if (!empty($batchId)) {
            return $query->find($batchId);
        }

        if ($filter->startYear !== null && $filter->endYear !== null) {
            $query->whereBetween('imported_year', [$filter->startYear, $filter->endYear]);
        } elseif ($filter->startYear !== null) {
            $query->where('imported_year', $filter->startYear);
        }

        return $query
            ->orderByDesc('imported_year')
            ->orderByDesc('imported_at')
            ->orderByDesc('created_at')
            ->first();
    }

    private function buildRowQuery(string $batchId, StatementFilterDTO $filter)
    {
        $query = FinanceStatementRow::query()
            ->where('batch_id', $batchId);

        if (!empty($filter->accountCode)) {
            $query->where(function ($builder) use ($filter): void {
                $builder
                    ->where('account_code', $filter->accountCode)
                    ->orWhere('account_name', 'like', '%' . $filter->accountCode . '%');
            });
        }

        if (!empty($filter->search)) {
            $search = $filter->search;
            $query->where(function ($builder) use ($search): void {
                $builder
                    ->where('account_code', 'like', '%' . $search . '%')
                    ->orWhere('account_name', 'like', '%' . $search . '%')
                    ->orWhere('group_label', 'like', '%' . $search . '%')
                    ->orWhere('section_label', 'like', '%' . $search . '%');
            });
        }

        return $query
            ->orderBy('sort_order')
            ->orderBy('sheet_row_number')
            ->orderBy('id');
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function emptyBalanceSections(): array
    {
        return [
            ['key' => 'liabilitas', 'label' => 'Liabilitas', 'rows' => [], 'total' => 0.0],
            ['key' => 'piutang', 'label' => 'Piutang', 'rows' => [], 'total' => 0.0],
            ['key' => 'kas', 'label' => 'Kas', 'rows' => [], 'total' => 0.0],
            ['key' => 'aset', 'label' => 'Aset', 'rows' => [], 'total' => 0.0],
        ];
    }

    /**
     * @return array{rows:array<int, array<string, mixed>>, imported_year:?int}
     */
    private function parseBalanceSheetWorkbook(Worksheet $sheet): array
    {
        $highestRow = $sheet->getHighestRow();
        $importedYear = $this->extractImportedYear($sheet);
        $rows = [];
        $currentMajor = null;
        $currentGroup = null;
        $sortOrder = 0;

        for ($row = 1; $row <= $highestRow; $row++) {
            $label = trim((string) $sheet->getCell('A' . $row)->getFormattedValue());
            $amountRaw = trim((string) $sheet->getCell('B' . $row)->getFormattedValue());

            if ($label === '' && $amountRaw === '') {
                continue;
            }

            if (
                $label === ''
                || strtoupper($label) === 'LAPORAN POSISI KEUANGAN'
                || strtoupper($label) === 'SALDO'
                || preg_match('/^\d{4}$/', $label) === 1
            ) {
                continue;
            }

            $major = $this->detectBalanceSheetMajorSection($label);
            if ($major !== null) {
                $currentMajor = $major;
                $currentGroup = $label;
                continue;
            }

            if ($label === 'LIABILITAS + ASET NETO' || $currentMajor === null) {
                continue;
            }

            $accountParts = $this->splitAccountLabel($label);
            if ($accountParts !== null) {
                $sortOrder++;
                $sectionKey = $this->resolveImportedBalanceSection(
                    $currentMajor,
                    $currentGroup,
                    $accountParts['code'],
                    $accountParts['name']
                );

                $rows[] = [
                    'section_key' => $sectionKey,
                    'section_label' => $this->resolveSectionLabel(FinanceStatementBatch::TYPE_BALANCE_SHEET, $sectionKey),
                    'group_label' => $currentGroup,
                    'account_code' => $accountParts['code'],
                    'account_name' => $accountParts['name'],
                    'finance_type' => $this->resolveFinanceTypeForBalanceSection($sectionKey),
                    'amount' => $this->parseMoneyValue($amountRaw),
                    'sort_order' => $sortOrder,
                    'sheet_row_number' => $row,
                    'meta' => [
                        'original_label' => $label,
                        'source_major' => $currentMajor,
                        'source_group' => $currentGroup,
                    ],
                ];
                continue;
            }

            if ($currentMajor === 'ASET_NETO') {
                if (strcasecmp($label, 'Aset Netto') === 0) {
                    $currentGroup = $label;
                    continue;
                }

                $sortOrder++;
                $rows[] = [
                    'section_key' => 'other',
                    'section_label' => 'Aset Neto',
                    'group_label' => $currentGroup ?: 'Aset Neto',
                    'account_code' => null,
                    'account_name' => $label,
                    'finance_type' => null,
                    'amount' => $this->parseMoneyValue($amountRaw),
                    'sort_order' => $sortOrder,
                    'sheet_row_number' => $row,
                    'meta' => [
                        'original_label' => $label,
                        'source_major' => $currentMajor,
                        'source_group' => $currentGroup,
                    ],
                ];
                continue;
            }

            $currentGroup = $label;
        }

        return [
            'rows' => $rows,
            'imported_year' => $importedYear,
        ];
    }

    /**
     * @return array{rows:array<int, array<string, mixed>>, imported_year:?int}
     */
    private function parseProfitLossWorkbook(Worksheet $sheet): array
    {
        $highestRow = $sheet->getHighestRow();
        $importedYear = $this->extractImportedYear($sheet);
        $rows = [];
        $currentSection = null;
        $currentGroup = null;
        $sortOrder = 0;

        for ($row = 1; $row <= $highestRow; $row++) {
            $label = trim((string) $sheet->getCell('A' . $row)->getFormattedValue());
            $amountRaw = trim((string) $sheet->getCell('B' . $row)->getFormattedValue());

            if ($label === '' && $amountRaw === '') {
                continue;
            }

            if (
                $label === ''
                || strtoupper($label) === 'LABA RUGI'
                || strtoupper($label) === 'SALDO'
                || preg_match('/^\d{4}$/', $label) === 1
            ) {
                continue;
            }

            if (strcasecmp($label, 'Penghasilan') === 0) {
                $currentSection = 'income';
                $currentGroup = $label;
                continue;
            }

            if (strcasecmp($label, 'Pengeluaran') === 0) {
                $currentSection = 'expense';
                $currentGroup = $label;
                continue;
            }

            if ($currentSection === null || in_array($label, ['Laba Bruto', 'Surplus (Defisit)'], true)) {
                continue;
            }

            $accountParts = $this->splitAccountLabel($label);
            if ($accountParts !== null) {
                $sortOrder++;
                $amount = $this->parseMoneyValue($amountRaw);
                $resolvedSection = $this->resolveImportedProfitLossSection(
                    $currentSection,
                    $currentGroup,
                    $accountParts['name'],
                    $amount
                );

                $rows[] = [
                    'section_key' => $resolvedSection,
                    'section_label' => $this->resolveSectionLabel(FinanceStatementBatch::TYPE_PROFIT_LOSS, $resolvedSection),
                    'group_label' => $currentGroup,
                    'account_code' => $accountParts['code'],
                    'account_name' => $accountParts['name'],
                    'finance_type' => $resolvedSection === 'expense' ? 'PENGELUARAN' : 'PENGHASILAN',
                    'amount' => $resolvedSection === 'expense' ? abs($amount) : $amount,
                    'sort_order' => $sortOrder,
                    'sheet_row_number' => $row,
                    'meta' => [
                        'original_label' => $label,
                        'source_group' => $currentGroup,
                    ],
                ];
                continue;
            }

            $currentGroup = $label;
        }

        return [
            'rows' => $rows,
            'imported_year' => $importedYear,
        ];
    }

    private function detectBalanceSheetMajorSection(string $label): ?string
    {
        return match (strtoupper(trim($label))) {
            'ASET' => 'ASET',
            'LIABILITAS' => 'LIABILITAS',
            'ASET NETO' => 'ASET_NETO',
            default => null,
        };
    }

    /**
     * @return array{code:string,name:string}|null
     */
    private function splitAccountLabel(string $label): ?array
    {
        if (preg_match('/^([0-9]+(?:\.[0-9]+)*)\s+(.+)$/u', trim($label), $matches) !== 1) {
            return null;
        }

        return [
            'code' => strtoupper(trim($matches[1])),
            'name' => trim($matches[2]),
        ];
    }

    private function resolveImportedBalanceSection(
        string $majorSection,
        ?string $groupLabel,
        string $accountCode,
        string $accountName
    ): string {
        if ($majorSection === 'LIABILITAS') {
            return 'liabilitas';
        }

        if ($majorSection !== 'ASET') {
            return 'other';
        }

        $haystack = strtoupper(trim(($groupLabel ?? '') . ' ' . $accountName . ' ' . $accountCode));

        if (str_contains($haystack, 'PIUTANG') || str_starts_with($accountCode, '120')) {
            return 'piutang';
        }

        if (
            str_contains($haystack, 'KAS')
            || str_contains($haystack, 'BANK')
            || str_contains($haystack, 'DEPOSITO')
            || str_starts_with($accountCode, '100')
            || str_starts_with($accountCode, '110')
        ) {
            return 'kas';
        }

        return 'aset';
    }

    private function resolveImportedProfitLossSection(
        string $currentSection,
        ?string $groupLabel,
        string $accountName,
        float $amount
    ): string {
        $haystack = strtoupper(trim(($groupLabel ?? '') . ' ' . $accountName));

        if (
            $currentSection === 'expense'
            || str_contains($haystack, 'BIAYA')
            || $amount < 0
        ) {
            return 'expense';
        }

        return 'income';
    }

    private function resolveFinanceTypeForBalanceSection(string $sectionKey): ?string
    {
        return match ($sectionKey) {
            'liabilitas' => 'PASIVA_TERKINI',
            'piutang' => 'PIUTANG',
            'kas' => 'KAS',
            'aset' => 'ASET',
            default => null,
        };
    }

    private function resolveSectionLabel(string $statementType, string $sectionKey): string
    {
        return match ([$statementType, strtolower($sectionKey)]) {
            [FinanceStatementBatch::TYPE_BALANCE_SHEET, 'liabilitas'] => 'Liabilitas',
            [FinanceStatementBatch::TYPE_BALANCE_SHEET, 'piutang'] => 'Piutang',
            [FinanceStatementBatch::TYPE_BALANCE_SHEET, 'kas'] => 'Kas',
            [FinanceStatementBatch::TYPE_BALANCE_SHEET, 'aset'] => 'Aset',
            [FinanceStatementBatch::TYPE_BALANCE_SHEET, 'other'] => 'Lainnya',
            [FinanceStatementBatch::TYPE_PROFIT_LOSS, 'income'] => 'Pemasukan',
            [FinanceStatementBatch::TYPE_PROFIT_LOSS, 'expense'] => 'Pengeluaran',
            default => ucwords(str_replace('_', ' ', strtolower($sectionKey))),
        };
    }

    private function extractImportedYear(Worksheet $sheet): ?int
    {
        for ($row = 1; $row <= min(12, $sheet->getHighestRow()); $row++) {
            $value = trim((string) $sheet->getCell('A' . $row)->getFormattedValue());
            if (preg_match('/\b(19|20)\d{2}\b/', $value, $matches) === 1) {
                return (int) $matches[0];
            }
        }

        return null;
    }

    private function parseMoneyValue(mixed $value): float
    {
        if ($value === null) {
            return 0.0;
        }

        if (is_numeric($value)) {
            return round((float) $value, 2);
        }

        $text = trim((string) $value);
        if ($text === '' || $text === '-') {
            return 0.0;
        }

        $negative = false;
        if (str_starts_with($text, '(') && str_ends_with($text, ')')) {
            $negative = true;
            $text = trim($text, '()');
        }

        $text = str_replace(['Rp', 'rp', ' '], '', $text);
        $text = preg_replace('/[^0-9,.\-]/', '', $text) ?? '0';

        $commaCount = substr_count($text, ',');
        $dotCount = substr_count($text, '.');

        if ($commaCount > 0 && $dotCount > 0) {
            $lastComma = strrpos($text, ',');
            $lastDot = strrpos($text, '.');
            if ($lastComma !== false && $lastDot !== false && $lastComma > $lastDot) {
                $text = str_replace('.', '', $text);
                $text = str_replace(',', '.', $text);
            } else {
                $text = str_replace(',', '', $text);
            }
        } elseif ($commaCount > 0) {
            $lastComma = strrpos($text, ',');
            $fractionLength = $lastComma === false ? 0 : strlen($text) - $lastComma - 1;
            if ($commaCount > 1 || $fractionLength > 2) {
                $text = str_replace(',', '', $text);
            } else {
                $text = str_replace(',', '.', $text);
            }
        } elseif ($dotCount > 0) {
            $lastDot = strrpos($text, '.');
            $fractionLength = $lastDot === false ? 0 : strlen($text) - $lastDot - 1;
            if ($dotCount > 1 || $fractionLength > 2) {
                $text = str_replace('.', '', $text);
            }
        }

        if ($text === '' || $text === '-') {
            return 0.0;
        }

        $amount = (float) $text;

        return round($negative ? -1 * abs($amount) : $amount, 2);
    }

    private function hasMeaningfulAmount(float $amount): bool
    {
        return abs($amount) > 0.004;
    }

    /**
     * @param array<string, int> $extra
     * @return array<string, mixed>
     */
    private function serializeBatch(FinanceStatementBatch $batch, array $extra = []): array
    {
        return array_merge([
            'id' => (string) $batch->id,
            'statement_type' => (string) $batch->statement_type,
            'source_type' => (string) $batch->source_type,
            'batch_name' => (string) $batch->batch_name,
            'source_filename' => $batch->source_filename !== null ? (string) $batch->source_filename : null,
            'sheet_name' => $batch->sheet_name !== null ? (string) $batch->sheet_name : null,
            'imported_year' => $batch->imported_year !== null ? (int) $batch->imported_year : null,
            'notes' => $batch->notes !== null ? (string) $batch->notes : null,
            'imported_at' => $batch->imported_at !== null ? $batch->imported_at->toDateTimeString() : null,
        ], $extra);
    }

    /**
     * @return array<string, mixed>
     */
    private function serializeRow(FinanceStatementRow $row): array
    {
        return [
            'id' => (string) $row->id,
            'batch_id' => (string) $row->batch_id,
            'section_key' => $row->section_key !== null ? (string) $row->section_key : null,
            'section_label' => $row->section_label !== null ? (string) $row->section_label : null,
            'group_label' => $row->group_label !== null ? (string) $row->group_label : null,
            'account_code' => $row->account_code !== null ? (string) $row->account_code : null,
            'account_name' => (string) $row->account_name,
            'finance_type' => $row->finance_type !== null ? (string) $row->finance_type : '',
            'amount' => round((float) $row->amount, 2),
            'sort_order' => (int) ($row->sort_order ?? 0),
            'sheet_row_number' => $row->sheet_row_number !== null ? (int) $row->sheet_row_number : null,
            'is_manual' => (bool) $row->is_manual,
        ];
    }
}
