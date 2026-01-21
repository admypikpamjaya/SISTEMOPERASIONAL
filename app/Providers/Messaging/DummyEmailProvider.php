<?php

namespace App\Providers\Messaging;

use App\Contracts\Messaging\EmailProviderInterface;
use App\DataTransferObjects\BlastPayload;
use Illuminate\Support\Facades\Log;

class DummyEmailProvider implements EmailProviderInterface
{
    public function send(
        string $to,
        string $subject,
        BlastPayload $payload
    ): bool {
        Log::info('[DUMMY EMAIL]', [
            'to' => $to,
            'subject' => $subject,
            'message' => $payload->message,
            'attachments' => $payload->attachments,
            'meta' => $payload->meta,
        ]);

        return true;
    }
}
