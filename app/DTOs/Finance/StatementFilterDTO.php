<?php

namespace App\DTOs\Finance;

class StatementFilterDTO
{
    public function __construct(
        public ?string $periodType = null,
        public ?string $reportDate = null,
        public ?int $year = null,
        public ?int $month = null,
        public ?string $startDate = null,
        public ?string $endDate = null,
        public ?int $startMonth = null,
        public ?int $endMonth = null,
        public ?int $startYear = null,
        public ?int $endYear = null,
        public ?string $accountCode = null,
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
            startDate: isset($data['start_date']) ? (string) $data['start_date'] : null,
            endDate: isset($data['end_date']) ? (string) $data['end_date'] : null,
            startMonth: isset($data['start_month']) ? (int) $data['start_month'] : null,
            endMonth: isset($data['end_month']) ? (int) $data['end_month'] : null,
            startYear: isset($data['start_year']) ? (int) $data['start_year'] : null,
            endYear: isset($data['end_year']) ? (int) $data['end_year'] : null,
            accountCode: isset($data['account_code']) && $data['account_code'] !== ''
                ? (string) $data['account_code']
                : null,
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
            'start_date' => $this->startDate,
            'end_date' => $this->endDate,
            'start_month' => $this->startMonth,
            'end_month' => $this->endMonth,
            'start_year' => $this->startYear,
            'end_year' => $this->endYear,
            'report_date' => $this->reportDate,
            'month' => $this->month,
            'year' => $this->year,
            'account_code' => $this->accountCode,
            'per_page' => $this->perPage,
        ];

        return array_filter(
            $query,
            static fn ($value): bool => $value !== null && $value !== ''
        );
    }
}
