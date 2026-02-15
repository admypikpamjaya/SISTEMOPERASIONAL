<?php

namespace App\Services\Finance;

use App\DTOs\Finance\ReconciliationResultDTO;

class ReconciliationService
{
    public function calculate(
        float $totalIncome,
        float $totalExpense,
        float $totalDepreciation
    ): ReconciliationResultDTO {
        $netResult = round(
            $totalIncome - $totalExpense - $totalDepreciation,
            2
        );

        return new ReconciliationResultDTO(
            $totalIncome,
            $totalExpense,
            $totalDepreciation,
            $netResult
        );
    }
}
