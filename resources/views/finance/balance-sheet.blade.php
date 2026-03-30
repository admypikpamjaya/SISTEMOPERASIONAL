@extends('layouts.app')

@section('content')
@php
    $summary = $report['summary'] ?? [];
    $sections = $report['sections'] ?? [];
    $uncategorizedCount = (int) ($report['uncategorized_count'] ?? 0);
    $hasRows = collect($sections)->sum(fn ($section) => count($section['rows'] ?? [])) > 0;
    $sectionMeta = [
        'liabilitas' => ['icon' => 'fa-landmark', 'badge' => 'fs-danger'],
        'piutang' => ['icon' => 'fa-file-invoice-dollar', 'badge' => 'fs-blue'],
        'kas' => ['icon' => 'fa-wallet', 'badge' => 'fs-green'],
        'aset' => ['icon' => 'fa-building', 'badge' => 'fs-amber'],
    ];
@endphp

<style>
    @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');

    :root {
        --fs-blue: #1d4ed8;
        --fs-blue-dark: #1e3a8a;
        --fs-blue-soft: rgba(37, 99, 235, 0.10);
        --fs-green: #059669;
        --fs-green-soft: rgba(16, 185, 129, 0.12);
        --fs-red: #dc2626;
        --fs-red-soft: rgba(239, 68, 68, 0.12);
        --fs-amber: #d97706;
        --fs-amber-soft: rgba(245, 158, 11, 0.12);
        --fs-bg: #f0f4fd;
        --fs-card: #ffffff;
        --fs-text: #0f172a;
        --fs-muted: #64748b;
        --fs-border: rgba(37, 99, 235, 0.10);
        --fs-shadow: 0 10px 32px rgba(15, 23, 42, 0.08), 0 4px 14px rgba(37, 99, 235, 0.06);
        --fs-radius: 18px;
        --fs-radius-sm: 12px;
    }

    body, .content-wrapper {
        background: var(--fs-bg) !important;
        font-family: 'Plus Jakarta Sans', sans-serif !important;
    }

    .fs-page-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        flex-wrap: wrap;
        margin-bottom: 1.5rem;
    }
    .fs-page-title {
        display: flex;
        align-items: center;
        gap: 0.9rem;
    }
    .fs-title-icon {
        width: 50px;
        height: 50px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, var(--fs-blue), var(--fs-blue-dark));
        color: #fff;
        font-size: 1.2rem;
        box-shadow: var(--fs-shadow);
    }
    .fs-page-title h1 {
        margin: 0;
        color: var(--fs-text);
        font-size: 1.4rem;
        font-weight: 800;
    }
    .fs-page-title p {
        margin: 0.15rem 0 0;
        color: var(--fs-muted);
        font-size: 0.82rem;
        font-weight: 500;
    }
    .fs-nav {
        display: flex;
        flex-wrap: wrap;
        gap: 0.55rem;
    }
    .fs-nav-link,
    .fs-btn {
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        border-radius: 12px;
        padding: 0.6rem 1rem;
        font-size: 0.82rem;
        font-weight: 700;
        text-decoration: none;
        border: 1px solid transparent;
        transition: all 0.2s ease;
    }
    .fs-nav-link:hover,
    .fs-btn:hover {
        text-decoration: none;
        transform: translateY(-1px);
    }
    .fs-nav-link.primary,
    .fs-btn-primary {
        background: linear-gradient(135deg, var(--fs-blue), #2563eb);
        color: #fff;
        box-shadow: 0 8px 22px rgba(37, 99, 235, 0.24);
    }
    .fs-nav-link.muted,
    .fs-btn-muted {
        background: #fff;
        color: var(--fs-muted);
        border-color: var(--fs-border);
    }

    .fs-filter-card,
    .fs-summary-card,
    .fs-section-card,
    .fs-empty-card,
    .fs-note-card {
        background: var(--fs-card);
        border: 1px solid var(--fs-border);
        border-radius: var(--fs-radius);
        box-shadow: var(--fs-shadow);
    }

    .fs-filter-head,
    .fs-section-head {
        padding: 1rem 1.2rem;
        border-bottom: 1px solid var(--fs-border);
    }
    .fs-filter-head {
        background: linear-gradient(135deg, var(--fs-blue-dark), var(--fs-blue));
        border-bottom: none;
        border-radius: var(--fs-radius) var(--fs-radius) 0 0;
    }
    .fs-filter-title,
    .fs-section-title {
        display: flex;
        align-items: center;
        gap: 0.65rem;
        font-size: 0.9rem;
        font-weight: 700;
    }
    .fs-filter-title {
        color: #fff;
    }
    .fs-section-title {
        color: var(--fs-text);
    }
    .fs-filter-icon,
    .fs-section-icon {
        width: 30px;
        height: 30px;
        border-radius: 10px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }
    .fs-filter-icon {
        background: rgba(255, 255, 255, 0.16);
    }
    .fs-section-icon {
        background: rgba(37, 99, 235, 0.08);
        color: var(--fs-blue);
    }
    .fs-filter-body {
        padding: 1.1rem 1.2rem 0.3rem;
    }
    .fs-field {
        margin-bottom: 1rem;
    }
    .fs-label {
        display: flex;
        align-items: center;
        gap: 0.35rem;
        margin-bottom: 0.4rem;
        font-size: 0.7rem;
        font-weight: 800;
        letter-spacing: 0.06em;
        text-transform: uppercase;
        color: var(--fs-muted);
    }
    .fs-control {
        width: 100%;
        border: 1.5px solid rgba(148, 163, 184, 0.18);
        border-radius: var(--fs-radius-sm);
        padding: 0.65rem 0.85rem;
        font-size: 0.84rem;
        color: var(--fs-text);
        background: #fff;
    }
    .fs-control:focus {
        outline: none;
        border-color: rgba(37, 99, 235, 0.4);
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
    }
    .fs-actions {
        display: flex;
        align-items: flex-end;
        gap: 0.55rem;
        margin-bottom: 1rem;
        flex-wrap: wrap;
    }

    .fs-summary-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(210px, 1fr));
        gap: 0.9rem;
        margin: 1.2rem 0;
    }
    .fs-summary-card {
        padding: 1rem 1.1rem;
    }
    .fs-summary-label {
        display: flex;
        align-items: center;
        gap: 0.45rem;
        color: var(--fs-muted);
        font-size: 0.72rem;
        font-weight: 800;
        letter-spacing: 0.06em;
        text-transform: uppercase;
        margin-bottom: 0.45rem;
    }
    .fs-summary-value {
        color: var(--fs-text);
        font-size: 1.3rem;
        font-weight: 800;
        line-height: 1.15;
    }
    .fs-summary-help {
        margin-top: 0.35rem;
        color: var(--fs-muted);
        font-size: 0.75rem;
        font-weight: 500;
    }

    .fs-note-card {
        margin-bottom: 1rem;
        padding: 0.9rem 1rem;
        display: flex;
        gap: 0.7rem;
        align-items: flex-start;
        color: #92400e;
        background: rgba(245, 158, 11, 0.08);
        border-color: rgba(245, 158, 11, 0.16);
    }

    .fs-section-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
        gap: 1rem;
    }
    .fs-section-total {
        color: var(--fs-muted);
        font-size: 0.8rem;
        font-weight: 700;
    }
    .fs-table-wrap {
        overflow-x: auto;
    }
    .fs-table {
        width: 100%;
        border-collapse: collapse;
    }
    .fs-table th {
        background: #f8fbff;
        color: var(--fs-muted);
        font-size: 0.68rem;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        padding: 0.75rem 1rem;
        border-bottom: 1px solid var(--fs-border);
    }
    .fs-table td {
        padding: 0.78rem 1rem;
        font-size: 0.82rem;
        color: #334155;
        border-bottom: 1px solid rgba(148, 163, 184, 0.12);
        vertical-align: middle;
    }
    .fs-table tbody tr:last-child td {
        border-bottom: none;
    }
    .fs-table tbody tr:hover td {
        background: rgba(37, 99, 235, 0.03);
    }
    .fs-amount {
        text-align: right;
        font-weight: 800;
        color: var(--fs-blue);
        white-space: nowrap;
    }
    .fs-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        border-radius: 999px;
        padding: 0.25rem 0.65rem;
        font-size: 0.68rem;
        font-weight: 800;
    }
    .fs-badge.fs-blue { background: var(--fs-blue-soft); color: var(--fs-blue); }
    .fs-badge.fs-green { background: var(--fs-green-soft); color: var(--fs-green); }
    .fs-badge.fs-danger { background: var(--fs-red-soft); color: var(--fs-red); }
    .fs-badge.fs-amber { background: var(--fs-amber-soft); color: var(--fs-amber); }

    .fs-empty-card {
        margin-top: 1rem;
        padding: 2.5rem 1.2rem;
        text-align: center;
        color: var(--fs-muted);
    }
    .fs-empty-card i {
        font-size: 2.3rem;
        margin-bottom: 0.8rem;
        color: rgba(37, 99, 235, 0.28);
    }
    .fs-empty-card h4 {
        color: var(--fs-text);
        font-size: 1rem;
        font-weight: 700;
        margin-bottom: 0.35rem;
    }

    body.dark-mode .fs-filter-card,
    body.dark-mode .fs-summary-card,
    body.dark-mode .fs-section-card,
    body.dark-mode .fs-empty-card,
    body.dark-mode .fs-note-card {
        background: var(--app-surface) !important;
        border-color: var(--app-border) !important;
        box-shadow: var(--app-shadow) !important;
    }
    body.dark-mode .fs-page-title h1,
    body.dark-mode .fs-summary-value,
    body.dark-mode .fs-section-title,
    body.dark-mode .fs-empty-card h4 {
        color: var(--app-text) !important;
    }
    body.dark-mode .fs-page-title p,
    body.dark-mode .fs-label,
    body.dark-mode .fs-summary-help,
    body.dark-mode .fs-summary-label,
    body.dark-mode .fs-section-total,
    body.dark-mode .fs-empty-card,
    body.dark-mode .fs-table th {
        color: var(--app-text-muted) !important;
    }
    body.dark-mode .fs-control,
    body.dark-mode .fs-nav-link.muted,
    body.dark-mode .fs-btn-muted {
        background: var(--app-surface-soft) !important;
        border-color: var(--app-border) !important;
        color: var(--app-text) !important;
    }
    body.dark-mode .fs-table td {
        color: var(--app-text-soft) !important;
        border-color: var(--app-border) !important;
    }
    body.dark-mode .fs-table tbody tr:hover td {
        background: var(--app-row-hover) !important;
    }
