@extends('layouts.app')

@section('content')
@php
    $incomeRows = $report['income_rows'] ?? [];
    $expenseRows = $report['expense_rows'] ?? [];
    $totals = $report['totals'] ?? ['income' => 0, 'expense' => 0, 'net_result' => 0];
    $hasRows = count($incomeRows) > 0 || count($expenseRows) > 0;
    $baseFilterQuery = $baseFilterQuery ?? ($filterQuery ?? []);
@endphp

<style>
    @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');

    :root {
        --pl-blue: #1d4ed8;
        --pl-blue-dark: #1e3a8a;
        --pl-green: #059669;
        --pl-green-soft: rgba(16, 185, 129, 0.12);
        --pl-red: #dc2626;
        --pl-red-soft: rgba(239, 68, 68, 0.12);
        --pl-amber: #d97706;
        --pl-amber-soft: rgba(245, 158, 11, 0.12);
        --pl-bg: #f0f4fd;
        --pl-card: #ffffff;
        --pl-text: #0f172a;
        --pl-muted: #64748b;
        --pl-border: rgba(37, 99, 235, 0.10);
        --pl-shadow: 0 10px 32px rgba(15, 23, 42, 0.08), 0 4px 14px rgba(37, 99, 235, 0.06);
        --pl-radius: 18px;
        --pl-radius-sm: 12px;
    }

    body, .content-wrapper {
        background: var(--pl-bg) !important;
        font-family: 'Plus Jakarta Sans', sans-serif !important;
    }

    .pl-page-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        flex-wrap: wrap;
        margin-bottom: 1.5rem;
    }
    .pl-page-title {
        display: flex;
        align-items: center;
        gap: 0.9rem;
    }
    .pl-title-icon {
        width: 50px;
        height: 50px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, var(--pl-blue), var(--pl-blue-dark));
        color: #fff;
        font-size: 1.2rem;
        box-shadow: var(--pl-shadow);
    }
    .pl-page-title h1 {
        margin: 0;
        color: var(--pl-text);
        font-size: 1.4rem;
        font-weight: 800;
    }
    .pl-page-title p {
        margin: 0.15rem 0 0;
        color: var(--pl-muted);
        font-size: 0.82rem;
        font-weight: 500;
    }
    .pl-nav {
        display: flex;
        flex-wrap: wrap;
        gap: 0.55rem;
    }
    .pl-nav-link,
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
    .pl-nav-link:hover,
    .fs-btn:hover {
        text-decoration: none;
        transform: translateY(-1px);
    }
    .pl-nav-link.primary,
    .fs-btn-primary {
        background: linear-gradient(135deg, var(--pl-blue), #2563eb);
        color: #fff;
        box-shadow: 0 8px 22px rgba(37, 99, 235, 0.24);
    }
    .pl-nav-link.muted,
    .fs-btn-muted {
        background: #fff;
        color: var(--pl-muted);
        border-color: var(--pl-border);
    }

    .fs-filter-card,
    .pl-summary-card,
    .pl-section-card,
    .pl-empty-card {
        background: var(--pl-card);
        border: 1px solid var(--pl-border);
        border-radius: var(--pl-radius);
        box-shadow: var(--pl-shadow);
    }
    .fs-filter-head,
    .pl-section-head {
        padding: 1rem 1.2rem;
        border-bottom: 1px solid var(--pl-border);
    }
    .fs-filter-head {
        background: linear-gradient(135deg, var(--pl-blue-dark), var(--pl-blue));
        border-bottom: none;
        border-radius: var(--pl-radius) var(--pl-radius) 0 0;
    }
    .fs-filter-title,
    .pl-section-title {
        display: flex;
        align-items: center;
        gap: 0.65rem;
        font-size: 0.9rem;
        font-weight: 700;
    }
    .fs-filter-title { color: #fff; }
    .pl-section-title { color: var(--pl-text); }
    .fs-filter-icon,
    .pl-section-icon {
        width: 30px;
        height: 30px;
        border-radius: 10px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }
    .fs-filter-icon { background: rgba(255, 255, 255, 0.16); }
    .pl-section-icon { background: rgba(37, 99, 235, 0.08); color: var(--pl-blue); }
    .fs-filter-body {
        padding: 1.1rem 1.2rem 0.3rem;
    }
    .fs-field { margin-bottom: 1rem; }
    .fs-label {
        display: flex;
        align-items: center;
        gap: 0.35rem;
        margin-bottom: 0.4rem;
        font-size: 0.7rem;
        font-weight: 800;
        letter-spacing: 0.06em;
        text-transform: uppercase;
        color: var(--pl-muted);
    }
    .fs-control {
        width: 100%;
        border: 1.5px solid rgba(148, 163, 184, 0.18);
        border-radius: var(--pl-radius-sm);
        padding: 0.65rem 0.85rem;
        font-size: 0.84rem;
        color: var(--pl-text);
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

    .pl-summary-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 0.9rem;
        margin: 1.2rem 0;
    }
    .pl-summary-card {
        padding: 1rem 1.1rem;
    }
    .pl-summary-label {
        display: flex;
        align-items: center;
        gap: 0.45rem;
        color: var(--pl-muted);
        font-size: 0.72rem;
        font-weight: 800;
        letter-spacing: 0.06em;
        text-transform: uppercase;
        margin-bottom: 0.45rem;
    }
    .pl-summary-value {
        color: var(--pl-text);
        font-size: 1.3rem;
        font-weight: 800;
        line-height: 1.15;
    }
    .pl-summary-value.positive { color: var(--pl-green); }
    .pl-summary-value.negative { color: var(--pl-red); }
    .pl-summary-help {
        margin-top: 0.35rem;
        color: var(--pl-muted);
        font-size: 0.75rem;
        font-weight: 500;
    }

    .pl-section-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(360px, 1fr));
        gap: 1rem;
    }
    .pl-table {
        width: 100%;
        border-collapse: collapse;
    }
    .pl-table th {
        background: #f8fbff;
        color: var(--pl-muted);
        font-size: 0.68rem;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        padding: 0.75rem 1rem;
        border-bottom: 1px solid var(--pl-border);
    }
    .pl-table td {
        padding: 0.78rem 1rem;
        font-size: 0.82rem;
        color: #334155;
        border-bottom: 1px solid rgba(148, 163, 184, 0.12);
    }
    .pl-table tbody tr:last-child td { border-bottom: none; }
    .pl-table tbody tr:hover td { background: rgba(37, 99, 235, 0.03); }
    .pl-amount {
        text-align: right;
        white-space: nowrap;
        font-weight: 800;
    }
    .pl-amount.income { color: var(--pl-green); }
    .pl-amount.expense { color: var(--pl-red); }
    .pl-total-row td {
        font-weight: 800;
        background: rgba(37, 99, 235, 0.04);
    }
    .pl-account-cell {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
    }
    .pl-account-name {
        min-width: 0;
        flex: 1 1 auto;
    }
    .pl-row-menu-btn {
        width: 30px;
        height: 30px;
        border: none;
        border-radius: 50%;
        background: transparent;
        color: var(--pl-muted);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.2s ease;
        flex-shrink: 0;
    }
    .pl-row-menu-btn::after { display: none; }
    .pl-row-menu-btn:hover {
        background: rgba(37, 99, 235, 0.08);
        color: var(--pl-blue);
    }
    .pl-row-menu {
        min-width: 180px;
        border-radius: 12px;
        border: 1px solid rgba(148, 163, 184, 0.18);
        box-shadow: 0 14px 30px rgba(15, 23, 42, 0.12);
        padding: 0.45rem;
    }
    .pl-row-menu .dropdown-item {
        border-radius: 8px;
        font-size: 0.78rem;
        font-weight: 700;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        color: var(--pl-text);
        padding: 0.55rem 0.7rem;
    }
    .pl-row-menu .dropdown-item:hover {
        background: rgba(37, 99, 235, 0.08);
        color: var(--pl-blue);
    }
    .pl-empty-card {
        margin-top: 1rem;
        padding: 2.5rem 1.2rem;
        text-align: center;
        color: var(--pl-muted);
    }
    .pl-empty-card i {
        font-size: 2.3rem;
        margin-bottom: 0.8rem;
        color: rgba(37, 99, 235, 0.28);
    }
    .pl-empty-card h4 {
        color: var(--pl-text);
        font-size: 1rem;
        font-weight: 700;
        margin-bottom: 0.35rem;
    }

    body.dark-mode,
    body.dark-mode .content-wrapper {
        background: var(--app-bg) !important;
    }
    body.dark-mode .fs-filter-card,
    body.dark-mode .pl-summary-card,
    body.dark-mode .pl-section-card,
    body.dark-mode .pl-empty-card {
        background: var(--app-surface) !important;
        border-color: var(--app-border) !important;
        box-shadow: var(--app-shadow) !important;
    }
    body.dark-mode .pl-page-title h1,
    body.dark-mode .pl-summary-value,
    body.dark-mode .pl-empty-card h4,
    body.dark-mode .pl-section-title {
        color: var(--app-text) !important;
    }
    body.dark-mode .pl-page-title p,
    body.dark-mode .fs-label,
    body.dark-mode .pl-summary-help,
    body.dark-mode .pl-summary-label,
    body.dark-mode .pl-table th {
        color: var(--app-text-muted) !important;
    }
    body.dark-mode .fs-control,
    body.dark-mode .pl-nav-link.muted,
    body.dark-mode .fs-btn-muted {
        background: var(--app-surface-soft) !important;
        border-color: var(--app-border) !important;
        color: var(--app-text) !important;
    }
    body.dark-mode .fs-control:focus {
        background: var(--app-surface) !important;
        border-color: rgba(96, 165, 250, 0.36) !important;
        box-shadow: 0 0 0 3px rgba(96, 165, 250, 0.14) !important;
    }
    body.dark-mode .fs-control option {
        background: var(--app-surface) !important;
        color: var(--app-text) !important;
    }
    body.dark-mode .pl-table th {
        background: var(--app-surface-soft) !important;
        border-color: var(--app-border) !important;
    }
    body.dark-mode .pl-table td {
        color: var(--app-text-soft) !important;
        border-color: var(--app-border) !important;
    }
    body.dark-mode .pl-table tbody tr:hover td {
        background: var(--app-row-hover) !important;
    }
    body.dark-mode .pl-total-row td {
        background: var(--app-surface-soft) !important;
        color: var(--app-text) !important;
    }
    body.dark-mode .pl-row-menu {
        background: var(--app-surface) !important;
        border-color: var(--app-border) !important;
        box-shadow: var(--app-shadow) !important;
    }
    body.dark-mode .pl-row-menu .dropdown-item {
        color: var(--app-text-soft) !important;
    }
    body.dark-mode .pl-row-menu .dropdown-item:hover {
        background: var(--app-surface-soft) !important;
        color: var(--app-text) !important;
    }
