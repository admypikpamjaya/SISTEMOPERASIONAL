<?php

namespace App\Services\Finance;

use App\DTOs\Finance\StatementFilterDTO;
use Carbon\Carbon;

class FinancialStatementDocumentService
{
    /**
     * @return array{content:string,filename:string,mime:string}
     */
    public function exportBalanceSheet(array $report, StatementFilterDTO $filter): array
    {
        return [
            'content' => $this->renderPdfDocument(
                'Laporan Lembar Saldo',
                $this->buildSubtitle('Ringkasan liabilitas, piutang, kas, dan aset.', $filter),
                $this->buildBalanceSheetLines($report)
            ),
            'filename' => $this->buildFilename('laporan-lembar-saldo', $filter),
            'mime' => 'application/pdf',
        ];
    }

    /**
     * @return array{content:string,filename:string,mime:string}
     */
    public function exportProfitLoss(array $report, StatementFilterDTO $filter): array
    {
        return [
            'content' => $this->renderPdfDocument(
                'Laporan Laba Rugi',
                $this->buildSubtitle('Ringkasan pemasukan dan pengeluaran.', $filter),
                $this->buildProfitLossLines($report)
            ),
            'filename' => $this->buildFilename('laporan-laba-rugi', $filter),
            'mime' => 'application/pdf',
        ];
    }

    /**
     * @return array{content:string,filename:string,mime:string}
     */
    public function exportGeneralLedger(array $report, StatementFilterDTO $filter): array
    {
        return [
            'content' => $this->renderPdfDocument(
                'Buku Besar',
                $this->buildSubtitle('Rincian jurnal keseluruhan per akun.', $filter),
                $this->buildGeneralLedgerLines($report)
            ),
            'filename' => $this->buildFilename('buku-besar', $filter),
            'mime' => 'application/pdf',
        ];
    }

    /**
     * @return array<int, array<string, int|string|bool>>
     */
    private function buildBalanceSheetLines(array $report): array
    {
        $summary = $report['summary'] ?? [];
        $sections = $report['sections'] ?? [];
        $uncategorizedCount = (int) ($report['uncategorized_count'] ?? 0);
        $lines = [
            $this->sectionLine('Ringkasan'),
            $this->bodyLine('Liabilitas       : ' . $this->formatCurrency((float) ($summary['liabilitas_total'] ?? 0))),
            $this->bodyLine('Piutang          : ' . $this->formatCurrency((float) ($summary['piutang_total'] ?? 0))),
            $this->bodyLine('Kas              : ' . $this->formatCurrency((float) ($summary['kas_total'] ?? 0))),
            $this->bodyLine('Aset             : ' . $this->formatCurrency((float) ($summary['aset_total'] ?? 0))),
            $this->bodyLine('Total Sisi Aset  : ' . $this->formatCurrency((float) ($summary['asset_side_total'] ?? 0))),
            $this->bodyLine('Jumlah Akun      : ' . number_format((int) ($summary['account_count'] ?? 0), 0, ',', '.'), false, 0, 6),
        ];

        if ($uncategorizedCount > 0) {
            $lines[] = $this->bodyLine(
                'Catatan: ' . number_format($uncategorizedCount, 0, ',', '.')
                . ' akun belum masuk kategori lembar saldo.',
                true,
                0,
                6
            );
        }

        foreach ($sections as $section) {
            $sectionRows = $section['rows'] ?? [];
            $lines[] = $this->sectionLine(
                strtoupper((string) ($section['label'] ?? 'Kategori'))
                . ' | Total: ' . $this->formatCurrency((float) ($section['total'] ?? 0))
            );
            $lines[] = $this->monoLine(
                $this->formatColumns([
                    ['text' => 'Kode', 'width' => 12],
                    ['text' => 'Nama Akun', 'width' => 32],
                    ['text' => 'Tipe', 'width' => 18],
                    ['text' => 'Saldo', 'width' => 20, 'align' => 'right'],
                ]),
                true
            );

            if (empty($sectionRows)) {
                $lines[] = $this->bodyLine('Tidak ada data untuk kategori ini.', false, 2, 4);
                continue;
            }

            foreach ($sectionRows as $row) {
                $lines[] = $this->monoLine(
                    $this->formatColumns([
                        ['text' => (string) ($row['account_code'] ?? '-'), 'width' => 12],
                        ['text' => (string) ($row['account_name'] ?? '-'), 'width' => 32],
                        ['text' => str_replace('_', ' ', (string) ($row['finance_type'] ?? '-')), 'width' => 18],
                        ['text' => $this->formatCurrency((float) ($row['balance'] ?? 0)), 'width' => 20, 'align' => 'right'],
                    ]),
                    false,
                    2
                );
            }

            $lines[] = $this->bodyLine('', false, 0, 4);
        }

        return $lines;
    }

