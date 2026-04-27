@extends('layouts.app')

@section('content')
@php
    $incomeRows = $report['income_rows'] ?? [];
    $expenseRows = $report['expense_rows'] ?? [];
    $totals = $report['totals'] ?? ['income' => 0, 'expense' => 0, 'net_result' => 0];
    $hasRows = count($incomeRows) > 0 || count($expenseRows) > 0;
    $baseFilterQuery = $baseFilterQuery ?? ($filterQuery ?? []);
    $isManageMode = $isManageMode ?? false;
    $pageRouteName = $pageRouteName ?? 'finance.report.profit-loss';
    $mainStatementRouteName = $mainStatementRouteName ?? 'finance.report.profit-loss';
    $manageStatementRouteName = $manageStatementRouteName ?? 'finance.report.profit-loss.manage';
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
    $pageSubtitle = $isManageMode
        ? 'Kelola import Excel dan edit manual laba rugi.'
        : ($isCombinedSource
            ? 'Hasil import dan data jurnal tampil dalam satu laporan laba rugi untuk periode ' . $periodLabel . '.'
            : ($isImportedSource
            ? 'Hasil import laba rugi tampil langsung di halaman utama.'
            : 'Ringkasan pemasukan dan pengeluaran pada periode ' . $periodLabel . '.'));
@endphp

