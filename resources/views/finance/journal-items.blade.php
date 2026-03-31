@extends('layouts.app')

@section('content')
@php
    $summary = $report['summary'] ?? ['entry_count' => 0, 'selected_count' => 0, 'total_debit' => 0, 'total_credit' => 0, 'total_amount' => 0];
    $items = $report['items'] ?? null;
    $account = $report['account'] ?? ['code' => null, 'name' => null];
    $journalItems = $items && method_exists($items, 'items') ? $items->items() : (is_array($items) ? $items : []);
    $searchTerm = $filters['search'] ?? null;
    $statementSourceLabel = $statementSourceLabel ?? 'Laporan Finance';
    $statementBackUrl = $statementBackUrl ?? route('finance.dashboard');
    $selectedItemCount = count(request()->query('selected_ids', []));
@endphp

<style>
    @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');

    :root {
        --ji-blue: #1d4ed8;
        --ji-blue-dark: #1e3a8a;
        --ji-green: #059669;
        --ji-red: #dc2626;
        --ji-amber: #d97706;
        --ji-bg: #f0f4fd;
        --ji-card: #ffffff;
        --ji-card-soft: #f8fbff;
        --ji-text: #0f172a;
        --ji-text-soft: #334155;
        --ji-muted: #64748b;
        --ji-border: rgba(37, 99, 235, 0.10);
        --ji-shadow: 0 10px 32px rgba(15, 23, 42, 0.08), 0 4px 14px rgba(37, 99, 235, 0.06);
        --ji-radius: 18px;
        --ji-radius-sm: 12px;
    }

    body, .content-wrapper {
        background: var(--ji-bg) !important;
        font-family: 'Plus Jakarta Sans', sans-serif !important;
    }

    .ji-page-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        flex-wrap: wrap;
        margin-bottom: 1.5rem;
    }
    .ji-page-title {
        display: flex;
        align-items: center;
        gap: 0.9rem;
    }
    .ji-title-icon {
        width: 50px;
        height: 50px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, var(--ji-blue), var(--ji-blue-dark));
        color: #fff;
        font-size: 1.2rem;
        box-shadow: var(--ji-shadow);
    }
    .ji-page-title h1 {
        margin: 0;
        color: var(--ji-text);
        font-size: 1.4rem;
        font-weight: 800;
    }
    .ji-page-title p {
        margin: 0.15rem 0 0;
        color: var(--ji-muted);
        font-size: 0.82rem;
        font-weight: 500;
    }
    .ji-nav {
        display: flex;
        flex-wrap: wrap;
        gap: 0.55rem;
    }
    .ji-nav-link,
    .ji-btn {
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
        cursor: pointer;
    }
    .ji-nav-link:hover,
    .ji-btn:hover {
        text-decoration: none;
        transform: translateY(-1px);
    }
    .ji-nav-link.primary,
    .ji-btn-primary {
        background: linear-gradient(135deg, var(--ji-blue), #2563eb);
        color: #fff;
        box-shadow: 0 8px 22px rgba(37, 99, 235, 0.24);
    }
    .ji-nav-link.muted,
    .ji-btn-muted {
        background: #fff;
        color: var(--ji-muted);
        border-color: var(--ji-border);
    }
    .ji-card,
    .ji-summary-card,
    .ji-table-card,
    .fs-filter-card {
        background: var(--ji-card);
        border: 1px solid var(--ji-border);
        border-radius: var(--ji-radius);
        box-shadow: var(--ji-shadow);
    }
    .ji-context-card {
        margin-bottom: 1rem;
        padding: 1rem 1.1rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 0.9rem;
    }
    .ji-context-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        border-radius: 999px;
        padding: 0.35rem 0.9rem;
        background: rgba(37, 99, 235, 0.08);
        color: var(--ji-blue);
        font-size: 0.78rem;
        font-weight: 800;
    }
    .ji-context-help {
        color: var(--ji-muted);
        font-size: 0.76rem;
        font-weight: 500;
    }
    .ji-summary-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 0.9rem;
        margin: 1.2rem 0;
    }
    .ji-summary-card {
        padding: 1rem 1.1rem;
    }
    .ji-summary-label {
        display: flex;
        align-items: center;
        gap: 0.45rem;
        color: var(--ji-muted);
        font-size: 0.72rem;
        font-weight: 800;
        letter-spacing: 0.06em;
        text-transform: uppercase;
        margin-bottom: 0.45rem;
    }
    .ji-summary-value {
        color: var(--ji-text);
        font-size: 1.25rem;
        font-weight: 800;
        line-height: 1.15;
    }
    .ji-toolbar {
        padding: 1rem 1.1rem;
        border-bottom: 1px solid var(--ji-border);
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.9rem;
        flex-wrap: wrap;
    }
    .ji-toolbar-left,
    .ji-toolbar-right {
        display: flex;
        align-items: center;
        gap: 0.7rem;
        flex-wrap: wrap;
    }
    .ji-select-all {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        color: var(--ji-text);
        font-size: 0.8rem;
        font-weight: 700;
    }
    .ji-select-chip {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.35rem 0.8rem;
        border-radius: 999px;
        background: rgba(16, 185, 129, 0.10);
        color: var(--ji-green);
        font-size: 0.74rem;
        font-weight: 800;
    }
    .ji-search-form {
        display: flex;
        align-items: center;
        gap: 0.55rem;
        flex-wrap: wrap;
    }
    .ji-search-box {
        position: relative;
        min-width: 320px;
    }
    .ji-search-box i {
        position: absolute;
        top: 50%;
        left: 0.85rem;
        transform: translateY(-50%);
        color: var(--ji-muted);
        font-size: 0.8rem;
    }
    .ji-search-input {
        width: 100%;
        border: 1.5px solid rgba(148, 163, 184, 0.18);
        border-radius: var(--ji-radius-sm);
        padding: 0.65rem 0.85rem 0.65rem 2.3rem;
        font-size: 0.84rem;
        color: var(--ji-text);
        background: #fff;
    }
    .ji-search-input:focus {
        outline: none;
        border-color: rgba(37, 99, 235, 0.4);
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
    }
    .ji-table-wrap {
        overflow: auto;
    }
    .ji-table {
        width: 100%;
        border-collapse: collapse;
        min-width: 1340px;
    }
    .ji-table th {
        background: var(--ji-card-soft);
        color: var(--ji-muted);
        font-size: 0.68rem;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        padding: 0.8rem 0.9rem;
        border-bottom: 1px solid var(--ji-border);
        white-space: nowrap;
    }
    .ji-table td {
        padding: 0.82rem 0.9rem;
        font-size: 0.8rem;
        color: var(--ji-text-soft);
        border-bottom: 1px solid rgba(148, 163, 184, 0.12);
        vertical-align: top;
    }
    .ji-table tbody tr:last-child td {
        border-bottom: none;
    }
    .ji-table tbody tr:hover td {
        background: rgba(37, 99, 235, 0.03);
    }
    .ji-checkbox-col {
        width: 48px;
        text-align: center;
    }
    .ji-checkbox {
        width: 16px;
        height: 16px;
        accent-color: var(--ji-blue);
    }
    .ji-amount {
        text-align: right;
        white-space: nowrap;
        font-weight: 800;
    }
    .ji-amount.blue { color: var(--ji-blue); }
    .ji-amount.green { color: var(--ji-green); }
    .ji-amount.red { color: var(--ji-red); }
    .ji-account {
        color: var(--ji-text);
        font-weight: 700;
    }
    .ji-entry-link {
        color: var(--ji-blue);
        font-weight: 700;
        text-decoration: none;
    }
    .ji-entry-link:hover {
        color: var(--ji-blue-dark);
        text-decoration: none;
    }
    .ji-pill {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        border-radius: 999px;
        padding: 0.25rem 0.65rem;
        background: rgba(37, 99, 235, 0.08);
        color: var(--ji-blue);
        font-size: 0.68rem;
        font-weight: 800;
    }
    .ji-row-action {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.3rem 0.65rem;
        border-radius: 999px;
        background: rgba(37, 99, 235, 0.08);
        border: 1px solid rgba(37, 99, 235, 0.14);
        color: var(--ji-blue);
        font-size: 0.7rem;
        font-weight: 800;
        text-decoration: none;
    }
    .ji-row-action:hover {
        background: var(--ji-blue);
        color: #fff;
        text-decoration: none;
    }
    .ji-pagination {
        margin-top: 1rem;
        padding: 0.95rem 1rem;
        background: var(--ji-card);
        border: 1px solid var(--ji-border);
        border-radius: var(--ji-radius);
        box-shadow: var(--ji-shadow);
    }
    .ji-empty {
        padding: 2.5rem 1.2rem;
        text-align: center;
        color: var(--ji-muted);
    }
    .ji-empty i {
        font-size: 2.2rem;
        margin-bottom: 0.75rem;
        color: rgba(37, 99, 235, 0.25);
    }

    body.dark-mode,
    body.dark-mode .content-wrapper {
        background: var(--app-bg) !important;
    }
    body.dark-mode .ji-card,
    body.dark-mode .ji-summary-card,
    body.dark-mode .ji-table-card,
    body.dark-mode .ji-context-card,
    body.dark-mode .ji-pagination,
    body.dark-mode .fs-filter-card {
        background: var(--app-surface) !important;
        border-color: var(--app-border) !important;
        box-shadow: var(--app-shadow) !important;
    }
    body.dark-mode .ji-page-title h1,
    body.dark-mode .ji-summary-value,
    body.dark-mode .ji-account,
    body.dark-mode .ji-select-all,
    body.dark-mode .ji-table td strong {
        color: var(--app-text) !important;
    }
    body.dark-mode .ji-page-title p,
    body.dark-mode .ji-summary-label,
    body.dark-mode .ji-context-help,
    body.dark-mode .ji-table th {
        color: var(--app-text-muted) !important;
    }
    body.dark-mode .ji-nav-link.muted,
    body.dark-mode .ji-btn-muted,
    body.dark-mode .ji-search-input {
        background: var(--app-surface-soft) !important;
        border-color: var(--app-border) !important;
        color: var(--app-text) !important;
    }
    body.dark-mode .ji-search-box i {
        color: var(--app-text-muted) !important;
    }
    body.dark-mode .ji-table th {
        background: var(--app-surface-soft) !important;
        border-color: var(--app-border) !important;
    }
    body.dark-mode .ji-table td {
        color: var(--app-text-soft) !important;
        border-color: var(--app-border) !important;
    }
    body.dark-mode .ji-table tbody tr:hover td {
        background: var(--app-row-hover) !important;
    }
    body.dark-mode .ji-entry-link,
    body.dark-mode .ji-row-action,
    body.dark-mode .ji-context-badge,
    body.dark-mode .ji-pill {
        color: var(--app-text) !important;
    }
    body.dark-mode .ji-row-action,
    body.dark-mode .ji-context-badge,
    body.dark-mode .ji-pill {
        background: rgba(96, 165, 250, 0.12) !important;
        border-color: rgba(96, 165, 250, 0.18) !important;
    }
    body.dark-mode .ji-row-action:hover {
        background: var(--app-accent) !important;
        color: #fff !important;
    }
    body.dark-mode .ji-pagination .page-link {
        background: var(--app-surface-soft) !important;
        border-color: var(--app-border) !important;
        color: var(--app-text-soft) !important;
    }
    body.dark-mode .ji-pagination .page-item.active .page-link {
        background: var(--app-accent) !important;
        border-color: var(--app-accent) !important;
        color: #fff !important;
    }
    @media (max-width: 991px) {
        .ji-search-box {
            min-width: 100%;
        }
        .ji-search-form {
            width: 100%;
        }
    }
