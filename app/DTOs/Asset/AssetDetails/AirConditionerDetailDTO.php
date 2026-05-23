<?php 

namespace App\DTOs\Asset\AssetDetails;

class AirConditionerDetailDTO
{
    public function __construct(
        public string $brand,
        public string $dimension,
        public string $powerRating
    ) {}

    public static function fromArray(array $data): self 
    {
        return new self(
            $data['brand'],
            $data['dimension'],
            $data['power_rating']
        );
    }
}
