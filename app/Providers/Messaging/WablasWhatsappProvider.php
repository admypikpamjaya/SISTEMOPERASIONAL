<?php

namespace App\Providers\Messaging;

use App\Contracts\Messaging\WhatsappProviderInterface;
use App\DataTransferObjects\BlastPayload;
use App\DataTransferObjects\BlastAttachment;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WablasWhatsappProvider implements WhatsappProviderInterface
{
    public function send(string $to, BlastPayload $payload): bool
    {
        if (!$this->hasConfiguredToken()) {
            $payload->setMeta('provider_error', 'Wablas token belum dikonfigurasi.');
            Log::error('[WABLAS CONFIG ERROR]', [
                'to' => $to,
            ]);

            return false;
        }

        try {
            $response = null;
            if (!empty($payload->attachments)) {
                $attachment = $payload->attachments[0];
                $response = $this->dispatchAttachmentFromLocal(
                    to: $to,
                    message: $payload->message,
                    attachment: $attachment
                );

                if (!$this->isSuccessResponse($response)) {
                    $attachmentUrl = $this->resolveAttachmentUrl($attachment->path);
                    if ($attachmentUrl === null) {
                        Log::error('[WABLAS ATTACHMENT URL FAILED]', [
                            'to' => $to,
                            'path' => $attachment->path,
                        ]);
                        $payload->setMeta(
                            'provider_error',
                            'Attachment URL invalid and local upload failed.'
                        );
                        return false;
                    }

                    $response = $this->dispatchAttachment(
                        to: $to,
                        message: $payload->message,
                        attachmentUrl: $attachmentUrl,
                        mime: $attachment->mime
                    );
                }
            } else {
                $message = trim($payload->message);
                if ($message === '') {
                    $payload->setMeta('provider_error', 'Pesan kosong.');
                    return false;
                }

                $response = $this->dispatchText(
                    to: $to,
                    message: $message
                );
            }

            if ($this->isSuccessResponse($response)) {
                $message = $this->extractResponseMessage($response);
                if ($message !== '') {
                    $payload->setMeta('provider_message', $message);
                }

                $deliveryStatus = $this->extractDeliveryStatus($response);
                if ($deliveryStatus !== '') {
                    $payload->setMeta('provider_delivery_status', $deliveryStatus);
                } else {
                    $payload->setMeta('provider_delivery_status', 'sent');
                }

                $providerReference = $this->extractResponseReference($response);
                if ($providerReference !== '') {
                    $payload->setMeta('provider_reference', $providerReference);
                }

                $providerMessageId = $this->extractResponseMessageId($response);
                if ($providerMessageId !== '') {
                    $payload->setMeta('provider_message_id', $providerMessageId);
                }

                return true;
            }

            $providerError = $this->extractResponseMessage($response);
            $payload->setMeta('provider_error', $providerError !== '' ? $providerError : 'Wablas request failed.');

            Log::error('[WABLAS FAILED]', [
                'to' => $to,
                'response' => $response,
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

    private function hasConfiguredToken(): bool
    {
        return trim((string) config('services.wablas.token', '')) !== '';
    }

    private function dispatchAttachment(
        string $to,
        string $message,
        string $attachmentUrl,
        string $mime
    ): mixed {
        $normalizedMime = strtolower(trim($mime));
        $endpoint = match (true) {
            str_starts_with($normalizedMime, 'image/') => 'send-image',
            str_starts_with($normalizedMime, 'video/') => 'send-video',
            str_starts_with($normalizedMime, 'audio/') => 'send-audio',
            default => 'send-document',
        };

        $field = match ($endpoint) {
            'send-image' => 'image',
            'send-video' => 'video',
            'send-audio' => 'audio',
            default => 'document',
        };

        $body = [
            'phone' => $to,
            $field => $attachmentUrl,
            'spintax' => true,
        ];

        if ($endpoint !== 'send-audio') {
            $body['caption'] = $message !== '' ? $message : null;
        }

        return $this->postJson($endpoint, $body);
    }

    private function dispatchAttachmentFromLocal(
        string $to,
        string $message,
        BlastAttachment $attachment
    ): mixed {
        $path = $attachment->path;
        if (!is_file($path) || !is_readable($path)) {
            return null;
        }

        $type = $this->resolveAttachmentType($attachment->mime);
        if ($type === null) {
            return null;
        }

        $payload = [
            'phone' => $to,
            'caption' => $message !== '' ? $message : null,
            'file' => base64_encode((string) file_get_contents($path)),
            'data' => json_encode(['name' => $attachment->filename]),
        ];

        return $this->postJson("send-{$type}-from-local", $payload);
    }

    private function dispatchText(string $to, string $message): mixed
    {
        return $this->postJson('send-message', [
            'phone' => $to,
            'message' => $message,
            'spintax' => true,
        ]);
    }

    private function postJson(string $endpoint, array $body): mixed
    {
        $response = $this->client()->post($this->apiUrl($endpoint), $body);
        $decoded = $response->json();

        if ($response->successful()) {
            return $decoded;
        }

        $message = is_array($decoded)
            ? trim((string) ($decoded['message'] ?? $decoded['reason'] ?? ''))
            : '';

        return [
            'status' => false,
            'message' => $message !== ''
                ? $message
                : 'Wablas HTTP error (' . $response->status() . ').',
        ];
    }

    private function client(): PendingRequest
    {
        $token = trim((string) config('services.wablas.token', ''));
        $secret = trim((string) config('services.wablas.secret_key', ''));
        $authorization = $secret !== '' ? $token . '.' . $secret : $token;

        return Http::timeout((int) config('services.wablas.timeout', 30))
            ->connectTimeout((int) config('services.wablas.connect_timeout', 10))
            ->acceptJson()
            ->withHeaders([
                'Content-Type' => 'application/json',
                'Authorization' => $authorization,
            ]);
    }

    private function apiUrl(string $endpoint): string
    {
        $server = trim((string) config('services.wablas.server', ''));
        if ($server !== '') {
            return 'https://' . $server . '.wablas.com/api/' . ltrim($endpoint, '/');
        }

        return rtrim((string) config('services.wablas.base_url', 'https://wablas.com'), '/')
            . '/api/' . ltrim($endpoint, '/');
    }

    private function resolveAttachmentType(string $mime): ?string
    {
        $normalizedMime = strtolower(trim($mime));

        if (str_starts_with($normalizedMime, 'image/')) {
            return 'image';
        }

        if (str_starts_with($normalizedMime, 'video/')) {
            return 'video';
        }

        if (str_starts_with($normalizedMime, 'audio/')) {
            return 'audio';
        }

        if ($normalizedMime !== '') {
            return 'document';
        }

        return null;
    }

    private function extractResponseMessage(mixed $decoded): string
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

        if (is_string($decoded)) {
            return trim($decoded);
        }

        return '';
    }

    private function extractResponseReference(mixed $decoded): string
    {
        $message = $this->firstResponseMessage($decoded);
        $reference = trim((string) (
            $message['id']
            ?? $message['messageId']
            ?? $message['message_id']
            ?? $message['reference']
            ?? ''
        ));

        if ($reference !== '') {
            return $reference;
        }

        if (is_array($decoded)) {
            $data = is_array($decoded['data'] ?? null) ? $decoded['data'] : [];
            return trim((string) (
                $data['id']
                ?? $data['messageId']
                ?? $data['message_id']
                ?? $data['reference']
                ?? ''
            ));
        }

        return '';
    }

    private function extractResponseMessageId(mixed $decoded): string
    {
        $message = $this->firstResponseMessage($decoded);
        $messageId = trim((string) (
            $message['messageId']
            ?? $message['message_id']
            ?? $message['id']
            ?? ''
        ));

        if ($messageId !== '') {
            return $messageId;
        }

        if (is_array($decoded)) {
            return trim((string) (
                $decoded['messageId']
                ?? $decoded['message_id']
                ?? data_get($decoded, 'data.messageId', '')
                ?? data_get($decoded, 'data.message_id', '')
                ?? ''
            ));
        }

        return '';
    }

    /** @return array<string, mixed> */
    private function firstResponseMessage(mixed $decoded): array
    {
        if (!is_array($decoded)) {
            return [];
        }

        $messages = data_get($decoded, 'data.messages');
        if (is_array($messages) && is_array($messages[0] ?? null)) {
            return $messages[0];
        }

        return [];
    }

    private function isSuccessResponse(mixed $decoded): bool
    {
        if (is_array($decoded)) {
            if (array_key_exists('status', $decoded)) {
                return $this->isTruthyStatus($decoded['status']);
            }

            if (array_key_exists('success', $decoded)) {
                return $this->isTruthyStatus($decoded['success']);
            }

            $message = strtolower(trim((string) ($decoded['message'] ?? '')));
            if ($message !== '' && str_contains($message, 'success')) {
                return true;
            }
        }

        return false;
    }

    private function isTruthyStatus(mixed $value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        if (is_numeric($value)) {
            return (int) $value === 1;
        }

        $normalized = strtolower(trim((string) $value));

        return in_array($normalized, [
            '1',
            'ok',
            'queued',
            'sent',
            'success',
            'true',
        ], true);
    }

    private function extractDeliveryStatus(mixed $decoded): string
    {
        if (!is_array($decoded)) {
            return '';
        }

        $firstMessage = $this->firstResponseMessage($decoded);
        $status = strtolower(trim((string) ($firstMessage['status'] ?? '')));
        if ($status !== '') {
            return $status;
        }

        $dataStatus = data_get($decoded, 'data.deliveryStatus')
            ?? data_get($decoded, 'data.delivery_status')
            ?? data_get($decoded, 'data.status');

        return strtolower(trim((string) $dataStatus));
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
