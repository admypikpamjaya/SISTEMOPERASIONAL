@extends('layouts.app')

@section('section_name', 'Dashboard')

@section('content')
<style>
    .medium-chart-card {
        margin-bottom: 1rem;
    }
    .medium-chart-card .card-header { padding: .6rem .75rem; }
    .medium-chart-card .card-title { font-size: .92rem; font-weight: 700; line-height: 1.2; }
    .medium-chart-card .card-body { padding: .55rem .75rem !important; }
    .medium-chart-card .card-footer { font-size: .78rem; padding: .45rem .75rem !important; }
    .medium-chart-canvas { width: 100% !important; height: 82px !important; }
</style>

<div class="row">
    @if($showFinanceWidgets)
        <div class="col-lg-4 col-md-6 col-sm-12">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3 id="dashboard-saldo-value">Rp {{ number_format((float) ($saldo ?? 0), 2, ',', '.') }}</h3>
                    <p>Saldo Finance (All Report)</p>
                    <small>
                        Update WIB:
                        <span id="dashboard-saldo-updated">{{ $saldoUpdatedAt ?? '-' }}</span>
                    </small>
                </div>
                <div class="icon">
                    <i class="fas fa-wallet"></i>
                </div>
                <a href="{{ route('finance.report.snapshots') }}" class="small-box-footer">
                    Lihat Snapshot Finance <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>
        <div class="col-lg-8 col-md-6 col-sm-12">
    @else
        <div class="col-12">
    @endif
        <div class="card">
            <div class="card-body py-4">
                <h3 class="text-center mb-0">Selamat Datang di Aplikasi Sistem Operasional Yayasan YPIK</h3>
            </div>
        </div>
    </div>
</div>

@if($showFinanceWidgets)
    <div class="row">
        <div class="col-lg-6 col-md-6 col-sm-12">
            <div class="card card-outline card-primary dashboard-chart-card medium-chart-card" data-href="{{ data_get($incomeChart, 'url') }}">
                <div class="card-header">
                    <h3 class="card-title mb-0">Income</h3>
                </div>
                <div class="card-body py-2">
                    <canvas id="chart-income" class="medium-chart-canvas" height="82"></canvas>
                </div>
                <div class="card-footer py-2 text-muted">
                    Klik untuk buka.
                </div>
            </div>
        </div>
        <div class="col-lg-6 col-md-6 col-sm-12">
            <div class="card card-outline card-danger dashboard-chart-card medium-chart-card" data-href="{{ data_get($expenseChart, 'url') }}">
                <div class="card-header">
                    <h3 class="card-title mb-0">Penyusutan/Pengeluaran</h3>
                </div>
                <div class="card-body py-2">
                    <canvas id="chart-expense" class="medium-chart-canvas" height="82"></canvas>
                </div>
                <div class="card-footer py-2 text-muted">
                    Klik untuk buka.
                </div>
            </div>
        </div>
    </div>
@endif

