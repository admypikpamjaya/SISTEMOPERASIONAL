<?php

namespace App\Http\Controllers\Finance;

use App\DTOs\Finance\FinanceSnapshotFilterDTO;
use App\DTOs\Finance\GenerateProfitLossReportDTO;
use App\DTOs\Finance\ProfitLossReportDetailDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\Finance\FinanceReportIndexRequest;
use App\Http\Requests\Finance\GenerateProfitLossReportRequest;
use App\Services\Finance\ReportDocumentService;
use App\Services\Finance\ReportService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use RuntimeException;
use Throwable;

class FinanceReportController extends Controller
{
    public function __construct(
        private ReportService $reportService,
        private ReportDocumentService $reportDocumentService
    ) {}

    public function index(FinanceReportIndexRequest $request)
    {
        try {
            $validated = $request->validated();
            $periodType = strtoupper((string) ($validated['period_type'] ?? 'MONTHLY'));

            $reportDate = !empty($validated['report_date'])
                ? Carbon::parse((string) $validated['report_date'])
                : now();
            $year = $periodType === 'DAILY'
                ? (int) $reportDate->year
                : (int) ($validated['year'] ?? now()->year);
            $month = $periodType === 'YEARLY'
                ? null
                : ($periodType === 'DAILY'
                    ? (int) $reportDate->month
                    : (isset($validated['month']) ? (int) $validated['month'] : (int) now()->month));
            $day = $periodType === 'DAILY' ? (int) $reportDate->day : null;

            $suggestedOpeningBalance = $this->reportService->getSuggestedOpeningBalance(
                $periodType,
                $year,
                $month,
                $day
            );

            return view('finance.report', [
                'suggestedOpeningBalance' => $suggestedOpeningBalance,
                'defaults' => [
                    'period_type' => $periodType,
                    'report_date' => $reportDate->toDateString(),
                    'year' => $year,
                    'month' => $month,
                ],
            ]);
        } catch (Throwable $exception) {
            report($exception);

            return redirect()
                ->route('finance.dashboard')
                ->with('error', 'Gagal memuat halaman input laporan finance.');
        }
    }

    public function snapshots(FinanceReportIndexRequest $request)
    {
        try {
            $validated = $request->validated();
            $selectedPeriodType = strtoupper((string) ($validated['period_type'] ?? 'MONTHLY'));

            $filter = FinanceSnapshotFilterDTO::fromArray($validated);
            $result = $this->reportService->getSnapshots($filter);

            return view('finance.snapshots', [
                'reports' => $result['reports'],
                'comparisons' => $result['comparisons'],
                'totals' => $result['totals'],
                'filters' => [
                    'period_type' => $selectedPeriodType,
                    'report_date' => $filter->reportDate,
                    'month' => $filter->month,
                    'year' => $filter->year,
                    'comparison_type' => $filter->comparisonType,
                    'comparison_offset' => $filter->comparisonOffset,
                    'comparison_date' => $filter->comparisonDate,
                ],
            ]);
        } catch (Throwable $exception) {
            report($exception);

            return redirect()
                ->route('finance.report.index')
                ->with('error', 'Gagal memuat snapshot laporan finance.');
        }
    }

    public function store(GenerateProfitLossReportRequest $request)
    {
        try {
            $dto = GenerateProfitLossReportDTO::fromArray(
                $request->validated(),
                auth()->id() ? (string) auth()->id() : null
            );

            $snapshot = $this->reportService->createProfitLossReport($dto);

            return redirect()
                ->route('finance.report.show', $snapshot->id)
                ->with('success', 'Laporan laba-rugi berhasil dibuat sebagai snapshot.');
        } catch (Throwable $exception) {
            report($exception);

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Gagal membuat laporan laba-rugi. Silakan coba lagi.');
        }
    }

    public function show(string $id)
    {
        try {
            $detail = $this->findReportOrFail($id);

            return view('finance.report-show', [
                'report' => $detail,
            ]);
        } catch (RuntimeException $exception) {
            return redirect()
                ->route('finance.report.snapshots')
                ->with('error', $exception->getMessage());
        } catch (Throwable $exception) {
            report($exception);

            return redirect()
                ->route('finance.report.snapshots')
                ->with('error', 'Gagal memuat detail laporan finance.');
        }
    }

    public function download(Request $request, string $id)
    {
        try {
            $format = strtolower((string) $request->query('format', 'docx'));
            if (!in_array($format, ['docx', 'word', 'excel', 'pdf'], true)) {
                return redirect()
                    ->back()
                    ->with('error', 'Format download tidak valid.');
            }
            if ($format === 'word') {
                $format = 'docx';
            }

            $detail = $this->findReportOrFail($id);
            $exported = $this->reportDocumentService->exportProfitLoss($detail, $format);

            return response($exported['content'], 200, [
                'Content-Type' => $exported['mime'],
                'Content-Disposition' => 'attachment; filename="' . $exported['filename'] . '"',
                'X-Content-Type-Options' => 'nosniff',
            ]);
        } catch (RuntimeException $exception) {
            return redirect()
                ->route('finance.report.snapshots')
                ->with('error', $exception->getMessage());
        } catch (Throwable $exception) {
            report($exception);

            return redirect()
                ->back()
                ->with('error', 'Gagal mengunduh dokumen laporan.');
        }
    }

    private function findReportOrFail(string $id): ProfitLossReportDetailDTO
    {
        return $this->reportService->getProfitLossReportDetail($id);
    }
}
