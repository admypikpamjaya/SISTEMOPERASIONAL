<?php

namespace App\Services\Finance;

use App\Models\FinanceInvoice;
use InvalidArgumentException;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class FinanceInvoiceDocumentService
{
    /**
     * @return array{content:string, filename:string, mime:string}
     */
    public function exportInvoice(FinanceInvoice $invoice, string $format): array
    {
        $normalizedFormat = strtolower($format);

        if (in_array($normalizedFormat, ['excel', 'xlsx'], true)) {
            return [
                'content' => $this->renderExcelDocument($invoice),
                'filename' => $this->buildFilename($invoice, 'xlsx'),
                'mime' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ];
        }

        if ($normalizedFormat === 'pdf') {
            return [
                'content' => $this->renderPdfDocument($invoice),
                'filename' => $this->buildFilename($invoice, 'pdf'),
                'mime' => 'application/pdf',
            ];
        }

        throw new InvalidArgumentException('Format dokumen faktur tidak didukung.');
    }

    private function renderExcelDocument(FinanceInvoice $invoice): string
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Faktur');

        $sheet->getColumnDimension('A')->setWidth(7);
        $sheet->getColumnDimension('B')->setWidth(17);
        $sheet->getColumnDimension('C')->setWidth(16);
        $sheet->getColumnDimension('D')->setWidth(17);
        $sheet->getColumnDimension('E')->setWidth(26);
        $sheet->getColumnDimension('F')->setWidth(22);
        $sheet->getColumnDimension('G')->setWidth(16);
        $sheet->getColumnDimension('H')->setWidth(16);

        $this->applySpreadsheetBrandHeader(
            $sheet,
            'H',
            'FAKTUR / ENTRI JURNAL',
            'Dokumen penarikan / jurnal Yayasan YPIK PAM JAYA'
        );

        $sheet->setCellValue('A6', 'Nomor Faktur');
        $sheet->setCellValue('B6', (string) $invoice->invoice_no);
        $sheet->setCellValue('A7', 'Tanggal Akuntansi');
        $sheet->setCellValue('B7', optional($invoice->accounting_date)->format('d/m/Y') ?? '-');
        $sheet->setCellValue('A8', 'Jenis');
        $sheet->setCellValue('B8', $this->resolveEntryTypeLabel((string) $invoice->entry_type));
        $sheet->setCellValue('A9', 'Jurnal');
        $sheet->setCellValue('B9', (string) $invoice->journal_name);
        $sheet->setCellValue('A10', 'Referensi');
        $sheet->setCellValue('B10', (string) ($invoice->reference ?? '-'));
        $sheet->setCellValue('A11', 'Status');
        $sheet->setCellValue('B11', (string) $invoice->status);

        $sheet->setCellValue('E6', 'Total Debit');
        $sheet->setCellValue('F6', (float) $invoice->total_debit);
        $sheet->setCellValue('E7', 'Total Kredit');
        $sheet->setCellValue('F7', (float) $invoice->total_credit);
        $sheet->setCellValue('E8', 'Dibuat Oleh');
        $sheet->setCellValue('F8', (string) ($invoice->creator?->name ?? '-'));
        $sheet->setCellValue('E9', 'Terekam Oleh');
        $sheet->setCellValue('F9', (string) ($invoice->poster?->name ?? '-'));
        $sheet->setCellValue('E10', 'Dibuat Pada');
        $sheet->setCellValue('F10', $invoice->created_at?->format('d/m/Y H:i:s') ?? '-');
        $sheet->setCellValue('E11', 'Diperbarui Pada');
        $sheet->setCellValue('F11', $invoice->updated_at?->format('d/m/Y H:i:s') ?? '-');

        $sheet->getStyle('A6:A11')->getFont()->setBold(true);
        $sheet->getStyle('E6:E11')->getFont()->setBold(true);
        $sheet->getStyle('F6:F7')->getNumberFormat()->setFormatCode('[$Rp-421] #,##0.00');
        $sheet->getStyle('F6:F7')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

        $headerRow = 14;
        $sheet->fromArray(
            ['No', 'Asset Category', 'Akun', 'Rekanan', 'Label', 'Analisa Distribusi', 'Debit', 'Kredit'],
            null,
            'A' . $headerRow
        );
        $sheet->getStyle('A' . $headerRow . ':H' . $headerRow)->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '1F4E78'],
            ],
        ]);

        $row = $headerRow + 1;
        if ($invoice->items->isEmpty()) {
            $sheet->mergeCells('A' . $row . ':H' . $row);
            $sheet->setCellValue('A' . $row, 'Belum ada item jurnal.');
            $sheet->getStyle('A' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $row++;
        } else {
            foreach ($invoice->items as $index => $item) {
                $sheet->setCellValue('A' . $row, $index + 1);
                $sheet->setCellValue('B' . $row, (string) ($item->asset_category ?? '-'));
                $sheet->setCellValue('C' . $row, (string) $item->account_code);
                $sheet->setCellValue('D' . $row, (string) ($item->partner_name ?? '-'));
                $sheet->setCellValue('E' . $row, (string) $item->label);
                $sheet->setCellValue('F' . $row, (string) ($item->analytic_distribution ?? '-'));
                $sheet->setCellValue('G' . $row, (float) $item->debit);
                $sheet->setCellValue('H' . $row, (float) $item->credit);
                $row++;
            }
        }

        $totalRow = $row;
        $sheet->mergeCells('A' . $totalRow . ':F' . $totalRow);
        $sheet->setCellValue('A' . $totalRow, 'TOTAL');
        $sheet->setCellValue('G' . $totalRow, (float) $invoice->total_debit);
        $sheet->setCellValue('H' . $totalRow, (float) $invoice->total_credit);
        $sheet->getStyle('A' . $totalRow . ':H' . $totalRow)->applyFromArray([
            'font' => ['bold' => true],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'EAF2FF'],
            ],
        ]);
        $sheet->getStyle('A' . $totalRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

        $sheet->getStyle('A' . $headerRow . ':H' . $totalRow)->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'D1D5DB'],
                ],
            ],
        ]);

        $currencyRangeStart = $headerRow + 1;
        $sheet->getStyle('G' . $currencyRangeStart . ':H' . $totalRow)
            ->getNumberFormat()
            ->setFormatCode('[$Rp-421] #,##0.00');
        $sheet->getStyle('G' . $currencyRangeStart . ':H' . $totalRow)
            ->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheet->getStyle('A' . $currencyRangeStart . ':F' . $totalRow)
            ->getAlignment()
            ->setVertical(Alignment::VERTICAL_TOP);

        $sheet->freezePane('A15');

        $notesSheet = $spreadsheet->createSheet();
        $notesSheet->setTitle('Log Catatan');
        $notesSheet->getColumnDimension('A')->setWidth(7);
        $notesSheet->getColumnDimension('B')->setWidth(22);
        $notesSheet->getColumnDimension('C')->setWidth(24);
        $notesSheet->getColumnDimension('D')->setWidth(18);
        $notesSheet->getColumnDimension('E')->setWidth(70);

        $this->applySpreadsheetBrandHeader(
            $notesSheet,
            'E',
            'LOG CATATAN FAKTUR',
            'Riwayat tindak lanjut finance Yayasan YPIK PAM JAYA',
            false
        );

        $notesHeaderRow = 6;
        $notesSheet->fromArray(['No', 'Waktu', 'Nama', 'Role', 'Catatan'], null, 'A' . $notesHeaderRow);
        $notesSheet->getStyle('A' . $notesHeaderRow . ':E' . $notesHeaderRow)->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '1F4E78'],
            ],
        ]);

        $notesRow = $notesHeaderRow + 1;
        if ($invoice->notes->isEmpty()) {
            $notesSheet->mergeCells('A' . $notesRow . ':E' . $notesRow);
            $notesSheet->setCellValue('A' . $notesRow, 'Belum ada catatan pada faktur ini.');
            $notesSheet->getStyle('A' . $notesRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $notesRow++;
        } else {
            foreach ($invoice->notes as $index => $note) {
                $notesSheet->setCellValue('A' . $notesRow, $index + 1);
                $notesSheet->setCellValue('B' . $notesRow, $note->created_at?->format('d/m/Y H:i:s') ?? '-');
                $notesSheet->setCellValue('C' . $notesRow, (string) ($note->user?->name ?? 'System'));
                $notesSheet->setCellValue('D' . $notesRow, (string) ($note->user?->role ?? '-'));
                $notesSheet->setCellValue('E' . $notesRow, (string) $note->note);
                $notesRow++;
            }
        }

        $notesLastDataRow = max($notesHeaderRow + 1, $notesRow - 1);
        $notesSheet->getStyle('A' . $notesHeaderRow . ':E' . $notesLastDataRow)->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'D1D5DB'],
                ],
            ],
        ]);
        $notesSheet->getStyle('E' . ($notesHeaderRow + 1) . ':E' . $notesLastDataRow)
            ->getAlignment()
            ->setWrapText(true);
        $notesSheet->freezePane('A7');

        $spreadsheet->setActiveSheetIndex(0);

        $writer = new Xlsx($spreadsheet);
        ob_start();
        $writer->save('php://output');
        return (string) ob_get_clean();
    }

    private function renderPdfDocument(FinanceInvoice $invoice): string
    {
        $totalItems = $invoice->items->count();
        $itemIndex = 0;
        $pageNumber = 1;
        $pages = [];
        $logoImage = $this->loadPdfFoundationLogo();

        while ($itemIndex < $totalItems || ($pageNumber === 1 && $totalItems === 0)) {
            $isFirstPage = $pageNumber === 1;
            $content = '';
            $top = $this->appendPdfHeader($content, $invoice, $isFirstPage, $pageNumber, $logoImage !== null);

            $maxRows = $isFirstPage ? 25 : 38;
            $rowsWritten = 0;

            if ($totalItems === 0) {
                $this->appendPdfLine($content, '(Tidak ada item jurnal)', $top, false);
            } else {
                while ($itemIndex < $totalItems && $rowsWritten < $maxRows) {
                    $item = $invoice->items[$itemIndex];
                    $this->appendPdfLine(
                        $content,
                        $this->buildPdfItemLine($itemIndex + 1, [
                            'asset_category' => (string) ($item->asset_category ?? '-'),
                            'account_code' => (string) $item->account_code,
                            'partner_name' => (string) ($item->partner_name ?? '-'),
                            'label' => (string) $item->label,
                            'analytic_distribution' => (string) ($item->analytic_distribution ?? '-'),
                            'debit' => (float) $item->debit,
                            'credit' => (float) $item->credit,
                        ]),
                        $top,
                        false
                    );
                    $rowsWritten++;
                    $itemIndex++;
                }
            }

            if ($itemIndex >= $totalItems) {
                $this->appendPdfLine($content, str_repeat('-', 91), $top, false);
                $this->appendPdfLine(
                    $content,
                    'TOTAL DEBIT  : Rp ' . $this->formatNominal((float) $invoice->total_debit),
                    $top,
                    true,
                    10,
                    13.0
                );
                $this->appendPdfLine(
                    $content,
                    'TOTAL KREDIT : Rp ' . $this->formatNominal((float) $invoice->total_credit),
                    $top,
                    true,
                    10,
                    13.0
                );

                $difference = round((float) $invoice->total_debit - (float) $invoice->total_credit, 2);
                $balanceLabel = abs($difference) < 0.01 ? 'SEIMBANG' : 'TIDAK SEIMBANG';
                $this->appendPdfLine(
                    $content,
                    'SELISIH      : Rp ' . $this->formatNominal(abs($difference)) . ' (' . $balanceLabel . ')',
                    $top,
                    true,
                    10,
                    13.0
                );

                $this->appendPdfLine(
                    $content,
                    'Jumlah log catatan: ' . $invoice->notes->count(),
                    $top,
                    false,
                    9
                );
            }

            $content .= $this->pdfDrawText(
                'Halaman ' . $pageNumber,
                505.0,
                815.0,
                8,
                false,
                [120, 130, 145]
            );

            $pages[] = $content;
            $pageNumber++;

            if ($totalItems === 0) {
                break;
            }
        }

        return $this->buildPdfDocument($pages, $logoImage);
    }

    private function appendPdfHeader(
        string &$content,
        FinanceInvoice $invoice,
        bool $isFirstPage,
        int $pageNumber,
        bool $withLogo
    ): float
    {
        if ($isFirstPage) {
            if ($withLogo) {
                $content .= $this->pdfDrawImage('Im1', 28.0, 26.0, 44.0, 44.0);
            }

            $content .= $this->pdfDrawText('YAYASAN YPIK PAM JAYA', 84.0, 28.0, 16, true, [30, 64, 175]);
            $content .= $this->pdfDrawText('Sistem Operasional Yayasan', 84.0, 46.0, 10, false, [100, 116, 139]);
            $content .= $this->pdfDrawText('Format Penarikan / Entri Jurnal', 84.0, 60.0, 11, true, [22, 30, 45]);

            $top = 92.0;
            $this->appendPdfLine($content, 'FAKTUR / ENTRI JURNAL', $top, true, 15, 18.0);
            $this->appendPdfLine($content, 'Nomor Faktur : ' . (string) $invoice->invoice_no, $top, false);
            $this->appendPdfLine(
                $content,
                'Tanggal      : ' . (optional($invoice->accounting_date)->format('d/m/Y') ?? '-')
                    . ' | Jenis: ' . $this->resolveEntryTypeLabel((string) $invoice->entry_type),
                $top,
                false
            );
            $this->appendPdfLine(
                $content,
                'Jurnal       : ' . (string) $invoice->journal_name,
                $top,
                false
            );
            $this->appendPdfLine(
                $content,
                'Referensi    : ' . (string) ($invoice->reference ?? '-'),
                $top,
                false
            );
            $this->appendPdfLine(
                $content,
                'Status       : ' . (string) $invoice->status
                    . ' | Dibuat oleh: ' . (string) ($invoice->creator?->name ?? '-')
                    . ' | Terekam oleh: ' . (string) ($invoice->poster?->name ?? '-'),
                $top,
                false
            );
        } else {
            if ($withLogo) {
                $content .= $this->pdfDrawImage('Im1', 28.0, 22.0, 28.0, 28.0);
            }

            $content .= $this->pdfDrawText('YAYASAN YPIK PAM JAYA', 62.0, 24.0, 10, true, [30, 64, 175]);
            $content .= $this->pdfDrawText('FAKTUR / ENTRI JURNAL (LANJUTAN)', 62.0, 38.0, 12, true, [22, 30, 45]);

            $top = 60.0;
            $this->appendPdfLine(
                $content,
                'Nomor Faktur : ' . (string) $invoice->invoice_no . ' | Halaman: ' . $pageNumber,
                $top,
                false
            );
        }

        $this->appendPdfLine($content, str_repeat('=', 91), $top, false);
        $this->appendPdfLine($content, $this->buildPdfHeaderLine(), $top, true);
        $this->appendPdfLine($content, str_repeat('-', 91), $top, false);

        return $top;
    }

    private function appendPdfLine(
        string &$content,
        string $text,
        float &$top,
        bool $bold = false,
        int $fontSize = 9,
        float $lineHeight = 12.0
    ): void {
        $content .= $this->pdfDrawText($text, 28.0, $top, $fontSize, $bold, [22, 30, 45]);
        $top += $lineHeight;
    }

    private function buildPdfHeaderLine(): string
    {
        return implode(' ', [
            $this->padPdfText('No', 2),
            $this->padPdfText('AssetCat', 10),
            $this->padPdfText('Akun', 10),
            $this->padPdfText('Rekanan', 10),
            $this->padPdfText('Label', 18),
            $this->padPdfText('Analisa', 10),
            str_pad('Debit', 12, ' ', STR_PAD_LEFT),
            str_pad('Kredit', 12, ' ', STR_PAD_LEFT),
        ]);
    }

    /**
     * @param array{
     *   asset_category:string,
     *   account_code:string,
     *   partner_name:string,
     *   label:string,
     *   analytic_distribution:string,
     *   debit:float,
     *   credit:float
     * } $item
     */
    private function buildPdfItemLine(int $number, array $item): string
    {
        return implode(' ', [
            str_pad((string) $number, 2, ' ', STR_PAD_LEFT),
            $this->padPdfText($item['asset_category'], 10),
            $this->padPdfText($item['account_code'], 10),
            $this->padPdfText($item['partner_name'], 10),
            $this->padPdfText($item['label'], 18),
            $this->padPdfText($item['analytic_distribution'], 10),
            str_pad($this->formatNominal($item['debit']), 12, ' ', STR_PAD_LEFT),
            str_pad($this->formatNominal($item['credit']), 12, ' ', STR_PAD_LEFT),
        ]);
    }

    private function padPdfText(?string $text, int $width): string
    {
        $value = trim((string) ($text ?? ''));
        if ($value === '') {
            $value = '-';
        }

        $value = (string) preg_replace('/\s+/', ' ', $value);
        $length = $this->stringLength($value);

        if ($length > $width) {
            if ($width <= 3) {
                $value = $this->stringSlice($value, 0, $width);
            } else {
                $value = rtrim($this->stringSlice($value, 0, $width - 3)) . '...';
            }
        }

        $padding = $width - $this->stringLength($value);
        if ($padding > 0) {
            $value .= str_repeat(' ', $padding);
        }

        return $value;
    }

    /**
     * @param array<int, string> $pageStreams
     * @param array{data:string,width:int,height:int}|null $logoImage
     */
    private function buildPdfDocument(array $pageStreams, ?array $logoImage = null): string
    {
        $objects = [];
        $objectIndex = 2;

        $fontRegularId = ++$objectIndex;
        $objects[$fontRegularId] = '<< /Type /Font /Subtype /Type1 /BaseFont /Courier /Encoding /WinAnsiEncoding >>';

        $fontBoldId = ++$objectIndex;
        $objects[$fontBoldId] = '<< /Type /Font /Subtype /Type1 /BaseFont /Courier-Bold /Encoding /WinAnsiEncoding >>';

        $logoObjectId = null;
        if ($logoImage !== null) {
            $logoObjectId = ++$objectIndex;
            $objects[$logoObjectId] = "<< /Type /XObject /Subtype /Image /Width {$logoImage['width']} /Height {$logoImage['height']} /ColorSpace /DeviceRGB /BitsPerComponent 8 /Filter /DCTDecode /Length "
                . strlen($logoImage['data'])
                . " >>\nstream\n"
                . $logoImage['data']
                . "\nendstream";
        }

        $pageObjectIds = [];
        foreach ($pageStreams as $stream) {
            $contentObjectId = ++$objectIndex;
            $objects[$contentObjectId] = "<< /Length " . strlen($stream) . " >>\nstream\n" . $stream . "\nendstream";

            $pageObjectId = ++$objectIndex;
            $pageObjectIds[] = $pageObjectId;
            $xObjectResource = $logoObjectId !== null ? " /XObject << /Im1 {$logoObjectId} 0 R >>" : '';
            $objects[$pageObjectId] = "<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Resources << /Font << /F1 {$fontRegularId} 0 R /F2 {$fontBoldId} 0 R >>{$xObjectResource} >> /Contents {$contentObjectId} 0 R >>";
        }

        $kids = implode(' ', array_map(static fn (int $id) => $id . ' 0 R', $pageObjectIds));
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

    /**
     * @param array{0:int,1:int,2:int} $rgb
     */
    private function pdfDrawText(
        string $text,
        float $x,
        float $top,
        int $fontSize,
        bool $bold,
        array $rgb
    ): string {
        $safeText = $this->normalizePdfText($text);
        $y = 842.0 - $top - ($fontSize * 0.90);

        return "BT\n"
            . '/F' . ($bold ? '2' : '1') . ' ' . $fontSize . " Tf\n"
            . $this->pdfRgb($rgb, false) . "\n"
            . '1 0 0 1 ' . $this->formatPdfNumber($x) . ' ' . $this->formatPdfNumber($y) . " Tm\n"
            . '(' . $this->escapePdfString($safeText) . ") Tj\n"
            . "ET\n";
    }

    private function normalizePdfText(string $value): string
    {
        $normalized = iconv('UTF-8', 'Windows-1252//TRANSLIT//IGNORE', $value);
        if ($normalized === false) {
            $normalized = preg_replace('/[^\x20-\x7E]/', '?', $value);
        }

        return (string) ($normalized ?? '');
    }

    /**
     * @param array{0:int,1:int,2:int} $rgb
     */
    private function pdfRgb(array $rgb, bool $stroke): string
    {
        $r = $this->formatPdfNumber($rgb[0] / 255);
        $g = $this->formatPdfNumber($rgb[1] / 255);
        $b = $this->formatPdfNumber($rgb[2] / 255);

        return $r . ' ' . $g . ' ' . $b . ' ' . ($stroke ? 'RG' : 'rg');
    }

    private function formatPdfNumber(float $value): string
    {
        $formatted = number_format($value, 3, '.', '');
        $formatted = rtrim(rtrim($formatted, '0'), '.');
        return $formatted === '' ? '0' : $formatted;
    }

    private function escapePdfString(string $value): string
    {
        return str_replace(
            ['\\', '(', ')'],
            ['\\\\', '\\(', '\\)'],
            $value
        );
    }

    private function stringLength(string $value): int
    {
        return function_exists('mb_strlen')
            ? (int) mb_strlen($value, 'UTF-8')
            : strlen($value);
    }

    private function stringSlice(string $value, int $start, int $length): string
    {
        if (function_exists('mb_substr')) {
            return (string) mb_substr($value, $start, $length, 'UTF-8');
        }

        return substr($value, $start, $length);
    }

    private function applySpreadsheetBrandHeader(
        Worksheet $sheet,
        string $lastColumn,
        string $title,
        string $subtitle,
        bool $withLogo = true
    ): void {
        if ($withLogo && ($logoPath = $this->resolveFoundationLogoPath()) !== null) {
            $drawing = new Drawing();
            $drawing->setName('Logo YPIK');
            $drawing->setDescription('Logo YPIK');
            $drawing->setPath($logoPath);
            $drawing->setHeight(52);
            $drawing->setCoordinates('A1');
            $drawing->setWorksheet($sheet);
        }

        $sheet->mergeCells("B1:{$lastColumn}1");
        $sheet->mergeCells("B2:{$lastColumn}2");
        $sheet->mergeCells("B3:{$lastColumn}3");
        $sheet->mergeCells("B4:{$lastColumn}4");

        $sheet->setCellValue('B1', 'YAYASAN YPIK PAM JAYA');
        $sheet->setCellValue('B2', 'Sistem Operasional Yayasan');
        $sheet->setCellValue('B3', $title);
        $sheet->setCellValue('B4', $subtitle);

        $sheet->getStyle("B1:{$lastColumn}4")->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle("B1:{$lastColumn}4")->getAlignment()->setWrapText(true);
        $sheet->getStyle("B1:{$lastColumn}4")->getFont()->setName('Arial');
        $sheet->getStyle("B1:{$lastColumn}1")->applyFromArray([
            'font' => ['bold' => true, 'size' => 15, 'color' => ['rgb' => '1E3A8A']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
        ]);
        $sheet->getStyle("B2:{$lastColumn}2")->applyFromArray([
            'font' => ['bold' => false, 'size' => 10, 'color' => ['rgb' => '64748B']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
        ]);
        $sheet->getStyle("B3:{$lastColumn}3")->applyFromArray([
            'font' => ['bold' => true, 'size' => 13, 'color' => ['rgb' => 'FFFFFF']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '1E3A8A'],
            ],
        ]);
        $sheet->getStyle("B4:{$lastColumn}4")->applyFromArray([
            'font' => ['bold' => false, 'size' => 9, 'color' => ['rgb' => '475569']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
        ]);

        $sheet->getRowDimension(1)->setRowHeight(22);
        $sheet->getRowDimension(2)->setRowHeight(18);
        $sheet->getRowDimension(3)->setRowHeight(22);
        $sheet->getRowDimension(4)->setRowHeight(18);
    }

    private function resolveFoundationLogoPath(): ?string
    {
        $candidates = [
            public_path('images/logo_ypik.png'),
            public_path('images/logo_ypik.webp'),
        ];

        foreach ($candidates as $candidate) {
            if (is_file($candidate)) {
                return $candidate;
            }
        }

        return null;
    }

    /**
     * @return array{data:string,width:int,height:int}|null
     */
    private function loadPdfFoundationLogo(): ?array
    {
        $logoPath = $this->resolveFoundationLogoPath();
        if ($logoPath === null) {
            return null;
        }

        $extension = strtolower((string) pathinfo($logoPath, PATHINFO_EXTENSION));

        try {
            $image = match ($extension) {
                'png' => @imagecreatefrompng($logoPath),
                'webp' => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($logoPath) : false,
                'jpg', 'jpeg' => @imagecreatefromjpeg($logoPath),
                default => false,
            };

            if ($image === false) {
                return null;
            }

            ob_start();
            imagejpeg($image, null, 90);
            $jpegData = (string) ob_get_clean();

            $width = imagesx($image);
            $height = imagesy($image);
            imagedestroy($image);

            if ($jpegData === '' || $width <= 0 || $height <= 0) {
                return null;
            }

            return [
                'data' => $jpegData,
                'width' => $width,
                'height' => $height,
            ];
        } catch (\Throwable) {
            return null;
        }
    }

    private function pdfDrawImage(string $resourceName, float $x, float $top, float $width, float $height): string
    {
        $y = 842.0 - $top - $height;

        return "q\n"
            . $this->formatPdfNumber($width) . " 0 0 " . $this->formatPdfNumber($height) . ' '
            . $this->formatPdfNumber($x) . ' ' . $this->formatPdfNumber($y) . " cm\n"
            . '/' . $resourceName . " Do\n"
            . "Q\n";
    }

    private function resolveEntryTypeLabel(string $entryType): string
    {
        return strtoupper($entryType) === 'INCOME' ? 'Pemasukan' : 'Pengeluaran';
    }

    private function formatNominal(float $amount): string
    {
        return number_format($amount, 2, ',', '.');
    }

    private function buildFilename(FinanceInvoice $invoice, string $extension): string
    {
        $base = (string) ($invoice->invoice_no ?: 'faktur');
        $sanitized = (string) preg_replace('/[^A-Za-z0-9._-]/', '-', $base);
        $sanitized = trim($sanitized, '-_.');

        if ($sanitized === '') {
            $sanitized = 'faktur';
        }

        return 'faktur-' . strtolower($sanitized) . '.' . strtolower($extension);
    }
}
