<?php

namespace App\Providers;

use App\Contracts\Messaging\EmailProviderInterface;
use App\Contracts\Messaging\WhatsappProviderInterface;
use App\Providers\Messaging\SmtpEmailProvider;
use App\Providers\Messaging\FonnteWhatsappProvider;

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
        $this->app->bind(
            WhatsappProviderInterface::class,
            FonnteWhatsappProvider::class
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
