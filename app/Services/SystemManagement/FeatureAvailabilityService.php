<?php

namespace App\Services\SystemManagement;

use App\Enums\User\UserRole;
use App\Models\AppSetting;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class FeatureAvailabilityService
{
    private const WIB_TIMEZONE = 'Asia/Jakarta';

    /**
     * @var string[]|null
     */
    private ?array $disabledKeysCache = null;

    /**
     * @var array<string, mixed>|null
     */
    private ?array $stateCache = null;

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
        $state = $this->state();
        $disabledKeys = $this->disabledKeys();
        $disabledFeatures = is_array($state['disabled_features'] ?? null)
            ? $state['disabled_features']
            : [];

        return collect($this->features())
            ->map(function (array $feature, string $key) use ($disabledKeys, $disabledFeatures): array {
                $locked = (bool) ($feature['locked'] ?? false);
                $disableInfo = is_array($disabledFeatures[$key] ?? null)
                    ? $disabledFeatures[$key]
                    : [];

                return array_merge($feature, [
                    'key' => $key,
                    'locked' => $locked,
                    'is_enabled' => $locked || !in_array($key, $disabledKeys, true),
                    'disable_reason' => (string) ($disableInfo['reason'] ?? ''),
                    'disabled_until' => $disableInfo['disabled_until'] ?? null,
                    'disabled_until_label' => $this->formatDateTimeLabel($disableInfo['disabled_until'] ?? null),
                    'disabled_at_label' => $this->formatDateTimeLabel($disableInfo['disabled_at'] ?? null),
                    'disabled_by_name' => (string) ($disableInfo['updated_by_name'] ?? ''),
                ]);
            })
            ->values()
            ->all();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function expiredNotifications(): array
    {
        $state = $this->state();
        $notifications = is_array($state['expired_notifications'] ?? null)
            ? $state['expired_notifications']
            : [];

        return collect($notifications)
            ->map(function (array $notification, string $key): array {
                $feature = $this->features()[$key] ?? [];

                return array_merge([
                    'key' => $key,
                    'name' => is_array($feature) ? ($feature['name'] ?? $key) : $key,
                    'description' => is_array($feature) ? ($feature['description'] ?? '') : '',
                    'reason' => '',
                    'disabled_until' => null,
                    'expired_at' => null,
                    'restored_at' => null,
                    'updated_by_name' => '',
                ], $notification, [
                    'key' => $key,
                    'disabled_until_label' => $this->formatDateTimeLabel($notification['disabled_until'] ?? null),
                    'expired_at_label' => $this->formatDateTimeLabel($notification['expired_at'] ?? null),
                    'restored_at_label' => $this->formatDateTimeLabel($notification['restored_at'] ?? null),
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

        $value = $this->state();
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

    public function setEnabled(
        string $featureKey,
        bool $enabled,
        ?User $updatedBy = null,
        ?string $reason = null,
        ?CarbonInterface $disabledUntil = null
    ): void
    {
        $feature = $this->features()[$featureKey] ?? null;
        if (!is_array($feature) || (bool) ($feature['locked'] ?? false)) {
            return;
        }

        $state = $this->state();
        $disabledKeys = collect(is_array($state['disabled_keys'] ?? null) ? $state['disabled_keys'] : []);
        $disabledFeatures = is_array($state['disabled_features'] ?? null) ? $state['disabled_features'] : [];
        $expiredNotifications = is_array($state['expired_notifications'] ?? null) ? $state['expired_notifications'] : [];

        if ($enabled) {
            $disabledKeys = $disabledKeys->reject(fn (string $key) => $key === $featureKey);
            unset($disabledFeatures[$featureKey], $expiredNotifications[$featureKey]);
        } else {
            $disabledKeys = $disabledKeys->push($featureKey);
            $disabledFeatures[$featureKey] = [
                'reason' => trim((string) $reason),
                'disabled_until' => $disabledUntil?->timezone(self::WIB_TIMEZONE)->toIso8601String(),
                'disabled_at' => now(self::WIB_TIMEZONE)->toIso8601String(),
                'updated_by' => $updatedBy?->id,
                'updated_by_name' => $updatedBy?->name,
                'updated_at' => now(self::WIB_TIMEZONE)->toIso8601String(),
            ];
            unset($expiredNotifications[$featureKey]);
        }

        $this->saveState(array_merge($state, [
            'disabled_keys' => $disabledKeys->unique()->values()->all(),
            'disabled_features' => $disabledFeatures,
            'expired_notifications' => $expiredNotifications,
            'updated_by' => $updatedBy?->id,
            'updated_at' => now(self::WIB_TIMEZONE)->toDateTimeString(),
        ]));

        $this->disabledKeysCache = null;
        $this->stateCache = null;
    }

    public function acknowledgeExpiredNotification(string $featureKey, ?User $updatedBy = null): void
    {
        $state = $this->state();
        $expiredNotifications = is_array($state['expired_notifications'] ?? null) ? $state['expired_notifications'] : [];
        unset($expiredNotifications[$featureKey]);

        $disabledKeys = collect(is_array($state['disabled_keys'] ?? null) ? $state['disabled_keys'] : [])
            ->reject(fn (string $key) => $key === $featureKey)
            ->unique()
            ->values()
            ->all();
        $disabledFeatures = is_array($state['disabled_features'] ?? null) ? $state['disabled_features'] : [];
        unset($disabledFeatures[$featureKey]);

        $this->saveState(array_merge($state, [
            'disabled_keys' => $disabledKeys,
            'disabled_features' => $disabledFeatures,
            'expired_notifications' => $expiredNotifications,
            'updated_by' => $updatedBy?->id,
            'updated_at' => now(self::WIB_TIMEZONE)->toDateTimeString(),
        ]));

        $this->disabledKeysCache = null;
        $this->stateCache = null;
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

    /**
     * @return array<string, mixed>
     */
    private function state(): array
    {
        if ($this->stateCache !== null) {
            return $this->stateCache;
        }

        try {
            if (!Schema::hasTable('app_settings')) {
                return $this->stateCache = [];
            }

            $setting = AppSetting::query()
                ->where('key', (string) config('feature_availability.setting_key', 'system.feature_availability'))
                ->first();
        } catch (\Throwable) {
            return $this->stateCache = [];
        }

        $state = is_array($setting?->value) ? $setting->value : [];
        $state = $this->restoreExpiredFeatures($state);

        return $this->stateCache = $state;
    }

    /**
     * @param array<string, mixed> $state
     * @return array<string, mixed>
     */
    private function restoreExpiredFeatures(array $state): array
    {
        $disabledKeys = collect(is_array($state['disabled_keys'] ?? null) ? $state['disabled_keys'] : [])
            ->map(fn ($key) => trim((string) $key))
            ->filter(fn (string $key) => $key !== '')
            ->unique();
        $disabledFeatures = is_array($state['disabled_features'] ?? null) ? $state['disabled_features'] : [];
        $expiredNotifications = is_array($state['expired_notifications'] ?? null) ? $state['expired_notifications'] : [];
        $now = now(self::WIB_TIMEZONE);
        $changed = false;

        foreach ($disabledFeatures as $key => $disableInfo) {
            if (!is_array($disableInfo)) {
                continue;
            }

            $disabledUntil = $this->parseDateTime($disableInfo['disabled_until'] ?? null);
            if ($disabledUntil === null || $disabledUntil->gt($now)) {
                continue;
            }

            $feature = $this->features()[(string) $key] ?? [];
            $expiredNotifications[(string) $key] = [
                'key' => (string) $key,
                'name' => is_array($feature) ? ($feature['name'] ?? (string) $key) : (string) $key,
                'description' => is_array($feature) ? ($feature['description'] ?? '') : '',
                'reason' => (string) ($disableInfo['reason'] ?? ''),
                'disabled_until' => $disabledUntil->toIso8601String(),
                'disabled_at' => $disableInfo['disabled_at'] ?? null,
                'expired_at' => $now->toIso8601String(),
                'restored_at' => $now->toIso8601String(),
                'updated_by' => $disableInfo['updated_by'] ?? null,
                'updated_by_name' => $disableInfo['updated_by_name'] ?? '',
            ];

            unset($disabledFeatures[$key]);
            $disabledKeys = $disabledKeys->reject(fn (string $disabledKey) => $disabledKey === (string) $key);
            $changed = true;
        }

        if (!$changed) {
            return array_merge($state, [
                'disabled_keys' => $disabledKeys->values()->all(),
                'disabled_features' => $disabledFeatures,
                'expired_notifications' => $expiredNotifications,
            ]);
        }

        $state = array_merge($state, [
            'disabled_keys' => $disabledKeys->values()->all(),
            'disabled_features' => $disabledFeatures,
            'expired_notifications' => $expiredNotifications,
            'auto_restored_at' => $now->toDateTimeString(),
        ]);

        $this->saveState($state);

        return $state;
    }

    /**
     * @param array<string, mixed> $state
     */
    private function saveState(array $state): void
    {
        try {
            if (!Schema::hasTable('app_settings')) {
                return;
            }

            AppSetting::query()->updateOrCreate(
                ['key' => (string) config('feature_availability.setting_key', 'system.feature_availability')],
                ['value' => $state]
            );
        } catch (\Throwable) {
            // Feature availability should fail open if settings storage is unavailable.
        }
    }

    private function parseDateTime(mixed $value): ?Carbon
    {
        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }

        try {
            return Carbon::parse($value)->timezone(self::WIB_TIMEZONE);
        } catch (\Throwable) {
            return null;
        }
    }

    private function formatDateTimeLabel(mixed $value): ?string
    {
        $date = $this->parseDateTime($value);

        return $date?->format('d/m/Y H:i');
    }
}
