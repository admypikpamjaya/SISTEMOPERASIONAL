@extends('layouts.app')

@section('content')
@php
    $summary = $report['summary'] ?? [];
    $sections = $report['sections'] ?? [];
    $uncategorizedCount = (int) ($report['uncategorized_count'] ?? 0);
    $uncategorizedRows = $report['uncategorized_rows'] ?? [];
    $uncategorizedSummary = $report['uncategorized_summary'] ?? [
        'profit_loss_count' => 0,
        'other_count' => 0,
        'unmapped_count' => 0,
    ];
    $hasRows = collect($sections)->sum(fn ($section) => count($section['rows'] ?? [])) > 0;
    $baseFilterQuery = $baseFilterQuery ?? ($filterQuery ?? []);
    $isManageMode = $isManageMode ?? false;
    $pageRouteName = $pageRouteName ?? 'finance.report.balance-sheet';
    $mainStatementRouteName = $mainStatementRouteName ?? 'finance.report.balance-sheet';
    $manageStatementRouteName = $manageStatementRouteName ?? 'finance.report.balance-sheet.manage';
    $statementDataSource = $statementDataSource ?? 'system';
    $isImportedSource = $statementDataSource === 'imported';
    $isCombinedSource = $statementDataSource === 'combined';
    $usesImportedData = in_array($statementDataSource, ['imported', 'combined'], true);
    $selectedBatchId = $selectedBatchId ?? null;
    $batchOptions = $batchOptions ?? [];
    $selectedBatch = $selectedBatch ?? null;
    $selectedBatchMeta = collect($batchOptions)->firstWhere('id', $selectedBatchId) ?? $selectedBatch;
    $importedRows = $importedRows ?? [];
    $editImportedRow = $editImportedRow ?? null;
    $financeCategoryOptions = $financeCategoryOptions ?? collect();
    $selectedFinanceCategoryId = old(
        'category_id',
        data_get($editImportedRow, 'category_id')
            ?? data_get($filters, 'category_id')
            ?? data_get($selectedBatchMeta, 'category_id')
    );
    $statementTypeOptions = \App\Models\FinanceAccount::manualStatementTypeOptions();
    $permissionService = app(\App\Services\AccessControl\PermissionService::class);
    $canManageStatementMapping = auth()->check()
        && $permissionService->checkAccess(
            auth()->user(),
            \App\Enums\Portal\PortalPermission::FINANCE_REPORT_GENERATE->value
        );
    $sourceQueryBase = collect($filterQuery ?? [])
        ->except(['statement_data_source', 'statement_batch_id', 'page'])
        ->filter(static fn ($value): bool => $value !== null && $value !== '')
        ->all();
    $combinedSourceQuery = array_merge(
        $sourceQueryBase,
        ['statement_data_source' => 'combined'],
        $selectedBatchId ? ['statement_batch_id' => $selectedBatchId] : []
    );
    $systemSourceQuery = array_merge($sourceQueryBase, ['statement_data_source' => 'system']);
    $importedSourceQuery = array_merge(
        $sourceQueryBase,
        ['statement_data_source' => 'imported', 'period_type' => $isImportedSource ? (data_get($filters, 'period_type', 'ALL') ?: 'ALL') : 'ALL'],
        $selectedBatchId ? ['statement_batch_id' => $selectedBatchId] : []
    );
    $statementLabel = \Illuminate\Support\Str::lower(__('app.finance.balance_sheet'));
    $pageSubtitle = $isManageMode
        ? __('app.finance.statement_manage_subtitle', ['statement' => $statementLabel])
        : ($isCombinedSource
            ? __('app.finance.statement_combined_subtitle', ['statement' => $statementLabel, 'period' => $periodLabel])
            : ($isImportedSource
            ? __('app.finance.statement_imported_subtitle', ['statement' => $statementLabel])
            : __('app.finance.statement_system_subtitle', ['items' => __('app.finance.balance_sheet_items'), 'period' => $periodLabel])));
    $sectionMeta = [
        'liabilitas' => ['icon' => 'fa-landmark', 'badge' => 'fs-danger'],
        'piutang' => ['icon' => 'fa-file-invoice-dollar', 'badge' => 'fs-blue'],
        'kas' => ['icon' => 'fa-wallet', 'badge' => 'fs-green'],
        'aset' => ['icon' => 'fa-building', 'badge' => 'fs-amber'],
    ];
@endphp

