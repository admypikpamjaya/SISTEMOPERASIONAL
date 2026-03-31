<?php 

namespace App\Enums\Asset;

enum AssetCategory: string 
{
    case AC = 'AC';
    case COMPUTER = 'COMPUTER';
    case OTHER = 'OTHER';

    public function label(): string
    {
        return match ($this) {
            self::AC => 'AC',
            self::COMPUTER => 'COMPUTER',
            self::OTHER => 'NON AC',
        };
    }
}
