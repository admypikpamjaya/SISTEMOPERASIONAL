<?php 

namespace App\Services\Asset;

use App\Contracts\Asset\AssetDetailHandler;
use App\Enums\Asset\AssetCategory;
use App\Services\Asset\AssetDetailHandlers\AirConditionerDetailHandler;
use App\Services\Asset\AssetDetailHandlers\BuildingInfrastructureDetailHandler;
use App\Services\Asset\AssetDetailHandlers\ComputerComponentDetailHandler;
use App\Services\Asset\AssetDetailHandlers\ElectronicDetailHandler;
use App\Services\Asset\AssetDetailHandlers\NoDetailHandler;
use App\Services\Asset\AssetDetailHandlers\RoomInventoryDetailHandler;
use App\Services\Asset\AssetDetailHandlers\VehicleDetailHandler;

class AssetFactory
{
    public static function createHandler(AssetCategory $category): AssetDetailHandler
    {
        return match($category)
        {
            AssetCategory::AC => new AirConditionerDetailHandler(),
            AssetCategory::COMPUTER => new ComputerComponentDetailHandler(),
            AssetCategory::ELECTRONIC => new ElectronicDetailHandler(),
            AssetCategory::ROOM_INVENTORY => new RoomInventoryDetailHandler(),
            AssetCategory::BUILDING_INFRASTRUCTURE => new BuildingInfrastructureDetailHandler(),
            AssetCategory::VEHICLE => new VehicleDetailHandler(),
            AssetCategory::OTHER => new AirConditionerDetailHandler(),
        };
    }
}
