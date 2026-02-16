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

    public function getByIdWithItems(string $id): ?FinanceReport
    {
        return FinanceReport::query()
            ->with(['user', 'period', 'items'])
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

    public function paginateByYearAndMonth(
        int $year,
        ?int $month = null,
        ?string $reportType = null,
        bool $readOnlyOnly = true,
        int $page = 1,
        int $perPage = 20
    ): LengthAwarePaginator {
        $query = FinanceReport::query()
            ->with('user')
            ->join('finance_periods', 'finance_periods.id', '=', 'finance_report_snapshots.period_id')
            ->where('finance_periods.year', $year)
            ->select('finance_report_snapshots.*');

        if ($month !== null) {
            $query->where('finance_periods.month', $month);
        }

        if (!empty($reportType)) {
            $query->where('finance_report_snapshots.report_type', $reportType);
        }

        if ($readOnlyOnly) {
            $query->where('finance_report_snapshots.is_read_only', true);
        }

        return $query->orderByDesc('finance_report_snapshots.generated_at')
            ->paginate($perPage, ['finance_report_snapshots.*'], 'page', $page);
    }
}
