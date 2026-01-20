<?php

namespace App\Contracts\Messaging;

use App\DataTransferObjects\BlastPayload;

interface WhatsappProviderInterface
{
    public function send(string $to, BlastPayload $payload): bool;
}
