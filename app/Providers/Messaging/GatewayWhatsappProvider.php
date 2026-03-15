<?php

namespace App\Providers\Messaging;

use App\Contracts\Messaging\WhatsappProviderInterface;
use App\DataTransferObjects\BlastPayload;
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
                $payload->setMeta('provider_error', 'Gateway HTTP error.');
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
}
