@extends('layouts.app')

@section('section_name', 'Finance Dashboard')

@section('content')
<div class="row">
    <div class="col-lg-8 col-md-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title mb-0">Filter Snapshot Finance</h3>
            </div>
            <div class="card-body">
                <form method="GET" action="{{ route('finance.dashboard') }}" class="form-row">
                    <div class="form-group col-md-3">
                        <label for="filter_type">Tipe Filter</label>
                        <select name="filter_type" id="filter_type" class="form-control">
                            <option value="monthly" {{ ($filters['filter_type'] ?? 'monthly') === 'monthly' ? 'selected' : '' }}>Bulanan</option>
                            <option value="daily" {{ ($filters['filter_type'] ?? '') === 'daily' ? 'selected' : '' }}>Harian</option>
                        </select>
                    </div>

                    {{-- FILTER BULANAN --}}
                    <div class="contents" id="filter-monthly" style="display: contents;">
                        <div class="form-group col-md-3">
                            <label for="month">Bulan</label>
                            <select name="month" id="month" class="form-control">
                                <option value="">Semua</option>
                                @for($m = 1; $m <= 12; $m++)
                                    <option value="{{ $m }}" {{ (int) ($filters['month'] ?? 0) === $m ? 'selected' : '' }}>
                                        {{ $m }}
                                    </option>
                                @endfor
                            </select>
                        </div>
                        <div class="form-group col-md-3">
                            <label for="year">Tahun</label>
                            <input
                                type="number"
                                name="year"
                                id="year"
                                class="form-control"
                                min="1900"
                                max="2100"
                                value="{{ $filters['year'] }}"
                            >
                        </div>
                    </div>

                    {{-- FILTER HARIAN --}}
                    <div class="form-group col-md-6" id="filter-daily" style="display: none;">
                        <label for="date">Tanggal</label>
                        <input
                            type="date"
                            name="date"
                            id="date"
                            class="form-control"
                            value="{{ $filters['date'] ?? '' }}"
                        >
                    </div>
                    <div class="form-group col-md-2">
                        <label for="per_page">Per Page</label>
                        <select name="per_page" id="per_page" class="form-control">
                            @foreach([5, 10, 20, 50] as $size)
                                <option value="{{ $size }}" {{ (int) request('per_page', 5) === $size ? 'selected' : '' }}>
                                    {{ $size }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group col-md-4 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary mr-2">
                            <i class="fas fa-filter mr-1"></i> Filter
                        </button>
                        <a href="{{ route('finance.dashboard') }}" class="btn btn-default">
                            Reset
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="col-lg-4 col-md-12">
        <div class="small-box bg-info">
            <div class="inner">
                <h3>{{ $totalReports }}</h3>
                <p>Total Snapshot</p>
            </div>
            <div class="icon">
                <i class="fas fa-file-invoice"></i>
            </div>
            <a href="{{ route('finance.report.snapshots', ['year' => $filters['year']]) }}" class="small-box-footer">
                Buka Snapshot Laporan <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title mb-0">Snapshot Terbaru</h3>
                <a href="{{ route('finance.depreciation.index') }}" class="btn btn-sm btn-outline-primary">
                    <i class="fas fa-calculator mr-1"></i> Hitung Penyusutan
                </a>
            </div>
            <div class="card-body p-0 table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Tipe</th>
                            <th>Versi</th>
                            <th>Saldo Awal</th>
                            <th>Saldo Akhir</th>
                            <th>Generated At</th>
                            <th>Generated By</th>
                            <th>Read Only</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($reports as $report)
                            <tr>
                                <td>{{ $report->report_type }}</td>
                                <td>{{ $report->version_no }}</td>
                                <td>Rp {{ number_format((float) data_get($report->summary, 'opening_balance', 0), 2, ',', '.') }}</td>
                                <td>Rp {{ number_format((float) data_get($report->summary, 'ending_balance', data_get($report->summary, 'net_result', 0)), 2, ',', '.') }}</td>
                                <td>{{ optional($report->generated_at)->format('Y-m-d H:i:s') ?? '-' }}</td>
                                <td>{{ $report->user?->name ?? '-' }}</td>
                                <td>{{ $report->is_read_only ? 'Yes' : 'No' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4">Belum ada snapshot laporan finance.</td>
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

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const filterTypeSelect = document.getElementById('filter_type');
        const filterMonthly = document.getElementById('filter-monthly');
        const filterDaily = document.getElementById('filter-daily');
        const dateInput = document.getElementById('date');

        function toggleFilters() {
            if (filterTypeSelect.value === 'daily') {
                filterMonthly.style.display = 'none';
                filterMonthly.querySelectorAll('input, select').forEach(el => el.disabled = true);
                
                filterDaily.style.display = 'block';
                dateInput.disabled = false;
                dateInput.required = true;
            } else {
                filterMonthly.style.display = 'contents';
                filterMonthly.querySelectorAll('input, select').forEach(el => el.disabled = false);

                filterDaily.style.display = 'none';
                dateInput.disabled = true;
                dateInput.required = false;
            }
        }

        filterTypeSelect.addEventListener('change', toggleFilters);
        toggleFilters(); // Initialize on load
    });
</script>
@endpush
