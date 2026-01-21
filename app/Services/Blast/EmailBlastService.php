<?php

namespace App\Services\Blast;

use App\Contracts\Messaging\EmailProviderInterface;
use App\DataTransferObjects\BlastPayload;

class EmailBlastService
{
    public function __construct(
        protected EmailProviderInterface $provider
    ) {}

    public function send(string $to, string $subject, BlastPayload $payload): bool
    {
        return $this->provider->send($to, $subject, $payload);
    }
}