    /**
     * @return array<int, array<string, int|string|bool>>
     */
    private function buildProfitLossLines(array $report): array
    {
        $incomeRows = $report['income_rows'] ?? [];
        $expenseRows = $report['expense_rows'] ?? [];
        $totals = $report['totals'] ?? [];
        $lines = [
            $this->sectionLine('Ringkasan'),
            $this->bodyLine('Total Pemasukan   : ' . $this->formatCurrency((float) ($totals['income'] ?? 0))),
            $this->bodyLine('Total Pengeluaran : ' . $this->formatCurrency((float) ($totals['expense'] ?? 0))),
            $this->bodyLine('Laba / Rugi Bersih: ' . $this->formatCurrency((float) ($totals['net_result'] ?? 0)), true, 0, 6),

            $this->sectionLine('PEMASUKAN'),
            $this->monoLine(
                $this->formatColumns([
                    ['text' => 'Kode', 'width' => 12],
                    ['text' => 'Nama Akun', 'width' => 44],
                    ['text' => 'Nominal', 'width' => 22, 'align' => 'right'],
                ]),
                true
            ),
        ];

        if (empty($incomeRows)) {
            $lines[] = $this->bodyLine('Belum ada pemasukan pada periode ini.', false, 2, 4);
        } else {
            foreach ($incomeRows as $row) {
                $lines[] = $this->monoLine(
                    $this->formatColumns([
                        ['text' => (string) ($row['account_code'] ?? '-'), 'width' => 12],
                        ['text' => (string) ($row['account_name'] ?? '-'), 'width' => 44],
                        ['text' => $this->formatCurrency((float) ($row['amount'] ?? 0)), 'width' => 22, 'align' => 'right'],
                    ]),
                    false,
                    2
                );
            }
        }

        $lines[] = $this->bodyLine('', false, 0, 4);
        $lines[] = $this->sectionLine('PENGELUARAN');
        $lines[] = $this->monoLine(
            $this->formatColumns([
                ['text' => 'Kode', 'width' => 12],
                ['text' => 'Nama Akun', 'width' => 44],
                ['text' => 'Nominal', 'width' => 22, 'align' => 'right'],
            ]),
            true
        );

        if (empty($expenseRows)) {
            $lines[] = $this->bodyLine('Belum ada pengeluaran pada periode ini.', false, 2, 4);
        } else {
            foreach ($expenseRows as $row) {
                $lines[] = $this->monoLine(
                    $this->formatColumns([
                        ['text' => (string) ($row['account_code'] ?? '-'), 'width' => 12],
                        ['text' => (string) ($row['account_name'] ?? '-'), 'width' => 44],
                        ['text' => $this->formatCurrency((float) ($row['amount'] ?? 0)), 'width' => 22, 'align' => 'right'],
                    ]),
                    false,
                    2
                );
            }
        }

        return $lines;
    }

