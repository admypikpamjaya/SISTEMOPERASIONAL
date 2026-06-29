<?php

namespace App\Services\Theme;

use App\Models\AppSetting;
use Illuminate\Database\QueryException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use RuntimeException;
use Throwable;

class ThemeService
{
    private const CACHE_KEY = 'website-theme.current';
    private const SETTING_KEY = 'website_theme';

    public function defaults(): array
    {
        return $this->buildTheme([
            'primary' => '#2563EB',
            'secondary' => '#0EA5E9',
            'accent' => '#10B981',
            'sidebar' => '#1F2937',
            'background' => '#EEF3FB',
            'surface' => '#FFFFFF',
            'primary_mid' => '#3B82F6',
            'primary_dark' => '#1D4ED8',
            'primary_deeper' => '#0F2460',
            'primary_light' => '#3B82F6',
            'primary_lighter' => '#EFF6FF',
            'primary_border' => '#BFDBFE',
            'sidebar_strong' => '#111827',
            'surface_soft' => '#F7FAFF',
            'surface_muted' => '#EDF4FF',
            'border' => '#D7E0EE',
            'text' => '#0F172A',
            'text_soft' => '#334155',
            'text_muted' => '#64748B',
        ], 'default', null, null);
    }

    public function current(): array
    {
        $defaults = $this->defaults();

        try {
            return Cache::rememberForever(self::CACHE_KEY, function () use ($defaults) {
                return $this->resolveCurrent($defaults);
            });
        } catch (Throwable) {
            return $this->resolveCurrent($defaults);
        }
    }

    public function saveManual(array $colors, int|string|null $userId = null): array
    {
        return $this->save($colors, 'manual', null, $userId);
    }

    public function saveFromImage(UploadedFile $image, int|string|null $userId = null): array
    {
        $colors = $this->extractPaletteFromImage($image);

        return $this->save($colors, 'image', $image->getClientOriginalName(), $userId);
    }

    public function reset(): void
    {
        if (! $this->settingsTableAvailable()) {
            $this->forgetCache();
        } else {
            AppSetting::query()
                ->where('key', self::SETTING_KEY)
                ->delete();
        }

        if (File::exists($this->fallbackPath())) {
            File::delete($this->fallbackPath());
        }

        $this->forgetCache();
    }

    public function cssVariables(): array
    {
        $theme = $this->current();
        $colors = $theme['colors'];

        return [
            '--app-bg' => $colors['background'],
            '--app-surface' => $colors['surface'],
            '--app-surface-soft' => $colors['surface_soft'],
            '--app-surface-muted' => $colors['surface_muted'],
            '--app-border' => $colors['border'],
            '--app-text' => $colors['text'],
            '--app-text-soft' => $colors['text_soft'],
            '--app-text-muted' => $colors['text_muted'],
            '--app-accent' => $colors['primary'],
            '--app-accent-strong' => $colors['primary_dark'],
            '--app-sidebar-bg' => $colors['sidebar'],
            '--app-sidebar-strong' => $colors['sidebar_strong'],
            '--app-row-hover' => $this->hexToRgba($colors['primary'], 0.07),
            '--app-row-selected' => $this->hexToRgba($colors['primary'], 0.13),
            '--app-row-selected-strong' => $this->hexToRgba($colors['primary'], 0.22),
            '--blue-primary' => $colors['primary'],
            '--blue-mid' => $colors['primary_mid'],
            '--blue-dark' => $colors['primary_dark'],
            '--blue-deeper' => $colors['primary_deeper'],
            '--blue-light' => $colors['primary_light'],
            '--blue-lighter' => $colors['primary_lighter'],
            '--blue-border' => $colors['primary_border'],
            '--blue-glow' => $this->hexToRgba($colors['primary'], 0.24),
            '--accent' => $colors['primary_dark'],
            '--accent-cyan' => $colors['secondary'],
            '--accent-green' => $colors['accent'],
            '--primary' => $colors['primary'],
            '--primary-color' => $colors['primary'],
            '--primary-hover' => $colors['primary_dark'],
            '--primary-dark' => $colors['primary_dark'],
            '--grad' => "linear-gradient(135deg, {$colors['primary_dark']} 0%, {$colors['primary']} 55%, {$colors['primary_light']} 100%)",
            '--grad-hero' => "linear-gradient(135deg, {$colors['sidebar_strong']} 0%, {$colors['primary_dark']} 52%, {$colors['primary_mid']} 100%)",
            '--ypk-blue' => $colors['primary_mid'],
            '--ypk-blue-dark' => $colors['primary_dark'],
            '--fa-blue' => $colors['primary_mid'],
            '--fa-blue-dark' => $colors['primary_dark'],
            '--tg-primary' => $colors['primary_mid'],
            '--tg-primary-soft' => $colors['primary'],
        ];
    }

