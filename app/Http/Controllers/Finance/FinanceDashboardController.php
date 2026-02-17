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
            $filterType = strtolower((string) ($validated['filter_type'] ?? 'monthly'));
            $date = !empty($validated['date'])
                ? \Carbon\Carbon::parse((string) $validated['date'])->toDateString()
                : null;
            $year = isset($validated['year']) ? (int) $validated['year'] : null;
            $month = isset($validated['month']) ? (int) $validated['month'] : null;
            $page = (int) ($validated['page'] ?? 1);
            $perPage = (int) ($validated['per_page'] ?? 5);
            $currentYear = (int) now()->year;
            $currentMonth = (int) now()->month;
            $hasMonthQuery = $request->query->has('month');

            $periodType = null;
            $reportDate = null;

            if ($filterType === 'monthly') {
                $periodType = 'MONTHLY';
                $year = $year ?? $currentYear;
                if ($month === null && !$hasMonthQuery) {
                    $month = $currentMonth;
                }
            } elseif ($filterType === 'yearly') {
                $periodType = 'YEARLY';
                $year = $year ?? $currentYear;
                $month = null;
            } else {
                if ($date !== null) {
                    $periodType = 'DAILY';
                    $reportDate = $date;
                    $parsedDate = \Carbon\Carbon::parse($date);
                    $year = (int) $parsedDate->year;
                    $month = (int) $parsedDate->month;
                } elseif ($month !== null && $year === null) {
                    $year = $currentYear;
                }
            }

            $reports = $this->reportService->getReports(
                year: $year,
                month: $month,
                periodType: $periodType,
                reportDate: $reportDate,
                page: $page,
                perPage: $perPage
            );

            return view('finance.dashboard', [
                'reports' => $reports,
                'totalReports' => $reports->total(),
                'filters' => [
                    'filter_type' => $filterType,
                    'date' => $reportDate ?? $date,
                    'month' => $month,
                    'year' => $year ?? (
                        in_array($filterType, ['monthly', 'yearly'], true)
                            ? $currentYear
                            : null
                    ),
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
