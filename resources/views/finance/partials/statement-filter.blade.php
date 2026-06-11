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
    $statementDataSource = $statementFilters['statement_data_source'] ?? null;
    $statementBatchId = $statementFilters['statement_batch_id'] ?? null;
    $ledgerSource = $statementFilters['ledger_source'] ?? null;
    $ledgerBatchId = $statementFilters['ledger_batch_id'] ?? null;
    $categoryId = $statementFilters['category_id'] ?? null;
    $financeCategoryOptions = $financeCategoryOptions ?? collect();
    $perPage = (int) ($statementFilters['per_page'] ?? 10);
    $action = $action ?? url()->current();
    $perPageOptions = $perPageOptions ?? [10, 20, 50, 100];
    $showPerPage = $showPerPage ?? false;
    $isJournalDetail = request()->routeIs('finance.report.journal-items');
    $resetQuery = array_filter([
        'account_code' => $accountCode,
        'statement_source' => $statementSource,
        'statement_data_source' => $statementDataSource,
        'statement_batch_id' => $statementBatchId,
        'ledger_source' => $ledgerSource,
        'ledger_batch_id' => $ledgerBatchId,
        'per_page' => $showPerPage ? $perPage : null,
    ], static fn ($value): bool => $value !== null && $value !== '');
    $monthOptions = collect(range(1, 12))
        ->mapWithKeys(fn (int $monthNumber): array => [$monthNumber => __('app.finance.months.' . $monthNumber)])
        ->all();
@endphp

