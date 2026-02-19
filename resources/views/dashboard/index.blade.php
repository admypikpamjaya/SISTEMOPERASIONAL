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
        --blue-glow: rgba(37, 99, 235, 0.25);
        --accent-cyan: #06b6d4;
        --accent-green: #10b981;
        --accent-red: #ef4444;
        --accent-amber: #f59e0b;
        --surface-bg: #f0f4fd;
        --surface-card: #ffffff;
        --surface-dark: #0f172a;
        --text-primary: #0f172a;
        --text-secondary: #475569;
        --text-muted: #94a3b8;
        --border-light: rgba(37, 99, 235, 0.12);
        --shadow-sm: 0 1px 3px rgba(15, 23, 42, 0.08), 0 1px 2px rgba(15, 23, 42, 0.06);
        --shadow-md: 0 4px 16px rgba(15, 23, 42, 0.10), 0 2px 8px rgba(37, 99, 235, 0.08);
        --shadow-lg: 0 10px 40px rgba(15, 23, 42, 0.14), 0 4px 16px rgba(37, 99, 235, 0.12);
        --shadow-glow: 0 0 0 3px rgba(37, 99, 235, 0.15);
        --radius-sm: 10px;
        --radius-md: 14px;
        --radius-lg: 20px;
        --radius-xl: 28px;
    }

    body, .content-wrapper {
        background: var(--surface-bg) !important;
        font-family: 'Plus Jakarta Sans', sans-serif !important;
    }

    /* ─── Page Header ─────────────────────────── */
    .dash-page-header {
        display: flex;
        align-items: center;
        gap: 1rem;
        margin-bottom: 1.75rem;
        padding: 0;
        animation: slideDown 0.5s cubic-bezier(0.34, 1.56, 0.64, 1) both;
    }
    .dash-page-header .header-icon {
        width: 48px;
        height: 48px;
        border-radius: var(--radius-sm);
        background: linear-gradient(135deg, var(--blue-primary), var(--blue-light));
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1.3rem;
        box-shadow: var(--shadow-md);
        flex-shrink: 0;
    }
    .dash-page-header .header-text h1 {
        font-size: 1.45rem;
        font-weight: 800;
        color: var(--text-primary);
        margin: 0;
        letter-spacing: -0.02em;
        line-height: 1.2;
    }
    .dash-page-header .header-text p {
        font-size: 0.82rem;
        color: var(--text-muted);
        margin: 0;
        font-weight: 500;
    }

    /* ─── Welcome Card ────────────────────────── */
    .welcome-card {
        background: linear-gradient(135deg, var(--blue-deeper) 0%, var(--blue-dark) 50%, var(--blue-primary) 100%);
        border-radius: var(--radius-lg);
        padding: 1.6rem 2rem;
        position: relative;
        overflow: hidden;
        box-shadow: var(--shadow-lg);
        color: white;
        height: 100%;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        animation: fadeUp 0.55s ease both;
    }
    .welcome-card::before {
        content: '';
        position: absolute;
        top: -40px; right: -40px;
        width: 180px; height: 180px;
        background: rgba(255,255,255,0.06);
        border-radius: 50%;
    }
    .welcome-card::after {
        content: '';
        position: absolute;
        bottom: -60px; left: 30%;
        width: 250px; height: 250px;
        background: rgba(255,255,255,0.04);
        border-radius: 50%;
    }
    .welcome-card h3 {
        font-size: 1.1rem;
        font-weight: 700;
        margin: 0 0 0.25rem;
        position: relative;
        z-index: 1;
    }
    .welcome-card p {
        font-size: 0.8rem;
        opacity: 0.75;
        margin: 0;
        position: relative;
        z-index: 1;
    }
    .welcome-card .welcome-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        background: rgba(255,255,255,0.15);
        border: 1px solid rgba(255,255,255,0.2);
        border-radius: 999px;
        padding: 0.3rem 0.8rem;
        font-size: 0.75rem;
        font-weight: 600;
        color: rgba(255,255,255,0.9);
        margin-bottom: 0.75rem;
        width: fit-content;
        backdrop-filter: blur(6px);
        position: relative;
        z-index: 1;
    }
    .welcome-card .welcome-badge i {
        color: #fbbf24;
        font-size: 0.7rem;
    }

    /* ─── Saldo Widget ────────────────────────── */
    .saldo-card {
        background: var(--surface-card);
        border-radius: var(--radius-lg);
        padding: 1.5rem;
        box-shadow: var(--shadow-md);
        border: 1px solid var(--border-light);
        position: relative;
        overflow: hidden;
        height: 100%;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        animation: fadeUp 0.6s ease both;
        text-decoration: none !important;
        transition: all 0.3s ease;
    }
    .saldo-card:hover {
        box-shadow: var(--shadow-lg), var(--shadow-glow);
        transform: translateY(-2px);
    }
    .saldo-card::before {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0;
        height: 3px;
        background: linear-gradient(90deg, var(--blue-primary), var(--accent-cyan));
        border-radius: var(--radius-lg) var(--radius-lg) 0 0;
    }
    .saldo-label {
        font-size: 0.76rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        color: var(--text-muted);
        display: flex;
        align-items: center;
        gap: 0.4rem;
        margin-bottom: 0.5rem;
    }
    .saldo-label .dot {
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background: var(--accent-green);
        animation: pulse 2s infinite;
    }
    @keyframes pulse {
        0%, 100% { opacity: 1; transform: scale(1); }
        50% { opacity: 0.6; transform: scale(1.3); }
    }
    .saldo-value {
        font-size: 1.7rem;
        font-weight: 800;
        color: var(--text-primary);
        letter-spacing: -0.03em;
        font-family: 'DM Mono', monospace;
        line-height: 1.1;
        margin-bottom: 0.4rem;
    }
    .saldo-meta {
        font-size: 0.72rem;
        color: var(--text-muted);
        display: flex;
        align-items: center;
        gap: 0.3rem;
    }
    .saldo-footer {
        margin-top: 1rem;
        padding-top: 0.75rem;
        border-top: 1px solid var(--border-light);
    }
    .saldo-footer a {
        font-size: 0.78rem;
        font-weight: 600;
        color: var(--blue-primary);
        text-decoration: none;
        display: flex;
        align-items: center;
        gap: 0.35rem;
        transition: gap 0.2s;
    }
    .saldo-footer a:hover { gap: 0.6rem; }
    .saldo-icon {
        width: 42px;
        height: 42px;
        border-radius: var(--radius-sm);
        background: linear-gradient(135deg, rgba(37,99,235,0.12), rgba(6,182,212,0.12));
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--blue-primary);
        font-size: 1.1rem;
        margin-bottom: 0.75rem;
    }

    /* ─── Chart Cards ─────────────────────────── */
    .dash-chart-card {
        background: var(--surface-card);
        border-radius: var(--radius-lg);
        box-shadow: var(--shadow-md);
        border: 1px solid var(--border-light);
        overflow: hidden;
        margin-bottom: 1.25rem;
        transition: all 0.3s ease;
        cursor: pointer;
        animation: fadeUp 0.7s ease both;
        position: relative;
    }
    .dash-chart-card:hover {
        box-shadow: var(--shadow-lg);
        transform: translateY(-3px);
    }
    .dash-chart-card .chart-card-header {
        padding: 0.9rem 1.1rem 0.6rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        border-bottom: 1px solid rgba(0,0,0,0.04);
    }
    .dash-chart-card .chart-card-title {
        font-size: 0.85rem;
        font-weight: 700;
        color: var(--text-primary);
        display: flex;
        align-items: center;
        gap: 0.5rem;
        margin: 0;
    }
    .dash-chart-card .chart-card-title .title-icon {
        width: 26px;
        height: 26px;
        border-radius: 7px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.7rem;
    }
    .dash-chart-card .chart-card-body {
        padding: 0.5rem 0.75rem 0.5rem;
    }
    .dash-chart-card .chart-card-footer {
        padding: 0.5rem 1.1rem;
        font-size: 0.72rem;
        color: var(--text-muted);
        display: flex;
        align-items: center;
        gap: 0.3rem;
        background: rgba(0,0,0,0.015);
        border-top: 1px solid rgba(0,0,0,0.04);
    }
    .dash-chart-card canvas {
        width: 100% !important;
        height: 88px !important;
        display: block;
    }

    /* Card accent bar */
    .accent-income::before   { background: linear-gradient(90deg, var(--blue-primary), var(--accent-cyan)); }
    .accent-expense::before  { background: linear-gradient(90deg, var(--accent-red), var(--accent-amber)); }
    .accent-wa::before       { background: linear-gradient(90deg, var(--accent-green), #34d399); }
    .accent-email::before    { background: linear-gradient(90deg, var(--accent-cyan), #818cf8); }
    .dash-chart-card::before {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0;
        height: 3px;
        border-radius: var(--radius-lg) var(--radius-lg) 0 0;
    }

    /* Title icon colors */
    .icon-income  { background: rgba(37,99,235,0.12); color: var(--blue-primary); }
    .icon-expense { background: rgba(239,68,68,0.12); color: var(--accent-red); }
    .icon-wa      { background: rgba(16,185,129,0.12); color: var(--accent-green); }
    .icon-email   { background: rgba(6,182,212,0.12); color: var(--accent-cyan); }

    /* ─── Stats Badge ─────────────────────────── */
    .open-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
        background: rgba(37,99,235,0.08);
        color: var(--blue-primary);
        font-size: 0.68rem;
        font-weight: 700;
        padding: 0.2rem 0.5rem;
        border-radius: 999px;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        transition: background 0.2s;
    }
    .dash-chart-card:hover .open-badge {
        background: rgba(37,99,235,0.15);
    }

    /* ─── Section Labels ──────────────────────── */
    .section-label {
        font-size: 0.72rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.1em;
        color: var(--text-muted);
        margin: 1.5rem 0 0.75rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    .section-label::after {
        content: '';
        flex: 1;
        height: 1px;
        background: var(--border-light);
    }

    /* ─── Animations ──────────────────────────── */
    @keyframes fadeUp {
        from { opacity: 0; transform: translateY(18px); }
        to   { opacity: 1; transform: translateY(0); }
    }
    @keyframes slideDown {
        from { opacity: 0; transform: translateY(-12px); }
        to   { opacity: 1; transform: translateY(0); }
    }

    .anim-delay-1 { animation-delay: 0.08s; }
    .anim-delay-2 { animation-delay: 0.14s; }
    .anim-delay-3 { animation-delay: 0.20s; }
    .anim-delay-4 { animation-delay: 0.26s; }
    .anim-delay-5 { animation-delay: 0.32s; }
</style>

{{-- Page Header --}}
<div class="dash-page-header">
    <div class="header-icon"><i class="fas fa-tachometer-alt"></i></div>
    <div class="header-text">
        <h1>Dashboard</h1>
        <p>Sistem Operasional Yayasan YPIK &mdash; Ringkasan & Monitoring</p>
    </div>
</div>

{{-- Top Row: Saldo + Welcome --}}
<div class="row">
    @if($showFinanceWidgets)
        <div class="col-lg-4 col-md-6 col-sm-12 mb-3">
            <div class="saldo-card">
                <div>
                    <div class="saldo-icon"><i class="fas fa-wallet"></i></div>
                    <div class="saldo-label">
                        <span class="dot"></span>
                        Saldo Finance (All Report)
                    </div>
                    <div class="saldo-value" id="dashboard-saldo-value">
                        Rp {{ number_format((float) ($saldo ?? 0), 2, ',', '.') }}
                    </div>
                    <div class="saldo-meta">
                        <i class="fas fa-clock" style="font-size:0.65rem;"></i>
                        Update WIB:&nbsp;<strong><span id="dashboard-saldo-updated">{{ $saldoUpdatedAt ?? '-' }}</span></strong>
                    </div>
                </div>
                <div class="saldo-footer">
                    <a href="{{ route('finance.report.snapshots') }}">
                        <i class="fas fa-chart-bar"></i>
                        Lihat Snapshot Finance
                        <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>
        <div class="col-lg-8 col-md-6 col-sm-12 mb-3">
    @else
        <div class="col-12 mb-3">
    @endif
            <div class="welcome-card">
                <div>
                    <div class="welcome-badge">
                        <i class="fas fa-circle" style="font-size:0.45rem;color:#4ade80;"></i>
                        Sistem Aktif
                    </div>
                    <h3>Selamat Datang di Aplikasi<br>Sistem Operasional Yayasan YPIK</h3>
                    <p>Pantau data keuangan, aset, dan operasional yayasan secara real-time dari satu dashboard terpadu.</p>
                </div>
                <div style="display:flex; gap:.6rem; margin-top:1rem; position:relative; z-index:1;">
                    <div style="background:rgba(255,255,255,0.1);border:1px solid rgba(255,255,255,0.15);border-radius:10px;padding:.55rem .9rem;flex:1;backdrop-filter:blur(6px);">
                        <div style="font-size:.65rem;opacity:.7;text-transform:uppercase;letter-spacing:.08em;margin-bottom:.15rem;">Modul Aktif</div>
                        <div style="font-size:1.1rem;font-weight:800;">{{ ($showFinanceWidgets ? 1 : 0) + ($showBlastingWidgets ? 1 : 0) + 3 }}</div>
                    </div>
                    <div style="background:rgba(255,255,255,0.1);border:1px solid rgba(255,255,255,0.15);border-radius:10px;padding:.55rem .9rem;flex:1;backdrop-filter:blur(6px);">
                        <div style="font-size:.65rem;opacity:.7;text-transform:uppercase;letter-spacing:.08em;margin-bottom:.15rem;">Refresh Data</div>
                        <div style="font-size:1.1rem;font-weight:800;">60 det</div>
                    </div>
                    <div style="background:rgba(255,255,255,0.1);border:1px solid rgba(255,255,255,0.15);border-radius:10px;padding:.55rem .9rem;flex:1;backdrop-filter:blur(6px);">
                        <div style="font-size:.65rem;opacity:.7;text-transform:uppercase;letter-spacing:.08em;margin-bottom:.15rem;">Status</div>
                        <div style="font-size:1.1rem;font-weight:800;color:#4ade80;">Online</div>
                    </div>
                </div>
            </div>
        </div>
</div>

@if($showFinanceWidgets)
    <div class="section-label"><i class="fas fa-chart-line" style="color:var(--blue-primary);font-size:.7rem;"></i> Grafik Keuangan</div>
    <div class="row">
        <div class="col-lg-6 col-md-6 col-sm-12">
            <div class="dash-chart-card accent-income dashboard-chart-card anim-delay-1" data-href="{{ data_get($incomeChart, 'url') }}">
                <div class="chart-card-header">
                    <h3 class="chart-card-title">
                        <span class="title-icon icon-income"><i class="fas fa-arrow-trend-up"></i></span>
                        Income
                    </h3>
                    <span class="open-badge"><i class="fas fa-external-link-alt" style="font-size:.6rem;"></i> Buka</span>
                </div>
                <div class="chart-card-body">
                    <canvas id="chart-income"></canvas>
                </div>
                <div class="chart-card-footer">
                    <i class="fas fa-info-circle" style="font-size:.7rem;"></i>
                    Klik kartu untuk membuka laporan lengkap
                </div>
            </div>
        </div>
        <div class="col-lg-6 col-md-6 col-sm-12">
            <div class="dash-chart-card accent-expense dashboard-chart-card anim-delay-2" data-href="{{ data_get($expenseChart, 'url') }}">
                <div class="chart-card-header">
                    <h3 class="chart-card-title">
                        <span class="title-icon icon-expense"><i class="fas fa-arrow-trend-down"></i></span>
                        Penyusutan / Pengeluaran
                    </h3>
                    <span class="open-badge"><i class="fas fa-external-link-alt" style="font-size:.6rem;"></i> Buka</span>
                </div>
                <div class="chart-card-body">
                    <canvas id="chart-expense"></canvas>
                </div>
                <div class="chart-card-footer">
                    <i class="fas fa-info-circle" style="font-size:.7rem;"></i>
                    Klik kartu untuk membuka laporan lengkap
                </div>
            </div>
        </div>
    </div>
@endif

@if($showBlastingWidgets)
    <div class="section-label"><i class="fas fa-paper-plane" style="color:var(--blue-primary);font-size:.7rem;"></i> Grafik Blasting</div>
    <div class="row">
        <div class="col-lg-6 col-md-6 col-sm-12">
            <div class="dash-chart-card accent-email dashboard-chart-card anim-delay-3" data-href="{{ data_get($emailChart, 'url') }}">
                <div class="chart-card-header">
                    <h3 class="chart-card-title">
                        <span class="title-icon icon-email"><i class="fas fa-envelope"></i></span>
                        Blasting Email
                    </h3>
                    <span class="open-badge"><i class="fas fa-external-link-alt" style="font-size:.6rem;"></i> Buka</span>
                </div>
                <div class="chart-card-body">
                    <canvas id="chart-email"></canvas>
                </div>
                <div class="chart-card-footer">
                    <i class="fas fa-info-circle" style="font-size:.7rem;"></i>
                    Klik kartu untuk membuka laporan lengkap
                </div>
            </div>
        </div>
        <div class="col-lg-6 col-md-6 col-sm-12">
            <div class="dash-chart-card accent-wa dashboard-chart-card anim-delay-4" data-href="{{ data_get($waChart, 'url') }}">
                <div class="chart-card-header">
                    <h3 class="chart-card-title">
                        <span class="title-icon icon-wa"><i class="fab fa-whatsapp"></i></span>
                        Blasting WA
                    </h3>
                    <span class="open-badge"><i class="fas fa-external-link-alt" style="font-size:.6rem;"></i> Buka</span>
                </div>
                <div class="chart-card-body">
                    <canvas id="chart-wa"></canvas>
                </div>
                <div class="chart-card-footer">
                    <i class="fas fa-info-circle" style="font-size:.7rem;"></i>
                    Klik kartu untuk membuka laporan lengkap
                </div>
            </div>
        </div>
    </div>
@endif
@endsection

@section('js')
<script src="{{ asset('vendor/adminlte/plugins/chart.js/Chart.min.js') }}"></script>
<script>
    (function () {
        const showFinanceWidgets = @json((bool) ($showFinanceWidgets ?? false));
        const showBlastingWidgets = @json((bool) ($showBlastingWidgets ?? false));
        const chartDataEndpoint = @json(route('dashboard.chart-data'));
        const refreshIntervalMs = 60000;
        let isRefreshing = false;

        const numberFormatter = new Intl.NumberFormat('id-ID', {
            minimumFractionDigits: 0,
            maximumFractionDigits: 0
        });
        const currencyFormatter = new Intl.NumberFormat('id-ID', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });

        const sharedGridLines = {
            display: true,
            color: 'rgba(60, 60, 60, 0.06)'
        };

        const saldoValueElement = document.getElementById('dashboard-saldo-value');
        const saldoUpdatedElement = document.getElementById('dashboard-saldo-updated');

        function formatCurrency(value) {
            const number = Number(value);
            return 'Rp ' + currencyFormatter.format(Number.isNaN(number) ? 0 : number);
        }

        const incomeCanvas = document.getElementById('chart-income');
        const expenseCanvas = document.getElementById('chart-expense');
        const waCanvas = document.getElementById('chart-wa');
        const emailCanvas = document.getElementById('chart-email');
        let incomeChart = null;
        let expenseChart = null;
        let waChart = null;
        let emailChart = null;

        if (showFinanceWidgets && incomeCanvas) {
            incomeChart = new Chart(incomeCanvas.getContext('2d'), {
                type: 'line',
                data: {
                    labels: @json(data_get($incomeChart, 'labels', [])),
                    datasets: [{
                        label: 'Income',
                        data: @json(data_get($incomeChart, 'values', [])),
                        borderColor: '#2563eb',
                        backgroundColor: 'rgba(37, 99, 235, 0.12)',
                        fill: true,
                        tension: 0.4,
                        pointRadius: 2.5,
                        pointBackgroundColor: '#2563eb',
                        borderWidth: 2.5
                    }]
                },
                options: {
                    maintainAspectRatio: false,
                    responsive: true,
                    legend: { display: false },
                    scales: {
                        xAxes: [{ gridLines: sharedGridLines, ticks: { maxTicksLimit: 4, fontColor: '#94a3b8', fontSize: 10 } }],
                        yAxes: [{
                            ticks: {
                                beginAtZero: true, maxTicksLimit: 3, fontColor: '#94a3b8', fontSize: 10,
                                callback: (value) => 'Rp ' + numberFormatter.format(value)
                            },
                            gridLines: sharedGridLines
                        }]
                    }
                }
            });
        }

        if (showFinanceWidgets && expenseCanvas) {
            expenseChart = new Chart(expenseCanvas.getContext('2d'), {
                type: 'line',
                data: {
                    labels: @json(data_get($expenseChart, 'labels', [])),
                    datasets: [
                        {
                            label: 'Pengeluaran',
                            data: @json(data_get($expenseChart, 'expenseValues', [])),
                            borderColor: '#ef4444',
                            backgroundColor: 'rgba(239, 68, 68, 0.1)',
                            fill: false, tension: 0.4, pointRadius: 2.5, borderWidth: 2.5,
                            pointBackgroundColor: '#ef4444'
                        },
                        {
                            label: 'Penyusutan',
                            data: @json(data_get($expenseChart, 'depreciationValues', [])),
                            borderColor: '#f59e0b',
                            backgroundColor: 'rgba(245, 158, 11, 0.1)',
                            fill: false, tension: 0.4, pointRadius: 2.5, borderWidth: 2.5,
                            pointBackgroundColor: '#f59e0b'
                        }
                    ]
                },
                options: {
                    maintainAspectRatio: false,
                    responsive: true,
                    legend: { display: true, labels: { boxWidth: 9, fontSize: 10, fontColor: '#64748b' } },
                    scales: {
                        xAxes: [{ gridLines: sharedGridLines, ticks: { maxTicksLimit: 4, fontColor: '#94a3b8', fontSize: 10 } }],
                        yAxes: [{
                            ticks: {
                                beginAtZero: true, maxTicksLimit: 3, fontColor: '#94a3b8', fontSize: 10,
                                callback: (value) => 'Rp ' + numberFormatter.format(value)
                            },
                            gridLines: sharedGridLines
                        }]
                    }
                }
            });
        }

        if (showBlastingWidgets && waCanvas) {
            waChart = new Chart(waCanvas.getContext('2d'), {
                type: 'line',
                data: {
                    labels: @json(data_get($waChart, 'labels', [])),
                    datasets: [{
                        label: 'Blast WA',
                        data: @json(data_get($waChart, 'values', [])),
                        borderColor: '#10b981',
                        backgroundColor: 'rgba(16, 185, 129, 0.12)',
                        fill: true, tension: 0.4, pointRadius: 2.5, borderWidth: 2.5,
                        pointBackgroundColor: '#10b981'
                    }]
                },
                options: {
                    maintainAspectRatio: false,
                    responsive: true,
                    legend: { display: false },
                    scales: {
                        xAxes: [{ gridLines: sharedGridLines, ticks: { maxTicksLimit: 4, fontColor: '#94a3b8', fontSize: 10 } }],
                        yAxes: [{ ticks: { beginAtZero: true, maxTicksLimit: 3, fontColor: '#94a3b8', fontSize: 10 }, gridLines: sharedGridLines }]
                    }
                }
            });
        }

        if (showBlastingWidgets && emailCanvas) {
            emailChart = new Chart(emailCanvas.getContext('2d'), {
                type: 'line',
                data: {
                    labels: @json(data_get($emailChart, 'labels', [])),
                    datasets: [{
                        label: 'Blast Email',
                        data: @json(data_get($emailChart, 'values', [])),
                        borderColor: '#06b6d4',
                        backgroundColor: 'rgba(6, 182, 212, 0.12)',
                        fill: true, tension: 0.4, pointRadius: 2.5, borderWidth: 2.5,
                        pointBackgroundColor: '#06b6d4'
                    }]
                },
                options: {
                    maintainAspectRatio: false,
                    responsive: true,
                    legend: { display: false },
                    scales: {
                        xAxes: [{ gridLines: sharedGridLines, ticks: { maxTicksLimit: 4, fontColor: '#94a3b8', fontSize: 10 } }],
                        yAxes: [{ ticks: { beginAtZero: true, maxTicksLimit: 3, fontColor: '#94a3b8', fontSize: 10 }, gridLines: sharedGridLines }]
                    }
                }
            });
        }

        function applyDashboardData(payload) {
            if (!payload || typeof payload !== 'object') return;

            if (showFinanceWidgets && saldoValueElement) {
                saldoValueElement.textContent = formatCurrency(payload.saldo ?? 0);
            }
            if (showFinanceWidgets && saldoUpdatedElement) {
                saldoUpdatedElement.textContent = payload.saldoUpdatedAt || '-';
            }

            if (incomeChart && payload.incomeChart) {
                incomeChart.data.labels = payload.incomeChart.labels || [];
                incomeChart.data.datasets[0].data = payload.incomeChart.values || [];
                incomeChart.update();
            }

            if (expenseChart && payload.expenseChart) {
                expenseChart.data.labels = payload.expenseChart.labels || [];
                expenseChart.data.datasets[0].data = payload.expenseChart.expenseValues || [];
                expenseChart.data.datasets[1].data = payload.expenseChart.depreciationValues || [];
                expenseChart.update();
            }

            if (showBlastingWidgets && waChart && payload.waChart) {
                waChart.data.labels = payload.waChart.labels || [];
                waChart.data.datasets[0].data = payload.waChart.values || [];
                waChart.update();
            }

            if (showBlastingWidgets && emailChart && payload.emailChart) {
                emailChart.data.labels = payload.emailChart.labels || [];
                emailChart.data.datasets[0].data = payload.emailChart.values || [];
                emailChart.update();
            }
        }

        async function refreshDashboardData() {
            if (isRefreshing) return;
            isRefreshing = true;
            try {
                const response = await fetch(chartDataEndpoint, {
                    method: 'GET',
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                });
                if (!response.ok) return;
                const payload = await response.json();
                applyDashboardData(payload);
            } catch (error) {
                // Ignore intermittent network failure and try again in next interval.
            } finally {
                isRefreshing = false;
            }
        }

        document.querySelectorAll('.dashboard-chart-card').forEach((card) => {
            card.addEventListener('click', () => {
                const targetUrl = card.getAttribute('data-href');
                if (targetUrl) window.location.href = targetUrl;
            });
        });

        setInterval(refreshDashboardData, refreshIntervalMs);
    })();
</script>
@endsection