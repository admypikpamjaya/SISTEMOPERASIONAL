<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Detail Log Penyusutan #{{ $log->id }}</title>
    <style>
        body {
            font-family: DejaVu Sans, Arial, sans-serif;
            font-size: 12px;
            color: #0f172a;
        }
        h1 {
            font-size: 18px;
            margin: 0 0 4px;
            color: #1d4ed8;
        }
        .sub {
            margin: 0 0 14px;
            color: #475569;
            font-size: 11px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid #cbd5e1;
            padding: 8px 10px;
            vertical-align: top;
        }
        th {
            width: 36%;
            background: #eff6ff;
            color: #1e3a8a;
            text-align: left;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.03em;
        }
        td {
            font-size: 12px;
            font-weight: 600;
        }
    </style>
</head>
<body>
    @php
        $assetCategoryRaw = $log->asset?->category;
        if ($assetCategoryRaw instanceof \App\Enums\Asset\AssetCategory) {
            $assetCategoryLabel = $assetCategoryRaw->label();
        } elseif (is_string($assetCategoryRaw) && trim($assetCategoryRaw) !== '') {
            $assetCategoryLabel = \App\Enums\Asset\AssetCategory::tryFrom($assetCategoryRaw)?->label() ?? $assetCategoryRaw;
        } else {
            $assetCategoryLabel = '-';
        }
    @endphp

    <h1>Detail Log Asset Depreciation</h1>
    <p class="sub">
        ID Log: #{{ $log->id }} | Dicetak: {{ now($timezone ?? config('app.timezone'))->format('d/m/Y H:i:s') }}
    </p>

    <table>
        <tbody>
            <tr>
                <th>Waktu Hitung (WIB)</th>
                <td>{{ $log->calculated_at?->timezone($timezone ?? config('app.timezone'))->format('d/m/Y H:i:s') ?? '-' }}</td>
            </tr>
            <tr>
                <th>Asset ID</th>
                <td>{{ $log->asset_id }}</td>
            </tr>
            <tr>
                <th>Kode Akun Asset</th>
                <td>{{ $log->asset?->account_code ?? '-' }}</td>
            </tr>
            <tr>
                <th>Kategori Asset</th>
                <td>{{ $assetCategoryLabel }}</td>
            </tr>
            <tr>
                <th>Lokasi Asset</th>
                <td>{{ $log->asset?->location ?? '-' }}</td>
            </tr>
            <tr>
                <th>Periode</th>
                <td>{{ sprintf('%02d/%04d', (int) $log->period_month, (int) $log->period_year) }}</td>
            </tr>
            <tr>
                <th>Nilai Perolehan</th>
                <td>Rp {{ number_format((float) $log->acquisition_cost, 2, ',', '.') }}</td>
            </tr>
            <tr>
                <th>Umur Manfaat (bulan)</th>
                <td>{{ (int) $log->useful_life_months }}</td>
            </tr>
            <tr>
                <th>Penyusutan per Bulan</th>
                <td>Rp {{ number_format((float) $log->depreciation_per_month, 2, ',', '.') }}</td>
            </tr>
            <tr>
                <th>Dihitung Oleh</th>
                <td>{{ $log->calculator?->name ?? '-' }}</td>
            </tr>
            <tr>
                <th>Email Penghitung</th>
                <td>{{ $log->calculator?->email ?? '-' }}</td>
            </tr>
        </tbody>
    </table>
</body>
</html>
