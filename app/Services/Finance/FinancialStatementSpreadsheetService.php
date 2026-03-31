<?php

namespace App\Services\Finance;

use App\DTOs\Finance\StatementFilterDTO;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class FinancialStatementSpreadsheetService
{
    /**
     * @return array{content:string,filename:string,mime:string}
     */
    public function exportBalanceSheet(array $report, StatementFilterDTO $filter): array
    {
        return [
            'content' => $this->renderBalanceSheetWorkbook($report, $filter),
            'filename' => $this->buildFilename('laporan-lembar-saldo', $filter),
            'mime' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ];
    }

    /**
     * @return array{content:string,filename:string,mime:string}
     */
    public function exportProfitLoss(array $report, StatementFilterDTO $filter): array
    {
        return [
            'content' => $this->renderProfitLossWorkbook($report, $filter),
            'filename' => $this->buildFilename('laporan-laba-rugi', $filter),
            'mime' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ];
    }

    /**
     * @return array{content:string,filename:string,mime:string}
     */
    public function exportGeneralLedger(array $report, StatementFilterDTO $filter): array
    {
        return [
            'content' => $this->renderGeneralLedgerWorkbook($report, $filter),
            'filename' => $this->buildFilename('buku-besar', $filter),
            'mime' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ];
    }

    /**
     * @return array{content:string,filename:string,mime:string}
     */
    public function exportJournalItems(array $report, StatementFilterDTO $filter): array
    {
        return [
            'content' => $this->renderJournalItemsWorkbook($report, $filter),
            'filename' => $this->buildFilename('item-jurnal', $filter),
            'mime' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ];
    }

    private function renderBalanceSheetWorkbook(array $report, StatementFilterDTO $filter): string
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Lembar Saldo');

        $this->prepareSheetHeader(
            $sheet,
            'LAPORAN LEMBAR SALDO',
            'Ringkasan liabilitas, piutang, kas, dan aset. Periode: ' . $this->resolvePeriodLabel($filter)
        );

        $summary = $report['summary'] ?? [];
        $sections = $report['sections'] ?? [];
        $row = 5;

        $row = $this->appendSummaryRows($sheet, $row, [
            ['Liabilitas', (float) ($summary['liabilitas_total'] ?? 0)],
            ['Piutang', (float) ($summary['piutang_total'] ?? 0)],
            ['Kas', (float) ($summary['kas_total'] ?? 0)],
            ['Aset', (float) ($summary['aset_total'] ?? 0)],
            ['Total Sisi Aset', (float) ($summary['asset_side_total'] ?? 0)],
        ]);

        $sheet->fromArray(['Kategori', 'Kode Akun', 'Nama Akun', 'Tipe', 'Saldo'], null, 'A' . $row);
        $this->styleTableHeader($sheet, 'A' . $row . ':E' . $row);
        $headerRow = $row;
        $row++;

        foreach ($sections as $section) {
            $sheet->mergeCells('A' . $row . ':E' . $row);
            $sheet->setCellValue(
                'A' . $row,
                strtoupper((string) ($section['label'] ?? 'Kategori'))
                . ' | Total: ' . $this->formatCurrency((float) ($section['total'] ?? 0))
            );
            $this->styleSectionRow($sheet, 'A' . $row . ':E' . $row);
            $row++;

            $sectionRows = $section['rows'] ?? [];
            if (empty($sectionRows)) {
                $sheet->mergeCells('A' . $row . ':E' . $row);
                $sheet->setCellValue('A' . $row, 'Tidak ada data untuk kategori ini.');
                $row++;
                continue;
            }

            foreach ($sectionRows as $sectionRow) {
                $sheet->fromArray([
                    $section['label'] ?? '-',
                    (string) ($sectionRow['account_code'] ?? '-'),
                    (string) ($sectionRow['account_name'] ?? '-'),
                    str_replace('_', ' ', (string) ($sectionRow['finance_type'] ?? '-')),
                    (float) ($sectionRow['balance'] ?? 0),
                ], null, 'A' . $row);
                $row++;
            }
        }

        $lastRow = max($row - 1, $headerRow);
        $this->applyGridStyle($sheet, 'A' . $headerRow . ':E' . $lastRow);
        $this->applyCurrencyStyle($sheet, 'B5:B9');
        $this->applyCurrencyStyle($sheet, 'E' . ($headerRow + 1) . ':E' . $lastRow);

        $sheet->getColumnDimension('A')->setWidth(20);
        $sheet->getColumnDimension('B')->setWidth(16);
        $sheet->getColumnDimension('C')->setWidth(40);
        $sheet->getColumnDimension('D')->setWidth(20);
        $sheet->getColumnDimension('E')->setWidth(18);
        $sheet->freezePane('A' . ($headerRow + 1));

        return $this->writeSpreadsheet($spreadsheet);
    }

    private function renderProfitLossWorkbook(array $report, StatementFilterDTO $filter): string
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Laba Rugi');

        $this->prepareSheetHeader(
            $sheet,
            'LAPORAN LABA RUGI',
            'Ringkasan pemasukan dan pengeluaran. Periode: ' . $this->resolvePeriodLabel($filter)
        );

        $totals = $report['totals'] ?? [];
        $row = 5;
        $row = $this->appendSummaryRows($sheet, $row, [
            ['Total Pemasukan', (float) ($totals['income'] ?? 0)],
            ['Total Pengeluaran', (float) ($totals['expense'] ?? 0)],
            ['Laba / Rugi Bersih', (float) ($totals['net_result'] ?? 0)],
        ]);

        $sheet->fromArray(['Kategori', 'Kode Akun', 'Nama Akun', 'Nominal'], null, 'A' . $row);
        $this->styleTableHeader($sheet, 'A' . $row . ':D' . $row);
        $headerRow = $row;
        $row++;

        $incomeRows = $report['income_rows'] ?? [];
        $expenseRows = $report['expense_rows'] ?? [];

        foreach ($incomeRows as $incomeRow) {
            $sheet->fromArray([
                'Pemasukan',
                (string) ($incomeRow['account_code'] ?? '-'),
                (string) ($incomeRow['account_name'] ?? '-'),
                (float) ($incomeRow['amount'] ?? 0),
            ], null, 'A' . $row);
            $row++;
        }

        foreach ($expenseRows as $expenseRow) {
            $sheet->fromArray([
                'Pengeluaran',
                (string) ($expenseRow['account_code'] ?? '-'),
                (string) ($expenseRow['account_name'] ?? '-'),
                (float) ($expenseRow['amount'] ?? 0),
            ], null, 'A' . $row);
            $row++;
        }

        if ($row === $headerRow + 1) {
            $sheet->mergeCells('A' . $row . ':D' . $row);
            $sheet->setCellValue('A' . $row, 'Belum ada data laba rugi pada periode ini.');
            $row++;
        }

        $totalRow = $row;
        $sheet->mergeCells('A' . $totalRow . ':C' . $totalRow);
        $sheet->setCellValue('A' . $totalRow, 'LABA / RUGI BERSIH');
        $sheet->setCellValue('D' . $totalRow, (float) ($totals['net_result'] ?? 0));
        $this->styleTotalRow($sheet, 'A' . $totalRow . ':D' . $totalRow);

        $lastRow = $totalRow;
        $this->applyGridStyle($sheet, 'A' . $headerRow . ':D' . $lastRow);
        $this->applyCurrencyStyle($sheet, 'B5:B7');
        $this->applyCurrencyStyle($sheet, 'D' . ($headerRow + 1) . ':D' . $lastRow);

        $sheet->getColumnDimension('A')->setWidth(18);
        $sheet->getColumnDimension('B')->setWidth(16);
        $sheet->getColumnDimension('C')->setWidth(44);
        $sheet->getColumnDimension('D')->setWidth(18);
        $sheet->freezePane('A' . ($headerRow + 1));

        return $this->writeSpreadsheet($spreadsheet);
    }

    private function renderGeneralLedgerWorkbook(array $report, StatementFilterDTO $filter): string
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Buku Besar');

        $this->prepareSheetHeader(
            $sheet,
            'BUKU BESAR',
            'Rincian jurnal keseluruhan per akun. Periode: ' . $this->resolvePeriodLabel($filter)
        );

        $summary = $report['summary'] ?? [];
        $row = 5;
        $row = $this->appendSummaryRows($sheet, $row, [
            ['Jumlah Akun', (float) ($summary['account_count'] ?? 0)],
            ['Baris Jurnal', (float) ($summary['entry_count'] ?? 0)],
            ['Total Debit', (float) ($summary['total_debit'] ?? 0)],
            ['Total Kredit', (float) ($summary['total_credit'] ?? 0)],
            ['Selisih', (float) ($summary['balance_gap'] ?? 0)],
        ], ['Jumlah Akun', 'Baris Jurnal']);

        $sheet->fromArray(
            ['Akun Kode', 'Akun Nama', 'Tanggal', 'No Jurnal', 'Nama Jurnal', 'Uraian', 'Partner', 'Referensi', 'Analitik', 'Debit', 'Kredit', 'Saldo'],
            null,
            'A' . $row
        );
        $this->styleTableHeader($sheet, 'A' . $row . ':L' . $row);
        $headerRow = $row;
        $row++;

        foreach ($report['groups'] ?? [] as $group) {
            $sheet->mergeCells('A' . $row . ':L' . $row);
            $sheet->setCellValue(
                'A' . $row,
                '[' . (string) ($group['account_code'] ?? '-') . '] '
                . (string) ($group['account_name'] ?? '-')
                . ' | Debit: ' . $this->formatCurrency((float) ($group['total_debit'] ?? 0))
                . ' | Kredit: ' . $this->formatCurrency((float) ($group['total_credit'] ?? 0))
                . ' | Saldo Akhir: ' . $this->formatCurrency((float) ($group['closing_balance'] ?? 0))
            );
            $this->styleSectionRow($sheet, 'A' . $row . ':L' . $row);
            $row++;

            foreach ($group['entries'] ?? [] as $entry) {
                $sheet->fromArray([
                    (string) ($group['account_code'] ?? '-'),
                    (string) ($group['account_name'] ?? '-'),
                    $this->formatDate((string) ($entry['accounting_date'] ?? '')),
                    (string) ($entry['invoice_no'] ?? '-'),
                    (string) ($entry['journal_name'] ?? '-'),
                    (string) ($entry['label'] ?? '-'),
                    (string) ($entry['partner_name'] ?? '-'),
                    (string) ($entry['reference'] ?? '-'),
                    (string) ($entry['analytic_distribution'] ?? '-'),
                    (float) ($entry['debit'] ?? 0),
                    (float) ($entry['credit'] ?? 0),
                    (float) ($entry['running_balance'] ?? 0),
                ], null, 'A' . $row);
                $row++;
            }
        }

        if ($row === $headerRow + 1) {
            $sheet->mergeCells('A' . $row . ':L' . $row);
            $sheet->setCellValue('A' . $row, 'Belum ada data buku besar pada filter aktif.');
            $row++;
        }

        $lastRow = max($row - 1, $headerRow);
        $this->applyGridStyle($sheet, 'A' . $headerRow . ':L' . $lastRow);
        $this->applyCurrencyStyle($sheet, 'J' . ($headerRow + 1) . ':L' . $lastRow);

        foreach (range('A', 'L') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }
        $sheet->freezePane('A' . ($headerRow + 1));

        return $this->writeSpreadsheet($spreadsheet);
    }

    private function renderJournalItemsWorkbook(array $report, StatementFilterDTO $filter): string
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Item Jurnal');

        $account = $report['account'] ?? ['code' => null, 'name' => null];
        $accountLabel = !empty($account['code'])
            ? '[' . (string) $account['code'] . '] ' . (string) ($account['name'] ?? '-')
            : 'Semua Akun';

        $this->prepareSheetHeader(
            $sheet,
            'ITEM JURNAL',
            $accountLabel . ' | Periode: ' . $this->resolvePeriodLabel($filter)
        );

        $summary = $report['summary'] ?? [];
        $row = 5;
        $row = $this->appendSummaryRows($sheet, $row, [
            ['Jumlah Baris', (float) ($summary['entry_count'] ?? 0)],
            ['Nilai Jurnal', (float) ($summary['total_amount'] ?? 0)],
            ['Total Debit', (float) ($summary['total_debit'] ?? 0)],
            ['Total Kredit', (float) ($summary['total_credit'] ?? 0)],
        ], ['Jumlah Baris']);

        $sheet->fromArray(
            ['ID', 'Tanggal', 'Entri Jurnal', 'Akun', 'Rekanan', 'Label', 'Tipe', 'Jumlah', 'Debit', 'Kredit', 'Pajak', 'Tax Grids', 'Analisa Distribusi'],
            null,
            'A' . $row
        );
        $this->styleTableHeader($sheet, 'A' . $row . ':M' . $row);
        $headerRow = $row;
        $row++;

        foreach ($report['items'] ?? [] as $item) {
            $sheet->fromArray([
                (int) ($item['item_id'] ?? 0),
                $this->formatDate((string) ($item['accounting_date'] ?? '')),
                (string) ($item['invoice_no'] ?? '-'),
                trim((string) ($item['account_code'] ?? '-') . ' ' . (string) ($item['account_name'] ?? '')),
                (string) ($item['partner_name'] ?? '-'),
                (string) ($item['label'] ?? '-'),
                (string) ($item['entry_type'] ?? '-'),
                (float) ($item['amount_currency'] ?? 0),
                (float) ($item['debit'] ?? 0),
                (float) ($item['credit'] ?? 0),
                (string) ($item['tax_label'] ?? '-'),
                (string) ($item['tax_grids'] ?? '-'),
                (string) ($item['analytic_distribution'] ?? '-'),
            ], null, 'A' . $row);
            $row++;
        }

        if ($row === $headerRow + 1) {
            $sheet->mergeCells('A' . $row . ':M' . $row);
            $sheet->setCellValue('A' . $row, 'Belum ada item jurnal untuk filter aktif.');
            $row++;
        }

        $lastRow = max($row - 1, $headerRow);
        $this->applyGridStyle($sheet, 'A' . $headerRow . ':M' . $lastRow);
        $this->applyCurrencyStyle($sheet, 'H' . ($headerRow + 1) . ':J' . $lastRow);

        foreach (range('A', 'M') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }
        $sheet->freezePane('A' . ($headerRow + 1));

        return $this->writeSpreadsheet($spreadsheet);
    }

    /**
     * @param array<int, array{0:string,1:float}> $rows
     * @param array<int, string> $plainNumberLabels
     */
    private function appendSummaryRows(Worksheet $sheet, int $startRow, array $rows, array $plainNumberLabels = []): int
    {
        $row = $startRow;

        foreach ($rows as [$label, $amount]) {
            $sheet->setCellValue('A' . $row, $label);
            $sheet->setCellValue('B' . $row, $amount);
            $sheet->getStyle('A' . $row)->getFont()->setBold(true);

            if (in_array($label, $plainNumberLabels, true)) {
                $sheet->getStyle('B' . $row)
                    ->getNumberFormat()
                    ->setFormatCode('#,##0');
            } else {
                $sheet->getStyle('B' . $row)
                    ->getNumberFormat()
                    ->setFormatCode('[$Rp-421] #,##0.00');
            }

            $sheet->getStyle('B' . $row)
                ->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $row++;
        }

        return $row + 1;
    }

    private function prepareSheetHeader(Worksheet $sheet, string $title, string $subtitle): void
    {
        $sheet->mergeCells('A1:M1');
        $sheet->setCellValue('A1', $title);
        $sheet->mergeCells('A2:M2');
        $sheet->setCellValue('A2', $subtitle);

        $sheet->getStyle('A1:A2')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle('A1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 14, 'color' => ['rgb' => 'FFFFFF']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '1E3A8A'],
            ],
        ]);
        $sheet->getStyle('A2')->applyFromArray([
            'font' => ['italic' => true, 'color' => ['rgb' => '475569']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(24);
        $sheet->getRowDimension(2)->setRowHeight(20);
    }

    private function styleTableHeader(Worksheet $sheet, string $range): void
    {
        $sheet->getStyle($range)->applyFromArray([
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
    }

    private function styleSectionRow(Worksheet $sheet, string $range): void
    {
        $sheet->getStyle($range)->applyFromArray([
            'font' => ['bold' => true],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'EAF2FF'],
            ],
        ]);
    }

    private function styleTotalRow(Worksheet $sheet, string $range): void
    {
        $sheet->getStyle($range)->applyFromArray([
            'font' => ['bold' => true],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'FFF2CC'],
            ],
        ]);
    }

    private function applyGridStyle(Worksheet $sheet, string $range): void
    {
        $sheet->getStyle($range)->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'D1D5DB'],
                ],
            ],
            'alignment' => [
                'vertical' => Alignment::VERTICAL_TOP,
            ],
        ]);
    }

    private function applyCurrencyStyle(Worksheet $sheet, string $range): void
    {
        $sheet->getStyle($range)->getNumberFormat()->setFormatCode('[$Rp-421] #,##0.00');
        $sheet->getStyle($range)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
    }

    private function writeSpreadsheet(Spreadsheet $spreadsheet): string
    {
        $writer = new Xlsx($spreadsheet);
        ob_start();
        $writer->save('php://output');

        return (string) ob_get_clean();
    }

    private function buildFilename(string $prefix, StatementFilterDTO $filter): string
    {
        return $prefix . '-' . $this->resolvePeriodSlug($filter) . '.xlsx';
    }

    private function resolvePeriodLabel(StatementFilterDTO $filter): string
    {
        $periodType = $filter->periodType ?? 'ALL';

        if ($periodType === 'DAILY' && !empty($filter->startDate)) {
            return $this->formatDateRangeLabel($filter->startDate, $filter->endDate, 'd/m/Y');
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
            return $this->formatDateRangeLabel($filter->startDate, $filter->endDate, 'Y-m-d', '_sd_');
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
}
