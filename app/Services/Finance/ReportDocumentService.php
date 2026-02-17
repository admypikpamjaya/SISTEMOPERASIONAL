<?php

namespace App\Services\Finance;

use App\DTOs\Finance\ProfitLossLineDTO;
use App\DTOs\Finance\ProfitLossReportDetailDTO;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ReportDocumentService
{
    /**
     * @return array{content:string,filename:string,mime:string}
     */
    public function exportProfitLoss(ProfitLossReportDetailDTO $report, string $format): array
    {
        $normalizedFormat = strtolower($format);

        return match ($normalizedFormat) {
            'docx' => [
                'content' => $this->renderDocxDocument($report),
                'filename' => $this->buildProfitLossFilename($report, 'docx'),
                'mime' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            ],
            'excel' => [
                'content' => $this->renderExcelDocument($report),
                'filename' => $this->buildProfitLossFilename($report, 'xlsx'),
                'mime' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ],
            'pdf' => [
                'content' => $this->renderPdfDocument($report),
                'filename' => $this->buildProfitLossFilename($report, 'pdf'),
                'mime' => 'application/pdf',
            ],
            default => [
                'content' => $this->renderDocxDocument($report),
                'filename' => $this->buildProfitLossFilename($report, 'docx'),
                'mime' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            ],
        };
    }

    public function renderProfitLossDocument(ProfitLossReportDetailDTO $report): string
    {
        return $this->renderWordDocument($report);
    }

    public function buildProfitLossFilename(ProfitLossReportDetailDTO $report, string $extension = 'docx'): string
    {
        $period = match ($report->reportType) {
            'DAILY' => ($report->periodDate ?? sprintf('%04d-%02d-%02d', $report->year, (int) ($report->month ?? 1), (int) ($report->day ?? 1))),
            'MONTHLY' => sprintf('%04d-%02d', $report->year, (int) ($report->month ?? 1)),
            default => (string) $report->year,
        };

        return sprintf('laporan-laba-rugi-%s.%s', $period, $extension);
    }

    private function renderWordDocument(ProfitLossReportDetailDTO $report): string
    {
        return view('finance.report-document', [
            'report' => $report,
        ])->render();
    }

    private function renderDocxDocument(ProfitLossReportDetailDTO $report): string
    {
        $lines = $this->buildPdfLines($report);
        $paragraphs = '';

        foreach ($lines as $line) {
            $paragraphs .= '<w:p><w:r><w:t xml:space="preserve">'
                . $this->escapeXmlText($line)
                . '</w:t></w:r></w:p>';
        }

        $documentXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<w:document xmlns:wpc="http://schemas.microsoft.com/office/word/2010/wordprocessingCanvas"'
            . ' xmlns:mc="http://schemas.openxmlformats.org/markup-compatibility/2006"'
            . ' xmlns:o="urn:schemas-microsoft-com:office:office"'
            . ' xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"'
            . ' xmlns:m="http://schemas.openxmlformats.org/officeDocument/2006/math"'
            . ' xmlns:v="urn:schemas-microsoft-com:vml"'
            . ' xmlns:wp14="http://schemas.microsoft.com/office/word/2010/wordprocessingDrawing"'
            . ' xmlns:wp="http://schemas.openxmlformats.org/drawingml/2006/wordprocessingDrawing"'
            . ' xmlns:w10="urn:schemas-microsoft-com:office:word"'
            . ' xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main"'
            . ' xmlns:w14="http://schemas.microsoft.com/office/word/2010/wordml"'
            . ' xmlns:wpg="http://schemas.microsoft.com/office/word/2010/wordprocessingGroup"'
            . ' xmlns:wpi="http://schemas.microsoft.com/office/word/2010/wordprocessingInk"'
            . ' xmlns:wne="http://schemas.microsoft.com/office/word/2006/wordml"'
            . ' xmlns:wps="http://schemas.microsoft.com/office/word/2010/wordprocessingShape"'
            . ' mc:Ignorable="w14 wp14">'
            . '<w:body>'
            . $paragraphs
            . '<w:sectPr><w:pgSz w:w="11906" w:h="16838"/><w:pgMar w:top="1440" w:right="1440" w:bottom="1440" w:left="1440" w:header="708" w:footer="708" w:gutter="0"/></w:sectPr>'
            . '</w:body></w:document>';

        $contentTypesXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
            . '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
            . '<Default Extension="xml" ContentType="application/xml"/>'
            . '<Override PartName="/word/document.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.document.main+xml"/>'
            . '<Override PartName="/docProps/core.xml" ContentType="application/vnd.openxmlformats-package.core-properties+xml"/>'
            . '<Override PartName="/docProps/app.xml" ContentType="application/vnd.openxmlformats-officedocument.extended-properties+xml"/>'
            . '</Types>';

        $relsXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="word/document.xml"/>'
            . '<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/package/2006/relationships/metadata/core-properties" Target="docProps/core.xml"/>'
            . '<Relationship Id="rId3" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/extended-properties" Target="docProps/app.xml"/>'
            . '</Relationships>';

        $documentRelsXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"></Relationships>';

        $generatedAt = gmdate('Y-m-d\\TH:i:s\\Z');
        $coreXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<cp:coreProperties xmlns:cp="http://schemas.openxmlformats.org/package/2006/metadata/core-properties"'
            . ' xmlns:dc="http://purl.org/dc/elements/1.1/"'
            . ' xmlns:dcterms="http://purl.org/dc/terms/"'
            . ' xmlns:dcmitype="http://purl.org/dc/dcmitype/"'
            . ' xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">'
            . '<dc:title>Laporan Laba Rugi</dc:title>'
            . '<dc:creator>SOY YPIK PAM JAYA</dc:creator>'
            . '<cp:lastModifiedBy>SOY YPIK PAM JAYA</cp:lastModifiedBy>'
            . '<dcterms:created xsi:type="dcterms:W3CDTF">' . $generatedAt . '</dcterms:created>'
            . '<dcterms:modified xsi:type="dcterms:W3CDTF">' . $generatedAt . '</dcterms:modified>'
            . '</cp:coreProperties>';

        $appXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Properties xmlns="http://schemas.openxmlformats.org/officeDocument/2006/extended-properties"'
            . ' xmlns:vt="http://schemas.openxmlformats.org/officeDocument/2006/docPropsVTypes">'
            . '<Application>SOY YPIK PAM JAYA</Application>'
            . '</Properties>';

        $tempFile = tempnam(sys_get_temp_dir(), 'finance-docx-');
        $zip = new \ZipArchive();
        $zip->open($tempFile, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);
        $zip->addFromString('[Content_Types].xml', $contentTypesXml);
        $zip->addFromString('_rels/.rels', $relsXml);
        $zip->addFromString('word/document.xml', $documentXml);
        $zip->addFromString('word/_rels/document.xml.rels', $documentRelsXml);
        $zip->addFromString('docProps/core.xml', $coreXml);
        $zip->addFromString('docProps/app.xml', $appXml);
        $zip->close();

        $binary = (string) file_get_contents($tempFile);
        @unlink($tempFile);

        return $binary;
    }

    private function renderExcelDocument(ProfitLossReportDetailDTO $report): string
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Laba Rugi');
        $sheet->getDefaultColumnDimension()->setWidth(20);
        $sheet->getColumnDimension('A')->setWidth(18);
        $sheet->getColumnDimension('B')->setWidth(40);
        $sheet->getColumnDimension('C')->setWidth(36);
        $sheet->getColumnDimension('D')->setWidth(22);

        $row = 1;
        $sheet->mergeCells('A1:D1');
        $sheet->setCellValue('A1', 'LAPORAN LABA RUGI');
        $sheet->getStyle('A1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 14],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(24);

        $sheet->setCellValue('A3', 'Periode');
        $sheet->mergeCells('B3:D3');
        $sheet->setCellValue('B3', $this->resolvePeriodLabel($report));
        $sheet->setCellValue('A4', 'Saldo Awal');
        $sheet->setCellValue('D4', $report->openingBalance);
        $sheet->setCellValue('A5', 'Saldo Akhir');
        $sheet->setCellValue('D5', $report->endingBalance);

        $sheet->getStyle('A3:A5')->getFont()->setBold(true);
        $sheet->getStyle('D4:D5')->getNumberFormat()->setFormatCode('[$Rp-421] #,##0.00');
        $sheet->getStyle('D4:D5')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

        $row = 7;
        $headerRow = $row;
        $sheet->fromArray(['Kode', 'Uraian', 'Keterangan', 'Nominal'], null, 'A' . $row);
        $sheet->getStyle('A' . $row . ':D' . $row)->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '1F4E78'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);
        $row++;

        $row = $this->appendSection($sheet, $row, 'PENGHASILAN', $report->incomeLines);
        $row = $this->appendTotalRow($sheet, $row, 'TOTAL PENGHASILAN', $report->totalIncome);
        $row++;

        $row = $this->appendSection($sheet, $row, 'PENGELUARAN', $report->expenseLines);
        $row = $this->appendTotalRow($sheet, $row, 'TOTAL PENGELUARAN (NON-PENYUSUTAN)', $report->totalExpense);
        $row++;

        $row = $this->appendSection($sheet, $row, 'PENYUSUTAN', $report->depreciationLines);
        $row = $this->appendTotalRow($sheet, $row, 'TOTAL PENYUSUTAN', $report->totalDepreciation);
        $row = $this->appendTotalRow($sheet, $row, 'SURPLUS (DEFISIT)', $report->surplusDeficit, 'E2F0D9');
        $row = $this->appendTotalRow($sheet, $row, 'SALDO AKHIR', $report->endingBalance, 'D9E1F2');

        $lastDataRow = $row - 1;
        $sheet->getStyle('A' . $headerRow . ':D' . $lastDataRow)->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'A6A6A6'],
                ],
            ],
        ]);

        $sheet->getStyle('D' . ($headerRow + 1) . ':D' . $lastDataRow)
            ->getNumberFormat()
            ->setFormatCode('[$Rp-421] #,##0.00');
        $sheet->getStyle('D' . ($headerRow + 1) . ':D' . $lastDataRow)
            ->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheet->getStyle('A' . ($headerRow + 1) . ':C' . $lastDataRow)
            ->getAlignment()
            ->setVertical(Alignment::VERTICAL_TOP);

        $sheet->freezePane('A8');

        $writer = new Xlsx($spreadsheet);
        ob_start();
        $writer->save('php://output');
        return (string) ob_get_clean();
    }

    private function appendSection(Worksheet $sheet, int $row, string $label, array $lines): int
    {
        $sheet->mergeCells('A' . $row . ':D' . $row);
        $sheet->setCellValue('A' . $row, $label);
        $sheet->getStyle('A' . $row . ':D' . $row)->applyFromArray([
            'font' => ['bold' => true],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'F2F2F2'],
            ],
        ]);
        $row++;

        if (empty($lines)) {
            $sheet->setCellValue('B' . $row, '-');
            $sheet->setCellValue('C' . $row, '-');
            $sheet->setCellValue('D' . $row, 0);
            return $row + 1;
        }

        foreach ($lines as $line) {
            if (!$line instanceof ProfitLossLineDTO) {
                continue;
            }

            $sheet->fromArray([
                $line->lineCode,
                $line->lineLabel,
                $line->description ?? '-',
                $line->amount,
            ], null, 'A' . $row);
            $row++;
        }

        return $row;
    }

    private function appendTotalRow(
        Worksheet $sheet,
        int $row,
        string $label,
        float $amount,
        string $fillColor = 'FFF2CC'
    ): int {
        $sheet->mergeCells('A' . $row . ':C' . $row);
        $sheet->setCellValue('A' . $row, $label);
        $sheet->setCellValue('D' . $row, $amount);
        $sheet->getStyle('A' . $row . ':D' . $row)->applyFromArray([
            'font' => ['bold' => true],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => $fillColor],
            ],
        ]);

        return $row + 1;
    }

    private function renderPdfDocument(ProfitLossReportDetailDTO $report): string
    {
        if (class_exists(\Dompdf\Dompdf::class)) {
            $dompdf = new \Dompdf\Dompdf();
            $dompdf->loadHtml($this->renderWordDocument($report), 'UTF-8');
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();
            return $dompdf->output();
        }

        $lines = $this->buildPdfLines($report);
        return $this->buildSimplePdf($lines);
    }

    /**
     * @return array<int, string>
     */
    private function buildPdfLines(ProfitLossReportDetailDTO $report): array
    {
        $lines = [
            'LAPORAN LABA RUGI',
            'Periode: ' . $this->resolvePeriodLabel($report),
            'Saldo Awal: Rp ' . number_format($report->openingBalance, 2, ',', '.'),
            'Saldo Akhir: Rp ' . number_format($report->endingBalance, 2, ',', '.'),
            '',
            'PENGHASILAN',
        ];

        foreach ($report->incomeLines as $line) {
            $lines[] = $line->lineCode . ' - ' . $line->lineLabel . ' : Rp ' . number_format($line->amount, 2, ',', '.');
        }

        $lines[] = 'Total Penghasilan: Rp ' . number_format($report->totalIncome, 2, ',', '.');
        $lines[] = '';
        $lines[] = 'PENGELUARAN';

        foreach ($report->expenseLines as $line) {
            $lines[] = $line->lineCode . ' - ' . $line->lineLabel . ' : Rp ' . number_format($line->amount, 2, ',', '.');
        }

        $lines[] = 'Total Pengeluaran: Rp ' . number_format($report->totalExpense, 2, ',', '.');
        $lines[] = '';
        $lines[] = 'PENYUSUTAN';

        foreach ($report->depreciationLines as $line) {
            $lines[] = $line->lineCode . ' - ' . $line->lineLabel . ' : Rp ' . number_format($line->amount, 2, ',', '.');
        }

        $lines[] = 'Total Penyusutan: Rp ' . number_format($report->totalDepreciation, 2, ',', '.');
        $lines[] = 'Surplus/Defisit: Rp ' . number_format($report->surplusDeficit, 2, ',', '.');
        $lines[] = 'Saldo Akhir: Rp ' . number_format($report->endingBalance, 2, ',', '.');

        return $lines;
    }

    /**
     * @param array<int, string> $lines
     */
    private function buildSimplePdf(array $lines): string
    {
        $maxLinesPerPage = 42;
        $chunks = array_chunk($lines, $maxLinesPerPage);

        $objects = [];
        $objectIndex = 1;

        $fontObjectId = ++$objectIndex;
        $objects[$fontObjectId] = "<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>";

        $pageObjectIds = [];
        $contentObjectIds = [];

        foreach ($chunks as $chunk) {
            $content = "BT\n/F1 10 Tf\n40 800 Td\n";
            foreach ($chunk as $index => $line) {
                if ($index > 0) {
                    $content .= "0 -16 Td\n";
                }
                $content .= '(' . $this->escapePdfString($line) . ") Tj\n";
            }
            $content .= "ET";

            $contentObjectId = ++$objectIndex;
            $contentObjectIds[] = $contentObjectId;
            $objects[$contentObjectId] = "<< /Length " . strlen($content) . " >>\nstream\n" . $content . "\nendstream";

            $pageObjectId = ++$objectIndex;
            $pageObjectIds[] = $pageObjectId;
            $objects[$pageObjectId] = "<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Resources << /Font << /F1 {$fontObjectId} 0 R >> >> /Contents {$contentObjectId} 0 R >>";
        }

        $kids = implode(' ', array_map(static fn ($id) => $id . ' 0 R', $pageObjectIds));
        $objects[2] = "<< /Type /Pages /Kids [ {$kids} ] /Count " . count($pageObjectIds) . " >>";
        $objects[1] = "<< /Type /Catalog /Pages 2 0 R >>";

        ksort($objects);

        $pdf = "%PDF-1.4\n";
        $offsets = [0];
        foreach ($objects as $id => $body) {
            $offsets[$id] = strlen($pdf);
            $pdf .= $id . " 0 obj\n" . $body . "\nendobj\n";
        }

        $xrefOffset = strlen($pdf);
        $pdf .= "xref\n0 " . (max(array_keys($objects)) + 1) . "\n";
        $pdf .= "0000000000 65535 f \n";
        for ($i = 1; $i <= max(array_keys($objects)); $i++) {
            $offset = $offsets[$i] ?? 0;
            $pdf .= sprintf('%010d 00000 n ', $offset) . "\n";
        }

        $pdf .= "trailer\n<< /Size " . (max(array_keys($objects)) + 1) . " /Root 1 0 R >>\n";
        $pdf .= "startxref\n{$xrefOffset}\n%%EOF";

        return $pdf;
    }

    private function escapePdfString(string $value): string
    {
        return str_replace(
            ['\\', '(', ')'],
            ['\\\\', '\\(', '\\)'],
            $value
        );
    }

    private function escapeXmlText(string $value): string
    {
        return htmlspecialchars($value, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }

    private function resolvePeriodLabel(ProfitLossReportDetailDTO $report): string
    {
        return match ($report->reportType) {
            'DAILY' => $report->periodDate ?? sprintf('%04d-%02d-%02d', $report->year, (int) ($report->month ?? 1), (int) ($report->day ?? 1)),
            'MONTHLY' => sprintf('%02d/%04d', (int) ($report->month ?? 1), $report->year),
            default => (string) $report->year,
        };
    }
}