</style>

<div class="pl-page-header">
    <div class="pl-page-title">
        <div class="pl-title-icon"><i class="fas fa-chart-area"></i></div>
        <div>
            <h1>Laporan Laba Rugi</h1>
            <p>Ringkasan pemasukan dan pengeluaran pada periode {{ $periodLabel }}.</p>
        </div>
    </div>

    <div class="pl-nav">
        <a href="{{ route('finance.dashboard') }}" class="pl-nav-link muted">
            <i class="fas fa-arrow-left"></i> Dashboard
        </a>
        <a href="{{ route('finance.report.profit-loss.download', $filterQuery) }}" class="pl-nav-link primary">
            <i class="fas fa-file-pdf"></i> Download PDF
        </a>
        <a href="{{ route('finance.report.balance-sheet', $filterQuery) }}" class="pl-nav-link muted">
            <i class="fas fa-balance-scale"></i> Lembar Saldo
        </a>
        <a href="{{ route('finance.report.general-ledger', $filterQuery) }}" class="pl-nav-link muted">
            <i class="fas fa-book-open"></i> Buku Besar
        </a>
    </div>
</div>

@include('finance.partials.statement-filter', [
    'action' => route('finance.report.profit-loss'),
    'filters' => $filters,
    'showPerPage' => false,
])

