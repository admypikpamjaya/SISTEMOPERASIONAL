@extends('layouts.app')

@section('content')
@php
    $summary = $report['summary'] ?? ['account_count' => 0, 'entry_count' => 0, 'total_debit' => 0, 'total_credit' => 0, 'balance_gap' => 0];
    $groups = $report['groups'] ?? [];
    $accounts = $report['accounts'] ?? null;
    $baseFilterQuery = $baseFilterQuery ?? ($filterQuery ?? []);
    $selectedAccountCode = $selectedAccountCode ?? null;
    $isManageMode = $isManageMode ?? false;
    $pageRouteName = $pageRouteName ?? 'finance.report.general-ledger';
    $mainLedgerRouteName = $mainLedgerRouteName ?? 'finance.report.general-ledger';
    $manageLedgerRouteName = $manageLedgerRouteName ?? 'finance.report.general-ledger.manage';
    $ledgerSource = $ledgerSource ?? 'system';
    $isImportedSource = $ledgerSource === 'imported';
    $isCombinedSource = $ledgerSource === 'combined';
    $usesImportedData = in_array($ledgerSource, ['imported', 'combined'], true);
    $selectedBatchId = $selectedBatchId ?? null;
    $batchOptions = $batchOptions ?? [];
    $selectedBatch = $selectedBatch ?? null;
    $selectedBatchMeta = collect($batchOptions)->firstWhere('id', $selectedBatchId)
        ?? $selectedBatch;
    $editEntry = $editEntry ?? null;
    $selectedAccountName = $selectedAccountCode !== null && !empty($groups)
        ? ($groups[0]['account_name'] ?? null)
        : null;
    $sourceQueryBase = collect($filterQuery ?? [])
        ->except(['ledger_source', 'ledger_batch_id', 'page'])
        ->filter(static fn ($value): bool => $value !== null && $value !== '')
        ->all();
    $combinedSourceQuery = array_merge(
        $sourceQueryBase,
        ['ledger_source' => 'combined'],
        $selectedBatchId ? ['ledger_batch_id' => $selectedBatchId] : []
    );
    $systemSourceQuery = array_merge($sourceQueryBase, ['ledger_source' => 'system']);
    $importedSourceQuery = array_merge(
        $sourceQueryBase,
        ['ledger_source' => 'imported', 'period_type' => $isImportedSource ? (data_get($filters, 'period_type', 'ALL') ?: 'ALL') : 'ALL'],
        $selectedBatchId ? ['ledger_batch_id' => $selectedBatchId] : []
    );
    $switchPageQuery = array_filter(array_merge($filterQuery ?? [], [
        'ledger_source' => $ledgerSource,
        'ledger_batch_id' => $selectedBatchId,
    ]), static fn ($value): bool => $value !== null && $value !== '');
    $managePageQuery = array_filter(array_merge($filterQuery ?? [], [
        'ledger_source' => 'imported',
        'ledger_batch_id' => $selectedBatchId,
        'period_type' => $isImportedSource ? (data_get($filters, 'period_type', 'ALL') ?: 'ALL') : 'ALL',
    ]), static fn ($value): bool => $value !== null && $value !== '');
    $mainPageQuery = array_filter(array_merge($baseFilterQuery ?? [], [
        'ledger_source' => $usesImportedData ? 'combined' : 'system',
        'ledger_batch_id' => $selectedBatchId,
    ]), static fn ($value): bool => $value !== null && $value !== '');
    $redirectFields = array_filter([
        'period_type' => data_get($filters, 'period_type'),
        'start_date' => data_get($filters, 'start_date'),
        'end_date' => data_get($filters, 'end_date'),
        'start_month' => data_get($filters, 'start_month'),
        'end_month' => data_get($filters, 'end_month'),
        'start_year' => data_get($filters, 'start_year'),
        'end_year' => data_get($filters, 'end_year'),
        'report_date' => data_get($filters, 'report_date'),
        'month' => data_get($filters, 'month'),
        'year' => data_get($filters, 'year'),
        'account_code_filter' => $selectedAccountCode,
        'search_filter' => data_get($filters, 'search'),
        'per_page' => data_get($filters, 'per_page'),
    ], static fn ($value): bool => $value !== null && $value !== '');
    $entryForm = $editEntry ?? [
        'row_type' => 'ENTRY',
        'entry_date' => data_get($filters, 'start_date') ?: now()->toDateString(),
        'account_code' => $selectedAccountCode,
        'account_name' => $selectedAccountName,
        'transaction_no' => null,
        'communication' => null,
        'partner_name' => null,
        'currency' => 'IDR',
        'label' => null,
        'reference' => null,
        'analytic_distribution' => null,
        'opening_balance' => 0,
        'debit' => 0,
        'credit' => 0,
    ];
    $pageSubtitle = $isManageMode
        ? __('app.finance.general_ledger_imported_manage_subtitle')
        : ($isCombinedSource
            ? __('app.finance.general_ledger_combined_subtitle', ['period' => $periodLabel])
            : ($isImportedSource
            ? __('app.finance.general_ledger_imported_subtitle')
            : __('app.finance.general_ledger_system_subtitle', ['period' => $periodLabel])));
@endphp

