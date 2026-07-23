<?php

namespace App\Http\Middleware;

use App\Enums\User\UserRole;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSystemManagement
{
    public function handle(Request $request, Closure $next): Response
    {
        abort_unless(
            $request->user()?->role === UserRole::SYSTEM_MANAGEMENT->value,
            403
        );

        return $next($request);
    }
}
