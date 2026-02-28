<?php

namespace App\Http\Controllers\Finance;

use App\DTOs\Finance\FinanceSnapshotFilterDTO;
use App\DTOs\Finance\GenerateProfitLossReportDTO;
use App\DTOs\Finance\ProfitLossReportDetailDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\Finance\FinanceReportIndexRequest;
use App\Http\Requests\Finance\GenerateProfitLossReportRequest;
use App\Models\FinanceAccount;
use App\Models\FinanceInvoice;
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
                'accountOptions' => $this->getAccountOptions(),
                'invoiceOptions' => $this->getInvoiceNumberOptions(),
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

    public function edit(string $id)
    {
        try {
            $payload = $this->reportService->getEditPayload($id);

            return view('finance.report', [
                'suggestedOpeningBalance' => (float) data_get($payload, 'opening_balance', 0),
                'defaults' => [
                    'period_type' => (string) data_get($payload, 'report_type', 'MONTHLY'),
                    'report_date' => (string) data_get($payload, 'report_date', now()->toDateString()),
                    'year' => (int) data_get($payload, 'year', now()->year),
                    'month' => data_get($payload, 'month'),
                ],
                'entryRows' => (array) data_get($payload, 'entries', []),
                'editingReport' => $payload,
                'accountOptions' => $this->getAccountOptions(),
                'invoiceOptions' => $this->getInvoiceNumberOptions(),
            ]);
        } catch (RuntimeException $exception) {
            return redirect()
                ->route('finance.report.snapshots')
                ->with('error', $exception->getMessage());
        } catch (Throwable $exception) {
            report($exception);

            return redirect()
                ->route('finance.report.snapshots')
                ->with('error', 'Gagal memuat halaman edit snapshot.');
        }
    }

    public function update(GenerateProfitLossReportRequest $request, string $id)
    {
        try {
            $dto = GenerateProfitLossReportDTO::fromArray(
                $request->validated(),
                auth()->id() ? (string) auth()->id() : null
            );

            $snapshot = $this->reportService->updateProfitLossReport($id, $dto);

            return redirect()
                ->route('finance.report.show', $snapshot->id)
                ->with('success', 'Snapshot laporan laba-rugi berhasil diperbarui.');
        } catch (RuntimeException $exception) {
            return redirect()
                ->route('finance.report.snapshots')
                ->with('error', $exception->getMessage());
        } catch (Throwable $exception) {
            report($exception);

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Gagal memperbarui snapshot laba-rugi. Silakan coba lagi.');
        }
    }

    public function destroy(string $id)
    {
        try {
            $this->reportService->deleteProfitLossReport(
                $id,
                auth()->id() ? (string) auth()->id() : null
            );

            return redirect()
                ->route('finance.report.snapshots')
                ->with('success', 'Snapshot laporan laba-rugi berhasil dihapus.');
        } catch (RuntimeException $exception) {
            return redirect()
                ->route('finance.report.snapshots')
                ->with('error', $exception->getMessage());
        } catch (Throwable $exception) {
            report($exception);

            return redirect()
                ->route('finance.report.snapshots')
                ->with('error', 'Gagal menghapus snapshot laba-rugi. Silakan coba lagi.');
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

    private function getAccountOptions()
    {
        return FinanceAccount::query()
            ->active()
            ->orderBy('class_no')
            ->orderBy('code')
            ->get(['code', 'name', 'type', 'class_no'])
            ->map(fn (FinanceAccount $account): array => [
                'code' => (string) $account->code,
                'name' => (string) $account->name,
                'type' => (string) $account->type,
                'type_label' => (string) $account->type_label,
                'class_no' => (int) $account->class_no,
            ])
            ->values();
    }

    private function getInvoiceNumberOptions()
    {
        return FinanceInvoice::query()
            ->whereNotNull('invoice_no')
            ->where('invoice_no', '!=', '')
            ->orderByDesc('accounting_date')
            ->orderByDesc('created_at')
            ->limit(300)
            ->pluck('invoice_no')
            ->map(fn ($invoiceNo) => trim((string) $invoiceNo))
            ->filter()
            ->unique()
            ->values();
    }
}
