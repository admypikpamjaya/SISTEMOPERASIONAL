<?php

namespace App\Http\Controllers\Finance;

use App\DTOs\Finance\StatementFilterDTO;
use App\Enums\Portal\PortalPermission;
use App\Http\Controllers\Controller;
use App\Http\Requests\Finance\FinanceStatementFilterRequest;
use App\Services\AccessControl\PermissionService;
use App\Services\Finance\FinancialStatementService;
use App\Services\Finance\ReportService;
use Throwable;

class FinanceDashboardController extends Controller
{
    public function __construct(
        private ReportService $reportService,
        private FinancialStatementService $financialStatementService,
        private PermissionService $permissionService
    ) {}

    public function index(FinanceStatementFilterRequest $request)
    {
        try {
            $filter = StatementFilterDTO::fromArray($request->validated());
            $reports = $this->reportService->getReports(
                year: $filter->year,
                month: $filter->month,
                periodType: $filter->periodType,
                reportDate: $filter->reportDate,
                page: $filter->page,
                perPage: $filter->perPage
            );

            $user = auth()->user();
            $featureAccess = [
                'balance_sheet' => $user !== null && $this->permissionService->checkAccess(
                    $user,
                    PortalPermission::FINANCE_BALANCE_SHEET_READ->value
                ),
                'profit_loss' => $user !== null && $this->permissionService->checkAccess(
                    $user,
                    PortalPermission::FINANCE_PROFIT_LOSS_READ->value
                ),
                'general_ledger' => $user !== null && $this->permissionService->checkAccess(
                    $user,
                    PortalPermission::FINANCE_GENERAL_LEDGER_READ->value
                ),
            ];

            $dashboardSummary = [];
            if (in_array(true, $featureAccess, true)) {
                $dashboardSummary = $this->financialStatementService->getDashboardSummary($filter);
            }

            return view('finance.dashboard', [
                'reports' => $reports,
                'totalReports' => $reports->total(),
                'filters' => [
                    'period_type' => $filter->periodType ?? 'ALL',
                    'report_date' => $filter->reportDate,
                    'month' => $filter->month,
                    'year' => $filter->year,
                    'per_page' => $filter->perPage,
                ],
                'filterQuery' => $filter->toQueryArray(),
                'featureAccess' => $featureAccess,
                'dashboardSummary' => $dashboardSummary,
            ]);
        } catch (Throwable $exception) {
            report($exception);

            return redirect()
                ->route('dashboard.index')
                ->with('error', 'Gagal memuat dashboard finance.');
        }
    }
}
