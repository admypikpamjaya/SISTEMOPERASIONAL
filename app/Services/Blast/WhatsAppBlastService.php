<?php

namespace App\Services\Blast;

use App\Contracts\Messaging\WhatsappProviderInterface;

class WhatsappBlastService
{
    public function __construct(
        protected WhatsappProviderInterface $provider
    ) {}

    public function send(string $to, string $message): bool
    {
        return $this->provider->send($to, $message);
    }
}
