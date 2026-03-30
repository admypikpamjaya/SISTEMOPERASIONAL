@php
    $statementFilters = $filters ?? [];
    $periodType = strtoupper((string) ($statementFilters['period_type'] ?? 'MONTHLY'));
    $reportDate = $statementFilters['report_date'] ?? null;
    $month = $statementFilters['month'] ?? null;
    $year = $statementFilters['year'] ?? null;
    $perPage = (int) ($statementFilters['per_page'] ?? 10);
    $action = $action ?? url()->current();
    $perPageOptions = $perPageOptions ?? [10, 20, 50, 100];
    $showPerPage = $showPerPage ?? false;
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

                <div class="col-md-3 fs-field" id="statement_report_date_group">
                    <label class="fs-label" for="statement_report_date">
                        <i class="fas fa-calendar-day"></i> Tanggal
                    </label>
                    <input
                        type="date"
                        name="report_date"
                        id="statement_report_date"
                        class="fs-control"
                        value="{{ $reportDate }}"
                    >
                </div>

                <div class="col-md-2 fs-field" id="statement_month_group">
                    <label class="fs-label" for="statement_month">
                        <i class="fas fa-calendar-week"></i> Bulan
                    </label>
                    <select name="month" id="statement_month" class="fs-control">
                        @for($m = 1; $m <= 12; $m++)
                            <option value="{{ $m }}" {{ (int) $month === $m ? 'selected' : '' }}>
                                {{ str_pad((string) $m, 2, '0', STR_PAD_LEFT) }}
                            </option>
                        @endfor
                    </select>
                </div>

                <div class="col-md-2 fs-field" id="statement_year_group">
                    <label class="fs-label" for="statement_year">
                        <i class="fas fa-calendar-alt"></i> Tahun
                    </label>
                    <input
                        type="number"
                        name="year"
                        id="statement_year"
                        class="fs-control"
                        min="1900"
                        max="2100"
                        value="{{ $year }}"
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

                <div class="{{ $showPerPage ? 'col-md-1' : 'col-md-3' }} fs-actions">
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
                const reportDateGroup = document.getElementById('statement_report_date_group');
                const reportDateInput = document.getElementById('statement_report_date');
                const monthGroup = document.getElementById('statement_month_group');
                const monthInput = document.getElementById('statement_month');
                const yearGroup = document.getElementById('statement_year_group');
                const yearInput = document.getElementById('statement_year');

                if (!periodTypeInput || !reportDateGroup || !monthGroup || !yearGroup) {
                    return;
                }

                function syncStatementPeriodFields() {
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
                    }

                    if (monthInput) {
                        monthInput.disabled = !isMonthly;
                    }

                    if (yearInput) {
                        yearInput.disabled = !(isMonthly || isYearly);
                    }

                    if (isAll) {
                        if (reportDateInput) {
                            reportDateInput.value = '';
                        }
                        if (yearInput) {
                            yearInput.value = '';
                        }
                    }
                }

                periodTypeInput.addEventListener('change', syncStatementPeriodFields);
                syncStatementPeriodFields();
            })();
        </script>
    @endpush
@endonce
