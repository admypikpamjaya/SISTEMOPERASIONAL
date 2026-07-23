<?php

namespace App\Http\Middleware;

use App\Enums\User\UserRole;
use App\Services\SystemManagement\FeatureAvailabilityService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureFeatureEnabled
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if ($user?->role === UserRole::SYSTEM_MANAGEMENT->value) {
            return $next($request);
        }

        $service = app(FeatureAvailabilityService::class);
        $feature = $service->featureForRouteName($request->route()?->getName());

        if (is_array($feature) && !$service->isEnabled((string) $feature['key'])) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Fitur ' . ($feature['name'] ?? $feature['key']) . ' sedang dinonaktifkan oleh Sistem Management.',
                ], 503);
            }

            return response()->view('errors.feature-disabled', [
                'feature' => $feature,
            ], 503);
        }

        return $next($request);
    }
}