<div class="pl-summary-grid">
    <div class="pl-summary-card">
        <div class="pl-summary-label"><i class="fas fa-arrow-trend-up"></i> Total Pemasukan</div>
        <div class="pl-summary-value positive">Rp {{ number_format((float) ($totals['income'] ?? 0), 2, ',', '.') }}</div>
        <div class="pl-summary-help">{{ number_format(count($incomeRows), 0, ',', '.') }} akun penghasilan masuk ke periode ini.</div>
    </div>
    <div class="pl-summary-card">
        <div class="pl-summary-label"><i class="fas fa-arrow-trend-down"></i> Total Pengeluaran</div>
        <div class="pl-summary-value negative">Rp {{ number_format((float) ($totals['expense'] ?? 0), 2, ',', '.') }}</div>
        <div class="pl-summary-help">{{ number_format(count($expenseRows), 0, ',', '.') }} akun pengeluaran tercatat di laporan ini.</div>
    </div>
    <div class="pl-summary-card">
        <div class="pl-summary-label"><i class="fas fa-scale-balanced"></i> Laba / Rugi Bersih</div>
        <div class="pl-summary-value {{ (float) ($totals['net_result'] ?? 0) >= 0 ? 'positive' : 'negative' }}">
            Rp {{ number_format((float) ($totals['net_result'] ?? 0), 2, ',', '.') }}
        </div>
        <div class="pl-summary-help">Hasil selisih pemasukan dan pengeluaran untuk filter yang aktif.</div>
    </div>
