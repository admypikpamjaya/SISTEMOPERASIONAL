<?php

namespace App\DataTransferObjects\Recipient;

class RecipientColumnMapDTO
{
    public function __construct(
        public ?string $emailCol,
        public ?string $phoneCol,
        public ?string $namaWaliCol,
        public ?string $namaSiswaCol,
        public ?string $kelasCol,
    ) {}
}