</style>

<div class="fs-page-header">
    <div class="fs-page-title">
        <div class="fs-title-icon"><i class="fas fa-balance-scale"></i></div>
        <div>
            <h1>Laporan Lembar Saldo</h1>
            <p>Ringkasan liabilitas, piutang, kas, dan aset untuk periode {{ $periodLabel }}.</p>
        </div>
    </div>

    <div class="fs-nav">
        <a href="{{ route('finance.dashboard') }}" class="fs-nav-link muted">
            <i class="fas fa-arrow-left"></i> Dashboard
        </a>
        <a href="{{ route('finance.report.profit-loss', $filterQuery) }}" class="fs-nav-link muted">
            <i class="fas fa-chart-area"></i> Laba Rugi
        </a>
        <a href="{{ route('finance.report.general-ledger', $filterQuery) }}" class="fs-nav-link primary">
            <i class="fas fa-book-open"></i> Buku Besar
        </a>
    </div>
</div>

@include('finance.partials.statement-filter', [
    'action' => route('finance.report.balance-sheet'),
    'filters' => $filters,
    'showPerPage' => false,
])

<div class="fs-summary-grid">
    <div class="fs-summary-card">
        <div class="fs-summary-label"><i class="fas fa-landmark"></i> Liabilitas</div>
        <div class="fs-summary-value">Rp {{ number_format((float) ($summary['liabilitas_total'] ?? 0), 2, ',', '.') }}</div>
        <div class="fs-summary-help">Total kewajiban yang terpetakan pada periode ini.</div>
    </div>
    <div class="fs-summary-card">
        <div class="fs-summary-label"><i class="fas fa-file-invoice-dollar"></i> Piutang</div>
        <div class="fs-summary-value">Rp {{ number_format((float) ($summary['piutang_total'] ?? 0), 2, ',', '.') }}</div>
        <div class="fs-summary-help">Total piutang berdasarkan akun yang aktif dan terpakai.</div>
    </div>
    <div class="fs-summary-card">
        <div class="fs-summary-label"><i class="fas fa-wallet"></i> Kas</div>
        <div class="fs-summary-value">Rp {{ number_format((float) ($summary['kas_total'] ?? 0), 2, ',', '.') }}</div>
        <div class="fs-summary-help">Saldo akun kas pada invoice jurnal yang sudah diposting.</div>
    </div>
    <div class="fs-summary-card">
        <div class="fs-summary-label"><i class="fas fa-building"></i> Aset</div>
        <div class="fs-summary-value">Rp {{ number_format((float) ($summary['aset_total'] ?? 0), 2, ',', '.') }}</div>
        <div class="fs-summary-help">Aset yang dikenali dari tipe akun atau akun asset terdaftar.</div>
    </div>
    <div class="fs-summary-card">
        <div class="fs-summary-label"><i class="fas fa-layer-group"></i> Total Sisi Aset</div>
        <div class="fs-summary-value">Rp {{ number_format((float) ($summary['asset_side_total'] ?? 0), 2, ',', '.') }}</div>
        <div class="fs-summary-help">{{ number_format((int) ($summary['account_count'] ?? 0), 0, ',', '.') }} akun masuk ke lembar saldo.</div>
    </div>
