<?php

namespace App\Contracts\Messaging;

use App\DataTransferObjects\BlastPayload;

interface EmailProviderInterface
{
    public function send(string $to, BlastPayload $payload): bool;
}
