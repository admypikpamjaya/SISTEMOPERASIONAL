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
        --blue-glow: rgba(37, 99, 235, 0.20);
        --accent-cyan: #06b6d4;
        --accent-green: #10b981;
        --accent-red: #ef4444;
        --accent-amber: #f59e0b;
        --accent-purple: #8b5cf6;
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

    /* ── Page Header ─────────────────────────────── */
    .plr-page-header {
        display: flex; align-items: center; justify-content: space-between;
        margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;
        animation: fadeDown 0.45s ease both;
    }
    .plr-header-left { display: flex; align-items: center; gap: 0.9rem; }
    .plr-header-icon {
        width: 48px; height: 48px; border-radius: var(--radius-sm);
        background: linear-gradient(135deg, var(--blue-primary), var(--blue-light));
        display: flex; align-items: center; justify-content: center;
        color: white; font-size: 1.25rem; box-shadow: var(--shadow-md); flex-shrink: 0;
    }
    .plr-header-title { font-size: 1.35rem; font-weight: 800; color: var(--text-primary); margin: 0; letter-spacing: -0.02em; line-height: 1.2; }
    .plr-header-sub { font-size: 0.8rem; color: var(--text-muted); margin: 0.1rem 0 0; font-weight: 500; }

    /* ── Action Buttons ──────────────────────────── */
    .plr-actions { display: flex; align-items: center; gap: 0.6rem; flex-wrap: wrap; }
    .btn-plr-back {
        display: inline-flex; align-items: center; gap: 0.4rem;
        background: white; border: 1px solid var(--border-light);
        color: var(--text-secondary); font-size: 0.82rem; font-weight: 600;
        padding: 0.55rem 1rem; border-radius: var(--radius-sm);
        text-decoration: none; transition: all 0.2s;
        box-shadow: var(--shadow-sm);
    }
    .btn-plr-back:hover { background: var(--surface-bg); color: var(--text-primary); border-color: var(--blue-light); text-decoration: none; }
    .btn-plr-download {
        display: inline-flex; align-items: center; gap: 0.4rem;
        background: linear-gradient(135deg, var(--blue-primary), var(--blue-mid));
        color: white; font-size: 0.82rem; font-weight: 700;
        padding: 0.55rem 1.1rem; border-radius: var(--radius-sm);
        border: none; transition: all 0.25s; box-shadow: 0 3px 10px rgba(37,99,235,0.35);
        cursor: pointer; position: relative;
    }
    .btn-plr-download:hover { transform: translateY(-1px); box-shadow: 0 6px 20px rgba(37,99,235,0.4); }
    .btn-plr-download .dropdown-menu {
        border: 1px solid var(--border-light); border-radius: var(--radius-md);
        box-shadow: var(--shadow-lg); padding: 0.4rem; min-width: 160px;
        margin-top: 0.3rem !important;
    }
    .btn-plr-download .dropdown-menu .dropdown-item {
        border-radius: 8px; font-size: 0.82rem; font-weight: 600; padding: 0.5rem 0.8rem;
        display: flex; align-items: center; gap: 0.5rem; color: var(--text-secondary);
        transition: all 0.15s;
    }
    .btn-plr-download .dropdown-menu .dropdown-item:hover { background: var(--surface-bg); color: var(--text-primary); }
    .btn-plr-download .dropdown-menu .dropdown-item .fa-file-word  { color: #2563eb; }
    .btn-plr-download .dropdown-menu .dropdown-item .fa-file-excel { color: #16a34a; }
    .btn-plr-download .dropdown-menu .dropdown-item .fa-file-pdf   { color: #dc2626; }

    /* ── Alert Banners ───────────────────────────── */
    .plr-alert {
        display: flex; align-items: flex-start; gap: 0.75rem;
        border-radius: var(--radius-md); padding: 0.9rem 1.1rem;
        margin-bottom: 1.25rem; font-size: 0.83rem; font-weight: 500;
        border: 1px solid transparent; animation: fadeUp 0.5s ease both;
    }
    .plr-alert.info {
        background: rgba(37,99,235,0.07); border-color: rgba(37,99,235,0.15); color: var(--blue-dark);
    }
    .plr-alert.warning {
        background: rgba(245,158,11,0.08); border-color: rgba(245,158,11,0.2); color: #92400e;
    }
    .plr-alert .alert-icon {
        width: 28px; height: 28px; border-radius: 8px; flex-shrink: 0;
        display: flex; align-items: center; justify-content: center; font-size: 0.8rem;
    }
    .plr-alert.info .alert-icon { background: rgba(37,99,235,0.12); color: var(--blue-primary); }
    .plr-alert.warning .alert-icon { background: rgba(245,158,11,0.15); color: var(--accent-amber); }

    /* ── Meta Info Cards ─────────────────────────── */
    .plr-meta-grid {
        display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        gap: 0.75rem; margin-bottom: 1.5rem;
    }
    .plr-meta-item {
        background: white; border: 1px solid var(--border-light);
        border-radius: var(--radius-md); padding: 0.9rem 1rem;
        box-shadow: var(--shadow-sm); transition: box-shadow 0.2s;
    }
    .plr-meta-item:hover { box-shadow: var(--shadow-md); }
    .plr-meta-label {
        font-size: 0.68rem; font-weight: 700; text-transform: uppercase;
        letter-spacing: 0.08em; color: var(--text-muted); margin-bottom: 0.3rem;
        display: flex; align-items: center; gap: 0.35rem;
    }
    .plr-meta-label i { font-size: 0.65rem; }
    .plr-meta-value {
        font-size: 0.92rem; font-weight: 700; color: var(--text-primary);
    }
    .plr-meta-value.mono { font-family: 'DM Mono', monospace; font-size: 0.88rem; }
    .plr-meta-value.green { color: var(--accent-green); }
    .plr-meta-value.blue  { color: var(--blue-primary); }

    /* ── Main Card ───────────────────────────────── */
    .plr-main-card {
        background: white; border-radius: var(--radius-lg);
        box-shadow: var(--shadow-md); border: 1px solid var(--border-light);
        overflow: hidden; animation: fadeUp 0.55s ease both;
    }

    /* ── Section Headers (Income/Expense/Depr) ───── */
    .plr-section-header {
        display: flex; align-items: center; gap: 0.6rem;
        padding: 0.75rem 1rem; font-size: 0.82rem; font-weight: 700;
        text-transform: uppercase; letter-spacing: 0.07em; border-bottom: 1px solid var(--border-table);
    }
    .plr-section-header .sh-icon {
        width: 24px; height: 24px; border-radius: 6px;
        display: flex; align-items: center; justify-content: center; font-size: 0.7rem;
    }
    .sh-income   { background: rgba(37,99,235,0.08);  color: var(--blue-primary); }
    .sh-expense  { background: rgba(239,68,68,0.08);  color: var(--accent-red); }
    .sh-deprec   { background: rgba(245,158,11,0.08); color: var(--accent-amber); }
    .sh-surplus  { background: rgba(16,185,129,0.12); color: var(--accent-green); }
    .sh-saldo    { background: rgba(37,99,235,0.10);  color: var(--blue-primary); }

    .row-income   td { color: var(--text-primary); }
    .row-expense  td { color: var(--text-primary); }
    .row-deprec   td { color: var(--text-primary); }
    .row-empty td { color: var(--text-muted) !important; font-size: 0.8rem; font-style: italic; }

    /* ── Table ───────────────────────────────────── */
    .plr-table { width: 100%; border-collapse: collapse; font-size: 0.83rem; }
    .plr-table th {
        background: #f8fafc; color: var(--text-muted); font-size: 0.7rem;
        font-weight: 700; text-transform: uppercase; letter-spacing: 0.07em;
        padding: 0.65rem 1rem; border-bottom: 2px solid var(--border-table);
        white-space: nowrap;
    }
    .plr-table td {
        padding: 0.6rem 1rem; border-bottom: 1px solid var(--border-table);
        color: var(--text-secondary); vertical-align: top;
        line-height: 1.45;
    }
    .plr-table tbody tr:last-child td { border-bottom: none; }
    .plr-table tbody tr:hover td { background: rgba(37,99,235,0.025); }
    .plr-table .col-code  { width: 130px; font-family: 'DM Mono', monospace; font-size: 0.78rem; color: var(--text-muted); }
    .plr-table .col-label { width: 240px; font-weight: 600; color: var(--text-primary); }
    .plr-table .col-amount{ width: 200px; text-align: right; font-family: 'DM Mono', monospace; font-size: 0.82rem; font-weight: 600; white-space: nowrap; }
    .plr-table .faktur-badge {
        display: inline-flex; align-items: center; gap: 0.3rem;
        background: rgba(37,99,235,0.08); color: var(--blue-primary);
        font-size: 0.7rem; font-weight: 700; padding: 0.2rem 0.55rem;
        border-radius: 999px; margin-bottom: 0.3rem;
    }

    /* ── Subtotal Rows ───────────────────────────── */
    .row-subtotal td {
        background: #f8fafc; font-weight: 700; font-size: 0.82rem;
        color: var(--text-primary) !important; border-top: 1.5px solid var(--border-table);
        border-bottom: 1.5px solid var(--border-table) !important;
    }
    .row-subtotal td.col-amount { color: var(--text-primary) !important; }

    /* ── Summary Rows ────────────────────────────── */
    .row-surplus td {
        background: linear-gradient(90deg, rgba(16,185,129,0.07), rgba(16,185,129,0.03));
        font-weight: 800; font-size: 0.88rem;
        border-top: 2px solid rgba(16,185,129,0.25) !important;
        border-bottom: 1px solid rgba(16,185,129,0.15) !important;
    }
    .row-surplus td:first-child { color: var(--accent-green); }
    .row-surplus .col-amount    { color: var(--accent-green); font-size: 0.92rem; }

    .row-saldo-akhir td {
        background: linear-gradient(90deg, rgba(37,99,235,0.07), rgba(37,99,235,0.03));
        font-weight: 800; font-size: 0.92rem;
        border-top: 2px solid rgba(37,99,235,0.2) !important;
    }
    .row-saldo-akhir td:first-child { color: var(--blue-primary); }
    .row-saldo-akhir .col-amount    { color: var(--blue-primary); font-size: 0.95rem; }

    /* ── Divider between sections ────────────────── */
    .plr-section-divider { height: 6px; background: var(--surface-bg); border: none; }

    /* ── Animations ──────────────────────────────── */
    @keyframes fadeUp   { from { opacity:0; transform:translateY(14px); } to { opacity:1; transform:translateY(0); } }
    @keyframes fadeDown { from { opacity:0; transform:translateY(-10px); } to { opacity:1; transform:translateY(0); } }
</style>

@php
    $periodLabel = $report->year;
    $hasNoDetailLines = count($report->incomeLines) === 0
        && count($report->expenseLines) === 0
        && count($report->depreciationLines) === 0;
    if ($report->reportType === 'DAILY') {
        $periodLabel = $report->periodDate
            ? \Carbon\Carbon::parse($report->periodDate)->format('d/m/Y')
            : sprintf('%02d/%02d/%04d', (int) ($report->day ?? 1), (int) ($report->month ?? 1), $report->year);
    } elseif ($report->reportType === 'MONTHLY') {
        $periodLabel = sprintf('%02d/%04d', (int) ($report->month ?? 1), $report->year);
    }
@endphp

{{-- ── Page Header ─────────────────────────────────── --}}
<div class="plr-page-header">
    <div class="plr-header-left">
        <div class="plr-header-icon"><i class="fas fa-file-invoice-dollar"></i></div>
        <div>
            <h1 class="plr-header-title">Preview Laporan Laba &amp; Rugi</h1>
            <p class="plr-header-sub">Dokumen Keuangan &mdash; {{ $periodLabel }} &mdash; {{ $report->reportType }}</p>
        </div>
    </div>

    <div class="plr-actions">
        <a href="{{ route('finance.report.snapshots', ['year' => $report->year]) }}" class="btn-plr-back">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
        <div class="btn-group">
            <button type="button" class="btn-plr-download dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
                <i class="fas fa-download"></i> Download Laporan
            </button>
            <div class="dropdown-menu dropdown-menu-right">
                <a class="dropdown-item" href="{{ route('finance.report.download', ['id' => $report->reportId, 'format' => 'docx']) }}">
                    <i class="far fa-file-word"></i> Microsoft Word (.docx)
                </a>
                <a class="dropdown-item" href="{{ route('finance.report.download', ['id' => $report->reportId, 'format' => 'excel']) }}">
                    <i class="far fa-file-excel"></i> Microsoft Excel (.xlsx)
                </a>
                <a class="dropdown-item" href="{{ route('finance.report.download', ['id' => $report->reportId, 'format' => 'pdf']) }}">
                    <i class="far fa-file-pdf"></i> PDF Document (.pdf)
                </a>
            </div>
        </div>
    </div>
</div>

{{-- ── Alert Banners ────────────────────────────────── --}}
<div class="plr-alert info">
    <div class="alert-icon"><i class="fas fa-eye"></i></div>
    <div>Ini adalah halaman <strong>preview</strong>. Pastikan data sudah sesuai, lalu pilih format dokumen untuk diunduh.</div>
</div>

@if($hasNoDetailLines)
    <div class="plr-alert warning">
        <div class="alert-icon"><i class="fas fa-exclamation-triangle"></i></div>
        <div>Belum ada detail pemasukan, pengeluaran, atau penyusutan pada snapshot ini.</div>
    </div>
@endif

{{-- ── Meta Info Grid ───────────────────────────────── --}}
<div class="plr-meta-grid">
    <div class="plr-meta-item">
        <div class="plr-meta-label"><i class="fas fa-calendar-alt"></i> Periode</div>
        <div class="plr-meta-value">{{ $periodLabel }}</div>
    </div>
    <div class="plr-meta-item">
        <div class="plr-meta-label"><i class="fas fa-tag"></i> Jenis Laporan</div>
        <div class="plr-meta-value">
            <span style="background:rgba(37,99,235,0.1);color:var(--blue-primary);padding:.2rem .6rem;border-radius:999px;font-size:.78rem;">
                {{ $report->reportType }}
            </span>
        </div>
    </div>
    <div class="plr-meta-item">
        <div class="plr-meta-label"><i class="fas fa-wallet"></i> Saldo Awal</div>
        <div class="plr-meta-value mono">Rp {{ number_format($report->openingBalance, 2, ',', '.') }}</div>
    </div>
    <div class="plr-meta-item">
        <div class="plr-meta-label"><i class="fas fa-wallet"></i> Saldo Akhir</div>
        <div class="plr-meta-value mono green">Rp {{ number_format($report->endingBalance, 2, ',', '.') }}</div>
    </div>
    <div class="plr-meta-item">
        <div class="plr-meta-label"><i class="fas fa-user"></i> Disusun Oleh</div>
        <div class="plr-meta-value">{{ $report->generatedByName ?? '-' }}</div>
    </div>
    <div class="plr-meta-item">
        <div class="plr-meta-label"><i class="fas fa-clock"></i> Generated At</div>
        <div class="plr-meta-value mono" style="font-size:.8rem;">{{ $report->generatedAt->format('Y-m-d H:i:s') }}</div>
    </div>
</div>

{{-- ── Main Report Table ────────────────────────────── --}}
<div class="plr-main-card">
    <div class="table-responsive">
        <table class="plr-table">
            <thead>
                <tr>
                    <th class="col-code">Kode</th>
                    <th class="col-label">Uraian</th>
                    <th>Keterangan</th>
                    <th class="col-amount">Nominal</th>
                </tr>
            </thead>
            <tbody>

                {{-- ── PENGHASILAN ─────────────────────── --}}
                <tr>
                    <td colspan="4" style="padding:0;">
                        <div class="plr-section-header" style="color:var(--blue-primary);">
                            <span class="sh-icon sh-income"><i class="fas fa-arrow-trend-up"></i></span>
                            Penghasilan
                        </div>
                    </td>
                </tr>
                @forelse($report->incomeLines as $line)
                    <tr class="row-income">
                        <td class="col-code">{{ $line->lineCode }}</td>
                        <td class="col-label">{{ $line->lineLabel }}</td>
                        <td>
                            @if($line->invoiceNumber)
                                <span class="faktur-badge"><i class="fas fa-receipt" style="font-size:.6rem;"></i> Faktur: {{ $line->invoiceNumber }}</span><br>
                            @endif
                            @if($line->description)
                                {{ $line->description }}
                            @elseif(!$line->invoiceNumber)
                                <span style="color:var(--text-muted);">—</span>
                            @endif
                        </td>
                        <td class="col-amount">Rp {{ number_format($line->amount, 2, ',', '.') }}</td>
                    </tr>
                @empty
                    <tr class="row-empty">
                        <td colspan="4" class="text-center">Tidak ada item penghasilan.</td>
                    </tr>
                @endforelse
                <tr class="row-subtotal">
                    <td colspan="3">Total Penghasilan</td>
                    <td class="col-amount">Rp {{ number_format($report->totalIncome, 2, ',', '.') }}</td>
                </tr>

                <tr class="plr-section-divider"><td colspan="4" style="padding:0;height:6px;background:#f0f4fd;"></td></tr>

                {{-- ── PENGELUARAN ─────────────────────── --}}
                <tr>
                    <td colspan="4" style="padding:0;">
                        <div class="plr-section-header" style="color:var(--accent-red);">
                            <span class="sh-icon sh-expense"><i class="fas fa-arrow-trend-down"></i></span>
                            Pengeluaran
                        </div>
                    </td>
                </tr>
                @forelse($report->expenseLines as $line)
                    <tr class="row-expense">
                        <td class="col-code">{{ $line->lineCode }}</td>
                        <td class="col-label">{{ $line->lineLabel }}</td>
                        <td>
                            @if($line->invoiceNumber)
                                <span class="faktur-badge"><i class="fas fa-receipt" style="font-size:.6rem;"></i> Faktur: {{ $line->invoiceNumber }}</span><br>
                            @endif
                            @if($line->description)
                                {{ $line->description }}
                            @elseif(!$line->invoiceNumber)
                                <span style="color:var(--text-muted);">—</span>
                            @endif
                        </td>
                        <td class="col-amount">Rp {{ number_format($line->amount, 2, ',', '.') }}</td>
                    </tr>
                @empty
                    <tr class="row-empty">
                        <td colspan="4" class="text-center">Tidak ada item pengeluaran.</td>
                    </tr>
                @endforelse
                <tr class="row-subtotal">
                    <td colspan="3">Total Pengeluaran <span style="font-weight:500;color:var(--text-muted);font-size:.78rem;">(non-penyusutan)</span></td>
                    <td class="col-amount">Rp {{ number_format($report->totalExpense, 2, ',', '.') }}</td>
                </tr>

                <tr><td colspan="4" style="padding:0;height:6px;background:#f0f4fd;"></td></tr>

                {{-- ── PENYUSUTAN ──────────────────────── --}}
                <tr>
                    <td colspan="4" style="padding:0;">
                        <div class="plr-section-header" style="color:var(--accent-amber);">
                            <span class="sh-icon sh-deprec"><i class="fas fa-chart-bar"></i></span>
                            Penyusutan
                        </div>
                    </td>
                </tr>
                @forelse($report->depreciationLines as $line)
                    <tr class="row-deprec">
                        <td class="col-code">{{ $line->lineCode }}</td>
                        <td class="col-label">{{ $line->lineLabel }}</td>
                        <td>
                            @if($line->invoiceNumber)
                                <span class="faktur-badge"><i class="fas fa-receipt" style="font-size:.6rem;"></i> Faktur: {{ $line->invoiceNumber }}</span><br>
                            @endif
                            @if($line->description)
                                {{ $line->description }}
                            @elseif(!$line->invoiceNumber)
                                <span style="color:var(--text-muted);">—</span>
                            @endif
                        </td>
                        <td class="col-amount">Rp {{ number_format($line->amount, 2, ',', '.') }}</td>
                    </tr>
                @empty
                    <tr class="row-empty">
                        <td colspan="4" class="text-center">Tidak ada item penyusutan.</td>
                    </tr>
                @endforelse
                <tr class="row-subtotal">
                    <td colspan="3">Total Penyusutan</td>
                    <td class="col-amount">Rp {{ number_format($report->totalDepreciation, 2, ',', '.') }}</td>
                </tr>

                {{-- ── SURPLUS / SALDO ──────────────────── --}}
                <tr class="row-surplus">
                    <td colspan="3">
                        <span style="display:inline-flex;align-items:center;gap:.5rem;">
                            <span style="width:20px;height:20px;border-radius:6px;background:rgba(16,185,129,0.15);display:inline-flex;align-items:center;justify-content:center;font-size:.65rem;"><i class="fas fa-balance-scale" style="color:var(--accent-green);"></i></span>
                            Surplus (Defisit)
                        </span>
                    </td>
                    <td class="col-amount">Rp {{ number_format($report->surplusDeficit, 2, ',', '.') }}</td>
                </tr>
                <tr class="row-saldo-akhir">
                    <td colspan="3">
                        <span style="display:inline-flex;align-items:center;gap:.5rem;">
                            <span style="width:20px;height:20px;border-radius:6px;background:rgba(37,99,235,0.15);display:inline-flex;align-items:center;justify-content:center;font-size:.65rem;"><i class="fas fa-wallet" style="color:var(--blue-primary);"></i></span>
                            Saldo Akhir
                        </span>
                    </td>
                    <td class="col-amount">Rp {{ number_format($report->endingBalance, 2, ',', '.') }}</td>
                </tr>

            </tbody>
        </table>
    </div>
</div>
@endsection