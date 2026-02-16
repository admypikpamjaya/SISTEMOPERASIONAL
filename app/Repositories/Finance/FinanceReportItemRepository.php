<?php

namespace App\Repositories\Finance;

use App\Models\FinanceReportItem;

class FinanceReportItemRepository
{
    public function createMany(array $rows): bool
    {
        return FinanceReportItem::query()->insert($rows);
    }
}