@if($showBlastingWidgets)
    <div class="row">
        <div class="col-lg-6 col-md-6 col-sm-12">
            <div class="card card-outline card-info dashboard-chart-card medium-chart-card" data-href="{{ data_get($emailChart, 'url') }}">
                <div class="card-header">
                    <h3 class="card-title mb-0">Blasting Email</h3>
                </div>
                <div class="card-body py-2">
                    <canvas id="chart-email" class="medium-chart-canvas" height="82"></canvas>
                </div>
                <div class="card-footer py-2 text-muted">
                    Klik untuk buka.
                </div>
            </div>
        </div>
        <div class="col-lg-6 col-md-6 col-sm-12">
            <div class="card card-outline card-success dashboard-chart-card medium-chart-card" data-href="{{ data_get($waChart, 'url') }}">
                <div class="card-header">
                    <h3 class="card-title mb-0">Blasting WA</h3>
                </div>
                <div class="card-body py-2">
                    <canvas id="chart-wa" class="medium-chart-canvas" height="82"></canvas>
                </div>
                <div class="card-footer py-2 text-muted">
                    Klik untuk buka.
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
            color: 'rgba(60, 60, 60, 0.08)'
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
                        borderColor: '#007bff',
                        backgroundColor: 'rgba(0, 123, 255, 0.15)',
                        fill: true,
                        tension: 0.35,
                        pointRadius: 2,
                        borderWidth: 2
                    }]
                },
                options: {
                    maintainAspectRatio: false,
                    responsive: true,
                    legend: {
                        display: false
                    },
                    scales: {
                        xAxes: [{
                            gridLines: sharedGridLines,
                            ticks: { maxTicksLimit: 4 }
                        }],
                        yAxes: [{
                            ticks: {
                                beginAtZero: true,
                                maxTicksLimit: 4,
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
                            borderColor: '#dc3545',
                            backgroundColor: 'rgba(220, 53, 69, 0.15)',
                            fill: false,
                            tension: 0.3,
                            pointRadius: 2,
                            borderWidth: 2
                        },
                        {
                            label: 'Penyusutan',
                            data: @json(data_get($expenseChart, 'depreciationValues', [])),
                            borderColor: '#ffc107',
                            backgroundColor: 'rgba(255, 193, 7, 0.15)',
                            fill: false,
                            tension: 0.3,
                            pointRadius: 2,
                            borderWidth: 2
                        }
                    ]
                },
                options: {
                    maintainAspectRatio: false,
                    responsive: true,
                    legend: {
                        display: true,
                        labels: { boxWidth: 10, fontSize: 10 }
                    },
                    scales: {
                        xAxes: [{
                            gridLines: sharedGridLines,
                            ticks: { maxTicksLimit: 4 }
                        }],
                        yAxes: [{
                            ticks: {
                                beginAtZero: true,
                                maxTicksLimit: 4,
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
                        borderColor: '#28a745',
                        backgroundColor: 'rgba(40, 167, 69, 0.15)',
                        fill: true,
                        tension: 0.3,
                        pointRadius: 2,
                        borderWidth: 2
                    }]
                },
                options: {
                    maintainAspectRatio: false,
                    responsive: true,
                    legend: {
                        display: false
                    },
                    scales: {
                        xAxes: [{
                            gridLines: sharedGridLines,
                            ticks: { maxTicksLimit: 4 }
                        }],
                        yAxes: [{
                            ticks: { beginAtZero: true, maxTicksLimit: 4 },
                            gridLines: sharedGridLines
                        }]
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
                        borderColor: '#17a2b8',
                        backgroundColor: 'rgba(23, 162, 184, 0.15)',
                        fill: true,
                        tension: 0.3,
                        pointRadius: 2,
                        borderWidth: 2
                    }]
                },
                options: {
                    maintainAspectRatio: false,
                    responsive: true,
                    legend: {
                        display: false
                    },
                    scales: {
                        xAxes: [{
                            gridLines: sharedGridLines,
                            ticks: { maxTicksLimit: 4 }
                        }],
                        yAxes: [{
                            ticks: { beginAtZero: true, maxTicksLimit: 4 },
                            gridLines: sharedGridLines
                        }]
                    }
                }
            });
        }

        function applyDashboardData(payload) {
            if (!payload || typeof payload !== 'object') {
                return;
            }

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
            if (isRefreshing) {
                return;
            }

            isRefreshing = true;
            try {
                const response = await fetch(chartDataEndpoint, {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                if (!response.ok) {
                    return;
                }

                const payload = await response.json();
                applyDashboardData(payload);
            } catch (error) {
                // Ignore intermittent network failure and try again in next interval.
            } finally {
                isRefreshing = false;
            }
        }

        document.querySelectorAll('.dashboard-chart-card').forEach((card) => {
            card.style.cursor = 'pointer';
            card.addEventListener('click', () => {
                const targetUrl = card.getAttribute('data-href');
                if (targetUrl) {
                    window.location.href = targetUrl;
                }
            });
        });

        setInterval(refreshDashboardData, refreshIntervalMs);
    })();
</script>
@endsection
