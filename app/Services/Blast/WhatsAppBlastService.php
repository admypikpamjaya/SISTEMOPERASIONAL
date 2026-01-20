<?php

namespace App\Services\Blast;

use App\Contracts\Messaging\WhatsappProviderInterface;
use App\DataTransferObjects\BlastPayload;

class WhatsappBlastService
{
    public function __construct(
        protected WhatsappProviderInterface $provider
    ) {}

    public function send(string $phone, BlastPayload $payload): bool
    {
        return $this->provider->send($phone, $payload);
    }
}
