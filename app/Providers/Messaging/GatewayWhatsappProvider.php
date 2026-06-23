<?php

namespace App\Providers\Messaging;

use App\Contracts\Messaging\WhatsappProviderInterface;
use App\DataTransferObjects\BlastPayload;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GatewayWhatsappProvider implements WhatsappProviderInterface
{
    public function send(string $to, BlastPayload $payload): bool
    {
        $deviceId = trim((string) ($payload->meta['device_id'] ?? ''));
        $baseUrl = rtrim(
            (string) config('services.whatsapp_gateway.base_url', ''),
            '/'
        );

        if ($baseUrl === '') {
            $payload->setMeta('provider_error', 'Gateway base URL belum dikonfigurasi.');
            Log::error('[WA GATEWAY CONFIG ERROR]', [
                'to' => $to,
            ]);
            return false;
        }

        $timeout = (int) config('services.whatsapp_gateway.timeout', 20);
        $apiKey = trim((string) config('services.whatsapp_gateway.api_key', ''));
        $apiKeyHeader = trim(
            (string) config('services.whatsapp_gateway.api_key_header', 'X-API-KEY')
        );

        $headers = [];
        if ($apiKey !== '') {
            $headers[$apiKeyHeader] = $apiKey;
        }

        try {
            $client = Http::timeout($timeout)->withHeaders($headers);
            $deviceResolution = $this->resolveSelectedDevice(
                $client,
                $baseUrl,
                $deviceId,
                $payload
            );

            if (!$deviceResolution['success']) {
                return false;
            }

            $deviceId = $deviceResolution['device_id'] ?? $deviceId;
            if ($deviceId !== '') {
                $payload->setMeta('device_id', $deviceId);
            }

            if (!empty($payload->attachments)) {
                $attachment = $payload->attachments[0];
                $path = $attachment->path;

                if (!is_file($path) || !is_readable($path)) {
                    $payload->setMeta('provider_error', 'File lampiran tidak ditemukan.');
                    Log::error('[WA GATEWAY FILE MISSING]', [
                        'to' => $to,
                        'path' => $path,
                    ]);
                    return false;
                }

                $filename = $attachment->filename !== ''
                    ? $attachment->filename
                    : basename($path);

                $fileHeaders = [];
                if ($attachment->mime !== '') {
                    $fileHeaders['Content-Type'] = $attachment->mime;
                }

                $client = $client->attach(
                    'file',
                    (string) file_get_contents($path),
                    $filename,
                    $fileHeaders
                );

                $response = $client->post($baseUrl . '/send-file', array_filter([
                    'phone' => $to,
                    'caption' => $payload->message,
                    'deviceId' => $deviceId !== '' ? $deviceId : null,
                ]));
            } else {
                $message = trim($payload->message);
                if ($message === '') {
                    $payload->setMeta('provider_error', 'Pesan kosong.');
                    return false;
                }

                $response = $client->post($baseUrl . '/send-message', array_filter([
                    'phone' => $to,
                    'message' => $message,
                    'deviceId' => $deviceId !== '' ? $deviceId : null,
                ]));
            }

            if (!$response->successful()) {
                $decoded = $response->json();
                $providerError = is_array($decoded)
                    ? trim((string) ($decoded['message'] ?? ''))
                    : '';
                $payload->setMeta(
                    'provider_error',
                    $providerError !== '' ? $providerError : 'Gateway HTTP error.'
                );
                Log::error('[WA GATEWAY FAILED]', [
                    'to' => $to,
                    'status' => $response->status(),
                    'response' => $response->body(),
                ]);
                return false;
            }

            $decoded = $response->json();
            if (is_array($decoded) && array_key_exists('success', $decoded)) {
                if ($decoded['success'] !== true) {
                    $payload->setMeta(
                        'provider_error',
                        (string) ($decoded['message'] ?? 'Gateway rejected')
                    );
                    Log::error('[WA GATEWAY REJECTED]', [
                        'to' => $to,
                        'response' => $decoded,
                    ]);
                    return false;
                }

                $message = trim((string) ($decoded['message'] ?? ''));
                if ($message !== '') {
                    $payload->setMeta('provider_message', $message);
                }

                $deliveryStatus = $this->resolveDeliveryStatus($decoded, $message);
                if ($deliveryStatus !== null) {
                    $payload->setMeta('provider_delivery_status', $deliveryStatus);
                }

                $providerReference = $this->resolveProviderReference($decoded);
                if ($providerReference !== null) {
                    $payload->setMeta('provider_reference', $providerReference);
                }

                $providerMessageId = $this->resolveProviderMessageId($decoded);
                if ($providerMessageId !== null) {
                    $payload->setMeta('provider_message_id', $providerMessageId);
                }
            }

            return true;
        } catch (\Throwable $exception) {
            $payload->setMeta('provider_error', $exception->getMessage());
            Log::error('[WA GATEWAY ERROR]', [
                'to' => $to,
                'error' => $exception->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * @return array{success:bool,device_id:string}
     */
    private function resolveSelectedDevice(
        PendingRequest $client,
        string $baseUrl,
        string $requestedDeviceId,
        BlastPayload $payload
    ): array {
        $gatewayData = $this->getGatewayDeviceData($client, $baseUrl);
        $devices = is_array($gatewayData['devices'] ?? null)
            ? array_values($gatewayData['devices'])
            : [];

        if ($devices === []) {
            return [
                'success' => true,
                'device_id' => $requestedDeviceId,
            ];
        }

        $effectiveDeviceId = $requestedDeviceId !== ''
            ? $requestedDeviceId
            : trim((string) ($gatewayData['activeDeviceId'] ?? ''));

        if ($effectiveDeviceId === '') {
            return [
                'success' => true,
                'device_id' => '',
            ];
        }

        $effectiveDevice = collect($devices)->first(
            fn ($device): bool => is_array($device)
                && trim((string) ($device['deviceId'] ?? '')) === $effectiveDeviceId
        );

        if (!is_array($effectiveDevice)) {
            $message = sprintf(
                'Perangkat WhatsApp %s tidak ditemukan di gateway.',
                $effectiveDeviceId
            );
            $payload->setMeta('provider_error', $message);
            Log::warning('[WA GATEWAY DEVICE NOT FOUND]', [
                'device_id' => $effectiveDeviceId,
            ]);

            return [
                'success' => false,
                'device_id' => $effectiveDeviceId,
            ];
        }

        $deviceStatus = strtolower(trim((string) ($effectiveDevice['status'] ?? '')));
        if ($deviceStatus !== 'connected') {
            $message = sprintf(
                'Perangkat WhatsApp %s tidak terhubung. Status saat ini: %s.',
                $effectiveDeviceId,
                $deviceStatus !== '' ? $deviceStatus : 'unknown'
            );
            $payload->setMeta('provider_error', $message);
            Log::warning('[WA GATEWAY DEVICE NOT CONNECTED]', [
                'device_id' => $effectiveDeviceId,
                'status' => $deviceStatus,
            ]);

            return [
                'success' => false,
                'device_id' => $effectiveDeviceId,
            ];
        }

        $senderPhone = $this->extractDevicePhone($effectiveDevice);
        if ($senderPhone !== null) {
            $payload->setMeta('provider_sender_phone', $senderPhone);
        }

        return [
            'success' => true,
            'device_id' => $effectiveDeviceId,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function getGatewayDeviceData(
        PendingRequest $client,
        string $baseUrl
    ): array {
        $cacheKey = 'whatsapp-gateway-devices:' . sha1($baseUrl);

        return Cache::remember($cacheKey, now()->addSeconds(10), function () use (
            $client,
            $baseUrl
        ): array {
            try {
                $response = $client->get($baseUrl . '/devices');
                if (!$response->successful()) {
                    return [];
                }

                $payload = $response->json();
                if (!is_array($payload)) {
                    return [];
                }

                $data = $payload['data'] ?? $payload;

                return is_array($data) ? $data : [];
            } catch (\Throwable $exception) {
                Log::warning('[WA GATEWAY DEVICE PREFLIGHT FAILED]', [
                    'error' => $exception->getMessage(),
                ]);

                return [];
            }
        });
    }

    private function extractDevicePhone(array $device): ?string
    {
        $user = is_array($device['user'] ?? null) ? $device['user'] : [];
        $userId = trim((string) ($user['id'] ?? ''));
        if ($userId === '') {
            return null;
        }

        if (preg_match('/^(\d+)(?::\d+)?@/', $userId, $matches) !== 1) {
            return null;
        }

        return $this->normalizePhone($matches[1] ?? null);
    }

    private function normalizePhone(?string $phone): ?string
    {
        $normalized = preg_replace('/\D+/', '', trim((string) $phone)) ?? '';
        if ($normalized === '') {
            return null;
        }

        if (str_starts_with($normalized, '0')) {
            $normalized = '62' . substr($normalized, 1);
        } elseif (str_starts_with($normalized, '8')) {
            $normalized = '62' . $normalized;
        }

        return str_starts_with($normalized, '62') ? $normalized : null;
    }

    private function resolveDeliveryStatus(array $decoded, string $message): ?string
    {
        $data = is_array($decoded['data'] ?? null) ? $decoded['data'] : [];
        $status = strtolower(trim((string) (
            $data['deliveryStatus']
            ?? $data['delivery_status']
            ?? $data['status']
            ?? $decoded['deliveryStatus']
            ?? $decoded['delivery_status']
            ?? ''
        )));

        if ($status !== '') {
            return $status;
        }

        return str_contains(strtolower($message), 'queued') ? 'queued' : null;
    }

    private function resolveProviderReference(array $decoded): ?string
    {
        $data = is_array($decoded['data'] ?? null) ? $decoded['data'] : [];
        $reference = trim((string) (
            $data['messageId']
            ?? $data['message_id']
            ?? $data['jobId']
            ?? $data['job_id']
            ?? $data['id']
            ?? ''
        ));

        return $reference !== '' ? $reference : null;
    }

    private function resolveProviderMessageId(array $decoded): ?string
    {
        $data = is_array($decoded['data'] ?? null) ? $decoded['data'] : [];
        $messageId = trim((string) (
            $data['messageId']
            ?? $data['message_id']
            ?? $decoded['messageId']
            ?? $decoded['message_id']
            ?? ''
        ));

        return $messageId !== '' ? $messageId : null;
    }
}
