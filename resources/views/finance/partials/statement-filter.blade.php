@php
    $statementFilters = $filters ?? [];
    $periodType = strtoupper((string) ($statementFilters['period_type'] ?? 'MONTHLY'));
    $reportDate = $statementFilters['report_date'] ?? null;
    $month = $statementFilters['month'] ?? null;
    $year = $statementFilters['year'] ?? null;
    $startDate = $statementFilters['start_date'] ?? $reportDate;
    $endDate = $statementFilters['end_date'] ?? $reportDate;
    $startMonth = (int) ($statementFilters['start_month'] ?? $month ?? now()->month);
    $endMonth = (int) ($statementFilters['end_month'] ?? $month ?? now()->month);
    $startYear = (int) ($statementFilters['start_year'] ?? $year ?? now()->year);
    $endYear = (int) ($statementFilters['end_year'] ?? $year ?? now()->year);
    $accountCode = $statementFilters['account_code'] ?? null;
    $search = $statementFilters['search'] ?? null;
    $statementSource = $statementFilters['statement_source'] ?? null;
    $perPage = (int) ($statementFilters['per_page'] ?? 10);
    $action = $action ?? url()->current();
    $perPageOptions = $perPageOptions ?? [10, 20, 50, 100];
    $showPerPage = $showPerPage ?? false;
    $monthOptions = [
        1 => 'Januari',
        2 => 'Februari',
        3 => 'Maret',
        4 => 'April',
        5 => 'Mei',
        6 => 'Juni',
        7 => 'Juli',
        8 => 'Agustus',
        9 => 'September',
        10 => 'Oktober',
        11 => 'November',
        12 => 'Desember',
    ];
@endphp