    private function save(array $colors, string $source, ?string $sourceName, int|string|null $userId): array
    {
        $theme = $this->buildTheme($colors, $source, $sourceName, $userId);
        $payload = [
            'colors' => $theme['colors'],
            'source' => $source,
            'source_name' => $sourceName,
            'updated_by' => $userId,
            'updated_at' => now()->toDateTimeString(),
        ];
        $databaseSaved = false;

        if ($this->settingsTableAvailable()) {
            try {
                AppSetting::query()->updateOrCreate(
                    ['key' => self::SETTING_KEY],
                    ['value' => $payload]
                );

                $databaseSaved = true;
            } catch (Throwable $exception) {
                report($exception);
            }
        }

        if (! $databaseSaved) {
            $this->writeFallbackValue($payload);
        } else {
            $this->writeFallbackValue($payload, true);
        }

        $this->forgetCache();

        return $this->current();
    }

    private function resolveCurrent(array $defaults): array
    {
        $stored = $this->readStoredValue();
        $colors = is_array($stored['colors'] ?? null) ? $stored['colors'] : [];

        if ($colors === []) {
            return $defaults;
        }

        return $this->buildTheme(
            array_merge($defaults['colors'], $colors),
            (string) ($stored['source'] ?? 'manual'),
            $stored['source_name'] ?? null,
            $stored['updated_by'] ?? null,
            $stored['updated_at'] ?? null
        );
    }

    private function readStoredValue(): array
    {
        if ($this->settingsTableAvailable()) {
            try {
                $setting = AppSetting::query()
                    ->where('key', self::SETTING_KEY)
                    ->first();

                if (is_array($setting?->value)) {
                    return $setting->value;
                }
            } catch (Throwable $exception) {
                report($exception);
            }
        }

        return $this->readFallbackValue();
    }

    private function readFallbackValue(): array
    {
        $path = $this->fallbackPath();

        if (! File::exists($path)) {
            return [];
        }

        try {
            $decoded = json_decode((string) File::get($path), true, 512, JSON_THROW_ON_ERROR);

            return is_array($decoded) ? $decoded : [];
        } catch (Throwable $exception) {
            report($exception);

            return [];
        }
    }

    private function writeFallbackValue(array $payload, bool $bestEffort = false): void
    {
        try {
            $directory = dirname($this->fallbackPath());

            if (! File::isDirectory($directory)) {
                File::makeDirectory($directory, 0755, true);
            }

            File::put($this->fallbackPath(), json_encode($payload, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR));
        } catch (Throwable $exception) {
            report($exception);

            if (! $bestEffort) {
                throw new RuntimeException(__('app.website_theme.saved_failed'));
            }
        }
    }

    private function fallbackPath(): string
    {
        return storage_path('app/website-theme.json');
    }

    private function forgetCache(): void
    {
        try {
            Cache::forget(self::CACHE_KEY);
        } catch (Throwable $exception) {
            report($exception);
        }
    }

