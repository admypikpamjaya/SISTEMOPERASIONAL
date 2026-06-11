<?php 

namespace App\Enums\Asset;

enum AssetCategory: string 
{
    case AC = 'AC';
    case BUILDING_INFRASTRUCTURE = 'BUILDING_INFRASTRUCTURE';
    case ELECTRONIC = 'ELECTRONIC';
    case ROOM_INVENTORY = 'ROOM_INVENTORY';
    case VEHICLE = 'VEHICLE';
    case COMPUTER = 'COMPUTER';
    case OTHER = 'OTHER';

    public function label(): string
    {
        return match ($this) {
            self::AC => __('app.asset.categories.ac'),
            self::BUILDING_INFRASTRUCTURE => __('app.asset.categories.building_infrastructure'),
            self::ELECTRONIC => __('app.asset.categories.electronic'),
            self::ROOM_INVENTORY => __('app.asset.categories.room_inventory'),
            self::VEHICLE => __('app.asset.categories.vehicle'),
            self::COMPUTER => __('app.asset.categories.computer'),
            self::OTHER => __('app.asset.categories.other'),
        };
    }

    public function slug(): string
    {
        return match ($this) {
            self::AC => 'ac',
            self::BUILDING_INFRASTRUCTURE => 'bangunan-sarana-prasarana',
            self::ELECTRONIC => 'elektronik',
            self::ROOM_INVENTORY => 'inventaris-ruangan',
            self::VEHICLE => 'kendaraan',
            self::COMPUTER => 'komputer',
            self::OTHER => 'lainnya',
        };
    }
}
