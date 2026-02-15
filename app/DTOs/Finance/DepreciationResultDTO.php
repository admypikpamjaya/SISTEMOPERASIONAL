<?php

namespace App\DTOs\Finance;

class DepreciationResultDTO
{
    public function __construct(
        public string $assetId,
        public float $acquisitionCost,
        public int $usefulLifeMonths,
        public float $depreciationPerMonth
    ) {}

    public function toArray(): array
    {
        return [
            'asset_id' => $this->assetId,
            'acquisition_cost' => $this->acquisitionCost,
            'useful_life_months' => $this->usefulLifeMonths,
            'depreciation_per_month' => $this->depreciationPerMonth,
        ];
    }
}