</style>

<div class="ji-page-header">
    <div class="ji-page-title">
        <div class="ji-title-icon"><i class="fas fa-table"></i></div>
        <div>
            <h1>{{ $statementSourceLabel }} · Item Jurnal</h1>
            <p>Rincian item jurnal untuk akun {{ !empty($account['code']) ? $account['code'] : 'terpilih' }} pada periode {{ $periodLabel }}.</p>
        </div>
    </div>

    <div class="ji-nav">
        <a href="{{ $statementBackUrl }}" class="ji-nav-link muted">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
        <a href="{{ route('finance.report.general-ledger', array_merge($baseFilterQuery, ['account_code' => $account['code']])) }}" class="ji-nav-link muted">
            <i class="fas fa-book-open"></i> Buku Besar
        </a>
    </div>
</div>

<div class="ji-card ji-context-card">
    <div>
        <span class="ji-context-badge">
            <i class="fas fa-filter"></i>
            {{ !empty($account['code']) ? '[' . $account['code'] . '] ' . ($account['name'] ?? '-') : 'Semua akun' }}
        </span>
        <div class="ji-context-help">Klik checkbox untuk memilih item jurnal yang ingin ikut diekspor. Jika tidak ada yang dipilih, ekspor memakai seluruh hasil filter aktif.</div>
    </div>
    <div class="ji-context-help">Sumber: {{ $statementSourceLabel }}</div>
