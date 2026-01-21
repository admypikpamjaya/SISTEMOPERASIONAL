<?php

namespace App\Contracts\Messaging;

use App\DataTransferObjects\BlastPayload;

interface EmailProviderInterface
{
    public function send(
        string $to,
        string $subject,
        BlastPayload $payload
    ): bool;
}
