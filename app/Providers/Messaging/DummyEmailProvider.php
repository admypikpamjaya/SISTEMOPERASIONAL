<?php

namespace App\Providers\Messaging;

use App\Contracts\Messaging\EmailProviderInterface;
use App\DataTransferObjects\BlastPayload;
use Illuminate\Support\Facades\Log;

class DummyEmailProvider implements EmailProviderInterface
{
    public function send(string $to, BlastPayload $payload): bool
    {
        Log::info('[DUMMY EMAIL]', [
            'to' => $to,
            'subject' => $payload->meta['subject'] ?? '(no subject)',
            'message' => $payload->message,
            'attachments' => collect($payload->attachments)->map(fn ($a) => $a->filename),
        ]);

        return true;
    }
}
