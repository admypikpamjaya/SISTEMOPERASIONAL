<?php

namespace App\Services\SystemManagement;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class ClientContextService
{
    /**
     * @return array<string, mixed>
     */
    public function fromRequest(Request $request): array
    {
        $userAgent = (string) $request->userAgent();
        $ip = (string) $request->ip();

        return array_merge(
            [
                'ip_address' => $ip,
                'user_agent' => substr($userAgent, 0, 65535),
            ],
            $this->parseUserAgent($userAgent),
            $this->resolveIpLocation($ip)
        );
    }

    /**
     * @return array{browser:string,platform:string,device:string}
     */
    public function parseUserAgent(string $userAgent): array
    {
        $agent = strtolower($userAgent);

        $browser = match (true) {
            str_contains($agent, 'edg/') || str_contains($agent, 'edge/') => 'Microsoft Edge',
            str_contains($agent, 'opr/') || str_contains($agent, 'opera') => 'Opera',
            str_contains($agent, 'firefox') => 'Firefox',
            str_contains($agent, 'chrome') || str_contains($agent, 'crios') => 'Chrome',
            str_contains($agent, 'safari') => 'Safari',
            default => 'Unknown',
        };

        $platform = match (true) {
            str_contains($agent, 'windows') => 'Windows',
            str_contains($agent, 'android') => 'Android',
            str_contains($agent, 'iphone') || str_contains($agent, 'ipad') => 'iOS',
            str_contains($agent, 'mac os') || str_contains($agent, 'macintosh') => 'macOS',
            str_contains($agent, 'linux') => 'Linux',
            default => 'Unknown',
        };

        $device = match (true) {
            str_contains($agent, 'ipad') || str_contains($agent, 'tablet') => 'Tablet',
            str_contains($agent, 'mobile') || str_contains($agent, 'android') || str_contains($agent, 'iphone') => 'Mobile',
            default => 'Desktop',
        };

        return compact('browser', 'platform', 'device');
    }

    /**
     * @return array<string, mixed>
     */
    public function resolveIpLocation(string $ip): array
    {
        $fallback = [
            'location_summary' => $this->isPublicIp($ip) ? 'Estimasi IP belum dikonfigurasi' : 'Jaringan lokal / privat',
            'country' => null,
            'region' => null,
            'city' => null,
            'latitude' => null,
            'longitude' => null,
        ];

        if (!$this->isPublicIp($ip)) {
            return $fallback;
        }

        $endpoint = trim((string) config('system_management.geolocation.endpoint', ''));
        if ($endpoint === '') {
            return $fallback;
        }

        return Cache::remember('system-ip-location:' . sha1($ip), now()->addHours(12), function () use ($endpoint, $ip, $fallback): array {
            try {
                $response = Http::timeout((int) config('system_management.geolocation.timeout', 2))
                    ->get(str_replace('{ip}', urlencode($ip), $endpoint));

                if (!$response->successful()) {
                    return $fallback;
                }

                $payload = $response->json();
                if (!is_array($payload)) {
                    return $fallback;
                }

                $lat = $payload['latitude'] ?? $payload['lat'] ?? null;
                $lon = $payload['longitude'] ?? $payload['lon'] ?? null;
                if ((!is_numeric($lat) || !is_numeric($lon)) && is_string($payload['loc'] ?? null)) {
                    [$lat, $lon] = array_pad(explode(',', (string) $payload['loc'], 2), 2, null);
                }

                $city = $payload['city'] ?? null;
                $region = $payload['region'] ?? $payload['regionName'] ?? null;
                $country = $payload['country'] ?? $payload['country_name'] ?? $payload['countryCode'] ?? null;
                $parts = array_values(array_filter([$city, $region, $country]));

                return [
                    'location_summary' => $parts !== [] ? implode(', ', $parts) : $fallback['location_summary'],
                    'country' => $country,
                    'region' => $region,
                    'city' => $city,
                    'latitude' => is_numeric($lat) ? (float) $lat : null,
                    'longitude' => is_numeric($lon) ? (float) $lon : null,
                ];
            } catch (\Throwable) {
                return $fallback;
            }
        });
    }

    private function isPublicIp(string $ip): bool
    {
        return filter_var(
            $ip,
            FILTER_VALIDATE_IP,
            FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
        ) !== false;
    }
}
