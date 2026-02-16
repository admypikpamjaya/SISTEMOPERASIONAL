<?php

namespace App\Repositories\Finance;

use App\Models\FinanceReconciliationSnapshot;

class FinanceReconciliationSnapshotRepository
{
    public function create(array $data): FinanceReconciliationSnapshot
    {
        return FinanceReconciliationSnapshot::query()->create($data);
    }
}
