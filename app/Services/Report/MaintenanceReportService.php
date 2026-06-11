<?php 

namespace App\Services\Report;

use App\DTOs\Report\CreateMaintenanceReportDTO;
use App\DTOs\Report\MaintenanceReportDataDTO;
use App\DTOs\Report\UpdateMaintenanceReportDTO;
use App\DTOs\Report\UpdateMaintenanceReportStatusDTO;
use App\Enums\Report\Maintenance\AssetMaintenanceReportStatus;
use App\Models\Log\MaintenanceDocumentation;
use App\Models\Log\MaintenanceLog;
use App\Services\Asset\AssetFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class MaintenanceReportService
{
    public function __construct(
        private MaintenanceNotificationService $maintenanceNotificationService
    ) {}

    public function getLogs(
        ?string $keyword = null, 
        ?AssetMaintenanceReportStatus $status = null, 
        ?int $page = 1,     
        ?string $dateFrom = null,
        ?string $dateTo = null
    )
    {
        return $this->buildFilteredLogQuery($keyword, $status, $dateFrom, $dateTo)
            ->orderBy('created_at', 'desc')
            ->paginate(10, ['*'], 'page', $page)
            ->appends(array_filter([
                'keyword'   => $keyword,
                'status'    => $status?->value,
                'date_from' => $dateFrom,
                'date_to'   => $dateTo,
            ]));
    }

    public function getLog(string $id)
    {
        $log = $this->findLogOrFail($id, ['asset', 'maintenanceDocumentations']);

        $asset = $log->asset;
        $relation = AssetFactory::createHandler($asset->category)
            ->getRelationName();

        if ($relation) {
            $asset->load($relation);
        }

        return MaintenanceReportDataDTO::fromModel($log);
    }

    public function createLog(CreateMaintenanceReportDTO $dto)
    {
        $log = DB::transaction(function () use ($dto) {
            $path = MaintenanceDocumentation::store($dto->evidencePhoto);    

            $log = MaintenanceLog::create([
                'asset_id' => $dto->assetId,
                'worker_name' => $dto->workerName,
                'date' => $dto->workingDate,
                'issue_description' => $dto->issueDescription,
                'working_description' => $dto->workingDescription,
                'pic' => $dto->pic,
                'cost' => $dto->cost
            ]);

            $log->maintenanceDocumentations()->create([
                'document_path' => $path
            ]);

            return $log->refresh()->load(['asset', 'maintenanceDocumentations']);
        });

        try {
            $this->maintenanceNotificationService->sendForLog($log);
        } catch (\Throwable $exception) {
            Log::warning('[MAINTENANCE EMAIL AUTO SEND FAILED]', [
                'maintenance_log_id' => (string) $log->id,
                'error' => $exception->getMessage(),
            ]);
            report($exception);
        }

        return MaintenanceReportDataDTO::fromModel($log);
    }

    public function updateLog(UpdateMaintenanceReportDTO $dto)
    {
        $log = MaintenanceLog::find($dto->id);
        if(empty($log))
            throw new \Exception('Laporan tidak ditemukan.', 404);

        $log->update([
            'worker_name' => $dto->workerName,
            'date' => $dto->workingDate,
            'issue_description' => $dto->issueDescription,
            'working_description' => $dto->workingDescription,
            'pic' => $dto->pic,
            'cost' => $dto->cost
        ]);

        return MaintenanceReportDataDTO::fromModel($log);
    }

    public function updateStatus(UpdateMaintenanceReportStatusDTO $dto)
    {
        $log = MaintenanceLog::find($dto->id);
        if(empty($log))
            throw new \Exception('Laporan tidak ditemukan.', 404);

        if($dto->status === AssetMaintenanceReportStatus::PENDING)
            throw new \Exception('Status tidak valid.', 400);
        
        $log->update([
            'status' => $dto->status
        ]);

        return MaintenanceReportDataDTO::fromModel($log);
    }

    public function deleteLog(string $id)
    {
        $log = MaintenanceLog::find($id);
        if(empty($log))
            throw new \Exception('Laporan tidak ditemukan.', 404);

        $log->delete();
    }

    public function exportLogToExcel(
        array $ids = [],
        ?string $keyword = null,
        ?AssetMaintenanceReportStatus $status = null,
        ?string $dateFrom = null,
        ?string $dateTo = null
    )
    {
        $logs = $this->getExportLogs($ids, $keyword, $status, $dateFrom, $dateTo);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Maintenance Logs');

        $sheet->setCellValue('A1', 'Kode Akun');
        $sheet->setCellValue('B1', 'Lokasi');
        $sheet->setCellValue('C1', 'Nama Pekerja');
        $sheet->setCellValue('D1', 'Tanggal Pengerjaan');
        $sheet->setCellValue('E1', 'Deskripsi Masalah');
        $sheet->setCellValue('F1', 'Deskripsi Pekerjaan');
        $sheet->setCellValue('G1', 'PIC (Penanggung Jawab)');
        $sheet->setCellValue('H1', 'Status');
        $sheet->setCellValue('I1', 'Biaya');
        $sheet->setCellValue('J1', 'Kategori');
        $sheet->setCellValue('K1', 'Dokumentasi Pemeliharaan');

        $row = 2; 
        foreach ($logs as $log) {
            $sheet->setCellValue('A' . $row, $log->asset?->account_code ?? '-');
            $sheet->setCellValue('B' . $row, $log->asset?->location ?? '-');
            $sheet->setCellValue('C' . $row, $log->worker_name);
            $sheet->setCellValue('D' . $row, $this->formatLogDate($log));
            $sheet->setCellValue('E' . $row, $log->issue_description);
            $sheet->setCellValue('F' . $row, $log->working_description);
            $sheet->setCellValue('G' . $row, $log->pic);
            $sheet->setCellValue('H' . $row, $log->status->value);
            $sheet->setCellValue('I' . $row, $log->cost_formatted);
            $sheet->setCellValue('J' . $row, $this->formatAssetCategory($log->asset?->category));
            $sheet->setCellValue('K' . $row, $this->getDocumentationUrl($log));

            $row++;
        }

        foreach (range('A', 'K') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);
        return $writer;
    }

    public function exportLogToPdf(
        array $ids = [],
        ?string $keyword = null,
        ?AssetMaintenanceReportStatus $status = null,
        ?string $dateFrom = null,
        ?string $dateTo = null
    ): string
    {
        $logs = $this->getExportLogs($ids, $keyword, $status, $dateFrom, $dateTo);
        $periodLabel = $this->formatPeriodLabel($dateFrom, $dateTo);

        if (class_exists(\Dompdf\Dompdf::class)) {
            $dompdf = new \Dompdf\Dompdf();
            $dompdf->loadHtml($this->renderPdfHtml($logs, $periodLabel), 'UTF-8');
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();

            return $dompdf->output();
        }

        return $this->buildFallbackPdf($logs, $periodLabel);
    }

    public function sendNotification(string $id, bool $manuallyTriggered = true): string
    {
        return $this->sendNotificationToRecipients($id, $manuallyTriggered);
    }

    /**
     * @param array<int, string> $manualRecipients
     */
    public function sendNotificationToRecipients(
        string $id,
        bool $manuallyTriggered = true,
        array $manualRecipients = [],
        ?array $selectedAdditionalRecipientIds = null
    ): string
    {
        $log = $this->findLogOrFail($id, ['asset', 'maintenanceDocumentations']);

        $recipients = $this->maintenanceNotificationService->sendForLog(
            $log,
            $manuallyTriggered,
            $manualRecipients,
            $selectedAdditionalRecipientIds
        );

        return $this->maintenanceNotificationService->formatRecipients($recipients);
    }

    public function getNotificationRecipients(): array
    {
        return $this->maintenanceNotificationService->getRecipientPayload();
    }

    private function findLogOrFail(string $id, array $relations = []): MaintenanceLog
    {
        $query = MaintenanceLog::query();

        if ($relations !== []) {
            $query->with($relations);
        }

        $log = $query->find($id);
        if (empty($log)) {
            throw new \Exception('Laporan tidak ditemukan.', 404);
        }

        return $log;
    }

    private function buildFilteredLogQuery(
        ?string $keyword = null,
        ?AssetMaintenanceReportStatus $status = null,
        ?string $dateFrom = null,
        ?string $dateTo = null
    ): Builder
    {
        $query = MaintenanceLog::query()->with('asset');

        if ($keyword) {
            $query->where(function ($q) use ($keyword) {
                $q->where('worker_name', 'like', "%{$keyword}%")
                    ->orWhere('issue_description', 'like', "%{$keyword}%")
                    ->orWhere('working_description', 'like', "%{$keyword}%")
                    ->orWhere('pic', 'like', "%{$keyword}%")
                    ->orWhereHas('asset', function ($assetQuery) use ($keyword) {
                        $assetQuery->where('account_code', 'like', "%{$keyword}%")
                            ->orWhere('location', 'like', "%{$keyword}%");
                    });
            });
        }

        if ($status) {
            $query->where('status', $status->value);
        }

        if ($dateFrom) {
            $query->whereDate('date', '>=', $dateFrom);
        }

        if ($dateTo) {
            $query->whereDate('date', '<=', $dateTo);
        }

        return $query;
    }

    private function getExportLogs(
        array $ids = [],
        ?string $keyword = null,
        ?AssetMaintenanceReportStatus $status = null,
        ?string $dateFrom = null,
        ?string $dateTo = null
    ): Collection
    {
        $normalizedIds = array_values(array_filter($ids, static fn ($id): bool => filled($id)));

        return $this->buildFilteredLogQuery($keyword, $status, $dateFrom, $dateTo)
            ->with(['asset', 'maintenanceDocumentations'])
            ->when($normalizedIds !== [], fn (Builder $query) => $query->whereIn('id', $normalizedIds))
            ->orderBy('date')
            ->orderBy('created_at')
            ->get();
    }

    private function renderPdfHtml(Collection $logs, string $periodLabel): string
    {
        $rows = $logs->map(function (MaintenanceLog $log, int $index): string {
            return '<tr>'
                . '<td>' . ($index + 1) . '</td>'
                . '<td>' . e($log->asset?->account_code ?? '-') . '</td>'
                . '<td>' . e($this->formatLogDate($log)) . '</td>'
                . '<td>' . e($log->pic) . '</td>'
                . '<td>' . e($log->status->value) . '</td>'
                . '<td>' . e($log->cost_formatted) . '</td>'
                . '<td>' . e($log->issue_description) . '</td>'
                . '<td>' . e($log->working_description) . '</td>'
                . '</tr>';
        })->implode('');

        if ($rows === '') {
            $rows = '<tr><td colspan="8" style="text-align:center;">Tidak ada data laporan.</td></tr>';
        }

        return '<!doctype html><html><head><meta charset="utf-8"><style>'
            . 'body{font-family:DejaVu Sans,Arial,sans-serif;font-size:11px;color:#111827;}'
            . 'h1{font-size:18px;margin:0 0 6px;}'
            . '.meta{color:#475569;margin-bottom:14px;}'
            . 'table{width:100%;border-collapse:collapse;}'
            . 'th,td{border:1px solid #cbd5e1;padding:6px;vertical-align:top;}'
            . 'th{background:#e2e8f0;text-align:left;}'
            . '</style></head><body>'
            . '<h1>Laporan Pemeliharaan</h1>'
            . '<div class="meta">Periode: ' . e($periodLabel) . ' | Dicetak: ' . e(now()->format('d/m/Y H:i')) . ' | Total: ' . $logs->count() . ' laporan</div>'
            . '<table><thead><tr>'
            . '<th>No</th><th>Kode Aset</th><th>Tanggal</th><th>PIC</th><th>Status</th><th>Biaya</th><th>Masalah</th><th>Pekerjaan</th>'
            . '</tr></thead><tbody>' . $rows . '</tbody></table>'
            . '</body></html>';
    }

    private function buildFallbackPdf(Collection $logs, string $periodLabel): string
    {
        $blocks = [
            $this->pdfBlock('LAPORAN PEMELIHARAAN', true, 18, 4),
            $this->pdfBlock('Periode: ' . $periodLabel, false, 10, 2),
            $this->pdfBlock('Dicetak: ' . now()->format('d/m/Y H:i') . ' | Total: ' . $logs->count() . ' laporan', false, 10, 12),
        ];

        if ($logs->isEmpty()) {
            $blocks[] = $this->pdfBlock('Tidak ada data laporan untuk filter yang dipilih.', false, 11, 0);
        }

        foreach ($logs as $index => $log) {
            $blocks[] = $this->pdfBlock(
                '#' . ($index + 1) . ' - ' . ($log->asset?->account_code ?? '-'),
                true,
                12,
                3
            );
            $blocks[] = $this->pdfBlock('Tanggal: ' . $this->formatLogDate($log) . ' | Status: ' . $log->status->value . ' | Biaya: ' . $log->cost_formatted);
            $blocks[] = $this->pdfBlock('Kategori: ' . $this->formatAssetCategory($log->asset?->category) . ' | Lokasi: ' . (string) ($log->asset?->location ?? '-'));
            $blocks[] = $this->pdfBlock('Pekerja: ' . (string) $log->worker_name . ' | PIC: ' . (string) $log->pic);
            $blocks[] = $this->pdfBlock('Masalah: ' . (string) $log->issue_description, false, 10, 1, 12);
            $blocks[] = $this->pdfBlock('Pekerjaan: ' . (string) $log->working_description, false, 10, 1, 12);
            $blocks[] = $this->pdfBlock('Dokumentasi: ' . $this->getDocumentationUrl($log), false, 9, 10, 12);
        }

        return $this->buildPdfBinary($this->buildPdfPages($blocks));
    }

    private function pdfBlock(
        string $text,
        bool $bold = false,
        int $fontSize = 10,
        int $marginAfter = 1,
        int $indent = 0
    ): array {
        return [
            'text' => $text,
            'bold' => $bold,
            'font_size' => $fontSize,
            'margin_after' => $marginAfter,
            'indent' => $indent,
        ];
    }

    private function buildPdfPages(array $blocks): array
    {
        $pages = [];
        $content = '';
        $cursorTop = 42.0;
        $pageNumber = 1;
        $pageLimitTop = 790.0;

        foreach ($blocks as $block) {
            $fontSize = (int) ($block['font_size'] ?? 10);
            $lineHeight = max(13, $fontSize + 4);
            $wrappedLines = $this->wrapPdfText((string) ($block['text'] ?? ''), $fontSize >= 12 ? 74 : 92);
            $blockHeight = (count($wrappedLines) * $lineHeight) + (int) ($block['margin_after'] ?? 0);

            if ($cursorTop + $blockHeight > $pageLimitTop && $content !== '') {
                $content .= $this->drawPdfFooter($pageNumber);
                $pages[] = $content;
                $content = '';
                $cursorTop = 42.0;
                $pageNumber++;
            }

            foreach ($wrappedLines as $line) {
                $content .= $this->drawPdfText(
                    $line,
                    40 + (int) ($block['indent'] ?? 0),
                    $cursorTop,
                    $fontSize,
                    (bool) ($block['bold'] ?? false)
                );
                $cursorTop += $lineHeight;
            }

            $cursorTop += (int) ($block['margin_after'] ?? 0);
        }

        $content .= $this->drawPdfFooter($pageNumber);
        $pages[] = $content;

        return $pages;
    }

    private function buildPdfBinary(array $pageStreams): string
    {
        $objects = [];
        $objectIndex = 2;

        $fontRegularId = ++$objectIndex;
        $objects[$fontRegularId] = '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica /Encoding /WinAnsiEncoding >>';

        $fontBoldId = ++$objectIndex;
        $objects[$fontBoldId] = '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold /Encoding /WinAnsiEncoding >>';

        $pageObjectIds = [];
        foreach ($pageStreams as $pageStream) {
            $contentObjectId = ++$objectIndex;
            $objects[$contentObjectId] = "<< /Length " . strlen($pageStream) . " >>\nstream\n"
                . $pageStream . "\nendstream";

            $pageObjectId = ++$objectIndex;
            $pageObjectIds[] = $pageObjectId;
            $objects[$pageObjectId] = "<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Resources << /Font << "
                . "/F1 {$fontRegularId} 0 R /F2 {$fontBoldId} 0 R "
                . ">> >> /Contents {$contentObjectId} 0 R >>";
        }

        $kids = implode(' ', array_map(static fn (int $id): string => $id . ' 0 R', $pageObjectIds));
        $objects[2] = "<< /Type /Pages /Kids [ {$kids} ] /Count " . count($pageObjectIds) . " >>";
        $objects[1] = '<< /Type /Catalog /Pages 2 0 R >>';

        ksort($objects);

        $pdf = "%PDF-1.4\n";
        $offsets = [0];
        foreach ($objects as $id => $body) {
            $offsets[$id] = strlen($pdf);
            $pdf .= $id . " 0 obj\n" . $body . "\nendobj\n";
        }

        $xrefOffset = strlen($pdf);
        $maxObjectId = max(array_keys($objects));
        $pdf .= "xref\n0 " . ($maxObjectId + 1) . "\n";
        $pdf .= "0000000000 65535 f \n";
        for ($i = 1; $i <= $maxObjectId; $i++) {
            $offset = $offsets[$i] ?? 0;
            $pdf .= sprintf('%010d 00000 n ', $offset) . "\n";
        }

        $pdf .= "trailer\n<< /Size " . ($maxObjectId + 1) . " /Root 1 0 R >>\n";
        $pdf .= "startxref\n{$xrefOffset}\n%%EOF";

        return $pdf;
    }

    private function drawPdfText(string $text, float $x, float $top, int $fontSize, bool $bold = false): string
    {
        $safeText = $this->normalizePdfText($text);
        $fontKey = $bold ? 'F2' : 'F1';
        $y = 842.0 - $top - ($fontSize * 0.9);

        return "BT\n"
            . '/' . $fontKey . ' ' . $fontSize . " Tf\n"
            . '0.12 0.16 0.23 rg' . "\n"
            . '1 0 0 1 ' . $this->formatPdfNumber($x) . ' ' . $this->formatPdfNumber($y) . " Tm\n"
            . '(' . $this->escapePdfString($safeText) . ") Tj\n"
            . "ET\n";
    }

    private function drawPdfFooter(int $pageNumber): string
    {
        return $this->drawPdfText('Halaman ' . $pageNumber, 478, 810, 9);
    }

    private function wrapPdfText(string $text, int $maxChars): array
    {
        $text = trim(preg_replace('/\s+/', ' ', $text) ?? '');
        if ($text === '') {
            return [''];
        }

        return explode("\n", wordwrap($text, $maxChars, "\n", true));
    }

    private function formatLogDate(MaintenanceLog $log): string
    {
        return $log->date ? $log->date->format('d/m/Y') : '-';
    }

    private function formatPeriodLabel(?string $dateFrom = null, ?string $dateTo = null): string
    {
        if ($dateFrom && $dateTo) {
            return \Carbon\Carbon::parse($dateFrom)->format('d/m/Y')
                . ' s.d. '
                . \Carbon\Carbon::parse($dateTo)->format('d/m/Y');
        }

        if ($dateFrom) {
            return 'Mulai ' . \Carbon\Carbon::parse($dateFrom)->format('d/m/Y');
        }

        if ($dateTo) {
            return 'Sampai ' . \Carbon\Carbon::parse($dateTo)->format('d/m/Y');
        }

        return 'Semua tanggal';
    }

    private function formatAssetCategory(mixed $category): string
    {
        if (is_object($category) && method_exists($category, 'label')) {
            return (string) $category->label();
        }

        if ($category instanceof \BackedEnum) {
            return (string) $category->value;
        }

        return is_scalar($category) && filled($category) ? (string) $category : '-';
    }

    private function getDocumentationUrl(MaintenanceLog $log): string
    {
        $documentationPath = $log->maintenanceDocumentations->isNotEmpty()
            ? $log->maintenanceDocumentations[0]->document_path
            : null;

        return $documentationPath
            ? asset('storage/' . $documentationPath)
            : 'No Documentation';
    }

    private function formatPdfNumber(float $value): string
    {
        $formatted = number_format($value, 3, '.', '');
        $formatted = rtrim(rtrim($formatted, '0'), '.');

        return $formatted === '' ? '0' : $formatted;
    }

    private function escapePdfString(string $value): string
    {
        return str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $value);
    }

    private function normalizePdfText(string $value): string
    {
        $normalized = iconv('UTF-8', 'Windows-1252//TRANSLIT//IGNORE', $value);
        if ($normalized === false) {
            $normalized = preg_replace('/[^\x20-\x7E]/', '?', $value);
        }

        return (string) ($normalized ?? '');
    }
}
