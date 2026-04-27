<?php

namespace App\Providers;

use App\Contracts\Messaging\EmailProviderInterface;
use App\Contracts\Messaging\WhatsappProviderInterface;
use App\Providers\Messaging\SmtpEmailProvider;
use App\Providers\Messaging\FonnteWhatsappProvider;
use App\Providers\Messaging\GatewayWhatsappProvider;
use App\Providers\Messaging\WablasWhatsappProvider;
use App\Services\Blast\WhatsAppProviderSelector;

use Illuminate\Http\Request;
use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Blade;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;

use App\Services\AccessControl\PermissionService;
use App\Enums\Portal\PortalPermission;
use App\Services\Recipient\RecipientNormalizer;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // ======================
        // EMAIL PROVIDER
        // ======================
        $this->app->bind(
            EmailProviderInterface::class,
            SmtpEmailProvider::class
        );

        // ======================
        // WHATSAPP PROVIDER
        // ======================
        $whatsappProvider = strtolower(
            (string) (new WhatsAppProviderSelector())->getProvider()
        );

        $this->app->bind(
            WhatsappProviderInterface::class,
            match ($whatsappProvider) {
                'fonnte' => FonnteWhatsappProvider::class,
                'gateway', 'baileys', 'node' => GatewayWhatsappProvider::class,
                default => WablasWhatsappProvider::class,
            }
        );

        // ======================
        // RECIPIENT NORMALIZER
        // (INI YANG HILANG KEMARIN)
        // ======================
        $this->app->singleton(RecipientNormalizer::class);
    }

    public function boot(): void
    {
        Paginator::useBootstrapFive();

        RateLimiter::for('blast-email', function () {
            $perMinute = max(
                1,
                (int) config('blast.rate_limits.email_per_minute', 90)
            );

            return Limit::perMinute($perMinute)->by('blast-channel-email');
        });

        RateLimiter::for('blast-whatsapp', function () {
            $perMinute = max(
                1,
                (int) config('blast.rate_limits.whatsapp_per_minute', 45)
            );

            return Limit::perMinute($perMinute)->by('blast-channel-whatsapp');
        });

        RateLimiter::for('public-maintenance-submission', function (Request $request) {
            $assetId = (string) $request->input('asset_id', 'unknown-asset');
            $key = sprintf(
                'public-maintenance:%s:%s',
                (string) $request->ip(),
                $assetId
            );

            return Limit::perMinute(3)
                ->by($key)
                ->response(function (Request $request, array $headers) {
                    return response()->json([
                        'message' => 'Terlalu banyak pengiriman laporan maintenance. Silakan coba lagi dalam beberapa menit.',
                    ], 429, $headers);
                });
        });

        Blade::if('permission', function (string $permission) {
            return auth()->check()
                && app(PermissionService::class)
                    ->checkAccess(
                        auth()->user(),
                        PortalPermission::from($permission)->value
                    );
        });
    }
}
