<?php

namespace App\DTOs\Finance;

class ProfitLossLineDTO
{
    public function __construct(
        public string $lineCode,
        public string $lineLabel,
        public ?string $description,
        public ?string $invoiceNumber,
        public float $amount,
        public bool $isDepreciation
    ) {}
}
