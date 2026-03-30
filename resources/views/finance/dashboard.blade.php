@extends('layouts.app')

@section('content')
@php
    $periodType = strtoupper((string) ($filters['period_type'] ?? 'ALL'));
    $reportDate = $filters['report_date'] ?? null;
    $month = $filters['month'] ?? null;
    $year = $filters['year'] ?? null;
    $perPage = (int) ($filters['per_page'] ?? 10);
    $featureAccess = $featureAccess ?? [];
    $dashboardSummary = $dashboardSummary ?? [];
    $balanceSheetSummary = data_get($dashboardSummary, 'balance_sheet.summary', []);
    $balanceSheetUncategorized = (int) data_get($dashboardSummary, 'balance_sheet.uncategorized_count', 0);
    $profitLossSummary = data_get($dashboardSummary, 'profit_loss.totals', []);
    $generalLedgerSummary = data_get($dashboardSummary, 'general_ledger', []);
@endphp

<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

<style>
    :root {
        --fd-blue: #2563eb;
        --fd-blue-dark: #1e3a8a;
        --fd-green: #059669;
        --fd-red: #dc2626;
        --fd-amber: #d97706;
        --fd-bg: #f0f4fd;
        --fd-card: #ffffff;
        --fd-text: #0f172a;
        --fd-muted: #64748b;
        --fd-border: rgba(37, 99, 235, 0.10);
        --fd-shadow: 0 10px 32px rgba(15, 23, 42, 0.08), 0 4px 14px rgba(37, 99, 235, 0.06);
        --fd-radius: 18px;
        --fd-radius-sm: 12px;
    }

    body, .content-wrapper { background: var(--fd-bg) !important; font-family: 'Plus Jakarta Sans', sans-serif !important; }
    .fd-wrap { color: var(--fd-text); }
    .fd-head { display:flex; justify-content:space-between; align-items:center; gap:1rem; flex-wrap:wrap; margin-bottom:1.5rem; }
    .fd-head-main { display:flex; align-items:center; gap:.9rem; }
    .fd-head-icon { width:52px; height:52px; border-radius:14px; display:flex; align-items:center; justify-content:center; background:linear-gradient(135deg,var(--fd-blue),var(--fd-blue-dark)); color:#fff; box-shadow:var(--fd-shadow); }
    .fd-head h1 { margin:0; font-size:1.45rem; font-weight:800; color:var(--fd-text); }
    .fd-head p { margin:.15rem 0 0; font-size:.82rem; font-weight:500; color:var(--fd-muted); }
    .fd-actions-top { display:flex; gap:.55rem; flex-wrap:wrap; }
    .fd-btn { display:inline-flex; align-items:center; gap:.45rem; padding:.62rem 1rem; border-radius:12px; font-size:.82rem; font-weight:700; text-decoration:none; border:1px solid transparent; transition:.2s ease; }
    .fd-btn:hover { text-decoration:none; transform:translateY(-1px); }
    .fd-btn-primary { background:linear-gradient(135deg,var(--fd-blue),#3b82f6); color:#fff; box-shadow:0 8px 22px rgba(37,99,235,.24); }
    .fd-btn-muted { background:#fff; color:var(--fd-muted); border-color:var(--fd-border); }
    .fd-card, .fd-hero, .fd-feature-card { background:var(--fd-card); border:1px solid var(--fd-border); border-radius:var(--fd-radius); box-shadow:var(--fd-shadow); }
    .fd-card-head { padding:1rem 1.15rem; border-bottom:1px solid var(--fd-border); display:flex; justify-content:space-between; align-items:center; gap:.75rem; flex-wrap:wrap; }
    .fd-card-title { display:flex; align-items:center; gap:.6rem; font-size:.9rem; font-weight:700; }
    .fd-card-icon { width:32px; height:32px; border-radius:10px; display:inline-flex; align-items:center; justify-content:center; background:rgba(37,99,235,.08); color:var(--fd-blue); }
    .fd-card-body { padding:1.1rem 1.15rem; }
    .fd-field { margin-bottom:1rem; }
    .fd-label { display:flex; align-items:center; gap:.35rem; margin-bottom:.4rem; color:var(--fd-muted); font-size:.7rem; font-weight:800; letter-spacing:.06em; text-transform:uppercase; }
    .fd-control { width:100%; border:1.5px solid rgba(148,163,184,.18); border-radius:var(--fd-radius-sm); padding:.65rem .85rem; font-size:.84rem; color:var(--fd-text); background:#fff; }
    .fd-control:focus { outline:none; border-color:rgba(37,99,235,.4); box-shadow:0 0 0 3px rgba(37,99,235,.12); }
    .fd-actions { display:flex; align-items:flex-end; gap:.55rem; margin-bottom:1rem; flex-wrap:wrap; }
    .fd-hero { padding:1.35rem; background:linear-gradient(135deg,#0f2f64 0%,#1e3a8a 55%,#2563eb 100%); color:#fff; min-height:100%; }
    .fd-hero small { display:inline-flex; align-items:center; gap:.35rem; padding:.32rem .7rem; border-radius:999px; background:rgba(255,255,255,.12); font-size:.72rem; font-weight:700; }
    .fd-hero strong { display:block; margin-top:.9rem; font-size:2.5rem; line-height:1; }
    .fd-hero p { margin:.35rem 0 1rem; color:rgba(255,255,255,.82); font-size:.82rem; }
    .fd-hero a { display:inline-flex; align-items:center; gap:.45rem; color:#fff; font-weight:700; text-decoration:none; }
    .fd-feature-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(280px,1fr)); gap:1rem; margin:1.2rem 0; }
    .fd-feature-card { padding:1.1rem 1.15rem; }
    .fd-feature-top { display:flex; justify-content:space-between; align-items:center; gap:.75rem; margin-bottom:.9rem; }
    .fd-feature-title { display:flex; align-items:center; gap:.6rem; font-size:.9rem; font-weight:800; }
    .fd-feature-icon { width:34px; height:34px; border-radius:10px; display:inline-flex; align-items:center; justify-content:center; background:rgba(37,99,235,.08); color:var(--fd-blue); }
    .fd-badge { display:inline-flex; align-items:center; gap:.35rem; border-radius:999px; padding:.26rem .7rem; font-size:.68rem; font-weight:800; background:rgba(37,99,235,.10); color:var(--fd-blue); }
    .fd-mini-grid { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:.7rem; }
    .fd-mini-item { background:#f8fbff; border:1px solid rgba(148,163,184,.12); border-radius:12px; padding:.75rem .8rem; }
    .fd-mini-label { color:var(--fd-muted); font-size:.68rem; font-weight:800; letter-spacing:.06em; text-transform:uppercase; margin-bottom:.25rem; }
    .fd-mini-value { color:var(--fd-text); font-size:.86rem; font-weight:800; line-height:1.35; }
    .fd-mini-value.green { color:var(--fd-green); } .fd-mini-value.red { color:var(--fd-red); } .fd-mini-value.blue { color:var(--fd-blue); }
    .fd-mini-note { margin-top:.85rem; color:var(--fd-muted); font-size:.76rem; font-weight:500; }
    .fd-table { width:100%; border-collapse:collapse; }
    .fd-table th { background:#f8fbff; color:var(--fd-muted); font-size:.68rem; text-transform:uppercase; letter-spacing:.06em; padding:.75rem 1rem; border-bottom:1px solid var(--fd-border); }
    .fd-table td { padding:.8rem 1rem; font-size:.82rem; color:#334155; border-bottom:1px solid rgba(148,163,184,.12); vertical-align:middle; }
    .fd-table tbody tr:last-child td { border-bottom:none; }
    .fd-table tbody tr:hover td { background:rgba(37,99,235,.03); }
    .fd-amount { font-weight:800; white-space:nowrap; color:var(--fd-blue); }
    .fd-user { display:inline-flex; align-items:center; gap:.55rem; }
    .fd-user-avatar { width:28px; height:28px; border-radius:50%; background:rgba(37,99,235,.10); color:var(--fd-blue); display:inline-flex; align-items:center; justify-content:center; font-size:.72rem; font-weight:800; }
    .fd-empty { padding:2.5rem 1rem; text-align:center; color:var(--fd-muted); }
    .fd-empty i { font-size:2.2rem; margin-bottom:.8rem; color:rgba(37,99,235,.28); }
    .fd-footer { padding:.95rem 1rem; border-top:1px solid var(--fd-border); background:#fafcff; }
    .fd-footer .pagination { margin:0; }
    @media (max-width: 768px) { .fd-mini-grid { grid-template-columns:1fr; } }
</style>

<div class="fd-wrap">
    <div class="fd-head">
        <div class="fd-head-main">
            <div class="fd-head-icon"><i class="fas fa-chart-pie"></i></div>
            <div>
                <h1>Finance Dashboard</h1>
                <p>Ringkasan snapshot, lembar saldo, laba rugi, dan buku besar dalam satu tampilan.</p>
            </div>
        </div>
        <div class="fd-actions-top">
            <a href="{{ route('finance.report.index') }}" class="fd-btn fd-btn-primary"><i class="fas fa-plus"></i> Input Finance Report</a>
            <a href="{{ route('finance.invoice.index') }}" class="fd-btn fd-btn-muted"><i class="fas fa-file-invoice-dollar"></i> Faktur / Jurnal</a>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="fd-card">
                <div class="fd-card-head">
                    <div class="fd-card-title"><span class="fd-card-icon"><i class="fas fa-filter"></i></span><span>Filter Periode Finance</span></div>
                </div>
                <div class="fd-card-body">
                    <form method="GET" action="{{ route('finance.dashboard') }}">
                        <div class="row">
                            <div class="col-md-2 fd-field">
                                <label class="fd-label" for="dashboard_period_type"><i class="fas fa-layer-group"></i> Periode</label>
                                <select name="period_type" id="dashboard_period_type" class="fd-control">
                                    <option value="ALL" {{ $periodType === 'ALL' ? 'selected' : '' }}>Semua</option>
                                    <option value="DAILY" {{ $periodType === 'DAILY' ? 'selected' : '' }}>Harian</option>
                                    <option value="MONTHLY" {{ $periodType === 'MONTHLY' ? 'selected' : '' }}>Bulanan</option>
                                    <option value="YEARLY" {{ $periodType === 'YEARLY' ? 'selected' : '' }}>Tahunan</option>
                                </select>
                            </div>
                            <div class="col-md-3 fd-field" id="dashboard_report_date_group">
                                <label class="fd-label" for="dashboard_report_date"><i class="fas fa-calendar-day"></i> Tanggal</label>
                                <input type="date" name="report_date" id="dashboard_report_date" class="fd-control" value="{{ $reportDate }}">
                            </div>
                            <div class="col-md-2 fd-field" id="dashboard_month_group">
                                <label class="fd-label" for="dashboard_month"><i class="fas fa-calendar-week"></i> Bulan</label>
                                <select name="month" id="dashboard_month" class="fd-control">
                                    @for($m = 1; $m <= 12; $m++)
                                        <option value="{{ $m }}" {{ (int) $month === $m ? 'selected' : '' }}>{{ str_pad((string) $m, 2, '0', STR_PAD_LEFT) }}</option>
                                    @endfor
                                </select>
                            </div>
                            <div class="col-md-2 fd-field" id="dashboard_year_group">
                                <label class="fd-label" for="dashboard_year"><i class="fas fa-calendar-alt"></i> Tahun</label>
                                <input type="number" name="year" id="dashboard_year" class="fd-control" min="1900" max="2100" value="{{ $year }}">
                            </div>
                            <div class="col-md-2 fd-field">
                                <label class="fd-label" for="dashboard_per_page"><i class="fas fa-list-ol"></i> Snapshot</label>
                                <select name="per_page" id="dashboard_per_page" class="fd-control">
                                    @foreach([5, 10, 20, 50] as $size)
                                        <option value="{{ $size }}" {{ $perPage === $size ? 'selected' : '' }}>{{ $size }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-1 fd-actions">
                                <button type="submit" class="fd-btn fd-btn-primary"><i class="fas fa-search"></i> Filter</button>
                                <a href="{{ route('finance.dashboard') }}" class="fd-btn fd-btn-muted"><i class="fas fa-redo"></i> Reset</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="fd-hero">
                <small><i class="fas fa-file-invoice"></i> Snapshot Tersedia</small>
                <strong>{{ number_format($totalReports, 0, ',', '.') }}</strong>
                <p>Total snapshot laporan finance untuk filter yang sedang aktif.</p>
                <a href="{{ route('finance.report.snapshots', $filterQuery) }}"><i class="fas fa-arrow-right"></i> Buka Snapshot Laporan</a>
            </div>
        </div>
    </div>

    <div class="fd-feature-grid">
        @if(!empty($featureAccess['balance_sheet']))
            <div class="fd-feature-card">
                <div class="fd-feature-top">
                    <div class="fd-feature-title"><span class="fd-feature-icon" style="color:var(--fd-amber);"><i class="fas fa-balance-scale"></i></span><span>Lembar Saldo</span></div>
                    <a href="{{ route('finance.report.balance-sheet', $filterQuery) }}" class="fd-badge"><i class="fas fa-arrow-right"></i> Buka</a>
                </div>
                <div class="fd-mini-grid">
                    <div class="fd-mini-item"><div class="fd-mini-label">Liabilitas</div><div class="fd-mini-value">Rp {{ number_format((float) ($balanceSheetSummary['liabilitas_total'] ?? 0), 2, ',', '.') }}</div></div>
                    <div class="fd-mini-item"><div class="fd-mini-label">Piutang</div><div class="fd-mini-value">Rp {{ number_format((float) ($balanceSheetSummary['piutang_total'] ?? 0), 2, ',', '.') }}</div></div>
                    <div class="fd-mini-item"><div class="fd-mini-label">Kas</div><div class="fd-mini-value green">Rp {{ number_format((float) ($balanceSheetSummary['kas_total'] ?? 0), 2, ',', '.') }}</div></div>
                    <div class="fd-mini-item"><div class="fd-mini-label">Aset</div><div class="fd-mini-value blue">Rp {{ number_format((float) ($balanceSheetSummary['aset_total'] ?? 0), 2, ',', '.') }}</div></div>
                </div>
                <div class="fd-mini-note">{{ number_format((int) ($balanceSheetSummary['account_count'] ?? 0), 0, ',', '.') }} akun terpetakan. @if($balanceSheetUncategorized > 0) {{ number_format($balanceSheetUncategorized, 0, ',', '.') }} akun belum masuk kategori lembar saldo. @endif</div>
            </div>
        @endif

        @if(!empty($featureAccess['profit_loss']))
            <div class="fd-feature-card">
                <div class="fd-feature-top">
                    <div class="fd-feature-title"><span class="fd-feature-icon" style="color:var(--fd-green);"><i class="fas fa-chart-area"></i></span><span>Laba Rugi</span></div>
                    <a href="{{ route('finance.report.profit-loss', $filterQuery) }}" class="fd-badge"><i class="fas fa-arrow-right"></i> Buka</a>
                </div>
                <div class="fd-mini-grid">
                    <div class="fd-mini-item"><div class="fd-mini-label">Pemasukan</div><div class="fd-mini-value green">Rp {{ number_format((float) ($profitLossSummary['income'] ?? 0), 2, ',', '.') }}</div></div>
                    <div class="fd-mini-item"><div class="fd-mini-label">Pengeluaran</div><div class="fd-mini-value red">Rp {{ number_format((float) ($profitLossSummary['expense'] ?? 0), 2, ',', '.') }}</div></div>
                    <div class="fd-mini-item" style="grid-column: span 2;"><div class="fd-mini-label">Laba / Rugi Bersih</div><div class="fd-mini-value {{ (float) ($profitLossSummary['net_result'] ?? 0) >= 0 ? 'green' : 'red' }}">Rp {{ number_format((float) ($profitLossSummary['net_result'] ?? 0), 2, ',', '.') }}</div></div>
                </div>
                <div class="fd-mini-note">Laporan ini hanya menampilkan akun pemasukan dan pengeluaran.</div>
            </div>
        @endif

        @if(!empty($featureAccess['general_ledger']))
            <div class="fd-feature-card">
                <div class="fd-feature-top">
                    <div class="fd-feature-title"><span class="fd-feature-icon"><i class="fas fa-book-open"></i></span><span>Buku Besar</span></div>
                    <a href="{{ route('finance.report.general-ledger', $filterQuery) }}" class="fd-badge"><i class="fas fa-arrow-right"></i> Buka</a>
                </div>
                <div class="fd-mini-grid">
                    <div class="fd-mini-item"><div class="fd-mini-label">Akun</div><div class="fd-mini-value">{{ number_format((int) ($generalLedgerSummary['account_count'] ?? 0), 0, ',', '.') }}</div></div>
                    <div class="fd-mini-item"><div class="fd-mini-label">Baris Jurnal</div><div class="fd-mini-value">{{ number_format((int) ($generalLedgerSummary['entry_count'] ?? 0), 0, ',', '.') }}</div></div>
                    <div class="fd-mini-item"><div class="fd-mini-label">Debit</div><div class="fd-mini-value green">Rp {{ number_format((float) ($generalLedgerSummary['total_debit'] ?? 0), 2, ',', '.') }}</div></div>
                    <div class="fd-mini-item"><div class="fd-mini-label">Kredit</div><div class="fd-mini-value red">Rp {{ number_format((float) ($generalLedgerSummary['total_credit'] ?? 0), 2, ',', '.') }}</div></div>
                </div>
                <div class="fd-mini-note">Buku besar mencakup seluruh baris jurnal finance yang sudah diposting.</div>
            </div>
        @endif
    </div>

    <div class="fd-card">
        <div class="fd-card-head">
            <div class="fd-card-title"><span class="fd-card-icon"><i class="fas fa-history"></i></span><span>Snapshot Laporan Finance</span></div>
            <div class="fd-actions-top">
                @permission('finance_balance_sheet.read')
                    <a href="{{ route('finance.report.balance-sheet', $filterQuery) }}" class="fd-btn fd-btn-muted"><i class="fas fa-balance-scale"></i> Lembar Saldo</a>
                @endpermission
                @permission('finance_profit_loss.read')
                    <a href="{{ route('finance.report.profit-loss', $filterQuery) }}" class="fd-btn fd-btn-muted"><i class="fas fa-chart-area"></i> Laba Rugi</a>
                @endpermission
                @permission('finance_general_ledger.read')
                    <a href="{{ route('finance.report.general-ledger', $filterQuery) }}" class="fd-btn fd-btn-muted"><i class="fas fa-book-open"></i> Buku Besar</a>
                @endpermission
            </div>
        </div>

        <div class="table-responsive">
            <table class="fd-table">
                <thead>
                    <tr>
                        <th>Periode</th>
                        <th>Tipe</th>
                        <th>Versi</th>
                        <th>Saldo Awal</th>
                        <th>Saldo Akhir</th>
                        <th>Generated At</th>
                        <th>Generated By</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($reports as $report)
                        @php
                            $period = $report->period;
                            $rowPeriodType = strtoupper((string) ($period->period_type ?? $report->report_type));
                            if ($rowPeriodType === 'DAILY') {
                                $periodLabel = optional($period?->start_date)->format('d/m/Y') ?? '-';
                            } elseif ($rowPeriodType === 'YEARLY') {
                                $periodLabel = (string) ($period->year ?? '-');
                            } else {
                                $periodLabel = sprintf('%02d/%04d', (int) ($period->month ?? 0), (int) ($period->year ?? 0));
                            }
                        @endphp
                        <tr>
                            <td><strong>{{ $periodLabel }}</strong></td>
                            <td><span class="fd-badge">{{ $rowPeriodType }}</span></td>
                            <td><span class="fd-badge" style="background:rgba(245,158,11,.12);color:var(--fd-amber);">v{{ $report->version_no }}</span></td>
                            <td class="fd-amount">Rp {{ number_format((float) data_get($report->summary, 'opening_balance', 0), 2, ',', '.') }}</td>
                            <td class="fd-amount">Rp {{ number_format((float) data_get($report->summary, 'ending_balance', data_get($report->summary, 'net_result', 0)), 2, ',', '.') }}</td>
                            <td>
                                <div><strong>{{ optional($report->generated_at)->format('d/m/Y') ?? '-' }}</strong></div>
                                <div style="font-size:.74rem;color:var(--fd-muted);">{{ optional($report->generated_at)->format('H:i:s') ?? '-' }}</div>
                            </td>
                            <td>
                                <span class="fd-user">
                                    <span class="fd-user-avatar">{{ strtoupper(substr($report->user?->name ?? '?', 0, 1)) }}</span>
                                    <span>{{ $report->user?->name ?? '-' }}</span>
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">
                                <div class="fd-empty">
                                    <i class="fas fa-inbox"></i>
                                    <div style="font-weight:700;color:var(--fd-text);margin-bottom:.35rem;">Belum ada snapshot laporan finance.</div>
                                    <div>Mulai dari input finance report atau ubah filter periode yang sedang aktif.</div>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="fd-footer">{{ $reports->appends(request()->query())->links() }}</div>
    </div>
</div>
@endsection

@section('js')
<script>
    (function () {
        const periodTypeInput = document.getElementById('dashboard_period_type');
        const reportDateGroup = document.getElementById('dashboard_report_date_group');
        const reportDateInput = document.getElementById('dashboard_report_date');
        const monthGroup = document.getElementById('dashboard_month_group');
        const monthInput = document.getElementById('dashboard_month');
        const yearGroup = document.getElementById('dashboard_year_group');
        const yearInput = document.getElementById('dashboard_year');

        if (!periodTypeInput || !reportDateGroup || !monthGroup || !yearGroup) return;

        function syncDashboardFilters() {
            const periodType = periodTypeInput.value;
            const isAll = periodType === 'ALL';
            const isDaily = periodType === 'DAILY';
            const isMonthly = periodType === 'MONTHLY';
            const isYearly = periodType === 'YEARLY';

            reportDateGroup.style.display = isDaily ? '' : 'none';
            monthGroup.style.display = isMonthly ? '' : 'none';
            yearGroup.style.display = (isMonthly || isYearly) ? '' : 'none';

            if (reportDateInput) {
                reportDateInput.disabled = !isDaily;
                if (isAll) reportDateInput.value = '';
            }
            if (monthInput) monthInput.disabled = !isMonthly;
            if (yearInput) {
                yearInput.disabled = !(isMonthly || isYearly);
                if (isAll) yearInput.value = '';
            }
        }

        periodTypeInput.addEventListener('change', syncDashboardFilters);
        syncDashboardFilters();
    })();
</script>
@endsection
