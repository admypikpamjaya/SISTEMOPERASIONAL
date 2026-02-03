<?php 

namespace App\DTOs\Asset;

use App\Enums\Asset\AssetUnit;
use App\Models\Asset\Asset;

class MinimalAssetDataDTO
{
    public function __construct(
        public string $id,
        public string $accountCode,
        public ?AssetUnit $unit,
        public string $location,
        public ?string $purchaseYear
    ) {}

    public static function fromModel(Asset $data): self 
    {
        return new self(
            $data->id,
            $data->account_code,
            $data->unit,
            $data->location,
            $data->purchase_year
        );
    }
}