<style>
    .fs-filter-form {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }
    .fs-filter-grid {
        display: grid;
        grid-template-columns: repeat(12, minmax(0, 1fr));
        gap: 0.9rem 1rem;
        align-items: end;
    }
    .fs-field[data-span="2"] { grid-column: span 2; }
    .fs-field[data-span="3"] { grid-column: span 3; }
    .fs-field[data-span="4"] { grid-column: span 4; }
    .fs-field[data-span="6"] { grid-column: span 6; }
    .fs-field[data-span="12"] { grid-column: 1 / -1; }
    .fs-field-inline {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        flex-wrap: wrap;
    }
    .fs-helper-text {
        margin-top: 0.45rem;
        color: var(--fs-muted, #64748b);
        font-size: 0.74rem;
        line-height: 1.45;
        font-weight: 500;
    }
    .fs-mode-note {
        display: flex;
        align-items: flex-start;
        gap: 0.65rem;
        padding: 0.85rem 1rem;
        border-radius: 14px;
        border: 1px dashed rgba(37, 99, 235, 0.18);
        background: rgba(37, 99, 235, 0.05);
        color: var(--fs-muted, #64748b);
        font-size: 0.78rem;
        font-weight: 500;
    }
    .fs-mode-note i {
        color: var(--fs-blue, #1d4ed8);
        margin-top: 0.15rem;
    }
    .fs-actions {
        justify-content: flex-start;
        padding-top: 0.1rem;
    }
    .fs-btn-muted.is-link-reset {
        text-decoration: none;
    }
    @media (max-width: 1199px) {
        .fs-field[data-span="2"],
        .fs-field[data-span="3"],
        .fs-field[data-span="4"] {
            grid-column: span 4;
        }
    }
    @media (max-width: 991px) {
        .fs-field[data-span="2"],
        .fs-field[data-span="3"],
        .fs-field[data-span="4"],
        .fs-field[data-span="6"] {
            grid-column: span 6;
        }
    }
    @media (max-width: 767px) {
        .fs-filter-grid {
            grid-template-columns: 1fr;
        }
        .fs-field[data-span="2"],
        .fs-field[data-span="3"],
        .fs-field[data-span="4"],
        .fs-field[data-span="6"],
        .fs-field[data-span="12"] {
            grid-column: auto;
        }
    }
    body.dark-mode .fs-mode-note {
        background: rgba(96, 165, 250, 0.08);
        border-color: rgba(96, 165, 250, 0.18);
        color: var(--app-text-muted, #94a3b8);
    }
    body.dark-mode .fs-mode-note i {
        color: var(--app-accent, #60a5fa);
    }
</style>

<div class="fs-filter-card">
    <div class="fs-filter-head">
        <div class="fs-filter-title">
            <span class="fs-filter-icon"><i class="fas fa-filter"></i></span>
            <span>{{ __('app.finance.report_period_filter') }}</span>
        </div>
    </div>
    <div class="fs-filter-body">
        <form method="GET" action="{{ $action }}" class="fs-filter-form">
            @if(!empty($accountCode))
                <input type="hidden" name="account_code" value="{{ $accountCode }}">
            @endif
            @if(!empty($search))
                <input type="hidden" name="search" value="{{ $search }}">
            @endif
            @if(!empty($statementSource))
                <input type="hidden" name="statement_source" value="{{ $statementSource }}">
            @endif
            @if(!empty($statementDataSource))
                <input type="hidden" name="statement_data_source" value="{{ $statementDataSource }}">
            @endif
            @if(!empty($statementBatchId))
                <input type="hidden" name="statement_batch_id" value="{{ $statementBatchId }}">
            @endif
            @if(!empty($ledgerSource))
                <input type="hidden" name="ledger_source" value="{{ $ledgerSource }}">
            @endif
            @if(!empty($ledgerBatchId))
                <input type="hidden" name="ledger_batch_id" value="{{ $ledgerBatchId }}">
            @endif
            <div class="fs-filter-grid">
                <div class="fs-field" id="statement_period_type_group" data-span="{{ $showPerPage ? '4' : '6' }}">
                    <label class="fs-label" for="statement_period_type">
                        <i class="fas fa-layer-group"></i> {{ __('app.finance.period') }}
                    </label>
                    <select name="period_type" id="statement_period_type" class="fs-control">
                        <option value="ALL" {{ $periodType === 'ALL' ? 'selected' : '' }}>{{ __('app.finance.all') }}</option>
                        <option value="DAILY" {{ $periodType === 'DAILY' ? 'selected' : '' }}>{{ __('app.finance.daily_date') }}</option>
                        <option value="MONTHLY" {{ $periodType === 'MONTHLY' ? 'selected' : '' }}>{{ __('app.finance.monthly') }}</option>
                        <option value="YEARLY" {{ $periodType === 'YEARLY' ? 'selected' : '' }}>{{ __('app.finance.yearly') }}</option>
                    </select>
                    <div class="fs-helper-text">
                        {!! __('app.finance.daily_filter_help', ['period' => '<strong>' . e(__('app.finance.daily_date')) . '</strong>']) !!}
                    </div>
                </div>

                @if($showPerPage)
                    <div class="fs-field" data-span="2">
                        <label class="fs-label" for="statement_per_page">
                            <i class="fas fa-list-ol"></i> {{ __('app.finance.per_page') }}
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

                <div class="fs-field" data-span="3">
                    <label class="fs-label" for="statement_category_id">
                        <i class="fas fa-tags"></i> Kategori Finance
                    </label>
                    <select name="category_id" id="statement_category_id" class="fs-control">
                        <option value="">Semua kategori</option>
                        @foreach($financeCategoryOptions as $category)
                            <option value="{{ $category->id }}" {{ (string) $categoryId === (string) $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                @if($isJournalDetail)
                    <div class="fs-field" data-span="{{ $showPerPage ? '6' : '6' }}">
                        <div class="fs-mode-note">
                            <i class="fas fa-calendar-day"></i>
                            <div>
                                {!! __('app.finance.journal_detail_date_note', ['period' => '<strong>' . e(__('app.finance.daily_date')) . '</strong>']) !!}
                            </div>
                        </div>
                    </div>
                @endif

                <div class="fs-field" id="statement_start_date_group" data-span="3">
                    <label class="fs-label" for="statement_start_date">
                        <i class="fas fa-calendar-day"></i> {{ __('app.finance.from_date') }}
                    </label>
                    <input
                        type="date"
                        name="start_date"
                        id="statement_start_date"
                        class="fs-control"
                        value="{{ $startDate }}"
                    >
                </div>

                <div class="fs-field" id="statement_end_date_group" data-span="3">
                    <label class="fs-label" for="statement_end_date">
                        <i class="fas fa-calendar-check"></i> {{ __('app.finance.until_date') }}
                    </label>
                    <input
                        type="date"
                        name="end_date"
                        id="statement_end_date"
                        class="fs-control"
                        value="{{ $endDate }}"
                    >
                </div>

                <div class="fs-field" id="statement_start_month_group" data-span="3">
                    <label class="fs-label" for="statement_start_month">
                        <i class="fas fa-calendar-week"></i> {{ __('app.finance.from_month') }}
                    </label>
                    <select name="start_month" id="statement_start_month" class="fs-control">
                        @foreach($monthOptions as $monthNumber => $monthLabel)
                            <option value="{{ $monthNumber }}" {{ $startMonth === $monthNumber ? 'selected' : '' }}>
                                {{ str_pad((string) $monthNumber, 2, '0', STR_PAD_LEFT) }} - {{ $monthLabel }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="fs-field" id="statement_start_year_group" data-span="2">
                    <label class="fs-label" for="statement_start_year">
                        <i class="fas fa-calendar-alt"></i> {{ __('app.finance.from_year') }}
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

                <div class="fs-field" id="statement_end_month_group" data-span="3">
                    <label class="fs-label" for="statement_end_month">
                        <i class="fas fa-calendar-week"></i> {{ __('app.finance.until_month') }}
                    </label>
                    <select name="end_month" id="statement_end_month" class="fs-control">
                        @foreach($monthOptions as $monthNumber => $monthLabel)
                            <option value="{{ $monthNumber }}" {{ $endMonth === $monthNumber ? 'selected' : '' }}>
                                {{ str_pad((string) $monthNumber, 2, '0', STR_PAD_LEFT) }} - {{ $monthLabel }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="fs-field" id="statement_end_year_group" data-span="2">
                    <label class="fs-label" for="statement_end_year">
                        <i class="fas fa-calendar-alt"></i> {{ __('app.finance.until_year') }}
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

                <div class="fs-field" id="statement_all_note_group" data-span="12" style="display: none;">
                    <div class="fs-mode-note">
                        <i class="fas fa-infinity"></i>
                        <div>
                            {!! __('app.finance.all_period_note', ['period' => '<strong>' . e(__('app.finance.all')) . '</strong>']) !!}
                        </div>
                    </div>
                </div>

                <div class="fs-field fs-actions" data-span="12">
                    <button type="submit" class="fs-btn fs-btn-primary">
                        <i class="fas fa-search"></i>
                        <span>{{ __('app.finance.filter') }}</span>
                    </button>
                    <a href="{{ $action }}{{ !empty($resetQuery) ? '?' . http_build_query($resetQuery) : '' }}" class="fs-btn fs-btn-muted is-link-reset">
                        <i class="fas fa-redo"></i>
                        <span>{{ __('app.finance.reset') }}</span>
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
                    allNote: document.getElementById('statement_all_note_group'),
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
                    setGroupState(fieldGroups.allNote, periodType === 'ALL');
                }

                periodTypeInput.addEventListener('change', syncStatementPeriodFields);
                syncStatementPeriodFields();
            })();
        </script>
    @endpush
@endonce
