<?php 

namespace App\Enums\Asset;

enum AssetCategory: string 
{
    case AC = 'AC';
    case OTHER = 'OTHER';

    public function label(): string
    {
        return match ($this) {
            self::AC => 'AC',
            self::OTHER => 'NON AC',
        };
    }
}
