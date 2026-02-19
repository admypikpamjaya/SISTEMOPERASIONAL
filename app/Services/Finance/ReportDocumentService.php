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
        $rows = $this->buildReportRows($report);
        $metaRows = [
            ['Periode', ': ' . $this->resolvePeriodLabel($report), 'Jenis', ': ' . $report->reportType],
            ['Disusun Oleh', ': ' . ($report->generatedByName ?? '-'), 'Generated At', ': ' . $report->generatedAt->format('Y-m-d H:i:s')],
        ];

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
            . '<w:background w:color="1F232A"/>'
            . '<w:body>'
            . $this->buildDocxParagraph('LABA DAN RUGI', [
                'bold' => true,
                'size' => 42,
                'color' => 'E8EDF3',
                'align' => 'center',
                'after' => 20,
            ])
            . $this->buildDocxParagraph('YPIK PAM JAYA', [
                'size' => 24,
                'color' => 'D0D7E1',
                'align' => 'center',
                'after' => 280,
            ])
            . $this->buildDocxMetaTable($metaRows)
            . $this->buildDocxParagraph('', ['after' => 120])
            . $this->buildDocxReportTable($rows)
            . $this->buildDocxParagraph('', ['after' => 720])
            . $this->buildDocxSignatureTable()
            . '<w:sectPr><w:pgSz w:w="11906" w:h="16838"/><w:pgMar w:top="900" w:right="900" w:bottom="1000" w:left="900" w:header="708" w:footer="708" w:gutter="0"/></w:sectPr>'
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

    /**
     * @param array<int, array<int, string>> $rows
     */
    private function buildDocxMetaTable(array $rows): string
    {
        $metaRowsXml = '';
        foreach ($rows as $row) {
            $metaRowsXml .= $this->buildDocxTableRow([
                $this->buildDocxTableCell((string) ($row[0] ?? ''), 1800, [
                    'bold' => true,
                    'color' => 'E8EDF3',
                    'borderColor' => null,
                ]),
                $this->buildDocxTableCell((string) ($row[1] ?? ''), 2800, [
                    'color' => 'D3DCE7',
                    'borderColor' => null,
                ]),
                $this->buildDocxTableCell((string) ($row[2] ?? ''), 1700, [
                    'bold' => true,
                    'color' => 'E8EDF3',
                    'borderColor' => null,
                ]),
                $this->buildDocxTableCell((string) ($row[3] ?? ''), 2900, [
                    'color' => 'D3DCE7',
                    'borderColor' => null,
                ]),
            ], 420);
        }

        return $this->buildDocxTable(
            9200,
            [1800, 2800, 1700, 2900],
            $metaRowsXml,
            true
        );
    }

    /**
     * @param array<int, array{type:string,label:string,code?:string,amount?:string}> $rows
     */
    private function buildDocxReportTable(array $rows): string
    {
        $reportRowsXml = $this->buildDocxTableRow([
            $this->buildDocxTableCell('Kode', 1800, [
                'bold' => true,
                'fill' => '2F3844',
                'color' => 'E8EDF3',
            ]),
            $this->buildDocxTableCell('Uraian', 4500, [
                'bold' => true,
                'fill' => '2F3844',
                'color' => 'E8EDF3',
            ]),
            $this->buildDocxTableCell('Nominal', 2900, [
                'bold' => true,
                'fill' => '2F3844',
                'color' => 'E8EDF3',
                'align' => 'right',
            ]),
        ], 460);

        foreach ($rows as $row) {
            $type = $row['type'];
            if ($type === 'section') {
                $reportRowsXml .= $this->buildDocxTableRow([
                    $this->buildDocxTableCell($row['label'], 9200, [
                        'gridSpan' => 3,
                        'bold' => true,
                        'fill' => '3A4350',
                        'color' => 'E8EDF3',
                    ]),
                ], 420);
                continue;
            }

            if ($type === 'item') {
                $reportRowsXml .= $this->buildDocxTableRow([
                    $this->buildDocxTableCell((string) ($row['code'] ?? '-'), 1800, [
                        'fill' => '252A33',
                    ]),
                    $this->buildDocxTableCell($row['label'], 4500, [
                        'fill' => '252A33',
                    ]),
                    $this->buildDocxTableCell((string) ($row['amount'] ?? '0,00'), 2900, [
                        'fill' => '252A33',
                        'align' => 'right',
                    ]),
                ], 380);
                continue;
            }

            if ($type === 'note') {
                $reportRowsXml .= $this->buildDocxTableRow([
                    $this->buildDocxTableCell($row['label'], 9200, [
                        'gridSpan' => 3,
                        'fill' => '252A33',
                        'color' => 'CAD3DE',
                    ]),
                ], 380);
                continue;
            }

            if ($type === 'surplus') {
                $reportRowsXml .= $this->buildDocxTableRow([
                    $this->buildDocxTableCell($row['label'], 6300, [
                        'gridSpan' => 2,
                        'bold' => true,
                        'fill' => '005E2A',
                        'color' => 'E8F8EE',
                    ]),
                    $this->buildDocxTableCell((string) ($row['amount'] ?? '0,00'), 2900, [
                        'bold' => true,
                        'fill' => '005E2A',
                        'color' => 'E8F8EE',
                        'align' => 'right',
                    ]),
                ], 430);
                continue;
            }

            $reportRowsXml .= $this->buildDocxTableRow([
                $this->buildDocxTableCell($row['label'], 6300, [
                    'gridSpan' => 2,
                    'bold' => true,
                    'fill' => '2A303A',
                ]),
                $this->buildDocxTableCell((string) ($row['amount'] ?? '0,00'), 2900, [
                    'bold' => true,
                    'fill' => '2A303A',
                    'align' => 'right',
                ]),
            ], 420);
        }

        return $this->buildDocxTable(
            9200,
            [1800, 4500, 2900],
            $reportRowsXml,
            false
        );
    }

    private function buildDocxSignatureTable(): string
    {
        $signatureRowsXml = $this->buildDocxTableRow([
            $this->buildDocxTableCell('Diperiksa,', 4600, [
                'align' => 'center',
                'color' => 'D8DFE8',
                'borderColor' => null,
            ]),
            $this->buildDocxTableCell('Mengetahui,', 4600, [
                'align' => 'center',
                'color' => 'D8DFE8',
                'borderColor' => null,
            ]),
        ], 420);

        return $this->buildDocxTable(
            9200,
            [4600, 4600],
            $signatureRowsXml,
            true
        );
    }

    /**
     * @param array<int, int> $gridWidths
     */
    private function buildDocxTable(int $width, array $gridWidths, string $rowsXml, bool $borderless): string
    {
        $grid = '';
        foreach ($gridWidths as $gridWidth) {
            $grid .= '<w:gridCol w:w="' . $gridWidth . '"/>';
        }

        $tableBorders = '<w:tblBorders>'
            . ($borderless
                ? '<w:top w:val="nil"/><w:left w:val="nil"/><w:bottom w:val="nil"/><w:right w:val="nil"/><w:insideH w:val="nil"/><w:insideV w:val="nil"/>'
                : '<w:top w:val="single" w:sz="6" w:space="0" w:color="4D596E"/><w:left w:val="single" w:sz="6" w:space="0" w:color="4D596E"/><w:bottom w:val="single" w:sz="6" w:space="0" w:color="4D596E"/><w:right w:val="single" w:sz="6" w:space="0" w:color="4D596E"/><w:insideH w:val="single" w:sz="6" w:space="0" w:color="4D596E"/><w:insideV w:val="single" w:sz="6" w:space="0" w:color="4D596E"/>')
            . '</w:tblBorders>';

        return '<w:tbl>'
            . '<w:tblPr>'
            . '<w:tblW w:w="' . $width . '" w:type="dxa"/>'
            . '<w:jc w:val="center"/>'
            . $tableBorders
            . '<w:tblCellMar><w:top w:w="40" w:type="dxa"/><w:left w:w="90" w:type="dxa"/><w:bottom w:w="40" w:type="dxa"/><w:right w:w="90" w:type="dxa"/></w:tblCellMar>'
            . '</w:tblPr>'
            . '<w:tblGrid>' . $grid . '</w:tblGrid>'
            . $rowsXml
            . '</w:tbl>';
    }

    /**
     * @param array<int, string> $cells
     */
    private function buildDocxTableRow(array $cells, int $height): string
    {
        return '<w:tr><w:trPr><w:trHeight w:val="' . $height . '" w:hRule="atLeast"/></w:trPr>'
            . implode('', $cells)
            . '</w:tr>';
    }

    /**
     * @param array{
     *   align?:string,
     *   bold?:bool,
     *   color?:string,
     *   fill?:string|null,
     *   gridSpan?:int,
     *   size?:int,
     *   borderColor?:string|null
     * } $options
     */
    private function buildDocxTableCell(string $text, int $width, array $options = []): string
    {
        $align = $options['align'] ?? 'left';
        $bold = (bool) ($options['bold'] ?? false);
        $color = strtoupper((string) ($options['color'] ?? 'E5EAF1'));
        $fill = $options['fill'] ?? null;
        $gridSpan = (int) ($options['gridSpan'] ?? 1);
        $size = (int) ($options['size'] ?? 22);
        $borderColor = $options['borderColor'] ?? '4D596E';

        $tcPr = '<w:tcW w:w="' . $width . '" w:type="dxa"/>';
        if ($gridSpan > 1) {
            $tcPr .= '<w:gridSpan w:val="' . $gridSpan . '"/>';
        }
        if ($fill !== null) {
            $tcPr .= '<w:shd w:val="clear" w:color="auto" w:fill="' . strtoupper((string) $fill) . '"/>';
        }
        if ($borderColor !== null) {
            $tcPr .= '<w:tcBorders>'
                . '<w:top w:val="single" w:sz="6" w:space="0" w:color="' . $borderColor . '"/>'
                . '<w:left w:val="single" w:sz="6" w:space="0" w:color="' . $borderColor . '"/>'
                . '<w:bottom w:val="single" w:sz="6" w:space="0" w:color="' . $borderColor . '"/>'
                . '<w:right w:val="single" w:sz="6" w:space="0" w:color="' . $borderColor . '"/>'
                . '</w:tcBorders>';
        }
        $tcPr .= '<w:vAlign w:val="center"/>';

        $runProps = '<w:rFonts w:ascii="Calibri" w:hAnsi="Calibri"/>'
            . ($bold ? '<w:b/>' : '')
            . '<w:color w:val="' . $color . '"/>'
            . '<w:sz w:val="' . $size . '"/>'
            . '<w:szCs w:val="' . $size . '"/>';

        $safeText = $text === '' ? ' ' : $text;

        return '<w:tc><w:tcPr>' . $tcPr . '</w:tcPr>'
            . '<w:p><w:pPr><w:jc w:val="' . $align . '"/><w:spacing w:before="0" w:after="0" w:lineRule="auto"/></w:pPr>'
            . '<w:r><w:rPr>' . $runProps . '</w:rPr><w:t xml:space="preserve">' . $this->escapeXmlText($safeText) . '</w:t></w:r>'
            . '</w:p>'
            . '</w:tc>';
    }

    /**
     * @param array{
     *   align?:string,
     *   bold?:bool,
     *   color?:string,
     *   size?:int,
     *   before?:int,
     *   after?:int
     * } $options
     */
    private function buildDocxParagraph(string $text, array $options = []): string
    {
        $align = $options['align'] ?? 'left';
        $bold = (bool) ($options['bold'] ?? false);
        $color = strtoupper((string) ($options['color'] ?? 'E5EAF1'));
        $size = (int) ($options['size'] ?? 22);
        $before = (int) ($options['before'] ?? 0);
        $after = (int) ($options['after'] ?? 120);
        $safeText = $text === '' ? ' ' : $text;

        return '<w:p>'
            . '<w:pPr><w:jc w:val="' . $align . '"/><w:spacing w:before="' . $before . '" w:after="' . $after . '"/></w:pPr>'
            . '<w:r><w:rPr>'
            . '<w:rFonts w:ascii="Calibri" w:hAnsi="Calibri"/>'
            . ($bold ? '<w:b/>' : '')
            . '<w:color w:val="' . $color . '"/>'
            . '<w:sz w:val="' . $size . '"/>'
            . '<w:szCs w:val="' . $size . '"/>'
            . '</w:rPr><w:t xml:space="preserve">' . $this->escapeXmlText($safeText) . '</w:t></w:r>'
            . '</w:p>';
    }

    /**
     * @return array<int, array{type:string,label:string,code?:string,amount?:string}>
     */
    private function buildReportRows(ProfitLossReportDetailDTO $report): array
    {
        $rows = [];

        $this->appendReportSectionRows(
            $rows,
            'Penghasilan',
            $report->incomeLines,
            'Tidak ada item penghasilan.'
        );
        $rows[] = [
            'type' => 'total',
            'label' => 'Total Penghasilan',
            'amount' => $this->formatNominal($report->totalIncome),
        ];

        $this->appendReportSectionRows(
            $rows,
            'Pengeluaran',
            $report->expenseLines,
            'Tidak ada item pengeluaran.'
        );
        $rows[] = [
            'type' => 'total',
            'label' => 'Total Pengeluaran (non-penyusutan)',
            'amount' => $this->formatNominal($report->totalExpense),
        ];

        $this->appendReportSectionRows(
            $rows,
            'Penyusutan',
            $report->depreciationLines,
            'Tidak ada item penyusutan.'
        );
        $rows[] = [
            'type' => 'total',
            'label' => 'Total Penyusutan',
            'amount' => $this->formatNominal($report->totalDepreciation),
        ];
        $rows[] = [
            'type' => 'surplus',
            'label' => 'Surplus (Defisit)',
            'amount' => $this->formatNominal($report->surplusDeficit),
        ];

        return $rows;
    }

    /**
     * @param array<int, array{type:string,label:string,code?:string,amount?:string}> $rows
     * @param array<int, ProfitLossLineDTO> $lines
     */
    private function appendReportSectionRows(array &$rows, string $sectionTitle, array $lines, string $emptyMessage): void
    {
        $rows[] = [
            'type' => 'section',
            'label' => $sectionTitle,
        ];

        if (count($lines) === 0) {
            $rows[] = [
                'type' => 'note',
                'label' => $emptyMessage,
            ];
            return;
        }

        foreach ($lines as $line) {
            if (!$line instanceof ProfitLossLineDTO) {
                continue;
            }

            $rows[] = [
                'type' => 'item',
                'code' => $line->lineCode,
                'label' => $this->composeLineLabel($line),
                'amount' => $this->formatNominal($line->amount),
            ];
        }
    }

    private function composeLineLabel(ProfitLossLineDTO $line): string
    {
        $invoiceNumber = $line->invoiceNumber !== null ? trim($line->invoiceNumber) : '';
        if ($invoiceNumber === '') {
            return $line->lineLabel;
        }

        return $line->lineLabel . ' (Faktur: ' . $invoiceNumber . ')';
    }

    private function composeLineDescription(ProfitLossLineDTO $line): string
    {
        $description = $line->description !== null ? trim($line->description) : '';
        $description = $description === '' ? '-' : $description;
        $invoiceNumber = $line->invoiceNumber !== null ? trim($line->invoiceNumber) : '';

        if ($invoiceNumber === '') {
            return $description;
        }

        if ($description === '-') {
            return 'Faktur: ' . $invoiceNumber;
        }

        return 'Faktur: ' . $invoiceNumber . ' | ' . $description;
    }

    private function formatNominal(float $amount): string
    {
        return number_format($amount, 2, ',', '.');
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
                $this->composeLineDescription($line),
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

        return $this->buildStyledPdfDocument($report);
    }

    /**
     * @return array<int, array{content:string,rowTop:float}>
     */
    private function buildStyledPdfPages(ProfitLossReportDetailDTO $report): array
    {
        $rows = $this->buildReportRows($report);
        $pages = [];

        $rowIndex = 0;
        $isFirstPage = true;
        while ($rowIndex < count($rows)) {
            $pageData = $this->buildStyledPdfPage($report, $rows, $rowIndex, $isFirstPage);
            $pages[] = [
                'content' => $pageData['content'],
                'rowTop' => $pageData['rowTop'],
            ];
            $rowIndex = $pageData['nextRowIndex'];
            $isFirstPage = false;
        }

        if (count($pages) === 0) {
            $shell = $this->buildPdfPageShell($report, true);
            $pages[] = [
                'content' => $shell['content'],
                'rowTop' => $shell['rowTop'],
            ];
        }

        $lastIndex = count($pages) - 1;
        $signatureTop = max($pages[$lastIndex]['rowTop'] + 62, 742.0);
        if ($signatureTop + 24 > 792.0) {
            $shell = $this->buildPdfPageShell($report, false);
            $pages[] = [
                'content' => $shell['content'] . $this->buildPdfSignature(742.0),
                'rowTop' => 742.0,
            ];
        } else {
            $pages[$lastIndex]['content'] .= $this->buildPdfSignature($signatureTop);
        }

        return $pages;
    }

    private function buildStyledPdfDocument(ProfitLossReportDetailDTO $report): string
    {
        $pages = $this->buildStyledPdfPages($report);

        $objects = [];
        $objectIndex = 1;

        $fontRegularId = ++$objectIndex;
        $objects[$fontRegularId] = '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica /Encoding /WinAnsiEncoding >>';

        $fontBoldId = ++$objectIndex;
        $objects[$fontBoldId] = '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold /Encoding /WinAnsiEncoding >>';

        $pageObjectIds = [];
        foreach ($pages as $page) {
            $stream = $page['content'];
            $contentObjectId = ++$objectIndex;
            $objects[$contentObjectId] = "<< /Length " . strlen($stream) . " >>\nstream\n" . $stream . "\nendstream";

            $pageObjectId = ++$objectIndex;
            $pageObjectIds[] = $pageObjectId;
            $objects[$pageObjectId] = "<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Resources << /Font << /F1 {$fontRegularId} 0 R /F2 {$fontBoldId} 0 R >> >> /Contents {$contentObjectId} 0 R >>";
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
     * @param array<int, array{type:string,label:string,code?:string,amount?:string}> $rows
     * @return array{content:string,nextRowIndex:int,rowTop:float}
     */
    private function buildStyledPdfPage(
        ProfitLossReportDetailDTO $report,
        array $rows,
        int $startRowIndex,
        bool $isFirstPage
    ): array {
        $shell = $this->buildPdfPageShell($report, $isFirstPage);
        $content = $shell['content'];
        $rowTop = $shell['rowTop'];
        $rowIndex = $startRowIndex;

        $pageLimitTop = 758.0;
        while ($rowIndex < count($rows)) {
            $row = $rows[$rowIndex];
            $rowHeight = $this->resolvePdfRowHeight($row['type']);

            if ($rowTop + $rowHeight > $pageLimitTop && $rowIndex > $startRowIndex) {
                break;
            }

            $content .= $this->drawPdfTableRow($row, $rowTop, $rowHeight);
            $rowTop += $rowHeight;
            $rowIndex++;
        }

        return [
            'content' => $content,
            'nextRowIndex' => $rowIndex,
            'rowTop' => $rowTop,
        ];
    }

    /**
     * @return array{content:string,rowTop:float}
     */
    private function buildPdfPageShell(ProfitLossReportDetailDTO $report, bool $isFirstPage): array
    {
        $tableX = 50.0;
        $tableCodeWidth = 130.0;
        $tableLabelWidth = 210.0;
        $tableAmountWidth = 170.0;
        $tableWidth = $tableCodeWidth + $tableLabelWidth + $tableAmountWidth;

        $content = '';
        $content .= $this->pdfDrawRect(0, 0, 595, 842, [30, 31, 35], null);
        $content .= $this->pdfDrawRect(16, 14, 563, 814, [39, 41, 47], null);

        if ($isFirstPage) {
            $content .= $this->pdfDrawText('LABA DAN RUGI', $tableX, 76, 32, true, [230, 236, 244], 'center', $tableWidth);
            $content .= $this->pdfDrawText('YPIK PAM JAYA', $tableX, 109, 16, false, [206, 215, 228], 'center', $tableWidth);

            $metaLeftX = 62.0;
            $metaRightX = 305.0;
            $valueOffset = 114.0;

            $content .= $this->pdfDrawText('Periode', $metaLeftX, 150, 12, true, [225, 232, 240]);
            $content .= $this->pdfDrawText(': ' . $this->resolvePeriodLabel($report), $metaLeftX + $valueOffset, 150, 12, false, [211, 220, 230]);
            $content .= $this->pdfDrawText('Jenis', $metaRightX, 150, 12, true, [225, 232, 240]);
            $content .= $this->pdfDrawText(': ' . $report->reportType, $metaRightX + $valueOffset, 150, 12, false, [211, 220, 230]);

            $content .= $this->pdfDrawText('Disusun Oleh', $metaLeftX, 188, 12, true, [225, 232, 240]);
            $content .= $this->pdfDrawText(': ' . ($report->generatedByName ?? '-'), $metaLeftX + $valueOffset, 188, 12, false, [211, 220, 230]);
            $content .= $this->pdfDrawText('Generated At', $metaRightX, 188, 12, true, [225, 232, 240]);
            $content .= $this->pdfDrawText(': ' . $report->generatedAt->format('Y-m-d H:i:s'), $metaRightX + $valueOffset, 188, 12, false, [211, 220, 230]);
            $tableTop = 248.0;
        } else {
            $content .= $this->pdfDrawText('LABA DAN RUGI (lanjutan)', $tableX, 72, 18, true, [230, 236, 244], 'center', $tableWidth);
            $tableTop = 108.0;
        }

        $borderColor = [78, 92, 111];
        $content .= $this->pdfDrawRect($tableX, $tableTop, $tableCodeWidth, 34, [47, 56, 68], $borderColor);
        $content .= $this->pdfDrawRect($tableX + $tableCodeWidth, $tableTop, $tableLabelWidth, 34, [47, 56, 68], $borderColor);
        $content .= $this->pdfDrawRect($tableX + $tableCodeWidth + $tableLabelWidth, $tableTop, $tableAmountWidth, 34, [47, 56, 68], $borderColor);
        $content .= $this->pdfDrawText('Kode', $tableX + 10, $tableTop + 10, 12, true, [232, 238, 245]);
        $content .= $this->pdfDrawText('Uraian', $tableX + $tableCodeWidth + 10, $tableTop + 10, 12, true, [232, 238, 245]);
        $content .= $this->pdfDrawText('Nominal', $tableX + $tableCodeWidth + $tableLabelWidth + 10, $tableTop + 10, 12, true, [232, 238, 245], 'right', $tableAmountWidth - 20);

        return [
            'content' => $content,
            'rowTop' => $tableTop + 34.0,
        ];
    }

    /**
     * @param array{type:string,label:string,code?:string,amount?:string} $row
     */
    private function drawPdfTableRow(array $row, float $top, float $height): string
    {
        $tableX = 50.0;
        $tableCodeWidth = 130.0;
        $tableLabelWidth = 210.0;
        $tableAmountWidth = 170.0;
        $tableWidth = $tableCodeWidth + $tableLabelWidth + $tableAmountWidth;
        $borderColor = [78, 92, 111];

        $textTop = $top + (($height - 13.0) / 2);
        $type = $row['type'];

        if ($type === 'section') {
            return $this->pdfDrawRect($tableX, $top, $tableWidth, $height, [59, 68, 82], $borderColor)
                . $this->pdfDrawText($row['label'], $tableX + 10, $textTop, 13, true, [231, 238, 245]);
        }

        if ($type === 'item') {
            $label = $this->truncatePdfText($row['label'], $tableLabelWidth - 16, 12);
            $amount = (string) ($row['amount'] ?? '0,00');

            return $this->pdfDrawRect($tableX, $top, $tableCodeWidth, $height, [38, 43, 52], $borderColor)
                . $this->pdfDrawRect($tableX + $tableCodeWidth, $top, $tableLabelWidth, $height, [38, 43, 52], $borderColor)
                . $this->pdfDrawRect($tableX + $tableCodeWidth + $tableLabelWidth, $top, $tableAmountWidth, $height, [38, 43, 52], $borderColor)
                . $this->pdfDrawText((string) ($row['code'] ?? '-'), $tableX + 10, $textTop, 12, false, [222, 229, 237])
                . $this->pdfDrawText($label, $tableX + $tableCodeWidth + 10, $textTop, 12, false, [222, 229, 237])
                . $this->pdfDrawText($amount, $tableX + $tableCodeWidth + $tableLabelWidth + 10, $textTop, 12, false, [222, 229, 237], 'right', $tableAmountWidth - 20);
        }

        if ($type === 'note') {
            return $this->pdfDrawRect($tableX, $top, $tableWidth, $height, [38, 43, 52], $borderColor)
                . $this->pdfDrawText($row['label'], $tableX + 10, $textTop, 12, false, [204, 214, 225]);
        }

        if ($type === 'surplus') {
            $amount = (string) ($row['amount'] ?? '0,00');
            return $this->pdfDrawRect($tableX, $top, $tableCodeWidth + $tableLabelWidth, $height, [0, 94, 42], $borderColor)
                . $this->pdfDrawRect($tableX + $tableCodeWidth + $tableLabelWidth, $top, $tableAmountWidth, $height, [0, 94, 42], $borderColor)
                . $this->pdfDrawText($row['label'], $tableX + 10, $textTop, 13, true, [231, 248, 238])
                . $this->pdfDrawText($amount, $tableX + $tableCodeWidth + $tableLabelWidth + 10, $textTop, 13, true, [231, 248, 238], 'right', $tableAmountWidth - 20);
        }

        $amount = (string) ($row['amount'] ?? '0,00');
        return $this->pdfDrawRect($tableX, $top, $tableCodeWidth + $tableLabelWidth, $height, [43, 49, 58], $borderColor)
            . $this->pdfDrawRect($tableX + $tableCodeWidth + $tableLabelWidth, $top, $tableAmountWidth, $height, [43, 49, 58], $borderColor)
            . $this->pdfDrawText($row['label'], $tableX + 10, $textTop, 13, true, [229, 236, 243])
            . $this->pdfDrawText($amount, $tableX + $tableCodeWidth + $tableLabelWidth + 10, $textTop, 13, true, [229, 236, 243], 'right', $tableAmountWidth - 20);
    }

    private function resolvePdfRowHeight(string $rowType): float
    {
        return match ($rowType) {
            'section' => 32.0,
            'item' => 34.0,
            'note' => 34.0,
            'surplus' => 38.0,
            default => 35.0,
        };
    }

    private function buildPdfSignature(float $top): string
    {
        $tableX = 50.0;
        $tableWidth = 510.0;
        $halfWidth = $tableWidth / 2;

        return $this->pdfDrawText('Diperiksa,', $tableX, $top, 14, false, [223, 231, 240], 'center', $halfWidth)
            . $this->pdfDrawText('Mengetahui,', $tableX + $halfWidth, $top, 14, false, [223, 231, 240], 'center', $halfWidth);
    }

    /**
     * @param array{0:int,1:int,2:int}|null $fillRgb
     * @param array{0:int,1:int,2:int}|null $strokeRgb
     */
    private function pdfDrawRect(float $x, float $top, float $width, float $height, ?array $fillRgb, ?array $strokeRgb): string
    {
        $y = 842.0 - $top - $height;
        $commands = [];

        if ($fillRgb !== null) {
            $commands[] = $this->pdfRgb($fillRgb, false);
        }
        if ($strokeRgb !== null) {
            $commands[] = $this->pdfRgb($strokeRgb, true);
            $commands[] = '0.8 w';
        }

        $operator = $fillRgb !== null && $strokeRgb !== null
            ? 'B'
            : ($fillRgb !== null ? 'f' : 'S');

        $commands[] = $this->formatPdfNumber($x) . ' '
            . $this->formatPdfNumber($y) . ' '
            . $this->formatPdfNumber($width) . ' '
            . $this->formatPdfNumber($height) . ' re ' . $operator;

        return implode("\n", $commands) . "\n";
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
        array $rgb,
        string $align = 'left',
        ?float $maxWidth = null
    ): string {
        $safeText = $this->normalizePdfText($text);
        if ($maxWidth !== null) {
            $textWidth = $this->estimatePdfTextWidth($safeText, $fontSize);
            if ($align === 'right') {
                $x += max(0.0, $maxWidth - $textWidth);
            } elseif ($align === 'center') {
                $x += max(0.0, ($maxWidth - $textWidth) / 2);
            }
        }

        $y = 842.0 - $top - ($fontSize * 0.90);

        return "BT\n"
            . '/F' . ($bold ? '2' : '1') . ' ' . $fontSize . " Tf\n"
            . $this->pdfRgb($rgb, false) . "\n"
            . '1 0 0 1 ' . $this->formatPdfNumber($x) . ' ' . $this->formatPdfNumber($y) . " Tm\n"
            . '(' . $this->escapePdfString($safeText) . ") Tj\n"
            . "ET\n";
    }

    private function estimatePdfTextWidth(string $text, int $fontSize): float
    {
        return strlen($text) * ($fontSize * 0.52);
    }

    private function truncatePdfText(string $text, float $maxWidth, int $fontSize): string
    {
        $candidate = $text;
        $normalized = $this->normalizePdfText($candidate);
        if ($this->estimatePdfTextWidth($normalized, $fontSize) <= $maxWidth) {
            return $candidate;
        }

        while ($this->stringLength($candidate) > 1) {
            $candidate = rtrim($this->stringSlice($candidate, 0, $this->stringLength($candidate) - 1));
            $trial = $candidate . '...';
            if ($this->estimatePdfTextWidth($this->normalizePdfText($trial), $fontSize) <= $maxWidth) {
                return $trial;
            }
        }

        return '...';
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
