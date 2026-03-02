<?php

namespace App\DataTransferObjects\Recipient;

class EmployeeRecipientRowDTO
{
    public function __construct(
        public ?string $email,
        public ?string $phone,
        public ?string $namaKaryawan,
        public ?string $instansi,
        public ?string $namaWali,
        public ?string $catatan,
        public bool $isValid,
        public array $errors = []
    ) {}
}

