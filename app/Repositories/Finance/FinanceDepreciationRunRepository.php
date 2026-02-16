<?php

namespace App\Repositories\Finance;

use App\Models\FinanceDepreciationRun;

class FinanceDepreciationRunRepository
{
    public function getNextRunNumber(string $periodId): int
    {
        $latest = FinanceDepreciationRun::query()
            ->where('period_id', $periodId)
            ->max('run_no');

        return ((int) $latest) + 1;
    }

    public function create(array $data): FinanceDepreciationRun
    {
        return FinanceDepreciationRun::query()->create($data);
    }
}
