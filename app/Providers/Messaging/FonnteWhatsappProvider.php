<?php

namespace App\Providers\Messaging;

use App\Contracts\Messaging\WhatsappProviderInterface;
use App\DataTransferObjects\BlastPayload;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FonnteWhatsappProvider implements WhatsappProviderInterface
{
    public function send(string $to, BlastPayload $payload): bool
    {
        try {
            $basePayload = [
                'target'  => $to,
                'message' => $payload->message,
            ];

            // JIKA ADA ATTACHMENT
            if (!empty($payload->attachments)) {
                $attachment = $payload->attachments[0];

                $basePayload['file'] = asset(
                    str_replace(public_path(), '', $attachment->path)
                );
            }

            $response = Http::timeout(20)->withHeaders([
                'Authorization' => config('services.fonnte.token'),
            ])->post(
                rtrim(config('services.fonnte.base_url'), '/') . '/send',
                $basePayload
            );

            if (!$response->successful()) {
                Log::error('[FONNTE FAILED]', [
                    'to' => $to,
                    'response' => $response->body(),
                ]);
                return false;
            }

            $decoded = $response->json();
            if (is_array($decoded) && array_key_exists('status', $decoded) && $decoded['status'] !== true) {
                Log::error('[FONNTE REJECTED]', [
                    'to' => $to,
                    'response' => $decoded,
                ]);
                return false;
            }

            return true;
        } catch (\Throwable $e) {
            Log::error('[FONNTE ERROR]', [
                'to' => $to,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }
}
