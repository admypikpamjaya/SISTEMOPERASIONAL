<?php

namespace App\Providers\Messaging;

use App\Contracts\Messaging\EmailProviderInterface;
use App\DataTransferObjects\BlastPayload;
use App\Mail\BlastMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class DummyEmailProvider implements EmailProviderInterface
{
    public function send(
        string $to,
        string $subject,
        BlastPayload $payload
    ): bool {
        Mail::to($to)->send(
            new BlastMail($subject, $payload)
        );

        Log::info('[EMAIL BLAST SENT]', [
            'to' => $to,
            'subject' => $subject,
        ]);

        return true;
    }
}
