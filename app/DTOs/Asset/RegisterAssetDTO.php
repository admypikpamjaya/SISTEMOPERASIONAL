<?php 

namespace App\DTOs\Asset;

use App\Enums\Asset\AssetCategory;

class RegisterAssetDTO
{
    public function __construct(
        public AssetCategory $category,
        public string $accountCode,
        public ?string $serialNumber,
        public string $unit,
        public string $location,
        public ?string $purchaseYear,
        public array $detail
    ) {}

    public function toArray(): array 
    {
        return [
            'category' => $this->category->value,
            'account_code' => $this->accountCode,
            'serial_number' => $this->serialNumber,
            'unit' => $this->unit,
            'location' => $this->location,
            'purchase_year' => $this->purchaseYear,
            'detail' => $this->detail
        ];
    }

    public static function fromArray(array $data): self 
    {
        return new self(
            AssetCategory::from($data['category']),
            $data['account_code'],
            $data['asset_serial_number'],
            $data['unit'],
            $data['location'],
            $data['purchase_year'],
            $data['detail']
        );
    }
}