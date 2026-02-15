<?php

namespace App\DTOs\Finance;

class ReconciliationResultDTO
{
    public function __construct(
        public float $totalIncome,
        public float $totalExpense,
        public float $totalDepreciation,
        public float $netResult
    ) {}

    public function toArray(): array
    {
        return [
            'total_income' => $this->totalIncome,
            'total_expense' => $this->totalExpense,
            'total_depreciation' => $this->totalDepreciation,
            'net_result' => $this->netResult,
        ];
    }
}
