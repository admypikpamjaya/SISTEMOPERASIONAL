<?php

namespace App\DataTransferObjects\Recipient;

class GeneralRecipientRowDTO
{
    public function __construct(
        public ?string $nama,
        public ?string $phone,
        public ?string $instansi,
        public ?string $email,
        public ?string $sertifikat,
        public ?string $eventName,
        public ?string $catatan,
        public bool $isValid,
        public array $errors = []
    ) {}
}
