<?php

namespace App\DTOs\Finance;

class ReportSummaryDTO
{
    public function __construct(
        public int $month,
        public int $year,
        public int $day,
        public float $totalIncome,
        public float $totalExpense,
        public float $totalDepreciation,
        public float $netResult,
        public float $openingBalance = 0.0,
        public float $endingBalance = 0.0
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            (int) $data['month'],
            (int) $data['year'],
            (int) ($data['day'] ?? 0),
            (float) $data['total_income'],
            (float) $data['total_expense'],
            (float) $data['total_depreciation'],
            (float) $data['net_result'],
            (float) ($data['opening_balance'] ?? 0),
            (float) ($data['ending_balance'] ?? 0)
        );
    }

    public function toArray(): array
    {
        return [
            'month' => $this->month,
            'year' => $this->year,
            'day' => $this->day,
            'total_income' => $this->totalIncome,
            'total_expense' => $this->totalExpense,
            'total_depreciation' => $this->totalDepreciation,
            'net_result' => $this->netResult,
            'opening_balance' => $this->openingBalance,
            'ending_balance' => $this->endingBalance,
        ];
    }
}
