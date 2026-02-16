<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Http\Requests\Finance\FinanceDashboardRequest;
use App\Services\Finance\ReportService;

class FinanceDashboardController extends Controller
{
    public function __construct(
        private ReportService $reportService
    ) {}

    public function index(FinanceDashboardRequest $request)
    {
        $validated = $request->validated();
        $year = (int) ($validated['year'] ?? now()->year);
        $month = isset($validated['month']) ? (int) $validated['month'] : null;
        $page = (int) ($validated['page'] ?? 1);
        $perPage = (int) ($validated['per_page'] ?? 5);

        $reports = $this->reportService->getReports(
            year: $year,
            month: $month,
            reportType: null,
            page: $page,
            perPage: $perPage
        );

        return view('finance.dashboard', [
            'reports' => $reports,
            'totalReports' => $reports->total(),
            'filters' => [
                'month' => $month,
                'year' => $year,
            ],
        ]);
    }
}