<div class="fs-filter-card">
    <div class="fs-filter-head">
        <div class="fs-filter-title">
            <span class="fs-filter-icon"><i class="fas fa-filter"></i></span>
            <span>Filter Periode Laporan</span>
        </div>
    </div>
    <div class="fs-filter-body">
        <form method="GET" action="{{ $action }}">
            @if(!empty($accountCode))
                <input type="hidden" name="account_code" value="{{ $accountCode }}">
            @endif
            @if(!empty($search))
                <input type="hidden" name="search" value="{{ $search }}">
            @endif
            @if(!empty($statementSource))
                <input type="hidden" name="statement_source" value="{{ $statementSource }}">
            @endif
            <div class="row">
                <div class="col-md-2 fs-field" id="statement_period_type_group">
                    <label class="fs-label" for="statement_period_type">
                        <i class="fas fa-layer-group"></i> Periode
                    </label>
                    <select name="period_type" id="statement_period_type" class="fs-control">
                        <option value="ALL" {{ $periodType === 'ALL' ? 'selected' : '' }}>Semua</option>
                        <option value="DAILY" {{ $periodType === 'DAILY' ? 'selected' : '' }}>Harian</option>
                        <option value="MONTHLY" {{ $periodType === 'MONTHLY' ? 'selected' : '' }}>Bulanan</option>
                        <option value="YEARLY" {{ $periodType === 'YEARLY' ? 'selected' : '' }}>Tahunan</option>
                    </select>
                </div>

                <div class="col-md-3 fs-field" id="statement_start_date_group">
                    <label class="fs-label" for="statement_start_date">
                        <i class="fas fa-calendar-day"></i> Dari Tanggal
                    </label>
                    <input
                        type="date"
                        name="start_date"
                        id="statement_start_date"
                        class="fs-control"
                        value="{{ $startDate }}"
                    >
                </div>

                <div class="col-md-3 fs-field" id="statement_end_date_group">
                    <label class="fs-label" for="statement_end_date">
                        <i class="fas fa-calendar-check"></i> Sampai Tanggal
                    </label>
                    <input
                        type="date"
                        name="end_date"
                        id="statement_end_date"
                        class="fs-control"
                        value="{{ $endDate }}"
                    >
                </div>

                <div class="col-md-2 fs-field" id="statement_start_month_group">
                    <label class="fs-label" for="statement_start_month">
                        <i class="fas fa-calendar-week"></i> Dari Bulan
                    </label>
                    <select name="start_month" id="statement_start_month" class="fs-control">
                        @foreach($monthOptions as $monthNumber => $monthLabel)
                            <option value="{{ $monthNumber }}" {{ $startMonth === $monthNumber ? 'selected' : '' }}>
                                {{ str_pad((string) $monthNumber, 2, '0', STR_PAD_LEFT) }} - {{ $monthLabel }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2 fs-field" id="statement_start_year_group">
                    <label class="fs-label" for="statement_start_year">
                        <i class="fas fa-calendar-alt"></i> Dari Tahun
                    </label>
                    <input
                        type="number"
                        name="start_year"
                        id="statement_start_year"
                        class="fs-control"
                        min="1900"
                        max="2100"
                        value="{{ $startYear }}"
                    >
                </div>

                <div class="col-md-2 fs-field" id="statement_end_month_group">
                    <label class="fs-label" for="statement_end_month">
                        <i class="fas fa-calendar-week"></i> Sampai Bulan
                    </label>
                    <select name="end_month" id="statement_end_month" class="fs-control">
                        @foreach($monthOptions as $monthNumber => $monthLabel)
                            <option value="{{ $monthNumber }}" {{ $endMonth === $monthNumber ? 'selected' : '' }}>
                                {{ str_pad((string) $monthNumber, 2, '0', STR_PAD_LEFT) }} - {{ $monthLabel }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2 fs-field" id="statement_end_year_group">
                    <label class="fs-label" for="statement_end_year">
                        <i class="fas fa-calendar-alt"></i> Sampai Tahun
                    </label>
                    <input
                        type="number"
                        name="end_year"
                        id="statement_end_year"
                        class="fs-control"
                        min="1900"
                        max="2100"
                        value="{{ $endYear }}"
                    >
                </div>

                @if($showPerPage)
                    <div class="col-md-2 fs-field">
                        <label class="fs-label" for="statement_per_page">
                            <i class="fas fa-list-ol"></i> Per Halaman
                        </label>
                        <select name="per_page" id="statement_per_page" class="fs-control">
                            @foreach($perPageOptions as $size)
                                <option value="{{ $size }}" {{ $perPage === (int) $size ? 'selected' : '' }}>
                                    {{ $size }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endif

                <div class="{{ $showPerPage ? 'col-md-3' : 'col-md-4' }} fs-actions">
                    <button type="submit" class="fs-btn fs-btn-primary">
                        <i class="fas fa-search"></i>
                        <span>Filter</span>
                    </button>
                    <a href="{{ $action }}" class="fs-btn fs-btn-muted">
                        <i class="fas fa-redo"></i>
                        <span>Reset</span>
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

@once
    @push('component_js')
        <script>
            (function () {
                const periodTypeInput = document.getElementById('statement_period_type');
                const fieldGroups = {
                    startDate: document.getElementById('statement_start_date_group'),
                    endDate: document.getElementById('statement_end_date_group'),
                    startMonth: document.getElementById('statement_start_month_group'),
                    startYear: document.getElementById('statement_start_year_group'),
                    endMonth: document.getElementById('statement_end_month_group'),
                    endYear: document.getElementById('statement_end_year_group'),
                };

                if (!periodTypeInput) {
                    return;
                }

                function setGroupState(group, isVisible) {
                    if (!group) {
                        return;
                    }

                    group.style.display = isVisible ? '' : 'none';
                    group.querySelectorAll('input, select').forEach(function (input) {
                        input.disabled = !isVisible;
                    });
                }

                function syncStatementPeriodFields() {
                    const periodType = periodTypeInput.value;
                    const isDaily = periodType === 'DAILY';
                    const isMonthly = periodType === 'MONTHLY';
                    const isYearly = periodType === 'YEARLY';

                    setGroupState(fieldGroups.startDate, isDaily);
                    setGroupState(fieldGroups.endDate, isDaily);
                    setGroupState(fieldGroups.startMonth, isMonthly);
                    setGroupState(fieldGroups.endMonth, isMonthly);
                    setGroupState(fieldGroups.startYear, isMonthly || isYearly);
                    setGroupState(fieldGroups.endYear, isMonthly || isYearly);
                }

                periodTypeInput.addEventListener('change', syncStatementPeriodFields);
                syncStatementPeriodFields();
            })();
        </script>
    @endpush
@endonce
