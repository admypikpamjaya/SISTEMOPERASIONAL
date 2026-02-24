<?php

namespace App\Http\Middleware;

use App\Enums\User\UserRole;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureFinanceAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            abort(401);
        }

        $allowedRoles = [
            UserRole::FINANCE->value,
            UserRole::IT_SUPPORT->value,
            UserRole::PEMBINA->value,
        ];

        if (!in_array((string) auth()->user()->role, $allowedRoles, true)) {
            abort(403);
        }

        return $next($request);
    }
}
