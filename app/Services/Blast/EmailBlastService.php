<?php

namespace App\Services\Blast;

use App\Contracts\Messaging\EmailProviderInterface;

class EmailBlastService
{
    public function __construct(
        protected EmailProviderInterface $provider
    ) {}

    public function send(string $to, string $subject, string $message): bool
    {
        return $this->provider->send($to, $subject, $message);
    }
}
