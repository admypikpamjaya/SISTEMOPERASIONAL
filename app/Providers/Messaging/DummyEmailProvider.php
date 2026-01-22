<?php

namespace App\Providers\Messaging;

use App\Contracts\Messaging\EmailProviderInterface;
use App\DataTransferObjects\BlastPayload;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\BlastMail;

class DummyEmailProvider implements EmailProviderInterface
{
    /**
     * BARU (Phase 8.4.1)
     * - REAL SEND via Mail::send
     * - Attachments supported
     */
    public function send(
        string $to,
        string $subject,
        BlastPayload $payload
    ): bool {
        try {
            Mail::to($to)->send(
                new BlastMail($subject, $payload)
            );

            Log::info('[EMAIL SENT]', [
                'to' => $to,
                'subject' => $subject,
            ]);

            return true;
        } catch (\Throwable $e) {
            Log::error('[EMAIL FAILED]', [
                'to' => $to,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }
}
