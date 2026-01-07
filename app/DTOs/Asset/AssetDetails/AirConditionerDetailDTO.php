<?php 

namespace App\DTOs\Asset\AssetDetails;

class AirConditionerDetailDTO
{
    public function __construct(
        public string $brand,
        public float $dimensions,
        public int $power_rating
    ) {}

    public static function fromArray(array $data): self 
    {
        return new self(
            $data['brand'],
            $data['dimensions'],
            $data['power_rating']
        );
    }
}