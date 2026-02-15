<?php

namespace App\Services\Finance;

use App\DTOs\Finance\DepreciationInputDTO;
use App\DTOs\Finance\DepreciationResultDTO;
use InvalidArgumentException;

class DepreciationService
{
    public function calculateStraightLine(DepreciationInputDTO $dto): DepreciationResultDTO
    {
        if ($dto->acquisitionCost < 0) {
            throw new InvalidArgumentException('Nilai perolehan tidak boleh negatif.');
        }

        if ($dto->usefulLifeMonths <= 0) {
            throw new InvalidArgumentException('Umur bulan harus lebih besar dari 0.');
        }

        $depreciationPerMonth = round(
            $dto->acquisitionCost / $dto->usefulLifeMonths,
            2
        );

        return new DepreciationResultDTO(
            $dto->assetId,
            $dto->acquisitionCost,
            $dto->usefulLifeMonths,
            $depreciationPerMonth
        );
    }
}
