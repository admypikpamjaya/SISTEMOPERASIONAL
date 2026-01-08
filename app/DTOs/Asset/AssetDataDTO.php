<?php 

namespace App\DTOs\Asset;

use App\Enums\Asset\AssetCategory;
use App\Models\Asset\Asset;
use App\Services\Asset\AssetFactory;

class AssetDataDTO
{
    public function __construct(
        public string $id,
        public AssetCategory $category,
        public string $accountCode,
        public ?string $serialNumber,
        public string $location,
        public ?int $purchaseYear,
        public array $detail,
        public array $maintenanceLogs
    ) {}

    public static function fromArray(array $data): self 
    {
        return new self(
            $data['id'],
            AssetCategory::from($data['category']),
            $data['account_code'],
            $data['asset_serial_number'],
            $data['location'],
            $data['purchase_year'],
            $data['detail'],
            $data['maintenance_logs']
        );
    }

    public static function fromModel(Asset $asset): self 
    {
        $handler = AssetFactory::createHandler($asset->category);
        $relationName = $handler->getRelationName();

        return new self(
            $asset->id,
            $asset->category,
            $asset->account_code,
            $asset->serial_number,
            $asset->location,
            $asset->purchase_year,
            $asset->{$relationName}?->toArray(),
            $asset->maintenanceLogs->toArray()
        );
    }
}