<?php

namespace App\DataTransferObjects\Recipient;

class RecipientRowDTO
{
    public function __construct(
        public ?string $email,
        public ?string $phone,
        public ?string $namaWali,
        public ?string $namaSiswa,
        public ?string $kelas,
        public bool $isValid,
        public array $errors = []
    ) {}
}
