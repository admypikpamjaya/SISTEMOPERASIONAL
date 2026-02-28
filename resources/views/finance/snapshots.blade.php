@extends('layouts.app')

@section('content')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=DM+Mono:wght@400;500&display=swap');

    :root {
        --blue-primary: #1a56db;
        --blue-dark: #1e3a8a;
        --blue-deeper: #0f2460;
        --blue-mid: #2563eb;
        --blue-light: #3b82f6;
        --accent-cyan: #06b6d4;
        --accent-green: #10b981;
        --accent-red: #ef4444;
        --accent-amber: #f59e0b;
        --surface-bg: #f0f4fd;
        --surface-card: #ffffff;
        --text-primary: #0f172a;
        --text-secondary: #475569;
        --text-muted: #94a3b8;
        --border-light: rgba(37, 99, 235, 0.10);
        --border-table: #e2e8f0;
        --shadow-sm: 0 1px 3px rgba(15,23,42,0.07);
        --shadow-md: 0 4px 16px rgba(15,23,42,0.09), 0 2px 8px rgba(37,99,235,0.07);
        --shadow-lg: 0 10px 40px rgba(15,23,42,0.13), 0 4px 16px rgba(37,99,235,0.10);
        --radius-sm: 10px;
        --radius-md: 14px;
        --radius-lg: 20px;
    }

    body, .content-wrapper { background: var(--surface-bg) !important; font-family: 'Plus Jakarta Sans', sans-serif !important; }

    /* ── Page Header ──────────────────────────── */
    .sfr-page-header {
        display: flex; align-items: center; justify-content: space-between;
        margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;
        animation: fadeDown 0.45s ease both;
    }
    .sfr-header-left { display: flex; align-items: center; gap: 0.9rem; }
    .sfr-header-icon {
        width: 48px; height: 48px; border-radius: var(--radius-sm);
        background: linear-gradient(135deg, var(--blue-primary), var(--blue-light));
        display: flex; align-items: center; justify-content: center;
        color: white; font-size: 1.25rem; box-shadow: var(--shadow-md); flex-shrink: 0;
    }
    .sfr-header-title { font-size: 1.35rem; font-weight: 800; color: var(--text-primary); margin: 0; letter-spacing: -0.02em; line-height: 1.2; }
    .sfr-header-sub { font-size: 0.8rem; color: var(--text-muted); margin: 0.1rem 0 0; font-weight: 500; }

    /* ── Action Button ────────────────────────── */
    .btn-sfr-action {
        display: inline-flex; align-items: center; gap: 0.4rem;
        background: linear-gradient(135deg, var(--accent-green), #059669);
        color: white; font-size: 0.82rem; font-weight: 700;
        padding: 0.55rem 1.1rem; border-radius: var(--radius-sm);
        text-decoration: none; transition: all 0.25s;
        box-shadow: 0 3px 10px rgba(16,185,129,0.35);
    }
    .btn-sfr-action:hover { transform: translateY(-1px); box-shadow: 0 6px 20px rgba(16,185,129,0.4); color: white; text-decoration: none; }

    /* ── Filter Card ──────────────────────────── */
    .sfr-filter-card {
        background: white; border-radius: var(--radius-lg);
        box-shadow: var(--shadow-md); border: 1px solid var(--border-light);
        overflow: hidden; margin-bottom: 1.25rem;
        animation: fadeUp 0.5s ease both;
    }
    .sfr-filter-header {
        display: flex; align-items: center; justify-content: space-between;
        padding: 1rem 1.25rem; border-bottom: 1px solid var(--border-light);
        background: linear-gradient(135deg, var(--blue-deeper), var(--blue-dark));
    }
    .sfr-filter-header-title {
        display: flex; align-items: center; gap: 0.6rem;
        font-size: 0.9rem; font-weight: 700; color: white; margin: 0;
    }
    .sfr-filter-header-title .fh-icon {
        width: 28px; height: 28px; border-radius: 8px;
        background: rgba(255,255,255,0.15); display: flex; align-items: center;
        justify-content: center; font-size: 0.75rem; color: white;
    }
    .sfr-filter-body { padding: 1.25rem 1.25rem 0.5rem; }

    /* ── Form Controls ────────────────────────── */
    .sfr-form-group { margin-bottom: 1rem; }
    .sfr-label {
        display: flex; align-items: center; gap: 0.3rem;
        font-size: 0.72rem; font-weight: 700; text-transform: uppercase;
        letter-spacing: 0.07em; color: var(--text-muted); margin-bottom: 0.4rem;
    }
    .sfr-label i { font-size: 0.65rem; color: var(--blue-primary); }
    .sfr-control {
        width: 100%; border: 1.5px solid var(--border-table); border-radius: var(--radius-sm);
        padding: 0.5rem 0.75rem; font-size: 0.83rem; font-family: 'Plus Jakarta Sans', sans-serif;
        color: var(--text-primary); background: white; transition: all 0.2s;
        appearance: none; -webkit-appearance: none;
    }
    .sfr-control:focus {
        outline: none; border-color: var(--blue-primary);
        box-shadow: 0 0 0 3px rgba(37,99,235,0.12);
    }
    select.sfr-control {
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%2394a3b8' stroke-width='2.5'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
        background-repeat: no-repeat; background-position: right 0.75rem center;
        padding-right: 2rem;
    }
    .sfr-control:disabled { background: #f8fafc; color: var(--text-muted); cursor: not-allowed; }

    /* ── Filter Buttons ───────────────────────── */
    .sfr-filter-actions { display: flex; align-items: flex-end; gap: 0.6rem; padding-bottom: 1rem; }
    .btn-apply {
        display: inline-flex; align-items: center; gap: 0.4rem;
        background: linear-gradient(135deg, var(--blue-primary), var(--blue-mid));
        color: white; font-size: 0.82rem; font-weight: 700;
        padding: 0.55rem 1.2rem; border-radius: var(--radius-sm);
        border: none; cursor: pointer; transition: all 0.2s;
        box-shadow: 0 3px 10px rgba(37,99,235,0.35); font-family: inherit;
    }
    .btn-apply:hover { transform: translateY(-1px); box-shadow: 0 6px 18px rgba(37,99,235,0.4); }
    .btn-reset {
        display: inline-flex; align-items: center; gap: 0.4rem;
        background: white; border: 1.5px solid var(--border-table);
        color: var(--text-secondary); font-size: 0.82rem; font-weight: 600;
        padding: 0.5rem 1rem; border-radius: var(--radius-sm);
        text-decoration: none; transition: all 0.2s;
    }
    .btn-reset:hover { border-color: var(--blue-light); color: var(--text-primary); text-decoration: none; }

    /* ── Summary Cards ────────────────────────── */
    .sfr-summary-grid {
        display: grid; grid-template-columns: repeat(4, 1fr);
        gap: 0.9rem; margin-bottom: 1.25rem;
    }
    @media(max-width: 768px) { .sfr-summary-grid { grid-template-columns: repeat(2, 1fr); } }
    @media(max-width: 480px) { .sfr-summary-grid { grid-template-columns: 1fr; } }
    .sfr-summary-card {
        background: white; border-radius: var(--radius-md);
        border: 1px solid var(--border-light); padding: 1rem 1.1rem;
        box-shadow: var(--shadow-sm); transition: box-shadow 0.2s;
        animation: fadeUp 0.55s ease both; position: relative; overflow: hidden;
    }
    .sfr-summary-card:hover { box-shadow: var(--shadow-md); }
    .sfr-summary-card::before {
        content: ''; position: absolute; top: 0; left: 0; right: 0;
        height: 3px; border-radius: var(--radius-md) var(--radius-md) 0 0;
    }
    .sfr-summary-card.sc-count::before   { background: linear-gradient(90deg, var(--blue-primary), var(--accent-cyan)); }
    .sfr-summary-card.sc-opening::before { background: linear-gradient(90deg, var(--accent-amber), #fbbf24); }
    .sfr-summary-card.sc-ending::before  { background: linear-gradient(90deg, var(--blue-mid), var(--blue-light)); }
    .sfr-summary-card.sc-surplus::before { background: linear-gradient(90deg, var(--accent-green), #34d399); }
    .sc-label {
        font-size: 0.68rem; font-weight: 700; text-transform: uppercase;
        letter-spacing: 0.08em; color: var(--text-muted); margin-bottom: 0.35rem;
        display: flex; align-items: center; gap: 0.35rem;
    }
    .sc-icon {
        width: 20px; height: 20px; border-radius: 6px;
        display: flex; align-items: center; justify-content: center; font-size: 0.6rem;
    }
    .sc-icon-count   { background: rgba(37,99,235,0.1); color: var(--blue-primary); }
    .sc-icon-opening { background: rgba(245,158,11,0.1); color: var(--accent-amber); }
    .sc-icon-ending  { background: rgba(37,99,235,0.1); color: var(--blue-primary); }
    .sc-icon-surplus { background: rgba(16,185,129,0.1); color: var(--accent-green); }
    .sc-value {
        font-size: 1.25rem; font-weight: 400; color: var(--text-primary);
        font-family: 'Plus Jakarta Sans', sans-serif; line-height: 1.1; letter-spacing: -0.01em;
    }
    .sc-value.blue   { color: var(--blue-primary); }
    .sc-value.green  { color: var(--accent-green); }
    .sc-value.red    { color: var(--accent-red); }
    .sc-value.big    { font-size: 1.6rem; }

    /* ── Table Card ───────────────────────────── */
    .sfr-table-card {
        background: white; border-radius: var(--radius-lg);
        box-shadow: var(--shadow-md); border: 1px solid var(--border-light);
        overflow: hidden; animation: fadeUp 0.6s ease both;
    }
    .sfr-table-header {
        display: flex; align-items: center; justify-content: space-between;
        padding: 1rem 1.25rem; border-bottom: 1px solid var(--border-light);
        background: white;
    }
    .sfr-table-title {
        display: flex; align-items: center; gap: 0.6rem;
        font-size: 0.9rem; font-weight: 700; color: var(--text-primary); margin: 0;
    }
    .sfr-table-title .tt-icon {
        width: 28px; height: 28px; border-radius: 8px;
        background: rgba(37,99,235,0.1); display: flex; align-items: center;
        justify-content: center; font-size: 0.7rem; color: var(--blue-primary);
    }

    /* ── Data Table ───────────────────────────── */
    .sfr-table { width: 100%; border-collapse: collapse; font-size: 0.82rem; }
    .sfr-table th {
        background: #f8fafc; color: var(--text-muted);
        font-size: 0.68rem; font-weight: 700; text-transform: uppercase;
        letter-spacing: 0.07em; padding: 0.7rem 1rem;
        border-bottom: 2px solid var(--border-table); white-space: nowrap;
    }
    .sfr-table td {
        padding: 0.7rem 1rem; border-bottom: 1px solid var(--border-table);
        color: var(--text-secondary); vertical-align: middle;
    }
    .sfr-table tbody tr:last-child td { border-bottom: none; }
    .sfr-table tbody tr:hover td { background: rgba(37,99,235,0.025); }

    /* ── Badges ───────────────────────────────── */
    .badge-type {
        display: inline-flex; align-items: center; gap: 0.3rem;
        border-radius: 999px; padding: 0.22rem 0.65rem;
        font-size: 0.68rem; font-weight: 700; letter-spacing: 0.05em;
        white-space: nowrap;
    }
    .badge-type.daily    { background: rgba(245,158,11,0.1);  color: #92400e; }
    .badge-type.monthly  { background: rgba(37,99,235,0.1);   color: var(--blue-dark); }
    .badge-type.yearly   { background: rgba(139,92,246,0.1);  color: #5b21b6; }
    .badge-type.all      { background: rgba(16,185,129,0.1);  color: #065f46; }
    .badge-version {
        display: inline-flex; align-items: center; gap: 0.2rem;
        background: #f1f5f9; color: var(--text-secondary);
        font-size: 0.7rem; font-weight: 600;
        font-family: 'Plus Jakarta Sans', sans-serif;
        padding: 0.2rem 0.55rem; border-radius: 999px;
    }
    .badge-version i { font-size: 0.55rem; color: var(--blue-primary); }

    /* ── Amount cells ─────────────────────────── */
    .amount-cell {
        font-family: 'Plus Jakarta Sans', sans-serif;
        font-size: 0.82rem; font-weight: 400; white-space: nowrap;
        color: var(--text-secondary);
    }
    .amount-cell.positive { color: var(--accent-green); }
    .amount-cell.negative { color: var(--accent-red); }

    /* ── Preview Button ───────────────────────── */
    .btn-preview {
        display: inline-flex; align-items: center; gap: 0.3rem;
        background: rgba(37,99,235,0.08); color: var(--blue-primary);
        border: 1px solid rgba(37,99,235,0.2); border-radius: 8px;
        font-size: 0.75rem; font-weight: 700; padding: 0.35rem 0.75rem;
        text-decoration: none; transition: all 0.2s; white-space: nowrap;
    }
    .btn-preview:hover { background: var(--blue-primary); color: white; text-decoration: none; }
    .sfr-actions {
        display: flex; align-items: center; gap: 0.35rem; flex-wrap: wrap;
    }
    .btn-action {
        display: inline-flex; align-items: center; gap: 0.3rem;
        border: 1px solid transparent; border-radius: 8px;
        font-size: 0.75rem; font-weight: 700; padding: 0.35rem 0.75rem;
        text-decoration: none; transition: all 0.2s; white-space: nowrap;
        background: white;
        font-family: 'Plus Jakarta Sans', sans-serif;
    }
    .btn-edit {
        color: var(--accent-amber);
        border-color: rgba(245,158,11,0.28);
        background: rgba(245,158,11,0.08);
    }
    .btn-edit:hover { background: var(--accent-amber); color: white; text-decoration: none; }
    .btn-delete {
        color: var(--accent-red);
        border-color: rgba(239,68,68,0.28);
        background: rgba(239,68,68,0.08);
        cursor: pointer;
    }
    .btn-delete:hover { background: var(--accent-red); color: white; }
    .sfr-actions form { margin: 0; }

    /* ── Comparison Cell ──────────────────────── */
    .comp-label { font-size: 0.72rem; font-weight: 700; color: var(--text-primary); margin-bottom: 0.15rem; }
    .comp-row { font-size: 0.72rem; font-weight: 600; display: flex; align-items: center; gap: 0.25rem; }
    .comp-row.up   { color: var(--accent-green); }
    .comp-row.down { color: var(--accent-red); }
    .comp-none { font-size: 0.75rem; color: var(--text-muted); font-style: italic; }

    /* ── Empty State ──────────────────────────── */
    .sfr-empty-state {
        padding: 3rem 1rem; text-align: center;
    }
    .sfr-empty-icon {
        width: 56px; height: 56px; border-radius: var(--radius-md);
        background: rgba(37,99,235,0.07); display: flex; align-items: center;
        justify-content: center; font-size: 1.4rem; color: var(--text-muted);
        margin: 0 auto 1rem;
    }
    .sfr-empty-text { font-size: 0.85rem; color: var(--text-muted); font-weight: 500; }

    /* ── Table Footer / Pagination ────────────── */
    .sfr-table-footer {
        padding: 0.75rem 1.25rem; border-top: 1px solid var(--border-light);
        background: #fafbff;
    }
    .sfr-table-footer .pagination { margin: 0; }
    .sfr-table-footer .page-link {
        border-radius: 8px; font-size: 0.78rem; font-weight: 600;
        color: var(--text-secondary); border-color: var(--border-table);
        margin: 0 1px;
    }
    .sfr-table-footer .page-item.active .page-link {
        background: var(--blue-primary); border-color: var(--blue-primary); color: white;
    }

    /* ── Alert ────────────────────────────────── */
    .sfr-alert-empty {
        display: flex; align-items: center; gap: 0.7rem;
        background: rgba(245,158,11,0.08); border: 1px solid rgba(245,158,11,0.2);
        border-radius: var(--radius-md); padding: 0.9rem 1.1rem;
        font-size: 0.83rem; font-weight: 500; color: #92400e; margin: 1.25rem;
    }

    /* ── Animations ───────────────────────────── */
    @keyframes fadeUp   { from { opacity:0; transform:translateY(14px); } to { opacity:1; transform:translateY(0); } }
    @keyframes fadeDown { from { opacity:0; transform:translateY(-10px); } to { opacity:1; transform:translateY(0); } }
    .anim-d1 { animation-delay: 0.05s; }
    .anim-d2 { animation-delay: 0.10s; }
    .anim-d3 { animation-delay: 0.15s; }
    .anim-d4 { animation-delay: 0.20s; }
</style>

@php
    $periodType = strtoupper((string) ($filters['period_type'] ?? 'MONTHLY'));
    $reportDate = (string) ($filters['report_date'] ?? now()->toDateString());
    $month = (int) ($filters['month'] ?? now()->month);
    $year = (int) ($filters['year'] ?? now()->year);
    $comparisonType = strtoupper((string) ($filters['comparison_type'] ?? 'NONE'));
    $comparisonOffset = (int) ($filters['comparison_offset'] ?? 1);
    $comparisonDate = (string) ($filters['comparison_date'] ?? now()->toDateString());
    $perPage = (int) request('per_page', 20);
    $totalEndingBalance = (float) data_get($totals ?? [], 'total_ending_balance', 0);
    $totalOpeningBalance = (float) data_get($totals ?? [], 'total_opening_balance', 0);
    $totalNetResult = (float) data_get($totals ?? [], 'total_net_result', 0);
    $totalCount = (int) data_get($totals ?? [], 'count', 0);
@endphp

{{-- ── Page Header ──────────────────────────────────── --}}
<div class="sfr-page-header">
    <div class="sfr-header-left">
        <div class="sfr-header-icon"><i class="fas fa-chart-pie"></i></div>
        <div>
            <h1 class="sfr-header-title">Snapshot Finance Report</h1>
            <p class="sfr-header-sub">Monitoring &amp; Snapshot Laporan Keuangan</p>
        </div>
    </div>
    <a href="{{ route('finance.report.index') }}" class="btn-sfr-action">
        <i class="fas fa-plus"></i> Input Finance Report
    </a>
</div>

{{-- ── Filter Card ──────────────────────────────────── --}}
<div class="sfr-filter-card">
    <div class="sfr-filter-header">
        <h3 class="sfr-filter-header-title">
            <span class="fh-icon"><i class="fas fa-filter"></i></span>
            Filter Snapshot Finance
        </h3>
    </div>
    <div class="sfr-filter-body">
        <form method="GET" action="{{ route('finance.report.snapshots') }}" id="snapshot-filter-form">
            <div class="row">
                <div class="col-md-2 sfr-form-group" id="period_type_col">
                    <label class="sfr-label"><i class="fas fa-calendar-alt"></i> Periode</label>
                    <select name="period_type" id="period_type" class="sfr-control">
                        <option value="ALL"     {{ $periodType === 'ALL'     ? 'selected' : '' }}>All Report</option>
                        <option value="DAILY"   {{ $periodType === 'DAILY'   ? 'selected' : '' }}>Harian</option>
                        <option value="MONTHLY" {{ $periodType === 'MONTHLY' ? 'selected' : '' }}>Bulanan</option>
                        <option value="YEARLY"  {{ $periodType === 'YEARLY'  ? 'selected' : '' }}>Tahunan</option>
                    </select>
                </div>

                <div class="col-md-3 sfr-form-group" id="report_date_group">
                    <label class="sfr-label"><i class="fas fa-calendar-day"></i> Sebagai Tanggal</label>
                    <input type="date" name="report_date" id="report_date" class="sfr-control" value="{{ $reportDate }}">
                </div>

                <div class="col-md-2 sfr-form-group" id="month_group">
                    <label class="sfr-label"><i class="fas fa-calendar-week"></i> Bulan</label>
                    <select name="month" id="month" class="sfr-control">
                        @for($m = 1; $m <= 12; $m++)
                            <option value="{{ $m }}" {{ $month === $m ? 'selected' : '' }}>{{ $m }}</option>
                        @endfor
                    </select>
                </div>

                <div class="col-md-2 sfr-form-group" id="year_group">
                    <label class="sfr-label"><i class="fas fa-calendar"></i> Tahun</label>
                    <input type="number" name="year" id="year" class="sfr-control" min="1900" max="2100" value="{{ $year }}">
                </div>

                <div class="col-md-3 sfr-form-group">
                    <label class="sfr-label"><i class="fas fa-code-branch"></i> Perbandingan</label>
                    <select name="comparison_type" id="comparison_type" class="sfr-control">
                        <option value="NONE"                  {{ $comparisonType === 'NONE'                  ? 'selected' : '' }}>Tidak ada</option>
                        <option value="PREVIOUS_PERIOD"       {{ $comparisonType === 'PREVIOUS_PERIOD'       ? 'selected' : '' }}>Periode Sebelumnya</option>
                        <option value="SAME_PERIOD_LAST_YEAR" {{ $comparisonType === 'SAME_PERIOD_LAST_YEAR' ? 'selected' : '' }}>Periode Sama Tahun Lalu</option>
                        <option value="SPECIFIC_DATE"         {{ $comparisonType === 'SPECIFIC_DATE'         ? 'selected' : '' }}>Tanggal Spesifik</option>
                    </select>
                </div>
            </div>

            <div class="row">
                <div class="col-md-2 sfr-form-group" id="comparison_offset_group">
                    <label class="sfr-label"><i class="fas fa-arrows-alt-h"></i> Jarak Periode</label>
                    <input type="number" name="comparison_offset" id="comparison_offset" class="sfr-control" min="1" max="36" value="{{ max(1, $comparisonOffset) }}">
                </div>

                <div class="col-md-3 sfr-form-group" id="comparison_date_group">
                    <label class="sfr-label"><i class="fas fa-calendar-check"></i> Tanggal Pembanding</label>
                    <input type="date" name="comparison_date" id="comparison_date" class="sfr-control" value="{{ $comparisonDate }}">
                </div>

                <div class="col-md-2 sfr-form-group">
                    <label class="sfr-label"><i class="fas fa-list-ol"></i> Per Page</label>
                    <select name="per_page" id="per_page" class="sfr-control">
                        @foreach([10, 20, 50, 100] as $size)
                            <option value="{{ $size }}" {{ $perPage === $size ? 'selected' : '' }}>{{ $size }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-5 sfr-filter-actions">
                    <button type="submit" class="btn-apply">
                        <i class="fas fa-search"></i> Terapkan Filter
                    </button>
                    <a href="{{ route('finance.report.snapshots', ['period_type' => 'MONTHLY', 'month' => now()->month, 'year' => now()->year]) }}" class="btn-reset">
                        <i class="fas fa-redo"></i> Reset
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- ── Summary Cards ────────────────────────────────── --}}
<div class="sfr-summary-grid">
    <div class="sfr-summary-card sc-count anim-d1">
        <div class="sc-label"><span class="sc-icon sc-icon-count"><i class="fas fa-layer-group"></i></span> Jumlah Snapshot</div>
        <div class="sc-value big">{{ number_format($totalCount, 0, ',', '.') }}</div>
    </div>
    <div class="sfr-summary-card sc-opening anim-d2">
        <div class="sc-label"><span class="sc-icon sc-icon-opening"><i class="fas fa-wallet"></i></span> Total Saldo Awal</div>
        <div class="sc-value" style="font-size:1rem;">Rp {{ number_format($totalOpeningBalance, 2, ',', '.') }}</div>
    </div>
    <div class="sfr-summary-card sc-ending anim-d3">
        <div class="sc-label"><span class="sc-icon sc-icon-ending"><i class="fas fa-wallet"></i></span> Total Saldo Keseluruhan</div>
        <div class="sc-value blue" style="font-size:1rem;">Rp {{ number_format($totalEndingBalance, 2, ',', '.') }}</div>
    </div>
    <div class="sfr-summary-card sc-surplus anim-d4">
        <div class="sc-label"><span class="sc-icon sc-icon-surplus"><i class="fas fa-balance-scale"></i></span> Total Surplus (Defisit)</div>
        <div class="sc-value {{ $totalNetResult >= 0 ? 'green' : 'red' }}" style="font-size:1rem;">Rp {{ number_format($totalNetResult, 2, ',', '.') }}</div>
    </div>
</div>

{{-- ── Table Card ───────────────────────────────────── --}}
<div class="sfr-table-card">
    <div class="sfr-table-header">
        <h3 class="sfr-table-title">
            <span class="tt-icon"><i class="fas fa-table"></i></span>
            Daftar Snapshot Laporan
        </h3>
    </div>

    @if($reports->total() === 0)
        <div class="sfr-alert-empty">
            <i class="fas fa-exclamation-triangle"></i>
            Belum ada snapshot laporan untuk filter periode yang dipilih.
        </div>
    @endif

    <div class="table-responsive">
        <table class="sfr-table">
            <thead>
                <tr>
                    <th>Aksi</th>
                    <th>Periode</th>
                    <th>Tipe</th>
                    <th>Versi</th>
                    <th>Saldo Awal</th>
                    <th>Saldo Akhir</th>
                    <th>Surplus (Defisit)</th>
                    <th>Perbandingan</th>
                    <th>Generated At</th>
                    <th>Generated By</th>
                </tr>
            </thead>
            <tbody>
                @forelse($reports as $report)
                    @php
                        $period = $report->period;
                        $rowPeriodType = strtoupper((string) ($period->period_type ?? $report->report_type));
                        $periodLabel = '-';
                        if ($period) {
                            if ($rowPeriodType === 'DAILY') {
                                $periodLabel = optional($period->start_date)->format('d/m/Y') ?? '-';
                            } elseif ($rowPeriodType === 'MONTHLY') {
                                $periodLabel = sprintf('%02d/%04d', (int) $period->month, (int) $period->year);
                            } else {
                                $periodLabel = (string) $period->year;
                            }
                        }
                        $openingBalance = (float) data_get($report->summary, 'opening_balance', 0);
                        $endingBalance  = (float) data_get($report->summary, 'ending_balance', 0);
                        $netResult      = (float) data_get($report->summary, 'net_result', 0);
                        $comparison     = $comparisons[$report->id] ?? null;

                        $typeBadgeClass = match($rowPeriodType) {
                            'DAILY'   => 'daily',
                            'MONTHLY' => 'monthly',
                            'YEARLY'  => 'yearly',
                            default   => 'all',
                        };
                        $typeIcon = match($rowPeriodType) {
                            'DAILY'   => 'fa-calendar-day',
                            'MONTHLY' => 'fa-calendar-week',
                            'YEARLY'  => 'fa-calendar',
                            default   => 'fa-layer-group',
                        };
                    @endphp
                    <tr>
                        <td>
                            <div class="sfr-actions">
                                <a href="{{ route('finance.report.show', $report->id) }}" class="btn-preview">
                                    <i class="fas fa-eye" style="font-size:.65rem;"></i> Preview
                                </a>
                                @permission('finance_report.generate')
                                    <a href="{{ route('finance.report.edit', $report->id) }}" class="btn-action btn-edit">
                                        <i class="fas fa-pen" style="font-size:.62rem;"></i> Edit
                                    </a>
                                    <form method="POST" action="{{ route('finance.report.destroy', $report->id) }}" onsubmit="return confirm('Hapus snapshot ini? Tindakan ini tidak bisa dibatalkan.')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-action btn-delete">
                                            <i class="fas fa-trash" style="font-size:.62rem;"></i> Delete
                                        </button>
                                    </form>
                                @endpermission
                            </div>
                        </td>
                        <td>
                            <span style="font-family:'Plus Jakarta Sans',sans-serif;font-size:.82rem;font-weight:500;color:var(--text-primary);">
                                {{ $periodLabel }}
                            </span>
                        </td>
                        <td>
                            <span class="badge-type {{ $typeBadgeClass }}">
                                <i class="fas {{ $typeIcon }}" style="font-size:.6rem;"></i>
                                {{ $rowPeriodType }}
                            </span>
                        </td>
                        <td>
                            <span class="badge-version">
                                <i class="fas fa-tag"></i> {{ $report->version_no }}
                            </span>
                        </td>
                        <td><span class="amount-cell">Rp {{ number_format($openingBalance, 2, ',', '.') }}</span></td>
                        <td><span class="amount-cell" style="color:var(--blue-primary);">Rp {{ number_format($endingBalance, 2, ',', '.') }}</span></td>
                        <td>
                            <span class="amount-cell {{ $netResult >= 0 ? 'positive' : 'negative' }}">
                                {{ $netResult >= 0 ? '' : '-' }}Rp {{ number_format(abs($netResult), 2, ',', '.') }}
                            </span>
                        </td>
                        <td>
                            @if(!$comparison)
                                <span class="comp-none">—</span>
                            @elseif(!data_get($comparison, 'available', false))
                                <div class="comp-label">{{ data_get($comparison, 'label', 'Perbandingan') }}</div>
                                <div style="font-size:.72rem;color:var(--text-muted);">{{ data_get($comparison, 'message', 'Data tidak ditemukan.') }}</div>
                            @else
                                @php
                                    $diffNet     = (float) data_get($comparison, 'difference_net_result', 0);
                                    $diffBalance = (float) data_get($comparison, 'difference_ending_balance', 0);
                                @endphp
                                <div class="comp-label">{{ data_get($comparison, 'label', 'Perbandingan') }}</div>
                                <div class="comp-row {{ $diffNet >= 0 ? 'up' : 'down' }}">
                                    <i class="fas fa-{{ $diffNet >= 0 ? 'arrow-up' : 'arrow-down' }}" style="font-size:.6rem;"></i>
                                    Surplus: {{ $diffNet >= 0 ? '+' : '' }}Rp {{ number_format($diffNet, 2, ',', '.') }}
                                </div>
                                <div class="comp-row {{ $diffBalance >= 0 ? 'up' : 'down' }}">
                                    <i class="fas fa-{{ $diffBalance >= 0 ? 'arrow-up' : 'arrow-down' }}" style="font-size:.6rem;"></i>
                                    Saldo: {{ $diffBalance >= 0 ? '+' : '' }}Rp {{ number_format($diffBalance, 2, ',', '.') }}
                                </div>
                            @endif
                        </td>
                        <td>
                            <span style="font-family:'Plus Jakarta Sans',sans-serif;font-size:.75rem;color:var(--text-muted);">
                                {{ optional($report->generated_at)->format('Y-m-d H:i:s') ?? '-' }}
                            </span>
                        </td>
                        <td>
                            <div style="display:flex;align-items:center;gap:.45rem;">
                                <div style="width:26px;height:26px;border-radius:50%;background:linear-gradient(135deg,var(--blue-primary),var(--blue-light));display:flex;align-items:center;justify-content:center;color:white;font-size:.65rem;font-weight:800;flex-shrink:0;">
                                    {{ strtoupper(substr($report->user?->name ?? '?', 0, 1)) }}
                                </div>
                                <span style="font-size:.8rem;font-weight:600;color:var(--text-primary);">{{ $report->user?->name ?? '-' }}</span>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10">
                            <div class="sfr-empty-state">
                                <div class="sfr-empty-icon"><i class="fas fa-inbox"></i></div>
                                <div class="sfr-empty-text">Tidak ada snapshot laporan untuk filter ini.</div>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="sfr-table-footer">
        {{ $reports->appends(request()->query())->links() }}
    </div>
</div>
@endsection

@section('js')
<script>
    (function () {
        const periodTypeSelect      = document.getElementById('period_type');
        const reportDateGroup       = document.getElementById('report_date_group');
        const reportDateInput       = document.getElementById('report_date');
        const monthGroup            = document.getElementById('month_group');
        const monthInput            = document.getElementById('month');
        const yearGroup             = document.getElementById('year_group');
        const yearInput             = document.getElementById('year');
        const comparisonTypeSelect  = document.getElementById('comparison_type');
        const comparisonOffsetGroup = document.getElementById('comparison_offset_group');
        const comparisonOffsetInput = document.getElementById('comparison_offset');
        const comparisonDateGroup   = document.getElementById('comparison_date_group');
        const comparisonDateInput   = document.getElementById('comparison_date');

        function syncPeriodFilter() {
            const periodType = periodTypeSelect.value;
            const isAll     = periodType === 'ALL';
            const isDaily   = periodType === 'DAILY';
            const isMonthly = periodType === 'MONTHLY';
            const isYearly  = periodType === 'YEARLY';

            reportDateGroup.style.display = isDaily   ? '' : 'none';
            monthGroup.style.display      = isMonthly ? '' : 'none';
            yearGroup.style.display       = (isMonthly || isYearly) ? '' : 'none';

            reportDateInput.disabled = !isDaily;
            reportDateInput.required = isDaily;
            monthInput.disabled      = !isMonthly;
            monthInput.required      = isMonthly;
            yearInput.disabled       = !(isMonthly || isYearly);
            yearInput.required       = (isMonthly || isYearly);

            if (isAll) { comparisonTypeSelect.value = 'NONE'; }
            comparisonTypeSelect.disabled = isAll;
        }

        function syncComparisonFilter() {
            const comparisonType = comparisonTypeSelect.value;
            const useOffset = comparisonType === 'PREVIOUS_PERIOD';
            const useDate   = comparisonType === 'SPECIFIC_DATE';

            comparisonOffsetGroup.style.display = useOffset ? '' : 'none';
            comparisonDateGroup.style.display   = useDate   ? '' : 'none';

            comparisonOffsetInput.disabled = !useOffset;
            comparisonOffsetInput.required = useOffset;
            comparisonDateInput.disabled   = !useDate;
            comparisonDateInput.required   = useDate;
        }

        periodTypeSelect.addEventListener('change', syncPeriodFilter);
        comparisonTypeSelect.addEventListener('change', syncComparisonFilter);

        syncPeriodFilter();
        syncComparisonFilter();
    })();
</script>
@endsection