<style>
    @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');

    :root {
        --fs-blue: #1d4ed8;
        --fs-blue-dark: #1e3a8a;
        --fs-blue-soft: rgba(37, 99, 235, 0.10);
        --fs-green: #059669;
        --fs-green-soft: rgba(16, 185, 129, 0.12);
        --fs-red: #dc2626;
        --fs-red-soft: rgba(239, 68, 68, 0.12);
        --fs-amber: #d97706;
        --fs-amber-soft: rgba(245, 158, 11, 0.12);
        --fs-bg: #f0f4fd;
        --fs-card: #ffffff;
        --fs-text: #0f172a;
        --fs-muted: #64748b;
        --fs-border: rgba(37, 99, 235, 0.10);
        --fs-shadow: 0 10px 32px rgba(15, 23, 42, 0.08), 0 4px 14px rgba(37, 99, 235, 0.06);
        --fs-radius: 18px;
        --fs-radius-sm: 12px;
    }

    body, .content-wrapper {
        background: var(--fs-bg) !important;
        font-family: 'Plus Jakarta Sans', sans-serif !important;
    }

    .fs-page-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        flex-wrap: wrap;
        margin-bottom: 1.5rem;
    }
    .fs-page-title {
        display: flex;
        align-items: center;
        gap: 0.9rem;
    }
    .fs-title-icon {
        width: 50px;
        height: 50px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, var(--fs-blue), var(--fs-blue-dark));
        color: #fff;
        font-size: 1.2rem;
        box-shadow: var(--fs-shadow);
    }
    .fs-page-title h1 {
        margin: 0;
        color: var(--fs-text);
        font-size: 1.4rem;
        font-weight: 800;
    }
    .fs-page-title p {
        margin: 0.15rem 0 0;
        color: var(--fs-muted);
        font-size: 0.82rem;
        font-weight: 500;
    }
    .fs-nav {
        display: flex;
        flex-wrap: wrap;
        gap: 0.55rem;
    }
    .fs-nav-link,
    .fs-btn {
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        border-radius: 12px;
        padding: 0.6rem 1rem;
        font-size: 0.82rem;
        font-weight: 700;
        text-decoration: none;
        border: 1px solid transparent;
        transition: all 0.2s ease;
    }
    .fs-nav-link:hover,
    .fs-btn:hover {
        text-decoration: none;
        transform: translateY(-1px);
    }
    .fs-nav-link.primary,
    .fs-btn-primary {
        background: linear-gradient(135deg, var(--fs-blue), #2563eb);
        color: #fff;
        box-shadow: 0 8px 22px rgba(37, 99, 235, 0.24);
    }
    .fs-nav-link.muted,
    .fs-btn-muted {
        background: #fff;
        color: var(--fs-muted);
        border-color: var(--fs-border);
    }

    .fs-filter-card,
    .fs-summary-card,
    .fs-section-card,
    .fs-empty-card,
    .fs-note-card {
        background: var(--fs-card);
        border: 1px solid var(--fs-border);
        border-radius: var(--fs-radius);
        box-shadow: var(--fs-shadow);
    }

    .fs-filter-head,
    .fs-section-head {
        padding: 1rem 1.2rem;
        border-bottom: 1px solid var(--fs-border);
    }
    .fs-filter-head {
        background: linear-gradient(135deg, var(--fs-blue-dark), var(--fs-blue));
        border-bottom: none;
        border-radius: var(--fs-radius) var(--fs-radius) 0 0;
    }
    .fs-filter-title,
    .fs-section-title {
        display: flex;
        align-items: center;
        gap: 0.65rem;
        font-size: 0.9rem;
        font-weight: 700;
    }
    .fs-filter-title {
        color: #fff;
    }
    .fs-section-title {
        color: var(--fs-text);
    }
    .fs-filter-icon,
    .fs-section-icon {
        width: 30px;
        height: 30px;
        border-radius: 10px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }
    .fs-filter-icon {
        background: rgba(255, 255, 255, 0.16);
    }
    .fs-section-icon {
        background: rgba(37, 99, 235, 0.08);
        color: var(--fs-blue);
    }
    .fs-filter-body {
        padding: 1.1rem 1.2rem 0.3rem;
    }
    .fs-field {
        margin-bottom: 1rem;
    }
    .fs-label {
        display: flex;
        align-items: center;
        gap: 0.35rem;
        margin-bottom: 0.4rem;
        font-size: 0.7rem;
        font-weight: 800;
        letter-spacing: 0.06em;
        text-transform: uppercase;
        color: var(--fs-muted);
    }
    .fs-control {
        width: 100%;
        border: 1.5px solid rgba(148, 163, 184, 0.18);
        border-radius: var(--fs-radius-sm);
        padding: 0.65rem 0.85rem;
        font-size: 0.84rem;
        color: var(--fs-text);
        background: #fff;
    }
    .fs-control:focus {
        outline: none;
        border-color: rgba(37, 99, 235, 0.4);
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
    }
    .fs-actions {
        display: flex;
        align-items: flex-end;
        gap: 0.55rem;
        margin-bottom: 1rem;
        flex-wrap: wrap;
    }

    .fs-summary-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(210px, 1fr));
        gap: 0.9rem;
        margin: 1.2rem 0;
    }
    .fs-summary-card {
        padding: 1rem 1.1rem;
    }
    .fs-summary-label {
        display: flex;
        align-items: center;
        gap: 0.45rem;
        color: var(--fs-muted);
        font-size: 0.72rem;
        font-weight: 800;
        letter-spacing: 0.06em;
        text-transform: uppercase;
        margin-bottom: 0.45rem;
    }
    .fs-summary-value {
        color: var(--fs-text);
        font-size: 1.3rem;
        font-weight: 800;
        line-height: 1.15;
    }
    .fs-summary-help {
        margin-top: 0.35rem;
        color: var(--fs-muted);
        font-size: 0.75rem;
        font-weight: 500;
    }

    .fs-note-card {
        margin-bottom: 1rem;
        padding: 0.9rem 1rem;
        display: flex;
        gap: 0.7rem;
        align-items: flex-start;
        color: #92400e;
        background: rgba(245, 158, 11, 0.08);
        border-color: rgba(245, 158, 11, 0.16);
    }
    .fs-note-copy {
        display: grid;
        gap: 0.35rem;
    }
    .fs-note-copy strong {
        color: var(--fs-text);
    }
    .fs-note-breakdown {
        display: flex;
        flex-wrap: wrap;
        gap: 0.45rem;
        margin-top: 0.15rem;
    }
    .fs-uncat-card {
        margin-bottom: 1rem;
    }
    .fs-uncat-status {
        display: grid;
        gap: 0.15rem;
    }
    .fs-uncat-status strong {
        color: var(--fs-text);
        font-size: 0.8rem;
    }
    .fs-uncat-status span {
        color: var(--fs-muted);
        font-size: 0.74rem;
        line-height: 1.45;
    }
    .fs-uncat-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 0.45rem;
    }
    .fs-inline-link {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        border-radius: 999px;
        padding: 0.35rem 0.75rem;
        border: 1px solid rgba(37, 99, 235, 0.12);
        background: rgba(37, 99, 235, 0.05);
        color: var(--fs-blue);
        font-size: 0.72rem;
        font-weight: 800;
        text-decoration: none;
    }
    .fs-inline-link:hover {
        text-decoration: none;
        color: #fff;
        background: var(--fs-blue);
        border-color: var(--fs-blue);
    }
    .fs-map-form {
        display: grid;
        grid-template-columns: minmax(220px, 1fr) auto;
        gap: 0.5rem;
        align-items: center;
    }
    .fs-map-form .fs-control {
        min-width: 220px;
    }
    .fs-map-help {
        color: var(--fs-muted);
        font-size: 0.74rem;
        line-height: 1.45;
    }

    .fs-section-grid {
        display: grid;
        grid-template-columns: 1fr;
        gap: 1rem;
    }
    .fs-section-card {
        width: 100%;
    }
    .fs-section-total {
        color: var(--fs-muted);
        font-size: 0.8rem;
        font-weight: 700;
    }
    .fs-table-wrap {
        overflow-x: auto;
    }
    .fs-table {
        width: 100%;
        border-collapse: collapse;
    }
    .fs-table th {
        background: #f8fbff;
        color: var(--fs-muted);
        font-size: 0.68rem;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        padding: 0.75rem 1rem;
        border-bottom: 1px solid var(--fs-border);
    }
    .fs-table td {
        padding: 0.78rem 1rem;
        font-size: 0.82rem;
        color: #334155;
        border-bottom: 1px solid rgba(148, 163, 184, 0.12);
        vertical-align: middle;
    }
    .fs-table tbody tr:last-child td {
        border-bottom: none;
    }
    .fs-table tbody tr:hover td {
        background: rgba(37, 99, 235, 0.03);
    }
    .fs-amount {
        text-align: right;
        font-weight: 800;
        color: var(--fs-blue);
        white-space: nowrap;
    }
    .fs-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        border-radius: 999px;
        padding: 0.25rem 0.65rem;
        font-size: 0.68rem;
        font-weight: 800;
    }
    .fs-badge.fs-blue { background: var(--fs-blue-soft); color: var(--fs-blue); }
    .fs-badge.fs-green { background: var(--fs-green-soft); color: var(--fs-green); }
    .fs-badge.fs-danger { background: var(--fs-red-soft); color: var(--fs-red); }
    .fs-badge.fs-amber { background: var(--fs-amber-soft); color: var(--fs-amber); }
    .fs-account-cell {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
    }
    .fs-account-name {
        min-width: 0;
        flex: 1 1 auto;
    }
    .fs-account-link,
    .fs-amount-link {
        color: inherit;
        text-decoration: none;
    }
    .fs-account-link:hover,
    .fs-amount-link:hover {
        color: var(--fs-blue);
        text-decoration: none;
    }
    .fs-amount-link {
        display: inline-flex;
        justify-content: flex-end;
        width: 100%;
        font-weight: 800;
    }
    .fs-row-menu-btn {
        width: 30px;
        height: 30px;
        border: none;
        border-radius: 50%;
        background: transparent;
        color: var(--fs-muted);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.2s ease;
        flex-shrink: 0;
    }
    .fs-row-menu-btn::after { display: none; }
    .fs-row-menu-btn:hover {
        background: rgba(37, 99, 235, 0.08);
        color: var(--fs-blue);
    }
    .fs-row-menu {
        min-width: 180px;
        border-radius: 12px;
        border: 1px solid rgba(148, 163, 184, 0.18);
        box-shadow: 0 14px 30px rgba(15, 23, 42, 0.12);
        padding: 0.45rem;
    }
    .fs-row-menu .dropdown-item {
        border-radius: 8px;
        font-size: 0.78rem;
        font-weight: 700;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        color: var(--fs-text);
        padding: 0.55rem 0.7rem;
    }
    .fs-row-menu .dropdown-item:hover {
        background: rgba(37, 99, 235, 0.08);
        color: var(--fs-blue);
    }

    .fs-empty-card {
        margin-top: 1rem;
        padding: 2.5rem 1.2rem;
        text-align: center;
        color: var(--fs-muted);
    }
    .fs-empty-card i {
        font-size: 2.3rem;
        margin-bottom: 0.8rem;
        color: rgba(37, 99, 235, 0.28);
    }
    .fs-empty-card h4 {
        color: var(--fs-text);
        font-size: 1rem;
        font-weight: 700;
        margin-bottom: 0.35rem;
    }

    body.dark-mode,
    body.dark-mode .content-wrapper {
        background: var(--app-bg) !important;
    }
    body.dark-mode .fs-filter-card,
    body.dark-mode .fs-summary-card,
    body.dark-mode .fs-section-card,
    body.dark-mode .fs-empty-card,
    body.dark-mode .fs-note-card {
        background: var(--app-surface) !important;
        border-color: var(--app-border) !important;
        box-shadow: var(--app-shadow) !important;
    }
    body.dark-mode .fs-page-title h1,
    body.dark-mode .fs-summary-value,
    body.dark-mode .fs-section-title,
    body.dark-mode .fs-empty-card h4 {
        color: var(--app-text) !important;
    }
    body.dark-mode .fs-page-title p,
    body.dark-mode .fs-label,
    body.dark-mode .fs-summary-help,
    body.dark-mode .fs-summary-label,
    body.dark-mode .fs-section-total,
    body.dark-mode .fs-empty-card,
    body.dark-mode .fs-table th,
    body.dark-mode .fs-note-card {
        color: var(--app-text-muted) !important;
    }
    body.dark-mode .fs-control,
    body.dark-mode .fs-nav-link.muted,
    body.dark-mode .fs-btn-muted {
        background: var(--app-surface-soft) !important;
        border-color: var(--app-border) !important;
        color: var(--app-text) !important;
    }
    body.dark-mode .fs-control:focus {
        background: var(--app-surface) !important;
        border-color: rgba(96, 165, 250, 0.36) !important;
        box-shadow: 0 0 0 3px rgba(96, 165, 250, 0.14) !important;
    }
    body.dark-mode .fs-control option {
        background: var(--app-surface) !important;
        color: var(--app-text) !important;
    }
    body.dark-mode .fs-table th {
        background: var(--app-surface-soft) !important;
        border-color: var(--app-border) !important;
    }
    body.dark-mode .fs-table td {
        color: var(--app-text-soft) !important;
        border-color: var(--app-border) !important;
    }
    body.dark-mode .fs-table td strong,
    body.dark-mode .fs-note-card i,
    body.dark-mode .fs-amount {
        color: var(--app-text) !important;
    }
    body.dark-mode .fs-note-copy strong,
    body.dark-mode .fs-uncat-status strong {
        color: var(--app-text) !important;
    }
    body.dark-mode .fs-uncat-status span,
    body.dark-mode .fs-map-help {
        color: var(--app-text-muted) !important;
    }
    body.dark-mode .fs-account-link,
    body.dark-mode .fs-amount-link {
        color: inherit !important;
    }
    body.dark-mode .fs-account-link:hover,
    body.dark-mode .fs-amount-link:hover {
        color: var(--app-accent) !important;
    }
    body.dark-mode .fs-row-menu {
        background: var(--app-surface) !important;
        border-color: var(--app-border) !important;
        box-shadow: var(--app-shadow) !important;
    }
    body.dark-mode .fs-row-menu .dropdown-item {
        color: var(--app-text-soft) !important;
    }
    body.dark-mode .fs-row-menu .dropdown-item:hover {
        background: var(--app-surface-soft) !important;
        color: var(--app-text) !important;
    }
    body.dark-mode .fs-table tbody tr:hover td {
        background: var(--app-row-hover) !important;
    }
    body.dark-mode .fs-section-icon {
        background: rgba(96, 165, 250, 0.12) !important;
        color: var(--app-accent) !important;
    }
    body.dark-mode .fs-inline-link {
        background: rgba(96, 165, 250, 0.12) !important;
        border-color: rgba(96, 165, 250, 0.18) !important;
        color: var(--app-text) !important;
    }
    body.dark-mode .fs-inline-link:hover {
        background: var(--app-accent) !important;
        border-color: var(--app-accent) !important;
        color: #fff !important;
    }
    .fs-source-card,
    .fs-manage-grid .fs-section-card {
        margin-top: 1rem;
    }
    .fs-source-actions,
    .fs-manage-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 1rem;
    }
    .fs-source-switch {
        display: flex;
        gap: 0.55rem;
        flex-wrap: wrap;
        margin-top: 0.9rem;
    }
    .fs-switch-link {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        padding: 0.6rem 0.9rem;
        border-radius: 12px;
        border: 1px solid var(--fs-border);
        background: #fff;
        color: var(--fs-muted);
        font-size: 0.8rem;
        font-weight: 700;
        text-decoration: none;
    }
    .fs-switch-link.active {
        background: linear-gradient(135deg, var(--fs-blue), #2563eb);
        border-color: transparent;
        color: #fff;
        box-shadow: 0 10px 20px rgba(37, 99, 235, 0.16);
    }
    .fs-manage-form {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 0.85rem 1rem;
    }
    .fs-manage-form .fs-field {
        margin-bottom: 0;
    }
    .fs-manage-form .fs-field.full {
        grid-column: 1 / -1;
    }
    .fs-manage-actions {
        display: flex;
        gap: 0.55rem;
        flex-wrap: wrap;
        align-items: center;
    }
    .fs-manage-table td,
    .fs-manage-table th {
        vertical-align: top;
    }
    .fs-soft-copy {
        color: var(--fs-muted);
        font-size: 0.76rem;
        line-height: 1.5;
    }
    .fs-pill {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.28rem 0.6rem;
        border-radius: 999px;
        background: rgba(37, 99, 235, 0.08);
        color: var(--fs-blue);
        font-size: 0.72rem;
        font-weight: 700;
    }
    body.dark-mode .fs-switch-link {
        background: var(--app-surface) !important;
        border-color: var(--app-border) !important;
        color: var(--app-text-soft) !important;
    }
    body.dark-mode .fs-switch-link.active {
        background: linear-gradient(135deg, var(--app-accent), #2563eb) !important;
        color: #fff !important;
        border-color: transparent !important;
    }
    @media (min-width: 1200px) {
        .fs-section-grid {
            gap: 1.15rem;
        }
        .fs-table th,
        .fs-table td {
            padding-left: 1.1rem;
            padding-right: 1.1rem;
        }
    }
    @media (max-width: 991px) {
        .fs-map-form {
            grid-template-columns: 1fr;
        }
        .fs-map-form .fs-control {
            min-width: 100%;
        }
        .fs-manage-form {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="fs-page-header">
    <div class="fs-page-title">
        <div class="fs-title-icon"><i class="fas fa-balance-scale"></i></div>
        <div>
            <h1>{{ __('app.finance.balance_sheet_report_title') }}</h1>
            <p>{{ $pageSubtitle }}</p>
        </div>
    </div>

    <div class="fs-nav">
        <a href="{{ route('finance.dashboard') }}" class="fs-nav-link muted">
            <i class="fas fa-arrow-left"></i> {{ __('app.finance.dashboard') }}
        </a>
        @if($isManageMode)
            <a href="{{ route($mainStatementRouteName, array_filter(array_merge($filterQuery ?? [], ['statement_data_source' => $statementDataSource, 'statement_batch_id' => $selectedBatchId]), static fn ($value): bool => $value !== null && $value !== '')) }}" class="fs-nav-link muted">
                <i class="fas fa-table-columns"></i> {{ __('app.finance.main_page') }}
            </a>
        @else
            <a href="{{ route($manageStatementRouteName, array_filter(array_merge($filterQuery ?? [], ['statement_data_source' => 'imported', 'statement_batch_id' => $selectedBatchId]), static fn ($value): bool => $value !== null && $value !== '')) }}" class="fs-nav-link muted">
                <i class="fas fa-sliders-h"></i> {{ __('app.finance.import_edit_manual') }}
            </a>
        @endif
        <a href="{{ route('finance.report.balance-sheet.download', array_merge($filterQuery, ['format' => 'excel'])) }}" class="fs-nav-link muted">
            <i class="fas fa-file-excel"></i> {{ __('app.finance.download_excel') }}
        </a>
        <a href="{{ route('finance.report.balance-sheet.download', $filterQuery) }}" class="fs-nav-link primary">
            <i class="fas fa-file-pdf"></i> {{ __('app.finance.download_pdf') }}
        </a>
        <form method="POST" action="{{ route('finance.report.balance-sheet.delete-all') }}" onsubmit="return confirm('Apakah Anda yakin ingin menghapus SEMUA data Lembar Saldo? Tindakan ini tidak dapat dibatalkan.')" style="display: inline;">
            @csrf
            @method('DELETE')
            <button type="submit" class="fs-nav-link" style="color: #dc2626; background: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.2); cursor: pointer;">
                <i class="fas fa-trash-alt"></i> Delete All Data
            </button>
        </form>
        <a href="{{ route('finance.report.audit-comparison', array_merge($filterQuery, ['statement_type' => 'BALANCE_SHEET'])) }}" class="fs-nav-link muted">
            <i class="fas fa-balance-scale-right"></i> Komparasi Audit
        </a>
        <a href="{{ route('finance.report.profit-loss', $filterQuery) }}" class="fs-nav-link muted">
            <i class="fas fa-chart-area"></i> {{ __('app.finance.profit_loss') }}
        </a>
        <a href="{{ route('finance.report.general-ledger', $filterQuery) }}" class="fs-nav-link muted">
            <i class="fas fa-book-open"></i> {{ __('app.finance.general_ledger') }}
        </a>
    </div>
</div>

@include('finance.partials.statement-filter', [
    'action' => route($pageRouteName),
    'filters' => $filters,
    'showPerPage' => false,
])

<div class="fs-section-card fs-source-card">
    <div class="fs-section-head d-flex justify-content-between align-items-center flex-wrap" style="gap:.8rem;">
        <div class="fs-section-title">
            <span class="fs-section-icon"><i class="fas fa-database"></i></span>
            <span>{{ $isManageMode ? __('app.finance.import_manual') : __('app.finance.report_data_source') }}</span>
        </div>
        @if($usesImportedData && $selectedBatchMeta)
            <span class="fs-badge fs-blue">
                <i class="fas fa-layer-group"></i>
                {{ $selectedBatchMeta['batch_name'] ?? __('app.finance.batch_import') }}
            </span>
        @endif
    </div>
    <div class="p-3">
        <div class="fs-soft-copy">
            {{ $isCombinedSource
                ? __('app.finance.combined_source_note', ['statement' => $statementLabel])
                : ($isImportedSource
                ? ($isManageMode
                    ? __('app.finance.imported_manage_source_note', ['statement' => $statementLabel])
                    : __('app.finance.imported_source_note', ['statement' => $statementLabel]))
                : __('app.finance.system_source_note')) }}
        </div>

        @unless($isManageMode)
            <div class="fs-source-switch">
                <a href="{{ route($pageRouteName, $combinedSourceQuery) }}" class="fs-switch-link {{ $isCombinedSource ? 'active' : '' }}">
                    <i class="fas fa-layer-group"></i> {{ __('app.finance.combined_data') }}
                </a>
                <a href="{{ route($pageRouteName, $systemSourceQuery) }}" class="fs-switch-link {{ !$isImportedSource && !$isCombinedSource ? 'active' : '' }}">
                    <i class="fas fa-server"></i> {{ __('app.finance.system_data') }}
                </a>
                <a href="{{ route($pageRouteName, $importedSourceQuery) }}" class="fs-switch-link {{ $isImportedSource ? 'active' : '' }}">
                    <i class="fas fa-file-import"></i> {{ __('app.finance.import_result') }}
                </a>
            </div>
        @endunless

        @if($usesImportedData)
            <form method="GET" action="{{ route($pageRouteName) }}" class="fs-source-actions mt-3">
                <input type="hidden" name="statement_data_source" value="{{ $statementDataSource }}">
                @if($isImportedSource)
                    <input type="hidden" name="period_type" value="{{ data_get($filters, 'period_type', 'ALL') }}">
                @endif
                @foreach(($baseFilterQuery ?? []) as $queryKey => $queryValue)
                    @if(
                        !in_array($queryKey, ['statement_data_source', 'statement_batch_id'], true)
                        && !($isImportedSource && $queryKey === 'period_type')
                    )
                        <input type="hidden" name="{{ $queryKey }}" value="{{ $queryValue }}">
                    @endif
                @endforeach
                <div class="fs-field">
                    <label class="fs-label" for="balance_statement_batch_id">
                        <i class="fas fa-copy"></i> {{ __('app.finance.select_import_batch') }}
                    </label>
                    <select name="statement_batch_id" id="balance_statement_batch_id" class="fs-control" onchange="this.form.submit()">
                        <option value="">{{ __('app.finance.select_batch_placeholder') }}</option>
                        @foreach($batchOptions as $batchOption)
                            <option value="{{ $batchOption['id'] }}" {{ $selectedBatchId === $batchOption['id'] ? 'selected' : '' }}>
                                {{ $batchOption['batch_name'] }}{{ !empty($batchOption['imported_year']) ? ' • ' . $batchOption['imported_year'] : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>
                @if($selectedBatchMeta)
                    <div class="fs-field">
                        <label class="fs-label">
                            <i class="fas fa-info-circle"></i> {{ __('app.finance.batch_summary') }}
                        </label>
                        <div class="fs-soft-copy">
                            {{ number_format((int) ($selectedBatchMeta['row_count'] ?? 0), 0, ',', '.') }} {{ __('app.finance.rows') }}
                            @if(!empty($selectedBatchMeta['manual_count']))
                                - {{ number_format((int) $selectedBatchMeta['manual_count'], 0, ',', '.') }} {{ __('app.finance.manual') }}
                            @endif
                            @if(!empty($selectedBatchMeta['imported_year']))
                                - {{ __('app.finance.import_year') }} {{ $selectedBatchMeta['imported_year'] }}
                            @endif
                        </div>
                    </div>
                @endif
            </form>
        @endif
    </div>
</div>

@if($isManageMode && $isImportedSource && $canManageStatementMapping)
    @php
        $balanceRowForm = $editImportedRow ?? [
            'section_key' => 'aset',
            'section_label' => 'Aset',
            'group_label' => null,
            'account_code' => null,
            'account_name' => null,
            'finance_type' => 'ASET',
            'amount' => 0,
            'batch_id' => $selectedBatchId,
        ];
    @endphp
    <div class="fs-manage-grid">
        <div class="fs-section-card">
            <div class="fs-section-head">
                <div class="fs-section-title">
                    <span class="fs-section-icon"><i class="fas fa-file-import"></i></span>
                    <span>{{ __('app.finance.import_excel_balance_sheet') }}</span>
                </div>
            </div>
            <div class="p-3">
                <form method="POST" action="{{ route('finance.report.balance-sheet.import') }}" enctype="multipart/form-data" class="fs-manage-form">
                    @csrf
                    <div class="fs-field full">
                        <label class="fs-label" for="balance_import_file"><i class="fas fa-file-excel"></i> {{ __('app.finance.file_excel') }}</label>
                        <input type="file" name="file" id="balance_import_file" class="fs-control" accept=".xlsx,.xls,.csv" required>
                        <div class="fs-helper-text">
                            {{ __('app.finance.balance_sheet_template_help') }}
                        </div>
                    </div>
                    <div class="fs-field">
                        <label class="fs-label" for="balance_import_category_id"><i class="fas fa-tags"></i> {{ __('app.finance.finance_category') }}</label>
                        <select name="category_id" id="balance_import_category_id" class="fs-control" required>
                            <option value="">{{ __('app.finance.choose_finance_category') }}</option>
                            @foreach($financeCategoryOptions as $category)
                                <option value="{{ $category->id }}" {{ (string) $selectedFinanceCategoryId === (string) $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="fs-field">
                        <label class="fs-label" for="balance_import_batch_name"><i class="fas fa-tag"></i> {{ __('app.finance.batch_name') }}</label>
                        <input type="text" name="batch_name" id="balance_import_batch_name" class="fs-control" value="{{ old('batch_name') }}">
                    </div>
                    <div class="fs-field">
                        <label class="fs-label" for="balance_import_notes"><i class="fas fa-sticky-note"></i> {{ __('app.finance.notes') }}</label>
                        <input type="text" name="notes" id="balance_import_notes" class="fs-control" value="{{ old('notes') }}">
                    </div>
                    <div class="fs-field">
                        <label class="fs-label" for="balance_import_audit_status"><i class="fas fa-check-double"></i> Status Audit</label>
                        <select name="audit_status" id="balance_import_audit_status" class="fs-control">
                            <option value="UNAUDITED">UNAUDITED</option>
                            <option value="PRE_AUDIT">PRE-AUDIT</option>
                            <option value="AUDITED">AUDITED</option>
                        </select>
                    </div>
                    <div class="fs-field full fs-manage-actions">
                        <a href="{{ route('finance.report.balance-sheet.template.download') }}" class="fs-btn fs-btn-muted">
                            <i class="fas fa-download"></i> {{ __('app.finance.download_balance_sheet_template') }}
                        </a>
                        <button type="submit" class="fs-btn fs-btn-primary">
                            <i class="fas fa-upload"></i> {{ __('app.finance.import_excel') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="fs-section-card">
            <div class="fs-section-head">
                <div class="fs-section-title">
                    <span class="fs-section-icon"><i class="fas fa-pen"></i></span>
                    <span>{{ $editImportedRow ? __('app.finance.edit_balance_sheet_row') : __('app.finance.add_balance_sheet_row') }}</span>
                </div>
            </div>
            <div class="p-3">
                <form method="POST" action="{{ $editImportedRow ? route('finance.report.balance-sheet.rows.update', $editImportedRow['id']) : route('finance.report.balance-sheet.rows.store') }}" class="fs-manage-form">
                    @csrf
                    @if($editImportedRow)
                        @method('PUT')
                    @endif
                    <input type="hidden" name="statement_type" value="BALANCE_SHEET">
                    <input type="hidden" name="batch_id" value="{{ old('batch_id', $balanceRowForm['batch_id'] ?? $selectedBatchId) }}">
                    <input type="hidden" name="period_type" value="{{ data_get($filters, 'period_type', 'ALL') }}">
                    <input type="hidden" name="start_date" value="{{ data_get($filters, 'start_date') }}">
                    <input type="hidden" name="end_date" value="{{ data_get($filters, 'end_date') }}">
                    <input type="hidden" name="start_month" value="{{ data_get($filters, 'start_month') }}">
                    <input type="hidden" name="end_month" value="{{ data_get($filters, 'end_month') }}">
                    <input type="hidden" name="start_year" value="{{ data_get($filters, 'start_year') }}">
                    <input type="hidden" name="end_year" value="{{ data_get($filters, 'end_year') }}">
                    <div class="fs-field">
                        <label class="fs-label" for="balance_row_category_id"><i class="fas fa-tags"></i> {{ __('app.finance.finance_category') }}</label>
                        <select name="category_id" id="balance_row_category_id" class="fs-control" required>
                            <option value="">{{ __('app.finance.choose_finance_category') }}</option>
                            @foreach($financeCategoryOptions as $category)
                                <option value="{{ $category->id }}" {{ (string) $selectedFinanceCategoryId === (string) $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="fs-field">
                        <label class="fs-label" for="balance_section_key"><i class="fas fa-folder-tree"></i> {{ __('app.finance.category') }}</label>
                        <select name="section_key" id="balance_section_key" class="fs-control" required>
                            @foreach(['liabilitas' => __('app.finance.liabilities'), 'piutang' => __('app.finance.receivables'), 'kas' => __('app.finance.cash'), 'aset' => __('app.finance.assets'), 'other' => __('app.finance.other')] as $sectionKey => $sectionLabel)
                                <option value="{{ $sectionKey }}" {{ old('section_key', $balanceRowForm['section_key']) === $sectionKey ? 'selected' : '' }}>{{ $sectionLabel }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="fs-field">
                        <label class="fs-label" for="balance_group_label"><i class="fas fa-layer-group"></i> {{ __('app.finance.group') }}</label>
                        <input type="text" name="group_label" id="balance_group_label" class="fs-control" value="{{ old('group_label', $balanceRowForm['group_label']) }}">
                    </div>
                    <div class="fs-field">
                        <label class="fs-label" for="balance_account_code"><i class="fas fa-hashtag"></i> {{ __('app.finance.account_code') }}</label>
                        <input type="text" name="account_code" id="balance_account_code" class="fs-control" value="{{ old('account_code', $balanceRowForm['account_code']) }}">
                    </div>
                    <div class="fs-field">
                        <label class="fs-label" for="balance_finance_type"><i class="fas fa-tag"></i> {{ __('app.finance.finance_type') }}</label>
                        <input type="text" name="finance_type" id="balance_finance_type" class="fs-control" value="{{ old('finance_type', $balanceRowForm['finance_type']) }}">
                    </div>
                    <div class="fs-field full">
                        <label class="fs-label" for="balance_account_name"><i class="fas fa-font"></i> {{ __('app.finance.row_name') }}</label>
                        <input type="text" name="account_name" id="balance_account_name" class="fs-control" value="{{ old('account_name', $balanceRowForm['account_name']) }}" required>
                    </div>
                    <div class="fs-field">
                        <label class="fs-label" for="balance_amount"><i class="fas fa-money-bill-wave"></i> {{ __('app.finance.amount') }}</label>
                        <input type="number" step="0.01" name="amount" id="balance_amount" class="fs-control" value="{{ old('amount', $balanceRowForm['amount']) }}" required>
                    </div>
                    <div class="fs-field full fs-manage-actions">
                        <button type="submit" class="fs-btn fs-btn-primary">
                            <i class="fas fa-save"></i> {{ $editImportedRow ? __('app.finance.update_row') : __('app.finance.add_row') }}
                        </button>
                        @if($editImportedRow)
                            <a href="{{ route($pageRouteName, array_filter(array_merge($filterQuery ?? [], ['statement_data_source' => 'imported', 'statement_batch_id' => $selectedBatchId]), static fn ($value): bool => $value !== null && $value !== '')) }}" class="fs-btn fs-btn-muted">
                                <i class="fas fa-times"></i> {{ __('app.finance.cancel_edit') }}
                            </a>
                        @endif
                    </div>
                </form>
            </div>
        </div>
    </div>

    @if(!empty($importedRows))
        <div class="fs-section-card mt-3">
            <div class="fs-section-head d-flex justify-content-between align-items-center flex-wrap" style="gap:.8rem;">
                <div class="fs-section-title">
                    <span class="fs-section-icon"><i class="fas fa-list"></i></span>
                    <span>{{ __('app.finance.imported_manual_rows') }}</span>
                </div>
                <span class="fs-pill">{{ number_format(count($importedRows), 0, ',', '.') }} {{ __('app.finance.rows') }}</span>
            </div>
            <form
                id="balance-bulk-category-form"
                method="POST"
                action="{{ route('finance.report.balance-sheet.rows.category') }}"
                class="finance-bulk-category-form js-bulk-category-form"
                data-checkbox-selector=".js-balance-row-checkbox"
            >
                @csrf
                @method('PATCH')
                <div class="bulk-field">
                    <label class="fs-label" for="balance_bulk_category_id">
                        <i class="fas fa-tags"></i> {{ __('app.finance.bulk_category_target') }}
                    </label>
                    <select name="category_id" id="balance_bulk_category_id" class="fs-control" required>
                        <option value="">{{ __('app.finance.choose_finance_category') }}</option>
                        @foreach($financeCategoryOptions as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="fs-btn fs-btn-primary">
                    <i class="fas fa-layer-group"></i> {{ __('app.finance.bulk_category_apply') }}
                </button>
                <div class="bulk-help">{{ __('app.finance.bulk_category_help') }}</div>
            </form>
            <div class="fs-table-wrap">
                <table class="fs-table fs-manage-table">
                    <thead>
                        <tr>
                            <th style="width:52px; text-align:center;">
                                <input
                                    type="checkbox"
                                    class="finance-bulk-checkbox js-bulk-check-all"
                                    data-checkbox-selector=".js-balance-row-checkbox"
                                    aria-label="{{ __('app.finance.select_all_rows') }}"
                                >
                            </th>
                            <th style="width:120px;">{{ __('app.finance.category') }}</th>
                            <th style="width:140px;">{{ __('app.finance.code') }}</th>
                            <th>{{ __('app.finance.name') }}</th>
                            <th style="width:180px;">{{ __('app.finance.group') }}</th>
                            <th style="width:170px; text-align:right;">{{ __('app.finance.amount') }}</th>
                            <th style="width:170px;">{{ __('app.finance.action') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($importedRows as $row)
                            <tr>
                                <td style="text-align:center;">
                                    <input
                                        type="checkbox"
                                        name="row_ids[]"
                                        value="{{ $row['id'] }}"
                                        form="balance-bulk-category-form"
                                        class="finance-bulk-checkbox js-bulk-row-checkbox js-balance-row-checkbox"
                                        data-checkbox-group=".js-balance-row-checkbox"
                                        aria-label="{{ __('app.finance.select_row') }}"
                                    >
                                </td>
                                <td>{{ $row['section_label'] ?? '-' }}</td>
                                <td><strong>{{ $row['account_code'] ?? '-' }}</strong></td>
                                <td>
                                    <div>{{ $row['account_name'] }}</div>
                                    @if(!empty($row['is_manual']))
                                        <div class="fs-soft-copy">{{ __('app.finance.manual_input') }}</div>
                                    @endif
                                </td>
                                <td>{{ $row['group_label'] ?? '-' }}</td>
                                <td class="fs-amount">Rp {{ number_format((float) ($row['amount'] ?? 0), 2, ',', '.') }}</td>
                                <td>
                                    <div class="fs-manage-actions">
                                        <a href="{{ route($pageRouteName, array_filter(array_merge($filterQuery ?? [], ['statement_data_source' => 'imported', 'statement_batch_id' => $selectedBatchId, 'edit_row' => $row['id']]), static fn ($value): bool => $value !== null && $value !== '')) }}" class="fs-inline-link">
                                            <i class="fas fa-pen"></i> {{ __('app.finance.edit') }}
                                        </a>
                                        <form method="POST" action="{{ route('finance.report.balance-sheet.rows.destroy', $row['id']) }}" onsubmit="return confirm(@json(__('app.finance.delete_balance_sheet_row_confirm')))">
                                            @csrf
                                            @method('DELETE')
                                            <input type="hidden" name="statement_data_source" value="imported">
                                            <input type="hidden" name="statement_batch_id" value="{{ $selectedBatchId }}">
                                            <input type="hidden" name="period_type" value="{{ data_get($filters, 'period_type', 'ALL') }}">
                                            <input type="hidden" name="return_to" value="manage">
                                            <button type="submit" class="fs-inline-link" style="color:var(--fs-red); border-color:rgba(239,68,68,.2);">
                                                <i class="fas fa-trash"></i> {{ __('app.finance.delete') }}
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
@endif

<div class="fs-summary-grid">
    <div class="fs-summary-card">
        <div class="fs-summary-label"><i class="fas fa-landmark"></i> {{ __('app.finance.liabilities') }}</div>
        <div class="fs-summary-value">Rp {{ number_format((float) ($summary['liabilitas_total'] ?? 0), 2, ',', '.') }}</div>
        <div class="fs-summary-help">{{ __('app.finance.liabilities_help') }}</div>
    </div>
    <div class="fs-summary-card">
        <div class="fs-summary-label"><i class="fas fa-file-invoice-dollar"></i> {{ __('app.finance.receivables') }}</div>
        <div class="fs-summary-value">Rp {{ number_format((float) ($summary['piutang_total'] ?? 0), 2, ',', '.') }}</div>
        <div class="fs-summary-help">{{ __('app.finance.receivables_help') }}</div>
    </div>
    <div class="fs-summary-card">
        <div class="fs-summary-label"><i class="fas fa-wallet"></i> {{ __('app.finance.cash') }}</div>
        <div class="fs-summary-value">Rp {{ number_format((float) ($summary['kas_total'] ?? 0), 2, ',', '.') }}</div>
        <div class="fs-summary-help">{{ __('app.finance.cash_help') }}</div>
    </div>
    <div class="fs-summary-card">
        <div class="fs-summary-label"><i class="fas fa-building"></i> {{ __('app.finance.assets') }}</div>
        <div class="fs-summary-value">Rp {{ number_format((float) ($summary['aset_total'] ?? 0), 2, ',', '.') }}</div>
        <div class="fs-summary-help">{{ __('app.finance.assets_help') }}</div>
    </div>
    <div class="fs-summary-card">
        <div class="fs-summary-label"><i class="fas fa-layer-group"></i> {{ __('app.finance.asset_side_total') }}</div>
        <div class="fs-summary-value">Rp {{ number_format((float) ($summary['asset_side_total'] ?? 0), 2, ',', '.') }}</div>
        <div class="fs-summary-help">{{ __('app.finance.asset_side_total_help', ['count' => number_format((int) ($summary['account_count'] ?? 0), 0, ',', '.')]) }}</div>
    </div>
</div>

@if($uncategorizedCount > 0)
    <div class="fs-note-card">
        <i class="fas fa-exclamation-triangle"></i>
        <div class="fs-note-copy">
            <strong>{{ __('app.finance.uncategorized_balance_sheet_title', ['count' => number_format($uncategorizedCount, 0, ',', '.')]) }}</strong>
            <span>
                @if($isImportedSource)
                    {!! __('app.finance.uncategorized_imported_note', ['mode' => '<strong>'.e(__('app.finance.import_edit_manual')).'</strong>']) !!}
                @elseif($isCombinedSource)
                    {!! __('app.finance.uncategorized_combined_note', ['mode' => '<strong>'.e(__('app.finance.import_edit_manual')).'</strong>']) !!}
                @else
                    {{ __('app.finance.uncategorized_system_note') }}
                @endif
            </span>
            <div class="fs-note-breakdown">
                @if(($uncategorizedSummary['profit_loss_count'] ?? 0) > 0)
                    <span class="fs-badge fs-blue">
                        <i class="fas fa-chart-line"></i>
                        {{ number_format((int) $uncategorizedSummary['profit_loss_count'], 0, ',', '.') }} {{ __('app.finance.already_in_profit_loss') }}
                    </span>
                @endif
                @if(($uncategorizedSummary['unmapped_count'] ?? 0) > 0)
                    <span class="fs-badge fs-amber">
                        <i class="fas fa-question-circle"></i>
                        {{ number_format((int) $uncategorizedSummary['unmapped_count'], 0, ',', '.') }} {{ __('app.finance.unmapped') }}
                    </span>
                @endif
                @if(($uncategorizedSummary['other_count'] ?? 0) > 0)
                    <span class="fs-badge fs-danger">
                        <i class="fas fa-layer-group"></i>
                        {{ number_format((int) $uncategorizedSummary['other_count'], 0, ',', '.') }} {{ __('app.finance.outside_reports') }}
                    </span>
                @endif
            </div>
        </div>
    </div>
@endif

@if(!empty($uncategorizedRows))
    <div class="fs-section-card fs-uncat-card">
        <div class="fs-section-head d-flex justify-content-between align-items-center flex-wrap" style="gap:.8rem;">
            <div class="fs-section-title">
                <span class="fs-section-icon"><i class="fas fa-map-signs"></i></span>
                <span>{{ __('app.finance.uncategorized_accounts_title') }}</span>
            </div>
            <div class="fs-section-total">
                <span class="fs-badge fs-amber">
                    <i class="fas fa-list"></i>
                    {{ number_format(count($uncategorizedRows), 0, ',', '.') }} {{ __('app.finance.accounts') }}
                </span>
            </div>
        </div>
        <div class="fs-table-wrap">
            <table class="fs-table">
                <thead>
                    <tr>
                        <th style="width:130px;">{{ __('app.finance.code') }}</th>
                        <th>{{ __('app.finance.account_name') }}</th>
                        <th style="width:210px;">{{ __('app.finance.current_position') }}</th>
                        <th style="width:100px; text-align:center;">{{ $isImportedSource || $isCombinedSource ? __('app.finance.source') : __('app.finance.item') }}</th>
                        <th style="width:160px; text-align:right;">{{ $isImportedSource ? __('app.finance.amount') : ($isCombinedSource ? __('app.finance.debit') . ' / ' . __('app.finance.amount') : __('app.finance.debit')) }}</th>
                        <th style="width:160px; text-align:right;">{{ $isImportedSource ? __('app.finance.status') : ($isCombinedSource ? __('app.finance.credit') . ' / ' . __('app.finance.status') : __('app.finance.credit')) }}</th>
                        <th style="width:360px;">{{ $isImportedSource || $isCombinedSource ? __('app.finance.action') : __('app.finance.choose_category') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($uncategorizedRows as $row)
                        @php
                            $rowHasJournalSource = (bool) ($row['has_journal_source'] ?? !$isImportedSource);
                            $rowHasImportedSource = (bool) ($row['has_imported_source'] ?? $isImportedSource);
                            $journalItemsRoute = route('finance.report.journal-items', array_merge($baseFilterQuery, [
                                'account_code' => $row['account_code'],
                                'statement_source' => 'balance_sheet',
                            ]));
                            $generalLedgerRoute = route('finance.report.general-ledger', array_merge($baseFilterQuery, [
                                'account_code' => $row['account_code'],
                            ]));
                            $manageUncategorizedRoute = route($manageStatementRouteName, array_filter(array_merge($baseFilterQuery ?? [], [
                                'statement_data_source' => 'imported',
                                'statement_batch_id' => $row['imported_batch_id'] ?? $selectedBatchId,
                                'account_code' => $row['account_code'] ?? null,
                                'edit_row' => $isImportedSource ? ($row['id'] ?? null) : null,
                            ]), static fn ($value): bool => $value !== null && $value !== ''));
                            $statusBadgeClass = match ($row['summary_key'] ?? '') {
                                'profit_loss_count' => 'fs-blue',
                                'other_count' => 'fs-danger',
                                default => 'fs-amber',
                            };
                        @endphp
                        <tr>
                            <td><strong>{{ $row['account_code'] }}</strong></td>
                            <td>
                                <div class="fs-account-name">{{ $row['account_name'] }}</div>
                                <div class="fs-map-help">
                                    {{ __('app.finance.current_type') }}: {{ $row['finance_type'] !== '' ? str_replace('_', ' ', $row['finance_type']) : __('app.finance.no_account_type') }}
                                </div>
                            </td>
                            <td>
                                <div class="fs-uncat-status">
                                    <strong>
                                        <span class="fs-badge {{ $statusBadgeClass }}">
                                            <i class="fas fa-tag"></i>
                                            {{ $row['current_statement'] }}
                                        </span>
                                    </strong>
                                    <span>{{ $row['current_section'] }}. {{ $row['reason'] }}</span>
                                </div>
                            </td>
                            <td style="text-align:center;">
                                @if($rowHasJournalSource)
                                    <div style="font-weight:800;">{{ number_format((int) ($row['entry_count'] ?? 0), 0, ',', '.') }}</div>
                                    <div class="fs-uncat-actions mt-2">
                                        @if($rowHasImportedSource)
                                            <span class="fs-pill">
                                                <i class="fas fa-file-import"></i> {{ __('app.finance.import') }}
                                            </span>
                                        @endif
                                        <a href="{{ $journalItemsRoute }}" class="fs-inline-link">
                                            <i class="fas fa-table"></i> {{ __('app.finance.item') }}
                                        </a>
                                        <a href="{{ $generalLedgerRoute }}" class="fs-inline-link">
                                            <i class="fas fa-book-open"></i> {{ __('app.finance.general_ledger') }}
                                        </a>
                                    </div>
                                @else
                                    <span class="fs-pill">
                                        <i class="fas fa-file-import"></i> {{ __('app.finance.import') }}
                                    </span>
                                @endif
                            </td>
                            <td class="fs-amount">
                                Rp {{ number_format((float) ($rowHasJournalSource ? ($row['total_debit'] ?? 0) : ($row['amount'] ?? 0)), 2, ',', '.') }}
                            </td>
                            <td class="fs-amount">
                                @if($rowHasJournalSource)
                                    Rp {{ number_format((float) ($row['total_credit'] ?? 0), 2, ',', '.') }}
                                @else
                                    {{ !empty($row['is_manual']) ? __('app.finance.manual') : __('app.finance.batch') }}
                                @endif
                            </td>
                            <td>
                                @if($rowHasImportedSource && !$rowHasJournalSource)
                                    <div class="fs-manage-actions">
                                        <a href="{{ $manageUncategorizedRoute }}" class="fs-inline-link">
                                            <i class="fas fa-pen"></i> {{ __('app.finance.manage_row') }}
                                        </a>
                                    </div>
                                @elseif($canManageStatementMapping && $rowHasJournalSource)
                                    @if($rowHasImportedSource)
                                        <div class="fs-uncat-actions mb-2">
                                            <a href="{{ $manageUncategorizedRoute }}" class="fs-inline-link">
                                                <i class="fas fa-file-import"></i> {{ __('app.finance.manage_import') }}
                                            </a>
                                        </div>
                                    @endif
                                    <form method="POST" action="{{ route('finance.report.account-mapping') }}" class="fs-map-form">
                                        @csrf
                                        <input type="hidden" name="account_code" value="{{ $row['account_code'] }}">
                                        <input type="hidden" name="account_name" value="{{ $row['account_name'] }}">
                                        <select name="statement_type" class="fs-control" required>
                                            <option value="">{{ __('app.finance.select_target_category_placeholder') }}</option>
                                            @foreach($statementTypeOptions as $groupLabel => $options)
                                                <optgroup label="{{ $groupLabel }}">
                                                    @foreach($options as $type => $optionLabel)
                                                        <option value="{{ $type }}" {{ ($row['finance_type'] ?? '') === $type ? 'selected' : '' }}>
                                                            {{ $optionLabel }}
                                                        </option>
                                                    @endforeach
                                                </optgroup>
                                            @endforeach
                                        </select>
                                        <button type="submit" class="fs-btn fs-btn-primary">
                                            <i class="fas fa-save"></i> {{ __('app.finance.save') }}
                                        </button>
                                    </form>
                                @else
                                    <div class="fs-map-help">
                                        {{ $rowHasImportedSource && !$rowHasJournalSource
                                            ? __('app.finance.import_row_edit_help', ['mode' => __('app.finance.import_edit_manual')])
                                            : __('app.finance.journal_detail_manage_help') }}
                                    </div>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endif

@if($hasRows)
    <div class="fs-section-grid">
        @foreach($sections as $section)
            @php
                $meta = $sectionMeta[$section['key']] ?? ['icon' => 'fa-book', 'badge' => 'fs-blue'];
            @endphp
            <div class="fs-section-card">
                <div class="fs-section-head d-flex justify-content-between align-items-center flex-wrap" style="gap:.8rem;">
                    <div class="fs-section-title">
                        <span class="fs-section-icon"><i class="fas {{ $meta['icon'] }}"></i></span>
                        <span>{{ $section['label'] }}</span>
                    </div>
                    <div class="fs-section-total">
                        <span class="fs-badge {{ $meta['badge'] }}">
                            <i class="fas fa-coins"></i>
                            Rp {{ number_format((float) ($section['total'] ?? 0), 2, ',', '.') }}
                        </span>
                    </div>
                </div>
                <div class="fs-table-wrap">
                    <table class="fs-table">
                        <thead>
                            <tr>
                                <th style="width:130px;">{{ __('app.finance.code') }}</th>
                                <th>{{ __('app.finance.account_name') }}</th>
                                <th style="width:160px;">{{ __('app.finance.type') }}</th>
                                <th style="width:170px; text-align:right;">{{ __('app.finance.ending_balance') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($section['rows'] as $row)
                                @php
                                    $rowHasJournalSource = (bool) ($row['has_journal_source'] ?? !$isImportedSource);
                                    $rowHasImportedSource = (bool) ($row['has_imported_source'] ?? $isImportedSource);
                                    $canOpenJournalDetail = $rowHasJournalSource
                                        && !empty($row['account_code'])
                                        && $row['account_code'] !== '-';
                                    $journalItemsRoute = route('finance.report.journal-items', array_merge($baseFilterQuery, [
                                        'account_code' => $row['account_code'],
                                        'statement_source' => 'balance_sheet',
                                    ]));
                                    $manageRowRoute = route($manageStatementRouteName, array_filter(array_merge($baseFilterQuery ?? [], [
                                        'statement_data_source' => 'imported',
                                        'statement_batch_id' => $row['imported_batch_id'] ?? $selectedBatchId,
                                        'account_code' => $row['account_code'] ?? null,
                                        'edit_row' => $isImportedSource ? ($row['id'] ?? null) : null,
                                    ]), static fn ($value): bool => $value !== null && $value !== ''));
                                @endphp
                                <tr>
                                    <td>
                                        @if($canOpenJournalDetail)
                                            <a href="{{ $journalItemsRoute }}" class="fs-account-link">
                                                <strong>{{ $row['account_code'] }}</strong>
                                            </a>
                                        @else
                                            <strong>{{ $row['account_code'] }}</strong>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="fs-account-cell">
                                            @if($canOpenJournalDetail)
                                                <a href="{{ $journalItemsRoute }}" class="fs-account-name fs-account-link">{{ $row['account_name'] }}</a>
                                            @else
                                                <div class="fs-account-name">{{ $row['account_name'] }}</div>
                                            @endif
                                            @if(!empty($row['group_label']))
                                                <div class="fs-soft-copy">{{ $row['group_label'] }}</div>
                                            @endif
                                            <div class="dropdown">
                                                <button type="button" class="fs-row-menu-btn dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                    <i class="fas fa-ellipsis-v"></i>
                                                </button>
                                                <div class="dropdown-menu dropdown-menu-right fs-row-menu">
                                                    @if($canOpenJournalDetail)
                                                        <a class="dropdown-item" href="{{ $journalItemsRoute }}">
                                                            <i class="fas fa-table"></i> {{ __('app.finance.journal_items') }}
                                                        </a>
                                                        <a class="dropdown-item" href="{{ route('finance.report.general-ledger', array_merge($baseFilterQuery, ['account_code' => $row['account_code']])) }}">
                                                            <i class="fas fa-book-open"></i> {{ __('app.finance.general_ledger') }}
                                                        </a>
                                                    @endif
                                                    @if($rowHasImportedSource)
                                                        <a class="dropdown-item" href="{{ $manageRowRoute }}">
                                                            <i class="fas fa-pen"></i> {{ $isImportedSource ? __('app.finance.edit_row') : __('app.finance.manage_import') }}
                                                        </a>
                                                        @if($canManageStatementMapping && !empty($row['id']))
                                                            <form method="POST" action="{{ route('finance.report.balance-sheet.rows.destroy', $row['id']) }}" onsubmit="return confirm(@json(__('app.finance.delete_balance_sheet_import_row_confirm')))" style="margin:0;">
                                                                @csrf
                                                                @method('DELETE')
                                                                <input type="hidden" name="statement_data_source" value="{{ $statementDataSource }}">
                                                                <input type="hidden" name="statement_batch_id" value="{{ $row['imported_batch_id'] ?? $selectedBatchId }}">
                                                                @if($isImportedSource)
                                                                    <input type="hidden" name="period_type" value="{{ data_get($filters, 'period_type', 'ALL') }}">
                                                                @endif
                                                                <input type="hidden" name="return_to" value="main">
                                                                <button type="submit" class="dropdown-item" style="color:var(--fs-red); background:none; border:none; width:100%; text-align:left;">
                                                                    <i class="fas fa-trash"></i> {{ $rowHasJournalSource ? __('app.finance.delete_import_row') : __('app.finance.delete_row') }}
                                                                </button>
                                                            </form>
                                                        @endif
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="fs-badge {{ $meta['badge'] }}">
                                            <i class="fas fa-tag"></i>
                                            {{ $row['finance_type'] !== '' ? str_replace('_', ' ', $row['finance_type']) : strtoupper($section['label']) }}
                                        </span>
                                    </td>
                                    <td class="fs-amount">
                                        @if($canOpenJournalDetail)
                                            <a href="{{ $journalItemsRoute }}" class="fs-amount-link">
                                                Rp {{ number_format((float) $row['balance'], 2, ',', '.') }}
                                            </a>
                                        @else
                                            Rp {{ number_format((float) $row['balance'], 2, ',', '.') }}
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" style="text-align:center; color:var(--fs-muted);">{{ __('app.finance.no_category_data') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @endforeach
    </div>
@else
    <div class="fs-empty-card">
        <i class="fas fa-inbox"></i>
        <h4>{{ __('app.finance.no_balance_sheet_data') }}</h4>
        <div>{!! __('app.finance.no_balance_sheet_data_note', ['status' => '<strong>'.e(__('app.finance.posted')).'</strong>']) !!}</div>
    </div>
@endif
@include('finance.partials.bulk-category-tools')
@endsection
