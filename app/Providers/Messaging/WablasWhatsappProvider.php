<?php

namespace App\Providers\Messaging;

use App\Contracts\Messaging\WhatsappProviderInterface;
use App\DataTransferObjects\BlastPayload;
use App\DataTransferObjects\BlastAttachment;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Silvanix\Wablas\Message as WablasMessage;

class WablasWhatsappProvider implements WhatsappProviderInterface
{
    public function send(string $to, BlastPayload $payload): bool
    {
        try {
            $client = new WablasMessage();

            $response = null;
            if (!empty($payload->attachments)) {
                $attachment = $payload->attachments[0];
                $response = $this->dispatchAttachmentFromLocal(
                    client: $client,
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
                        client: $client,
                        to: $to,
                        message: $payload->message,
                        attachmentUrl: $attachmentUrl,
                        mime: $attachment->mime
                    );
                }
            } else {
                $response = $client->single_text($to, $payload->message);
            }

            if ($this->isSuccessResponse($response)) {
                $message = $this->extractResponseMessage($response);
                if ($message !== '') {
                    $payload->setMeta('provider_message', $message);
                }

                $deliveryStatus = $this->extractDeliveryStatus($response);
                if ($deliveryStatus !== '') {
                    $payload->setMeta('provider_delivery_status', $deliveryStatus);
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

    private function dispatchAttachment(
        WablasMessage $client,
        string $to,
        string $message,
        string $attachmentUrl,
        string $mime
    ): mixed {
        $normalizedMime = strtolower(trim($mime));

        if (str_starts_with($normalizedMime, 'image/')) {
            return $client->single_image($to, $attachmentUrl, $message ?: null);
        }

        if (str_starts_with($normalizedMime, 'video/')) {
            return $client->single_video($to, $attachmentUrl, $message ?: null);
        }

        if (str_starts_with($normalizedMime, 'audio/')) {
            return $client->single_audio($to, $attachmentUrl);
        }

        return $client->single_document($to, $attachmentUrl, $message ?: null);
    }

    private function dispatchAttachmentFromLocal(
        WablasMessage $client,
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

        $url = $client->api() . "send-{$type}-from-local";
        return Http::withHeaders([
            'Content-Type' => 'application/json',
            'Authorization' => $client->token(),
        ])->post($url, $payload)->json();
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

    private function isSuccessResponse(mixed $decoded): bool
    {
        if (is_array($decoded)) {
            if (array_key_exists('status', $decoded)) {
                return (bool) $decoded['status'] === true;
            }

            if (array_key_exists('success', $decoded)) {
                return (bool) $decoded['success'] === true;
            }

            $message = strtolower(trim((string) ($decoded['message'] ?? '')));
            if ($message !== '' && str_contains($message, 'success')) {
                return true;
            }
        }

        return false;
    }

    private function extractDeliveryStatus(mixed $decoded): string
    {
        if (!is_array($decoded)) {
            return '';
        }

        $data = $decoded['data'] ?? null;
        if (!is_array($data)) {
            return '';
        }

        $messages = $data['messages'] ?? null;
        if (!is_array($messages) || $messages === []) {
            return '';
        }

        $firstMessage = $messages[0] ?? null;
        if (!is_array($firstMessage)) {
            return '';
        }

        return strtolower(trim((string) ($firstMessage['status'] ?? '')));
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
