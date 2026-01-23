<?php

namespace App\Providers;

use App\Contracts\Messaging\EmailProviderInterface;
use App\Contracts\Messaging\WhatsappProviderInterface;
use App\Providers\Messaging\SmtpEmailProvider;
use App\Providers\Messaging\FonnteWhatsappProvider;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Blade;
use App\Services\AccessControl\PermissionService;
use App\Enums\Portal\PortalPermission;

class AppServiceProvider extends ServiceProvider
{
   public function register(): void
{
    // EMAIL
    $this->app->bind(
        EmailProviderInterface::class,
        SmtpEmailProvider::class
    );

    // WHATSAPP
    $this->app->bind(
        WhatsappProviderInterface::class,
        FonnteWhatsappProvider::class
    );
}

    public function boot(): void
    {
        Paginator::useBootstrapFive();

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
