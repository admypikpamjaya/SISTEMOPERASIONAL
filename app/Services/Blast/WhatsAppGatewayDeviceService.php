<?php

namespace App\Services\Blast;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class WhatsAppGatewayDeviceService
{
    public function __construct(
        private readonly WhatsAppDeviceLabelStore $labelStore
    ) {}

    /**
     * @return array{
     *   success:bool,
     *   message:string,
     *   data:array<string, mixed>
     * }
     */
    public function listDevices(bool $includeSensitive = false): array
    {
        try {
            [$baseUrl, $client] = $this->buildGatewayClient();
            $response = $client->get($baseUrl . '/devices');
        } catch (\Throwable $exception) {
            throw new RuntimeException(
                $exception->getMessage() ?: 'Gateway tidak dapat dihubungi.',
                previous: $exception
            );
        }

        if (!$response->successful()) {
            throw new RuntimeException('Gateway merespon error.');
        }

        $payload = $response->json();
        if (!is_array($payload)) {
            $payload = [];
        }

        $data = $payload['data'] ?? $payload;
        if (!is_array($data)) {
            $data = [];
        }

        $devices = $data['devices'] ?? [];
        $labels = $this->labelStore->getLabels();

        if (is_array($devices)) {
            $devices = array_values(array_filter(array_map(
                function ($device) use ($labels, $includeSensitive): ?array {
                    if (!is_array($device)) {
                        return null;
                    }

                    $deviceId = (string) ($device['deviceId'] ?? '');
                    if ($deviceId === '') {
                        return null;
                    }

                    $device['label'] = $labels[$deviceId] ?? $deviceId;

                    if (!$includeSensitive) {
                        unset($device['qr'], $device['qrDataUrl'], $device['user']);
                    }

                    return $device;
                },
                $devices
            )));
        } else {
            $devices = [];
        }

        $data['devices'] = $devices;
        $data['labels'] = $labels;

        return [
            'success' => (bool) ($payload['success'] ?? true),
            'message' => (string) ($payload['message'] ?? 'OK'),
            'data' => $data,
        ];
    }

    public function sanitizeDeviceId(?string $raw): ?string
    {
        $value = trim((string) $raw);
        if ($value === '') {
            return null;
        }

        $normalized = preg_replace('/[^a-zA-Z0-9_-]/', '', $value) ?? '';
        $normalized = strtolower($normalized);

        return $normalized !== '' ? $normalized : null;
    }

    private function buildGatewayClient(): array
    {
        $baseUrl = rtrim(
            (string) config('services.whatsapp_gateway.base_url', ''),
            '/'
        );

        if ($baseUrl === '') {
            throw new RuntimeException('Gateway base URL belum disetel.');
        }

        $timeout = (int) config('services.whatsapp_gateway.timeout', 20);
        $apiKey = trim((string) config('services.whatsapp_gateway.api_key', ''));
        $apiKeyHeader = trim((string) config('services.whatsapp_gateway.api_key_header', 'X-API-KEY'));

        $headers = [];
        if ($apiKey !== '') {
            $headers[$apiKeyHeader] = $apiKey;
        }

        $client = Http::timeout($timeout)->withHeaders($headers);

        return [$baseUrl, $client];
    }
}
