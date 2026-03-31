<?php 

namespace App\Services\Asset;

use App\Contracts\Asset\AssetDetailHandler;
use App\Enums\Asset\AssetCategory;
use App\Services\Asset\AssetDetailHandlers\AirConditionerDetailHandler;
use App\Services\Asset\AssetDetailHandlers\ComputerComponentDetailHandler;

class AssetFactory
{
    public static function createHandler(AssetCategory $category): AssetDetailHandler
    {
        return match($category)
        {
            AssetCategory::AC => new AirConditionerDetailHandler(),
            AssetCategory::COMPUTER => new ComputerComponentDetailHandler(),
            AssetCategory::OTHER => new AirConditionerDetailHandler(),
        };
    }
}