    /**
     * @return array<int, array<string, int|string|bool>>
     */
    private function buildGeneralLedgerLines(array $report): array
    {
        $summary = $report['summary'] ?? [];
        $groups = $report['groups'] ?? [];
        $lines = [
            $this->sectionLine('Ringkasan'),
            $this->bodyLine('Jumlah Akun  : ' . number_format((int) ($summary['account_count'] ?? 0), 0, ',', '.')),
            $this->bodyLine('Baris Jurnal : ' . number_format((int) ($summary['entry_count'] ?? 0), 0, ',', '.')),
            $this->bodyLine('Total Debit  : ' . $this->formatCurrency((float) ($summary['total_debit'] ?? 0))),
            $this->bodyLine('Total Kredit : ' . $this->formatCurrency((float) ($summary['total_credit'] ?? 0))),
            $this->bodyLine('Selisih      : ' . $this->formatCurrency((float) ($summary['balance_gap'] ?? 0)), true, 0, 6),
        ];

        if (empty($groups)) {
            $lines[] = $this->bodyLine('Belum ada data buku besar pada filter aktif.', false, 0, 4);

            return $lines;
        }

        foreach ($groups as $group) {
            $lines[] = $this->sectionLine(
                '[' . (string) ($group['account_code'] ?? '-') . '] '
                . (string) ($group['account_name'] ?? '-')
            );
            $lines[] = $this->bodyLine(
                'Tipe: ' . str_replace('_', ' ', (string) ($group['finance_type'] ?? 'TANPA TIPE'))
                . ' | Saldo Normal: ' . (string) ($group['normal_side'] ?? '-')
            );
            $lines[] = $this->bodyLine(
                'Debit: ' . $this->formatCurrency((float) ($group['total_debit'] ?? 0))
                . ' | Kredit: ' . $this->formatCurrency((float) ($group['total_credit'] ?? 0))
                . ' | Saldo Akhir: ' . $this->formatCurrency((float) ($group['closing_balance'] ?? 0)),
                false,
                0,
                2
            );
            $lines[] = $this->monoLine(
                $this->formatColumns([
                    ['text' => 'Tanggal', 'width' => 12],
                    ['text' => 'No Jurnal', 'width' => 18],
                    ['text' => 'Debit', 'width' => 15, 'align' => 'right'],
                    ['text' => 'Kredit', 'width' => 15, 'align' => 'right'],
                    ['text' => 'Saldo', 'width' => 15, 'align' => 'right'],
                ]),
                true,
                2
            );

            $entries = $group['entries'] ?? [];
            if (empty($entries)) {
                $lines[] = $this->bodyLine('Belum ada baris jurnal untuk akun ini.', false, 2, 4);
                continue;
            }

            foreach ($entries as $entry) {
                $lines[] = $this->monoLine(
                    $this->formatColumns([
                        [
                            'text' => $this->formatDate((string) ($entry['accounting_date'] ?? '')),
                            'width' => 12,
                        ],
                        ['text' => (string) ($entry['invoice_no'] ?? '-'), 'width' => 18],
                        ['text' => $this->formatCurrency((float) ($entry['debit'] ?? 0)), 'width' => 15, 'align' => 'right'],
                        ['text' => $this->formatCurrency((float) ($entry['credit'] ?? 0)), 'width' => 15, 'align' => 'right'],
                        ['text' => $this->formatCurrency((float) ($entry['running_balance'] ?? 0)), 'width' => 15, 'align' => 'right'],
                    ]),
                    false,
                    2
                );

                $detailSegments = array_filter([
                    'Jurnal: ' . (string) ($entry['journal_name'] ?? '-'),
                    'Label: ' . (string) ($entry['label'] ?? '-'),
                    !empty($entry['reference']) ? 'Ref: ' . (string) $entry['reference'] : null,
                    !empty($entry['partner_name']) ? 'Partner: ' . (string) $entry['partner_name'] : null,
                    !empty($entry['analytic_distribution']) ? 'Analitik: ' . (string) $entry['analytic_distribution'] : null,
                ]);

                $lines[] = $this->bodyLine(implode(' | ', $detailSegments), false, 4, 2);
            }

            $lines[] = $this->bodyLine('', false, 0, 4);
        }

        return $lines;
    }

    /**
     * @param array<int, array<string, int|string|bool>> $lines
     */
    private function renderPdfDocument(string $title, string $subtitle, array $lines): string
    {
        $blocks = $this->buildBlocks($lines);
        $pages = [];
        $blockIndex = 0;
        $pageNumber = 1;

        while ($blockIndex < count($blocks)) {
            $pages[] = $this->buildPdfPage($title, $subtitle, $blocks, $blockIndex, $pageNumber);
            $pageNumber++;
        }

        if (empty($pages)) {
            $pages[] = $this->buildPdfPage($title, $subtitle, [], $blockIndex, 1);
        }

        return $this->buildPdfBinary($pages);
    }