<style>
    @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');

    :root {
        --pl-blue: #1d4ed8;
        --pl-blue-dark: #1e3a8a;
        --pl-green: #059669;
        --pl-green-soft: rgba(16, 185, 129, 0.12);
        --pl-red: #dc2626;
        --pl-red-soft: rgba(239, 68, 68, 0.12);
        --pl-amber: #d97706;
        --pl-amber-soft: rgba(245, 158, 11, 0.12);
        --pl-bg: #f0f4fd;
        --pl-card: #ffffff;
        --pl-text: #0f172a;
        --pl-muted: #64748b;
        --pl-border: rgba(37, 99, 235, 0.10);
        --pl-shadow: 0 10px 32px rgba(15, 23, 42, 0.08), 0 4px 14px rgba(37, 99, 235, 0.06);
        --pl-radius: 18px;
        --pl-radius-sm: 12px;
    }

    body, .content-wrapper {
        background: var(--pl-bg) !important;
        font-family: 'Plus Jakarta Sans', sans-serif !important;
    }

    .pl-page-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        flex-wrap: wrap;
        margin-bottom: 1.5rem;
    }
    .pl-page-title {
        display: flex;
        align-items: center;
        gap: 0.9rem;
    }
    .pl-title-icon {
        width: 50px;
        height: 50px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, var(--pl-blue), var(--pl-blue-dark));
        color: #fff;
        font-size: 1.2rem;
        box-shadow: var(--pl-shadow);
    }
    .pl-page-title h1 {
        margin: 0;
        color: var(--pl-text);
        font-size: 1.4rem;
        font-weight: 800;
    }
    .pl-page-title p {
        margin: 0.15rem 0 0;
        color: var(--pl-muted);
        font-size: 0.82rem;
        font-weight: 500;
    }
    .pl-nav {
        display: flex;
        flex-wrap: wrap;
        gap: 0.55rem;
    }
    .pl-nav-link,
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
    .pl-nav-link:hover,
    .fs-btn:hover {
        text-decoration: none;
        transform: translateY(-1px);
    }
    .pl-nav-link.primary,
    .fs-btn-primary {
        background: linear-gradient(135deg, var(--pl-blue), #2563eb);
        color: #fff;
        box-shadow: 0 8px 22px rgba(37, 99, 235, 0.24);
    }
    .pl-nav-link.muted,
    .fs-btn-muted {
        background: #fff;
        color: var(--pl-muted);
        border-color: var(--pl-border);
    }

    .fs-filter-card,
    .pl-summary-card,
    .pl-section-card,
    .pl-empty-card {
        background: var(--pl-card);
        border: 1px solid var(--pl-border);
        border-radius: var(--pl-radius);
        box-shadow: var(--pl-shadow);
    }
    .fs-filter-head,
    .pl-section-head {
        padding: 1rem 1.2rem;
        border-bottom: 1px solid var(--pl-border);
    }
    .fs-filter-head {
        background: linear-gradient(135deg, var(--pl-blue-dark), var(--pl-blue));
        border-bottom: none;
        border-radius: var(--pl-radius) var(--pl-radius) 0 0;
    }
    .fs-filter-title,
    .pl-section-title {
        display: flex;
        align-items: center;
        gap: 0.65rem;
        font-size: 0.9rem;
        font-weight: 700;
    }
    .fs-filter-title { color: #fff; }
    .pl-section-title { color: var(--pl-text); }
    .fs-filter-icon,
    .pl-section-icon {
        width: 30px;
        height: 30px;
        border-radius: 10px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }
    .fs-filter-icon { background: rgba(255, 255, 255, 0.16); }
    .pl-section-icon { background: rgba(37, 99, 235, 0.08); color: var(--pl-blue); }
    .fs-filter-body {
        padding: 1.1rem 1.2rem 0.3rem;
    }
    .fs-field { margin-bottom: 1rem; }
    .fs-label {
        display: flex;
        align-items: center;
        gap: 0.35rem;
        margin-bottom: 0.4rem;
        font-size: 0.7rem;
        font-weight: 800;
        letter-spacing: 0.06em;
        text-transform: uppercase;
        color: var(--pl-muted);
    }
    .fs-control {
        width: 100%;
        border: 1.5px solid rgba(148, 163, 184, 0.18);
        border-radius: var(--pl-radius-sm);
        padding: 0.65rem 0.85rem;
        font-size: 0.84rem;
        color: var(--pl-text);
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

    .pl-summary-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 0.9rem;
        margin: 1.2rem 0;
    }
    .pl-summary-card {
        padding: 1rem 1.1rem;
    }
    .pl-summary-label {
        display: flex;
        align-items: center;
        gap: 0.45rem;
        color: var(--pl-muted);
        font-size: 0.72rem;
        font-weight: 800;
        letter-spacing: 0.06em;
        text-transform: uppercase;
        margin-bottom: 0.45rem;
    }
    .pl-summary-value {
        color: var(--pl-text);
        font-size: 1.3rem;
        font-weight: 800;
        line-height: 1.15;
    }
    .pl-summary-value.positive { color: var(--pl-green); }
    .pl-summary-value.negative { color: var(--pl-red); }
    .pl-summary-help {
        margin-top: 0.35rem;
        color: var(--pl-muted);
        font-size: 0.75rem;
        font-weight: 500;
    }

    .pl-section-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(360px, 1fr));
        gap: 1rem;
    }
    .pl-table {
        width: 100%;
        border-collapse: collapse;
    }
    .pl-table th {
        background: #f8fbff;
        color: var(--pl-muted);
        font-size: 0.68rem;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        padding: 0.75rem 1rem;
        border-bottom: 1px solid var(--pl-border);
    }
    .pl-table td {
        padding: 0.78rem 1rem;
        font-size: 0.82rem;
        color: #334155;
        border-bottom: 1px solid rgba(148, 163, 184, 0.12);
    }
    .pl-table tbody tr:last-child td { border-bottom: none; }
    .pl-table tbody tr:hover td { background: rgba(37, 99, 235, 0.03); }
    .pl-amount {
        text-align: right;
        white-space: nowrap;
        font-weight: 800;
    }
    .pl-amount.income { color: var(--pl-green); }
    .pl-amount.expense { color: var(--pl-red); }
    .pl-total-row td {
        font-weight: 800;
        background: rgba(37, 99, 235, 0.04);
    }
    .pl-account-cell {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
    }
    .pl-account-name {
        min-width: 0;
        flex: 1 1 auto;
    }
    .pl-account-link,
    .pl-amount-link {
        color: inherit;
        text-decoration: none;
    }
    .pl-account-link:hover,
    .pl-amount-link:hover {
        color: var(--pl-blue);
        text-decoration: none;
    }
    .pl-amount-link {
        display: inline-flex;
        justify-content: flex-end;
        width: 100%;
        font-weight: 800;
    }
    .pl-row-menu-btn {
        width: 30px;
        height: 30px;
        border: none;
        border-radius: 50%;
        background: transparent;
        color: var(--pl-muted);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.2s ease;
        flex-shrink: 0;
    }
    .pl-row-menu-btn::after { display: none; }
    .pl-row-menu-btn:hover {
        background: rgba(37, 99, 235, 0.08);
        color: var(--pl-blue);
    }
    .pl-row-menu {
        min-width: 180px;
        border-radius: 12px;
        border: 1px solid rgba(148, 163, 184, 0.18);
        box-shadow: 0 14px 30px rgba(15, 23, 42, 0.12);
        padding: 0.45rem;
    }
    .pl-row-menu .dropdown-item {
        border-radius: 8px;
        font-size: 0.78rem;
        font-weight: 700;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        color: var(--pl-text);
        padding: 0.55rem 0.7rem;
    }
    .pl-row-menu .dropdown-item:hover {
        background: rgba(37, 99, 235, 0.08);
        color: var(--pl-blue);
    }
    .pl-empty-card {
        margin-top: 1rem;
        padding: 2.5rem 1.2rem;
        text-align: center;
        color: var(--pl-muted);
    }
    .pl-empty-card i {
        font-size: 2.3rem;
        margin-bottom: 0.8rem;
        color: rgba(37, 99, 235, 0.28);
    }
    .pl-empty-card h4 {
        color: var(--pl-text);
        font-size: 1rem;
        font-weight: 700;
        margin-bottom: 0.35rem;
    }

    body.dark-mode,
    body.dark-mode .content-wrapper {
        background: var(--app-bg) !important;
    }
    body.dark-mode .fs-filter-card,
    body.dark-mode .pl-summary-card,
    body.dark-mode .pl-section-card,
    body.dark-mode .pl-empty-card {
        background: var(--app-surface) !important;
        border-color: var(--app-border) !important;
        box-shadow: var(--app-shadow) !important;
    }
    body.dark-mode .pl-page-title h1,
    body.dark-mode .pl-summary-value,
    body.dark-mode .pl-empty-card h4,
    body.dark-mode .pl-section-title {
        color: var(--app-text) !important;
    }
    body.dark-mode .pl-page-title p,
    body.dark-mode .fs-label,
    body.dark-mode .pl-summary-help,
    body.dark-mode .pl-summary-label,
    body.dark-mode .pl-table th {
        color: var(--app-text-muted) !important;
    }
    body.dark-mode .fs-control,
    body.dark-mode .pl-nav-link.muted,
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
    body.dark-mode .pl-table th {
        background: var(--app-surface-soft) !important;
        border-color: var(--app-border) !important;
    }
    body.dark-mode .pl-table td {
        color: var(--app-text-soft) !important;
        border-color: var(--app-border) !important;
    }
    body.dark-mode .pl-table tbody tr:hover td {
        background: var(--app-row-hover) !important;
    }
    body.dark-mode .pl-total-row td {
        background: var(--app-surface-soft) !important;
        color: var(--app-text) !important;
    }
    body.dark-mode .pl-row-menu {
        background: var(--app-surface) !important;
        border-color: var(--app-border) !important;
        box-shadow: var(--app-shadow) !important;
    }
    body.dark-mode .pl-row-menu .dropdown-item {
        color: var(--app-text-soft) !important;
    }
    body.dark-mode .pl-row-menu .dropdown-item:hover {
        background: var(--app-surface-soft) !important;
        color: var(--app-text) !important;
    }
    body.dark-mode .pl-account-link,
    body.dark-mode .pl-amount-link {
        color: inherit !important;
    }
    body.dark-mode .pl-account-link:hover,
    body.dark-mode .pl-amount-link:hover {
        color: var(--app-accent) !important;
    }
    .pl-source-card,
    .pl-manage-grid .pl-section-card {
        margin-top: 1rem;
    }
    .pl-source-switch,
    .pl-manage-actions {
        display: flex;
        gap: 0.55rem;
        flex-wrap: wrap;
        align-items: center;
    }
    .pl-manage-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 1rem;
        margin-top: 1rem;
    }
    .pl-manage-form {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 0.85rem 1rem;
    }
    .pl-manage-form .fs-field {
        margin-bottom: 0;
    }
    .pl-manage-form .fs-field.full {
        grid-column: 1 / -1;
    }
    .pl-switch-link {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        padding: 0.6rem 0.9rem;
        border-radius: 12px;
        border: 1px solid var(--pl-border);
        background: #fff;
        color: var(--pl-muted);
        font-size: 0.8rem;
        font-weight: 700;
        text-decoration: none;
    }
    .pl-switch-link.active {
        background: linear-gradient(135deg, var(--pl-blue), #2563eb);
        border-color: transparent;
        color: #fff;
        box-shadow: 0 10px 20px rgba(37, 99, 235, 0.16);
    }
    .pl-soft-copy {
        color: var(--pl-muted);
        font-size: 0.76rem;
        line-height: 1.5;
    }
    .pl-pill {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.28rem 0.6rem;
        border-radius: 999px;
        background: rgba(37, 99, 235, 0.08);
        color: var(--pl-blue);
        font-size: 0.72rem;
        font-weight: 700;
    }
    .pl-batch-meta {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
        gap: 0.7rem;
        margin-top: 0.9rem;
    }
    .pl-batch-stat {
        background: #f8fbff;
        border: 1px solid rgba(148, 163, 184, 0.14);
        border-radius: 12px;
        padding: 0.75rem 0.85rem;
    }
    .pl-batch-stat label {
        display: block;
        color: var(--pl-muted);
        font-size: 0.68rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        margin-bottom: 0.2rem;
    }
    .pl-batch-stat div {
        color: var(--pl-text);
        font-size: 0.82rem;
        font-weight: 700;
    }
    .pl-import-guide {
        padding: 0.95rem 1rem;
        border-radius: 16px;
        border: 1px dashed rgba(37, 99, 235, 0.2);
        background: linear-gradient(135deg, rgba(37, 99, 235, 0.05), rgba(14, 165, 233, 0.05));
        margin-bottom: 1rem;
    }
    .pl-import-guide-title {
        display: flex;
        align-items: center;
        gap: 0.55rem;
        color: var(--pl-blue-dark);
        font-size: 0.9rem;
        font-weight: 800;
        margin-bottom: 0.55rem;
    }
    .pl-import-guide-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 0.75rem;
        margin-top: 0.85rem;
    }
    .pl-import-guide-card {
        background: rgba(255, 255, 255, 0.82);
        border: 1px solid rgba(148, 163, 184, 0.14);
        border-radius: 14px;
        padding: 0.8rem 0.85rem;
    }
    .pl-import-guide-card label {
        display: block;
        color: var(--pl-blue-dark);
        font-size: 0.7rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        margin-bottom: 0.35rem;
    }
    .pl-import-guide-card div {
        color: var(--pl-text);
        font-size: 0.8rem;
        line-height: 1.55;
    }
    .pl-import-chip-row {
        display: flex;
        flex-wrap: wrap;
        gap: 0.45rem;
        margin-top: 0.75rem;
    }
    .pl-import-chip {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.35rem 0.65rem;
        border-radius: 999px;
        background: rgba(15, 23, 42, 0.04);
        border: 1px solid rgba(148, 163, 184, 0.14);
        color: var(--pl-text);
        font-size: 0.72rem;
        font-weight: 700;
    }
    body.dark-mode .pl-switch-link {
        background: var(--app-surface) !important;
        border-color: var(--app-border) !important;
        color: var(--app-text-soft) !important;
    }
    body.dark-mode .pl-switch-link.active {
        background: linear-gradient(135deg, var(--app-accent), #2563eb) !important;
        color: #fff !important;
        border-color: transparent !important;
    }
    body.dark-mode .pl-batch-stat,
    body.dark-mode .pl-import-guide-card,
    body.dark-mode .pl-import-chip {
        background: var(--app-surface-soft) !important;
        border-color: var(--app-border) !important;
        color: var(--app-text) !important;
    }
    body.dark-mode .pl-batch-stat label,
    body.dark-mode .pl-import-guide-card label {
        color: var(--app-text-muted) !important;
    }
    body.dark-mode .pl-batch-stat div,
    body.dark-mode .pl-import-guide-card div,
    body.dark-mode .pl-import-chip {
        color: var(--app-text) !important;
    }
    body.dark-mode .pl-import-guide {
        background: rgba(96, 165, 250, 0.08) !important;
        border-color: rgba(96, 165, 250, 0.18) !important;
    }
    body.dark-mode .pl-import-guide-title {
        color: var(--app-text) !important;
    }
    @media (max-width: 991px) {
        .pl-manage-form {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="pl-page-header">
    <div class="pl-page-title">
        <div class="pl-title-icon"><i class="fas fa-chart-area"></i></div>
        <div>
            <h1>Laporan Laba Rugi</h1>
            <p>{{ $pageSubtitle }}</p>
        </div>
    </div>

    <div class="pl-nav">
        <a href="{{ route('finance.dashboard') }}" class="pl-nav-link muted">
            <i class="fas fa-arrow-left"></i> Dashboard
        </a>
        @if($isManageMode)
            <a href="{{ route($mainStatementRouteName, array_filter(array_merge($filterQuery ?? [], ['statement_data_source' => $statementDataSource, 'statement_batch_id' => $selectedBatchId]), static fn ($value): bool => $value !== null && $value !== '')) }}" class="pl-nav-link muted">
                <i class="fas fa-table-columns"></i> Halaman Utama
            </a>
        @else
            <a href="{{ route($manageStatementRouteName, array_filter(array_merge($filterQuery ?? [], ['statement_data_source' => 'imported', 'statement_batch_id' => $selectedBatchId]), static fn ($value): bool => $value !== null && $value !== '')) }}" class="pl-nav-link muted">
                <i class="fas fa-sliders-h"></i> Import & Edit Manual
            </a>
        @endif
        <a href="{{ route('finance.report.profit-loss.download', array_merge($filterQuery, ['format' => 'excel'])) }}" class="pl-nav-link muted">
            <i class="fas fa-file-excel"></i> Download Excel
        </a>
        <a href="{{ route('finance.report.profit-loss.download', $filterQuery) }}" class="pl-nav-link primary">
            <i class="fas fa-file-pdf"></i> Download PDF
        </a>
        <a href="{{ route('finance.report.balance-sheet', $filterQuery) }}" class="pl-nav-link muted">
            <i class="fas fa-balance-scale"></i> Lembar Saldo
        </a>
        <a href="{{ route('finance.report.general-ledger', $filterQuery) }}" class="pl-nav-link muted">
            <i class="fas fa-book-open"></i> Buku Besar
        </a>
    </div>
</div>

@include('finance.partials.statement-filter', [
    'action' => route($pageRouteName),
    'filters' => $filters,
    'showPerPage' => false,
])

<div class="pl-section-card pl-source-card">
    <div class="pl-section-head d-flex justify-content-between align-items-center flex-wrap" style="gap:.8rem;">
        <div class="pl-section-title">
            <span class="pl-section-icon"><i class="fas fa-database"></i></span>
            <span>{{ $isManageMode ? 'Import & Manual' : 'Sumber Data Laporan' }}</span>
        </div>
        @if($usesImportedData && $selectedBatchMeta)
            <span class="pl-pill">
                <i class="fas fa-layer-group"></i> {{ $selectedBatchMeta['batch_name'] ?? 'Batch Import' }}
            </span>
        @endif
    </div>
    <div class="p-3">
        <div class="pl-soft-copy">
            {{ $isCombinedSource
                ? 'Halaman utama sedang menggabungkan data jurnal sistem dengan hasil import laba rugi.'
                : ($isImportedSource
                ? ($isManageMode
                    ? 'Kelola batch hasil import dan baris manual untuk laba rugi.'
                    : 'Halaman utama sedang membaca hasil import laba rugi.')
                : 'Halaman ini sedang membaca data jurnal sistem yang sudah diposting.') }}
        </div>
        @unless($isManageMode)
            <div class="pl-source-switch mt-3">
                <a href="{{ route($pageRouteName, $combinedSourceQuery) }}" class="pl-switch-link {{ $isCombinedSource ? 'active' : '' }}">
                    <i class="fas fa-layer-group"></i> Data Gabungan
                </a>
                <a href="{{ route($pageRouteName, $systemSourceQuery) }}" class="pl-switch-link {{ !$isImportedSource && !$isCombinedSource ? 'active' : '' }}">
                    <i class="fas fa-server"></i> Data Sistem
                </a>
                <a href="{{ route($pageRouteName, $importedSourceQuery) }}" class="pl-switch-link {{ $isImportedSource ? 'active' : '' }}">
                    <i class="fas fa-file-import"></i> Hasil Import
                </a>
            </div>
        @endunless
        @if($usesImportedData)
            <form method="GET" action="{{ route($pageRouteName) }}" class="mt-3">
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
                    <label class="fs-label" for="profit_statement_batch_id">
                        <i class="fas fa-copy"></i> Pilih Batch Import
                    </label>
                    <select name="statement_batch_id" id="profit_statement_batch_id" class="fs-control" onchange="this.form.submit()">
                        <option value="">Pilih batch...</option>
                        @foreach($batchOptions as $batchOption)
                            <option value="{{ $batchOption['id'] }}" {{ $selectedBatchId === $batchOption['id'] ? 'selected' : '' }}>
                                {{ $batchOption['batch_name'] }}{{ !empty($batchOption['imported_year']) ? ' | ' . $batchOption['imported_year'] : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>
                @if($selectedBatchMeta)
                    <div class="pl-batch-meta">
                        <div class="pl-batch-stat">
                            <label>Batch Aktif</label>
                            <div>{{ data_get($selectedBatchMeta, 'batch_name', '-') }}</div>
                        </div>
                        <div class="pl-batch-stat">
                            <label>File Sumber</label>
                            <div>{{ data_get($selectedBatchMeta, 'source_filename', 'Manual / Tidak ada file') }}</div>
                        </div>
                        <div class="pl-batch-stat">
                            <label>Total Baris</label>
                            <div>{{ number_format((int) data_get($selectedBatchMeta, 'row_count', 0), 0, ',', '.') }}</div>
                        </div>
                        <div class="pl-batch-stat">
                            <label>Baris Manual</label>
                            <div>{{ number_format((int) data_get($selectedBatchMeta, 'manual_count', 0), 0, ',', '.') }}</div>
                        </div>
                        <div class="pl-batch-stat">
                            <label>Tahun Import</label>
                            <div>{{ data_get($selectedBatchMeta, 'imported_year', '-') }}</div>
                        </div>
                    </div>
                @endif
            </form>
        @endif
    </div>
</div>

@if($isManageMode && $isImportedSource)
    @php
        $profitRowForm = $editImportedRow ?? [
            'section_key' => 'income',
            'group_label' => null,
            'account_code' => null,
            'account_name' => null,
            'finance_type' => 'PENGHASILAN',
            'amount' => 0,
            'batch_id' => $selectedBatchId,
        ];
    @endphp
    <div class="pl-manage-grid">
        <div class="pl-section-card">
            <div class="pl-section-head">
                <div class="pl-section-title">
                    <span class="pl-section-icon"><i class="fas fa-file-import"></i></span>
                    <span>Import Excel Laba Rugi</span>
                </div>
            </div>
            <div class="p-3">
                <div class="pl-import-guide">
                    <div class="pl-import-guide-title">
                        <i class="fas fa-circle-info"></i>
                        <span>Format file yang dibaca parser laba rugi</span>
                    </div>
                    <div class="pl-soft-copy">
                        Sistem membaca <strong>sheet pertama</strong>. Kolom <strong>A</strong> dipakai untuk label grup dan akun,
                        kolom <strong>B</strong> untuk nominal. Judul seperti <strong>Laba Rugi</strong>, <strong>Saldo</strong>,
                        tahun saja, <strong>Laba Bruto</strong>, dan <strong>Surplus (Defisit)</strong> akan dilewati otomatis.
                    </div>
                    <div class="pl-import-guide-grid">
                        <div class="pl-import-guide-card">
                            <label>Header Bagian</label>
                            <div>Gunakan label <strong>Penghasilan</strong> atau <strong>Pengeluaran</strong> untuk memulai bagian utama.</div>
                        </div>
                        <div class="pl-import-guide-card">
                            <label>Baris Akun</label>
                            <div>Isi kolom A dengan format seperti <strong>410.01 Pendapatan SPP</strong> lalu nominalnya di kolom B.</div>
                        </div>
                        <div class="pl-import-guide-card">
                            <label>Grup Tambahan</label>
                            <div>Baris tanpa nominal akan dianggap sebagai nama grup sampai parser menemukan akun berikutnya.</div>
                        </div>
                    </div>
                    <div class="pl-import-chip-row">
                        <span class="pl-import-chip"><i class="fas fa-table-columns"></i> Kolom A: label / akun</span>
                        <span class="pl-import-chip"><i class="fas fa-money-bill-wave"></i> Kolom B: nominal</span>
                        <span class="pl-import-chip"><i class="fas fa-file-import"></i> Otomatis pilih batch hasil import</span>
                    </div>
                </div>
                <form method="POST" action="{{ route('finance.report.profit-loss.import') }}" enctype="multipart/form-data" class="pl-manage-form">
                    @csrf
                    <div class="fs-field full">
                        <label class="fs-label" for="profit_import_file"><i class="fas fa-file-excel"></i> File Excel</label>
                        <input type="file" name="file" id="profit_import_file" class="fs-control" accept=".xlsx,.xls,.csv" required>
                        <div class="fs-helper-text">
                            Upload file Excel/CSV laba rugi. Parser akan membaca sheet pertama dan fokus pada kolom A-B.
                        </div>
                    </div>
                    <div class="fs-field">
                        <label class="fs-label" for="profit_import_batch_name"><i class="fas fa-tag"></i> Nama Batch</label>
                        <input type="text" name="batch_name" id="profit_import_batch_name" class="fs-control" value="{{ old('batch_name') }}">
                        <div class="fs-helper-text">
                            Kosongkan jika ingin memakai nama file sebagai nama batch import.
                        </div>
                    </div>
                    <div class="fs-field">
                        <label class="fs-label" for="profit_import_notes"><i class="fas fa-sticky-note"></i> Catatan</label>
                        <input type="text" name="notes" id="profit_import_notes" class="fs-control" value="{{ old('notes') }}">
                        <div class="fs-helper-text">
                            Opsional, cocok untuk menandai periode, sumber file, atau revisi data.
                        </div>
                    </div>
                    <div class="fs-field full pl-manage-actions">
                        <button type="submit" class="fs-btn fs-btn-primary">
                            <i class="fas fa-upload"></i> Import Excel
                        </button>
                    </div>
                </form>
            </div>
        </div>
        <div class="pl-section-card">
            <div class="pl-section-head">
                <div class="pl-section-title">
                    <span class="pl-section-icon"><i class="fas fa-pen"></i></span>
                    <span>{{ $editImportedRow ? 'Edit Baris Laba Rugi' : 'Tambah Baris Laba Rugi' }}</span>
                </div>
            </div>
            <div class="p-3">
                <form method="POST" action="{{ $editImportedRow ? route('finance.report.profit-loss.rows.update', $editImportedRow['id']) : route('finance.report.profit-loss.rows.store') }}" class="pl-manage-form">
                    @csrf
                    @if($editImportedRow)
                        @method('PUT')
                    @endif
                    <input type="hidden" name="statement_type" value="PROFIT_LOSS">
                    <input type="hidden" name="batch_id" value="{{ old('batch_id', $profitRowForm['batch_id'] ?? $selectedBatchId) }}">
                    <input type="hidden" name="period_type" value="{{ data_get($filters, 'period_type', 'ALL') }}">
                    <div class="fs-field">
                        <label class="fs-label" for="profit_section_key"><i class="fas fa-folder-tree"></i> Kategori</label>
                        <select name="section_key" id="profit_section_key" class="fs-control" required>
                            @foreach(['income' => 'Pemasukan', 'expense' => 'Pengeluaran'] as $sectionKey => $sectionLabel)
                                <option value="{{ $sectionKey }}" {{ old('section_key', $profitRowForm['section_key']) === $sectionKey ? 'selected' : '' }}>{{ $sectionLabel }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="fs-field">
                        <label class="fs-label" for="profit_group_label"><i class="fas fa-layer-group"></i> Grup</label>
                        <input type="text" name="group_label" id="profit_group_label" class="fs-control" value="{{ old('group_label', $profitRowForm['group_label']) }}">
                    </div>
                    <div class="fs-field">
                        <label class="fs-label" for="profit_account_code"><i class="fas fa-hashtag"></i> Kode Akun</label>
                        <input type="text" name="account_code" id="profit_account_code" class="fs-control" value="{{ old('account_code', $profitRowForm['account_code']) }}">
                    </div>
                    <div class="fs-field">
                        <label class="fs-label" for="profit_finance_type"><i class="fas fa-tag"></i> Tipe Finance</label>
                        <input type="text" name="finance_type" id="profit_finance_type" class="fs-control" value="{{ old('finance_type', $profitRowForm['finance_type']) }}">
                    </div>
                    <div class="fs-field full">
                        <label class="fs-label" for="profit_account_name"><i class="fas fa-font"></i> Nama Baris</label>
                        <input type="text" name="account_name" id="profit_account_name" class="fs-control" value="{{ old('account_name', $profitRowForm['account_name']) }}" required>
                    </div>
                    <div class="fs-field">
                        <label class="fs-label" for="profit_amount"><i class="fas fa-money-bill-wave"></i> Nominal</label>
                        <input type="number" step="0.01" name="amount" id="profit_amount" class="fs-control" value="{{ old('amount', $profitRowForm['amount']) }}" required>
                    </div>
                    <div class="fs-field full pl-manage-actions">
                        <button type="submit" class="fs-btn fs-btn-primary">
                            <i class="fas fa-save"></i> {{ $editImportedRow ? 'Update Baris' : 'Tambah Baris' }}
                        </button>
                        @if($editImportedRow)
                            <a href="{{ route($pageRouteName, array_filter(array_merge($filterQuery ?? [], ['statement_data_source' => 'imported', 'statement_batch_id' => $selectedBatchId]), static fn ($value): bool => $value !== null && $value !== '')) }}" class="fs-btn fs-btn-muted">
                                <i class="fas fa-times"></i> Batal Edit
                            </a>
                        @endif
                    </div>
                </form>
            </div>
        </div>
    </div>

    @if(!empty($importedRows))
        <div class="pl-section-card mt-3">
            <div class="pl-section-head d-flex justify-content-between align-items-center flex-wrap" style="gap:.8rem;">
                <div class="pl-section-title">
                    <span class="pl-section-icon"><i class="fas fa-list"></i></span>
                    <span>Baris Import & Manual</span>
                </div>
                <span class="pl-pill">{{ number_format(count($importedRows), 0, ',', '.') }} baris</span>
            </div>
            <div class="table-responsive">
                <table class="pl-table">
                    <thead>
                        <tr>
                            <th style="width:120px;">Kategori</th>
                            <th style="width:140px;">Kode</th>
                            <th>Nama</th>
                            <th style="width:180px;">Grup</th>
                            <th style="width:170px; text-align:right;">Nominal</th>
                            <th style="width:170px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($importedRows as $row)
                            <tr>
                                <td>{{ $row['section_label'] ?? '-' }}</td>
                                <td><strong>{{ $row['account_code'] ?? '-' }}</strong></td>
                                <td>
                                    <div>{{ $row['account_name'] }}</div>
                                    @if(!empty($row['is_manual']))
                                        <div class="pl-soft-copy">Input manual</div>
                                    @endif
                                </td>
                                <td>{{ $row['group_label'] ?? '-' }}</td>
                                <td class="pl-amount {{ ($row['section_key'] ?? 'income') === 'expense' ? 'expense' : 'income' }}">
                                    Rp {{ number_format((float) ($row['amount'] ?? 0), 2, ',', '.') }}
                                </td>
                                <td>
                                    <div class="pl-manage-actions">
                                        <a href="{{ route($pageRouteName, array_filter(array_merge($filterQuery ?? [], ['statement_data_source' => 'imported', 'statement_batch_id' => $selectedBatchId, 'edit_row' => $row['id']]), static fn ($value): bool => $value !== null && $value !== '')) }}" class="pl-switch-link">
                                            <i class="fas fa-pen"></i> Edit
                                        </a>
                                        <form method="POST" action="{{ route('finance.report.profit-loss.rows.destroy', $row['id']) }}" onsubmit="return confirm('Hapus baris laba rugi ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <input type="hidden" name="statement_data_source" value="imported">
                                            <input type="hidden" name="statement_batch_id" value="{{ $selectedBatchId }}">
                                            <input type="hidden" name="period_type" value="{{ data_get($filters, 'period_type', 'ALL') }}">
                                            <button type="submit" class="pl-switch-link" style="color:var(--pl-red); border-color:rgba(239,68,68,.2);">
                                                <i class="fas fa-trash"></i> Hapus
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

<div class="pl-summary-grid">
    <div class="pl-summary-card">
        <div class="pl-summary-label"><i class="fas fa-arrow-trend-up"></i> Total Pemasukan</div>
        <div class="pl-summary-value positive">Rp {{ number_format((float) ($totals['income'] ?? 0), 2, ',', '.') }}</div>
        <div class="pl-summary-help">{{ number_format(count($incomeRows), 0, ',', '.') }} akun penghasilan masuk ke periode ini.</div>
    </div>
    <div class="pl-summary-card">
        <div class="pl-summary-label"><i class="fas fa-arrow-trend-down"></i> Total Pengeluaran</div>
        <div class="pl-summary-value negative">Rp {{ number_format((float) ($totals['expense'] ?? 0), 2, ',', '.') }}</div>
        <div class="pl-summary-help">{{ number_format(count($expenseRows), 0, ',', '.') }} akun pengeluaran tercatat di laporan ini.</div>
    </div>
    <div class="pl-summary-card">
        <div class="pl-summary-label"><i class="fas fa-scale-balanced"></i> Laba / Rugi Bersih</div>
        <div class="pl-summary-value {{ (float) ($totals['net_result'] ?? 0) >= 0 ? 'positive' : 'negative' }}">
            Rp {{ number_format((float) ($totals['net_result'] ?? 0), 2, ',', '.') }}
        </div>
        <div class="pl-summary-help">Hasil selisih pemasukan dan pengeluaran untuk filter yang aktif.</div>
    </div>
</div>

@if($hasRows)
    <div class="pl-section-grid">
        <div class="pl-section-card">
            <div class="pl-section-head">
                <div class="pl-section-title">
                    <span class="pl-section-icon" style="background:var(--pl-green-soft);color:var(--pl-green);">
                        <i class="fas fa-arrow-up"></i>
                    </span>
                    <span>Pemasukan</span>
                </div>
            </div>
            <div class="table-responsive">
                <table class="pl-table">
                    <thead>
                        <tr>
                            <th style="width:140px;">Kode</th>
                            <th>Nama Akun</th>
                            <th style="width:180px; text-align:right;">Nominal</th>
                        </tr>
                    </thead>
                        <tbody>
                            @forelse($incomeRows as $row)
                                @php
                                    $rowHasJournalSource = (bool) ($row['has_journal_source'] ?? !$isImportedSource);
                                    $rowHasImportedSource = (bool) ($row['has_imported_source'] ?? $isImportedSource);
                                    $canOpenJournalDetail = $rowHasJournalSource
                                        && !empty($row['account_code'])
                                        && $row['account_code'] !== '-';
                                    $journalItemsRoute = route('finance.report.journal-items', array_merge($baseFilterQuery, [
                                        'account_code' => $row['account_code'],
                                        'statement_source' => 'profit_loss',
                                    ]));
                                    $manageIncomeRowRoute = route($manageStatementRouteName, array_filter(array_merge($baseFilterQuery ?? [], [
                                        'statement_data_source' => 'imported',
                                        'statement_batch_id' => $row['imported_batch_id'] ?? $selectedBatchId,
                                        'account_code' => $row['account_code'] ?? null,
                                        'edit_row' => $isImportedSource ? ($row['id'] ?? null) : null,
                                    ]), static fn ($value): bool => $value !== null && $value !== ''));
                                @endphp
                            <tr>
                                <td>
                                    @if($canOpenJournalDetail)
                                        <a href="{{ $journalItemsRoute }}" class="pl-account-link">
                                            <strong>{{ $row['account_code'] }}</strong>
                                        </a>
                                    @else
                                        <strong>{{ $row['account_code'] }}</strong>
                                    @endif
                                </td>
                                <td>
                                    <div class="pl-account-cell">
                                        @if($canOpenJournalDetail)
                                            <a href="{{ $journalItemsRoute }}" class="pl-account-name pl-account-link">{{ $row['account_name'] }}</a>
                                        @else
                                            <div class="pl-account-name">{{ $row['account_name'] }}</div>
                                        @endif
                                        @if(!empty($row['group_label']))
                                            <div class="pl-soft-copy">{{ $row['group_label'] }}</div>
                                        @endif
                                        <div class="dropdown">
                                            <button type="button" class="pl-row-menu-btn dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                <i class="fas fa-ellipsis-v"></i>
                                            </button>
                                            <div class="dropdown-menu dropdown-menu-right pl-row-menu">
                                                @if($canOpenJournalDetail)
                                                    <a class="dropdown-item" href="{{ $journalItemsRoute }}">
                                                        <i class="fas fa-table"></i> Item Jurnal
                                                    </a>
                                                    <a class="dropdown-item" href="{{ route('finance.report.general-ledger', array_merge($baseFilterQuery, ['account_code' => $row['account_code']])) }}">
                                                        <i class="fas fa-book-open"></i> Buku Besar
                                                    </a>
                                                @endif
                                                @if($rowHasImportedSource)
                                                    <a class="dropdown-item" href="{{ $manageIncomeRowRoute }}">
                                                        <i class="fas fa-pen"></i> {{ $isImportedSource ? 'Edit Baris' : 'Kelola Import' }}
                                                    </a>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="pl-amount income">
                                    @if($canOpenJournalDetail)
                                        <a href="{{ $journalItemsRoute }}" class="pl-amount-link">
                                            Rp {{ number_format((float) $row['amount'], 2, ',', '.') }}
                                        </a>
                                    @else
                                        Rp {{ number_format((float) $row['amount'], 2, ',', '.') }}
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" style="text-align:center; color:var(--pl-muted);">Belum ada pemasukan pada periode ini.</td>
                            </tr>
                        @endforelse
                        <tr class="pl-total-row">
                            <td colspan="2">Total Pemasukan</td>
                            <td class="pl-amount income">Rp {{ number_format((float) ($totals['income'] ?? 0), 2, ',', '.') }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="pl-section-card">
            <div class="pl-section-head">
                <div class="pl-section-title">
                    <span class="pl-section-icon" style="background:var(--pl-red-soft);color:var(--pl-red);">
                        <i class="fas fa-arrow-down"></i>
                    </span>
                    <span>Pengeluaran</span>
                </div>
            </div>
            <div class="table-responsive">
                <table class="pl-table">
                    <thead>
                        <tr>
                            <th style="width:140px;">Kode</th>
                            <th>Nama Akun</th>
                            <th style="width:180px; text-align:right;">Nominal</th>
                        </tr>
                    </thead>
                        <tbody>
                            @forelse($expenseRows as $row)
                                @php
                                    $rowHasJournalSource = (bool) ($row['has_journal_source'] ?? !$isImportedSource);
                                    $rowHasImportedSource = (bool) ($row['has_imported_source'] ?? $isImportedSource);
                                    $canOpenJournalDetail = $rowHasJournalSource
                                        && !empty($row['account_code'])
                                        && $row['account_code'] !== '-';
                                    $journalItemsRoute = route('finance.report.journal-items', array_merge($baseFilterQuery, [
                                        'account_code' => $row['account_code'],
                                        'statement_source' => 'profit_loss',
                                    ]));
                                    $manageExpenseRowRoute = route($manageStatementRouteName, array_filter(array_merge($baseFilterQuery ?? [], [
                                        'statement_data_source' => 'imported',
                                        'statement_batch_id' => $row['imported_batch_id'] ?? $selectedBatchId,
                                        'account_code' => $row['account_code'] ?? null,
                                        'edit_row' => $isImportedSource ? ($row['id'] ?? null) : null,
                                    ]), static fn ($value): bool => $value !== null && $value !== ''));
                                @endphp
                            <tr>
                                <td>
                                    @if($canOpenJournalDetail)
                                        <a href="{{ $journalItemsRoute }}" class="pl-account-link">
                                            <strong>{{ $row['account_code'] }}</strong>
                                        </a>
                                    @else
                                        <strong>{{ $row['account_code'] }}</strong>
                                    @endif
                                </td>
                                <td>
                                    <div class="pl-account-cell">
                                        @if($canOpenJournalDetail)
                                            <a href="{{ $journalItemsRoute }}" class="pl-account-name pl-account-link">{{ $row['account_name'] }}</a>
                                        @else
                                            <div class="pl-account-name">{{ $row['account_name'] }}</div>
                                        @endif
                                        @if(!empty($row['group_label']))
                                            <div class="pl-soft-copy">{{ $row['group_label'] }}</div>
                                        @endif
                                        <div class="dropdown">
                                            <button type="button" class="pl-row-menu-btn dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                <i class="fas fa-ellipsis-v"></i>
                                            </button>
                                            <div class="dropdown-menu dropdown-menu-right pl-row-menu">
                                                @if($canOpenJournalDetail)
                                                    <a class="dropdown-item" href="{{ $journalItemsRoute }}">
                                                        <i class="fas fa-table"></i> Item Jurnal
                                                    </a>
                                                    <a class="dropdown-item" href="{{ route('finance.report.general-ledger', array_merge($baseFilterQuery, ['account_code' => $row['account_code']])) }}">
                                                        <i class="fas fa-book-open"></i> Buku Besar
                                                    </a>
                                                @endif
                                                @if($rowHasImportedSource)
                                                    <a class="dropdown-item" href="{{ $manageExpenseRowRoute }}">
                                                        <i class="fas fa-pen"></i> {{ $isImportedSource ? 'Edit Baris' : 'Kelola Import' }}
                                                    </a>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="pl-amount expense">
                                    @if($canOpenJournalDetail)
                                        <a href="{{ $journalItemsRoute }}" class="pl-amount-link">
                                            Rp {{ number_format((float) $row['amount'], 2, ',', '.') }}
                                        </a>
                                    @else
                                        Rp {{ number_format((float) $row['amount'], 2, ',', '.') }}
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" style="text-align:center; color:var(--pl-muted);">Belum ada pengeluaran pada periode ini.</td>
                            </tr>
                        @endforelse
                        <tr class="pl-total-row">
                            <td colspan="2">Total Pengeluaran</td>
                            <td class="pl-amount expense">Rp {{ number_format((float) ($totals['expense'] ?? 0), 2, ',', '.') }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@else
    <div class="pl-empty-card">
        <i class="fas fa-chart-line"></i>
        <h4>Belum ada data laba rugi</h4>
        <div>Pastikan jurnal sudah <strong>POSTED</strong> dan akun pemasukan atau pengeluaran sudah dipetakan di bagan akun.</div>
    </div>
@endif
@endsection
