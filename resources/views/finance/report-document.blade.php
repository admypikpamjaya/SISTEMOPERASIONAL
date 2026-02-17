<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Laba Rugi</title>
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            color: #1f2937;
            font-size: 12px;
            margin: 20px;
        }

        .header {
            text-align: center;
            margin-bottom: 18px;
        }

        .header h1 {
            margin: 0;
            font-size: 22px;
            letter-spacing: 0.5px;
        }

        .header p {
            margin: 4px 0 0 0;
            font-size: 12px;
        }

        .meta {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 16px;
        }

        .meta td {
            padding: 4px 6px;
            vertical-align: top;
        }

        .meta .label {
            width: 140px;
            font-weight: bold;
        }

        .report-table {
            width: 100%;
            border-collapse: collapse;
        }

        .report-table th,
        .report-table td {
            border: 1px solid #cbd5e1;
            padding: 6px 8px;
        }

        .report-table th {
            background: #f1f5f9;
            text-align: left;
        }

        .report-table .amount {
            text-align: right;
            white-space: nowrap;
        }

        .section-row td {
            background: #e2e8f0;
            font-weight: bold;
        }

        .total-row td {
            background: #f8fafc;
            font-weight: bold;
        }

        .surplus-row td {
            background: #dcfce7;
            font-weight: bold;
        }

        .signature {
            margin-top: 48px;
            width: 100%;
            border-collapse: collapse;
        }

        .signature td {
            width: 50%;
            text-align: center;
            vertical-align: top;
            padding: 0 20px;
        }

        .signature .name {
            margin-top: 64px;
            font-weight: bold;
            text-decoration: underline;
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
            <td class="label">Saldo Awal</td>
            <td>: Rp {{ number_format($report->openingBalance, 2, ',', '.') }}</td>
            <td class="label">Saldo Akhir</td>
            <td>: Rp {{ number_format($report->endingBalance, 2, ',', '.') }}</td>
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
                <th style="width: 150px;">Kode</th>
                <th style="width: 260px;">Uraian</th>
                <th>Keterangan</th>
                <th style="width: 220px;" class="amount">Nominal</th>
            </tr>
        </thead>
        <tbody>
            <tr class="section-row">
                <td colspan="4">Penghasilan</td>
            </tr>
            @forelse($report->incomeLines as $line)
                <tr>
                    <td>{{ $line->lineCode }}</td>
                    <td>{{ $line->lineLabel }}</td>
                    <td>{{ $line->description ?: '-' }}</td>
                    <td class="amount">Rp {{ number_format($line->amount, 2, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4">Tidak ada item penghasilan.</td>
                </tr>
            @endforelse
            <tr class="total-row">
                <td colspan="3">Total Penghasilan</td>
                <td class="amount">Rp {{ number_format($report->totalIncome, 2, ',', '.') }}</td>
            </tr>

            <tr class="section-row">
                <td colspan="4">Pengeluaran</td>
            </tr>
            @forelse($report->expenseLines as $line)
                <tr>
                    <td>{{ $line->lineCode }}</td>
                    <td>{{ $line->lineLabel }}</td>
                    <td>{{ $line->description ?: '-' }}</td>
                    <td class="amount">Rp {{ number_format($line->amount, 2, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4">Tidak ada item pengeluaran.</td>
                </tr>
            @endforelse
            <tr class="total-row">
                <td colspan="3">Total Pengeluaran (non-penyusutan)</td>
                <td class="amount">Rp {{ number_format($report->totalExpense, 2, ',', '.') }}</td>
            </tr>

            <tr class="section-row">
                <td colspan="4">Penyusutan</td>
            </tr>
            @forelse($report->depreciationLines as $line)
                <tr>
                    <td>{{ $line->lineCode }}</td>
                    <td>{{ $line->lineLabel }}</td>
                    <td>{{ $line->description ?: '-' }}</td>
                    <td class="amount">Rp {{ number_format($line->amount, 2, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4">Tidak ada item penyusutan.</td>
                </tr>
            @endforelse
            <tr class="total-row">
                <td colspan="3">Total Penyusutan</td>
                <td class="amount">Rp {{ number_format($report->totalDepreciation, 2, ',', '.') }}</td>
            </tr>

            <tr class="surplus-row">
                <td colspan="3">Surplus (Defisit)</td>
                <td class="amount">Rp {{ number_format($report->surplusDeficit, 2, ',', '.') }}</td>
            </tr>
            <tr class="total-row">
                <td colspan="3">Saldo Akhir</td>
                <td class="amount">Rp {{ number_format($report->endingBalance, 2, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

    <table class="signature">
        <tr>
            <td>
                Diperiksa,
                <div class="name">Bendahara</div>
            </td>
            <td>
                Mengetahui,
                <div class="name">Ketua Pengurus</div>
            </td>
        </tr>
    </table>
</body>
</html>
