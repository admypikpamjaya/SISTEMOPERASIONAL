<?php

namespace App\DTOs\Finance;

class DepreciationInputDTO
{
    public function __construct(
        public string $assetId,
        public float $acquisitionCost,
        public int $usefulLifeMonths
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            $data['asset_id'],
            (float) $data['acquisition_cost'],
            (int) $data['useful_life_months']
        );
    }
}
