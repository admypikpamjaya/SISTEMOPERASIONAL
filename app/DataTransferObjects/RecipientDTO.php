<?php

namespace App\DataTransferObjects;

class RecipientDTO
{
    public function __construct(
        public ?string $email_wali,
        public ?string $wa_wali,
        public ?string $catatan = null,
        public array $raw = []
    ) {}
}