</div>

@include('finance.partials.statement-filter', [
    'action' => route('finance.report.journal-items'),
    'filters' => $filters,
    'showPerPage' => true,
])

<div class="ji-summary-grid">
    <div class="ji-summary-card">
        <div class="ji-summary-label"><i class="fas fa-list-ul"></i> Baris Jurnal</div>
        <div class="ji-summary-value">{{ number_format((int) ($summary['entry_count'] ?? 0), 0, ',', '.') }}</div>
    </div>
    <div class="ji-summary-card">
        <div class="ji-summary-label"><i class="fas fa-money-bill-wave"></i> Nilai Jurnal</div>
        <div class="ji-summary-value">Rp {{ number_format((float) ($summary['total_amount'] ?? 0), 2, ',', '.') }}</div>
    </div>
    <div class="ji-summary-card">
        <div class="ji-summary-label"><i class="fas fa-arrow-up"></i> Total Debit</div>
        <div class="ji-summary-value">Rp {{ number_format((float) ($summary['total_debit'] ?? 0), 2, ',', '.') }}</div>
    </div>
    <div class="ji-summary-card">
        <div class="ji-summary-label"><i class="fas fa-arrow-down"></i> Total Kredit</div>
        <div class="ji-summary-value">Rp {{ number_format((float) ($summary['total_credit'] ?? 0), 2, ',', '.') }}</div>
    </div>
