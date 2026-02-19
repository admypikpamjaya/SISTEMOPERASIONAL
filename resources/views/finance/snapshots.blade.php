@extends('layouts.app')

@section('section_name', 'Snapshot Laporan Finance')

@section('content')
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

<div class="row">
    <div class="col-12">
        <div class="card card-outline card-primary">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title mb-0">Filter Snapshot Laporan</h3>
                <a href="{{ route('finance.report.index') }}" class="btn btn-sm btn-outline-success">
                    <i class="fas fa-plus mr-1"></i> Input Finance Report
                </a>
            </div>
            <div class="card-body">
                <form method="GET" action="{{ route('finance.report.snapshots') }}" id="snapshot-filter-form">
                    <div class="form-row">
                        <div class="form-group col-md-2">
                            <label for="period_type">Periode</label>
                            <select name="period_type" id="period_type" class="form-control">
                                <option value="ALL" {{ $periodType === 'ALL' ? 'selected' : '' }}>All Report</option>
                                <option value="DAILY" {{ $periodType === 'DAILY' ? 'selected' : '' }}>Harian</option>
                                <option value="MONTHLY" {{ $periodType === 'MONTHLY' ? 'selected' : '' }}>Bulanan</option>
                                <option value="YEARLY" {{ $periodType === 'YEARLY' ? 'selected' : '' }}>Tahunan</option>
                            </select>
                        </div>

                        <div class="form-group col-md-3" id="report_date_group">
                            <label for="report_date">Sebagai Tanggal</label>
                            <input type="date" name="report_date" id="report_date" class="form-control" value="{{ $reportDate }}">
                        </div>

                        <div class="form-group col-md-2" id="month_group">
                            <label for="month">Bulan</label>
                            <select name="month" id="month" class="form-control">
                                @for($m = 1; $m <= 12; $m++)
                                    <option value="{{ $m }}" {{ $month === $m ? 'selected' : '' }}>{{ $m }}</option>
                                @endfor
                            </select>
                        </div>

                        <div class="form-group col-md-2" id="year_group">
                            <label for="year">Tahun</label>
                            <input type="number" name="year" id="year" class="form-control" min="1900" max="2100" value="{{ $year }}">
                        </div>

                        <div class="form-group col-md-3">
                            <label for="comparison_type">Perbandingan</label>
                            <select name="comparison_type" id="comparison_type" class="form-control">
                                <option value="NONE" {{ $comparisonType === 'NONE' ? 'selected' : '' }}>Tidak ada</option>
                                <option value="PREVIOUS_PERIOD" {{ $comparisonType === 'PREVIOUS_PERIOD' ? 'selected' : '' }}>Periode Sebelumnya</option>
                                <option value="SAME_PERIOD_LAST_YEAR" {{ $comparisonType === 'SAME_PERIOD_LAST_YEAR' ? 'selected' : '' }}>Periode Sama Tahun Lalu</option>
                                <option value="SPECIFIC_DATE" {{ $comparisonType === 'SPECIFIC_DATE' ? 'selected' : '' }}>Tanggal Spesifik</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-2" id="comparison_offset_group">
                            <label for="comparison_offset">Jarak Periode</label>
                            <input
                                type="number"
                                name="comparison_offset"
                                id="comparison_offset"
                                class="form-control"
                                min="1"
                                max="36"
                                value="{{ max(1, $comparisonOffset) }}"
                            >
                        </div>

                        <div class="form-group col-md-3" id="comparison_date_group">
                            <label for="comparison_date">Tanggal Pembanding</label>
                            <input
                                type="date"
                                name="comparison_date"
                                id="comparison_date"
                                class="form-control"
                                value="{{ $comparisonDate }}"
                            >
                        </div>

                        <div class="form-group col-md-2">
                            <label for="per_page">Per Page</label>
                            <select name="per_page" id="per_page" class="form-control">
                                @foreach([10, 20, 50, 100] as $size)
                                    <option value="{{ $size }}" {{ $perPage === $size ? 'selected' : '' }}>
                                        {{ $size }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group col-md-5 d-flex align-items-end justify-content-end">
                            <button type="submit" class="btn btn-primary mr-2">
                                <i class="fas fa-search mr-1"></i> Terapkan
                            </button>
                            <a
                                href="{{ route('finance.report.snapshots', ['period_type' => 'MONTHLY', 'month' => now()->month, 'year' => now()->year]) }}"
                                class="btn btn-default"
                            >
                                Reset
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card card-outline card-info">
            <div class="card-body py-3">
                <div class="row">
                    <div class="col-md-3">
                        <div class="small text-muted">Jumlah Snapshot</div>
                        <div class="h4 mb-0">{{ number_format($totalCount, 0, ',', '.') }}</div>
                    </div>
                    <div class="col-md-3">
                        <div class="small text-muted">Total Saldo Awal</div>
                        <div class="h4 mb-0">Rp {{ number_format($totalOpeningBalance, 2, ',', '.') }}</div>
                    </div>
                    <div class="col-md-3">
                        <div class="small text-muted">Total Saldo Keseluruhan</div>
                        <div class="h4 mb-0 text-primary">Rp {{ number_format($totalEndingBalance, 2, ',', '.') }}</div>
                    </div>
                    <div class="col-md-3">
                        <div class="small text-muted">Total Surplus (Defisit)</div>
                        <div class="h4 mb-0 {{ $totalNetResult >= 0 ? 'text-success' : 'text-danger' }}">
                            Rp {{ number_format($totalNetResult, 2, ',', '.') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title mb-0">Daftar Snapshot Laporan</h3>
            </div>
            <div class="card-body table-responsive p-0">
                @if($reports->total() === 0)
                    <div class="p-4">
                        <div class="alert alert-warning mb-0">
                            Belum ada snapshot laporan untuk filter periode yang dipilih.
                        </div>
                    </div>
                @endif
                <table class="table table-striped mb-0">
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
                                $endingBalance = (float) data_get($report->summary, 'ending_balance', 0);
                                $netResult = (float) data_get($report->summary, 'net_result', 0);
                                $comparison = $comparisons[$report->id] ?? null;
                            @endphp
                            <tr>
                                <td>
                                    <a href="{{ route('finance.report.show', $report->id) }}" class="btn btn-sm btn-outline-primary mb-1">
                                        Preview
                                    </a>
                                    <a href="{{ route('finance.report.edit', $report->id) }}" class="btn btn-sm btn-outline-warning mb-1">
                                        Edit
                                    </a>
                                </td>
                                <td>{{ $periodLabel }}</td>
                                <td>{{ $rowPeriodType }}</td>
                                <td>{{ $report->version_no }}</td>
                                <td>Rp {{ number_format($openingBalance, 2, ',', '.') }}</td>
                                <td>Rp {{ number_format($endingBalance, 2, ',', '.') }}</td>
                                <td>Rp {{ number_format($netResult, 2, ',', '.') }}</td>
                                <td>
                                    @if(!$comparison)
                                        <span class="text-muted">Tidak ada perbandingan</span>
                                    @elseif(!data_get($comparison, 'available', false))
                                        <div class="small font-weight-bold">{{ data_get($comparison, 'label', 'Perbandingan') }}</div>
                                        <div class="small text-muted">{{ data_get($comparison, 'message', 'Data pembanding tidak ditemukan.') }}</div>
                                    @else
                                        @php
                                            $diffNet = (float) data_get($comparison, 'difference_net_result', 0);
                                            $diffBalance = (float) data_get($comparison, 'difference_ending_balance', 0);
                                        @endphp
                                        <div class="small font-weight-bold">{{ data_get($comparison, 'label', 'Perbandingan') }}</div>
                                        <div class="small {{ $diffNet >= 0 ? 'text-success' : 'text-danger' }}">
                                            Surplus: {{ $diffNet >= 0 ? '+' : '' }}Rp {{ number_format($diffNet, 2, ',', '.') }}
                                        </div>
                                        <div class="small {{ $diffBalance >= 0 ? 'text-success' : 'text-danger' }}">
                                            Saldo Akhir: {{ $diffBalance >= 0 ? '+' : '' }}Rp {{ number_format($diffBalance, 2, ',', '.') }}
                                        </div>
                                    @endif
                                </td>
                                <td>{{ optional($report->generated_at)->format('Y-m-d H:i:s') ?? '-' }}</td>
                                <td>{{ $report->user?->name ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center py-4">Tidak ada snapshot laporan untuk filter ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-footer clearfix">
                {{ $reports->appends(request()->query())->links() }}
            </div>
        </div>
    </div>
</div>
@endsection

@section('js')
<script>
    (function () {
        const periodTypeSelect = document.getElementById('period_type');
        const reportDateGroup = document.getElementById('report_date_group');
        const reportDateInput = document.getElementById('report_date');
        const monthGroup = document.getElementById('month_group');
        const monthInput = document.getElementById('month');
        const yearGroup = document.getElementById('year_group');
        const yearInput = document.getElementById('year');
        const comparisonTypeSelect = document.getElementById('comparison_type');
        const comparisonOffsetGroup = document.getElementById('comparison_offset_group');
        const comparisonOffsetInput = document.getElementById('comparison_offset');
        const comparisonDateGroup = document.getElementById('comparison_date_group');
        const comparisonDateInput = document.getElementById('comparison_date');

        function syncPeriodFilter() {
            const periodType = periodTypeSelect.value;
            const isAll = periodType === 'ALL';
            const isDaily = periodType === 'DAILY';
            const isMonthly = periodType === 'MONTHLY';
            const isYearly = periodType === 'YEARLY';

            reportDateGroup.style.display = isDaily ? '' : 'none';
            monthGroup.style.display = isMonthly ? '' : 'none';
            yearGroup.style.display = (isMonthly || isYearly) ? '' : 'none';

            reportDateInput.disabled = !isDaily;
            reportDateInput.required = isDaily;

            monthInput.disabled = !isMonthly;
            monthInput.required = isMonthly;

            yearInput.disabled = !(isMonthly || isYearly);
            yearInput.required = (isMonthly || isYearly);

            if (isAll) {
                comparisonTypeSelect.value = 'NONE';
            }

            comparisonTypeSelect.disabled = isAll;
        }

        function syncComparisonFilter() {
            const comparisonType = comparisonTypeSelect.value;
            const useOffset = comparisonType === 'PREVIOUS_PERIOD';
            const useDate = comparisonType === 'SPECIFIC_DATE';

            comparisonOffsetGroup.style.display = useOffset ? '' : 'none';
            comparisonDateGroup.style.display = useDate ? '' : 'none';

            comparisonOffsetInput.disabled = !useOffset;
            comparisonOffsetInput.required = useOffset;

            comparisonDateInput.disabled = !useDate;
            comparisonDateInput.required = useDate;
        }

        periodTypeSelect.addEventListener('change', syncPeriodFilter);
        comparisonTypeSelect.addEventListener('change', syncComparisonFilter);

        syncPeriodFilter();
        syncComparisonFilter();
    })();
</script>
@endsection
