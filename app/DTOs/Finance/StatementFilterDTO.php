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
        public ?string $search = null,
        public ?string $statementSource = null,
        public ?string $statementDataSource = null,
        public ?string $statementBatchId = null,
        public ?string $ledgerSource = null,
        public ?string $ledgerBatchId = null,
        /** @var array<int, int> */
        public array $selectedIds = [],
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
            search: isset($data['search']) && trim((string) $data['search']) !== ''
                ? trim((string) $data['search'])
                : null,
            statementSource: isset($data['statement_source']) && trim((string) $data['statement_source']) !== ''
                ? strtolower(trim((string) $data['statement_source']))
                : null,
            statementDataSource: isset($data['statement_data_source']) && trim((string) $data['statement_data_source']) !== ''
                ? strtolower(trim((string) $data['statement_data_source']))
                : null,
            statementBatchId: isset($data['statement_batch_id']) && trim((string) $data['statement_batch_id']) !== ''
                ? trim((string) $data['statement_batch_id'])
                : null,
            ledgerSource: isset($data['ledger_source']) && trim((string) $data['ledger_source']) !== ''
                ? strtolower(trim((string) $data['ledger_source']))
                : null,
            ledgerBatchId: isset($data['ledger_batch_id']) && trim((string) $data['ledger_batch_id']) !== ''
                ? trim((string) $data['ledger_batch_id'])
                : null,
            selectedIds: collect($data['selected_ids'] ?? [])
                ->map(static fn ($id): int => (int) $id)
                ->filter(static fn (int $id): bool => $id > 0)
                ->values()
                ->all(),
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
            'search' => $this->search,
            'statement_source' => $this->statementSource,
            'statement_data_source' => $this->statementDataSource,
            'statement_batch_id' => $this->statementBatchId,
            'ledger_source' => $this->ledgerSource,
            'ledger_batch_id' => $this->ledgerBatchId,
            'per_page' => $this->perPage,
        ];

        return array_filter(
            $query,
            static fn ($value): bool => $value !== null && $value !== ''
        );
    }
}
