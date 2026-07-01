<?php

namespace App\Http\Middleware;

use App\Support\AssetPublicUrl;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class RedirectLegacyAssetHost
{
    public function handle(Request $request, Closure $next): Response
    {
        if (
            AssetPublicUrl::isLegacyHost($request->getHost())
            && AssetPublicUrl::canonicalHost() !== ''
            && strtolower($request->getHost()) !== AssetPublicUrl::canonicalHost()
        ) {
            $status = $request->isMethodSafe() ? 301 : 308;
            $targetUrl = AssetPublicUrl::urlForPath($request->getRequestUri());

            Log::info('[LEGACY HOST REDIRECT]', [
                'from_host' => $request->getHost(),
                'to_url' => $targetUrl,
                'method' => $request->getMethod(),
                'status' => $status,
            ]);

            return redirect()->to($targetUrl, $status);
        }

        return $next($request);
    }
}
