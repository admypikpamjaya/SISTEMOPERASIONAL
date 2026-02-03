<?php 

namespace App\DTOs\Asset;

use App\DTOs\Report\MaintenanceReportDataDTO;
use App\Enums\Asset\AssetCategory;
use App\Enums\Asset\AssetUnit;
use App\Models\Asset\Asset;
use App\Services\Asset\AssetFactory;

class AssetDataDTO
{
    public function __construct(
        public string $id,
        public AssetCategory $category,
        public string $accountCode,
        public ?string $serialNumber,
        public ?AssetUnit $unit,
        public string $location,
        public ?string $purchaseYear,
        public array $detail,
        public ?array $maintenanceLogs
    ) {}

    public static function fromArray(array $data): self 
    {
        return new self(
            $data['id'],
            AssetCategory::from($data['category']),
            $data['account_code'],
            $data['asset_serial_number'] ?? null,
            (AssetUnit::from($data['unit']) ?? null),
            $data['location'],
            $data['purchase_year'] ?? null,
            $data['detail'],
            $data['maintenance_logs'] ?? null
        );
    }

    public static function fromModel(Asset $asset): self 
    {
        $handler = AssetFactory::createHandler($asset->category);
        $relationName = $handler->getRelationName();

        $logs = $asset->relationLoaded('maintenanceLogs') 
                ? $asset->maintenanceLogs->map(fn ($log) => MaintenanceReportDataDTO::fromModel($log))->toArray()
                : null;

        return new self(
            $asset->id,
            $asset->category,
            $asset->account_code,
            $asset->serial_number,
            $asset->unit,
            $asset->location,
            $asset->purchase_year,
            $asset->{$relationName}?->toArray(),
            $logs
        );
    }
}