<?php

namespace App\Providers;

use App\Enums\Portal\PortalPermission;
use App\Services\AccessControl\PermissionService;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::useBootstrapFive();
        Blade::if('permission', function(string $permission) {
            return auth()->check() && app(PermissionService::class)->checkAccess(auth()->user(), PortalPermission::from($permission)->value);
        });
    }
}
