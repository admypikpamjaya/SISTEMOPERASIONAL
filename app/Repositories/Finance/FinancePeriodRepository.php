<?php

namespace App\Repositories\Finance;

use App\Models\FinancePeriod;
use Carbon\Carbon;
use Illuminate\Support\Facades\Schema;

class FinancePeriodRepository
{
    public function findByTypeYearMonthDay(string $periodType, int $year, int $month, int $day = 0): ?FinancePeriod
    {
        $query = FinancePeriod::query()
            ->where('period_type', strtoupper($periodType))
            ->where('year', $year)
            ->where('month', $month);

        if ($this->hasDayColumn()) {
            $query->where('day', $day);
        }

        return $query->first();
    }

    public function create(
        string $periodType,
        int $year,
        int $month,
        int $day,
        Carbon $startDate,
        Carbon $endDate,
        float $openingBalance = 0.0,
        float $closingBalance = 0.0
    ): FinancePeriod {
        $data = [
            'period_type' => strtoupper($periodType),
            'year' => $year,
            'month' => $month,
            'start_date' => $startDate->toDateString(),
            'end_date' => $endDate->toDateString(),
            'status' => 'OPEN',
        ];

        if ($this->hasDayColumn()) {
            $data['day'] = $day;
        }

        if ($this->hasBalanceColumns()) {
            $data['opening_balance'] = round($openingBalance, 2);
            $data['closing_balance'] = round($closingBalance, 2);
        }

        return FinancePeriod::query()->create($data);
    }

    public function updateBalances(string $periodId, float $openingBalance, float $closingBalance): void
    {
        if (!$this->hasBalanceColumns()) {
            return;
        }

        FinancePeriod::query()
            ->whereKey($periodId)
            ->update([
                'opening_balance' => round($openingBalance, 2),
                'closing_balance' => round($closingBalance, 2),
            ]);
    }

    public function getLatestClosingBalanceBefore(
        string $periodType,
        int $year,
        int $month,
        int $day = 0
    ): ?float
    {
        if (!$this->hasBalanceColumns()) {
            return null;
        }

        $periodType = strtoupper($periodType);
        $query = FinancePeriod::query()
            ->where('period_type', strtoupper($periodType))
            ->where(function ($query) use ($year, $month, $day, $periodType) {
                $query->where('year', '<', $year)
                    ->orWhere(function ($subQuery) use ($year, $month) {
                        $subQuery->where('year', $year)
                            ->where('month', '<', $month);
                    });

                if ($periodType === 'DAILY' && $this->hasDayColumn()) {
                    $query->orWhere(function ($subQuery) use ($year, $month, $day) {
                        $subQuery->where('year', $year)
                            ->where('month', $month)
                            ->where('day', '<', $day);
                    });
                }
            })
            ->orderByDesc('year')
            ->orderByDesc('month');

        if ($this->hasDayColumn()) {
            $query->orderByDesc('day');
        }

        $value = $query->value('closing_balance');

        return $value !== null ? (float) $value : null;
    }

    private function hasBalanceColumns(): bool
    {
        return Schema::hasColumn('finance_periods', 'opening_balance')
            && Schema::hasColumn('finance_periods', 'closing_balance');
    }

    private function hasDayColumn(): bool
    {
        return Schema::hasColumn('finance_periods', 'day');
    }
}
