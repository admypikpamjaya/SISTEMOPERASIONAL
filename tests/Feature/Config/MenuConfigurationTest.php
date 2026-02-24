<?php

namespace Tests\Feature\Config;

use App\Enums\Portal\PortalPermission;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class MenuConfigurationTest extends TestCase
{
    public function test_all_menu_routes_are_registered(): void
    {
        foreach ($this->flattenMenus(config('menu')) as $menu) {
            $this->assertArrayHasKey('route', $menu);
            $this->assertTrue(
                Route::has($menu['route']),
                "Route menu tidak ditemukan: {$menu['route']}"
            );
        }
    }

    public function test_menu_module_names_map_to_valid_read_permissions(): void
    {
        foreach ($this->flattenMenus(config('menu')) as $menu) {
            $moduleName = $menu['module_name'] ?? null;
            if (empty($moduleName)) {
                continue;
            }

            $this->assertNotNull(
                PortalPermission::tryFrom($moduleName . '.read'),
                "module_name tidak punya permission *.read valid: {$moduleName}"
            );
        }
    }

    public function test_hide_on_routes_value_is_valid_shape(): void
    {
        foreach (config('menu') as $menu) {
            if (!array_key_exists('hide_on_routes', $menu)) {
                continue;
            }

            $hideOnRoutes = $menu['hide_on_routes'];
            $isValid = is_string($hideOnRoutes)
                || (
                    is_array($hideOnRoutes)
                    && collect($hideOnRoutes)->every(
                        static fn ($pattern): bool => is_string($pattern) && $pattern !== ''
                    )
                );

            $this->assertTrue($isValid, "hide_on_routes tidak valid pada menu {$menu['label']}");
        }
    }

    /**
     * @param array<int, array<string, mixed>> $menus
     * @return array<int, array<string, mixed>>
     */
    private function flattenMenus(array $menus): array
    {
        $flattened = [];

        foreach ($menus as $menu) {
            $flattened[] = $menu;

            if (!empty($menu['children']) && is_array($menu['children'])) {
                foreach ($menu['children'] as $child) {
                    $flattened[] = $child;
                }
            }
        }

        return $flattened;
    }
}
