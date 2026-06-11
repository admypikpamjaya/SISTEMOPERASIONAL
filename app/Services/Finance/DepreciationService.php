<?php

namespace App\Services\Finance;

use App\DTOs\Finance\DepreciationInputDTO;
use App\DTOs\Finance\DepreciationResultDTO;
use App\Enums\Asset\AssetCategory;
use App\Models\Asset\Asset;
use App\Models\AssetDepreciation;
use App\Models\FinanceAccount;
use App\Models\FinanceAssetPolicy;
use App\Models\FinanceCategory;
use App\Models\FinanceDepreciationRun;
use App\Models\FinanceInvoice;
use App\Models\FinancePeriod;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class DepreciationService
{
    private const AUTO_RUN_NOTE = 'AUTO_ASSET_DEPRECIATION_POSTING';
    private const AUTO_SOURCE = 'AUTO_ASSET_DEPRECIATION';
    private const AUTO_JOURNAL_NAME = 'Jurnal Penyusutan Aset';

    /**
     * Calculate monthly depreciation using the straight-line method.
     */
    public function calculateStraightLine(DepreciationInputDTO $dto): DepreciationResultDTO
    {
        if ($dto->acquisitionCost < 0) {
            throw new InvalidArgumentException('Nilai perolehan tidak boleh negatif.');
        }

        if ($dto->usefulLifeMonths <= 0) {
            throw new InvalidArgumentException('Umur bulan harus lebih besar dari 0.');
        }

        $depreciationPerMonth = round(
            $dto->acquisitionCost / $dto->usefulLifeMonths,
            2
        );

        return new DepreciationResultDTO(
            $dto->assetId,
            $dto->acquisitionCost,
            $dto->usefulLifeMonths,
            $depreciationPerMonth
        );
    }

    /**
     * @return array{
     *   period: FinancePeriod,
     *   policy: FinanceAssetPolicy,
     *   run: FinanceDepreciationRun,
     *   history: AssetDepreciation,
     *   invoice: FinanceInvoice
     * }
     */
    public function postCalculatedDepreciation(
        Asset $asset,
        DepreciationResultDTO $result,
        CarbonImmutable $periodStart,
        CarbonImmutable $periodEnd,
        string $categoryId
    ): array {
        return DB::transaction(function () use ($asset, $result, $periodStart, $periodEnd, $categoryId): array {
            $period = $this->resolveMonthlyPeriod($periodEnd);
            $policy = $this->resolvePolicy($asset, $result, $periodStart, $period, $categoryId);
            $run = $this->resolveRun($period, $categoryId);
            $history = $this->upsertHistory($run, $period, $policy, $asset, $result, $categoryId);
            $expenseAccount = $this->resolveExpenseAccount($asset, $categoryId);
            $invoice = $this->upsertInvoice($asset, $result, $history, $expenseAccount, $periodStart, $periodEnd, $categoryId);

            $run->update([
                'status' => 'POSTED',
                'assets_count' => (int) AssetDepreciation::query()
                    ->where('depreciation_run_id', $run->id)
                    ->count(),
                'total_depreciation' => round((float) AssetDepreciation::query()
                    ->where('depreciation_run_id', $run->id)
                    ->sum('depreciation_amount'), 2),
                'generated_by' => auth()->id() ? (string) auth()->id() : null,
                'generated_at' => now(),
            ]);

            return [
                'period' => $period,
                'policy' => $policy,
                'run' => $run->fresh(),
                'history' => $history->fresh(),
                'invoice' => $invoice->fresh('items'),
            ];
        });
    }

    private function resolveMonthlyPeriod(CarbonImmutable $periodEnd): FinancePeriod
    {
        return FinancePeriod::query()->firstOrCreate(
            [
                'period_type' => 'MONTHLY',
                'year' => $periodEnd->year,
                'month' => $periodEnd->month,
            ],
            [
                'start_date' => $periodEnd->startOfMonth()->toDateString(),
                'end_date' => $periodEnd->endOfMonth()->toDateString(),
                'status' => 'OPEN',
            ]
        );
    }

    private function resolvePolicy(
        Asset $asset,
        DepreciationResultDTO $result,
        CarbonImmutable $periodStart,
        FinancePeriod $period,
        string $categoryId
    ): FinanceAssetPolicy {
        $latestPolicy = FinanceAssetPolicy::query()
            ->where('asset_id', $asset->id)
            ->orderByDesc('revision_no')
            ->first();

        if ($latestPolicy !== null) {
            $samePolicy = round((float) $latestPolicy->acquisition_cost, 2) === round($result->acquisitionCost, 2)
                && round((float) $latestPolicy->residual_value, 2) === 0.0
                && (int) $latestPolicy->useful_life_months === (int) $result->usefulLifeMonths
                && optional($latestPolicy->depreciation_start_date)->toDateString() === $periodStart->toDateString();

            if ($samePolicy && ($latestPolicy->category_id === null || (string) $latestPolicy->category_id === $categoryId)) {
                if ($latestPolicy->category_id === null) {
                    $latestPolicy->update(['category_id' => $categoryId]);
                }

                return $latestPolicy;
            }
        }

        return FinanceAssetPolicy::query()->create([
            'asset_id' => $asset->id,
            'category_id' => $categoryId,
            'revision_no' => (int) ($latestPolicy?->revision_no ?? 0) + 1,
            'method' => 'STRAIGHT_LINE',
            'acquisition_cost' => round($result->acquisitionCost, 2),
            'residual_value' => 0,
            'useful_life_months' => $result->usefulLifeMonths,
            'depreciation_start_date' => $periodStart->toDateString(),
            'effective_period_id' => $period->id,
            'notes' => 'Auto-generated from depreciation calculator.',
            'created_by' => auth()->id() ? (string) auth()->id() : null,
            'created_at' => now(),
        ]);
    }

    private function resolveRun(FinancePeriod $period, string $categoryId): FinanceDepreciationRun
    {
        $run = FinanceDepreciationRun::query()
            ->where('period_id', $period->id)
            ->where('category_id', $categoryId)
            ->where('notes', self::AUTO_RUN_NOTE)
            ->orderBy('run_no')
            ->first();

        if ($run !== null) {
            return $run;
        }

        $nextRunNo = ((int) FinanceDepreciationRun::query()
            ->where('period_id', $period->id)
            ->max('run_no')) + 1;

        return FinanceDepreciationRun::query()->create([
            'period_id' => $period->id,
            'category_id' => $categoryId,
            'run_no' => $nextRunNo,
            'status' => 'POSTED',
            'assets_count' => 0,
            'total_depreciation' => 0,
            'generated_by' => auth()->id() ? (string) auth()->id() : null,
            'generated_at' => now(),
            'notes' => self::AUTO_RUN_NOTE,
        ]);
    }

    private function upsertHistory(
        FinanceDepreciationRun $run,
        FinancePeriod $period,
        FinanceAssetPolicy $policy,
        Asset $asset,
        DepreciationResultDTO $result,
        string $categoryId
    ): AssetDepreciation {
        $sequenceMonth = max(1, $result->usefulLifeMonths);
        $depreciationAmount = round($result->depreciationPerMonth, 2);
        $accumulatedBefore = round(max(0, ($sequenceMonth - 1) * $depreciationAmount), 2);
        $remainingValue = max(0, round($result->acquisitionCost - $accumulatedBefore, 2));
        $depreciationAmount = min($depreciationAmount, $remainingValue);
        $accumulatedAfter = round($accumulatedBefore + $depreciationAmount, 2);
        $bookValueEnd = round(max(0, $result->acquisitionCost - $accumulatedAfter), 2);

        return AssetDepreciation::query()->updateOrCreate(
            [
                'depreciation_run_id' => $run->id,
                'asset_id' => $asset->id,
            ],
            [
                'period_id' => $period->id,
                'category_id' => $categoryId,
                'policy_id' => $policy->id,
                'method' => 'STRAIGHT_LINE',
                'acquisition_cost_snapshot' => round($result->acquisitionCost, 2),
                'residual_value_snapshot' => 0,
                'useful_life_months_snapshot' => $result->usefulLifeMonths,
                'sequence_month' => $sequenceMonth,
                'accumulated_before' => $accumulatedBefore,
                'depreciation_amount' => $depreciationAmount,
                'accumulated_after' => $accumulatedAfter,
                'book_value_end' => $bookValueEnd,
            ]
        );
    }

    private function resolveExpenseAccount(Asset $asset, string $categoryId): FinanceAccount
    {
        $actorId = auth()->id() ? (string) auth()->id() : null;
        $categorySuffix = $this->buildCategoryCodeSuffix($categoryId);

        $config = match ($asset->category) {
            AssetCategory::AC => [
                'code' => 'DEP-EXP-AC-' . $categorySuffix,
                'name' => 'Beban Penyusutan AC',
            ],
            AssetCategory::COMPUTER => [
                'code' => 'DEP-EXP-COMPUTER-' . $categorySuffix,
                'name' => 'Beban Penyusutan Komputer',
            ],
            default => [
                'code' => 'DEP-EXP-ASSET-' . $categorySuffix,
                'name' => 'Beban Penyusutan Aset',
            ],
        };

        $account = FinanceAccount::query()->firstOrCreate(
            ['code' => $config['code']],
            [
                'category_id' => $categoryId,
                'name' => $config['name'],
                'type' => FinanceAccount::TYPE_PENGELUARAN,
                'class_no' => FinanceAccount::classForType(FinanceAccount::TYPE_PENGELUARAN),
                'is_active' => true,
                'created_by' => $actorId,
                'updated_by' => $actorId,
                'meta' => [
                    'source' => self::AUTO_SOURCE,
                    'category_id' => $categoryId,
                    'asset_category' => $asset->category->value,
                ],
            ]
        );

        $account->update([
            'category_id' => $account->category_id ?: $categoryId,
            'name' => $config['name'],
            'type' => FinanceAccount::TYPE_PENGELUARAN,
            'class_no' => FinanceAccount::classForType(FinanceAccount::TYPE_PENGELUARAN),
            'is_active' => true,
            'updated_by' => $actorId,
            'meta' => array_merge((array) $account->meta, [
                'source' => self::AUTO_SOURCE,
                'category_id' => $categoryId,
                'asset_category' => $asset->category->value,
            ]),
        ]);

        return $account->fresh();
    }

    private function upsertInvoice(
        Asset $asset,
        DepreciationResultDTO $result,
        AssetDepreciation $history,
        FinanceAccount $expenseAccount,
        CarbonInterface $periodStart,
        CarbonInterface $periodEnd,
        string $categoryId
    ): FinanceInvoice {
        $actorId = auth()->id() ? (string) auth()->id() : null;
        $accountingDate = CarbonImmutable::instance($periodEnd)->endOfMonth()->toDateString();
        $amount = round((float) $history->depreciation_amount, 2);
        $reference = $this->buildInvoiceReference($asset->account_code, $periodEnd, $categoryId);
        $invoice = FinanceInvoice::query()
            ->where('reference', $reference)
            ->first();

        $payload = [
            'category_id' => $categoryId,
            'accounting_date' => $accountingDate,
            'entry_type' => 'EXPENSE',
            'journal_name' => self::AUTO_JOURNAL_NAME,
            'reference' => $reference,
            'status' => 'POSTED',
            'total_debit' => $amount,
            'total_credit' => $amount,
            'updated_by' => $actorId,
            'posted_by' => $actorId,
            'posted_at' => now(),
            'meta' => [
                'source' => self::AUTO_SOURCE,
                'category_id' => $categoryId,
                'asset_id' => $asset->id,
                'asset_account_code' => $asset->account_code,
                'policy_id' => $history->policy_id,
                'depreciation_run_id' => $history->depreciation_run_id,
                'depreciation_history_id' => $history->id,
                'period_start' => $periodStart->toDateString(),
                'period_end' => $periodEnd->toDateString(),
            ],
        ];

        if ($invoice === null) {
            $invoice = FinanceInvoice::query()->create(array_merge($payload, [
                'invoice_no' => $this->generateInvoiceNo($accountingDate, 'EXPENSE'),
                'created_by' => $actorId,
            ]));
        } else {
            $invoice->update($payload);
        }

        $label = 'Penyusutan aset ' . $asset->account_code . ' - ' . $asset->category->label();

        $invoice->items()->delete();
        $invoice->items()->createMany([
            [
                'asset_category' => $asset->category->value,
                'account_code' => $expenseAccount->code,
                'partner_name' => null,
                'label' => $label,
                'analytic_distribution' => $asset->location,
                'debit' => $amount,
                'credit' => 0,
                'sort_order' => 1,
                'meta' => [
                    'source' => self::AUTO_SOURCE,
                    'side' => 'DEBIT',
                    'asset_id' => $asset->id,
                    'depreciation_history_id' => $history->id,
                ],
            ],
            [
                'asset_category' => $asset->category->value,
                'account_code' => $asset->account_code,
                'partner_name' => null,
                'label' => $label,
                'analytic_distribution' => $asset->location,
                'debit' => 0,
                'credit' => $amount,
                'sort_order' => 2,
                'meta' => [
                    'source' => self::AUTO_SOURCE,
                    'side' => 'CREDIT',
                    'asset_id' => $asset->id,
                    'depreciation_history_id' => $history->id,
                ],
            ],
        ]);

        return $invoice;
    }

    private function buildInvoiceReference(string $accountCode, CarbonInterface $periodEnd, string $categoryId): string
    {
        $normalizedCode = preg_replace('/[^A-Z0-9]+/', '-', strtoupper($accountCode)) ?? strtoupper($accountCode);
        $normalizedCode = trim($normalizedCode, '-');

        return 'DEP/' . $periodEnd->format('Ym') . '/' . $this->buildCategoryCodeSuffix($categoryId) . '/' . $normalizedCode;
    }

    private function buildCategoryCodeSuffix(string $categoryId): string
    {
        $category = FinanceCategory::query()->find($categoryId);
        $source = $category?->id ?? $categoryId;

        return strtoupper(substr(str_replace('-', '', (string) $source), 0, 10));
    }

    private function generateInvoiceNo(string $accountingDate, string $entryType): string
    {
        $date = CarbonImmutable::parse($accountingDate);
        $prefix = strtoupper($entryType) === 'INCOME' ? 'JMAS' : 'JKEL';
        $base = sprintf('%s/%s/%s/', $prefix, $date->format('Y'), $date->format('m'));

        $latestNo = FinanceInvoice::query()
            ->where('invoice_no', 'like', $base . '%')
            ->lockForUpdate()
            ->orderByDesc('invoice_no')
            ->value('invoice_no');

        $lastSequence = 0;
        if (is_string($latestNo) && preg_match('/(\d{4})$/', $latestNo, $matches)) {
            $lastSequence = (int) $matches[1];
        }

        return $base . str_pad((string) ($lastSequence + 1), 4, '0', STR_PAD_LEFT);
    }
}
