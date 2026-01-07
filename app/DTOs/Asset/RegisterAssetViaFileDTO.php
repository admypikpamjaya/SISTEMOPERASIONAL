<?php 

namespace App\DTOs\Asset;

use App\Enums\Asset\AssetCategory;

class RegisterAssetViaFileDTO
{
    public function __construct(
        public AssetCategory $category,
        public $file
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(AssetCategory::from($data['category']), $data['file']);
    }
}