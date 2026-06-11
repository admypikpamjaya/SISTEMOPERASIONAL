<?php

namespace App\Http\Controllers\Finance;

use App\DTOs\Finance\DepreciationInputDTO;
use App\Enums\Asset\AssetCategory;
use App\Http\Controllers\Controller;
use App\Http\Requests\Finance\CalculateDepreciationRequest;
use App\Models\Asset\Asset;
use App\Models\FinanceDepreciationCalculationLog;
use App\Services\Finance\DepreciationService;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Throwable;

/**
 * Serves the manual depreciation calculator and its audit log.
 *
 * This controller does not yet generate period-closing depreciation runs from
 * asset master data. It exposes the current manual bridge used by finance
 * staff while the automated policy-based flow is still being completed.
 */
class AssetDepreciationController extends Controller
{
    public function __construct(
        private DepreciationService $depreciationService
    ) {}

    public function calculate(CalculateDepreciationRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();
            $periodStart = $this->parsePeriodMonth($validated['period_start']);
            $periodEnd = $this->parsePeriodMonth($validated['period_end']);
            $usefulLifeMonths = $this->resolveUsefulLifeMonths($periodStart, $periodEnd);
            $asset = Asset::query()
                ->select('id', 'account_code', 'category', 'location', 'purchase_price')
                ->find($validated['asset_id']);
            if ($asset === null) {
                throw new \Exception('Aset tidak ditemukan.', 404);
            }

            $acquisitionCost = $this->resolveAcquisitionCost(
                $validated['acquisition_cost'] ?? null,
                $asset
            );
            $input = DepreciationInputDTO::fromArray(array_merge($validated, [
                'acquisition_cost' => $acquisitionCost,
                'useful_life_months' => $usefulLifeMonths,
            ]));
            $result = $this->depreciationService->calculateStraightLine($input);
            $calculatedAt = now(config('app.timezone'));
            $logId = null;
            $logPayload = null;
            $loggingAvailable = Schema::hasTable('finance_depreciation_calculation_logs');
            $posting = $this->depreciationService->postCalculatedDepreciation(
                $asset,
                $result,
                $periodStart,
                $periodEnd,
                $validated['category_id']
            );

            // The log records what the user typed into the calculator at that
            // moment. It is useful as an audit trail, but it is not the same as
            // a posted depreciation run in finance_depreciation_histories.
            if ($loggingAvailable) {
                try {
                    $log = FinanceDepreciationCalculationLog::query()->create([
                        'asset_id' => $validated['asset_id'],
                        'category_id' => $validated['category_id'],
                        'period_start_date' => $periodStart->toDateString(),
                        'period_end_date' => $periodEnd->toDateString(),
                        'period_month' => $periodEnd->month,
                        'period_year' => $periodEnd->year,
                        'acquisition_cost' => $result->acquisitionCost,
                        'useful_life_months' => $result->usefulLifeMonths,
                        'depreciation_per_month' => $result->depreciationPerMonth,
                        'calculated_by' => auth()->id() ? (string) auth()->id() : null,
                        'calculated_at' => $calculatedAt,
                    ]);
                    $logId = $log->id;
                    $log->loadMissing([
                        'asset:id,account_code,category,location',
                        'category:id,name,status',
                        'calculator:id,name',
                    ]);

                    $logPayload = [
                        'id' => $log->id,
                        'calculated_at_label' => $log->calculated_at?->timezone(config('app.timezone'))->format('d/m/Y H:i:s'),
                        'asset_label' => $this->formatAssetDisplay(
                            $log->asset?->category,
                            $log->asset?->account_code,
                            $log->asset?->location
                        ),
                        'asset_account_code' => $log->asset?->account_code ?? '-',
                        'asset_category_label' => $this->formatAssetCategory($log->asset?->category),
                        'asset_location' => $log->asset?->location ?? '-',
                        'asset_display_meta' => $this->formatAssetMeta(
                            $log->asset?->category,
                            $log->asset?->location
                        ),
                        'finance_category_name' => $log->category?->name ?? '-',
                        'period_label' => $this->formatPeriodLabel(
                            $log->period_start_date,
                            $log->period_end_date,
                            $log->period_month,
                            $log->period_year
                        ),
                        'acquisition_cost' => (float) $log->acquisition_cost,
                        'useful_life_months' => (int) $log->useful_life_months,
                        'depreciation_per_month' => (float) $log->depreciation_per_month,
                        'calculated_by_name' => $log->calculator?->name ?? '-',
                    ];
                } catch (Throwable $loggingException) {
                    report($loggingException);
                    $loggingAvailable = false;
                }
            }

            $message = $loggingAvailable
                ? 'Perhitungan penyusutan berhasil. Histori dan jurnal penyusutan juga sudah direkam.'
                : 'Perhitungan penyusutan berhasil. Histori dan jurnal penyusutan sudah direkam, tetapi tabel log kalkulasi belum tersedia.';

            return response()->json([
                'message' => $message,
                'data' => array_merge($result->toArray(), [
                    'asset_label' => $this->formatAssetDisplay(
                        $asset?->category,
                        $asset?->account_code,
                        $asset?->location
                    ),
                    'period_start' => $validated['period_start'],
                    'period_end' => $validated['period_end'],
                    'period_label' => $this->formatPeriodLabel(
                        $periodStart,
                        $periodEnd
                    ),
                    'period_month' => $periodEnd->month,
                    'period_year' => $periodEnd->year,
                    'calculated_at' => $calculatedAt->format('Y-m-d H:i:s'),
                    'policy_id' => $posting['policy']->id,
                    'depreciation_run_id' => $posting['run']->id,
                    'depreciation_history_id' => $posting['history']->id,
                    'journal_invoice_id' => $posting['invoice']->id,
                    'journal_invoice_no' => $posting['invoice']->invoice_no,
                    'journal_reference' => $posting['invoice']->reference,
                    'journal_status' => $posting['invoice']->status,
                    'log_id' => $logId,
                    'log_saved' => $logId !== null,
                    'logging_available' => $loggingAvailable,
                    'log' => $logPayload,
                ]),
            ]);
        } catch (Throwable $exception) {
            $status = $this->resolveExceptionStatusCode($exception);
            if ($status >= 500) {
                report($exception);
            }

            return response()->json([
                'message' => $status >= 500
                    ? 'Gagal menghitung penyusutan aset.'
                    : $exception->getMessage(),
            ], $status);
        }
    }

    public function index(Request $request)
    {
        try {
            $filters = $request->validate([
                'category_id' => [
                    'nullable',
                    'uuid',
                    Rule::exists('finance_categories', 'id'),
                ],
            ]);
            $selectedCategoryId = $filters['category_id'] ?? null;

            // The page still needs the asset list because users pick an asset
            // first, then enter finance values manually for the calculation.
            $assets = Asset::query()
                ->select('id', 'account_code', 'category', 'location', 'purchase_price')
                ->orderBy('account_code')
                ->get()
                ->map(function (Asset $asset): Asset {
                    $asset->category_label = $this->formatAssetCategory($asset->category);
                    $asset->display_label = $this->formatAssetDisplay(
                        $asset->category,
                        $asset->account_code,
                        $asset->location
                    );

                    return $asset;
                });
            $logs = new Collection();
            if (Schema::hasTable('finance_depreciation_calculation_logs')) {
                $logs = FinanceDepreciationCalculationLog::query()
                    ->with([
                        'asset:id,account_code,category,location',
                        'category:id,name,status',
                        'calculator:id,name',
                    ])
                    ->when($selectedCategoryId, function ($query) use ($selectedCategoryId): void {
                        $query->where('category_id', $selectedCategoryId);
                    })
                    ->orderByDesc('calculated_at')
                    ->limit(50)
                    ->get()
                    ->map(function (FinanceDepreciationCalculationLog $log): FinanceDepreciationCalculationLog {
                        $log->asset_category_label = $this->formatAssetCategory($log->asset?->category);
                        $log->asset_display_code = $log->asset?->account_code ?? '-';
                        $log->asset_display_meta = $this->formatAssetMeta(
                            $log->asset?->category,
                            $log->asset?->location
                        );
                        $log->asset_display_label = $this->formatAssetDisplay(
                            $log->asset?->category,
                            $log->asset?->account_code,
                            $log->asset?->location
                        );
                        $log->period_display_label = $this->formatPeriodLabel(
                            $log->period_start_date,
                            $log->period_end_date,
                            $log->period_month,
                            $log->period_year
                        );
                        $log->finance_category_name = $log->category?->name ?? '-';

                        return $log;
                    });
            }

            return view('finance.depreciation', [
                'assets' => $assets,
                'logs' => $logs,
                'filters' => [
                    'category_id' => $selectedCategoryId,
                ],
            ]);
        } catch (Throwable $exception) {
            report($exception);

            return redirect()
                ->route('finance.dashboard')
                ->with('error', 'Gagal memuat halaman penyusutan aset.');
        }
    }

    public function showLog(FinanceDepreciationCalculationLog $log)
    {
        $log->loadMissing([
            'asset:id,account_code,category,location',
            'category:id,name,status',
            'calculator:id,name,email',
        ]);

        return view('finance.depreciation-show', [
            'log' => $log,
            'periodLabel' => $this->formatPeriodLabel(
                $log->period_start_date,
                $log->period_end_date,
                $log->period_month,
                $log->period_year
            ),
        ]);
    }

    public function downloadLogPdf(FinanceDepreciationCalculationLog $log): Response
    {
        $log->loadMissing([
            'asset:id,account_code,category,location',
            'category:id,name,status',
            'calculator:id,name,email',
        ]);

        $filename = 'depreciation-log-' . $log->id . '.pdf';

        if (class_exists(\Dompdf\Dompdf::class)) {
            $html = view('finance.depreciation-log-pdf', [
                'log' => $log,
                'periodLabel' => $this->formatPeriodLabel(
                    $log->period_start_date,
                    $log->period_end_date,
                    $log->period_month,
                    $log->period_year
                ),
                'timezone' => config('app.timezone'),
            ])->render();

            $dompdf = new \Dompdf\Dompdf();
            $dompdf->loadHtml($html, 'UTF-8');
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();

            return response($dompdf->output(), 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ]);
        }

        return response($this->buildFallbackPdf($log), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    private function buildFallbackPdf(FinanceDepreciationCalculationLog $log): string
    {
        $calculatedAt = $log->calculated_at
            ? $log->calculated_at->timezone(config('app.timezone'))->format('d/m/Y H:i:s')
            : '-';

        $lines = [
            'DETAIL LOG PENYUSUTAN ASET',
            'ID Log: ' . $log->id,
            'Waktu Hitung: ' . $calculatedAt,
            'Kode Akun Asset: ' . (string) ($log->asset?->account_code ?? '-'),
            'Kategori Asset: ' . $this->formatAssetCategory($log->asset?->category),
            'Lokasi Asset: ' . (string) ($log->asset?->location ?? '-'),
            'Kategori Finance: ' . (string) ($log->category?->name ?? '-'),
            'Periode: ' . $this->formatPeriodLabel(
                $log->period_start_date,
                $log->period_end_date,
                $log->period_month,
                $log->period_year
            ),
            'Nilai Perolehan: Rp ' . number_format((float) $log->acquisition_cost, 2, ',', '.'),
            'Umur Manfaat (bulan): ' . (int) $log->useful_life_months,
            'Penyusutan per bulan: Rp ' . number_format((float) $log->depreciation_per_month, 2, ',', '.'),
            'Dihitung Oleh: ' . (string) ($log->calculator?->name ?? '-'),
        ];

        $content = "BT\n/F1 11 Tf\n15 TL\n50 790 Td\n";
        foreach ($lines as $index => $line) {
            if ($index > 0) {
                $content .= "T*\n";
            }
            $content .= '(' . $this->escapePdfText($line) . ") Tj\n";
        }
        $content .= "ET";

        $objects = [
            1 => '<< /Type /Catalog /Pages 2 0 R >>',
            2 => '<< /Type /Pages /Kids [3 0 R] /Count 1 >>',
            3 => '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Resources << /Font << /F1 5 0 R >> >> /Contents 4 0 R >>',
            4 => "<< /Length " . strlen($content) . " >>\nstream\n" . $content . "\nendstream",
            5 => '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica /Encoding /WinAnsiEncoding >>',
        ];

        $pdf = "%PDF-1.4\n";
        $offsets = [0];

        foreach ($objects as $id => $body) {
            $offsets[$id] = strlen($pdf);
            $pdf .= $id . " 0 obj\n" . $body . "\nendobj\n";
        }

        $xrefOffset = strlen($pdf);
        $pdf .= "xref\n0 6\n";
        $pdf .= "0000000000 65535 f \n";
        for ($i = 1; $i <= 5; $i++) {
            $offset = $offsets[$i] ?? 0;
            $pdf .= sprintf('%010d 00000 n ', $offset) . "\n";
        }

        $pdf .= "trailer\n<< /Size 6 /Root 1 0 R >>\n";
        $pdf .= "startxref\n{$xrefOffset}\n%%EOF";

        return $pdf;
    }

    private function escapePdfText(string $text): string
    {
        return str_replace(
            ['\\', '(', ')', "\r", "\n", "\t"],
            ['\\\\', '\(', '\)', ' ', ' ', ' '],
            $text
        );
    }

    private function formatAssetCategory(mixed $category): string
    {
        if ($category instanceof AssetCategory) {
            return $category->label();
        }

        if (is_string($category) && trim($category) !== '') {
            $resolved = AssetCategory::tryFrom($category);
            return $resolved?->label() ?? $category;
        }

        return '-';
    }

    private function formatAssetDisplay(
        mixed $category,
        ?string $accountCode,
        ?string $location
    ): string {
        return implode(' | ', [
            $this->formatAssetCategory($category),
            $accountCode ?: '-',
            $location ?: '-',
        ]);
    }

    private function formatAssetMeta(mixed $category, ?string $location): string
    {
        return implode(' - ', array_filter([
            $this->formatAssetCategory($category),
            $location,
        ])) ?: '-';
    }

    private function formatPeriodLabel(
        ?CarbonInterface $periodStart = null,
        ?CarbonInterface $periodEnd = null,
        ?int $periodMonth = null,
        ?int $periodYear = null
    ): string {
        if ($periodStart !== null && $periodEnd !== null) {
            return $periodStart->format('m/Y') . ' s/d ' . $periodEnd->format('m/Y');
        }

        if ($periodMonth !== null && $periodYear !== null) {
            return sprintf('%02d/%04d', $periodMonth, $periodYear);
        }

        return '-';
    }

    private function parsePeriodMonth(string $value): CarbonImmutable
    {
        return CarbonImmutable::createFromFormat(
            'Y-m',
            trim($value),
            config('app.timezone')
        )->startOfMonth();
    }

    private function resolveUsefulLifeMonths(
        CarbonImmutable $periodStart,
        CarbonImmutable $periodEnd
    ): int {
        return (($periodEnd->year - $periodStart->year) * 12)
            + ($periodEnd->month - $periodStart->month)
            + 1;
    }

    private function resolveAcquisitionCost(mixed $inputAcquisitionCost, Asset $asset): float
    {
        if ($inputAcquisitionCost !== null && $inputAcquisitionCost !== '') {
            return round((float) $inputAcquisitionCost, 2);
        }

        if ($asset->purchase_price !== null) {
            return round((float) $asset->purchase_price, 2);
        }

        throw new \Exception(
            'Harga aset belum tersedia. Isi nilai perolehan manual atau lengkapi harga pada data aset terlebih dahulu.',
            422
        );
    }

    private function resolveExceptionStatusCode(Throwable $exception): int
    {
        $code = (int) $exception->getCode();

        if ($code >= 400 && $code <= 599) {
            return $code;
        }

        return 500;
    }
}
