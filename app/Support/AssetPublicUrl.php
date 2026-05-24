<?php

namespace App\Support;

class AssetPublicUrl
{
    public static function baseUrl(): string
    {
        $configured = trim((string) config('asset.public_base_url', config('app.url')));
        if ($configured === '') {
            $configured = trim((string) config('app.url'));
        }

        return rtrim($configured, '/');
    }

    public static function detailPath(string $assetId): string
    {
        return '/assets/' . ltrim($assetId, '/');
    }

    public static function detailUrl(string $assetId): string
    {
        return self::baseUrl() . self::detailPath($assetId);
    }

    public static function currentHost(): string
    {
        return strtolower((string) request()->getHost());
    }

    public static function canonicalHost(): string
    {
        $host = parse_url(self::baseUrl(), PHP_URL_HOST);

        return strtolower((string) $host);
    }

    public static function isLegacyHost(?string $host = null): bool
    {
        $resolvedHost = strtolower(trim((string) ($host ?? self::currentHost())));
        if ($resolvedHost === '') {
            return false;
        }

        return in_array(
            $resolvedHost,
            array_map(
                static fn (string $item): string => strtolower(trim($item)),
                (array) config('asset.legacy_hosts', [])
            ),
            true
        );
    }
}
