<?php

namespace App\Services\SystemManagement;

use App\Models\SystemAccessLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\Response;

class RequestAuditService
{
    public function __construct(
        private readonly ClientContextService $clientContext
    ) {}

    public function record(Request $request, ?Response $response, float $startedAt): void
    {
        if (!config('system_management.access_audit.enabled', true)) {
            return;
        }

        try {
            if (!Schema::hasTable('system_access_logs')) {
                return;
            }
        } catch (\Throwable) {
            return;
        }

        $path = $request->path();
        foreach ((array) config('system_management.access_audit.skip_paths', []) as $skipPath) {
            $skipPath = trim((string) $skipPath, '/');
            if ($skipPath !== '' && str_starts_with($path, $skipPath)) {
                return;
            }
        }

        $context = $this->clientContext->fromRequest($request);

        try {
            SystemAccessLog::query()->create(array_merge($context, [
                'user_id' => Auth::id(),
                'guard' => Auth::getDefaultDriver(),
                'method' => $request->method(),
                'route_name' => $request->route()?->getName(),
                'path' => substr('/' . ltrim($path, '/'), 0, 500),
                'status_code' => $response?->getStatusCode(),
                'duration_ms' => max(0, (int) round((microtime(true) - $startedAt) * 1000)),
                'metadata' => [
                    'ajax' => $request->ajax(),
                    'expects_json' => $request->expectsJson(),
                    'query_keys' => array_keys($request->query()),
                ],
                'occurred_at' => now(),
            ]));
        } catch (\Throwable $exception) {
            report($exception);
        }
    }
}