    /**
     * @param array<int, array<string, int|string|bool>> $lines
     * @return array<int, array{rows:array<int, array<string, int|string|bool>>, margin_after:int, height:float}>
     */
    private function buildBlocks(array $lines): array
    {
        $blocks = [];

        foreach ($lines as $line) {
            $rows = [];
            $fontSize = (int) ($line['font_size'] ?? 10);
            $lineHeight = (float) ($line['line_height'] ?? max(13, $fontSize + 3));
            $indent = (int) ($line['indent'] ?? 0);
            $bold = (bool) ($line['bold'] ?? false);
            $mono = (bool) ($line['mono'] ?? false);
            $maxChars = (int) ($line['max_chars'] ?? ($mono ? 86 : 90));
            $text = (string) ($line['text'] ?? '');
            $wrappedLines = $this->wrapText($text, $maxChars, $mono);

            if (empty($wrappedLines)) {
                $wrappedLines = [''];
            }

            foreach ($wrappedLines as $wrappedLine) {
                $rows[] = [
                    'text' => $wrappedLine,
                    'font_size' => $fontSize,
                    'line_height' => $lineHeight,
                    'indent' => $indent,
                    'bold' => $bold,
                    'mono' => $mono,
                ];
            }

            $blocks[] = [
                'rows' => $rows,
                'margin_after' => (int) ($line['margin_after'] ?? 0),
                'height' => (count($rows) * $lineHeight) + (int) ($line['margin_after'] ?? 0),
            ];
        }

        return $blocks;
    }

    /**
     * @param array<int, array{rows:array<int, array<string, int|string|bool>>, margin_after:int, height:float}> $blocks
     */
    private function buildPdfPage(
        string $title,
        string $subtitle,
        array $blocks,
        int &$blockIndex,
        int $pageNumber
    ): string {
        $content = '';
        $pageTitle = $pageNumber === 1 ? $title : $title . ' (lanjutan)';

        $content .= $this->pdfDrawText($pageTitle, 40, 40, 18, 'bold', false, [29, 78, 216]);
        $content .= $this->pdfDrawText($subtitle, 40, 64, 9, 'regular', false, [100, 116, 139]);
        $content .= $this->pdfDrawLine(40, 78, 555, 78, [215, 224, 238], 1.0);
        $content .= $this->pdfDrawText('Halaman ' . $pageNumber, 480, 804, 9, 'regular', false, [148, 163, 184]);

        $cursorTop = 96.0;
        $pageLimitTop = 790.0;

        while ($blockIndex < count($blocks)) {
            $block = $blocks[$blockIndex];

            if ($cursorTop + (float) $block['height'] > $pageLimitTop && $cursorTop > 96.0) {
                break;
            }

            foreach ($block['rows'] as $row) {
                $content .= $this->pdfDrawText(
                    (string) $row['text'],
                    40 + (int) $row['indent'],
                    $cursorTop,
                    (int) $row['font_size'],
                    (bool) $row['bold'] ? 'bold' : 'regular',
                    (bool) $row['mono'],
                    (bool) $row['bold'] ? [15, 23, 42] : [51, 65, 85]
                );
                $cursorTop += (float) $row['line_height'];
            }

            $cursorTop += (int) $block['margin_after'];
            $blockIndex++;
        }

        return $content;
    }

