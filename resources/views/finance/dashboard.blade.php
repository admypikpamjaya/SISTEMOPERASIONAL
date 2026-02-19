@extends('layouts.app')

@section('content')

<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

<style>
    /* ── Design Tokens — Blue-Slate palette (matches dark sidebar + blue accent) ── */
    :root {
        --p1:        #3B82F6;
        --p2:        #2563EB;
        --p3:        #1E40AF;
        --grad:      linear-gradient(135deg, #3B82F6 0%, #2563EB 55%, #1D4ED8 100%);
        --grad-hero: linear-gradient(135deg, #1E3A5F 0%, #1E40AF 55%, #2563EB 100%);
        --surface:   #FFFFFF;
        --bg:        #F0F4FF;
        --border:    #DBEAFE;
        --text:      #1E293B;
        --muted:     #64748B;
        --success:   #22C55E;
        --s-bg:      #F0FDF4;
        --s-b:       #BBF7D0;
        --warn:      #F59E0B;
        --w-bg:      #FFFBEB;
        --w-b:       #FDE68A;
        --danger:    #EF4444;
        --d-bg:      #FFF1F2;
        --d-b:       #FECDD3;
        --radius:    18px;
        --radius-sm: 11px;
        --shadow:    0 4px 24px rgba(37,99,235,.09);
        --shadow-lg: 0 8px 32px rgba(37,99,235,.16);
        --font:      'Plus Jakarta Sans', 'Nunito', 'Segoe UI', sans-serif;
    }

    .fd, .fd * {
        font-family: var(--font) !important;
        box-sizing: border-box;
    }

    /* pastikan Font Awesome tidak di-override */
    .fd .fas,
    .fd .far,
    .fd .fab,
    .fd .fal {
        font-family: 'Font Awesome 5 Free' !important;
        font-style: normal !important;
        -webkit-font-smoothing: antialiased;
        display: inline-block;
        line-height: 1;
        vertical-align: middle;
    }

    /* ── Icon wrapper ── */
    .ico {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
        flex-shrink: 0;
    }
    .ico-md { width: 38px; height: 38px; font-size: 1rem; }
    .ico-sm { width: 30px; height: 30px; font-size: .85rem; }
    .ico-xs { width: 24px; height: 24px; font-size: .72rem; border-radius: 6px; }

    .ico-white  { background: rgba(255,255,255,.18); color: #fff; }
    .ico-blue   { background: #EFF6FF; color: var(--p1); border: 1px solid #BFDBFE; }
    .ico-green  { background: var(--s-bg); color: #16A34A; border: 1px solid var(--s-b); }
    .ico-yellow { background: var(--w-bg); color: #B45309; border: 1px solid var(--w-b); }
    .ico-red    { background: var(--d-bg); color: var(--danger); border: 1px solid var(--d-b); }
    .ico-gray   { background: #F1F5F9; color: #64748B; border: 1px solid #E2E8F0; }
    .ico-circle { border-radius: 50% !important; }

    /* ── Page brand ── */
    .fd-brand {
        display: flex; align-items: center; gap: 14px;
        margin-bottom: 24px;
    }
    .fd-brand-logo {
        width: 52px; height: 52px;
        background: var(--grad);
        border-radius: 14px;
        display: flex; align-items: center; justify-content: center;
        color: #fff; font-size: 1.4rem;
        box-shadow: 0 4px 16px rgba(37,99,235,.28);
        flex-shrink: 0;
    }
    .fd-brand-logo .fas { font-size: 1.4rem !important; color: #fff !important; }
    .fd-brand h1 {
        font-size: 1.3rem; font-weight: 800;
        color: var(--text); margin: 0 0 2px; line-height: 1.2;
    }
    .fd-brand p {
        font-size: .8rem; color: var(--muted); font-weight: 500; margin: 0;
    }

    /* ── Card ── */
    .fd-card {
        background: var(--surface);
        border-radius: var(--radius);
        box-shadow: var(--shadow);
        border: 1.5px solid var(--border);
        overflow: hidden;
        margin-bottom: 24px;
        transition: box-shadow .2s, transform .2s;
    }
    .fd-card:hover { box-shadow: var(--shadow-lg); transform: translateY(-2px); }

    .fd-card-header {
        background: var(--grad);
        padding: 15px 22px;
        display: flex; align-items: center;
        justify-content: space-between; gap: 12px;
    }
    .fd-card-header-left { display: flex; align-items: center; gap: 12px; }
    .fd-card-header h3 { margin: 0; font-size: 1rem; font-weight: 700; color: #fff; }

    .fd-card-body { padding: 22px; }

    .fd-card-footer {
        padding: 13px 22px;
        background: #F8FAFF;
        border-top: 1.5px solid var(--border);
        display: flex; align-items: center;
        justify-content: space-between; flex-wrap: wrap; gap: 10px;
        font-size: .83rem; color: var(--muted);
    }
    .fd-card-footer .pagination { margin: 0; }

    /* ── Hero snapshot ── */
    .fd-hero {
        background: var(--grad-hero);
        border-radius: var(--radius);
        box-shadow: var(--shadow-lg);
        padding: 26px 24px 0;
        margin-bottom: 24px;
        position: relative; overflow: hidden;
        transition: transform .2s, box-shadow .2s;
    }
    .fd-hero:hover { transform: translateY(-2px); box-shadow: 0 12px 36px rgba(37,99,235,.24); }
    .fd-hero .deco  { position: absolute; right: -16px; top: -16px; width: 90px; height: 90px; border-radius: 50%; background: rgba(255,255,255,.08); pointer-events: none; }
    .fd-hero .deco2 { position: absolute; right: 50px; bottom: 22px; width: 50px; height: 50px; border-radius: 50%; background: rgba(255,255,255,.06); pointer-events: none; }
    .fd-hero .hero-icon { font-size: 2rem; color: rgba(255,255,255,.30); margin-bottom: 8px; display: block; }
    .fd-hero .hero-icon .fas { font-size: 2rem !important; color: rgba(255,255,255,.30) !important; }
    .fd-hero .hero-num   { font-size: 2.8rem; font-weight: 800; color: #fff; line-height: 1; margin-bottom: 4px; }
    .fd-hero .hero-label { font-size: .88rem; color: rgba(255,255,255,.72); font-weight: 500; margin-bottom: 18px; }
    .fd-hero .hero-footer {
        display: flex; align-items: center; gap: 8px;
        margin: 0 -24px;
        padding: 11px 24px;
        background: rgba(0,0,0,.16);
        color: rgba(255,255,255,.88);
        font-size: .82rem; font-weight: 600;
        text-decoration: none;
        transition: background .2s;
    }
    .fd-hero .hero-footer:hover { background: rgba(0,0,0,.26); color: #fff; text-decoration: none; }
    .fd-hero .hero-footer .fas { font-size: .9rem !important; color: rgba(255,255,255,.88) !important; }

    /* ── Form labels ── */
    .fd-label {
        font-size: .74rem; font-weight: 700;
        color: var(--muted); text-transform: uppercase;
        letter-spacing: .04em; margin-bottom: 6px;
        display: flex; align-items: center; gap: 6px;
    }
    .fd-label .fas, .fd-label .far {
        font-size: .78rem !important;
        color: var(--p1) !important;
        width: 14px; text-align: center;
    }

    /* ── Inputs ── */
    .fd-input, .fd-select {
        width: 100%;
        background: #F8FAFF;
        border: 1.5px solid var(--border);
        border-radius: var(--radius-sm);
        padding: 9px 13px;
        font-size: .9rem; color: var(--text);
        font-family: var(--font) !important; outline: none;
        transition: border-color .2s, box-shadow .2s;
        height: auto; appearance: auto;
    }
    .fd-input:focus, .fd-select:focus {
        border-color: var(--p1);
        box-shadow: 0 0 0 3px rgba(59,130,246,.14);
        background: #fff;
    }

    /* ── Buttons ── */
    .fd-btn {
        display: inline-flex; align-items: center; gap: 8px;
        padding: 10px 20px; border-radius: var(--radius-sm);
        font-size: .88rem; font-weight: 700;
        cursor: pointer; border: none;
        transition: transform .15s, box-shadow .15s;
        text-decoration: none; font-family: var(--font) !important;
        white-space: nowrap; line-height: 1;
    }
    .fd-btn:hover { transform: translateY(-1px); box-shadow: 0 4px 14px rgba(37,99,235,.22); text-decoration: none; }
    .fd-btn .fas, .fd-btn .far { font-size: .88rem !important; }

    .fd-btn-primary { background: var(--grad); color: #fff !important; }
    .fd-btn-primary .fas { color: #fff !important; }

    .fd-btn-outline { background: var(--surface); color: var(--p1) !important; border: 1.5px solid var(--border); }
    .fd-btn-outline .fas { color: var(--p1) !important; }

    .fd-btn-sm { padding: 7px 14px; font-size: .8rem; border-radius: 9px; }
    .fd-btn-sm .fas { font-size: .8rem !important; }

    .fd-btn-light { background: rgba(255,255,255,.15); color: #fff !important; border: 1.5px solid rgba(255,255,255,.25); }
    .fd-btn-light .fas { color: #fff !important; }
    .fd-btn-light:hover { background: rgba(255,255,255,.28); color: #fff !important; }

    /* ── Table ── */
    .fd-table { width: 100%; border-collapse: separate; border-spacing: 0; }
    .fd-table thead th {
        font-size: .71rem; font-weight: 800;
        text-transform: uppercase; letter-spacing: .05em;
        color: var(--muted); padding: 12px 16px;
        background: #F8FAFF; border-bottom: 2px solid var(--border);
        white-space: nowrap;
    }
    .fd-table thead th .fas,
    .fd-table thead th .far {
        font-size: .72rem !important;
        color: var(--p1) !important;
        margin-right: 5px; opacity: .85;
    }
    .fd-table tbody tr { transition: background .15s; }
    .fd-table tbody tr:hover { background: #F0F6FF; }
    .fd-table tbody td {
        padding: 13px 16px; vertical-align: middle;
        border-bottom: 1px solid var(--border);
        font-size: .88rem; color: var(--text);
    }
    .fd-table tbody tr:last-child td { border-bottom: none; }

    /* ── Badges ── */
    .fd-badge {
        display: inline-flex; align-items: center; gap: 5px;
        padding: 4px 11px; border-radius: 20px;
        font-size: .74rem; font-weight: 700; line-height: 1;
    }
    .fd-badge .fas { font-size: .68rem !important; }

    /* Biru — menggantikan ungu sebelumnya */
    .fb-info    { background: #EFF6FF; color: var(--p1); border: 1px solid #BFDBFE; }
    .fb-info .fas { color: var(--p1) !important; }

    .fb-gray    { background: #F1F5F9; color: #64748B; border: 1px solid #CBD5E1; }
    .fb-gray .fas { color: #64748B !important; }

    .fb-success { background: var(--s-bg); color: #16A34A; border: 1px solid var(--s-b); }
    .fb-success .fas { color: #16A34A !important; }

    .fb-warn    { background: var(--w-bg); color: #B45309; border: 1px solid var(--w-b); }
    .fb-warn .fas { color: #B45309 !important; }

    /* ── Currency ── */
    .cur-p { font-weight: 700; color: var(--p2); }
    .cur-s { font-weight: 700; color: #16A34A; }

    /* ── User chip ── */
    .user-chip { display: inline-flex; align-items: center; gap: 8px; }
    .user-chip .avatar {
        width: 28px; height: 28px;
        background: #EFF6FF; border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0; border: 1.5px solid #BFDBFE;
    }
    .user-chip .avatar .fas { font-size: .72rem !important; color: var(--p1) !important; }
    .user-chip .uname { font-weight: 600; font-size: .87rem; }

    /* ── Generated at ── */
    .gen-date { font-weight: 600; font-size: .87rem; color: var(--text); }
    .gen-time { font-size: .76rem; color: var(--muted); display: flex; align-items: center; gap: 4px; margin-top: 2px; }
    .gen-time .fas { font-size: .72rem !important; color: var(--muted) !important; }

    /* ── Empty ── */
    .fd-empty { text-align: center; padding: 52px 24px; }
    .fd-empty .ei { font-size: 3rem; color: #BFDBFE; margin-bottom: 12px; display: block; }
    .fd-empty .ei .fas { font-size: 3rem !important; color: #BFDBFE !important; }
    .fd-empty h5 { color: var(--muted); font-weight: 700; margin-bottom: 6px; }
    .fd-empty p  { color: var(--muted); font-size: .87rem; margin-bottom: 18px; }

    /* ── Footer info ── */
    .fd-footer-info { display: flex; align-items: center; gap: 6px; }
    .fd-footer-info .fas { font-size: .82rem !important; color: var(--p1) !important; }

    /* ── Pagination ── */
    .pagination .page-link {
        border: none; margin: 0 2px;
        border-radius: 9px !important;
        color: var(--p1); padding: 6px 11px;
        font-size: .82rem; font-weight: 600;
        transition: background .15s, color .15s;
    }
    .pagination .page-item.active .page-link {
        background: var(--grad); color: #fff;
        box-shadow: 0 2px 10px rgba(37,99,235,.28);
    }
    .pagination .page-link:hover { background: #EFF6FF; color: var(--p1); }

    @media (max-width: 768px) {
        .fd-card-body { padding: 16px; }
        .fd-card-header { flex-direction: column; align-items: flex-start; }
    }
</style>

<div class="fd">

    {{-- ── Brand Header ── --}}
    <div class="fd-brand">
        <div class="fd-brand-logo">
            <i class="fas fa-chart-pie"></i>
        </div>
        <div>
            <h1>Finance Dashboard</h1>
            <p>Monitoring &amp; Snapshot Laporan Keuangan</p>
        </div>
    </div>

    <div class="row">

        {{-- ══ Filter Card ══ --}}
        <div class="col-lg-8 col-md-12">
            <div class="fd-card">
                <div class="fd-card-header">
                    <div class="fd-card-header-left">
                        <span class="ico ico-sm ico-white">
                            <i class="fas fa-filter"></i>
                        </span>
                        <h3>Filter Snapshot Finance</h3>
                    </div>
                </div>
                <div class="fd-card-body">
                    <form method="GET" action="{{ route('finance.dashboard') }}">
                        <div class="form-row">
                            <div class="form-group col-md-3">
                                <label class="fd-label" for="month">
                                    <i class="fas fa-calendar"></i> Bulan
                                </label>
                                <select name="month" id="month" class="fd-select">
                                    <option value="">Semua</option>
                                    @for($m = 1; $m <= 12; $m++)
                                        <option value="{{ $m }}" {{ (int) ($filters['month'] ?? 0) === $m ? 'selected' : '' }}>
                                            {{ $m }}
                                        </option>
                                    @endfor
                                </select>
                            </div>

                            <div class="form-group col-md-3">
                                <label class="fd-label" for="year">
                                    <i class="fas fa-calendar-alt"></i> Tahun
                                </label>
                                <input
                                    type="number" name="year" id="year"
                                    class="fd-input" min="1900" max="2100"
                                    value="{{ $filters['year'] }}"
                                >
                            </div>

                            <div class="form-group col-md-2">
                                <label class="fd-label" for="per_page">
                                    <i class="fas fa-list-ol"></i> Per Page
                                </label>
                                <select name="per_page" id="per_page" class="fd-select">
                                    @foreach([5, 10, 20, 50] as $size)
                                        <option value="{{ $size }}" {{ (int) request('per_page', 5) === $size ? 'selected' : '' }}>
                                            {{ $size }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group col-md-4 d-flex align-items-end" style="gap: 8px;">
                                <button type="submit" class="fd-btn fd-btn-primary">
                                    <i class="fas fa-filter"></i>
                                    <span>Filter</span>
                                </button>
                                <a href="{{ route('finance.dashboard') }}" class="fd-btn fd-btn-outline">
                                    <i class="fas fa-sync-alt"></i>
                                    <span>Reset</span>
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- ══ Hero Snapshot ══ --}}
        <div class="col-lg-4 col-md-12">
            <div class="fd-hero">
                <div class="deco"></div>
                <div class="deco2"></div>
                <span class="hero-icon"><i class="fas fa-file-invoice"></i></span>
                <div class="hero-num">{{ $totalReports }}</div>
                <div class="hero-label">Total Snapshot</div>
                <a href="{{ route('finance.report.snapshots', ['year' => $filters['year']]) }}" class="hero-footer">
                    <i class="fas fa-arrow-circle-right"></i>
                    <span>Buka Snapshot Laporan</span>
                </a>
            </div>
        </div>

    </div>

    {{-- ══ Snapshot Table ══ --}}
    <div class="row">
        <div class="col-12">
            <div class="fd-card">
                <div class="fd-card-header">
                    <div class="fd-card-header-left">
                        <span class="ico ico-sm ico-white">
                            <i class="fas fa-history"></i>
                        </span>
                        <h3>Snapshot Terbaru</h3>
                    </div>
                    <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                        <a href="{{ route('finance.depreciation.index') }}" class="fd-btn fd-btn-light fd-btn-sm">
                            <i class="fas fa-calculator"></i>
                            <span>Hitung Penyusutan</span>
                        </a>
                        <a href="{{ route('finance.invoice.index') }}" class="fd-btn fd-btn-light fd-btn-sm">
                            <i class="fas fa-file-invoice-dollar"></i>
                            <span>Faktur / Jurnal</span>
                        </a>
                    </div>
                </div>

                <div style="overflow-x: auto;">
                    <table class="fd-table">
                        <thead>
                            <tr>
                                <th><i class="fas fa-tag"></i>Tipe</th>
                                <th><i class="fas fa-code-branch"></i>Versi</th>
                                <th><i class="fas fa-arrow-up"></i>Saldo Awal</th>
                                <th><i class="fas fa-arrow-down"></i>Saldo Akhir</th>
                                <th><i class="far fa-clock"></i>Generated At</th>
                                <th><i class="far fa-user"></i>Generated By</th>
                                <th style="text-align: center;"><i class="fas fa-shield-alt"></i>Read Only</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($reports as $report)
                                <tr>
                                    <td>
                                        <span class="fd-badge fb-info">
                                            <i class="fas fa-file-invoice"></i>
                                            {{ $report->report_type }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="fd-badge fb-gray">
                                            <i class="fas fa-tag"></i>
                                            v{{ $report->version_no }}
                                        </span>
                                    </td>
                                    <td class="cur-p">
                                        Rp {{ number_format((float) data_get($report->summary, 'opening_balance', 0), 2, ',', '.') }}
                                    </td>
                                    <td class="cur-s">
                                        Rp {{ number_format((float) data_get($report->summary, 'ending_balance', data_get($report->summary, 'net_result', 0)), 2, ',', '.') }}
                                    </td>
                                    <td>
                                        <div class="gen-date">
                                            {{ optional($report->generated_at)->format('Y-m-d') ?? '-' }}
                                        </div>
                                        <div class="gen-time">
                                            <i class="fas fa-clock"></i>
                                            {{ optional($report->generated_at)->format('H:i:s') ?? '-' }}
                                        </div>
                                    </td>
                                    <td>
                                        <div class="user-chip">
                                            <span class="avatar">
                                                <i class="fas fa-user"></i>
                                            </span>
                                            <span class="uname">{{ $report->user?->name ?? '-' }}</span>
                                        </div>
                                    </td>
                                    <td style="text-align: center;">
                                        @if($report->is_read_only)
                                            <span class="fd-badge fb-success">
                                                <i class="fas fa-check-circle"></i> Yes
                                            </span>
                                        @else
                                            <span class="fd-badge fb-warn">
                                                <i class="fas fa-pen"></i> No
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7">
                                        <div class="fd-empty">
                                            <span class="ei"><i class="fas fa-inbox"></i></span>
                                            <h5>Belum ada snapshot laporan finance.</h5>
                                            <p>Mulai dengan membuat snapshot laporan baru.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="fd-card-footer">
                    <div class="fd-footer-info">
                        <i class="fas fa-info-circle"></i>
                        <span>Menampilkan data snapshot</span>
                    </div>
                    <div>{{ $reports->appends(request()->query())->links() }}</div>
                </div>
            </div>
        </div>
    </div>

</div>{{-- /fd --}}
@endsection