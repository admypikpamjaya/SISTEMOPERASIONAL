<?php

namespace App\Services\Theme;

use App\Models\AppSetting;
use Illuminate\Database\QueryException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
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

    public function cssVariables(?array $theme = null): array
    {
        $theme ??= $this->current();
        $colors = $theme['colors'];
        $primaryRgba = fn (float $alpha): string => $this->hexToRgba($colors['primary'], $alpha);
        $accentRgba = fn (float $alpha): string => $this->hexToRgba($colors['accent'], $alpha);
        $danger = '#EF4444';
        $warning = '#F59E0B';

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
            '--app-row-hover' => $primaryRgba(0.07),
            '--app-row-selected' => $primaryRgba(0.13),
            '--app-row-selected-strong' => $primaryRgba(0.22),
            '--app-shadow' => "0 18px 38px {$primaryRgba(0.12)}",
            '--radius-xs' => '6px',
            '--radius-sm' => '8px',
            '--radius' => '12px',
            '--radius-md' => '14px',
            '--radius-lg' => '18px',
            '--radius-xl' => '24px',
            '--blue-primary' => $colors['primary'],
            '--blue-secondary' => $colors['secondary'],
            '--blue-mid' => $colors['primary_mid'],
            '--blue-dark' => $colors['primary_dark'],
            '--blue-deeper' => $colors['primary_deeper'],
            '--blue-light' => $colors['primary_light'],
            '--blue-lighter' => $colors['primary_lighter'],
            '--blue-border' => $colors['primary_border'],
            '--blue-glow' => $primaryRgba(0.24),
            '--border-blue' => $primaryRgba(0.18),
            '--shadow-blue' => $primaryRgba(0.18),
            '--accent' => $colors['primary_dark'],
            '--accent-cyan' => $colors['secondary'],
            '--accent-green' => $colors['accent'],
            '--accent-purple' => $this->mix($colors['primary'], '#8B5CF6', 0.45),
            '--accent-red' => $danger,
            '--accent-amber' => $warning,
            '--primary' => $colors['primary'],
            '--primary-color' => $colors['primary'],
            '--primary-hover' => $colors['primary_dark'],
            '--primary-dark' => $colors['primary_dark'],
            '--secondary-color' => $colors['secondary'],
            '--grad' => "linear-gradient(135deg, {$colors['primary_dark']} 0%, {$colors['primary']} 55%, {$colors['primary_light']} 100%)",
            '--grad-hero' => "linear-gradient(135deg, {$colors['sidebar_strong']} 0%, {$colors['primary_dark']} 52%, {$colors['primary_mid']} 100%)",
            '--grad-warn' => "linear-gradient(135deg, {$warning} 0%, #D97706 100%)",
            '--surface-bg' => $colors['background'],
            '--surface-card' => $colors['surface'],
            '--surface-dark' => $colors['sidebar_strong'],
            '--surface' => $colors['surface'],
            '--surface-alt' => $colors['surface_soft'],
            '--surf-alt' => $colors['surface_soft'],
            '--bg' => $colors['background'],
            '--bg-main' => $colors['background'],
            '--light-bg' => $colors['surface_soft'],
            '--card' => $colors['surface'],
            '--border' => $colors['border'],
            '--border-color' => $colors['border'],
            '--border-light' => $primaryRgba(0.14),
            '--border-table' => $colors['border'],
            '--text' => $colors['text'],
            '--muted' => $colors['text_muted'],
            '--text-primary' => $colors['text'],
            '--text-secondary' => $colors['text_soft'],
            '--text-muted' => $colors['text_muted'],
            '--text-dark' => $colors['text'],
            '--text-mid' => $colors['text_soft'],
            '--text-light' => $colors['text_muted'],
            '--white' => $colors['surface'],
            '--navy' => $colors['sidebar_strong'],
            '--navy-mid' => $colors['sidebar'],
            '--navy-light' => $colors['sidebar'],
            '--dark-nav' => $colors['sidebar'],
            '--green' => $colors['accent'],
            '--green-bg' => $accentRgba(0.12),
            '--green-border' => $accentRgba(0.24),
            '--red' => '#FCA5A5',
            '--red-bg' => 'rgba(239, 68, 68, 0.12)',
            '--red-border' => 'rgba(239, 68, 68, 0.22)',
            '--yellow' => '#FCD34D',
            '--yellow-bg' => 'rgba(245, 158, 11, 0.14)',
            '--yellow-border' => 'rgba(245, 158, 11, 0.26)',
            '--warning-bg' => 'rgba(245, 158, 11, 0.14)',
            '--warning-border' => 'rgba(245, 158, 11, 0.28)',
            '--warning-text' => '#FBBF24',
            '--pin-bg' => 'rgba(245, 158, 11, 0.14)',
            '--info-bg' => $primaryRgba(0.12),
            '--info-b' => $primaryRgba(0.24),
            '--d-bg' => 'rgba(239, 68, 68, 0.12)',
            '--d-b' => 'rgba(239, 68, 68, 0.22)',
            '--w-bg' => 'rgba(245, 158, 11, 0.14)',
            '--w-b' => 'rgba(245, 158, 11, 0.26)',
            '--s-bg' => $accentRgba(0.12),
            '--s-b' => $accentRgba(0.24),
            '--success' => $colors['accent'],
            '--success-color' => $colors['accent'],
            '--danger' => $danger,
            '--danger-color' => $danger,
            '--warn' => $warning,
            '--focus' => $primaryRgba(0.2),
            '--shadow' => "0 18px 40px {$primaryRgba(0.14)}",
            '--shadow-sm' => "0 1px 3px {$primaryRgba(0.12)}",
            '--shadow-md' => "0 4px 16px {$primaryRgba(0.16)}",
            '--shadow-lg' => "0 10px 40px {$primaryRgba(0.18)}",
            '--shadow-glow' => "0 0 0 3px {$primaryRgba(0.16)}",
            '--card-shadow' => "0 12px 30px {$primaryRgba(0.12)}",
            '--card-shadow-hover' => "0 16px 38px {$primaryRgba(0.18)}",
            '--ypk-blue' => $colors['primary_mid'],
            '--ypk-blue-dark' => $colors['primary_dark'],
            '--ypk-blue-soft' => $colors['primary_lighter'],
            '--ypk-blue-50' => $colors['primary_lighter'],
            '--ypk-blue-100' => $colors['primary_border'],
            '--ypk-blue-700' => $colors['primary_dark'],
            '--ypk-blue-800' => $colors['primary_deeper'],
            '--ypk-blue-900' => $colors['primary_deeper'],
            '--ypk-text' => $colors['text'],
            '--ypk-muted' => $colors['text_muted'],
            '--ypk-border' => $colors['border'],
            '--ypk-bg' => $colors['background'],
            '--ypk-card' => $colors['surface'],
            '--ypk-shadow' => "0 24px 56px {$primaryRgba(0.16)}",
            '--ypk-text-500' => $colors['text_muted'],
            '--ypk-text-700' => $colors['text_soft'],
            '--ypk-text-900' => $colors['text'],
            '--fa-blue' => $colors['primary_mid'],
            '--fa-blue-dark' => $colors['primary_dark'],
            '--fa-green' => $colors['accent'],
            '--fa-red' => $danger,
            '--fa-bg' => $colors['background'],
            '--fa-card' => $colors['surface'],
            '--fa-border' => $colors['border'],
            '--fa-text' => $colors['text'],
            '--fa-muted' => $colors['text_muted'],
            '--fa-shadow' => "0 18px 40px {$primaryRgba(0.14)}",
            '--fa-radius' => '18px',
            '--tg-primary' => $colors['primary_mid'],
            '--tg-primary-soft' => $colors['primary'],
            '--tg-bg' => $colors['background'],
            '--tg-card' => $colors['surface'],
            '--tg-border' => $colors['border'],
            '--tg-text' => $colors['text'],
            '--tg-muted' => $colors['text_muted'],
            '--tg-success' => $colors['accent'],
            '--tg-danger' => $danger,
            '--tg-warning' => $warning,
            '--tg-shadow' => "0 18px 40px {$primaryRgba(0.14)}",
            '--tpl-blue-50' => $colors['primary_lighter'],
            '--tpl-blue-100' => $colors['primary_border'],
            '--tpl-blue-600' => $colors['primary'],
            '--tpl-blue-700' => $colors['primary_dark'],
            '--tpl-blue-800' => $colors['primary_deeper'],
            '--tpl-blue-900' => $colors['primary_deeper'],
            '--tpl-text-500' => $colors['text_muted'],
            '--tpl-text-700' => $colors['text_soft'],
            '--tpl-text-900' => $colors['text'],
            '--tpl-border' => $colors['border'],
            '--tpl-bg' => $colors['surface_soft'],
            '--emp-blue-50' => $colors['primary_lighter'],
            '--emp-blue-100' => $colors['primary_border'],
            '--emp-blue-700' => $colors['primary_dark'],
            '--emp-blue-800' => $colors['primary_deeper'],
            '--emp-blue-900' => $colors['primary_deeper'],
            '--emp-text-500' => $colors['text_muted'],
            '--emp-text-700' => $colors['text_soft'],
            '--emp-text-900' => $colors['text'],
            '--emp-border' => $colors['border'],
            '--emp-bg' => $colors['background'],
            '--wa-green' => $colors['accent'],
            '--wa-dark' => $colors['sidebar_strong'],
            ...$this->moduleVariables('fs', $colors, $danger, $warning),
            ...$this->moduleVariables('gl', $colors, $danger, $warning),
            ...$this->moduleVariables('pl', $colors, $danger, $warning),
            ...$this->moduleVariables('ji', $colors, $danger, $warning),
            ...$this->moduleVariables('fd', $colors, $danger, $warning),
            ...$this->moduleVariables('adl', $colors, $danger, $warning),
        ];
    }

    private function moduleVariables(string $prefix, array $colors, string $danger, string $warning): array
    {
        return [
            "--{$prefix}-bg" => $colors['background'],
            "--{$prefix}-card" => $colors['surface'],
            "--{$prefix}-card-soft" => $colors['surface_soft'],
            "--{$prefix}-border" => $colors['border'],
            "--{$prefix}-text" => $colors['text'],
            "--{$prefix}-text-soft" => $colors['text_soft'],
            "--{$prefix}-muted" => $colors['text_muted'],
            "--{$prefix}-blue" => $colors['primary'],
            "--{$prefix}-blue-dark" => $colors['primary_dark'],
            "--{$prefix}-blue-soft" => $colors['primary_lighter'],
            "--{$prefix}-cyan" => $colors['secondary'],
            "--{$prefix}-green" => $colors['accent'],
            "--{$prefix}-green-soft" => $this->hexToRgba($colors['accent'], 0.12),
            "--{$prefix}-red" => $danger,
            "--{$prefix}-red-soft" => 'rgba(239, 68, 68, 0.12)',
            "--{$prefix}-amber" => $warning,
            "--{$prefix}-amber-soft" => 'rgba(245, 158, 11, 0.14)',
            "--{$prefix}-shadow" => "0 18px 40px {$this->hexToRgba($colors['primary'], 0.14)}",
            "--{$prefix}-radius" => '18px',
            "--{$prefix}-radius-sm" => '12px',
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

        Log::info('[WEBSITE THEME SAVE REQUEST]', [
            'source' => $source,
            'source_name' => $sourceName,
            'updated_by' => $userId,
            'primary' => $theme['colors']['primary'] ?? null,
        ]);

        if ($this->settingsTableAvailable()) {
            try {
                AppSetting::query()->updateOrCreate(
                    ['key' => self::SETTING_KEY],
                    ['value' => $payload]
                );

                $databaseSaved = true;
            } catch (Throwable $exception) {
                Log::warning('[WEBSITE THEME DATABASE SAVE FAILED]', [
                    'source' => $source,
                    'message' => $exception->getMessage(),
                ]);
                report($exception);
            }
        }

        if (! $databaseSaved) {
            $this->writeFallbackValue($payload);
        } else {
            $this->writeFallbackValue($payload, true);
        }

        $this->forgetCache();

        Log::info('[WEBSITE THEME SAVE SUCCESS]', [
            'source' => $source,
            'storage' => $databaseSaved ? 'database' : 'file',
            'updated_by' => $userId,
        ]);

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

        $info = @getimagesize($path) ?: [];
        $mime = strtolower((string) ($info['mime'] ?? $image->getMimeType() ?? $image->getClientMimeType() ?? ''));

        Log::info('[WEBSITE THEME IMAGE PALETTE START]', [
            'name' => $image->getClientOriginalName(),
            'mime' => $mime,
            'size' => $image->getSize(),
        ]);

        if ($mime === 'image/svg+xml' || str_ends_with(strtolower($image->getClientOriginalName()), '.svg')) {
            $palette = $this->extractPaletteFromSvg($path);

            Log::info('[WEBSITE THEME IMAGE PALETTE SUCCESS]', [
                'source' => 'svg',
                'colors' => $palette,
            ]);

            return $palette;
        }

        $resource = $this->createImageResource($path, $mime);

        $width = imagesx($resource);
        $height = imagesy($resource);
        $step = max(1, (int) floor(sqrt(max(1, ($width * $height) / 14000))));
        $buckets = [];

        for ($y = 0; $y < $height; $y += $step) {
            for ($x = 0; $x < $width; $x += $step) {
                $rgba = imagecolorsforindex($resource, imagecolorat($resource, $x, $y));

                if (($rgba['alpha'] ?? 0) > 112) {
                    continue;
                }

                $r = (int) ($rgba['red'] ?? 0);
                $g = (int) ($rgba['green'] ?? 0);
                $b = (int) ($rgba['blue'] ?? 0);

                $max = max($r, $g, $b);
                $min = min($r, $g, $b);
                $brightness = ($r * 299 + $g * 587 + $b * 114) / 1000;
                $saturation = $max === 0 ? 0 : ($max - $min) / $max;

                if ($brightness < 8 || $brightness > 248) {
                    continue;
                }

                $key = implode(',', [
                    (int) (round($r / 16) * 16),
                    (int) (round($g / 16) * 16),
                    (int) (round($b / 16) * 16),
                ]);

                $buckets[$key] ??= [
                    'count' => 0,
                    'r' => 0,
                    'g' => 0,
                    'b' => 0,
                    'saturation' => 0,
                    'brightness' => 0,
                ];

                $buckets[$key]['count']++;
                $buckets[$key]['r'] += $r;
                $buckets[$key]['g'] += $g;
                $buckets[$key]['b'] += $b;
                $buckets[$key]['saturation'] += $saturation;
                $buckets[$key]['brightness'] += $brightness;
            }
        }

        imagedestroy($resource);

        if ($buckets === []) {
            Log::warning('[WEBSITE THEME IMAGE PALETTE EMPTY]', [
                'name' => $image->getClientOriginalName(),
                'mime' => $mime,
            ]);

            throw new RuntimeException(__('app.website_theme.image_no_palette'));
        }

        uasort($buckets, function (array $left, array $right): int {
            return $this->bucketScore($right) <=> $this->bucketScore($left);
        });

        $hexes = array_map(function (array $bucket): string {
            $count = max(1, (int) $bucket['count']);

            return $this->rgbToHex(
                (int) round($bucket['r'] / $count),
                (int) round($bucket['g'] / $count),
                (int) round($bucket['b'] / $count)
            );
        }, array_slice($buckets, 0, 12));

        $palette = $this->paletteFromHexes($hexes);

        Log::info('[WEBSITE THEME IMAGE PALETTE SUCCESS]', [
            'source' => 'raster',
            'mime' => $mime,
            'sampled_colors' => array_slice($hexes, 0, 6),
            'colors' => $palette,
        ]);

        return $palette;
    }

    private function createImageResource(string $path, string $mime)
    {
        $resource = match ($mime) {
            'image/jpeg', 'image/jpg' => function_exists('imagecreatefromjpeg') ? @imagecreatefromjpeg($path) : false,
            'image/png' => function_exists('imagecreatefrompng') ? @imagecreatefrompng($path) : false,
            'image/webp' => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($path) : false,
            'image/gif' => function_exists('imagecreatefromgif') ? @imagecreatefromgif($path) : false,
            'image/bmp', 'image/x-ms-bmp' => function_exists('imagecreatefrombmp') ? @imagecreatefrombmp($path) : false,
            'image/avif' => function_exists('imagecreatefromavif') ? @imagecreatefromavif($path) : false,
            default => false,
        };

        if (! $resource && function_exists('imagecreatefromstring')) {
            $resource = @imagecreatefromstring((string) @file_get_contents($path));
        }

        if (! $resource) {
            $resource = $this->createImageResourceWithImagick($path);
        }

        if (! $resource) {
            Log::warning('[WEBSITE THEME IMAGE UNSUPPORTED]', [
                'mime' => $mime,
                'gd' => extension_loaded('gd'),
            ]);

            throw new RuntimeException(__('app.website_theme.image_failed'));
        }

        return $resource;
    }

    private function createImageResourceWithImagick(string $path)
    {
        if (! class_exists(\Imagick::class) || ! function_exists('imagecreatefromstring')) {
            return false;
        }

        try {
            $imagick = new \Imagick();
            $imagick->readImage($path);
            $imagick->setIteratorIndex(0);
            $imagick->setImageBackgroundColor('white');

            if (method_exists($imagick, 'mergeImageLayers')) {
                $imagick = $imagick->mergeImageLayers(\Imagick::LAYERMETHOD_FLATTEN);
            }

            $imagick->setImageFormat('png');
            $blob = $imagick->getImagesBlob();
            $resource = @imagecreatefromstring($blob);
            $imagick->clear();
            $imagick->destroy();

            return $resource ?: false;
        } catch (Throwable $exception) {
            Log::warning('[WEBSITE THEME IMAGE IMAGICK FAILED]', [
                'message' => $exception->getMessage(),
            ]);

            return false;
        }
    }

    private function extractPaletteFromSvg(string $path): array
    {
        $content = (string) File::get($path);
        $hexes = [];

        preg_match_all('/#(?:[0-9a-fA-F]{3}|[0-9a-fA-F]{6})\b/', $content, $hexMatches);
        foreach ($hexMatches[0] ?? [] as $hex) {
            $hexes[] = $this->normalizeHex($hex);
        }

        preg_match_all('/rgb\(\s*(\d{1,3})\s*,\s*(\d{1,3})\s*,\s*(\d{1,3})\s*\)/i', $content, $rgbMatches, PREG_SET_ORDER);
        foreach ($rgbMatches as $match) {
            $hexes[] = $this->rgbToHex(
                min(255, (int) $match[1]),
                min(255, (int) $match[2]),
                min(255, (int) $match[3])
            );
        }

        $hexes = array_values(array_filter(array_unique($hexes), function (string $hex): bool {
            return ! in_array($hex, ['#FFFFFF', '#000000'], true);
        }));

        if ($hexes === []) {
            Log::warning('[WEBSITE THEME SVG PALETTE EMPTY]');

            throw new RuntimeException(__('app.website_theme.image_no_palette'));
        }

        usort($hexes, fn (string $left, string $right): int => $this->colorWeight($right) <=> $this->colorWeight($left));

        return $this->paletteFromHexes($hexes);
    }

    private function paletteFromHexes(array $hexes): array
    {
        $hexes = array_values(array_unique(array_map(fn (string $hex): string => $this->normalizeHex($hex), $hexes)));
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

    private function bucketScore(array $bucket): float
    {
        $count = max(1, (int) $bucket['count']);
        $saturation = ((float) $bucket['saturation']) / $count;
        $brightness = ((float) $bucket['brightness']) / $count;
        $brightnessScore = 1 - min(1, abs($brightness - 138) / 138);

        return $count * (0.62 + $saturation) * (0.45 + $brightnessScore);
    }

    private function colorWeight(string $hex): float
    {
        [$r, $g, $b] = $this->hexToRgb($hex);
        $max = max($r, $g, $b);
        $min = min($r, $g, $b);
        $brightness = ($r * 299 + $g * 587 + $b * 114) / 1000;
        $saturation = $max === 0 ? 0 : ($max - $min) / $max;

        return (0.62 + $saturation) * (1 - min(1, abs($brightness - 138) / 138));
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
