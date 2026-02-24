<?php

namespace App\Http\Controllers;

use App\Enums\Portal\PortalPermission;
use App\Models\BlastLog;
use App\Models\FinanceReport;
use App\Services\AccessControl\PermissionService;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    private const WIB_TIMEZONE = 'Asia/Jakarta';

    public function index(): View
    {
        return view('dashboard.index', $this->buildDashboardPayload());
    }

    public function chartData(): JsonResponse
    {
        return response()->json($this->buildDashboardPayload());
    }

    /**
     * @return array<string, mixed>
     */
    private function buildDashboardPayload(): array
    {
        $currentUser = auth()->user();
        $permissionService = app(PermissionService::class);

        $showFinanceWidgets = $currentUser !== null
            && $permissionService->checkAccess(
                $currentUser,
                PortalPermission::FINANCE_REPORT_READ->value
            );

        $showBlastingWidgets = $currentUser !== null
            && $permissionService->checkAccess(
                $currentUser,
                PortalPermission::ADMIN_BLAST_READ->value
            );

        $blastSeries = null;
        $financeSeries = null;
        $saldo = null;
        $saldoUpdatedAt = null;

        if ($showBlastingWidgets) {
            $blastSeries = $this->buildBlastSeries();
        }

        if ($showFinanceWidgets) {
            $financeSeries = $this->buildFinanceSeries();

            $allReports = FinanceReport::query()
                ->where('is_read_only', true)
                ->get(['summary']);

            $saldo = round($allReports->sum(static function (FinanceReport $report): float {
                return (float) data_get(
                    $report->summary,
                    'ending_balance',
                    data_get($report->summary, 'net_result', 0)
                );
            }), 2);

            $latestReport = FinanceReport::query()
                ->where('is_read_only', true)
                ->orderByDesc('generated_at')
                ->orderByDesc('version_no')
                ->first();

            $saldoUpdatedAt = $latestReport?->generated_at
                ? $latestReport->generated_at
                    ->copy()
                    ->timezone(self::WIB_TIMEZONE)
                    ->format('d/m/Y H:i:s')
                : null;
        }

        return [
            'showFinanceWidgets' => $showFinanceWidgets,
            'showBlastingWidgets' => $showBlastingWidgets,
            'saldo' => $saldo,
            'saldoUpdatedAt' => $saldoUpdatedAt,
            'incomeChart' => $showFinanceWidgets ? [
                'labels' => $financeSeries['labels'],
                'values' => $financeSeries['income'],
                'url' => route('finance.report.snapshots', ['period_type' => 'MONTHLY']),
            ] : null,
            'expenseChart' => $showFinanceWidgets ? [
                'labels' => $financeSeries['labels'],
                'expenseValues' => $financeSeries['expense'],
                'depreciationValues' => $financeSeries['depreciation'],
                'url' => route('finance.depreciation.index'),
            ] : null,
            'waChart' => $showBlastingWidgets ? [
                'labels' => $blastSeries['labels'],
                'values' => $blastSeries['whatsapp'],
                'url' => route('admin.blast.whatsapp'),
            ] : null,
            'emailChart' => $showBlastingWidgets ? [
                'labels' => $blastSeries['labels'],
                'values' => $blastSeries['email'],
                'url' => route('admin.blast.email'),
            ] : null,
        ];
    }

    /**
     * @return array{labels:array<int,string>,income:array<int,float>,expense:array<int,float>,depreciation:array<int,float>}
     */
    private function buildFinanceSeries(): array
    {
        $reports = FinanceReport::query()
            ->with('period:id,year,month,day,period_type,start_date')
            ->where('is_read_only', true)
            ->orderByDesc('generated_at')
            ->orderByDesc('version_no')
            ->get()
            ->filter(static function (FinanceReport $report): bool {
                return $report->period !== null;
            })
            ->unique('period_id')
            ->sortBy(static function (FinanceReport $report): string {
                $period = $report->period;
                if ($period?->start_date !== null) {
                    return $period->start_date->format('Y-m-d');
                }

                return sprintf(
                    '%04d-%02d-%02d',
                    (int) $period?->year,
                    (int) $period?->month,
                    (int) $period?->day
                );
            })
            ->values();

        if ($reports->isEmpty()) {
            return [
                'labels' => [],
                'income' => [],
                'expense' => [],
                'depreciation' => [],
            ];
        }

        return [
            'labels' => $reports->map(static function (FinanceReport $report): string {
                $period = $report->period;
                $periodType = strtoupper((string) $period?->period_type);

                if ($periodType === 'DAILY' && $period?->start_date !== null) {
                    return $period->start_date->format('d/m/Y');
                }

                if ($periodType === 'YEARLY') {
                    return (string) (int) ($period?->year ?? 0);
                }

                return sprintf(
                    '%02d/%04d',
                    (int) ($period?->month ?? 0),
                    (int) ($period?->year ?? 0)
                );
            })->all(),
            'income' => $reports->map(static function (FinanceReport $report): float {
                return round((float) data_get($report->summary, 'total_income', 0), 2);
            })->all(),
            'expense' => $reports->map(static function (FinanceReport $report): float {
                return round((float) data_get($report->summary, 'total_expense', 0), 2);
            })->all(),
            'depreciation' => $reports->map(static function (FinanceReport $report): float {
                return round((float) data_get($report->summary, 'total_depreciation', 0), 2);
            })->all(),
        ];
    }

    /**
     * @return array{labels:array<int,string>,whatsapp:array<int,int>,email:array<int,int>}
     */
    private function buildBlastSeries(int $days = 7): array
    {
        $start = now(self::WIB_TIMEZONE)->startOfDay()->subDays($days - 1);
        $end = now(self::WIB_TIMEZONE)->endOfDay();
        $dateKeys = [];
        $labels = [];

        $cursor = $start->copy();
        while ($cursor->lte($end)) {
            $dateKeys[] = $cursor->format('Y-m-d');
            $labels[] = $cursor->format('d/m');
            $cursor->addDay();
        }

        return [
            'labels' => $labels,
            'whatsapp' => $this->buildBlastChannelCounts('WHATSAPP', $start, $end, $dateKeys),
            'email' => $this->buildBlastChannelCounts('EMAIL', $start, $end, $dateKeys),
        ];
    }

    /**
     * @param array<int,string> $dateKeys
     * @return array<int,int>
     */
    private function buildBlastChannelCounts(
        string $channel,
        Carbon $start,
        Carbon $end,
        array $dateKeys
    ): array {
        $grouped = BlastLog::query()
            ->select(['blast_logs.created_at'])
            ->join('blast_messages', 'blast_messages.id', '=', 'blast_logs.blast_message_id')
            ->where('blast_messages.channel', strtoupper($channel))
            ->whereBetween('blast_logs.created_at', [$start->toDateTimeString(), $end->toDateTimeString()])
            ->orderBy('blast_logs.created_at')
            ->get()
            ->groupBy(static function (BlastLog $log): string {
                return $log->created_at
                    ? $log->created_at->copy()->timezone(self::WIB_TIMEZONE)->format('Y-m-d')
                    : '';
            })
            ->map(static fn ($items): int => $items->count());

        return collect($dateKeys)
            ->map(static fn (string $key): int => (int) ($grouped[$key] ?? 0))
            ->all();
    }
}
