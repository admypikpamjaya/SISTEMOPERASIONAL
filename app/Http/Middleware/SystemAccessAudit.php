<?php

namespace App\Http\Middleware;

use App\Services\SystemManagement\RequestAuditService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SystemAccessAudit
{
    private float $startedAt = 0.0;

    public function handle(Request $request, Closure $next): Response
    {
        $this->startedAt = microtime(true);

        return $next($request);
    }

    public function terminate(Request $request, Response $response): void
    {
        app(RequestAuditService::class)->record(
            $request,
            $response,
            $this->startedAt > 0 ? $this->startedAt : microtime(true)
        );
    }
}
