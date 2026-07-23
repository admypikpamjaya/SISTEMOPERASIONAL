<?php

namespace App\Services\SystemManagement;

use App\Enums\User\UserRole;
use App\Models\AppSetting;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class FeatureAvailabilityService
{
    /**
     * @var string[]|null
     */
    private ?array $disabledKeysCache = null;

    /**
     * @return array<string, array<string, mixed>>
     */
    public function features(): array
    {
        $features = config('feature_availability.features', []);

        return is_array($features) ? $features : [];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function featuresWithState(): array
    {
        $disabledKeys = $this->disabledKeys();

        return collect($this->features())
            ->map(function (array $feature, string $key) use ($disabledKeys): array {
                $locked = (bool) ($feature['locked'] ?? false);

                return array_merge($feature, [
                    'key' => $key,
                    'locked' => $locked,
                    'is_enabled' => $locked || !in_array($key, $disabledKeys, true),
                ]);
            })
            ->values()
            ->all();
    }

    /**
     * @return string[]
     */
    public function disabledKeys(): array
    {
        if ($this->disabledKeysCache !== null) {
            return $this->disabledKeysCache;
        }

        try {
            if (!Schema::hasTable('app_settings')) {
                return $this->disabledKeysCache = [];
            }

            $setting = AppSetting::query()
                ->where('key', (string) config('feature_availability.setting_key', 'system.feature_availability'))
                ->first();
        } catch (\Throwable) {
            return $this->disabledKeysCache = [];
        }

        $value = is_array($setting?->value) ? $setting->value : [];
        $keys = $value['disabled_keys'] ?? [];

        if (!is_array($keys)) {
            return $this->disabledKeysCache = [];
        }

        return $this->disabledKeysCache = collect($keys)
            ->map(fn ($key) => trim((string) $key))
            ->filter(fn (string $key) => $key !== '')
            ->unique()
            ->values()
            ->all();
    }

    public function isEnabled(string $featureKey): bool
    {
        $feature = $this->features()[$featureKey] ?? null;
        if (!is_array($feature)) {
            return true;
        }

        if ((bool) ($feature['locked'] ?? false)) {
            return true;
        }

        return !in_array($featureKey, $this->disabledKeys(), true);
    }

    public function setEnabled(string $featureKey, bool $enabled, ?User $updatedBy = null): void
    {
        $feature = $this->features()[$featureKey] ?? null;
        if (!is_array($feature) || (bool) ($feature['locked'] ?? false)) {
            return;
        }

        $disabledKeys = collect($this->disabledKeys());
        $disabledKeys = $enabled
            ? $disabledKeys->reject(fn (string $key) => $key === $featureKey)
            : $disabledKeys->push($featureKey);

        AppSetting::query()->updateOrCreate(
            ['key' => (string) config('feature_availability.setting_key', 'system.feature_availability')],
            [
                'value' => [
                    'disabled_keys' => $disabledKeys->unique()->values()->all(),
                    'updated_by' => $updatedBy?->id,
                    'updated_at' => now()->toDateTimeString(),
                ],
            ]
        );

        $this->disabledKeysCache = null;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function featureForRouteName(?string $routeName): ?array
    {
        if ($routeName === null || $routeName === '') {
            return null;
        }

        foreach ($this->features() as $key => $feature) {
            $patterns = (array) ($feature['route_patterns'] ?? []);
            foreach ($patterns as $pattern) {
                if (is_string($pattern) && Str::is($pattern, $routeName)) {
                    return array_merge($feature, ['key' => $key]);
                }
            }
        }

        return null;
    }

    public function isMenuItemVisible(array $item, ?User $user = null): bool
    {
        $user ??= Auth::user();
        if ($user?->role === UserRole::SYSTEM_MANAGEMENT->value) {
            return true;
        }

        $featureKey = $this->featureKeyForMenuItem($item);

        return $featureKey === null || $this->isEnabled($featureKey);
    }

    public function featureKeyForMenuItem(array $item): ?string
    {
        $explicitKey = trim((string) ($item['feature_key'] ?? ''));
        if ($explicitKey !== '') {
            return array_key_exists($explicitKey, $this->features()) ? $explicitKey : null;
        }

        $moduleName = trim((string) ($item['module_name'] ?? ''));
        if ($moduleName !== '' && array_key_exists($moduleName, $this->features())) {
            return $moduleName;
        }

        $route = trim((string) ($item['route'] ?? ''));
        if ($route === '') {
            return null;
        }

        foreach ($this->features() as $key => $feature) {
            $menuRoutes = (array) ($feature['menu_routes'] ?? []);
            if (in_array($route, $menuRoutes, true)) {
                return $key;
            }
        }

        $feature = $this->featureForRouteName($route);

        return is_array($feature) ? (string) $feature['key'] : null;
    }
}
