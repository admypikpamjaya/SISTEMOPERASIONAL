@php
    use App\Enums\Report\Maintenance\AssetMaintenanceReportStatus;

    $pageReports = collect($reports->items());
    $statusPageCounts = collect(AssetMaintenanceReportStatus::cases())->mapWithKeys(function ($status) use ($pageReports) {
        return [
            $status->value => $pageReports->filter(
                fn ($report) => (string) data_get($report, 'status.value', data_get($report, 'status')) === $status->value
            )->count(),
        ];
    });
    $activeFilterCount = collect([
        request('date_from'),
        request('date_to'),
        request('status'),
        request('keyword'),
    ])->filter(fn ($value) => filled($value))->count();
    $dateRangeLabel = request('date_from') || request('date_to')
        ? (request('date_from') ?: '-') . ' - ' . (request('date_to') ?: '-')
        : __('app.maintenance.all_dates');
    $statusFilterLabel = request('status')
        ? __('app.maintenance.statuses.' . strtolower((string) request('status')))
        : __('app.maintenance.all_statuses');
@endphp

<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap">

<style>
    body,
    body .content-wrapper {
        background: var(--app-bg) !important;
    }

    .maintenance-report-shell {
        color: var(--app-text);
        font-family: 'Plus Jakarta Sans', sans-serif;
    }

    .maintenance-report-head {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 1rem;
        margin-bottom: 1rem;
    }

    .maintenance-report-title-group {
        display: flex;
        align-items: flex-start;
        gap: .9rem;
        min-width: 0;
    }

    .maintenance-report-icon {
        width: 48px;
        height: 48px;
        border-radius: 14px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        background: linear-gradient(135deg, #2563eb, #0f766e);
        box-shadow: 0 16px 30px rgba(37, 99, 235, .18);
        flex: 0 0 auto;
    }

    .maintenance-report-title {
        margin: 0;
        color: var(--app-text);
        font-size: 1.45rem;
        line-height: 1.2;
        font-weight: 800;
    }

    .maintenance-report-subtitle {
        margin: .3rem 0 0;
        max-width: 720px;
        color: var(--app-text-muted);
        font-size: .84rem;
        line-height: 1.7;
        font-weight: 500;
    }

    .maintenance-report-actions {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: .55rem;
        flex-wrap: wrap;
    }

    .maintenance-action-btn {
        min-height: 40px;
        border: 1px solid var(--app-border);
        border-radius: 12px;
        padding: .58rem .85rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: .45rem;
        background: var(--app-surface);
        color: var(--app-text-soft);
        font-size: .8rem;
        font-weight: 800;
        line-height: 1;
        text-decoration: none;
        transition: transform .18s ease, border-color .18s ease, background .18s ease, color .18s ease;
        cursor: pointer;
        white-space: nowrap;
    }

    .maintenance-action-btn:hover,
    .maintenance-action-btn:focus {
        color: var(--app-text);
        border-color: rgba(37, 99, 235, .28);
        text-decoration: none;
        transform: translateY(-1px);
    }

    .maintenance-action-btn.is-primary {
        border-color: transparent;
        color: #fff;
        background: linear-gradient(135deg, #2563eb, #3b82f6);
        box-shadow: 0 12px 24px rgba(37, 99, 235, .22);
    }

    .maintenance-action-btn.is-excel {
        border-color: rgba(5, 150, 105, .22);
        color: #047857;
        background: rgba(5, 150, 105, .1);
    }

    .maintenance-action-btn.is-pdf {
        border-color: rgba(220, 38, 38, .22);
        color: #dc2626;
        background: rgba(220, 38, 38, .1);
    }

    .maintenance-summary-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: .8rem;
        margin-bottom: 1rem;
    }

    .maintenance-summary-card {
        position: relative;
        overflow: hidden;
        min-height: 104px;
        border: 1px solid var(--app-border);
        border-radius: 16px;
        padding: .95rem 1rem;
        background: var(--app-surface);
        box-shadow: var(--app-shadow);
    }

    .maintenance-summary-card.is-main {
        color: #fff;
        border-color: transparent;
        background: linear-gradient(135deg, #0f172a, #1d4ed8 58%, #0f766e);
    }

    .maintenance-summary-label {
        display: inline-flex;
        align-items: center;
        gap: .38rem;
        color: var(--app-text-muted);
        font-size: .68rem;
        font-weight: 800;
        letter-spacing: .05em;
        text-transform: uppercase;
    }

    .maintenance-summary-card.is-main .maintenance-summary-label {
        color: rgba(255, 255, 255, .72);
    }

    .maintenance-summary-value {
        display: block;
        margin-top: .35rem;
        color: var(--app-text);
        font-size: 1.55rem;
        line-height: 1.15;
        font-weight: 800;
    }

    .maintenance-summary-card.is-main .maintenance-summary-value {
        color: #fff;
    }

    .maintenance-summary-note {
        display: block;
        margin-top: .35rem;
        color: var(--app-text-muted);
        font-size: .76rem;
        line-height: 1.45;
        font-weight: 600;
    }

    .maintenance-summary-card.is-main .maintenance-summary-note {
        color: rgba(255, 255, 255, .78);
    }

    .maintenance-panel {
        border: 1px solid var(--app-border);
        border-radius: 18px;
        background: var(--app-surface);
        box-shadow: var(--app-shadow);
        overflow: hidden;
        margin-bottom: 1rem;
    }

    .maintenance-panel-head {
        padding: 1rem 1.1rem;
        border-bottom: 1px solid var(--app-border);
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: .8rem;
        flex-wrap: wrap;
        background: var(--app-surface-soft);
    }

    .maintenance-panel-title {
        margin: 0;
        display: inline-flex;
        align-items: center;
        gap: .55rem;
        color: var(--app-text);
        font-size: .96rem;
        line-height: 1.35;
        font-weight: 800;
    }

    .maintenance-panel-title i {
        color: var(--app-accent);
    }

    .maintenance-panel-note {
        color: var(--app-text-muted);
        font-size: .76rem;
        line-height: 1.5;
        font-weight: 700;
    }

    .maintenance-filter-body {
        padding: 1rem 1.1rem 1.1rem;
    }

    .maintenance-filter-grid {
        display: grid;
        grid-template-columns: repeat(12, minmax(0, 1fr));
        gap: .85rem;
        align-items: end;
    }

    .maintenance-field {
        grid-column: span 2;
        min-width: 0;
    }

    .maintenance-field.is-search {
        grid-column: span 3;
    }

    .maintenance-field.is-actions {
        grid-column: span 3;
    }

    .maintenance-field label {
        display: flex;
        align-items: center;
        gap: .35rem;
        margin: 0 0 .4rem;
        color: var(--app-text-muted);
        font-size: .68rem;
        font-weight: 800;
        letter-spacing: .05em;
        text-transform: uppercase;
    }

    .maintenance-control {
        width: 100%;
        min-height: 42px;
        border: 1px solid var(--app-border);
        border-radius: 12px;
        padding: .58rem .78rem;
        color: var(--app-text);
        background: var(--app-surface);
        font-size: .84rem;
        font-weight: 700;
        outline: none;
        transition: border-color .18s ease, box-shadow .18s ease, background .18s ease;
    }

    .maintenance-control:focus {
        border-color: rgba(37, 99, 235, .38);
        box-shadow: 0 0 0 3px rgba(37, 99, 235, .11);
    }

    .maintenance-filter-actions {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: .55rem;
        flex-wrap: wrap;
    }

    .maintenance-export-strip {
        padding: .8rem 1.1rem;
        border-top: 1px solid var(--app-border);
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: .75rem;
        flex-wrap: wrap;
        background: var(--app-surface-soft);
    }

    .maintenance-export-note {
        display: inline-flex;
        align-items: center;
        gap: .45rem;
        color: var(--app-text-muted);
        font-size: .78rem;
        font-weight: 700;
    }

    .maintenance-table-wrap {
        width: 100%;
        overflow-x: auto;
    }

    .maintenance-table {
        width: 100%;
        min-width: 860px;
        border-collapse: collapse;
    }

    .maintenance-table th {
        padding: .85rem 1rem;
        color: var(--app-text-muted);
        background: var(--app-surface-soft);
        border-bottom: 1px solid var(--app-border);
        font-size: .68rem;
        font-weight: 800;
        letter-spacing: .05em;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .maintenance-table td {
        padding: .95rem 1rem;
        color: var(--app-text-soft);
        border-bottom: 1px solid var(--app-border);
        vertical-align: middle;
        font-size: .84rem;
        font-weight: 600;
    }

    .maintenance-table tbody tr:hover td {
        background: var(--app-row-hover);
    }

    .maintenance-check {
        width: 16px;
        height: 16px;
        accent-color: #2563eb;
        cursor: pointer;
    }

    .maintenance-asset-link {
        display: inline-flex;
        align-items: center;
        gap: .48rem;
        color: var(--app-text);
        font-weight: 800;
        text-decoration: none;
    }

    .maintenance-asset-link:hover {
        color: var(--app-accent);
        text-decoration: none;
    }

    .maintenance-row-muted {
        display: block;
        margin-top: .18rem;
        color: var(--app-text-muted);
        font-size: .72rem;
        font-weight: 700;
        line-height: 1.45;
    }

    .maintenance-date-chip,
    .maintenance-pic-chip {
        display: inline-flex;
        align-items: center;
        gap: .5rem;
    }

    .maintenance-date-chip i {
        color: var(--app-accent);
    }

    .maintenance-avatar {
        width: 30px;
        height: 30px;
        border-radius: 10px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: var(--app-accent);
        background: rgba(37, 99, 235, .1);
        font-size: .72rem;
        font-weight: 800;
        flex: 0 0 auto;
    }

    .maintenance-status-badge {
        display: inline-flex;
        align-items: center;
        gap: .38rem;
        border-radius: 999px;
        padding: .34rem .72rem;
        font-size: .72rem;
        font-weight: 800;
        white-space: nowrap;
    }

    .maintenance-status-badge.is-pending {
        color: #b45309;
        background: rgba(245, 158, 11, .13);
        border: 1px solid rgba(245, 158, 11, .26);
    }

    .maintenance-status-badge.is-approved {
        color: #047857;
        background: rgba(5, 150, 105, .12);
        border: 1px solid rgba(5, 150, 105, .24);
    }

    .maintenance-status-badge.is-rejected {
        color: #dc2626;
        background: rgba(220, 38, 38, .12);
        border: 1px solid rgba(220, 38, 38, .24);
    }

    .maintenance-row-actions {
        display: inline-flex;
        align-items: center;
        justify-content: flex-end;
        gap: .38rem;
    }

    .maintenance-icon-btn {
        width: 34px;
        height: 34px;
        border-radius: 10px;
        border: 1px solid var(--app-border);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: var(--app-surface);
        color: var(--app-text-soft);
        transition: transform .18s ease, border-color .18s ease, background .18s ease, color .18s ease;
    }

    .maintenance-icon-btn:hover,
    .maintenance-icon-btn:focus {
        color: var(--app-text);
        border-color: rgba(37, 99, 235, .28);
        text-decoration: none;
        transform: translateY(-1px);
    }

    .maintenance-icon-btn.is-info {
        color: #2563eb;
        background: rgba(37, 99, 235, .1);
        border-color: rgba(37, 99, 235, .22);
    }

    .maintenance-icon-btn.is-excel {
        color: #047857;
        background: rgba(5, 150, 105, .1);
        border-color: rgba(5, 150, 105, .22);
    }

    .maintenance-icon-btn.is-pdf {
        color: #dc2626;
        background: rgba(220, 38, 38, .1);
        border-color: rgba(220, 38, 38, .22);
    }

    .maintenance-empty-state {
        padding: 3.2rem 1rem;
        text-align: center;
        color: var(--app-text-muted);
    }

    .maintenance-empty-state i {
        width: 58px;
        height: 58px;
        border-radius: 18px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: var(--app-accent);
        background: rgba(37, 99, 235, .1);
        font-size: 1.55rem;
        margin-bottom: .85rem;
    }

    .maintenance-empty-state h3 {
        margin: 0;
        color: var(--app-text);
        font-size: 1rem;
        font-weight: 800;
    }

    .maintenance-empty-state p {
        margin: .35rem auto 0;
        max-width: 440px;
        font-size: .82rem;
        line-height: 1.7;
        font-weight: 600;
    }

    .maintenance-panel-footer {
        padding: .9rem 1.1rem;
        border-top: 1px solid var(--app-border);
        background: var(--app-surface-soft);
    }

    .maintenance-panel-footer .pagination {
        margin: 0;
    }

    body.dark-mode .maintenance-action-btn,
    body.dark-mode .maintenance-control,
    body.dark-mode .maintenance-icon-btn {
        background: var(--app-surface-soft);
        border-color: var(--app-border);
        color: var(--app-text-soft);
    }

    body.dark-mode .maintenance-control option {
        background: var(--app-surface);
        color: var(--app-text);
    }

    body.dark-mode .maintenance-summary-card.is-main {
        background: linear-gradient(135deg, #020617, #172554 56%, #0f766e);
        border-color: rgba(96, 165, 250, .12);
    }

    body.dark-mode .maintenance-action-btn.is-excel,
    body.dark-mode .maintenance-icon-btn.is-excel,
    body.dark-mode .maintenance-status-badge.is-approved {
        color: #86efac;
    }

    body.dark-mode .maintenance-action-btn.is-pdf,
    body.dark-mode .maintenance-icon-btn.is-pdf,
    body.dark-mode .maintenance-status-badge.is-rejected {
        color: #fca5a5;
    }

    body.dark-mode .maintenance-status-badge.is-pending {
        color: #fcd34d;
    }

    body.dark-mode .maintenance-panel-footer .page-link {
        background: var(--app-surface-soft);
        border-color: var(--app-border);
        color: var(--app-text-soft);
    }

    body.dark-mode .maintenance-panel-footer .page-item.active .page-link {
        background: var(--app-accent);
        border-color: var(--app-accent);
        color: #fff;
    }

    @media (max-width: 1199.98px) {
        .maintenance-summary-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .maintenance-field,
        .maintenance-field.is-search {
            grid-column: span 6;
        }

        .maintenance-field.is-actions {
            grid-column: span 12;
        }

        .maintenance-filter-actions {
            justify-content: flex-start;
        }
    }

    @media (max-width: 767.98px) {
        .maintenance-report-head {
            flex-direction: column;
        }

        .maintenance-report-actions,
        .maintenance-action-btn {
            width: 100%;
        }

        .maintenance-summary-grid,
        .maintenance-filter-grid {
            grid-template-columns: 1fr;
        }

        .maintenance-field,
        .maintenance-field.is-search,
        .maintenance-field.is-actions {
            grid-column: span 1;
        }

        .maintenance-filter-actions,
        .maintenance-export-strip {
            align-items: stretch;
            flex-direction: column;
        }
    }
</style>

<div class="maintenance-report-shell">
    <a id="download-bulk-report-anchor" href="#" class="d-none"></a>

    <div class="maintenance-report-head">
        <div class="maintenance-report-title-group">
            <span class="maintenance-report-icon">
                <i class="fas fa-tools"></i>
            </span>
            <div>
                <h1 class="maintenance-report-title">{{ __('app.maintenance.report_title') }}</h1>
                <p class="maintenance-report-subtitle">{{ __('app.maintenance.report_subtitle') }}</p>
            </div>
        </div>

        <div class="maintenance-report-actions" role="group" aria-label="{{ __('app.maintenance.download_report') }}">
            <button id="download-bulk-report-excel-button" type="button" class="maintenance-action-btn is-excel" title="{{ __('app.maintenance.download_excel_report') }}">
                <i class="fas fa-file-excel"></i>
                <span>Excel</span>
            </button>
            <button id="download-bulk-report-pdf-button" type="button" class="maintenance-action-btn is-pdf" title="{{ __('app.maintenance.download_pdf_report') }}">
                <i class="fas fa-file-pdf"></i>
                <span>PDF</span>
            </button>
        </div>
    </div>

    <section class="maintenance-summary-grid" aria-label="{{ __('app.maintenance.report_overview') }}">
        <article class="maintenance-summary-card is-main">
            <span class="maintenance-summary-label">
                <i class="fas fa-clipboard-list"></i>
                {{ __('app.maintenance.total_reports') }}
            </span>
            <strong class="maintenance-summary-value">{{ number_format($reports->total()) }}</strong>
            <span class="maintenance-summary-note">{{ __('app.maintenance.total_reports_note') }}</span>
        </article>
        <article class="maintenance-summary-card">
            <span class="maintenance-summary-label">
                <i class="fas fa-filter"></i>
                {{ __('app.maintenance.active_filters') }}
            </span>
            <strong class="maintenance-summary-value">{{ $activeFilterCount }}</strong>
            <span class="maintenance-summary-note">{{ $dateRangeLabel }} | {{ $statusFilterLabel }}</span>
        </article>
        <article class="maintenance-summary-card">
            <span class="maintenance-summary-label">
                <i class="fas fa-envelope-open-text"></i>
                {{ __('app.maintenance.notification_recipients') }}
            </span>
            <strong class="maintenance-summary-value">{{ (int) data_get($maintenanceNotificationConfig, 'totalCount', 1) }}</strong>
            <span class="maintenance-summary-note">
                {{ __('app.maintenance.master_email') }}: {{ $maintenanceNotificationMasterRecipient }}
            </span>
        </article>
        <article class="maintenance-summary-card">
            <span class="maintenance-summary-label">
                <i class="fas fa-check-square"></i>
                {{ __('app.maintenance.selected_reports') }}
            </span>
            <strong class="maintenance-summary-value" id="maintenance-selected-count-value">0</strong>
            <span class="maintenance-summary-note" id="maintenance-selected-export-note">{{ __('app.maintenance.filtered_export_note') }}</span>
        </article>
    </section>

    <form id="maintenance-report-filter-form" method="GET" action="{{ route('maintenance-report.index') }}" class="maintenance-panel">
        <div class="maintenance-panel-head">
            <div>
                <h2 class="maintenance-panel-title">
                    <i class="fas fa-sliders-h"></i>
                    {{ __('app.maintenance.filter_panel_title') }}
                </h2>
                <span class="maintenance-panel-note">{{ __('app.maintenance.filter_panel_note') }}</span>
            </div>
            <a href="{{ route('maintenance-report.index') }}" class="maintenance-action-btn">
                <i class="fas fa-undo"></i>
                <span>{{ __('app.maintenance.reset_filter') }}</span>
            </a>
        </div>
        <div class="maintenance-filter-body">
            <div class="maintenance-filter-grid">
                <div class="maintenance-field">
                    <label for="maintenance-date-from">
                        <i class="fas fa-calendar-day"></i>
                        {{ __('app.maintenance.filter_date_from') }}
                    </label>
                    <input
                        id="maintenance-date-from"
                        type="date"
                        name="date_from"
                        value="{{ request('date_from') }}"
                        class="maintenance-control"
                    />
                </div>
                <div class="maintenance-field">
                    <label for="maintenance-date-to">
                        <i class="fas fa-calendar-check"></i>
                        {{ __('app.maintenance.filter_date_to') }}
                    </label>
                    <input
                        id="maintenance-date-to"
                        type="date"
                        name="date_to"
                        value="{{ request('date_to') }}"
                        class="maintenance-control"
                    />
                </div>
                <div class="maintenance-field">
                    <label for="filter-status-select">
                        <i class="fas fa-tags"></i>
                        {{ __('app.maintenance.status_upper') }}
                    </label>
                    <select name="status" id="filter-status-select" class="maintenance-control">
                        <option value="">{{ __('app.maintenance.all_statuses') }}</option>
                        @foreach (AssetMaintenanceReportStatus::cases() as $status)
                            <option value="{{ $status->value }}" {{ request('status') == $status->value ? 'selected' : '' }}>
                                {{ __('app.maintenance.statuses.' . strtolower($status->value)) }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="maintenance-field is-search">
                    <label for="maintenance-keyword">
                        <i class="fas fa-search"></i>
                        {{ __('app.maintenance.keyword') }}
                    </label>
                    <input
                        id="maintenance-keyword"
                        type="text"
                        name="keyword"
                        value="{{ request('keyword') }}"
                        class="maintenance-control"
                        placeholder="{{ __('app.maintenance.search_placeholder') }}"
                    />
                </div>
                <div class="maintenance-field is-actions">
                    <div class="maintenance-filter-actions">
                        <button type="submit" class="maintenance-action-btn is-primary">
                            <i class="fas fa-search"></i>
                            <span>{{ __('app.maintenance.apply_filter') }}</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <div class="maintenance-export-strip">
            <span class="maintenance-export-note">
                <i class="fas fa-info-circle"></i>
                <span>{{ __('app.maintenance.export_scope_note') }}</span>
            </span>
        </div>
    </form>

    <section class="maintenance-panel">
        <div class="maintenance-panel-head">
            <div>
                <h2 class="maintenance-panel-title">
                    <i class="fas fa-tasks"></i>
                    {{ __('app.maintenance.table_title') }}
                </h2>
                <span class="maintenance-panel-note">
                    {{ __('app.maintenance.current_page_count', ['count' => $reports->count(), 'total' => $reports->total()]) }}
                </span>
            </div>
            <div class="maintenance-report-actions">
                @foreach (AssetMaintenanceReportStatus::cases() as $status)
                    <span class="maintenance-status-badge is-{{ strtolower($status->value) }}">
                        <i class="fas fa-circle"></i>
                        {{ __('app.maintenance.statuses.' . strtolower($status->value)) }}: {{ $statusPageCounts[$status->value] ?? 0 }}
                    </span>
                @endforeach
            </div>
        </div>
        <div class="maintenance-table-wrap">
            <table class="maintenance-table">
                <thead>
                    <tr>
                        <th scope="col">
                            <input id="root-checkbox" class="maintenance-check" type="checkbox" aria-label="{{ __('app.maintenance.select_all') }}">
                        </th>
                        <th scope="col">#</th>
                        <th scope="col">{{ __('app.maintenance.asset_code_upper') }}</th>
                        <th scope="col">{{ __('app.maintenance.working_date') }}</th>
                        <th scope="col">{{ __('app.maintenance.pic_upper') }}</th>
                        <th scope="col">{{ __('app.maintenance.status_upper') }}</th>
                        <th scope="col" class="text-right">{{ __('app.maintenance.action_upper') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($reports as $report)
                        @php
                            $asset = $report->asset;
                            $picInitial = strtoupper(substr((string) ($report->pic ?: '?'), 0, 1));
                            $statusValue = (string) $report->status->value;
                        @endphp
                        <tr>
                            <td>
                                <input class="child-checkbox maintenance-check" type="checkbox" value="{{ $report->id }}" aria-label="{{ __('app.maintenance.select_report') }}">
                            </td>
                            <th scope="row">{{ $reports->firstItem() + $loop->index }}</th>
                            <td>
                                <a class="maintenance-asset-link" href="{{ \App\Support\AssetPublicUrl::detailUrl((string) $asset->id) }}" target="_blank" rel="noopener">
                                    <i class="fas fa-external-link-alt"></i>
                                    <span>{{ $asset->account_code }}</span>
                                </a>
                                <span class="maintenance-row-muted">{{ $asset->location ?? '-' }}</span>
                            </td>
                            <td>
                                <span class="maintenance-date-chip">
                                    <i class="fas fa-calendar-alt"></i>
                                    <span>{{ optional($report->date)->format('d/m/Y') ?? '-' }}</span>
                                </span>
                            </td>
                            <td>
                                <span class="maintenance-pic-chip">
                                    <span class="maintenance-avatar">{{ $picInitial }}</span>
                                    <span>{{ $report->pic }}</span>
                                </span>
                            </td>
                            <td>
                                <span class="maintenance-status-badge is-{{ strtolower($statusValue) }}">
                                    <i class="fas fa-circle"></i>
                                    {{ __('app.maintenance.statuses.' . strtolower($statusValue)) }}
                                </span>
                            </td>
                            <td class="text-right">
                                <span class="maintenance-row-actions">
                                    <button
                                        id="toggle-maintenance-report-detail-button"
                                        type="button"
                                        class="maintenance-icon-btn is-info"
                                        data-url="{{ route('maintenance-report.detail', $report->id) }}"
                                        title="{{ __('app.maintenance.view_detail') }}"
                                        aria-label="{{ __('app.maintenance.view_detail') }}"
                                    >
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <a
                                        href="{{ route('maintenance-report.export-excel', ['ids' => [$report->id]]) }}"
                                        class="maintenance-icon-btn is-excel"
                                        title="{{ __('app.maintenance.download_excel_report') }}"
                                        aria-label="{{ __('app.maintenance.download_excel_report') }}"
                                    >
                                        <i class="fas fa-file-excel"></i>
                                    </a>
                                    <a
                                        href="{{ route('maintenance-report.export-pdf', ['ids' => [$report->id]]) }}"
                                        class="maintenance-icon-btn is-pdf"
                                        title="{{ __('app.maintenance.download_pdf_report') }}"
                                        aria-label="{{ __('app.maintenance.download_pdf_report') }}"
                                    >
                                        <i class="fas fa-file-pdf"></i>
                                    </a>
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">
                                <div class="maintenance-empty-state">
                                    <i class="fas fa-clipboard-check"></i>
                                    <h3>{{ __('app.maintenance.no_report_data') }}</h3>
                                    <p>{{ __('app.maintenance.no_report_data_note') }}</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="maintenance-panel-footer">
            {{ $reports->appends(request()->query())->links() }}
        </div>
    </section>
</div>
