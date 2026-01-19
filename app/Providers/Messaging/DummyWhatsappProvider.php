<?php

namespace App\Providers\Messaging;

use App\Contracts\Messaging\WhatsappProviderInterface;
use Illuminate\Support\Facades\Log;

class DummyWhatsappProvider implements WhatsappProviderInterface
{
    public function send(string $to, string $message): bool
    {
        Log::info('[DUMMY WHATSAPP]', [
            'to' => $to,
            'message' => $message,
        ]);

        return true;
    }
}
