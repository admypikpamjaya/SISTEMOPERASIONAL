<?php

namespace App\Repositories\Finance;

use App\Models\FinancePeriod;
use Carbon\Carbon;

class FinancePeriodRepository
{
    public function findByTypeYearMonth(string $periodType, int $year, int $month): ?FinancePeriod
    {
        return FinancePeriod::query()
            ->where('period_type', strtoupper($periodType))
            ->where('year', $year)
            ->where('month', $month)
            ->first();
    }

    public function create(
        string $periodType,
        int $year,
        int $month,
        Carbon $startDate,
        Carbon $endDate
    ): FinancePeriod {
        return FinancePeriod::query()->create([
            'period_type' => strtoupper($periodType),
            'year' => $year,
            'month' => $month,
            'start_date' => $startDate->toDateString(),
            'end_date' => $endDate->toDateString(),
            'status' => 'OPEN',
        ]);
    }
}
