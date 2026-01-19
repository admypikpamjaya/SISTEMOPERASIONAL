<?php

namespace App\Providers\Messaging;

use App\Contracts\Messaging\EmailProviderInterface;
use Illuminate\Support\Facades\Log;

class DummyEmailProvider implements EmailProviderInterface
{
    public function send(string $to, string $subject, string $message): bool
    {
        Log::info('[DUMMY EMAIL]', [
            'to' => $to,
            'subject' => $subject,
            'message' => $message,
        ]);

        return true;
    }
}
