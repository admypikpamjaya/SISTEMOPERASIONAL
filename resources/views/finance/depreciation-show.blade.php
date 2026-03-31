@extends('layouts.app')

@section('content')
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
<style>
    :root {
        --adl-blue: #2563eb;
        --adl-blue-dark: #1e3a8a;
        --adl-bg: #f0f4fd;
        --adl-card: #ffffff;
        --adl-card-soft: #f8fbff;
        --adl-text: #0f172a;
        --adl-text-soft: #334155;
        --adl-muted: #64748b;
        --adl-border: rgba(37, 99, 235, 0.12);
        --adl-shadow: 0 10px 30px rgba(15, 23, 42, 0.08), 0 4px 14px rgba(37, 99, 235, 0.08);
    }

    body,
    .content-wrapper {
        background: var(--adl-bg) !important;
    }
    .adl-wrap {
        max-width: 980px;
        margin: 0 auto;
        font-family: 'Plus Jakarta Sans', 'Segoe UI', sans-serif;
        color: var(--adl-text);
    }
    .adl-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        flex-wrap: wrap;
        margin-bottom: 16px;
    }
    .adl-title {
        margin: 0;
        font-size: 1.22rem;
        font-weight: 800;
        color: var(--adl-text);
    }
    .adl-subtitle {
        margin: 3px 0 0;
        font-size: .8rem;
        color: var(--adl-muted);
    }
    .adl-actions {
        display: inline-flex;
        gap: 8px;
        flex-wrap: wrap;
    }
    .adl-btn {
        border: 1px solid var(--adl-border);
        background: #eff6ff;
        color: var(--adl-blue);
        border-radius: 10px;
        font-size: .8rem;
        font-weight: 700;
        padding: 8px 12px;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 7px;
        transition: all .2s ease;
    }
    .adl-btn:hover {
        text-decoration: none;
        background: #dbeafe;
        color: var(--adl-blue-dark);
        transform: translateY(-1px);
    }
    .adl-btn-primary {
        background: linear-gradient(135deg, var(--adl-blue), #1d4ed8);
        color: #fff;
        border-color: transparent;
        box-shadow: 0 8px 22px rgba(37, 99, 235, .22);
    }
    .adl-btn-primary:hover {
        color: #fff;
        background: linear-gradient(135deg, #1d4ed8, #1e40af);
    }
    .adl-card {
        background: var(--adl-card);
        border: 1px solid var(--adl-border);
        border-radius: 16px;
        box-shadow: var(--adl-shadow);
        overflow: hidden;
    }
    .adl-card-head {
        background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
        color: #fff;
        padding: 14px 18px;
        font-size: .94rem;
        font-weight: 700;
    }
    .adl-table-wrap {
        overflow-x: auto;
    }
    .adl-table {
        width: 100%;
        border-collapse: collapse;
    }
    .adl-table th {
        width: 230px;
        padding: 12px 16px;
        font-size: .73rem;
        color: var(--adl-muted);
        text-transform: uppercase;
        letter-spacing: .05em;
        border-bottom: 1px solid var(--adl-border);
        background: var(--adl-card-soft);
        vertical-align: top;
    }
    .adl-table td {
        padding: 12px 16px;
        font-size: .88rem;
        color: var(--adl-text-soft);
        border-bottom: 1px solid var(--adl-border);
        font-weight: 600;
        background: var(--adl-card);
    }
    .adl-table tr:last-child th,
    .adl-table tr:last-child td {
        border-bottom: none;
    }
    .adl-table tr:hover td {
        background: rgba(37, 99, 235, .03);
    }

    body.dark-mode,
    body.dark-mode .content-wrapper {
        background: var(--app-bg) !important;
    }
    body.dark-mode .adl-title {
        color: var(--app-text) !important;
    }
    body.dark-mode .adl-subtitle,
    body.dark-mode .adl-table th {
        color: var(--app-text-muted) !important;
    }
    body.dark-mode .adl-card {
        background: var(--app-surface) !important;
        border-color: var(--app-border) !important;
        box-shadow: var(--app-shadow) !important;
    }
    body.dark-mode .adl-btn {
        background: var(--app-surface-soft) !important;
        border-color: var(--app-border) !important;
        color: var(--app-text) !important;
    }
    body.dark-mode .adl-btn:hover {
        background: var(--app-surface) !important;
        color: var(--app-text) !important;
    }
    body.dark-mode .adl-btn-primary {
        background: linear-gradient(135deg, var(--adl-blue), #1d4ed8) !important;
        color: #fff !important;
        border-color: transparent !important;
    }
    body.dark-mode .adl-btn-primary:hover {
        background: linear-gradient(135deg, #1d4ed8, #1e40af) !important;
        color: #fff !important;
    }
    body.dark-mode .adl-table th {
        background: var(--app-surface-soft) !important;
        border-color: var(--app-border) !important;
    }
    body.dark-mode .adl-table td {
        background: var(--app-surface) !important;
        color: var(--app-text-soft) !important;
        border-color: var(--app-border) !important;
    }
    body.dark-mode .adl-table tr:hover td {
        background: var(--app-row-hover) !important;
    }
    @media (max-width: 768px) {
        .adl-table th,
        .adl-table td {
            padding: 11px 14px;
        }
        .adl-table th {
            width: 180px;
        }
    }
</style>

<div class="adl-wrap">
    <div class="adl-head">
        <div>
            <h1 class="adl-title">Detail Log Asset Depreciation</h1>
            <p class="adl-subtitle">Rincian hasil kalkulasi penyusutan aset.</p>
        </div>
        <div class="adl-actions">
            <a href="{{ route('finance.depreciation.index') }}" class="adl-btn">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
            <a href="{{ route('finance.depreciation.logs.download', ['log' => $log->id]) }}" class="adl-btn adl-btn-primary">
                <i class="fas fa-file-pdf"></i> Download PDF
            </a>
        </div>
    </div>

    <div class="adl-card">
        <div class="adl-card-head">
            ID Log #{{ $log->id }}
        </div>
        <div class="adl-table-wrap">
            <table class="adl-table">
                <tbody>
                    <tr>
                        <th>Waktu Hitung (WIB)</th>
                        <td>{{ $log->calculated_at?->timezone(config('app.timezone'))->format('d/m/Y H:i:s') ?? '-' }}</td>
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
        </div>
    </div>
</div>
@endsection