    private function buildTheme(
        array $colors,
        string $source,
        ?string $sourceName,
        int|string|null $updatedBy,
        ?string $updatedAt = null
    ): array {
        $base = [
            'primary' => $this->normalizeHex($colors['primary'] ?? '#2563EB'),
            'secondary' => $this->normalizeHex($colors['secondary'] ?? '#0EA5E9'),
            'accent' => $this->normalizeHex($colors['accent'] ?? '#10B981'),
            'sidebar' => $this->normalizeHex($colors['sidebar'] ?? '#0F172A'),
            'background' => $this->normalizeHex($colors['background'] ?? '#EEF3FB'),
            'surface' => $this->normalizeHex($colors['surface'] ?? '#FFFFFF'),
        ];

        $base['primary_mid'] = $this->normalizeHex($colors['primary_mid'] ?? $this->mix($base['primary'], '#FFFFFF', 0.16));
        $base['primary_dark'] = $this->normalizeHex($colors['primary_dark'] ?? $this->mix($base['primary'], '#000000', 0.24));
        $base['primary_deeper'] = $this->normalizeHex($colors['primary_deeper'] ?? $this->mix($base['primary'], '#000000', 0.52));
        $base['primary_light'] = $this->normalizeHex($colors['primary_light'] ?? $this->mix($base['primary'], '#FFFFFF', 0.38));
        $base['primary_lighter'] = $this->normalizeHex($colors['primary_lighter'] ?? $this->mix($base['primary'], '#FFFFFF', 0.88));
        $base['primary_border'] = $this->normalizeHex($colors['primary_border'] ?? $this->mix($base['primary'], '#FFFFFF', 0.68));
        $base['sidebar_strong'] = $this->normalizeHex($colors['sidebar_strong'] ?? $this->mix($base['sidebar'], '#000000', 0.34));
        $base['surface_soft'] = $this->normalizeHex($colors['surface_soft'] ?? $this->mix($base['surface'], $base['primary'], 0.05));
        $base['surface_muted'] = $this->normalizeHex($colors['surface_muted'] ?? $this->mix($base['surface'], $base['primary'], 0.1));
        $base['border'] = $this->normalizeHex($colors['border'] ?? $this->mix($base['primary'], '#D7E0EE', 0.78));
        $base['text'] = $this->normalizeHex($colors['text'] ?? $this->readableTextColor($base['surface']));
        $base['text_soft'] = $this->normalizeHex($colors['text_soft'] ?? $this->mix($base['text'], $base['surface'], 0.24));
        $base['text_muted'] = $this->normalizeHex($colors['text_muted'] ?? $this->mix($base['text'], $base['surface'], 0.48));

        return [
            'colors' => $base,
            'source' => $source,
            'source_name' => $sourceName,
            'updated_by' => $updatedBy,
            'updated_at' => $updatedAt,
        ];
    }

    private function extractPaletteFromImage(UploadedFile $image): array
    {
        $path = $image->getRealPath();

        if ($path === false || ! is_file($path)) {
            throw new RuntimeException(__('app.website_theme.image_failed'));
        }

        $info = @getimagesize($path);
        $mime = $info['mime'] ?? '';

        $resource = match ($mime) {
            'image/jpeg' => @imagecreatefromjpeg($path),
            'image/png' => @imagecreatefrompng($path),
            'image/webp' => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($path) : false,
            default => false,
        };

        if (! $resource) {
            throw new RuntimeException(__('app.website_theme.image_failed'));
        }

        $width = imagesx($resource);
        $height = imagesy($resource);
        $step = max(1, (int) floor(max($width, $height) / 96));
        $buckets = [];

        for ($y = 0; $y < $height; $y += $step) {
            for ($x = 0; $x < $width; $x += $step) {
                $rgb = imagecolorat($resource, $x, $y);
                $r = ($rgb >> 16) & 0xFF;
                $g = ($rgb >> 8) & 0xFF;
                $b = $rgb & 0xFF;

                $max = max($r, $g, $b);
                $min = min($r, $g, $b);
                $brightness = ($r * 299 + $g * 587 + $b * 114) / 1000;
                $saturation = $max === 0 ? 0 : ($max - $min) / $max;

                if ($brightness < 36 || $brightness > 238 || $saturation < 0.14) {
                    continue;
                }

                $key = implode(',', [
                    (int) (round($r / 24) * 24),
                    (int) (round($g / 24) * 24),
                    (int) (round($b / 24) * 24),
                ]);

                $buckets[$key] = ($buckets[$key] ?? 0) + 1;
            }
        }

        imagedestroy($resource);

        if ($buckets === []) {
            throw new RuntimeException(__('app.website_theme.image_no_palette'));
        }

        arsort($buckets);
        $dominants = array_slice(array_keys($buckets), 0, 8);
        $hexes = array_map(function (string $bucket): string {
            [$r, $g, $b] = array_map('intval', explode(',', $bucket));

            return $this->rgbToHex(
                min(255, max(0, $r)),
                min(255, max(0, $g)),
                min(255, max(0, $b))
            );
        }, $dominants);

        $primary = $hexes[0] ?? '#2563EB';
        $secondary = $this->pickDistinctColor($hexes, $primary, '#0EA5E9');
        $accent = $this->pickDistinctColor($hexes, $secondary, '#10B981', [$primary]);

        return [
            'primary' => $this->ensureColorUsable($primary),
            'secondary' => $this->ensureColorUsable($secondary),
            'accent' => $this->ensureColorUsable($accent),
            'sidebar' => $this->mix($primary, '#020617', 0.72),
            'background' => $this->mix($primary, '#F8FAFC', 0.92),
            'surface' => $this->mix($secondary, '#FFFFFF', 0.94),
        ];
    }

