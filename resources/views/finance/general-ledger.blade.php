@extends('layouts.app')

@section('content')
@php
    $summary = $report['summary'] ?? ['account_count' => 0, 'entry_count' => 0, 'total_debit' => 0, 'total_credit' => 0, 'balance_gap' => 0];
    $groups = $report['groups'] ?? [];
    $accounts = $report['accounts'] ?? null;
@endphp

<style>
    @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');

    :root {
        --gl-blue: #1d4ed8;
        --gl-blue-dark: #1e3a8a;
        --gl-cyan: #0891b2;
        --gl-green: #059669;
        --gl-green-soft: rgba(16, 185, 129, 0.12);
        --gl-red: #dc2626;
        --gl-red-soft: rgba(239, 68, 68, 0.12);
        --gl-amber: #d97706;
        --gl-bg: #f0f4fd;
        --gl-card: #ffffff;
        --gl-text: #0f172a;
        --gl-muted: #64748b;
        --gl-border: rgba(37, 99, 235, 0.10);
        --gl-shadow: 0 10px 32px rgba(15, 23, 42, 0.08), 0 4px 14px rgba(37, 99, 235, 0.06);
        --gl-radius: 18px;
        --gl-radius-sm: 12px;
    }

    body, .content-wrapper {
        background: var(--gl-bg) !important;
        font-family: 'Plus Jakarta Sans', sans-serif !important;
    }

    .gl-page-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        flex-wrap: wrap;
        margin-bottom: 1.5rem;
    }
    .gl-page-title {
        display: flex;
        align-items: center;
        gap: 0.9rem;
    }
    .gl-title-icon {
        width: 50px;
        height: 50px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, var(--gl-blue), var(--gl-blue-dark));
        color: #fff;
        font-size: 1.2rem;
        box-shadow: var(--gl-shadow);
    }
    .gl-page-title h1 {
        margin: 0;
        color: var(--gl-text);
        font-size: 1.4rem;
        font-weight: 800;
    }
    .gl-page-title p {
        margin: 0.15rem 0 0;
        color: var(--gl-muted);
        font-size: 0.82rem;
        font-weight: 500;
    }
    .gl-nav {
        display: flex;
        flex-wrap: wrap;
        gap: 0.55rem;
    }
    .gl-nav-link,
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
    .gl-nav-link:hover,
    .fs-btn:hover {
        text-decoration: none;
        transform: translateY(-1px);
    }
    .gl-nav-link.primary,
    .fs-btn-primary {
        background: linear-gradient(135deg, var(--gl-blue), #2563eb);
        color: #fff;
        box-shadow: 0 8px 22px rgba(37, 99, 235, 0.24);
    }
    .gl-nav-link.muted,
    .fs-btn-muted {
        background: #fff;
        color: var(--gl-muted);
        border-color: var(--gl-border);
    }

    .fs-filter-card,
    .gl-summary-card,
    .gl-ledger-card,
    .gl-empty-card {
        background: var(--gl-card);
        border: 1px solid var(--gl-border);
        border-radius: var(--gl-radius);
        box-shadow: var(--gl-shadow);
    }
    .fs-filter-head,
    .gl-ledger-head {
        padding: 1rem 1.2rem;
        border-bottom: 1px solid var(--gl-border);
    }
    .fs-filter-head {
        background: linear-gradient(135deg, var(--gl-blue-dark), var(--gl-blue));
        border-bottom: none;
        border-radius: var(--gl-radius) var(--gl-radius) 0 0;
    }
    .fs-filter-title,
    .gl-ledger-title {
        display: flex;
        align-items: center;
        gap: 0.65rem;
        font-size: 0.9rem;
        font-weight: 700;
    }
    .fs-filter-title { color: #fff; }
    .gl-ledger-title { color: var(--gl-text); }
    .fs-filter-icon,
    .gl-ledger-icon {
        width: 30px;
        height: 30px;
        border-radius: 10px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }
    .fs-filter-icon { background: rgba(255, 255, 255, 0.16); }
    .gl-ledger-icon { background: rgba(37, 99, 235, 0.08); color: var(--gl-blue); }
    .fs-filter-body { padding: 1.1rem 1.2rem 0.3rem; }
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
        color: var(--gl-muted);
    }
    .fs-control {
        width: 100%;
        border: 1.5px solid rgba(148, 163, 184, 0.18);
        border-radius: var(--gl-radius-sm);
        padding: 0.65rem 0.85rem;
        font-size: 0.84rem;
        color: var(--gl-text);
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

    .gl-summary-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 0.9rem;
        margin: 1.2rem 0;
    }
    .gl-summary-card {
        padding: 1rem 1.1rem;
    }
    .gl-summary-label {
        display: flex;
        align-items: center;
        gap: 0.45rem;
        color: var(--gl-muted);
        font-size: 0.72rem;
        font-weight: 800;
        letter-spacing: 0.06em;
        text-transform: uppercase;
        margin-bottom: 0.45rem;
    }
    .gl-summary-value {
        color: var(--gl-text);
        font-size: 1.25rem;
        font-weight: 800;
        line-height: 1.15;
    }
    .gl-summary-help {
        margin-top: 0.35rem;
        color: var(--gl-muted);
        font-size: 0.75rem;
        font-weight: 500;
    }

    .gl-ledger-list {
        display: grid;
        gap: 1rem;
    }
    .gl-ledger-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        align-items: center;
    }
    .gl-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        border-radius: 999px;
        padding: 0.25rem 0.65rem;
        font-size: 0.68rem;
        font-weight: 800;
        background: rgba(37, 99, 235, 0.10);
        color: var(--gl-blue);
    }
    .gl-badge.green { background: var(--gl-green-soft); color: var(--gl-green); }
    .gl-badge.red { background: var(--gl-red-soft); color: var(--gl-red); }

    .gl-ledger-head {
        display: flex;
        justify-content: space-between;
        gap: 0.8rem;
        flex-wrap: wrap;
    }
    .gl-ledger-sub {
        margin-top: 0.2rem;
        color: var(--gl-muted);
        font-size: 0.76rem;
        font-weight: 500;
    }
    .gl-ledger-totals {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 0.6rem;
        min-width: 320px;
    }
    .gl-ledger-total {
        background: #f8fbff;
        border-radius: 12px;
        padding: 0.7rem 0.8rem;
        border: 1px solid rgba(148, 163, 184, 0.14);
    }
    .gl-ledger-total label {
        display: block;
        margin-bottom: 0.2rem;
        color: var(--gl-muted);
        font-size: 0.66rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.06em;
    }
    .gl-ledger-total div {
        color: var(--gl-text);
        font-size: 0.82rem;
        font-weight: 800;
        white-space: nowrap;
    }

    .gl-table {
        width: 100%;
        border-collapse: collapse;
    }
    .gl-table th {
        background: #f8fbff;
        color: var(--gl-muted);
        font-size: 0.68rem;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        padding: 0.75rem 1rem;
        border-bottom: 1px solid var(--gl-border);
    }
    .gl-table td {
        padding: 0.78rem 1rem;
        font-size: 0.81rem;
        color: #334155;
        border-bottom: 1px solid rgba(148, 163, 184, 0.12);
        vertical-align: top;
    }
    .gl-table tbody tr:last-child td { border-bottom: none; }
    .gl-table tbody tr:hover td { background: rgba(37, 99, 235, 0.03); }
    .gl-amount {
        text-align: right;
        white-space: nowrap;
        font-weight: 800;
    }
    .gl-amount.debit { color: var(--gl-green); }
    .gl-amount.credit { color: var(--gl-red); }
    .gl-amount.balance { color: var(--gl-blue); }
    .gl-entry-title {
        color: var(--gl-text);
        font-weight: 700;
        margin-bottom: 0.15rem;
    }
    .gl-entry-sub {
        color: var(--gl-muted);
        font-size: 0.74rem;
        line-height: 1.45;
    }
    .gl-pagination {
        margin-top: 1rem;
        padding: 0.95rem 1rem;
        background: var(--gl-card);
        border: 1px solid var(--gl-border);
        border-radius: var(--gl-radius);
        box-shadow: var(--gl-shadow);
    }
    .gl-pagination .pagination { margin: 0; }
    .gl-empty-card {
        margin-top: 1rem;
        padding: 2.5rem 1.2rem;
        text-align: center;
        color: var(--gl-muted);
    }
    .gl-empty-card i {
        font-size: 2.3rem;
        margin-bottom: 0.8rem;
        color: rgba(37, 99, 235, 0.28);
    }
    .gl-empty-card h4 {
        color: var(--gl-text);
        font-size: 1rem;
        font-weight: 700;
        margin-bottom: 0.35rem;
    }

    body.dark-mode,
    body.dark-mode .content-wrapper {
        background: var(--app-bg) !important;
    }
    body.dark-mode .fs-filter-card,
    body.dark-mode .gl-summary-card,
    body.dark-mode .gl-ledger-card,
    body.dark-mode .gl-empty-card,
    body.dark-mode .gl-pagination {
        background: var(--app-surface) !important;
        border-color: var(--app-border) !important;
        box-shadow: var(--app-shadow) !important;
    }
    body.dark-mode .gl-page-title h1,
    body.dark-mode .gl-summary-value,
    body.dark-mode .gl-empty-card h4,
    body.dark-mode .gl-ledger-title,
    body.dark-mode .gl-ledger-total div,
    body.dark-mode .gl-entry-title {
        color: var(--app-text) !important;
    }
    body.dark-mode .gl-page-title p,
    body.dark-mode .fs-label,
    body.dark-mode .gl-summary-help,
    body.dark-mode .gl-summary-label,
    body.dark-mode .gl-table th,
    body.dark-mode .gl-entry-sub,
    body.dark-mode .gl-ledger-sub,
    body.dark-mode .gl-ledger-total label {
        color: var(--app-text-muted) !important;
    }
    body.dark-mode .fs-control,
    body.dark-mode .gl-nav-link.muted,
    body.dark-mode .fs-btn-muted,
    body.dark-mode .gl-ledger-total {
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
    body.dark-mode .gl-table th {
        background: var(--app-surface-soft) !important;
        border-color: var(--app-border) !important;
    }
    body.dark-mode .gl-table td {
        color: var(--app-text-soft) !important;
        border-color: var(--app-border) !important;
    }
    body.dark-mode .gl-table tbody tr:hover td {
        background: var(--app-row-hover) !important;
    }
    body.dark-mode .gl-pagination .page-link {
        background: var(--app-surface-soft) !important;
        border-color: var(--app-border) !important;
        color: var(--app-text-soft) !important;
    }
    body.dark-mode .gl-pagination .page-item.active .page-link {
        background: var(--app-accent) !important;
        border-color: var(--app-accent) !important;
        color: #fff !important;
    }
    @media (max-width: 991px) {
        .gl-ledger-totals {
            grid-template-columns: 1fr;
            min-width: 100%;
        }
    }
</style>

<div class="gl-page-header">
    <div class="gl-page-title">
        <div class="gl-title-icon"><i class="fas fa-book-open"></i></div>
        <div>
            <h1>Buku Besar</h1>
            <p>Rincian jurnal keseluruhan per akun untuk periode {{ $periodLabel }}.</p>
        </div>
    </div>

    <div class="gl-nav">
        <a href="{{ route('finance.dashboard') }}" class="gl-nav-link muted">
            <i class="fas fa-arrow-left"></i> Dashboard
        </a>
        <a href="{{ route('finance.report.general-ledger.download', $filterQuery) }}" class="gl-nav-link primary">
            <i class="fas fa-file-pdf"></i> Download PDF
        </a>
        <a href="{{ route('finance.report.balance-sheet', $filterQuery) }}" class="gl-nav-link muted">
            <i class="fas fa-balance-scale"></i> Lembar Saldo
        </a>
        <a href="{{ route('finance.report.profit-loss', $filterQuery) }}" class="gl-nav-link muted">
            <i class="fas fa-chart-area"></i> Laba Rugi
        </a>
    </div>
</div>

@include('finance.partials.statement-filter', [
    'action' => route('finance.report.general-ledger'),
    'filters' => $filters,
    'showPerPage' => true,
])

<div class="gl-summary-grid">
    <div class="gl-summary-card">
        <div class="gl-summary-label"><i class="fas fa-sitemap"></i> Jumlah Akun</div>
        <div class="gl-summary-value">{{ number_format((int) ($summary['account_count'] ?? 0), 0, ',', '.') }}</div>
        <div class="gl-summary-help">Akun unik yang muncul dalam jurnal sesuai filter aktif.</div>
    </div>
    <div class="gl-summary-card">
        <div class="gl-summary-label"><i class="fas fa-list-ul"></i> Baris Jurnal</div>
        <div class="gl-summary-value">{{ number_format((int) ($summary['entry_count'] ?? 0), 0, ',', '.') }}</div>
        <div class="gl-summary-help">Total baris debit dan kredit yang masuk ke buku besar.</div>
    </div>
    <div class="gl-summary-card">
        <div class="gl-summary-label"><i class="fas fa-arrow-up"></i> Total Debit</div>
        <div class="gl-summary-value">Rp {{ number_format((float) ($summary['total_debit'] ?? 0), 2, ',', '.') }}</div>
        <div class="gl-summary-help">Akumulasi sisi debit untuk periode ini.</div>
    </div>
    <div class="gl-summary-card">
        <div class="gl-summary-label"><i class="fas fa-arrow-down"></i> Total Kredit</div>
        <div class="gl-summary-value">Rp {{ number_format((float) ($summary['total_credit'] ?? 0), 2, ',', '.') }}</div>
        <div class="gl-summary-help">Akumulasi sisi kredit untuk periode ini.</div>
    </div>
    <div class="gl-summary-card">
        <div class="gl-summary-label"><i class="fas fa-scale-balanced"></i> Selisih</div>
        <div class="gl-summary-value" style="color: {{ (float) ($summary['balance_gap'] ?? 0) === 0.0 ? 'var(--gl-green)' : 'var(--gl-red)' }};">
            Rp {{ number_format((float) ($summary['balance_gap'] ?? 0), 2, ',', '.') }}
        </div>
        <div class="gl-summary-help">Idealnya bernilai 0 jika jurnal seimbang.</div>
    </div>
</div>

@if(!empty($groups))
    <div class="gl-ledger-list">
        @foreach($groups as $group)
            <div class="gl-ledger-card">
                <div class="gl-ledger-head">
                    <div>
                        <div class="gl-ledger-title">
                            <span class="gl-ledger-icon"><i class="fas fa-book"></i></span>
                            <span>{{ $group['account_code'] }} - {{ $group['account_name'] }}</span>
                        </div>
                        <div class="gl-ledger-sub">
                            <div class="gl-ledger-meta">
                                <span class="gl-badge">
                                    <i class="fas fa-tag"></i>
                                    {{ $group['finance_type'] !== '' ? str_replace('_', ' ', $group['finance_type']) : 'TANPA TIPE' }}
                                </span>
                                <span class="gl-badge {{ $group['normal_side'] === 'CREDIT' ? 'red' : 'green' }}">
                                    <i class="fas fa-arrows-alt-h"></i>
                                    Saldo Normal {{ $group['normal_side'] }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="gl-ledger-totals">
                        <div class="gl-ledger-total">
                            <label>Total Debit</label>
                            <div>Rp {{ number_format((float) $group['total_debit'], 2, ',', '.') }}</div>
                        </div>
                        <div class="gl-ledger-total">
                            <label>Total Kredit</label>
                            <div>Rp {{ number_format((float) $group['total_credit'], 2, ',', '.') }}</div>
                        </div>
                        <div class="gl-ledger-total">
                            <label>Saldo Akhir</label>
                            <div>Rp {{ number_format((float) $group['closing_balance'], 2, ',', '.') }}</div>
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="gl-table">
                        <thead>
                            <tr>
                                <th style="width:120px;">Tanggal</th>
                                <th style="width:150px;">No Jurnal</th>
                                <th style="width:180px;">Nama Jurnal</th>
                                <th>Uraian</th>
                                <th style="width:150px; text-align:right;">Debit</th>
                                <th style="width:150px; text-align:right;">Kredit</th>
                                <th style="width:160px; text-align:right;">Saldo</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($group['entries'] as $entry)
                                <tr>
                                    <td>{{ \Carbon\Carbon::parse($entry['accounting_date'])->format('d/m/Y') }}</td>
                                    <td><strong>{{ $entry['invoice_no'] }}</strong></td>
                                    <td>{{ $entry['journal_name'] }}</td>
                                    <td>
                                        <div class="gl-entry-title">{{ $entry['label'] }}</div>
                                        <div class="gl-entry-sub">
                                            @if(!empty($entry['partner_name']))
                                                Partner: {{ $entry['partner_name'] }}<br>
                                            @endif
                                            @if(!empty($entry['reference']))
                                                Ref: {{ $entry['reference'] }}<br>
                                            @endif
                                            @if(!empty($entry['analytic_distribution']))
                                                Analitik: {{ $entry['analytic_distribution'] }}
                                            @endif
                                        </div>
                                    </td>
                                    <td class="gl-amount debit">Rp {{ number_format((float) $entry['debit'], 2, ',', '.') }}</td>
                                    <td class="gl-amount credit">Rp {{ number_format((float) $entry['credit'], 2, ',', '.') }}</td>
                                    <td class="gl-amount balance">Rp {{ number_format((float) $entry['running_balance'], 2, ',', '.') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" style="text-align:center; color:var(--gl-muted);">Belum ada baris jurnal untuk akun ini.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @endforeach
    </div>

    @if($accounts && method_exists($accounts, 'links') && $accounts->hasPages())
        <div class="gl-pagination">
            {{ $accounts->appends(request()->query())->links() }}
        </div>
    @endif
@else
    <div class="gl-empty-card">
        <i class="fas fa-book-dead"></i>
        <h4>Belum ada data buku besar</h4>
        <div>Pastikan jurnal finance sudah <strong>POSTED</strong> agar muncul di buku besar.</div>
    </div>
@endif
@endsection
