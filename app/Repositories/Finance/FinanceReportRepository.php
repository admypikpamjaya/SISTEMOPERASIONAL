<?php

namespace App\Repositories\Finance;

use App\Models\FinanceReport;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class FinanceReportRepository
{
    public function create(array $data): FinanceReport
    {
        return FinanceReport::query()->create($data);
    }

    public function getById(string $id): ?FinanceReport
    {
        return FinanceReport::query()
            ->with('user')
            ->find($id);
    }

    public function getByPeriod(string $periodId): Collection
    {
        return FinanceReport::query()
            ->where('period_id', $periodId)
            ->orderByDesc('version_no')
            ->get();
    }

    public function getLatestVersion(string $periodId, string $reportType): ?FinanceReport
    {
        return FinanceReport::query()
            ->where('period_id', $periodId)
            ->where('report_type', $reportType)
            ->orderByDesc('version_no')
            ->first();
    }

    public function paginate(
        ?string $reportType = null,
        bool $readOnlyOnly = true,
        int $page = 1,
        int $perPage = 20
    ): LengthAwarePaginator {
        $query = FinanceReport::query()
            ->with('user');

        if (!empty($reportType)) {
            $query->where('report_type', $reportType);
        }

        if ($readOnlyOnly) {
            $query->where('is_read_only', true);
        }

        return $query->orderByDesc('generated_at')
            ->paginate($perPage, ['*'], 'page', $page);
    }
}
