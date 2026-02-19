<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Laba Rugi</title>
    <style>
        body {
            margin: 0;
            padding: 24px;
            font-family: DejaVu Sans, Arial, Helvetica, sans-serif;
            font-size: 12px;
            color: #dce3eb;
            background: #1f2229;
        }

        .document-shell {
            max-width: 860px;
            margin: 0 auto;
            padding: 36px 38px 64px;
            background: linear-gradient(90deg, #22252d 0%, #2b2e35 100%);
        }

        .header {
            text-align: center;
            margin-bottom: 22px;
        }

        .header h1 {
            margin: 0;
            font-size: 44px;
            font-weight: 700;
            letter-spacing: 1px;
            color: #e8eef4;
        }

        .header p {
            margin: 6px 0 0;
            font-size: 23px;
            color: #d2dae5;
            letter-spacing: 0.4px;
        }

        .meta {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 16px;
        }

        .meta td {
            padding: 6px 8px;
            vertical-align: top;
            color: #d6dee8;
        }

        .meta .label {
            width: 160px;
            font-weight: 700;
            color: #e7edf4;
        }

        .report-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            margin-top: 8px;
        }

        .report-table th,
        .report-table td {
            border: 1px solid #4d596d;
            padding: 8px 10px;
        }

        .report-table thead th {
            background: #2f3844;
            color: #e8edf4;
            font-weight: 700;
            text-align: left;
        }

        .report-table td {
            background: #252a33;
        }

        .report-table .section-row td {
            background: #3a4350;
            font-weight: 700;
            color: #e8edf4;
        }

        .report-table .total-row td {
            background: #2a303a;
            font-weight: 700;
        }

        .report-table .surplus-row td {
            background: #005e2a;
            font-weight: 700;
            color: #e8f8ee;
        }

        .report-table .amount {
            text-align: right;
            white-space: nowrap;
            width: 220px;
        }

        .signature {
            width: 100%;
            border-collapse: collapse;
            margin-top: 56px;
        }

        .signature td {
            width: 50%;
            text-align: center;
            color: #d6dee8;
            padding-top: 4px;
        }
    </style>
</head>
<body>
    @php
        $periodLabel = $report->year;
        if ($report->reportType === 'DAILY') {
            $periodLabel = $report->periodDate
                ? \Carbon\Carbon::parse($report->periodDate)->format('d/m/Y')
                : sprintf('%02d/%02d/%04d', (int) ($report->day ?? 1), (int) ($report->month ?? 1), $report->year);
        } elseif ($report->reportType === 'MONTHLY') {
            $periodLabel = sprintf('%02d/%04d', (int) ($report->month ?? 1), $report->year);
        }
    @endphp

    <div class="document-shell">
        <div class="header">
            <h1>LABA DAN RUGI</h1>
            <p>YPIK PAM JAYA</p>
        </div>

        <table class="meta">
            <tr>
                <td class="label">Periode</td>
                <td>: {{ $periodLabel }}</td>
                <td class="label">Jenis</td>
                <td>: {{ $report->reportType }}</td>
            </tr>
            <tr>
                <td class="label">Disusun Oleh</td>
                <td>: {{ $report->generatedByName ?? '-' }}</td>
                <td class="label">Generated At</td>
                <td>: {{ $report->generatedAt->format('Y-m-d H:i:s') }}</td>
            </tr>
        </table>

        <table class="report-table">
            <thead>
                <tr>
                    <th style="width: 170px;">Kode</th>
                    <th>Uraian</th>
                    <th class="amount">Nominal</th>
                </tr>
            </thead>
            <tbody>
                <tr class="section-row">
                    <td colspan="3">Penghasilan</td>
                </tr>
                @forelse($report->incomeLines as $line)
                    <tr>
                        <td>{{ $line->lineCode }}</td>
                        <td>
                            {{ $line->lineLabel }}
                            @if($line->invoiceNumber)
                                <div style="font-size: 10px; color: #b9c6d8; margin-top: 2px;">
                                    Faktur: {{ $line->invoiceNumber }}
                                </div>
                            @endif
                        </td>
                        <td class="amount">{{ number_format($line->amount, 2, ',', '.') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3">Tidak ada item penghasilan.</td>
                    </tr>
                @endforelse
                <tr class="total-row">
                    <td colspan="2">Total Penghasilan</td>
                    <td class="amount">{{ number_format($report->totalIncome, 2, ',', '.') }}</td>
                </tr>

                <tr class="section-row">
                    <td colspan="3">Pengeluaran</td>
                </tr>
                @forelse($report->expenseLines as $line)
                    <tr>
                        <td>{{ $line->lineCode }}</td>
                        <td>
                            {{ $line->lineLabel }}
                            @if($line->invoiceNumber)
                                <div style="font-size: 10px; color: #b9c6d8; margin-top: 2px;">
                                    Faktur: {{ $line->invoiceNumber }}
                                </div>
                            @endif
                        </td>
                        <td class="amount">{{ number_format($line->amount, 2, ',', '.') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3">Tidak ada item pengeluaran.</td>
                    </tr>
                @endforelse
                <tr class="total-row">
                    <td colspan="2">Total Pengeluaran (non-penyusutan)</td>
                    <td class="amount">{{ number_format($report->totalExpense, 2, ',', '.') }}</td>
                </tr>

                <tr class="section-row">
                    <td colspan="3">Penyusutan</td>
                </tr>
                @forelse($report->depreciationLines as $line)
                    <tr>
                        <td>{{ $line->lineCode }}</td>
                        <td>
                            {{ $line->lineLabel }}
                            @if($line->invoiceNumber)
                                <div style="font-size: 10px; color: #b9c6d8; margin-top: 2px;">
                                    Faktur: {{ $line->invoiceNumber }}
                                </div>
                            @endif
                        </td>
                        <td class="amount">{{ number_format($line->amount, 2, ',', '.') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3">Tidak ada item penyusutan.</td>
                    </tr>
                @endforelse
                <tr class="total-row">
                    <td colspan="2">Total Penyusutan</td>
                    <td class="amount">{{ number_format($report->totalDepreciation, 2, ',', '.') }}</td>
                </tr>

                <tr class="surplus-row">
                    <td colspan="2">Surplus (Defisit)</td>
                    <td class="amount">{{ number_format($report->surplusDeficit, 2, ',', '.') }}</td>
                </tr>
            </tbody>
        </table>

        <table class="signature">
            <tr>
                <td>Diperiksa,</td>
                <td>Mengetahui,</td>
            </tr>
        </table>
    </div>
</body>
</html>
