<?php

namespace App\Providers\Messaging;

use App\Contracts\Messaging\EmailProviderInterface;
use App\DataTransferObjects\BlastPayload;
use App\Mail\BlastMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class SmtpEmailProvider implements EmailProviderInterface
{
    public function send(
        string $to,
        string $subject,
        BlastPayload $payload
    ): bool {
        try {
            Mail::to($to)->send(
                new BlastMail($subject, $payload)
            );

            Log::info('[SMTP EMAIL SENT]', [
                'to' => $to,
                'subject' => $subject,
            ]);

            return true;
        } catch (\Throwable $e) {
            Log::error('[SMTP EMAIL FAILED]', [
                'to' => $to,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }
}
