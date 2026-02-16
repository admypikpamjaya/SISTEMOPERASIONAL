<?php

namespace App\DTOs\Finance;

use Carbon\Carbon;

class ProfitLossReportDetailDTO
{
    /**
     * @param array<int, ProfitLossLineDTO> $incomeLines
     * @param array<int, ProfitLossLineDTO> $expenseLines
     * @param array<int, ProfitLossLineDTO> $depreciationLines
     */
    public function __construct(
        public string $reportId,
        public string $reportType,
        public int $year,
        public ?int $month,
        public float $openingBalance,
        public float $endingBalance,
        public Carbon $generatedAt,
        public ?string $generatedByName,
        public array $incomeLines,
        public array $expenseLines,
        public array $depreciationLines,
        public float $totalIncome,
        public float $totalExpense,
        public float $totalDepreciation,
        public float $surplusDeficit
    ) {}
}
