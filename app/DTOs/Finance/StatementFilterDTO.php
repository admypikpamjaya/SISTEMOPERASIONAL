<?php

namespace App\DTOs\Finance;

class StatementFilterDTO
{
    public function __construct(
        public ?string $periodType = null,
        public ?string $reportDate = null,
        public ?int $year = null,
        public ?int $month = null,
        public int $page = 1,
        public int $perPage = 10
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
            page: max(1, (int) ($data['page'] ?? 1)),
            perPage: max(1, (int) ($data['per_page'] ?? 10))
        );
    }

    /**
     * @return array<string, int|string>
     */
    public function toQueryArray(): array
    {
        $query = [
            'period_type' => $this->periodType ?? 'ALL',
            'report_date' => $this->reportDate,
            'month' => $this->month,
            'year' => $this->year,
            'per_page' => $this->perPage,
        ];

        return array_filter(
            $query,
            static fn ($value): bool => $value !== null && $value !== ''
        );
    }
}
