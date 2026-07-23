<?php

namespace App\Http\Middleware;

use App\Enums\User\UserRole;
use App\Models\AppSetting;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\Response;

class SystemMaintenanceMode
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!$this->isMaintenanceActive()) {
            return $next($request);
        }

        $user = $request->user();
        $path = trim($request->path(), '/');
        $isSystemPath = $path === 'system-management'
            || str_starts_with($path, 'system-management/');

        if ($isSystemPath || ($user && $user->role === UserRole::SYSTEM_MANAGEMENT->value)) {
            return $next($request);
        }

        return response()
            ->view('errors.system-maintenance', [
                'message' => $this->maintenanceMessage(),
            ], 503)
            ->header('Retry-After', '600');
    }

    private function isMaintenanceActive(): bool
    {
        try {
            if (!Schema::hasTable('app_settings')) {
                return false;
            }

            $setting = AppSetting::query()
                ->where('key', 'system.maintenance')
                ->value('value');
        } catch (\Throwable) {
            return false;
        }

        $value = is_array($setting) ? $setting : json_decode((string) $setting, true);

        return (bool) ($value['enabled'] ?? false);
    }

    private function maintenanceMessage(): string
    {
        try {
            $setting = AppSetting::query()
                ->where('key', 'system.maintenance')
                ->value('value');
        } catch (\Throwable) {
            return 'Sistem sedang maintenance berkala. Silakan coba lagi beberapa saat lagi.';
        }

        $value = is_array($setting) ? $setting : json_decode((string) $setting, true);

        return trim((string) ($value['message'] ?? 'Sistem sedang maintenance berkala. Silakan coba lagi beberapa saat lagi.'));
    }
}
