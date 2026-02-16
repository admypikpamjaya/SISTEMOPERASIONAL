<?php

namespace App\DTOs\Finance;

class ProfitLossLineInputDTO
{
    public function __construct(
        public string $type,
        public string $lineCode,
        public string $lineLabel,
        public ?string $description,
        public float $amount,
        public bool $isDepreciation = false
    ) {}

    public static function fromArray(array $data): self
    {
        $description = isset($data['description']) ? trim((string) $data['description']) : null;

        return new self(
            strtoupper((string) $data['type']),
            (string) $data['line_code'],
            (string) $data['line_label'],
            $description === '' ? null : $description,
            (float) $data['amount'],
            (bool) ($data['is_depreciation'] ?? false)
        );
    }
}
