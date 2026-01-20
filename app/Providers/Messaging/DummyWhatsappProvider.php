<?php

namespace App\Providers\Messaging;

namespace App\Providers\Messaging;

use App\Contracts\Messaging\WhatsappProviderInterface;
use App\DataTransferObjects\BlastPayload;
use Illuminate\Support\Facades\Log;

class DummyWhatsappProvider implements WhatsappProviderInterface
{
    public function send(string $to, BlastPayload $payload): bool
    {
        Log::info('[DUMMY WHATSAPP]', [
            'to' => $to,
            'message' => $payload->message,
            'attachments' => $payload->attachments,
            'meta' => $payload->meta,
        ]);

        return true;
    }
}
