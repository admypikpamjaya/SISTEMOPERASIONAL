<?php

namespace App\Support;

class AssetPublicUrl
{
    private const DEFAULT_PUBLIC_BASE_URL = 'https://soy.ypikpamjaya.com';

    public static function baseUrl(): string
    {
        $configured = trim((string) config('asset.public_base_url', self::DEFAULT_PUBLIC_BASE_URL));
        if ($configured === '') {
            $configured = self::DEFAULT_PUBLIC_BASE_URL;
        }

        if (! preg_match('/^https?:\/\//i', $configured)) {
            $configured = 'https://' . ltrim($configured, '/');
        }

        $host = parse_url($configured, PHP_URL_HOST);
        if (self::isLegacyHost(is_string($host) ? $host : null)) {
            $configured = self::DEFAULT_PUBLIC_BASE_URL;
        }

        return rtrim($configured, '/');
    }

    public static function detailPath(string $assetId): string
    {
        return '/assets/' . ltrim($assetId, '/');
    }

    public static function detailUrl(string $assetId): string
    {
        return self::urlForPath(self::detailPath($assetId));
    }

    public static function storageUrl(string $path): string
    {
        return self::urlForPath('/storage/' . ltrim($path, '/'));
    }

    public static function urlForPath(string $path): string
    {
        return self::baseUrl() . '/' . ltrim($path, '/');
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
