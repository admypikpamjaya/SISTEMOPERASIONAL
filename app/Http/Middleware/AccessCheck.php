<?php

namespace App\Http\Middleware;

use App\Enums\Portal\PortalPermission;
use App\Services\AccessControl\PermissionService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AccessCheck
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $permissionName): Response
    {
        abort_if(!app(PermissionService::class)->checkAccess(auth()->user(), PortalPermission::from($permissionName)->value), 403);
        return $next($request);
    }
}
