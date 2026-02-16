<?php

namespace App\Http\Controllers\Finance;

use App\DTOs\Finance\GenerateProfitLossReportDTO;
use App\DTOs\Finance\ProfitLossReportDetailDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\Finance\FinanceReportIndexRequest;
use App\Http\Requests\Finance\GenerateProfitLossReportRequest;
use App\Services\Finance\ReportDocumentService;
use App\Services\Finance\ReportService;
use RuntimeException;

class FinanceReportController extends Controller
{
    public function __construct(
        private ReportService $reportService,
        private ReportDocumentService $reportDocumentService
    ) {}

    public function index(FinanceReportIndexRequest $request)
    {
        $validated = $request->validated();
        $year = (int) ($validated['year'] ?? now()->year);

        $reports = $this->reportService->getReports(
            year: $year,
            month: isset($validated['month']) ? (int) $validated['month'] : null,
            reportType: $validated['report_type'] ?? null,
            page: (int) ($validated['page'] ?? 1),
            perPage: (int) ($validated['per_page'] ?? 20)
        );

        return view('finance.report', [
            'reports' => $reports,
            'filters' => [
                'month' => $validated['month'] ?? null,
                'year' => $year,
                'report_type' => $validated['report_type'] ?? null,
            ],
        ]);
    }

    public function store(GenerateProfitLossReportRequest $request)
    {
        $dto = GenerateProfitLossReportDTO::fromArray(
            $request->validated(),
            auth()->id() ? (string) auth()->id() : null
        );

        $snapshot = $this->reportService->createProfitLossReport($dto);

        return redirect()
            ->route('finance.report.show', $snapshot->id)
            ->with('success', 'Laporan laba-rugi berhasil dibuat sebagai snapshot.');
    }

    public function show(string $id)
    {
        $detail = $this->findReportOrFail($id);

        return view('finance.report-show', [
            'report' => $detail,
        ]);
    }

    public function download(string $id)
    {
        $detail = $this->findReportOrFail($id);
        $documentHtml = $this->reportDocumentService->renderProfitLossDocument($detail);
        $filename = $this->reportDocumentService->buildProfitLossFilename($detail);

        return response($documentHtml, 200, [
            'Content-Type' => 'application/msword; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    private function findReportOrFail(string $id): ProfitLossReportDetailDTO
    {
        try {
            return $this->reportService->getProfitLossReportDetail($id);
        } catch (RuntimeException $exception) {
            abort(404, $exception->getMessage());
        }
    }
}
