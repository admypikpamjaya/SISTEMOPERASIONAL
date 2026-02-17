<?php

namespace App\DTOs\Finance;

class GenerateProfitLossReportDTO
{
    /**
     * @param array<int, ProfitLossLineInputDTO> $entries
     */
    public function __construct(
        public int $year,
        public ?int $month,
        public ?int $day,
        public ?string $reportDate,
        public string $reportType,
        public float $openingBalance,
        public array $entries,
        public ?string $generatedBy = null
    ) {}

    public static function fromArray(array $data, ?string $generatedBy = null): self
    {
        $entries = array_map(
            fn (array $entry) => ProfitLossLineInputDTO::fromArray($entry),
            $data['entries'] ?? []
        );

        return new self(
            (int) $data['year'],
            isset($data['month']) ? (int) $data['month'] : null,
            isset($data['day']) ? (int) $data['day'] : null,
            isset($data['report_date']) ? (string) $data['report_date'] : null,
            strtoupper((string) $data['report_type']),
            (float) ($data['opening_balance'] ?? 0),
            $entries,
            $generatedBy
        );
    }
}
