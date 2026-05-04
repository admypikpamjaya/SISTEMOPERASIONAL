<?php

namespace App\Services\Finance;

use App\DTOs\Finance\DepreciationInputDTO;
use App\DTOs\Finance\DepreciationResultDTO;
use InvalidArgumentException;

/**
 * Manual straight-line depreciation calculator.
 *
 * The current implementation is intentionally small: it calculates one monthly
 * depreciation figure from user-supplied acquisition cost and useful life.
 * It is not yet the end-of-period batch engine that reads asset policies and
 * produces posted depreciation history automatically.
 */
class DepreciationService
{
    /**
     * Calculate monthly depreciation using the straight-line method.
     */
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
