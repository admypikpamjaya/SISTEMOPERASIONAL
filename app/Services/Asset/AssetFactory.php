<?php 

namespace App\Services\Asset;

use App\Contracts\Asset\AssetDetailHandler;
use App\Enums\Asset\AssetCategory;
use App\Services\Asset\AssetDetailHandlers\AirConditionerDetailHandler;

class AssetFactory
{
    public static function createHandler(AssetCategory $category): AssetDetailHandler
    {
        return match($category)
        {
            AssetCategory::AC => new AirConditionerDetailHandler(),
        };
    }
}