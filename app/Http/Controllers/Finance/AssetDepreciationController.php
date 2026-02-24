<?php

namespace App\Http\Controllers\Finance;

use App\DTOs\Finance\DepreciationInputDTO;
use App\Enums\Asset\AssetCategory;
use App\Http\Controllers\Controller;
use App\Http\Requests\Finance\CalculateDepreciationRequest;
use App\Models\Asset\Asset;
use App\Models\FinanceDepreciationCalculationLog;
use App\Services\Finance\DepreciationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Throwable;

class AssetDepreciationController extends Controller
{
    public function __construct(
        private DepreciationService $depreciationService
    ) {}

    public function calculate(CalculateDepreciationRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();
            $input = DepreciationInputDTO::fromArray($validated);
            $result = $this->depreciationService->calculateStraightLine($input);
            $calculatedAt = now(config('app.timezone'));
            $logId = null;
            $logPayload = null;
            $loggingAvailable = Schema::hasTable('finance_depreciation_calculation_logs');

            if ($loggingAvailable) {
                $log = FinanceDepreciationCalculationLog::query()->create([
                    'asset_id' => $validated['asset_id'],
                    'period_month' => (int) $validated['month'],
                    'period_year' => (int) $validated['year'],
                    'acquisition_cost' => $result->acquisitionCost,
                    'useful_life_months' => $result->usefulLifeMonths,
                    'depreciation_per_month' => $result->depreciationPerMonth,
                    'calculated_by' => auth()->id() ? (string) auth()->id() : null,
                    'calculated_at' => $calculatedAt,
                ]);
                $logId = $log->id;
                $log->loadMissing([
                    'asset:id,account_code',
                    'calculator:id,name',
                ]);

                $logPayload = [
                    'id' => $log->id,
                    'calculated_at_label' => $log->calculated_at?->timezone(config('app.timezone'))->format('d/m/Y H:i:s'),
                    'asset_id' => $log->asset_id,
                    'asset_account_code' => $log->asset?->account_code ?? '-',
                    'period_label' => sprintf('%02d/%04d', (int) $log->period_month, (int) $log->period_year),
                    'acquisition_cost' => (float) $log->acquisition_cost,
                    'useful_life_months' => (int) $log->useful_life_months,
                    'depreciation_per_month' => (float) $log->depreciation_per_month,
                    'calculated_by_name' => $log->calculator?->name ?? '-',
                ];
            }

            $message = $loggingAvailable
                ? 'Perhitungan penyusutan berhasil dan log tersimpan.'
                : 'Perhitungan penyusutan berhasil, tetapi log belum tersimpan (tabel log belum tersedia).';

            return response()->json([
                'message' => $message,
                'data' => array_merge($result->toArray(), [
                    'period_month' => (int) $validated['month'],
                    'period_year' => (int) $validated['year'],
                    'calculated_at' => $calculatedAt->format('Y-m-d H:i:s'),
                    'log_id' => $logId,
                    'log_saved' => $logId !== null,
                    'logging_available' => $loggingAvailable,
                    'log' => $logPayload,
                ]),
            ]);
        } catch (Throwable $exception) {
            report($exception);

            return response()->json([
                'message' => 'Gagal menghitung penyusutan aset.',
            ], 500);
        }
    }

    public function index()
    {
        try {
            $assets = Asset::query()
                ->select('id', 'account_code', 'category', 'location')
                ->orderBy('account_code')
                ->get();
            $logs = new Collection();
            if (Schema::hasTable('finance_depreciation_calculation_logs')) {
                $logs = FinanceDepreciationCalculationLog::query()
                    ->with([
                        'asset:id,account_code,category,location',
                        'calculator:id,name',
                    ])
                    ->orderByDesc('calculated_at')
                    ->limit(50)
                    ->get();
            }

            return view('finance.depreciation', [
                'assets' => $assets,
                'logs' => $logs,
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
            'calculator:id,name,email',
        ]);

        return view('finance.depreciation-show', [
            'log' => $log,
        ]);
    }

    public function downloadLogPdf(FinanceDepreciationCalculationLog $log): Response
    {
        $log->loadMissing([
            'asset:id,account_code,category,location',
            'calculator:id,name,email',
        ]);

        $filename = 'depreciation-log-' . $log->id . '.pdf';

        if (class_exists(\Dompdf\Dompdf::class)) {
            $html = view('finance.depreciation-log-pdf', [
                'log' => $log,
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
            'Asset ID: ' . $log->asset_id,
            'Kode Akun Asset: ' . (string) ($log->asset?->account_code ?? '-'),
            'Kategori Asset: ' . $this->formatAssetCategory($log->asset?->category),
            'Lokasi Asset: ' . (string) ($log->asset?->location ?? '-'),
            'Periode: ' . sprintf('%02d/%04d', (int) $log->period_month, (int) $log->period_year),
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
}
