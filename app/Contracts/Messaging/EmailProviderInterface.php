<?php

namespace App\Contracts\Messaging;

interface EmailProviderInterface
{
    public function send(string $to, string $subject, string $message): bool;
}
