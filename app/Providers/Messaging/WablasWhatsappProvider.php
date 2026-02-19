<?php

namespace App\Providers\Messaging;

use App\Contracts\Messaging\WhatsappProviderInterface;
use App\DataTransferObjects\BlastPayload;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WablasWhatsappProvider implements WhatsappProviderInterface
{
    private const DEFAULT_FALLBACK_BASE_URLS = [
        'https://tegal.wablas.com',
        'https://solo.wablas.com',
        'https://jogja.wablas.com',
        'https://kudus.wablas.com',
        'https://pati.wablas.com',
        'https://sby.wablas.com',
        'https://bdg.wablas.com',
        'https://deu.wablas.com',
        'https://texas.wablas.com',
    ];

    private static ?string $resolvedBaseUrl = null;

    public function send(string $to, BlastPayload $payload): bool
    {
        try {
            $token = trim((string) config('services.wablas.token'));
            $secretKey = trim((string) config('services.wablas.secret_key'));
            $configuredBaseUrl = rtrim(
                (string) config('services.wablas.base_url', 'https://wablas.com'),
                '/'
            );

            if ($token === '' || $secretKey === '') {
                Log::error('[WABLAS CONFIG MISSING]', [
                    'token_present' => $token !== '',
                    'secret_key_present' => $secretKey !== '',
                ]);
                return false;
            }

            $endpoint = '/api/send-message';
            $requestPayload = [
                'phone' => $to,
                'message' => $payload->message,
            ];

            if (!empty($payload->attachments)) {
                $attachment = $payload->attachments[0];
                $attachmentUrl = $this->resolveAttachmentUrl($attachment->path);

                if ($attachmentUrl === null) {
                    Log::error('[WABLAS ATTACHMENT URL FAILED]', [
                        'to' => $to,
                        'path' => $attachment->path,
                    ]);
                    return false;
                }

                [$endpoint, $requestPayload] = $this->buildAttachmentPayload(
                    to: $to,
                    message: $payload->message,
                    attachmentUrl: $attachmentUrl,
                    mime: $attachment->mime
                );
            }

            $attempts = [];
            foreach ($this->resolveBaseUrls($configuredBaseUrl) as $baseUrl) {
                $response = Http::timeout(20)
                    ->withHeaders([
                        'Authorization' => $token . '.' . $secretKey,
                    ])
                    ->post($baseUrl . $endpoint, $requestPayload);

                $decoded = $response->json();
                $message = $this->extractResponseMessage(
                    $decoded,
                    (string) $response->body()
                );

                if (
                    $response->successful()
                    && (
                        !is_array($decoded)
                        || !array_key_exists('status', $decoded)
                        || (bool) $decoded['status'] === true
                    )
                ) {
                    self::$resolvedBaseUrl = $baseUrl;
                    return true;
                }

                $attempts[] = [
                    'base_url' => $baseUrl,
                    'status_code' => $response->status(),
                    'message' => $message,
                ];

                if (!$this->shouldTryNextBaseUrl($message)) {
                    break;
                }
            }

            $providerError = $this->buildProviderErrorMessage($attempts);
            $payload->setMeta('provider_error', $providerError);

            Log::error('[WABLAS FAILED]', [
                'to' => $to,
                'endpoint' => $endpoint,
                'attempts' => $attempts,
            ]);
            return false;
        } catch (\Throwable $exception) {
            $payload->setMeta('provider_error', $exception->getMessage());
            Log::error('[WABLAS ERROR]', [
                'to' => $to,
                'error' => $exception->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * @return array{0:string,1:array<string,string>}
     */
    private function buildAttachmentPayload(
        string $to,
        string $message,
        string $attachmentUrl,
        string $mime
    ): array {
        $normalizedMime = strtolower(trim($mime));

        if (str_starts_with($normalizedMime, 'image/')) {
            return [
                '/api/send-image',
                [
                    'phone' => $to,
                    'caption' => $message,
                    'image' => $attachmentUrl,
                ],
            ];
        }

        if (str_starts_with($normalizedMime, 'video/')) {
            return [
                '/api/send-video',
                [
                    'phone' => $to,
                    'caption' => $message,
                    'video' => $attachmentUrl,
                ],
            ];
        }

        if (str_starts_with($normalizedMime, 'audio/')) {
            return [
                '/api/send-audio',
                [
                    'phone' => $to,
                    'audio' => $attachmentUrl,
                ],
            ];
        }

        return [
            '/api/send-document',
            [
                'phone' => $to,
                'caption' => $message,
                'document' => $attachmentUrl,
            ],
        ];
    }

    /**
     * @return string[]
     */
    private function resolveBaseUrls(string $configuredBaseUrl): array
    {
        $candidates = [];

        if (self::$resolvedBaseUrl !== null) {
            $candidates[] = self::$resolvedBaseUrl;
        }

        $candidates[] = $configuredBaseUrl;

        $rawFallback = trim((string) config('services.wablas.fallback_base_urls', ''));
        $fallbackBaseUrls = $rawFallback === ''
            ? self::DEFAULT_FALLBACK_BASE_URLS
            : explode(',', $rawFallback);

        foreach ($fallbackBaseUrls as $candidate) {
            $normalized = $this->normalizeBaseUrl((string) $candidate);
            if ($normalized !== null) {
                $candidates[] = $normalized;
            }
        }

        $normalizedCandidates = [];
        foreach ($candidates as $candidate) {
            $normalized = $this->normalizeBaseUrl((string) $candidate);
            if ($normalized !== null) {
                $normalizedCandidates[] = $normalized;
            }
        }

        return array_values(array_unique($normalizedCandidates));
    }

    private function normalizeBaseUrl(string $baseUrl): ?string
    {
        $trimmed = trim($baseUrl);
        if ($trimmed === '') {
            return null;
        }

        if (!preg_match('/^https?:\\/\\//i', $trimmed)) {
            $trimmed = 'https://' . $trimmed;
        }

        return rtrim($trimmed, '/');
    }

    private function extractResponseMessage(mixed $decoded, string $rawBody): string
    {
        if (is_array($decoded)) {
            $message = trim((string) ($decoded['message'] ?? ''));
            if ($message !== '') {
                return $message;
            }

            $reason = trim((string) ($decoded['reason'] ?? ''));
            if ($reason !== '') {
                return $reason;
            }
        }

        return trim($rawBody);
    }

    private function shouldTryNextBaseUrl(string $message): bool
    {
        $normalized = strtolower($message);

        return str_contains($normalized, 'api access is not allowed on this server')
            || str_contains($normalized, 'token invalid')
            || str_contains($normalized, 'device expired')
            || str_contains($normalized, 'not connected');
    }

    /**
     * @param array<int, array<string, mixed>> $attempts
     */
    private function buildProviderErrorMessage(array $attempts): string
    {
        if ($attempts === []) {
            return 'Wablas request failed without response.';
        }

        $last = end($attempts);
        $lastMessage = trim((string) ($last['message'] ?? ''));
        $lastBaseUrl = trim((string) ($last['base_url'] ?? ''));

        if ($lastMessage === '') {
            return 'Wablas request failed on ' . $lastBaseUrl . '.';
        }

        return 'Wablas failed on ' . $lastBaseUrl . ': ' . $lastMessage;
    }

    private function resolveAttachmentUrl(string $path): ?string
    {
        $normalizedPath = str_replace('\\', '/', $path);

        if (preg_match('/^https?:\\/\\//i', $normalizedPath) === 1) {
            return $normalizedPath;
        }

        $storagePrefix = str_replace('\\', '/', storage_path('app/public/'));
        if (str_starts_with($normalizedPath, $storagePrefix)) {
            $relative = ltrim(
                substr($normalizedPath, strlen($storagePrefix)),
                '/'
            );

            return asset('storage/' . $relative);
        }

        $publicPrefix = str_replace('\\', '/', public_path());
        if (str_starts_with($normalizedPath, $publicPrefix)) {
            $relative = ltrim(
                substr($normalizedPath, strlen($publicPrefix)),
                '/'
            );

            return asset($relative);
        }

        return null;
    }
}