<style>
    @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');

    :root {
        --gl-blue: #1d4ed8;
        --gl-blue-dark: #1e3a8a;
        --gl-cyan: #0891b2;
        --gl-green: #059669;
        --gl-green-soft: rgba(16, 185, 129, 0.12);
        --gl-red: #dc2626;
        --gl-red-soft: rgba(239, 68, 68, 0.12);
        --gl-amber: #d97706;
        --gl-bg: #f0f4fd;
        --gl-card: #ffffff;
        --gl-text: #0f172a;
        --gl-muted: #64748b;
        --gl-border: rgba(37, 99, 235, 0.10);
        --gl-shadow: 0 10px 32px rgba(15, 23, 42, 0.08), 0 4px 14px rgba(37, 99, 235, 0.06);
        --gl-radius: 18px;
        --gl-radius-sm: 12px;
    }

    body, .content-wrapper {
        background: var(--gl-bg) !important;
        font-family: 'Plus Jakarta Sans', sans-serif !important;
    }

    .gl-page-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        flex-wrap: wrap;
        margin-bottom: 1.5rem;
    }
    .gl-page-title {
        display: flex;
        align-items: center;
        gap: 0.9rem;
    }
    .gl-title-icon {
        width: 50px;
        height: 50px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, var(--gl-blue), var(--gl-blue-dark));
        color: #fff;
        font-size: 1.2rem;
        box-shadow: var(--gl-shadow);
    }
    .gl-page-title h1 {
        margin: 0;
        color: var(--gl-text);
        font-size: 1.4rem;
        font-weight: 800;
    }
    .gl-page-title p {
        margin: 0.15rem 0 0;
        color: var(--gl-muted);
        font-size: 0.82rem;
        font-weight: 500;
    }
    .gl-nav {
        display: flex;
        flex-wrap: wrap;
        gap: 0.55rem;
    }
    .gl-nav-link,
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
    .gl-nav-link:hover,
    .fs-btn:hover {
        text-decoration: none;
        transform: translateY(-1px);
    }
    .gl-nav-link.primary,
    .fs-btn-primary {
        background: linear-gradient(135deg, var(--gl-blue), #2563eb);
        color: #fff;
        box-shadow: 0 8px 22px rgba(37, 99, 235, 0.24);
    }
    .gl-nav-link.muted,
    .fs-btn-muted {
        background: #fff;
        color: var(--gl-muted);
        border-color: var(--gl-border);
    }

    .fs-filter-card,
    .gl-summary-card,
    .gl-ledger-card,
    .gl-empty-card {
        background: var(--gl-card);
        border: 1px solid var(--gl-border);
        border-radius: var(--gl-radius);
        box-shadow: var(--gl-shadow);
    }
    .fs-filter-head,
    .gl-ledger-head {
        padding: 1rem 1.2rem;
        border-bottom: 1px solid var(--gl-border);
    }
    .fs-filter-head {
        background: linear-gradient(135deg, var(--gl-blue-dark), var(--gl-blue));
        border-bottom: none;
        border-radius: var(--gl-radius) var(--gl-radius) 0 0;
    }
    .fs-filter-title,
    .gl-ledger-title {
        display: flex;
        align-items: center;
        gap: 0.65rem;
        font-size: 0.9rem;
        font-weight: 700;
    }
    .fs-filter-title { color: #fff; }
    .gl-ledger-title { color: var(--gl-text); }
    .fs-filter-icon,
    .gl-ledger-icon {
        width: 30px;
        height: 30px;
        border-radius: 10px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }
    .fs-filter-icon { background: rgba(255, 255, 255, 0.16); }
    .gl-ledger-icon { background: rgba(37, 99, 235, 0.08); color: var(--gl-blue); }
    .fs-filter-body { padding: 1.1rem 1.2rem 0.3rem; }
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
        color: var(--gl-muted);
    }
    .fs-control {
        width: 100%;
        border: 1.5px solid rgba(148, 163, 184, 0.18);
        border-radius: var(--gl-radius-sm);
        padding: 0.65rem 0.85rem;
        font-size: 0.84rem;
        color: var(--gl-text);
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

    .gl-summary-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 0.9rem;
        margin: 1.2rem 0;
    }
    .gl-summary-card {
        padding: 1rem 1.1rem;
    }
    .gl-summary-label {
        display: flex;
        align-items: center;
        gap: 0.45rem;
        color: var(--gl-muted);
        font-size: 0.72rem;
        font-weight: 800;
        letter-spacing: 0.06em;
        text-transform: uppercase;
        margin-bottom: 0.45rem;
    }
    .gl-summary-value {
        color: var(--gl-text);
        font-size: 1.25rem;
        font-weight: 800;
        line-height: 1.15;
    }
    .gl-summary-help {
        margin-top: 0.35rem;
        color: var(--gl-muted);
        font-size: 0.75rem;
        font-weight: 500;
    }
    .gl-filter-note {
        margin: 1rem 0 0;
        padding: 0.9rem 1rem;
        border-radius: 14px;
        background: rgba(37, 99, 235, 0.07);
        border: 1px solid rgba(37, 99, 235, 0.12);
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.8rem;
        flex-wrap: wrap;
        color: var(--gl-text);
    }
    .gl-filter-note small {
        display: block;
        margin-top: 0.18rem;
        color: var(--gl-muted);
        font-size: 0.74rem;
        font-weight: 500;
    }
    .gl-filter-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        border-radius: 999px;
        padding: 0.28rem 0.8rem;
        background: rgba(37, 99, 235, 0.1);
        color: var(--gl-blue);
        font-size: 0.74rem;
        font-weight: 800;
    }
    .gl-reset-link {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        padding: 0.55rem 0.9rem;
        border-radius: 10px;
        background: #fff;
        border: 1px solid var(--gl-border);
        color: var(--gl-muted);
        font-size: 0.78rem;
        font-weight: 700;
        text-decoration: none;
        transition: all 0.2s ease;
    }
    .gl-reset-link:hover {
        text-decoration: none;
        color: var(--gl-text);
        transform: translateY(-1px);
    }
    .gl-source-grid,
    .gl-manage-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 1rem;
        margin-top: 1rem;
    }
    .gl-source-card,
    .gl-manage-card {
        background: var(--gl-card);
        border: 1px solid var(--gl-border);
        border-radius: var(--gl-radius);
        box-shadow: var(--gl-shadow);
        padding: 1rem 1.1rem;
    }
    .gl-source-links {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 0.75rem;
    }
    .gl-source-link {
        display: block;
        padding: 0.9rem 1rem;
        border-radius: 14px;
        border: 1px solid var(--gl-border);
        background: #f8fbff;
        color: var(--gl-text);
        text-decoration: none;
        transition: all 0.2s ease;
    }
    .gl-source-link:hover {
        text-decoration: none;
        transform: translateY(-1px);
    }
    .gl-source-link.is-active {
        background: linear-gradient(135deg, rgba(29, 78, 216, 0.12), rgba(37, 99, 235, 0.2));
        border-color: rgba(37, 99, 235, 0.24);
    }
    .gl-source-link strong {
        display: block;
        font-size: 0.86rem;
        margin-bottom: 0.2rem;
    }
    .gl-source-link span {
        color: var(--gl-muted);
        font-size: 0.75rem;
    }
    .gl-panel-title {
        display: flex;
        align-items: center;
        gap: 0.55rem;
        color: var(--gl-text);
        font-size: 0.92rem;
        font-weight: 800;
        margin-bottom: 0.85rem;
    }
    .gl-panel-help {
        color: var(--gl-muted);
        font-size: 0.76rem;
        line-height: 1.5;
        margin-top: 0.35rem;
    }
    .gl-batch-form,
    .gl-manage-form {
        display: grid;
        gap: 0.85rem;
    }
    .gl-batch-meta {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
        gap: 0.7rem;
        margin-top: 0.8rem;
    }
    .gl-batch-stat {
        background: #f8fbff;
        border: 1px solid rgba(148, 163, 184, 0.14);
        border-radius: 12px;
        padding: 0.75rem 0.85rem;
    }
    .gl-batch-stat label {
        display: block;
        color: var(--gl-muted);
        font-size: 0.68rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        margin-bottom: 0.2rem;
    }
    .gl-batch-stat div {
        color: var(--gl-text);
        font-size: 0.82rem;
        font-weight: 700;
    }
    .gl-import-guide {
        padding: 0.95rem 1rem;
        border-radius: 16px;
        border: 1px dashed rgba(8, 145, 178, 0.2);
        background: linear-gradient(135deg, rgba(8, 145, 178, 0.06), rgba(37, 99, 235, 0.05));
        margin-bottom: 1rem;
    }
    .gl-import-guide-title {
        display: flex;
        align-items: center;
        gap: 0.55rem;
        color: var(--gl-blue-dark);
        font-size: 0.9rem;
        font-weight: 800;
        margin-bottom: 0.55rem;
    }
    .gl-import-guide-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 0.75rem;
        margin-top: 0.85rem;
    }
    .gl-import-guide-card {
        background: rgba(255, 255, 255, 0.82);
        border: 1px solid rgba(148, 163, 184, 0.14);
        border-radius: 14px;
        padding: 0.8rem 0.85rem;
    }
    .gl-import-guide-card label {
        display: block;
        color: var(--gl-blue-dark);
        font-size: 0.7rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        margin-bottom: 0.35rem;
    }
    .gl-import-guide-card div {
        color: var(--gl-text);
        font-size: 0.8rem;
        line-height: 1.55;
    }
    .gl-import-chip-row {
        display: flex;
        flex-wrap: wrap;
        gap: 0.45rem;
        margin-top: 0.75rem;
    }
    .gl-import-chip {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.35rem 0.65rem;
        border-radius: 999px;
        background: rgba(15, 23, 42, 0.04);
        border: 1px solid rgba(148, 163, 184, 0.14);
        color: var(--gl-text);
        font-size: 0.72rem;
        font-weight: 700;
    }
    .gl-form-grid {
        display: grid;
        grid-template-columns: repeat(12, minmax(0, 1fr));
        gap: 0.8rem;
    }
    .gl-form-grid .gl-col-12 { grid-column: span 12; }
    .gl-form-grid .gl-col-6 { grid-column: span 6; }
    .gl-form-grid .gl-col-4 { grid-column: span 4; }
    .gl-form-grid .gl-col-3 { grid-column: span 3; }
    .gl-form-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 0.65rem;
        align-items: center;
    }
    .gl-chip {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.28rem 0.65rem;
        border-radius: 999px;
        background: rgba(15, 23, 42, 0.06);
        color: var(--gl-muted);
        font-size: 0.7rem;
        font-weight: 800;
        margin-top: 0.35rem;
    }
    .gl-chip.imported {
        background: rgba(8, 145, 178, 0.12);
        color: var(--gl-cyan);
    }
    .gl-chip.manual {
        background: rgba(16, 185, 129, 0.12);
        color: var(--gl-green);
    }
    .gl-row-actions {
        display: flex;
        justify-content: flex-end;
        gap: 0.45rem;
        flex-wrap: wrap;
    }
    .gl-inline-form {
        margin: 0;
    }
    .gl-row-link,
    .gl-row-btn {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.38rem 0.72rem;
        border-radius: 10px;
        border: 1px solid rgba(37, 99, 235, 0.15);
        background: rgba(37, 99, 235, 0.08);
        color: var(--gl-blue);
        font-size: 0.72rem;
        font-weight: 800;
        text-decoration: none;
    }
    .gl-row-btn {
        cursor: pointer;
    }
    .gl-row-link:hover,
    .gl-row-btn:hover {
        text-decoration: none;
        background: var(--gl-blue);
        color: #fff;
    }
    .gl-row-btn.danger {
        background: rgba(239, 68, 68, 0.08);
        border-color: rgba(239, 68, 68, 0.15);
        color: var(--gl-red);
    }
    .gl-row-btn.danger:hover {
        background: var(--gl-red);
        color: #fff;
    }
    .gl-table td.gl-action-cell {
        width: 170px;
    }

    .gl-ledger-list {
        display: grid;
        gap: 1rem;
    }
    .gl-ledger-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        align-items: center;
    }
    .gl-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        border-radius: 999px;
        padding: 0.25rem 0.65rem;
        font-size: 0.68rem;
        font-weight: 800;
        background: rgba(37, 99, 235, 0.10);
        color: var(--gl-blue);
    }
    .gl-badge.green { background: var(--gl-green-soft); color: var(--gl-green); }
    .gl-badge.red { background: var(--gl-red-soft); color: var(--gl-red); }

    .gl-ledger-head {
        display: flex;
        justify-content: space-between;
        gap: 0.8rem;
        flex-wrap: wrap;
    }
    .gl-ledger-sub {
        margin-top: 0.2rem;
        color: var(--gl-muted);
        font-size: 0.76rem;
        font-weight: 500;
    }
    .gl-ledger-totals {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 0.6rem;
        min-width: 320px;
    }
    .gl-ledger-total {
        background: #f8fbff;
        border-radius: 12px;
        padding: 0.7rem 0.8rem;
        border: 1px solid rgba(148, 163, 184, 0.14);
    }
    .gl-ledger-total label {
        display: block;
        margin-bottom: 0.2rem;
        color: var(--gl-muted);
        font-size: 0.66rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.06em;
    }
    .gl-ledger-total div {
        color: var(--gl-text);
        font-size: 0.82rem;
        font-weight: 800;
        white-space: nowrap;
    }

    .gl-table {
        width: 100%;
        border-collapse: collapse;
    }
    .gl-table th {
        background: #f8fbff;
        color: var(--gl-muted);
        font-size: 0.68rem;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        padding: 0.75rem 1rem;
        border-bottom: 1px solid var(--gl-border);
    }
    .gl-table td {
        padding: 0.78rem 1rem;
        font-size: 0.81rem;
        color: #334155;
        border-bottom: 1px solid rgba(148, 163, 184, 0.12);
        vertical-align: top;
    }
    .gl-table tbody tr:last-child td { border-bottom: none; }
    .gl-table tbody tr:hover td { background: rgba(37, 99, 235, 0.03); }
    .gl-amount {
        text-align: right;
        white-space: nowrap;
        font-weight: 800;
    }
    .gl-amount.debit { color: var(--gl-green); }
    .gl-amount.credit { color: var(--gl-red); }
    .gl-amount.balance { color: var(--gl-blue); }
    .gl-entry-title {
        color: var(--gl-text);
        font-weight: 700;
        margin-bottom: 0.15rem;
    }
    .gl-entry-sub {
        color: var(--gl-muted);
        font-size: 0.74rem;
        line-height: 1.45;
    }
    .gl-journal-link {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        margin-top: 0.35rem;
        padding: 0.28rem 0.65rem;
        border-radius: 999px;
        background: rgba(37, 99, 235, 0.08);
        border: 1px solid rgba(37, 99, 235, 0.14);
        color: var(--gl-blue);
        font-size: 0.7rem;
        font-weight: 800;
        text-decoration: none;
    }
    .gl-journal-link:hover {
        background: var(--gl-blue);
        color: #fff;
        text-decoration: none;
    }
    .gl-pagination {
        margin-top: 1rem;
        padding: 0.95rem 1rem;
        background: var(--gl-card);
        border: 1px solid var(--gl-border);
        border-radius: var(--gl-radius);
        box-shadow: var(--gl-shadow);
    }
    .gl-pagination .pagination { margin: 0; }
    .gl-empty-card {
        margin-top: 1rem;
        padding: 2.5rem 1.2rem;
        text-align: center;
        color: var(--gl-muted);
    }
    .gl-empty-card i {
        font-size: 2.3rem;
        margin-bottom: 0.8rem;
        color: rgba(37, 99, 235, 0.28);
    }
    .gl-empty-card h4 {
        color: var(--gl-text);
        font-size: 1rem;
        font-weight: 700;
        margin-bottom: 0.35rem;
    }

    body.dark-mode,
    body.dark-mode .content-wrapper {
        background: var(--app-bg) !important;
    }
    body.dark-mode .fs-filter-card,
    body.dark-mode .gl-summary-card,
    body.dark-mode .gl-ledger-card,
    body.dark-mode .gl-empty-card,
    body.dark-mode .gl-pagination,
    body.dark-mode .gl-source-card,
    body.dark-mode .gl-manage-card {
        background: var(--app-surface) !important;
        border-color: var(--app-border) !important;
        box-shadow: var(--app-shadow) !important;
    }
    body.dark-mode .gl-page-title h1,
    body.dark-mode .gl-summary-value,
    body.dark-mode .gl-empty-card h4,
    body.dark-mode .gl-ledger-title,
    body.dark-mode .gl-ledger-total div,
    body.dark-mode .gl-entry-title {
        color: var(--app-text) !important;
    }
    body.dark-mode .gl-page-title p,
    body.dark-mode .fs-label,
    body.dark-mode .gl-summary-help,
    body.dark-mode .gl-summary-label,
    body.dark-mode .gl-table th,
    body.dark-mode .gl-entry-sub,
    body.dark-mode .gl-ledger-sub,
    body.dark-mode .gl-ledger-total label,
    body.dark-mode .gl-panel-help,
    body.dark-mode .gl-source-link span,
    body.dark-mode .gl-batch-stat label {
        color: var(--app-text-muted) !important;
    }
    body.dark-mode .fs-control,
    body.dark-mode .gl-nav-link.muted,
    body.dark-mode .fs-btn-muted,
    body.dark-mode .gl-ledger-total,
    body.dark-mode .gl-reset-link,
    body.dark-mode .gl-source-link,
    body.dark-mode .gl-batch-stat,
    body.dark-mode .gl-import-guide-card,
    body.dark-mode .gl-import-chip {
        background: var(--app-surface-soft) !important;
        border-color: var(--app-border) !important;
        color: var(--app-text) !important;
    }
    body.dark-mode .gl-import-guide {
        background: rgba(96, 165, 250, 0.08) !important;
        border-color: rgba(96, 165, 250, 0.18) !important;
    }
    body.dark-mode .gl-import-guide-title {
        color: var(--app-text) !important;
    }
    body.dark-mode .gl-import-guide-card label {
        color: var(--app-text-muted) !important;
    }
    body.dark-mode .gl-import-guide-card div,
    body.dark-mode .gl-import-chip {
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
    body.dark-mode .gl-table th {
        background: var(--app-surface-soft) !important;
        border-color: var(--app-border) !important;
    }
    body.dark-mode .gl-filter-note {
        background: var(--app-surface) !important;
        border-color: var(--app-border) !important;
        box-shadow: var(--app-shadow) !important;
    }
    body.dark-mode .gl-table td {
        color: var(--app-text-soft) !important;
        border-color: var(--app-border) !important;
    }
    body.dark-mode .gl-table tbody tr:hover td {
        background: var(--app-row-hover) !important;
    }
    body.dark-mode .gl-journal-link {
        background: rgba(96, 165, 250, 0.12) !important;
        border-color: rgba(96, 165, 250, 0.18) !important;
        color: var(--app-text) !important;
    }
    body.dark-mode .gl-journal-link:hover {
        background: var(--app-accent) !important;
        color: #fff !important;
    }
    body.dark-mode .gl-source-link.is-active {
        background: rgba(96, 165, 250, 0.12) !important;
        border-color: rgba(96, 165, 250, 0.22) !important;
    }
    body.dark-mode .gl-row-link,
    body.dark-mode .gl-row-btn {
        background: rgba(96, 165, 250, 0.12) !important;
        border-color: rgba(96, 165, 250, 0.18) !important;
        color: var(--app-text) !important;
    }
    body.dark-mode .gl-row-btn.danger {
        background: rgba(248, 113, 113, 0.12) !important;
        border-color: rgba(248, 113, 113, 0.18) !important;
        color: #fecaca !important;
    }
    body.dark-mode .gl-row-link:hover,
    body.dark-mode .gl-row-btn:hover {
        background: var(--app-accent) !important;
        color: #fff !important;
    }
    body.dark-mode .gl-row-btn.danger:hover {
        background: #ef4444 !important;
        color: #fff !important;
    }
    body.dark-mode .gl-pagination .page-link {
        background: var(--app-surface-soft) !important;
        border-color: var(--app-border) !important;
        color: var(--app-text-soft) !important;
    }
    body.dark-mode .gl-pagination .page-item.active .page-link {
        background: var(--app-accent) !important;
        border-color: var(--app-accent) !important;
        color: #fff !important;
    }
    @media (max-width: 991px) {
        .gl-ledger-totals {
            grid-template-columns: 1fr;
            min-width: 100%;
        }
        .gl-form-grid .gl-col-6,
        .gl-form-grid .gl-col-4,
        .gl-form-grid .gl-col-3 {
            grid-column: span 6;
        }
    }
    @media (max-width: 767px) {
        .gl-source-links {
            grid-template-columns: 1fr;
        }
        .gl-form-grid {
            grid-template-columns: 1fr;
        }
        .gl-form-grid .gl-col-12,
        .gl-form-grid .gl-col-6,
        .gl-form-grid .gl-col-4,
        .gl-form-grid .gl-col-3 {
            grid-column: auto;
        }
    }
</style>

<div class="gl-page-header">
    <div class="gl-page-title">
        <div class="gl-title-icon"><i class="fas fa-book-open"></i></div>
        <div>
            <h1>{{ __('app.finance.general_ledger_report_title') }}</h1>
            <p>{{ $pageSubtitle }}</p>
        </div>
    </div>

    <div class="gl-nav">
        <a href="{{ route('finance.dashboard') }}" class="gl-nav-link muted">
            <i class="fas fa-arrow-left"></i> {{ __('app.finance.dashboard') }}
        </a>
        <a href="{{ route('finance.report.general-ledger.download', array_merge($filterQuery, ['format' => 'excel'])) }}" class="gl-nav-link muted">
            <i class="fas fa-file-excel"></i> {{ __('app.finance.download_excel') }}
        </a>
        <a href="{{ route('finance.report.general-ledger.download', $filterQuery) }}" class="gl-nav-link primary">
            <i class="fas fa-file-pdf"></i> {{ __('app.finance.download_pdf') }}
        </a>
        <a href="{{ route('finance.report.balance-sheet', $baseFilterQuery) }}" class="gl-nav-link muted">
            <i class="fas fa-balance-scale"></i> {{ __('app.finance.balance_sheet') }}
        </a>
        <a href="{{ route('finance.report.profit-loss', $baseFilterQuery) }}" class="gl-nav-link muted">
            <i class="fas fa-chart-area"></i> {{ __('app.finance.profit_loss') }}
        </a>
        @if($isManageMode)
            <a href="{{ route($mainLedgerRouteName, $mainPageQuery) }}" class="gl-nav-link muted">
                <i class="fas fa-book"></i> {{ __('app.finance.main_page') }}
            </a>
        @else
            @permission('finance_report.generate')
                <a href="{{ route($manageLedgerRouteName, $managePageQuery) }}" class="gl-nav-link muted">
                    <i class="fas fa-sliders-h"></i> {{ __('app.finance.import_edit_manual') }}
                </a>
            @endpermission
        @endif
    </div>
</div>

<div class="gl-source-grid">
    <div class="gl-source-card">
        <div class="gl-panel-title">
            <i class="fas fa-database"></i>
            <span>{{ __('app.finance.general_ledger_data_source') }}</span>
        </div>
        <div class="gl-source-links">
            @unless($isManageMode)
                <a href="{{ route($pageRouteName, $combinedSourceQuery) }}" class="gl-source-link {{ $isCombinedSource ? 'is-active' : '' }}">
                    <strong>{{ __('app.finance.combined_data') }}</strong>
                    <span>{{ __('app.finance.general_ledger_combined_note') }}</span>
                </a>
            @endunless
            <a href="{{ route($pageRouteName, $systemSourceQuery) }}" class="gl-source-link {{ !$isImportedSource && !$isCombinedSource ? 'is-active' : '' }}">
                <strong>{{ __('app.finance.system_journal') }}</strong>
                <span>{{ __('app.finance.general_ledger_system_note') }}</span>
            </a>
            <a href="{{ route($pageRouteName, $importedSourceQuery) }}" class="gl-source-link {{ $isImportedSource ? 'is-active' : '' }}">
                <strong>{{ $isManageMode ? __('app.finance.import_manual') : __('app.finance.import_result') }}</strong>
                <span>{{ $isManageMode ? __('app.finance.general_ledger_imported_manage_note') : __('app.finance.general_ledger_imported_note') }}</span>
            </a>
        </div>
    </div>

    @if($usesImportedData)
        <div class="gl-source-card">
            <div class="gl-panel-title">
                <i class="fas fa-layer-group"></i>
                <span>{{ $isManageMode ? __('app.finance.select_import_batch') : __('app.finance.select_import_result') }}</span>
            </div>
            <form method="GET" action="{{ route($pageRouteName) }}" class="gl-batch-form">
                <input type="hidden" name="ledger_source" value="{{ $ledgerSource }}">
                @if($isImportedSource)
                    <input type="hidden" name="period_type" value="{{ data_get($filters, 'period_type', 'ALL') }}">
                @endif
                @foreach($sourceQueryBase as $queryKey => $queryValue)
                    @if($queryKey !== 'period_type' || !$isImportedSource)
                        <input type="hidden" name="{{ $queryKey }}" value="{{ $queryValue }}">
                    @endif
                @endforeach
                <div class="fs-field" style="margin-bottom:0;">
                    <label class="fs-label" for="ledger_batch_id">
                        <i class="fas fa-box-open"></i> {{ __('app.finance.batch') }} {{ __('app.finance.general_ledger') }}
                    </label>
                    <select name="ledger_batch_id" id="ledger_batch_id" class="fs-control" onchange="this.form.submit()">
                        <option value="">{{ __('app.finance.select_import_batch_placeholder') }}</option>
                        @foreach($batchOptions as $batchOption)
                            <option value="{{ $batchOption['id'] }}" {{ $selectedBatchId === $batchOption['id'] ? 'selected' : '' }}>
                                {{ $batchOption['batch_name'] }}
                                @if(!empty($batchOption['imported_year']))
                                    | {{ $batchOption['imported_year'] }}
                                @endif
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="gl-panel-help">
                    {{ $isManageMode
                        ? __('app.finance.general_ledger_batch_manage_help')
                        : ($isCombinedSource
                            ? __('app.finance.general_ledger_batch_combined_help')
                            : __('app.finance.general_ledger_batch_imported_help')) }}
                </div>
            </form>

            @if(!empty($selectedBatchMeta))
                <div class="gl-batch-meta">
                    <div class="gl-batch-stat">
                        <label>{{ __('app.finance.batch') }}</label>
                        <div>{{ data_get($selectedBatchMeta, 'batch_name', '-') }}</div>
                    </div>
                    <div class="gl-batch-stat">
                        <label>{{ __('app.finance.source_file') }}</label>
                        <div>{{ data_get($selectedBatchMeta, 'source_filename', __('app.finance.manual_no_file')) }}</div>
                    </div>
                    <div class="gl-batch-stat">
                        <label>{{ __('app.finance.accounts') }}</label>
                        <div>{{ number_format((int) data_get($selectedBatchMeta, 'account_count', 0), 0, ',', '.') }}</div>
                    </div>
                    <div class="gl-batch-stat">
                        <label>{{ __('app.finance.manual_rows') }}</label>
                        <div>{{ number_format((int) data_get($selectedBatchMeta, 'manual_count', 0), 0, ',', '.') }}</div>
                    </div>
                </div>
            @endif
        </div>
    @endif
</div>

@include('finance.partials.statement-filter', [
    'action' => route($pageRouteName),
    'filters' => $filters,
    'showPerPage' => true,
])

@if($isManageMode && $isImportedSource)
    @permission('finance_report.generate')
        <div class="gl-manage-grid">
            <div class="gl-manage-card">
                <div class="gl-panel-title">
                    <i class="fas fa-file-import"></i>
                    <span>{{ __('app.finance.import_excel_general_ledger') }}</span>
                </div>
                <form method="POST" action="{{ route('finance.report.general-ledger.import') }}" enctype="multipart/form-data" class="gl-manage-form">
                    @csrf
                    <div class="gl-import-guide">
                        <div class="gl-import-guide-title">
                            <i class="fas fa-circle-info"></i>
                            <span>{{ __('app.finance.gl_import_parser_title') }}</span>
                        </div>
                        <div class="gl-panel-help" style="margin-top:0;">
                            {!! __('app.finance.gl_import_parser_desc', [
                                'sheet' => '<strong>'.e(__('app.finance.first_sheet')).'</strong>',
                                'column_a' => '<strong>A</strong>',
                                'format' => '<strong>100.01.01 '.e(__('app.finance.account_name')).'</strong>',
                                'opening_balance' => '<strong>'.e(__('app.finance.opening_balance')).'</strong>',
                            ]) !!}
                        </div>
                        <div class="gl-import-guide-grid">
                            <div class="gl-import-guide-card">
                                <label>{{ __('app.finance.gl_import_account_header') }}</label>
                                <div>{{ __('app.finance.gl_import_account_header_desc') }}</div>
                            </div>
                            <div class="gl-import-guide-card">
                                <label>{{ __('app.finance.gl_import_transaction_row') }}</label>
                                <div>{{ __('app.finance.gl_import_transaction_row_desc') }}</div>
                            </div>
                            <div class="gl-import-guide-card">
                                <label>{{ __('app.finance.opening_balance') }}</label>
                                <div>{{ __('app.finance.gl_import_opening_balance_desc') }}</div>
                            </div>
                        </div>
                        <div class="gl-import-chip-row">
                            <span class="gl-import-chip"><i class="fas fa-hashtag"></i> {{ __('app.finance.gl_import_chip_transaction_header') }}</span>
                            <span class="gl-import-chip"><i class="fas fa-calendar-day"></i> {{ __('app.finance.gl_import_chip_date') }}</span>
                            <span class="gl-import-chip"><i class="fas fa-wallet"></i> {{ __('app.finance.gl_import_chip_amounts') }}</span>
                        </div>
                    </div>
                    <div class="fs-field" style="margin-bottom:0;">
                        <label class="fs-label" for="gl_import_file">
                            <i class="fas fa-file-excel"></i> {{ __('app.finance.file_excel') }}
                        </label>
                        <input type="file" name="file" id="gl_import_file" class="fs-control" accept=".xlsx,.xls,.csv" required>
                        <div class="fs-helper-text">
                            {{ __('app.finance.gl_import_file_help') }}
                        </div>
                    </div>
                    <div class="gl-form-grid">
                        <div class="fs-field gl-col-6" style="margin-bottom:0;">
                            <label class="fs-label" for="gl_import_batch_name">
                                <i class="fas fa-signature"></i> {{ __('app.finance.batch_name') }}
                            </label>
                            <input type="text" name="batch_name" id="gl_import_batch_name" class="fs-control" placeholder="{{ __('app.finance.example_general_ledger_batch') }}">
                            <div class="fs-helper-text">
                                {{ __('app.finance.batch_name_help') }}
                            </div>
                        </div>
                        <div class="fs-field gl-col-6" style="margin-bottom:0;">
                            <label class="fs-label" for="gl_import_notes">
                                <i class="fas fa-sticky-note"></i> {{ __('app.finance.notes') }}
                            </label>
                            <input type="text" name="notes" id="gl_import_notes" class="fs-control" placeholder="{{ __('app.finance.optional') }}">
                            <div class="fs-helper-text">
                                {{ __('app.finance.gl_import_notes_help') }}
                            </div>
                        </div>
                    </div>
                    <button type="submit" class="fs-btn fs-btn-primary">
                        <i class="fas fa-upload"></i>
                        <span>{{ __('app.finance.import_now') }}</span>
                    </button>
                    <div class="gl-panel-help">
                        {{ __('app.finance.gl_import_parser_footer') }}
                    </div>
                </form>
            </div>

            <div class="gl-manage-card">
                <div class="gl-panel-title">
                    <i class="fas fa-pen-to-square"></i>
                    <span>{{ $editEntry ? __('app.finance.edit_general_ledger_row') : __('app.finance.add_general_ledger_row') }}</span>
                </div>
                <form
                    method="POST"
                    action="{{ $editEntry ? route('finance.report.general-ledger.entries.update', $editEntry['id']) : route('finance.report.general-ledger.entries.store') }}"
                    class="gl-manage-form"
                >
                    @csrf
                    @if($editEntry)
                        @method('PUT')
                    @endif
                    <input type="hidden" name="batch_id" value="{{ old('batch_id', $editEntry['batch_id'] ?? $selectedBatchId) }}">
                    @foreach($redirectFields as $queryKey => $queryValue)
                        <input type="hidden" name="{{ $queryKey }}" value="{{ $queryValue }}">
                    @endforeach
                    <div class="gl-form-grid">
                        <div class="fs-field gl-col-3" style="margin-bottom:0;">
                            <label class="fs-label" for="gl_row_type">
                                <i class="fas fa-stream"></i> {{ __('app.finance.row_type') }}
                            </label>
                            <select name="row_type" id="gl_row_type" class="fs-control">
                                <option value="ENTRY" {{ old('row_type', $entryForm['row_type']) === 'ENTRY' ? 'selected' : '' }}>{{ __('app.finance.transaction') }}</option>
                                <option value="OPENING" {{ old('row_type', $entryForm['row_type']) === 'OPENING' ? 'selected' : '' }}>{{ __('app.finance.opening_balance') }}</option>
                            </select>
                        </div>
                        <div class="fs-field gl-col-3" style="margin-bottom:0;">
                            <label class="fs-label" for="gl_entry_date">
                                <i class="fas fa-calendar-day"></i> {{ __('app.finance.date') }}
                            </label>
                            <input type="date" name="entry_date" id="gl_entry_date" class="fs-control" value="{{ old('entry_date', $entryForm['entry_date']) }}">
                        </div>
                        <div class="fs-field gl-col-3" style="margin-bottom:0;">
                            <label class="fs-label" for="gl_account_code">
                                <i class="fas fa-hashtag"></i> {{ __('app.finance.account_code') }}
                            </label>
                            <input type="text" name="account_code" id="gl_account_code" class="fs-control" value="{{ old('account_code', $entryForm['account_code']) }}" placeholder="100.02.01">
                        </div>
                        <div class="fs-field gl-col-3" style="margin-bottom:0;">
                            <label class="fs-label" for="gl_currency">
                                <i class="fas fa-coins"></i> {{ __('app.finance.currency') }}
                            </label>
                            <input type="text" name="currency" id="gl_currency" class="fs-control" value="{{ old('currency', $entryForm['currency']) }}" placeholder="IDR">
                        </div>
                        <div class="fs-field gl-col-12" style="margin-bottom:0;">
                            <label class="fs-label" for="gl_account_name">
                                <i class="fas fa-font"></i> {{ __('app.finance.account_name') }}
                            </label>
                            <input type="text" name="account_name" id="gl_account_name" class="fs-control" value="{{ old('account_name', $entryForm['account_name']) }}" placeholder="{{ __('app.finance.account_name') }}">
                        </div>
                        <div class="fs-field gl-col-4" style="margin-bottom:0;">
                            <label class="fs-label" for="gl_transaction_no">
                                <i class="fas fa-receipt"></i> {{ __('app.finance.transaction_no') }}
                            </label>
                            <input type="text" name="transaction_no" id="gl_transaction_no" class="fs-control" value="{{ old('transaction_no', $entryForm['transaction_no']) }}">
                        </div>
                        <div class="fs-field gl-col-4" style="margin-bottom:0;">
                            <label class="fs-label" for="gl_communication">
                                <i class="fas fa-comments"></i> {{ __('app.finance.communication') }}
                            </label>
                            <input type="text" name="communication" id="gl_communication" class="fs-control" value="{{ old('communication', $entryForm['communication']) }}">
                        </div>
                        <div class="fs-field gl-col-4" style="margin-bottom:0;">
                            <label class="fs-label" for="gl_partner_name">
                                <i class="fas fa-handshake"></i> {{ __('app.finance.partner') }}
                            </label>
                            <input type="text" name="partner_name" id="gl_partner_name" class="fs-control" value="{{ old('partner_name', $entryForm['partner_name']) }}">
                        </div>
                        <div class="fs-field gl-col-6" style="margin-bottom:0;">
                            <label class="fs-label" for="gl_label">
                                <i class="fas fa-align-left"></i> {{ __('app.finance.description') }}
                            </label>
                            <input type="text" name="label" id="gl_label" class="fs-control" value="{{ old('label', $entryForm['label']) }}">
                        </div>
                        <div class="fs-field gl-col-3" style="margin-bottom:0;">
                            <label class="fs-label" for="gl_reference">
                                <i class="fas fa-link"></i> {{ __('app.finance.reference') }}
                            </label>
                            <input type="text" name="reference" id="gl_reference" class="fs-control" value="{{ old('reference', $entryForm['reference']) }}">
                        </div>
                        <div class="fs-field gl-col-3" style="margin-bottom:0;">
                            <label class="fs-label" for="gl_analytic_distribution">
                                <i class="fas fa-project-diagram"></i> {{ __('app.finance.analytic') }}
                            </label>
                            <input type="text" name="analytic_distribution" id="gl_analytic_distribution" class="fs-control" value="{{ old('analytic_distribution', $entryForm['analytic_distribution']) }}">
                        </div>
                        <div class="fs-field gl-col-4" style="margin-bottom:0;">
                            <label class="fs-label" for="gl_opening_balance">
                                <i class="fas fa-wallet"></i> {{ __('app.finance.opening_balance') }}
                            </label>
                            <input type="number" step="0.01" name="opening_balance" id="gl_opening_balance" class="fs-control" value="{{ old('opening_balance', $entryForm['opening_balance']) }}">
                        </div>
                        <div class="fs-field gl-col-4" style="margin-bottom:0;">
                            <label class="fs-label" for="gl_debit">
                                <i class="fas fa-arrow-up"></i> Debit
                            </label>
                            <input type="number" step="0.01" name="debit" id="gl_debit" class="fs-control" value="{{ old('debit', $entryForm['debit']) }}">
                        </div>
                        <div class="fs-field gl-col-4" style="margin-bottom:0;">
                            <label class="fs-label" for="gl_credit">
                                <i class="fas fa-arrow-down"></i> Kredit
                            </label>
                            <input type="number" step="0.01" name="credit" id="gl_credit" class="fs-control" value="{{ old('credit', $entryForm['credit']) }}">
                        </div>
                    </div>
                    <div class="gl-form-actions">
                        <button type="submit" class="fs-btn fs-btn-primary">
                            <i class="fas fa-save"></i>
                            <span>{{ $editEntry ? __('app.finance.update_row') : __('app.finance.add_row') }}</span>
                        </button>
                        @if($editEntry)
                            <a href="{{ route($pageRouteName, array_merge($filterQuery, ['ledger_source' => 'imported', 'ledger_batch_id' => $selectedBatchId])) }}" class="fs-btn fs-btn-muted">
                                <i class="fas fa-times-circle"></i>
                                <span>{{ __('app.finance.cancel_edit') }}</span>
                            </a>
                        @endif
                    </div>
                </form>
            </div>
        </div>
    @endpermission
@endif

@if(!empty($selectedAccountCode))
    <div class="gl-filter-note">
        <div>
            <span class="gl-filter-badge">
                <i class="fas fa-filter"></i>
                {{ __('app.finance.focused_account', ['account' => $selectedAccountCode . ($selectedAccountName ? ' - ' . $selectedAccountName : '')]) }}
            </span>
            <small>{{ __('app.finance.focused_account_note') }}</small>
        </div>
        <a href="{{ route($pageRouteName, array_merge($baseFilterQuery, ['ledger_source' => $ledgerSource], $selectedBatchId ? ['ledger_batch_id' => $selectedBatchId] : [])) }}" class="gl-reset-link">
            <i class="fas fa-times-circle"></i> {{ __('app.finance.view_all_accounts') }}
        </a>
    </div>
@endif

<div class="gl-summary-grid">
    <div class="gl-summary-card">
        <div class="gl-summary-label"><i class="fas fa-sitemap"></i> {{ __('app.finance.account_count') }}</div>
        <div class="gl-summary-value">{{ number_format((int) ($summary['account_count'] ?? 0), 0, ',', '.') }}</div>
        <div class="gl-summary-help">{{ $isCombinedSource ? __('app.finance.gl_account_count_help_combined') : ($isImportedSource ? __('app.finance.gl_account_count_help_imported') : __('app.finance.gl_account_count_help_system')) }}</div>
    </div>
    <div class="gl-summary-card">
        <div class="gl-summary-label"><i class="fas fa-list-ul"></i> {{ __('app.finance.journal_rows') }}</div>
        <div class="gl-summary-value">{{ number_format((int) ($summary['entry_count'] ?? 0), 0, ',', '.') }}</div>
        <div class="gl-summary-help">{{ $isCombinedSource ? __('app.finance.gl_journal_rows_help_combined') : ($isImportedSource ? __('app.finance.gl_journal_rows_help_imported') : __('app.finance.gl_journal_rows_help_system')) }}</div>
    </div>
    <div class="gl-summary-card">
        <div class="gl-summary-label"><i class="fas fa-arrow-up"></i> {{ __('app.finance.total_debit') }}</div>
        <div class="gl-summary-value">Rp {{ number_format((float) ($summary['total_debit'] ?? 0), 2, ',', '.') }}</div>
        <div class="gl-summary-help">{{ __('app.finance.total_debit_help') }}</div>
    </div>
    <div class="gl-summary-card">
        <div class="gl-summary-label"><i class="fas fa-arrow-down"></i> {{ __('app.finance.total_credit') }}</div>
        <div class="gl-summary-value">Rp {{ number_format((float) ($summary['total_credit'] ?? 0), 2, ',', '.') }}</div>
        <div class="gl-summary-help">{{ __('app.finance.total_credit_help') }}</div>
    </div>
    <div class="gl-summary-card">
        <div class="gl-summary-label"><i class="fas fa-scale-balanced"></i> {{ __('app.finance.balance_difference') }}</div>
        <div class="gl-summary-value" style="color: {{ (float) ($summary['balance_gap'] ?? 0) === 0.0 ? 'var(--gl-green)' : 'var(--gl-red)' }};">
            Rp {{ number_format((float) ($summary['balance_gap'] ?? 0), 2, ',', '.') }}
        </div>
        <div class="gl-summary-help">{{ __('app.finance.balance_gap_help') }}</div>
    </div>
    @if($usesImportedData && !empty($selectedBatchMeta))
        <div class="gl-summary-card">
            <div class="gl-summary-label"><i class="fas fa-database"></i> {{ __('app.finance.active_batch') }}</div>
            <div class="gl-summary-value" style="font-size:1rem;">{{ data_get($selectedBatchMeta, 'batch_name', '-') }}</div>
            <div class="gl-summary-help">{{ data_get($selectedBatchMeta, 'source_filename', __('app.finance.manual_no_file')) }}</div>
        </div>
    @endif
</div>

@if(!empty($groups))
    <div class="gl-ledger-list">
        @foreach($groups as $group)
            <div class="gl-ledger-card">
                <div class="gl-ledger-head">
                    <div>
                        <div class="gl-ledger-title">
                            <span class="gl-ledger-icon"><i class="fas fa-book"></i></span>
                            <span>{{ $group['account_code'] }} - {{ $group['account_name'] }}</span>
                        </div>
                        <div class="gl-ledger-sub">
                            <div class="gl-ledger-meta">
                                <span class="gl-badge">
                                    <i class="fas fa-tag"></i>
                                    {{ $group['finance_type'] !== '' ? str_replace('_', ' ', $group['finance_type']) : __('app.finance.no_type') }}
                                </span>
                                <span class="gl-badge {{ $group['normal_side'] === 'CREDIT' ? 'red' : 'green' }}">
                                    <i class="fas fa-arrows-alt-h"></i>
                                    {{ __('app.finance.normal_balance', ['side' => $group['normal_side']]) }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="gl-ledger-totals">
                        <div class="gl-ledger-total">
                            <label>{{ __('app.finance.total_debit') }}</label>
                            <div>Rp {{ number_format((float) $group['total_debit'], 2, ',', '.') }}</div>
                        </div>
                        <div class="gl-ledger-total">
                            <label>{{ __('app.finance.total_credit') }}</label>
                            <div>Rp {{ number_format((float) $group['total_credit'], 2, ',', '.') }}</div>
                        </div>
                        <div class="gl-ledger-total">
                            <label>{{ __('app.finance.ending_balance') }}</label>
                            <div>Rp {{ number_format((float) $group['closing_balance'], 2, ',', '.') }}</div>
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="gl-table">
                        <thead>
                            <tr>
                                <th style="width:120px;">{{ __('app.finance.date') }}</th>
                                <th style="width:160px;">{{ $isCombinedSource ? __('app.finance.document_no') : ($isImportedSource ? __('app.finance.transaction_no') : __('app.finance.journal_no')) }}</th>
                                <th style="width:190px;">{{ $isCombinedSource ? __('app.finance.communication_journal') : ($isImportedSource ? __('app.finance.communication') : __('app.finance.journal_name')) }}</th>
                                <th>{{ __('app.finance.description') }}</th>
                                <th style="width:150px; text-align:right;">{{ __('app.finance.debit') }}</th>
                                <th style="width:150px; text-align:right;">{{ __('app.finance.credit') }}</th>
                                <th style="width:160px; text-align:right;">{{ __('app.finance.ending_balance') }}</th>
                                @if($isImportedSource && $isManageMode)
                                    @permission('finance_report.generate')
                                        <th style="width:170px; text-align:right;">{{ __('app.finance.action') }}</th>
                                    @endpermission
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($group['entries'] as $entry)
                                @php
                                    $entryHasJournalSource = (bool) ($entry['has_journal_source'] ?? !$isImportedSource);
                                    $entryHasImportedSource = (bool) ($entry['has_imported_source'] ?? $isImportedSource);
                                    $entryManageRoute = route($manageLedgerRouteName, array_filter(array_merge($baseFilterQuery ?? [], [
                                        'ledger_source' => 'imported',
                                        'ledger_batch_id' => $entry['imported_batch_id'] ?? $selectedBatchId,
                                        'account_code' => $group['account_code'] ?? null,
                                        'edit_entry' => ($entryHasImportedSource && !empty($entry['entry_id'])) ? $entry['entry_id'] : null,
                                    ]), static fn ($value): bool => $value !== null && $value !== ''));
                                @endphp
                                <tr>
                                    <td>
                                        @if(!empty($entry['accounting_date']))
                                            {{ \Carbon\Carbon::parse($entry['accounting_date'])->format('d/m/Y') }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>
                                        <strong>{{ $entry['invoice_no'] }}</strong>
                                        @if($entryHasJournalSource && !empty($entry['invoice_id']))
                                            <a href="{{ route('finance.invoice.show', $entry['invoice_id']) }}" class="gl-journal-link">
                                                <i class="fas fa-folder-open"></i> {{ __('app.finance.journal_items') }}
                                            </a>
                                        @endif
                                        @if($entryHasImportedSource)
                                            <div class="gl-chip {{ !empty($entry['is_manual']) ? 'manual' : 'imported' }}">
                                                <i class="fas {{ !empty($entry['is_manual']) ? 'fa-pen' : 'fa-file-import' }}"></i>
                                                {{ !empty($entry['is_manual']) ? __('app.finance.manual') : __('app.finance.import') }}
                                            </div>
                                        @elseif($isCombinedSource)
                                            <div class="gl-chip">
                                                <i class="fas fa-server"></i>
                                                {{ __('app.finance.journal') }}
                                            </div>
                                        @endif
                                    </td>
                                    <td>{{ $entry['journal_name'] }}</td>
                                    <td>
                                        <div class="gl-entry-title">{{ $entry['label'] }}</div>
                                        <div class="gl-entry-sub">
                                            @if(!empty($entry['partner_name']))
                                                {{ __('app.finance.partner_label') }}: {{ $entry['partner_name'] }}<br>
                                            @endif
                                            @if(!empty($entry['reference']))
                                                {{ __('app.finance.ref_label') }}: {{ $entry['reference'] }}<br>
                                            @endif
                                            @if(!empty($entry['analytic_distribution']))
                                                {{ __('app.finance.analytic') }}: {{ $entry['analytic_distribution'] }}<br>
                                            @endif
                                            @if($entryHasImportedSource && !empty($entry['currency']))
                                                {{ __('app.finance.currency_label') }}: {{ $entry['currency'] }}
                                            @endif
                                        </div>
                                        @if($isCombinedSource && $entryHasImportedSource)
                                            <div class="mt-2">
                                                <a href="{{ $entryManageRoute }}" class="gl-row-link">
                                                    <i class="fas fa-file-import"></i> {{ __('app.finance.manage_import') }}
                                                </a>
                                            </div>
                                        @endif
                                    </td>
                                    <td class="gl-amount debit">Rp {{ number_format((float) $entry['debit'], 2, ',', '.') }}</td>
                                    <td class="gl-amount credit">Rp {{ number_format((float) $entry['credit'], 2, ',', '.') }}</td>
                                    <td class="gl-amount balance">Rp {{ number_format((float) $entry['running_balance'], 2, ',', '.') }}</td>
                                    @if($isImportedSource && $isManageMode)
                                        @permission('finance_report.generate')
                                            <td class="gl-action-cell">
                                                @if(!empty($entry['can_edit']) && !empty($entry['entry_id']))
                                                    <div class="gl-row-actions">
                                                        <a
                                                            href="{{ route($pageRouteName, array_merge($filterQuery, ['ledger_source' => 'imported', 'ledger_batch_id' => $selectedBatchId, 'edit_entry' => $entry['entry_id']])) }}"
                                                            class="gl-row-link"
                                                        >
                                                            <i class="fas fa-pen"></i> {{ __('app.finance.edit') }}
                                                        </a>
                                                        <form method="POST" action="{{ route('finance.report.general-ledger.entries.destroy', $entry['entry_id']) }}" class="gl-inline-form" onsubmit="return confirm(@json(__('app.finance.delete_general_ledger_row_confirm')))">
                                                            @csrf
                                                            @method('DELETE')
                                                            @foreach($filterQuery as $queryKey => $queryValue)
                                                                @if($queryValue !== null && $queryValue !== '')
                                                                    <input type="hidden" name="{{ $queryKey }}" value="{{ $queryValue }}">
                                                                @endif
                                                            @endforeach
                                                            <button type="submit" class="gl-row-btn danger">
                                                                <i class="fas fa-trash"></i> {{ __('app.finance.delete') }}
                                                            </button>
                                                        </form>
                                                    </div>
                                                @endif
                                            </td>
                                        @endpermission
                                    @endif
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ $isImportedSource && $isManageMode ? 8 : 7 }}" style="text-align:center; color:var(--gl-muted);">{{ __('app.finance.no_journal_rows_for_account') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @endforeach
    </div>

    @if($accounts && method_exists($accounts, 'links') && $accounts->hasPages())
        <div class="gl-pagination">
            {{ $accounts->appends(request()->query())->links() }}
        </div>
    @endif
@else
    <div class="gl-empty-card">
        <i class="fas fa-book-dead"></i>
        <h4>{{ __('app.finance.no_general_ledger_data') }}</h4>
        <div>
            {!! $isCombinedSource
                ? __('app.finance.no_general_ledger_combined_note', ['status' => '<strong>'.e(__('app.finance.posted')).'</strong>', 'mode' => '<strong>'.e(__('app.finance.import_edit_manual')).'</strong>'])
                : ($isImportedSource
                ? ($isManageMode
                    ? __('app.finance.no_general_ledger_imported_manage_note', ['import_excel' => '<strong>'.e(__('app.finance.import_excel')).'</strong>'])
                    : __('app.finance.no_general_ledger_imported_note', ['mode' => '<strong>'.e(__('app.finance.import_edit_manual')).'</strong>']))
                : __('app.finance.no_general_ledger_system_note', ['status' => '<strong>'.e(__('app.finance.posted')).'</strong>'])) !!}
        </div>
    </div>
@endif
@endsection
