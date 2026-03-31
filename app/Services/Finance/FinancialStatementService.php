<?php

namespace App\Services\Finance;

use App\DTOs\Finance\StatementFilterDTO;
use App\Models\FinanceAccount;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class FinancialStatementService
{
    /**
     * @return array{
     *   sections: array<int, array{key:string,label:string,rows:array<int, array<string, mixed>>,total:float}>,
     *   summary: array<string, float|int>,
     *   uncategorized_count: int
     * }
     */
    public function getBalanceSheetReport(StatementFilterDTO $filter): array
    {
        $rows = $this->makeFilteredItemQuery($filter)
            ->selectRaw('fii.account_code as account_code')
            ->selectRaw("COALESCE(MAX(fa.name), MAX(fii.label), fii.account_code) as account_name")
            ->selectRaw("UPPER(COALESCE(MAX(fa.type), '')) as finance_type")
            ->selectRaw('MAX(a.account_code) as asset_account_code')
            ->selectRaw('SUM(fii.debit) as total_debit')
            ->selectRaw('SUM(fii.credit) as total_credit')
            ->groupBy('fii.account_code')
            ->orderBy('fii.account_code')
            ->get();

        $sections = [
            'liabilitas' => [
                'key' => 'liabilitas',
                'label' => 'Liabilitas',
                'rows' => [],
                'total' => 0.0,
            ],
            'piutang' => [
                'key' => 'piutang',
                'label' => 'Piutang',
                'rows' => [],
                'total' => 0.0,
            ],
            'kas' => [
                'key' => 'kas',
                'label' => 'Kas',
                'rows' => [],
                'total' => 0.0,
            ],
            'aset' => [
                'key' => 'aset',
                'label' => 'Aset',
                'rows' => [],
                'total' => 0.0,
            ],
        ];

        $uncategorizedCount = 0;

        foreach ($rows as $row) {
            $sectionKey = $this->resolveBalanceSheetSection(
                (string) ($row->finance_type ?? ''),
                $row->asset_account_code !== null
            );

            if ($sectionKey === null) {
                $uncategorizedCount++;
                continue;
            }

            $balance = $sectionKey === 'liabilitas'
                ? $this->calculateCreditNormalBalance($row->total_debit, $row->total_credit)
                : $this->calculateDebitNormalBalance($row->total_debit, $row->total_credit);

            if (!$this->hasMeaningfulAmount($balance)) {
                continue;
            }

            $sections[$sectionKey]['rows'][] = [
                'account_code' => (string) $row->account_code,
                'account_name' => (string) $row->account_name,
                'finance_type' => (string) $row->finance_type,
                'balance' => $balance,
            ];
            $sections[$sectionKey]['total'] = round($sections[$sectionKey]['total'] + $balance, 2);
        }

        $kasTotal = (float) $sections['kas']['total'];
        $piutangTotal = (float) $sections['piutang']['total'];
        $asetTotal = (float) $sections['aset']['total'];
        $liabilitasTotal = (float) $sections['liabilitas']['total'];

        return [
            'sections' => array_values($sections),
            'summary' => [
                'kas_total' => $kasTotal,
                'piutang_total' => $piutangTotal,
                'aset_total' => $asetTotal,
                'liabilitas_total' => $liabilitasTotal,
                'asset_side_total' => round($kasTotal + $piutangTotal + $asetTotal, 2),
                'account_count' => collect($sections)->sum(
                    static fn (array $section): int => count($section['rows'])
                ),
            ],
            'uncategorized_count' => $uncategorizedCount,
        ];
    }

    /**
     * @return array{
     *   income_rows: array<int, array<string, mixed>>,
     *   expense_rows: array<int, array<string, mixed>>,
     *   totals: array{income:float,expense:float,net_result:float}
     * }
     */
    public function getProfitLossReport(StatementFilterDTO $filter): array
    {
        $rows = $this->makeFilteredItemQuery($filter)
            ->selectRaw('fii.account_code as account_code')
            ->selectRaw("COALESCE(MAX(fa.name), MAX(fii.label), fii.account_code) as account_name")
            ->selectRaw("UPPER(COALESCE(MAX(fa.type), '')) as finance_type")
            ->selectRaw('SUM(fii.debit) as total_debit')
            ->selectRaw('SUM(fii.credit) as total_credit')
            ->groupBy('fii.account_code')
            ->orderBy('fii.account_code')
            ->get();

        $incomeRows = [];
        $expenseRows = [];
        $totalIncome = 0.0;
        $totalExpense = 0.0;

        foreach ($rows as $row) {
            $financeType = strtoupper(trim((string) ($row->finance_type ?? '')));
            if (in_array($financeType, FinanceAccount::incomeTypes(), true)) {
                $amount = $this->calculateCreditNormalBalance($row->total_debit, $row->total_credit);
                if ($this->hasMeaningfulAmount($amount)) {
                    $incomeRows[] = [
                        'account_code' => (string) $row->account_code,
                        'account_name' => (string) $row->account_name,
                        'finance_type' => $financeType,
                        'amount' => $amount,
                    ];
                    $totalIncome = round($totalIncome + $amount, 2);
                }

                continue;
            }

            if ($financeType !== FinanceAccount::TYPE_PENGELUARAN) {
                continue;
            }

            $amount = $this->calculateDebitNormalBalance($row->total_debit, $row->total_credit);
            if (!$this->hasMeaningfulAmount($amount)) {
                continue;
            }

            $expenseRows[] = [
                'account_code' => (string) $row->account_code,
                'account_name' => (string) $row->account_name,
                'finance_type' => $financeType,
                'amount' => $amount,
            ];
            $totalExpense = round($totalExpense + $amount, 2);
        }

        return [
            'income_rows' => $incomeRows,
            'expense_rows' => $expenseRows,
            'totals' => [
                'income' => $totalIncome,
                'expense' => $totalExpense,
                'net_result' => round($totalIncome - $totalExpense, 2),
            ],
        ];
    }

    /**
     * @return array{
     *   groups: array<int, array<string, mixed>>,
     *   accounts: mixed,
     *   summary: array{account_count:int,entry_count:int,total_debit:float,total_credit:float,balance_gap:float}
     * }
     */
    public function getGeneralLedgerReport(StatementFilterDTO $filter, bool $paginate = true): array
    {
        $accountQuery = $this->makeFilteredItemQuery($filter)
            ->selectRaw('fii.account_code as account_code')
            ->selectRaw("COALESCE(MAX(fa.name), MAX(fii.label), fii.account_code) as account_name")
            ->selectRaw("UPPER(COALESCE(MAX(fa.type), '')) as finance_type")
            ->selectRaw('SUM(fii.debit) as total_debit')
            ->selectRaw('SUM(fii.credit) as total_credit')
            ->groupBy('fii.account_code')
            ->orderBy('fii.account_code');

        $accounts = $paginate
            ? $accountQuery->paginate($filter->perPage, ['*'], 'page', $filter->page)
            : $accountQuery->get();

        $accountRows = $paginate
            ? collect($accounts->items())
            : collect($accounts);

        $accountCodes = $accountRows
            ->map(static fn ($row): string => (string) $row->account_code)
            ->filter()
            ->values()
            ->all();

        $entriesByAccount = collect();
        if (!empty($accountCodes)) {
            $entriesByAccount = $this->makeFilteredItemQuery($filter)
                ->select([
                    'fii.id',
                    'fii.account_code',
                    'fi.accounting_date',
                    'fi.id as invoice_id',
                    'fi.invoice_no',
                    'fi.journal_name',
                    'fi.reference',
                    'fi.entry_type',
                    'fii.label',
                    'fii.partner_name',
                    'fii.analytic_distribution',
                    'fii.debit',
                    'fii.credit',
                    'fii.sort_order',
                ])
                ->selectRaw("COALESCE(fa.name, fii.label, fii.account_code) as account_name")
                ->selectRaw("UPPER(COALESCE(fa.type, '')) as finance_type")
                ->whereIn('fii.account_code', $accountCodes)
                ->orderBy('fii.account_code')
                ->orderBy('fi.accounting_date')
                ->orderBy('fi.invoice_no')
                ->orderBy('fii.sort_order')
                ->orderBy('fii.id')
                ->get()
                ->groupBy('account_code');
        }

        $groups = $accountRows
            ->map(function ($accountRow) use ($entriesByAccount): array {
                $financeType = strtoupper(trim((string) ($accountRow->finance_type ?? '')));
                $normalSide = $this->resolveNormalSide($financeType);
                $runningBalance = 0.0;
                $entryRows = [];

                foreach ($entriesByAccount->get((string) $accountRow->account_code, collect()) as $entry) {
                    $runningBalance = $normalSide === 'CREDIT'
                        ? round($runningBalance + ((float) $entry->credit - (float) $entry->debit), 2)
                        : round($runningBalance + ((float) $entry->debit - (float) $entry->credit), 2);

                    $entryRows[] = [
                        'accounting_date' => (string) $entry->accounting_date,
                        'invoice_id' => (string) $entry->invoice_id,
                        'invoice_no' => (string) $entry->invoice_no,
                        'journal_name' => (string) $entry->journal_name,
                        'reference' => $entry->reference !== null ? (string) $entry->reference : null,
                        'entry_type' => (string) $entry->entry_type,
                        'label' => (string) $entry->label,
                        'partner_name' => $entry->partner_name !== null ? (string) $entry->partner_name : null,
                        'analytic_distribution' => $entry->analytic_distribution !== null
                            ? (string) $entry->analytic_distribution
                            : null,
                        'debit' => round((float) $entry->debit, 2),
                        'credit' => round((float) $entry->credit, 2),
                        'running_balance' => $runningBalance,
                    ];
                }

                return [
                    'account_code' => (string) $accountRow->account_code,
                    'account_name' => (string) $accountRow->account_name,
                    'finance_type' => $financeType,
                    'normal_side' => $normalSide,
                    'total_debit' => round((float) $accountRow->total_debit, 2),
                    'total_credit' => round((float) $accountRow->total_credit, 2),
                    'closing_balance' => round($runningBalance, 2),
                    'entries' => $entryRows,
                ];
            })
            ->values()
            ->all();

        return [
            'groups' => $groups,
            'accounts' => $accounts,
            'summary' => $this->getGeneralLedgerSummary($filter),
        ];
    }

    /**
     * @return array{account_count:int,entry_count:int,total_debit:float,total_credit:float,balance_gap:float}
     */
    public function getGeneralLedgerSummary(StatementFilterDTO $filter): array
    {
        $baseQuery = $this->makeFilteredItemQuery($filter);

        $accountCount = (int) (clone $baseQuery)
            ->selectRaw('COUNT(DISTINCT fii.account_code) as total_accounts')
            ->value('total_accounts');
        $entryCount = (int) (clone $baseQuery)->count('fii.id');
        $totalDebit = round((float) (clone $baseQuery)->sum('fii.debit'), 2);
        $totalCredit = round((float) (clone $baseQuery)->sum('fii.credit'), 2);

        return [
            'account_count' => $accountCount,
            'entry_count' => $entryCount,
            'total_debit' => $totalDebit,
            'total_credit' => $totalCredit,
            'balance_gap' => round($totalDebit - $totalCredit, 2),
        ];
    }

    /**
     * @return array{
     *   balance_sheet: array<string, mixed>,
     *   profit_loss: array<string, mixed>,
     *   general_ledger: array<string, mixed>
     * }
     */
    public function getDashboardSummary(StatementFilterDTO $filter): array
    {
        $balanceSheet = $this->getBalanceSheetReport($filter);
        $profitLoss = $this->getProfitLossReport($filter);

        return [
            'balance_sheet' => [
                'summary' => $balanceSheet['summary'],
                'uncategorized_count' => $balanceSheet['uncategorized_count'],
            ],
            'profit_loss' => [
                'totals' => $profitLoss['totals'],
                'income_count' => count($profitLoss['income_rows']),
                'expense_count' => count($profitLoss['expense_rows']),
            ],
            'general_ledger' => $this->getGeneralLedgerSummary($filter),
        ];
    }

    private function makeFilteredItemQuery(StatementFilterDTO $filter): Builder
    {
        $query = DB::table('finance_invoice_items as fii')
            ->join('finance_invoices as fi', 'fi.id', '=', 'fii.invoice_id')
            ->leftJoin('finance_accounts as fa', 'fa.code', '=', 'fii.account_code')
            ->leftJoin('assets as a', 'a.account_code', '=', 'fii.account_code')
            ->where('fi.status', 'POSTED');

        return $this->applyPeriodFilter($query, $filter);
    }

    private function applyPeriodFilter(Builder $query, StatementFilterDTO $filter): Builder
    {
        if (!empty($filter->startDate) || !empty($filter->endDate)) {
            if (!empty($filter->startDate)) {
                $query->whereDate('fi.accounting_date', '>=', $filter->startDate);
            }

            if (!empty($filter->endDate)) {
                $query->whereDate('fi.accounting_date', '<=', $filter->endDate);
            }
        } else {
            if (!empty($filter->reportDate)) {
                $query->whereDate('fi.accounting_date', $filter->reportDate);
            }

            if ($filter->year !== null) {
                $query->whereYear('fi.accounting_date', $filter->year);
            }

            if ($filter->month !== null) {
                $query->whereMonth('fi.accounting_date', $filter->month);
            }
        }

        if (!empty($filter->accountCode)) {
            $query->where('fii.account_code', $filter->accountCode);
        }

        return $query;
    }

    private function resolveBalanceSheetSection(string $financeType, bool $hasAssetRecord): ?string
    {
        $normalizedType = strtoupper(trim($financeType));

        if ($normalizedType === FinanceAccount::TYPE_KAS) {
            return 'kas';
        }

        if ($normalizedType === FinanceAccount::TYPE_PIUTANG) {
            return 'piutang';
        }

        if ($normalizedType === FinanceAccount::TYPE_ASET || $hasAssetRecord) {
            return 'aset';
        }

        if (in_array($normalizedType, FinanceAccount::liabilityTypes(), true)) {
            return 'liabilitas';
        }

        return null;
    }

    private function resolveNormalSide(string $financeType): string
    {
        if (
            in_array($financeType, FinanceAccount::liabilityTypes(), true)
            || in_array($financeType, FinanceAccount::incomeTypes(), true)
            || $financeType === FinanceAccount::TYPE_EKUITAS
        ) {
            return 'CREDIT';
        }

        return 'DEBIT';
    }

    private function calculateDebitNormalBalance(mixed $debit, mixed $credit): float
    {
        return round((float) $debit - (float) $credit, 2);
    }

    private function calculateCreditNormalBalance(mixed $debit, mixed $credit): float
    {
        return round((float) $credit - (float) $debit, 2);
    }

    private function hasMeaningfulAmount(float $amount): bool
    {
        return abs($amount) >= 0.01;
    }
}
