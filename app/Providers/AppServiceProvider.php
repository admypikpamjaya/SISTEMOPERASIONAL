<?php

namespace App\Providers;

use App\Contracts\Messaging\EmailProviderInterface;
use App\Contracts\Messaging\WhatsappProviderInterface;
use App\Providers\Messaging\DummyEmailProvider;
use App\Providers\Messaging\DummyWhatsappProvider;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use App\Services\AccessControl\PermissionService;
use App\Enums\Portal\PortalPermission;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            WhatsappProviderInterface::class,
            DummyWhatsappProvider::class
        );

        $this->app->bind(
            EmailProviderInterface::class,
            DummyEmailProvider::class
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
