@extends('layouts.app')

@section('section_name', 'Finance Dashboard')

@section('content')
<div class="row">
    <div class="col-lg-8 col-md-12">
        <div class="card card-primary card-outline animate__animated animate__fadeInLeft">
            <div class="card-header bg-gradient-primary text-white">
                <h3 class="card-title mb-0">
                    <i class="fas fa-filter mr-2"></i>Filter Snapshot Finance
                </h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool text-white" data-card-widget="collapse">
                        <i class="fas fa-minus"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <form method="GET" action="{{ route('finance.dashboard') }}" class="form-row align-items-end">
                    <div class="form-group col-md-2">
                        <label for="filter_type">Tipe Filter</label>
                        <select name="filter_type" id="filter_type" class="form-control">
                            <option value="monthly" {{ ($filters['filter_type'] ?? 'monthly') === 'monthly' ? 'selected' : '' }}>Bulanan</option>
                            <option value="yearly" {{ ($filters['filter_type'] ?? '') === 'yearly' ? 'selected' : '' }}>Tahunan</option>
                            <option value="custom" {{ ($filters['filter_type'] ?? '') === 'custom' ? 'selected' : '' }}>Custom (Tanggal/Bulan/Tahun)</option>
                        </select>
                    </div>

                    <div class="form-group col-md-2" id="month-wrapper">
                        <label for="month">Bulan</label>
                        <select name="month" id="month" class="form-control">
                            <option value="">Semua</option>
                            @for($m = 1; $m <= 12; $m++)
                                <option value="{{ $m }}" {{ (int) ($filters['month'] ?? 0) === $m ? 'selected' : '' }}>
                                    {{ sprintf('%02d', $m) }}
                                </option>
                            @endfor
                        </select>
                    </div>

                    <div class="form-group col-md-2" id="year-wrapper">
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

                    <div class="form-group col-md-3" id="date-wrapper">
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
                        <label for="per_page">
                            <i class="fas fa-list-ol mr-1 text-primary"></i>Per Page
                        </label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text bg-light border-right-0">
                                    <i class="fas fa-sort-amount-down text-primary"></i>
                                </span>
                            </div>
                            <select name="per_page" id="per_page" class="form-control border-left-0">
                                @foreach([5, 10, 20, 50, 100] as $size)
                                    <option value="{{ $size }}" {{ (int) request('per_page', 5) === $size ? 'selected' : '' }}>
                                        {{ $size }} Data
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="form-group col-md-4 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary mr-2 shadow-sm px-4">
                            <i class="fas fa-filter mr-2"></i> Terapkan Filter
                        </button>
                        <a href="{{ route('finance.dashboard') }}" class="btn btn-outline-secondary shadow-sm px-4">
                            <i class="fas fa-sync-alt mr-2"></i> Reset
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="col-lg-4 col-md-12">
        <div class="small-box bg-gradient-info shadow-lg animate__animated animate__fadeInRight">
            <div class="inner">
                <h3 class="font-weight-bold counter">{{ $totalReports }}</h3>
                <p class="mb-0">Total Snapshot Laporan</p>
            </div>
            <div class="icon">
                <i class="fas fa-file-invoice fa-3x"></i>
            </div>
            <a href="{{ route('finance.report.snapshots', ['year' => $filters['year']]) }}" class="small-box-footer">
                <span>Buka Snapshot Laporan</span>
                <i class="fas fa-arrow-circle-right ml-1"></i>
            </a>
        </div>
    </div>
</div>