    private function pickDistinctColor(array $hexes, string $base, string $fallback, array $alsoAvoid = []): string
    {
        foreach ($hexes as $hex) {
            if ($this->colorDistance($hex, $base) < 74) {
                continue;
            }

            foreach ($alsoAvoid as $avoid) {
                if ($this->colorDistance($hex, $avoid) < 74) {
                    continue 2;
                }
            }

            return $hex;
        }

        return $fallback;
    }

    private function ensureColorUsable(string $hex): string
    {
        $rgb = $this->hexToRgb($hex);
        $brightness = ($rgb[0] * 299 + $rgb[1] * 587 + $rgb[2] * 114) / 1000;

        if ($brightness < 72) {
            return $this->mix($hex, '#FFFFFF', 0.24);
        }

        if ($brightness > 210) {
            return $this->mix($hex, '#000000', 0.18);
        }

        return $hex;
    }

    private function settingsTableAvailable(): bool
    {
        try {
            return Schema::hasTable('app_settings');
        } catch (QueryException) {
            return false;
        } catch (Throwable) {
            return false;
        }
    }

    private function normalizeHex(string $hex): string
    {
        $hex = strtoupper(trim($hex));

        if (preg_match('/^#([0-9A-F]{3})$/', $hex, $matches)) {
            return '#' . $matches[1][0] . $matches[1][0]
                . $matches[1][1] . $matches[1][1]
                . $matches[1][2] . $matches[1][2];
        }

        if (preg_match('/^#([0-9A-F]{6})$/', $hex)) {
            return $hex;
        }

        return '#2563EB';
    }

    private function mix(string $from, string $to, float $amount): string
    {
        $fromRgb = $this->hexToRgb($from);
        $toRgb = $this->hexToRgb($to);

        return $this->rgbToHex(
            (int) round($fromRgb[0] + (($toRgb[0] - $fromRgb[0]) * $amount)),
            (int) round($fromRgb[1] + (($toRgb[1] - $fromRgb[1]) * $amount)),
            (int) round($fromRgb[2] + (($toRgb[2] - $fromRgb[2]) * $amount))
        );
    }

    private function hexToRgb(string $hex): array
    {
        $hex = ltrim($this->normalizeHex($hex), '#');

        return [
            hexdec(substr($hex, 0, 2)),
            hexdec(substr($hex, 2, 2)),
            hexdec(substr($hex, 4, 2)),
        ];
    }

    private function rgbToHex(int $r, int $g, int $b): string
    {
        return sprintf('#%02X%02X%02X', $r, $g, $b);
    }

    private function hexToRgba(string $hex, float $alpha): string
    {
        [$r, $g, $b] = $this->hexToRgb($hex);

        return sprintf('rgba(%d, %d, %d, %.2F)', $r, $g, $b, $alpha);
    }

    private function readableTextColor(string $background): string
    {
        [$r, $g, $b] = $this->hexToRgb($background);
        $brightness = ($r * 299 + $g * 587 + $b * 114) / 1000;

        return $brightness > 140 ? '#0F172A' : '#E2E8F0';
    }

    private function colorDistance(string $a, string $b): float
    {
        $aRgb = $this->hexToRgb($a);
        $bRgb = $this->hexToRgb($b);

        return sqrt(
            (($aRgb[0] - $bRgb[0]) ** 2)
            + (($aRgb[1] - $bRgb[1]) ** 2)
            + (($aRgb[2] - $bRgb[2]) ** 2)
        );
    }
}
