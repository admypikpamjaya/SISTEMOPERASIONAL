<?php

namespace App\DTOs\Finance;

class FinanceSnapshotFilterDTO
{
    public function __construct(
        public ?string $periodType = null,
        public ?string $reportDate = null,
        public ?int $year = null,
        public ?int $month = null,
        public int $page = 1,
        public int $perPage = 20,
        public string $comparisonType = 'NONE',
        public int $comparisonOffset = 1,
        public ?string $comparisonDate = null
    ) {}

    public static function fromArray(array $data): self
    {
        $periodType = isset($data['period_type']) ? strtoupper((string) $data['period_type']) : null;
        if ($periodType === 'ALL') {
            $periodType = null;
        }

        return new self(
            periodType: $periodType,
            reportDate: isset($data['report_date']) ? (string) $data['report_date'] : null,
            year: isset($data['year']) ? (int) $data['year'] : null,
            month: isset($data['month']) ? (int) $data['month'] : null,
            page: (int) ($data['page'] ?? 1),
            perPage: (int) ($data['per_page'] ?? 20),
            comparisonType: strtoupper((string) ($data['comparison_type'] ?? 'NONE')),
            comparisonOffset: (int) ($data['comparison_offset'] ?? 1),
            comparisonDate: isset($data['comparison_date']) ? (string) $data['comparison_date'] : null
        );
    }
}