<!-- Summary Cards -->
<div class="row mt-4">
    <div class="col-lg-3 col-6">
        <div class="small-box bg-gradient-success animate__animated animate__fadeInUp" style="animation-delay: 0.1s">
            <div class="inner">
                <h3 class="counter">{{ $reports->total() }}</h3>
                <p>Total Data Ditampilkan</p>
            </div>
            <div class="icon">
                <i class="fas fa-database"></i>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-gradient-warning animate__animated animate__fadeInUp" style="animation-delay: 0.2s">
            <div class="inner">
                <h3 class="counter">{{ $reports->where('is_read_only', true)->count() }}</h3>
                <p>Snapshot Read Only</p>
            </div>
            <div class="icon">
                <i class="fas fa-lock"></i>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-gradient-danger animate__animated animate__fadeInUp" style="animation-delay: 0.3s">
            <div class="inner">
                <h3 class="counter">{{ $reports->where('is_read_only', false)->count() }}</h3>
                <p>Snapshot Editable</p>
            </div>
            <div class="icon">
                <i class="fas fa-pen"></i>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-gradient-secondary animate__animated animate__fadeInUp" style="animation-delay: 0.4s">
            <div class="inner">
                <h3 class="counter">{{ $reports->unique('report_type')->count() }}</h3>
                <p>Tipe Laporan</p>
            </div>
            <div class="icon">
                <i class="fas fa-tags"></i>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-12">
        <div class="card card-primary card-outline animate__animated animate__fadeInUp">
            <div class="card-header bg-gradient-primary text-white d-flex justify-content-between align-items-center py-3">
                <h3 class="card-title mb-0">
                    <i class="fas fa-history mr-2"></i>Snapshot Terbaru
                </h3>
                <div>
                    <a href="{{ route('finance.depreciation.index') }}" class="btn btn-sm btn-light shadow-sm">
                        <i class="fas fa-calculator mr-1"></i> Hitung Penyusutan
                    </a>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0" id="snapshotTable">
                        <thead class="bg-light">
                            <tr>
                                <th class="border-top-0">
                                    <i class="fas fa-tag mr-1 text-muted"></i>Tipe
                                </th>
                                <th class="border-top-0">
                                    <i class="fas fa-code-branch mr-1 text-muted"></i>Versi
                                </th>
                                <th class="border-top-0">
                                    <i class="fas fa-arrow-up mr-1 text-primary"></i>Saldo Awal
                                </th>
                                <th class="border-top-0">
                                    <i class="fas fa-arrow-down mr-1 text-success"></i>Saldo Akhir
                                </th>
                                <th class="border-top-0">
                                    <i class="far fa-clock mr-1 text-muted"></i>Generated At
                                </th>
                                <th class="border-top-0">
                                    <i class="far fa-user mr-1 text-muted"></i>Generated By
                                </th>
                                <th class="border-top-0 text-center">
                                    <i class="fas fa-shield-alt mr-1 text-muted"></i>Status
                                </th>
                                <th class="border-top-0 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($reports as $report)
                                <tr>
                                    <td>
                                        <span class="badge badge-info p-2">
                                            <i class="fas fa-file-invoice mr-1"></i>
                                            {{ $report->report_type }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge badge-secondary p-2">
                                            <i class="fas fa-tag mr-1"></i>
                                            v{{ $report->version_no }}
                                        </span>
                                    </td>
                                    <td class="font-weight-bold text-primary">
                                        <span class="currency-value">
                                            Rp {{ number_format((float) data_get($report->summary, 'opening_balance', 0), 2, ',', '.') }}
                                        </span>
                                    </td>
                                    <td class="font-weight-bold text-success">
                                        <span class="currency-value">
                                            Rp {{ number_format((float) data_get($report->summary, 'ending_balance', data_get($report->summary, 'net_result', 0)), 2, ',', '.') }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge badge-light" data-toggle="tooltip" title="{{ optional($report->generated_at)->format('l, d F Y H:i:s') }}">
                                            <i class="far fa-calendar-alt text-primary mr-1"></i>
                                            {{ optional($report->generated_at)->format('Y-m-d') ?? '-' }}
                                            <br>
                                            <small class="text-muted">
                                                <i class="far fa-clock mr-1"></i>
                                                {{ optional($report->generated_at)->format('H:i:s') ?? '-' }}
                                            </small>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="user-info">
                                            <i class="far fa-user-circle text-primary mr-1"></i>
                                            <span class="font-weight-bold">{{ $report->user?->name ?? '-' }}</span>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        @if($report->is_read_only)
                                            <span class="badge badge-success p-2">
                                                <i class="fas fa-check-circle mr-1"></i>Read Only
                                            </span>
                                        @else
                                            <span class="badge badge-warning p-2">
                                                <i class="fas fa-pen mr-1"></i>Editable
                                            </span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group" role="group">
                                            <button type="button" class="btn btn-sm btn-outline-primary" data-toggle="tooltip" title="Lihat Detail">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            @if(!$report->is_read_only)
                                                <button type="button" class="btn btn-sm btn-outline-warning" data-toggle="tooltip" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-5">
                                        <div class="empty-state">
                                            <i class="fas fa-inbox fa-4x text-muted mb-3"></i>
                                            <h5 class="text-muted">Belum ada snapshot laporan finance</h5>
                                            <p class="text-muted mb-3">Mulai dengan membuat snapshot laporan baru</p>
                                            <a href="{{ route('finance.depreciation.index') }}" class="btn btn-primary">
                                                <i class="fas fa-plus-circle mr-2"></i>Buat Snapshot Baru
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer bg-white clearfix border-top">
                <div class="d-flex justify-content-between align-items-center flex-wrap">
                    <div class="text-muted mb-2 mb-md-0">
                        <i class="fas fa-info-circle mr-1"></i>
                        Menampilkan {{ $reports->firstItem() ?? 0 }} - {{ $reports->lastItem() ?? 0 }} 
                        dari <span class="font-weight-bold">{{ $reports->total() }}</span> data
                    </div>
                    <div class="pagination-wrapper">
                        {{ $reports->appends(request()->query())->links('pagination::bootstrap-4') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('css')
<!-- Animate.css -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">

<style>
    /* Modern Dashboard Styling */
    :root {
        --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        --success-gradient: linear-gradient(135deg, #84fab0 0%, #8fd3f4 100%);
        --warning-gradient: linear-gradient(135deg, #f6d365 0%, #fda085 100%);
        --danger-gradient: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        --info-gradient: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        --secondary-gradient: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%);
    }

    .bg-gradient-primary {
        background: var(--primary-gradient);
    }
    
    .bg-gradient-success {
        background: var(--success-gradient);
        color: #333;
    }
    
    .bg-gradient-warning {
        background: var(--warning-gradient);
        color: #333;
    }
    
    .bg-gradient-danger {
        background: var(--danger-gradient);
        color: #333;
    }
    
    .bg-gradient-info {
        background: var(--info-gradient);
    }
    
    .bg-gradient-secondary {
        background: var(--secondary-gradient);
        color: #333;
    }
    
    .card {
        border: none;
        border-radius: 15px;
        box-shadow: 0 2px 6px rgba(0,0,0,0.1);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        overflow: hidden;
    }
    
    .card:hover {
        box-shadow: 0 8px 20px rgba(0,0,0,0.15);
        transform: translateY(-2px);
    }
    
    .card-header {
        border-bottom: none;
        padding: 1.25rem;
    }
    
    .small-box {
        border-radius: 15px;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        overflow: hidden;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    }
    
    .small-box:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.2);
    }
    
    .small-box .inner {
        padding: 20px;
        position: relative;
        z-index: 1;
    }
    
    .small-box .icon {
        position: absolute;
        top: 15px;
        right: 15px;
        z-index: 0;
        opacity: 0.3;
        transition: all 0.3s ease;
    }
    
    .small-box:hover .icon {
        transform: scale(1.2) rotate(5deg);
        opacity: 0.4;
    }
    
    .small-box-footer {
        display: block;
        padding: 10px 20px;
        background: rgba(0,0,0,0.1);
        color: white;
        text-decoration: none;
        transition: all 0.3s ease;
    }
    
    .small-box-footer:hover {
        background: rgba(0,0,0,0.2);
        color: white;
        text-decoration: none;
    }
    
    .table th {
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.8rem;
        letter-spacing: 0.5px;
        border-bottom-width: 1px;
        padding: 1rem;
    }
    
    .table td {
        padding: 1rem;
        vertical-align: middle;
    }
    
    .badge {
        font-weight: 500;
        border-radius: 8px;
        padding: 0.5rem 0.75rem;
        font-size: 0.75rem;
        transition: all 0.3s ease;
    }
    
    .badge:hover {
        transform: translateY(-2px);
        box-shadow: 0 3px 10px rgba(0,0,0,0.1);
    }
    
    .btn {
        border-radius: 10px;
        transition: all 0.3s ease;
        font-weight: 500;
    }
    
    .btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(0,0,0,0.2);
    }
    
    .btn-group .btn {
        border-radius: 8px !important;
        margin: 0 2px;
    }
    
    .form-control {
        border-radius: 10px;
        border: 1px solid #e0e0e0;
        transition: all 0.3s ease;
        height: calc(2.5rem + 2px);
    }
    
    .form-control:focus {
        box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        border-color: #667eea;
    }
    
    .input-group-text {
        border-radius: 10px 0 0 10px;
        border: 1px solid #e0e0e0;
    }
    
    /* Pagination Styling */
    .pagination {
        margin-bottom: 0;
    }
    
    .pagination .page-link {
        border: none;
        margin: 0 3px;
        border-radius: 10px;
        color: #667eea;
        padding: 0.5rem 0.75rem;
        transition: all 0.3s ease;
    }
    
    .pagination .page-item.active .page-link {
        background: var(--primary-gradient);
        border: none;
        color: white;
        box-shadow: 0 3px 10px rgba(102, 126, 234, 0.4);
    }
    
    .pagination .page-link:hover {
        background: var(--primary-gradient);
        color: white;
        transform: translateY(-2px);
        box-shadow: 0 3px 10px rgba(102, 126, 234, 0.4);
    }
    
    /* Empty State */
    .empty-state {
        padding: 40px 20px;
        background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
        border-radius: 15px;
    }
    
    /* Currency Animation */
    .currency-value {
        display: inline-block;
        transition: all 0.3s ease;
    }
    
    .currency-value:hover {
        transform: scale(1.05);
        color: #333;
    }
    
    /* Counter Animation */
    .counter {
        animation: countUp 2s ease-out;
    }
    
    @keyframes countUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    /* Tooltip Customization */
    .tooltip .tooltip-inner {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 10px;
        padding: 8px 12px;
        box-shadow: 0 3px 15px rgba(0,0,0,0.2);
    }
    
    .tooltip.bs-tooltip-top .arrow::before {
        border-top-color: #764ba2;
    }
    
    /* Responsive Adjustments */
    @media (max-width: 768px) {
        .card-header {
            flex-direction: column;
            text-align: center;
        }
        
        .btn-group {
            margin-top: 10px;
        }
        
        .table td {
            white-space: nowrap;
        }
        
        .small-box .inner {
            padding: 15px;
        }
        
        .small-box h3 {
            font-size: 1.5rem;
        }
    }
    
    /* Loading Animation */
    .fa-spinner {
        animation: spin 1s linear infinite;
    }
    
    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
</style>
@endpush

@push('js')
<script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/waypoints@4.0.1/lib/jquery.waypoints.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/counterup2@2.0.2/dist/index.min.js"></script>

<script>
    $(document).ready(function() {
        // Initialize tooltips
        $('[data-toggle="tooltip"]').tooltip();
        
        // Counter animation for numbers
        const counterUp = window.counterUp.default;
        const counters = document.querySelectorAll('.counter');
        
        counters.forEach(el => {
            new Waypoint({
                element: el,
                handler: function() {
                    counterUp(el, {
                        duration: 2000,
                        delay: 16,
                    });
                    this.destroy();
                },
                offset: 'bottom-in-view',
            });
        });
        
        // Loading effect on form submit
        document.querySelector('form').addEventListener('submit', function() {
            const submitBtn = this.querySelector('button[type="submit"]');
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Loading...';
            submitBtn.disabled = true;
        });
        
        // Smooth scroll to table
        $('a[href*="#"]').on('click', function(e) {
            if (this.hash !== '') {
                e.preventDefault();
                const hash = this.hash;
                $('html, body').animate({
                    scrollTop: $(hash).offset().top
                }, 800);
            }
        });
        
        // Add hover effect to table rows
        $('#snapshotTable tbody tr').hover(
            function() {
                $(this).addClass('shadow-sm');
            },
            function() {
                $(this).removeClass('shadow-sm');
            }
        );
        
        // Auto close alerts
        setTimeout(function() {
            $('.alert').fadeOut('slow');
        }, 5000);
        
        // Refresh button effect
        $('.btn-refresh').click(function() {
            $(this).find('i').addClass('fa-spin');
            setTimeout(() => {
                $(this).find('i').removeClass('fa-spin');
            }, 1000);
        });
    });
</script>
@endpush