</div>

@if($uncategorizedCount > 0)
    <div class="fs-note-card">
        <i class="fas fa-exclamation-triangle"></i>
        <div>{{ number_format($uncategorizedCount, 0, ',', '.') }} akun jurnal tidak masuk ke kategori liabilitas, piutang, kas, atau aset sehingga tidak ditampilkan di lembar saldo.</div>
    </div>
@endif

@if($hasRows)
    <div class="fs-section-grid">
        @foreach($sections as $section)
            @php
                $meta = $sectionMeta[$section['key']] ?? ['icon' => 'fa-book', 'badge' => 'fs-blue'];
            @endphp
            <div class="fs-section-card">
                <div class="fs-section-head d-flex justify-content-between align-items-center flex-wrap" style="gap:.8rem;">
                    <div class="fs-section-title">
                        <span class="fs-section-icon"><i class="fas {{ $meta['icon'] }}"></i></span>
                        <span>{{ $section['label'] }}</span>
                    </div>
                    <div class="fs-section-total">
                        <span class="fs-badge {{ $meta['badge'] }}">
                            <i class="fas fa-coins"></i>
                            Rp {{ number_format((float) ($section['total'] ?? 0), 2, ',', '.') }}
                        </span>
                    </div>
                </div>
                <div class="fs-table-wrap">
                    <table class="fs-table">
                        <thead>
                            <tr>
                                <th style="width:130px;">Kode</th>
                                <th>Nama Akun</th>
                                <th style="width:160px;">Tipe</th>
                                <th style="width:170px; text-align:right;">Saldo</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($section['rows'] as $row)
                                <tr>
                                    <td><strong>{{ $row['account_code'] }}</strong></td>
                                    <td>{{ $row['account_name'] }}</td>
                                    <td>
                                        <span class="fs-badge {{ $meta['badge'] }}">
                                            <i class="fas fa-tag"></i>
                                            {{ $row['finance_type'] !== '' ? str_replace('_', ' ', $row['finance_type']) : strtoupper($section['label']) }}
                                        </span>
                                    </td>
                                    <td class="fs-amount">Rp {{ number_format((float) $row['balance'], 2, ',', '.') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" style="text-align:center; color:var(--fs-muted);">Belum ada data untuk kategori ini.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @endforeach
    </div>
@else
    <div class="fs-empty-card">
        <i class="fas fa-inbox"></i>
        <h4>Belum ada data lembar saldo</h4>
        <div>Pastikan invoice finance sudah berstatus <strong>POSTED</strong> dan akun sudah dipetakan ke kategori yang sesuai.</div>
    </div>
@endif
@endsection
