<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Http\Requests\Finance\FinanceDashboardRequest;
use App\Services\Finance\ReportService;
use Throwable;

class FinanceDashboardController extends Controller
{
    public function __construct(
        private ReportService $reportService
    ) {}

    public function index(FinanceDashboardRequest $request)
    {
        try {
            $validated = $request->validated();
            $filterType = $validated['filter_type'] ?? 'monthly';
            $date = $validated['date'] ?? null;
            $year = (int) ($validated['year'] ?? now()->year);
            $month = isset($validated['month']) ? (int) $validated['month'] : null;
            $page = (int) ($validated['page'] ?? 1);
            $perPage = (int) ($validated['per_page'] ?? 5);

            if ($filterType === 'daily' && $date) {
                $parsedDate = \Carbon\Carbon::parse($date);
                $reports = $this->reportService->getReports(
                    year: $parsedDate->year,
                    month: $parsedDate->month,
                    reportType: 'DAILY',
                    reportDate: $parsedDate->toDateString(),
                    page: $page,
                    perPage: $perPage
                );
            } else {
                $reports = $this->reportService->getReports(
                    year: $year,
                    month: $month,
                    reportType: null,
                    page: $page,
                    perPage: $perPage
                );
            }

            return view('finance.dashboard', [
                'reports' => $reports,
                'totalReports' => $reports->total(),
                'filters' => [
                    'filter_type' => $filterType,
                    'date' => $date,
                    'month' => $month,
                    'year' => $year,
                ],
            ]);
        } catch (Throwable $exception) {
            report($exception);

            return redirect()
                ->route('dashboard.index')
                ->with('error', 'Gagal memuat dashboard finance.');
        }
    }
}