</div>

@if($hasRows)
    <div class="pl-section-grid">
        <div class="pl-section-card">
            <div class="pl-section-head">
                <div class="pl-section-title">
                    <span class="pl-section-icon" style="background:var(--pl-green-soft);color:var(--pl-green);">
                        <i class="fas fa-arrow-up"></i>
                    </span>
                    <span>Pemasukan</span>
                </div>
            </div>
            <div class="table-responsive">
                <table class="pl-table">
                    <thead>
                        <tr>
                            <th style="width:140px;">Kode</th>
                            <th>Nama Akun</th>
                            <th style="width:180px; text-align:right;">Nominal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($incomeRows as $row)
                            <tr>
                                <td><strong>{{ $row['account_code'] }}</strong></td>
                                <td>
                                    <div class="pl-account-cell">
                                        <span class="pl-account-name">{{ $row['account_name'] }}</span>
                                        <div class="dropdown">
                                            <button type="button" class="pl-row-menu-btn dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                <i class="fas fa-ellipsis-v"></i>
                                            </button>
                                            <div class="dropdown-menu dropdown-menu-right pl-row-menu">
                                                <a class="dropdown-item" href="{{ route('finance.report.general-ledger', array_merge($baseFilterQuery, ['account_code' => $row['account_code']])) }}">
                                                    <i class="fas fa-book-open"></i> Buku Besar
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="pl-amount income">Rp {{ number_format((float) $row['amount'], 2, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" style="text-align:center; color:var(--pl-muted);">Belum ada pemasukan pada periode ini.</td>
                            </tr>
                        @endforelse
                        <tr class="pl-total-row">
                            <td colspan="2">Total Pemasukan</td>
                            <td class="pl-amount income">Rp {{ number_format((float) ($totals['income'] ?? 0), 2, ',', '.') }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="pl-section-card">
            <div class="pl-section-head">
                <div class="pl-section-title">
                    <span class="pl-section-icon" style="background:var(--pl-red-soft);color:var(--pl-red);">
                        <i class="fas fa-arrow-down"></i>
                    </span>
                    <span>Pengeluaran</span>
                </div>
            </div>
            <div class="table-responsive">
                <table class="pl-table">
                    <thead>
                        <tr>
                            <th style="width:140px;">Kode</th>
                            <th>Nama Akun</th>
                            <th style="width:180px; text-align:right;">Nominal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($expenseRows as $row)
                            <tr>
                                <td><strong>{{ $row['account_code'] }}</strong></td>
                                <td>
                                    <div class="pl-account-cell">
                                        <span class="pl-account-name">{{ $row['account_name'] }}</span>
                                        <div class="dropdown">
                                            <button type="button" class="pl-row-menu-btn dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                <i class="fas fa-ellipsis-v"></i>
                                            </button>
                                            <div class="dropdown-menu dropdown-menu-right pl-row-menu">
                                                <a class="dropdown-item" href="{{ route('finance.report.general-ledger', array_merge($baseFilterQuery, ['account_code' => $row['account_code']])) }}">
                                                    <i class="fas fa-book-open"></i> Buku Besar
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="pl-amount expense">Rp {{ number_format((float) $row['amount'], 2, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" style="text-align:center; color:var(--pl-muted);">Belum ada pengeluaran pada periode ini.</td>
                            </tr>
                        @endforelse
                        <tr class="pl-total-row">
                            <td colspan="2">Total Pengeluaran</td>
                            <td class="pl-amount expense">Rp {{ number_format((float) ($totals['expense'] ?? 0), 2, ',', '.') }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@else
    <div class="pl-empty-card">
        <i class="fas fa-chart-line"></i>
        <h4>Belum ada data laba rugi</h4>
        <div>Pastikan jurnal sudah <strong>POSTED</strong> dan akun pemasukan atau pengeluaran sudah dipetakan di bagan akun.</div>
    </div>
@endif
@endsection
