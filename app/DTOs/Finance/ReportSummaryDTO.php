<?php

namespace App\DTOs\Finance;

class ReportSummaryDTO
{
    public function __construct(
        public int $month,
        public int $year,
        public float $totalIncome,
        public float $totalExpense,
        public float $totalDepreciation,
        public float $netResult
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            (int) $data['month'],
            (int) $data['year'],
            (float) $data['total_income'],
            (float) $data['total_expense'],
            (float) $data['total_depreciation'],
            (float) $data['net_result']
        );
    }

    public function toArray(): array
    {
        return [
            'month' => $this->month,
            'year' => $this->year,
            'total_income' => $this->totalIncome,
            'total_expense' => $this->totalExpense,
            'total_depreciation' => $this->totalDepreciation,
            'net_result' => $this->netResult,
        ];
    }
}
