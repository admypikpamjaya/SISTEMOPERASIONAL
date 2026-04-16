<?php 

namespace App\DTOs\Asset;

use App\Enums\Asset\AssetCategory;
use App\Enums\Asset\AssetUnit;
use App\Models\Asset\Asset;
use App\Services\Asset\AssetFactory;

class MinimalAssetDataDTO
{
    public function __construct(
        public string $id,
        public string $accountCode,
        public AssetCategory $category,
        public array $detail,
        public ?AssetUnit $unit,
        public string $location,
        public ?string $purchaseYear
    ) {}

    public static function fromModel(Asset $data): self 
    {
        return new self(
            $data->id,
            $data->account_code,
            $data->category,
            self::mapDetail($data),
            $data->unit,
            $data->location,
            $data->purchase_year
        );
    }

    private static function mapDetail(Asset $asset): array
    {
        $relation = AssetFactory::createHandler($asset->category)
            ->getRelationName();

        $data = $asset->$relation ?? null;

        if ($data instanceof \Illuminate\Database\Eloquent\Model) {
            return $data->toArray();
        }

        if ($data instanceof \Illuminate\Support\Collection) {
            return $data->map->toArray()->toArray();
        }

        return [];
    }
}