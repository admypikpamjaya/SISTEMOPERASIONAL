<?php

namespace App\Http\Controllers\Asset;

use App\Http\Controllers\Controller;
use App\Services\Asset\AssetService;
use Illuminate\Http\Request;

class PublicAssetController extends Controller
{
    public function __construct(
        private AssetService $assetService
    ) {}

    public function show(string $id)
    {
        try 
        {
            return view('public.asset.index', [
                'asset' => $this->assetService->getAsset($id)
            ]);
        }
        catch(\Throwable $e) 
        {
            return ($e->getCode() === 404) ? abort(404) : abort(500);
        }
    }
}
