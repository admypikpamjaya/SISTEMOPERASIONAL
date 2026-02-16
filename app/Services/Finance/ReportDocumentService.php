<?php

namespace App\Services\Finance;

use App\DTOs\Finance\ProfitLossReportDetailDTO;

class ReportDocumentService
{
    public function renderProfitLossDocument(ProfitLossReportDetailDTO $report): string
    {
        return view('finance.report-document', [
            'report' => $report,
        ])->render();
    }

    public function buildProfitLossFilename(ProfitLossReportDetailDTO $report): string
    {
        $period = $report->month !== null
            ? sprintf('%04d-%02d', $report->year, $report->month)
            : (string) $report->year;

        return sprintf('laporan-laba-rugi-%s.doc', $period);
    }
}
