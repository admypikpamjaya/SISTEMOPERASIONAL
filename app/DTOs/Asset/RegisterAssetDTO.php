<?php 

namespace App\DTOs\Asset;

use App\Enums\Asset\AssetCategory;

class RegisterAssetDTO
{
    public function __construct(
        public AssetCategory $category,
        public string $accountCode,
        public ?string $serialNumber,
        public string $location,
        public ?int $purchaseYear,
        public array $detail
    ) {}

    public function toArray(): array 
    {
        return [
            'category' => $this->category->value,
            'account_code' => $this->accountCode,
            'serial_number' => $this->serialNumber,
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
            $data['location'],
            $data['purchase_year'],
            $data['detail']
        );
    }
}