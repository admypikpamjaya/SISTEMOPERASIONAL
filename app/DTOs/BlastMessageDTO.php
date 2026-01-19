<?php

namespace App\DTOs;

class BlastMessageDTO
{
    public function __construct(
        public readonly ?string $email,
        public readonly ?string $phone,
        public readonly string $message,
        public readonly ?string $attachmentPath = null
    ) {}
}
