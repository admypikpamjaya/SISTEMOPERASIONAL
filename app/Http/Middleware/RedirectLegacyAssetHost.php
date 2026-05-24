<?php

namespace App\Http\Middleware;

use App\Support\AssetPublicUrl;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RedirectLegacyAssetHost
{
    public function handle(Request $request, Closure $next): Response
    {
        $assetId = (string) $request->route('id', '');
        if (
            $assetId !== ''
            && AssetPublicUrl::isLegacyHost($request->getHost())
            && AssetPublicUrl::canonicalHost() !== ''
            && strtolower($request->getHost()) !== AssetPublicUrl::canonicalHost()
        ) {
            return redirect()->to(AssetPublicUrl::detailUrl($assetId), 301);
        }

        return $next($request);
    }
}
