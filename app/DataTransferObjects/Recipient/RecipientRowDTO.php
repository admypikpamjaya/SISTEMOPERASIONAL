<?php

namespace App\DataTransferObjects\Recipient;

class RecipientRowDTO
{
    public function __construct(
        public ?string $email,
        public ?string $phone,
        public ?string $phoneSecondary,
        public ?string $namaWali,
        public ?string $namaSiswa,
        public ?string $kelas,

        // ✅ TAMBAHAN (INI YANG KURANG)
        public ?string $catatan,

        public bool $isValid,
        public array $errors = []
    ) {}
}