</div>

<div class="ji-table-card">
    <div class="ji-toolbar">
        <div class="ji-toolbar-left">
            <label class="ji-select-all">
                <input type="checkbox" class="ji-checkbox" id="ji-select-all">
                <span>Pilih Semua</span>
            </label>
            <span class="ji-select-chip" id="ji-selected-chip">
                <i class="fas fa-check-circle"></i>
                <span id="ji-selected-count">{{ $selectedItemCount }}</span> item dipilih
            </span>
        </div>

        <div class="ji-toolbar-right">
            <form method="GET" action="{{ route('finance.report.journal-items') }}" class="ji-search-form">
                @foreach($filterQuery as $key => $value)
                    @if(!in_array($key, ['search'], true))
                        <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                    @endif
                @endforeach
                <div class="ji-search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" name="search" class="ji-search-input" placeholder="Cari entri jurnal, label, rekanan, referensi, atau akun..." value="{{ $searchTerm }}">
                </div>
                <button type="submit" class="ji-btn ji-btn-muted">
                    <i class="fas fa-search"></i> Cari
                </button>
            </form>

            <button type="button" class="ji-btn ji-btn-muted ji-export-btn" data-format="excel">
                <i class="fas fa-file-excel"></i> Excel
            </button>
            <button type="button" class="ji-btn ji-btn-primary ji-export-btn" data-format="pdf">
                <i class="fas fa-file-pdf"></i> PDF
            </button>
        </div>
    </div>

    <div class="ji-table-wrap">
        <table class="ji-table">
            <thead>
                <tr>
                    <th class="ji-checkbox-col"></th>
                    <th>Tanggal</th>
                    <th>Entri Jurnal</th>
                    <th>Akun</th>
                    <th>Rekanan</th>
                    <th>Label</th>
                    <th>Pajak</th>
                    <th>Jumlah</th>
                    <th>Debit</th>
                    <th>Kredit</th>
                    <th>Tax Grids</th>
                    <th>Analisa Distribusi</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($journalItems as $item)
                    <tr>
                        <td class="ji-checkbox-col">
                            <input type="checkbox" class="ji-checkbox ji-item-checkbox" value="{{ $item['item_id'] }}">
                        </td>
                        <td>{{ \Carbon\Carbon::parse($item['accounting_date'])->format('d/m/Y') }}</td>
                        <td>
                            <a href="{{ route('finance.invoice.show', $item['invoice_id']) }}" class="ji-entry-link">
                                {{ $item['invoice_no'] }}
                            </a>
                            <div class="ji-context-help">{{ $item['journal_name'] }}</div>
                        </td>
                        <td>
                            <div class="ji-account">{{ $item['account_code'] }}</div>
                            <div>{{ $item['account_name'] }}</div>
                        </td>
                        <td>{{ $item['partner_name'] ?? '-' }}</td>
                        <td>{{ $item['label'] }}</td>
                        <td><span class="ji-pill">{{ $item['tax_label'] ?? '-' }}</span></td>
                        <td class="ji-amount blue">Rp {{ number_format((float) $item['amount_currency'], 2, ',', '.') }}</td>
                        <td class="ji-amount green">Rp {{ number_format((float) $item['debit'], 2, ',', '.') }}</td>
                        <td class="ji-amount red">Rp {{ number_format((float) $item['credit'], 2, ',', '.') }}</td>
                        <td>{{ $item['tax_grids'] ?? '-' }}</td>
                        <td>{{ $item['analytic_distribution'] ?? '-' }}</td>
                        <td>
                            <a href="{{ route('finance.invoice.show', $item['invoice_id']) }}" class="ji-row-action">
                                <i class="fas fa-folder-open"></i> Item Jurnal
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="13">
                            <div class="ji-empty">
                                <i class="fas fa-inbox"></i>
                                <div>Belum ada item jurnal untuk filter aktif.</div>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@if($items && method_exists($items, 'links') && $items->hasPages())
    <div class="ji-pagination">
        {{ $items->appends(request()->query())->links() }}
    </div>