    /**
     * @param array<int, string> $pageStreams
     */
    private function buildPdfBinary(array $pageStreams): string
    {
        $objects = [];
        $objectIndex = 2;

        $fontRegularId = ++$objectIndex;
        $objects[$fontRegularId] = '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica /Encoding /WinAnsiEncoding >>';

        $fontBoldId = ++$objectIndex;
        $objects[$fontBoldId] = '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold /Encoding /WinAnsiEncoding >>';

        $fontMonoId = ++$objectIndex;
        $objects[$fontMonoId] = '<< /Type /Font /Subtype /Type1 /BaseFont /Courier /Encoding /WinAnsiEncoding >>';

        $fontMonoBoldId = ++$objectIndex;
        $objects[$fontMonoBoldId] = '<< /Type /Font /Subtype /Type1 /BaseFont /Courier-Bold /Encoding /WinAnsiEncoding >>';

        $pageObjectIds = [];
        foreach ($pageStreams as $pageStream) {
            $contentObjectId = ++$objectIndex;
            $objects[$contentObjectId] = "<< /Length " . strlen($pageStream) . " >>\nstream\n"
                . $pageStream . "\nendstream";

            $pageObjectId = ++$objectIndex;
            $pageObjectIds[] = $pageObjectId;
            $objects[$pageObjectId] = "<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Resources << /Font << "
                . "/F1 {$fontRegularId} 0 R /F2 {$fontBoldId} 0 R /F3 {$fontMonoId} 0 R /F4 {$fontMonoBoldId} 0 R "
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

    /**
     * @param array<int, array{align?:string,text:string,width:int}> $columns
     */
    private function formatColumns(array $columns): string
    {
        $formatted = '';

        foreach ($columns as $column) {
            $text = $this->truncateText((string) ($column['text'] ?? ''), (int) ($column['width'] ?? 0));
            $width = (int) ($column['width'] ?? 0);
            $align = (string) ($column['align'] ?? 'left');

            if ($align === 'right') {
                $formatted .= str_pad($text, $width, ' ', STR_PAD_LEFT);
                continue;
            }

            $formatted .= str_pad($text, $width, ' ', STR_PAD_RIGHT);
        }

        return rtrim($formatted);
    }

    /**
     * @return array<int, string>
     */
    private function wrapText(string $text, int $maxChars, bool $mono = false): array
    {
        $text = trim($text);
        if ($text === '') {
            return [];
        }

        if ($mono) {
            return [$text];
        }

        $wrapped = wordwrap($text, $maxChars, "\n", true);

        return array_values(array_filter(explode("\n", $wrapped), static fn (string $line): bool => $line !== ''));
    }

    private function truncateText(string $text, int $width): string
    {
        if ($width <= 0) {
            return '';
        }

        $length = $this->stringLength($text);
        if ($length <= $width) {
            return $text;
        }

        if ($width <= 3) {
            return $this->stringSlice($text, 0, $width);
        }

        return rtrim($this->stringSlice($text, 0, $width - 3)) . '...';
    }

    /**
     * @param array{0:int,1:int,2:int} $rgb
     */
    private function pdfDrawText(
        string $text,
        float $x,
        float $top,
        int $fontSize,
        string $weight = 'regular',
        bool $mono = false,
        array $rgb = [51, 65, 85]
    ): string {
        $safeText = $this->normalizePdfText($text);
        $fontKey = match (true) {
            $mono && $weight === 'bold' => 'F4',
            $mono => 'F3',
            $weight === 'bold' => 'F2',
            default => 'F1',
        };
        $y = 842.0 - $top - ($fontSize * 0.9);

        return "BT\n"
            . '/' . $fontKey . ' ' . $fontSize . " Tf\n"
            . $this->pdfRgb($rgb, false) . "\n"
            . '1 0 0 1 ' . $this->formatPdfNumber($x) . ' ' . $this->formatPdfNumber($y) . " Tm\n"
            . '(' . $this->escapePdfString($safeText) . ") Tj\n"
            . "ET\n";
    }

    /**
     * @param array{0:int,1:int,2:int} $rgb
     */
    private function pdfDrawLine(float $x1, float $top1, float $x2, float $top2, array $rgb, float $width = 1.0): string
    {
        $y1 = 842.0 - $top1;
        $y2 = 842.0 - $top2;

        return $this->pdfRgb($rgb, true) . "\n"
            . $this->formatPdfNumber($width) . " w\n"
            . $this->formatPdfNumber($x1) . ' ' . $this->formatPdfNumber($y1) . " m\n"
            . $this->formatPdfNumber($x2) . ' ' . $this->formatPdfNumber($y2) . " l S\n";
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

    private function buildSubtitle(string $description, StatementFilterDTO $filter): string
    {
        return $description
            . ' Periode: ' . $this->resolvePeriodLabel($filter)
            . ' | Dicetak: ' . now()->format('d/m/Y H:i');
    }

    private function buildFilename(string $prefix, StatementFilterDTO $filter): string
    {
        return $prefix . '-' . $this->resolvePeriodSlug($filter) . '.pdf';
    }

    private function resolvePeriodLabel(StatementFilterDTO $filter): string
    {
        $periodType = $filter->periodType ?? 'ALL';

        if ($periodType === 'DAILY' && !empty($filter->startDate)) {
            return $this->formatDateRangeLabel(
                $filter->startDate,
                $filter->endDate,
                'd/m/Y'
            );
        }

        if (
            $periodType === 'MONTHLY'
            && $filter->startYear !== null
            && $filter->startMonth !== null
        ) {
            return $this->formatMonthRangeLabel(
                $filter->startYear,
                $filter->startMonth,
                $filter->endYear,
                $filter->endMonth,
                'm/Y'
            );
        }

        if ($periodType === 'YEARLY' && $filter->startYear !== null) {
            return $this->formatYearRangeLabel($filter->startYear, $filter->endYear);
        }

        return 'Semua Periode';
    }

    private function resolvePeriodSlug(StatementFilterDTO $filter): string
    {
        $periodType = $filter->periodType ?? 'ALL';

        if ($periodType === 'DAILY' && !empty($filter->startDate)) {
            return $this->formatDateRangeLabel(
                $filter->startDate,
                $filter->endDate,
                'Y-m-d',
                '_sd_'
            );
        }

        if (
            $periodType === 'MONTHLY'
            && $filter->startYear !== null
            && $filter->startMonth !== null
        ) {
            return $this->formatMonthRangeLabel(
                $filter->startYear,
                $filter->startMonth,
                $filter->endYear,
                $filter->endMonth,
                'Y-m',
                '_sd_'
            );
        }

        if ($periodType === 'YEARLY' && $filter->startYear !== null) {
            return $this->formatYearRangeLabel($filter->startYear, $filter->endYear, '_sd_');
        }

        return 'semua-periode';
    }

    private function formatDateRangeLabel(
        string $startDate,
        ?string $endDate,
        string $format,
        string $separator = ' s.d. '
    ): string {
        $start = Carbon::parse($startDate);
        $end = !empty($endDate) ? Carbon::parse($endDate) : $start->copy();

        if ($start->equalTo($end)) {
            return $start->format($format);
        }

        return $start->format($format) . $separator . $end->format($format);
    }

    private function formatMonthRangeLabel(
        int $startYear,
        int $startMonth,
        ?int $endYear,
        ?int $endMonth,
        string $format,
        string $separator = ' s.d. '
    ): string {
        $start = Carbon::create($startYear, $startMonth, 1);
        $end = Carbon::create($endYear ?? $startYear, $endMonth ?? $startMonth, 1);

        if ($start->equalTo($end)) {
            return $start->format($format);
        }

        return $start->format($format) . $separator . $end->format($format);
    }

    private function formatYearRangeLabel(int $startYear, ?int $endYear, string $separator = ' s.d. '): string
    {
        $resolvedEndYear = $endYear ?? $startYear;

        if ($startYear === $resolvedEndYear) {
            return (string) $startYear;
        }

        return $startYear . $separator . $resolvedEndYear;
    }

    /**
     * @return array<string, int|string|bool>
     */
    private function sectionLine(string $text): array
    {
        return [
            'text' => $text,
            'bold' => true,
            'mono' => false,
            'font_size' => 11,
            'line_height' => 15,
            'margin_after' => 2,
            'max_chars' => 88,
        ];
    }

    /**
     * @return array<string, int|string|bool>
     */
    private function bodyLine(string $text, bool $bold = false, int $indent = 0, int $marginAfter = 0): array
    {
        return [
            'text' => $text,
            'bold' => $bold,
            'mono' => false,
            'font_size' => 10,
            'line_height' => 13,
            'indent' => $indent,
            'margin_after' => $marginAfter,
            'max_chars' => max(60, 90 - intdiv($indent, 2)),
        ];
    }

    /**
     * @return array<string, int|string|bool>
     */
    private function monoLine(string $text, bool $bold = false, int $indent = 0): array
    {
        return [
            'text' => $text,
            'bold' => $bold,
            'mono' => true,
            'font_size' => 9,
            'line_height' => 12,
            'indent' => $indent,
            'margin_after' => 0,
            'max_chars' => 86,
        ];
    }

    private function formatCurrency(float $amount): string
    {
        return 'Rp ' . number_format($amount, 2, ',', '.');
    }

    private function formatDate(string $value): string
    {
        if ($value === '') {
            return '-';
        }

        try {
            return Carbon::parse($value)->format('d/m/Y');
        } catch (\Throwable) {
            return $value;
        }
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
}
