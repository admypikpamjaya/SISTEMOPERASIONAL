<?php

namespace App\Contracts\Messaging;

interface WhatsappProviderInterface
{
    public function send(string $to, string $message): bool;
}