@endif

<form method="GET" action="{{ route('finance.report.journal-items.download') }}" id="ji-export-form" style="display:none;">
    @foreach($filterQuery as $key => $value)
        <input type="hidden" name="{{ $key }}" value="{{ $value }}">
    @endforeach
    <input type="hidden" name="format" id="ji-export-format" value="pdf">
    <div id="ji-selected-fields"></div>
</form>

@push('component_js')
    <script>
        (function () {
            const selectAll = document.getElementById('ji-select-all');
            const checkboxes = Array.from(document.querySelectorAll('.ji-item-checkbox'));
            const selectedCount = document.getElementById('ji-selected-count');
            const exportForm = document.getElementById('ji-export-form');
            const exportFormat = document.getElementById('ji-export-format');
            const selectedFields = document.getElementById('ji-selected-fields');
            const exportButtons = Array.from(document.querySelectorAll('.ji-export-btn'));

            function updateSelectedCount() {
                const checkedCount = checkboxes.filter(function (checkbox) {
                    return checkbox.checked;
                }).length;

                if (selectedCount) {
                    selectedCount.textContent = checkedCount.toString();
                }

                if (selectAll) {
                    selectAll.checked = checkedCount > 0 && checkedCount === checkboxes.length;
                    selectAll.indeterminate = checkedCount > 0 && checkedCount < checkboxes.length;
                }
            }

            if (selectAll) {
                selectAll.addEventListener('change', function () {
                    checkboxes.forEach(function (checkbox) {
                        checkbox.checked = selectAll.checked;
                    });
                    updateSelectedCount();
                });
            }

            checkboxes.forEach(function (checkbox) {
                checkbox.addEventListener('change', updateSelectedCount);
            });

            exportButtons.forEach(function (button) {
                button.addEventListener('click', function () {
                    if (!exportForm || !exportFormat || !selectedFields) {
                        return;
                    }

                    exportFormat.value = button.dataset.format || 'pdf';
                    selectedFields.innerHTML = '';

                    checkboxes
                        .filter(function (checkbox) {
                            return checkbox.checked;
                        })
                        .forEach(function (checkbox) {
                            const hiddenInput = document.createElement('input');
                            hiddenInput.type = 'hidden';
                            hiddenInput.name = 'selected_ids[]';
                            hiddenInput.value = checkbox.value;
                            selectedFields.appendChild(hiddenInput);
                        });

                    exportForm.submit();
                });
            });

            updateSelectedCount();
        })();
    </script>
@endpush
@endsection
