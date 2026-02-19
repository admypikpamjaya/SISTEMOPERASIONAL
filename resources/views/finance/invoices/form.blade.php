@extends('layouts.app')

@section('content')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=DM+Mono:wght@400;500&display=swap');

    :root {
        --blue-primary: #1a56db;
        --blue-dark: #1e3a8a;
        --blue-deeper: #0f2460;
        --blue-mid: #2563eb;
        --blue-light: #3b82f6;
        --accent-green: #10b981;
        --accent-red: #ef4444;
        --accent-amber: #f59e0b;
        --surface-bg: #f0f4fd;
        --surface-card: #ffffff;
        --text-primary: #0f172a;
        --text-secondary: #475569;
        --text-muted: #94a3b8;
        --border-light: rgba(37,99,235,0.10);
        --border-table: #e2e8f0;
        --shadow-sm: 0 1px 3px rgba(15,23,42,0.07);
        --shadow-md: 0 4px 16px rgba(15,23,42,0.09), 0 2px 8px rgba(37,99,235,0.07);
        --shadow-lg: 0 10px 40px rgba(15,23,42,0.13), 0 4px 16px rgba(37,99,235,0.10);
        --radius-sm: 10px;
        --radius-md: 14px;
        --radius-lg: 20px;
    }

    body, .content-wrapper { background: var(--surface-bg) !important; font-family: 'Plus Jakarta Sans', sans-serif !important; }

    /* ── Page Header ──────────────────────────── */
    .ivf-page-header {
        display: flex; align-items: center; justify-content: space-between;
        margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;
        animation: fadeDown 0.4s ease both;
    }
    .ivf-header-left { display: flex; align-items: center; gap: 0.9rem; }
    .ivf-header-icon {
        width: 48px; height: 48px; border-radius: var(--radius-sm);
        background: linear-gradient(135deg, var(--blue-primary), var(--blue-light));
        display: flex; align-items: center; justify-content: center;
        color: white; font-size: 1.25rem; box-shadow: var(--shadow-md); flex-shrink: 0;
    }
    .ivf-header-title { font-size: 1.35rem; font-weight: 800; color: var(--text-primary); margin: 0; letter-spacing: -0.02em; line-height: 1.2; }
    .ivf-header-sub { font-size: 0.8rem; color: var(--text-muted); margin: 0.1rem 0 0; font-weight: 500; }
    .ivf-header-actions { display: flex; align-items: center; gap: 0.6rem; flex-wrap: wrap; }

    /* ── Buttons ──────────────────────────────── */
    .btn-ivf-back {
        display: inline-flex; align-items: center; gap: 0.4rem;
        background: white; border: 1.5px solid var(--border-table);
        color: var(--text-secondary); font-size: 0.82rem; font-weight: 600;
        padding: 0.52rem 1rem; border-radius: var(--radius-sm);
        text-decoration: none; transition: all 0.2s; box-shadow: var(--shadow-sm);
    }
    .btn-ivf-back:hover { border-color: var(--blue-light); color: var(--text-primary); text-decoration: none; }

    .btn-ivf-reset {
        display: inline-flex; align-items: center; gap: 0.4rem;
        background: rgba(245,158,11,0.1); border: 1.5px solid rgba(245,158,11,0.3);
        color: #92400e; font-size: 0.82rem; font-weight: 700;
        padding: 0.52rem 1rem; border-radius: var(--radius-sm);
        cursor: pointer; font-family: inherit; transition: all 0.2s;
    }
    .btn-ivf-reset:hover { background: var(--accent-amber); color: white; border-color: var(--accent-amber); }

    .btn-draft {
        display: inline-flex; align-items: center; gap: 0.4rem;
        background: white; border: 1.5px solid var(--border-table);
        color: var(--text-secondary); font-size: 0.85rem; font-weight: 700;
        padding: 0.6rem 1.25rem; border-radius: var(--radius-sm);
        cursor: pointer; font-family: inherit; transition: all 0.2s;
    }
    .btn-draft:hover { border-color: var(--text-secondary); color: var(--text-primary); }

    .btn-post {
        display: inline-flex; align-items: center; gap: 0.4rem;
        background: linear-gradient(135deg, var(--blue-primary), var(--blue-mid));
        color: white; font-size: 0.85rem; font-weight: 700;
        padding: 0.6rem 1.35rem; border-radius: var(--radius-sm);
        border: none; cursor: pointer; font-family: inherit;
        transition: all 0.25s; box-shadow: 0 3px 10px rgba(37,99,235,0.35);
    }
    .btn-post:hover { transform: translateY(-1px); box-shadow: 0 6px 20px rgba(37,99,235,0.45); }

    .btn-add-row {
        display: inline-flex; align-items: center; gap: 0.4rem;
        background: rgba(16,185,129,0.08); border: 1.5px solid rgba(16,185,129,0.25);
        color: #065f46; font-size: 0.82rem; font-weight: 700;
        padding: 0.52rem 1rem; border-radius: var(--radius-sm);
        cursor: pointer; font-family: inherit; transition: all 0.2s;
    }
    .btn-add-row:hover { background: var(--accent-green); color: white; border-color: var(--accent-green); }
    .btn-add-row:disabled { opacity: 0.45; cursor: not-allowed; }

    /* ── Alert ────────────────────────────────── */
    .ivf-alert {
        display: flex; align-items: flex-start; gap: 0.75rem;
        border-radius: var(--radius-md); padding: 0.9rem 1.1rem;
        margin-bottom: 1.25rem; font-size: 0.83rem; font-weight: 500;
        border: 1px solid transparent; animation: fadeUp 0.4s ease both;
    }
    .ivf-alert.danger  { background: rgba(239,68,68,0.07); border-color: rgba(239,68,68,0.2); color: #991b1b; }
    .ivf-alert.info    { background: rgba(37,99,235,0.07); border-color: rgba(37,99,235,0.15); color: var(--blue-dark); }
    .ivf-alert .al-icon {
        width: 28px; height: 28px; border-radius: 8px; flex-shrink: 0;
        display: flex; align-items: center; justify-content: center; font-size: 0.78rem;
    }
    .ivf-alert.danger .al-icon  { background: rgba(239,68,68,0.12); color: var(--accent-red); }
    .ivf-alert.info .al-icon    { background: rgba(37,99,235,0.12); color: var(--blue-primary); }
    .ivf-alert ul { margin: 0.4rem 0 0; padding-left: 1.2rem; }
    .ivf-alert li { margin-bottom: 0.15rem; }

    /* ── Main Card ────────────────────────────── */
    .ivf-main-card {
        background: white; border-radius: var(--radius-lg);
        box-shadow: var(--shadow-md); border: 1px solid var(--border-light);
        overflow: hidden; animation: fadeUp 0.5s ease both;
    }

    /* ── Invoice Meta Banner ──────────────────── */
    .ivf-meta-banner {
        display: flex; align-items: center; justify-content: space-between;
        padding: 1.1rem 1.4rem; border-bottom: 1px solid var(--border-light);
        background: linear-gradient(135deg, rgba(37,99,235,0.03), rgba(37,99,235,0.01));
        flex-wrap: wrap; gap: 0.75rem;
    }
    .ivf-meta-no {
        font-family: 'Plus Jakarta Sans', sans-serif; font-size: 1.25rem; font-weight: 700;
        color: var(--text-primary); letter-spacing: 0; margin: 0 0 0.15rem;
    }
    .ivf-meta-sub { font-size: 0.78rem; color: var(--text-muted); font-weight: 500; }

    .badge-status-lg {
        display: inline-flex; align-items: center; gap: 0.35rem;
        border-radius: 999px; padding: 0.35rem 0.9rem;
        font-size: 0.75rem; font-weight: 800; letter-spacing: 0.06em;
    }
    .badge-posted-lg    { background: rgba(16,185,129,0.12); color: #065f46; }
    .badge-draft-lg     { background: rgba(245,158,11,0.12); color: #92400e; }
    .badge-cancelled-lg { background: rgba(100,116,139,0.1); color: #475569; }

    /* ── Form Body ────────────────────────────── */
    .ivf-form-body { padding: 1.4rem; }

    /* ── Form Controls ────────────────────────── */
    .ivf-form-group { margin-bottom: 1.1rem; }
    .ivf-label {
        display: flex; align-items: center; gap: 0.3rem;
        font-size: 0.7rem; font-weight: 700; text-transform: uppercase;
        letter-spacing: 0.07em; color: var(--text-muted); margin-bottom: 0.4rem;
    }
    .ivf-label i { font-size: 0.62rem; color: var(--blue-primary); }
    .ivf-control {
        width: 100%; border: 1.5px solid var(--border-table); border-radius: var(--radius-sm);
        padding: 0.55rem 0.85rem; font-size: 0.84rem; font-family: 'Plus Jakarta Sans', sans-serif;
        color: var(--text-primary); background: white; transition: all 0.2s;
        appearance: none; -webkit-appearance: none;
    }
    .ivf-control:focus { outline: none; border-color: var(--blue-primary); box-shadow: 0 0 0 3px rgba(37,99,235,0.12); }
    .ivf-control:disabled { background: #f8fafc; color: var(--text-muted); cursor: not-allowed; }
    select.ivf-control {
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%2394a3b8' stroke-width='2.5'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
        background-repeat: no-repeat; background-position: right 0.75rem center; padding-right: 2rem;
    }
    textarea.ivf-control { resize: vertical; min-height: 68px; }

    /* ── Section Divider ──────────────────────── */
    .ivf-section-label {
        display: flex; align-items: center; gap: 0.5rem;
        font-size: 0.72rem; font-weight: 700; text-transform: uppercase;
        letter-spacing: 0.09em; color: var(--text-muted);
        margin: 1.4rem 0 0.9rem;
    }
    .ivf-section-label::before {
        content: ''; flex: none; width: 3px; height: 14px;
        border-radius: 2px; background: var(--blue-primary);
    }
    .ivf-section-label::after {
        content: ''; flex: 1; height: 1px; background: var(--border-light);
    }

    /* ── Items Table ──────────────────────────── */
    .ivf-items-wrap {
        border: 1.5px solid var(--border-table); border-radius: var(--radius-md);
        overflow: hidden; margin-bottom: 0.5rem;
    }
    .ivf-items-table { width: 100%; border-collapse: collapse; font-size: 0.8rem; }
    .ivf-items-table th {
        background: #f8fafc; color: var(--text-muted);
        font-size: 0.66rem; font-weight: 700; text-transform: uppercase;
        letter-spacing: 0.07em; padding: 0.6rem 0.75rem;
        border-bottom: 1.5px solid var(--border-table); white-space: nowrap;
    }
    .ivf-items-table td {
        padding: 0.4rem 0.45rem; border-bottom: 1px solid var(--border-table);
        vertical-align: middle;
    }
    .ivf-items-table tbody tr:last-child td { border-bottom: none; }
    .ivf-items-table tbody tr:hover td { background: rgba(37,99,235,0.02); }

    /* Inputs inside table */
    .ivf-cell-input {
        width: 100%; border: 1.5px solid transparent; border-radius: 7px;
        padding: 0.38rem 0.55rem; font-size: 0.79rem; font-family: 'Plus Jakarta Sans', sans-serif;
        color: var(--text-primary); background: transparent; transition: all 0.18s;
    }
    .ivf-cell-input:focus { outline: none; border-color: var(--blue-primary); background: white; box-shadow: 0 0 0 2px rgba(37,99,235,0.10); }
    .ivf-cell-input:disabled { color: var(--text-muted); cursor: not-allowed; }
    .ivf-cell-input.text-right { text-align: right; font-family: 'Plus Jakarta Sans', sans-serif; font-weight: 400; }

    /* tfoot totals */
    .ivf-items-table tfoot th {
        background: #f0f4fd; border-top: 2px solid var(--border-table);
        font-size: 0.78rem; padding: 0.65rem 0.75rem; color: var(--text-primary);
    }
    .ivf-items-table tfoot .total-amount {
        font-family: 'Plus Jakarta Sans', sans-serif; font-size: 0.82rem; font-weight: 600;
        color: var(--blue-primary); text-align: right;
    }

    /* remove row button */
    .btn-remove-row {
        display: inline-flex; align-items: center; justify-content: center;
        width: 28px; height: 28px; border-radius: 7px; border: 1.5px solid rgba(239,68,68,0.2);
        background: rgba(239,68,68,0.07); color: #991b1b; cursor: pointer;
        transition: all 0.18s; font-size: 0.7rem;
    }
    .btn-remove-row:hover { background: var(--accent-red); color: white; border-color: var(--accent-red); }
    .btn-remove-row:disabled { opacity: 0.3; cursor: not-allowed; }

    /* ── Card Footer ──────────────────────────── */
    .ivf-card-footer {
        display: flex; align-items: center; justify-content: space-between;
        padding: 1rem 1.4rem; border-top: 1px solid var(--border-light);
        background: #fafbff; flex-wrap: wrap; gap: 0.75rem;
    }
    .ivf-footer-right { display: flex; align-items: center; gap: 0.6rem; }

    /* ── Animations ───────────────────────────── */
    @keyframes fadeUp   { from { opacity:0; transform:translateY(14px); } to { opacity:1; transform:translateY(0); } }
    @keyframes fadeDown { from { opacity:0; transform:translateY(-10px); } to { opacity:1; transform:translateY(0); } }
</style>

@php
    $isEdit = isset($invoice) && $invoice;
    $isReadOnly = (bool) ($isReadOnly ?? false);
    $formAction = $isEdit ? route('finance.invoice.update', $invoice->id) : route('finance.invoice.store');
    $defaultDate = old('accounting_date', $isEdit ? optional($invoice->accounting_date)->toDateString() : now()->toDateString());
    $defaultEntryType = old('entry_type', $isEdit ? $invoice->entry_type : 'EXPENSE');
    $defaultJournalName = old('journal_name', $isEdit ? $invoice->journal_name : '');
    $defaultReference = old('reference', $isEdit ? $invoice->reference : '');
    $defaultAction = old('action', 'save_draft');

    $rowItems = old('items');
    if (!is_array($rowItems)) {
        $rowItems = collect($items ?? [])->map(function ($item) {
            return [
                'asset_category' => $item->asset_category ?? '',
                'account_code' => $item->account_code ?? '',
                'partner_name' => $item->partner_name ?? '',
                'label' => $item->label ?? '',
                'analytic_distribution' => $item->analytic_distribution ?? '',
                'debit' => isset($item->debit) ? (float) $item->debit : 0,
                'credit' => isset($item->credit) ? (float) $item->credit : 0,
            ];
        })->toArray();
    }
    if (empty($rowItems)) {
        $rowItems = [[
            'asset_category' => '', 'account_code' => '', 'partner_name' => '',
            'label' => '', 'analytic_distribution' => '', 'debit' => 0, 'credit' => 0,
        ]];
    }

    $status = strtoupper((string) ($isEdit ? $invoice->status : 'DRAFT'));
    $statusBadge = match($status) {
        'POSTED'    => 'badge-posted-lg',
        'CANCELLED' => 'badge-cancelled-lg',
        default     => 'badge-draft-lg',
    };
    $statusIcon = match($status) {
        'POSTED'    => 'fa-check-circle',
        'CANCELLED' => 'fa-times-circle',
        default     => 'fa-clock',
    };
@endphp

{{-- ── Page Header ──────────────────────────────────── --}}
<div class="ivf-page-header">
    <div class="ivf-header-left">
        <div class="ivf-header-icon">
            <i class="fas fa-{{ $isEdit ? 'file-signature' : 'file-medical' }}"></i>
        </div>
        <div>
            <h1 class="ivf-header-title">
                {{ $isEdit ? 'Ubah Faktur / Jurnal' : 'Draft Faktur / Jurnal Baru' }}
            </h1>
            <p class="ivf-header-sub">
                {{ $isEdit ? 'Faktur: ' . $invoice->invoice_no : 'Nomor faktur akan dibuat otomatis saat disimpan' }}
            </p>
        </div>
    </div>
    <div class="ivf-header-actions">
        <a href="{{ route('finance.invoice.index') }}" class="btn-ivf-back">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
        @if($isEdit && $invoice->status === 'POSTED')
            <form action="{{ route('finance.invoice.set-draft', $invoice->id) }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn-ivf-reset">
                    <i class="fas fa-undo"></i> Reset ke Rancangan
                </button>
            </form>
        @endif
    </div>
</div>

{{-- ── Validation Errors ────────────────────────────── --}}
@if($errors->any())
    <div class="ivf-alert danger">
        <div class="al-icon"><i class="fas fa-exclamation-triangle"></i></div>
        <div>
            <strong>Validasi gagal:</strong>
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    </div>
@endif

{{-- ── Main Form Card ───────────────────────────────── --}}
<div class="ivf-main-card">

    {{-- Invoice Meta Banner --}}
    <div class="ivf-meta-banner">
        <div>
            <div class="ivf-meta-no">
                {{ $isEdit ? $invoice->invoice_no : 'Draft' }}
            </div>
            <div class="ivf-meta-sub">
                {{ $isEdit ? 'Edit entri jurnal yang sudah ada' : 'Buat entri jurnal & faktur baru' }}
            </div>
        </div>
        <span class="badge-status-lg {{ $statusBadge }}">
            <i class="fas {{ $statusIcon }}" style="font-size:.6rem;"></i>
            {{ $status }}
        </span>
    </div>

    {{-- Read-only info banner --}}
    @if($isReadOnly)
        <div class="ivf-alert info" style="margin: 1rem 1.4rem 0; animation:none;">
            <div class="al-icon"><i class="fas fa-lock"></i></div>
            <div>Faktur ini sudah <strong>terekam</strong>. Klik <strong>Reset ke Rancangan</strong> di atas untuk mengedit isi jurnal.</div>
        </div>
    @endif

    <form method="POST" action="{{ $formAction }}" id="invoice-form">
        @csrf
        @if($isEdit) @method('PUT') @endif
        <input type="hidden" name="action" id="form-action" value="{{ $defaultAction }}">

        <div class="ivf-form-body">

            {{-- ── Info Utama ── --}}
            <div class="ivf-section-label"><i class="fas fa-info-circle" style="font-size:.65rem;color:var(--blue-primary);"></i> Informasi Faktur</div>

            <div class="row">
                <div class="col-md-3 ivf-form-group">
                    <label class="ivf-label"><i class="fas fa-calendar-alt"></i> Tanggal Akuntansi</label>
                    <input type="date" name="accounting_date" id="accounting_date"
                        class="ivf-control" value="{{ $defaultDate }}"
                        {{ $isReadOnly ? 'disabled' : '' }} required>
                </div>
                <div class="col-md-3 ivf-form-group">
                    <label class="ivf-label"><i class="fas fa-tags"></i> Jenis</label>
                    <select name="entry_type" id="entry_type" class="ivf-control"
                        {{ $isReadOnly ? 'disabled' : '' }} required>
                        <option value="INCOME"  {{ $defaultEntryType === 'INCOME'  ? 'selected' : '' }}>Pemasukan</option>
                        <option value="EXPENSE" {{ $defaultEntryType === 'EXPENSE' ? 'selected' : '' }}>Pengeluaran</option>
                    </select>
                </div>
                <div class="col-md-6 ivf-form-group">
                    <label class="ivf-label"><i class="fas fa-book"></i> Jurnal</label>
                    <input type="text" name="journal_name" id="journal_name"
                        class="ivf-control" placeholder="Contoh: J.BSM.PMB-Keluar"
                        value="{{ $defaultJournalName }}"
                        {{ $isReadOnly ? 'disabled' : '' }} required>
                </div>
                <div class="col-12 ivf-form-group">
                    <label class="ivf-label"><i class="fas fa-link"></i> Referensi</label>
                    <input type="text" name="reference" id="reference"
                        class="ivf-control" placeholder="Contoh: Pembayaran SPP Februari 2026"
                        value="{{ $defaultReference }}"
                        {{ $isReadOnly ? 'disabled' : '' }}>
                </div>
            </div>

            {{-- ── Baris Jurnal ── --}}
            <div class="ivf-section-label"><i class="fas fa-table" style="font-size:.65rem;color:var(--blue-primary);"></i> Baris Jurnal</div>

            <div class="ivf-items-wrap">
                <table class="ivf-items-table" id="invoice-items-table">
                    <thead>
                        <tr>
                            <th style="width:130px;">Asset Category</th>
                            <th style="width:130px;">Akun <span style="color:var(--accent-red);">*</span></th>
                            <th style="width:150px;">Rekanan</th>
                            <th style="width:200px;">Label <span style="color:var(--accent-red);">*</span></th>
                            <th>Analisa Distribusi</th>
                            <th style="width:148px;text-align:right;">Debit</th>
                            <th style="width:148px;text-align:right;">Kredit</th>
                            <th style="width:52px;text-align:center;"></th>
                        </tr>
                    </thead>
                    <tbody id="invoice-items-body">
                        @foreach($rowItems as $index => $item)
                            <tr>
                                <td><input type="text" name="items[{{ $index }}][asset_category]" class="ivf-cell-input" value="{{ $item['asset_category'] ?? '' }}" {{ $isReadOnly ? 'disabled' : '' }}></td>
                                <td><input type="text" name="items[{{ $index }}][account_code]" class="ivf-cell-input" value="{{ $item['account_code'] ?? '' }}" required {{ $isReadOnly ? 'disabled' : '' }}></td>
                                <td><input type="text" name="items[{{ $index }}][partner_name]" class="ivf-cell-input" value="{{ $item['partner_name'] ?? '' }}" {{ $isReadOnly ? 'disabled' : '' }}></td>
                                <td><input type="text" name="items[{{ $index }}][label]" class="ivf-cell-input" value="{{ $item['label'] ?? '' }}" required {{ $isReadOnly ? 'disabled' : '' }}></td>
                                <td><input type="text" name="items[{{ $index }}][analytic_distribution]" class="ivf-cell-input" value="{{ $item['analytic_distribution'] ?? '' }}" {{ $isReadOnly ? 'disabled' : '' }}></td>
                                <td><input type="number" min="0" step="0.01" name="items[{{ $index }}][debit]" class="ivf-cell-input text-right js-amount-debit" value="{{ $item['debit'] ?? 0 }}" {{ $isReadOnly ? 'disabled' : '' }}></td>
                                <td><input type="number" min="0" step="0.01" name="items[{{ $index }}][credit]" class="ivf-cell-input text-right js-amount-credit" value="{{ $item['credit'] ?? 0 }}" {{ $isReadOnly ? 'disabled' : '' }}></td>
                                <td style="text-align:center;">
                                    <button type="button" class="btn-remove-row js-remove-row"
                                        data-locked="{{ $isReadOnly ? '1' : '0' }}"
                                        {{ $isReadOnly ? 'disabled' : '' }}>
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="5" style="text-align:right;color:var(--text-muted);">Total</th>
                            <th class="total-amount" id="total-debit-text">Rp 0,00</th>
                            <th class="total-amount" id="total-credit-text">Rp 0,00</th>
                            <th></th>
                        </tr>
                    </tfoot>
                </table>
            </div>

            {{-- ── Catatan ── --}}
            <div class="ivf-section-label"><i class="fas fa-sticky-note" style="font-size:.65rem;color:var(--blue-primary);"></i> Catatan</div>
            <div class="ivf-form-group">
                <label class="ivf-label"><i class="fas fa-comment-alt"></i> Catatan Awal <span style="font-weight:400;text-transform:none;letter-spacing:0;">(opsional)</span></label>
                <textarea name="initial_note" id="initial_note" class="ivf-control"
                    placeholder="Catatan untuk log faktur (bisa ditambah lagi di halaman detail)."
                    {{ $isReadOnly ? 'disabled' : '' }}>{{ old('initial_note') }}</textarea>
            </div>

        </div>{{-- /ivf-form-body --}}

        {{-- ── Footer ── --}}
        <div class="ivf-card-footer">
            <button type="button" id="add-item-row" class="btn-add-row" {{ $isReadOnly ? 'disabled' : '' }}>
                <i class="fas fa-plus"></i> Tambahkan Baris
            </button>
            @if(!$isReadOnly)
                <div class="ivf-footer-right">
                    <button type="submit" class="btn-draft js-submit-action" data-action="save_draft">
                        <i class="fas fa-save"></i> Simpan Draft
                    </button>
                    <button type="submit" class="btn-post js-submit-action" data-action="post">
                        <i class="fas fa-check-circle"></i> Rekam
                    </button>
                </div>
            @endif
        </div>

    </form>
</div>
@endsection

@section('js')
<script>
    (function () {
        const tableBody       = document.getElementById('invoice-items-body');
        const addButton       = document.getElementById('add-item-row');
        const form            = document.getElementById('invoice-form');
        const actionInput     = document.getElementById('form-action');
        const totalDebitText  = document.getElementById('total-debit-text');
        const totalCreditText = document.getElementById('total-credit-text');

        function parseNumber(value) {
            const number = Number(value);
            return Number.isFinite(number) ? number : 0;
        }

        function formatRupiah(value) {
            return 'Rp ' + Number(value).toLocaleString('id-ID', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        }

        function updateTotals() {
            let totalDebit = 0;
            let totalCredit = 0;
            Array.from(tableBody.querySelectorAll('tr')).forEach(function (row) {
                const debitInput  = row.querySelector('.js-amount-debit');
                const creditInput = row.querySelector('.js-amount-credit');
                totalDebit  += parseNumber(debitInput  ? debitInput.value  : 0);
                totalCredit += parseNumber(creditInput ? creditInput.value : 0);
            });
            totalDebitText.textContent  = formatRupiah(totalDebit);
            totalCreditText.textContent = formatRupiah(totalCredit);
        }

        function renumberRows() {
            Array.from(tableBody.querySelectorAll('tr')).forEach(function (row, index) {
                row.querySelectorAll('input').forEach(function (input) {
                    const name = input.getAttribute('name');
                    if (!name) return;
                    input.setAttribute('name', name.replace(/items\[\d+\]/, 'items[' + index + ']'));
                });
                const removeButton = row.querySelector('.js-remove-row');
                if (removeButton) {
                    const isLocked = removeButton.getAttribute('data-locked') === '1';
                    removeButton.disabled = isLocked || tableBody.querySelectorAll('tr').length <= 1;
                }
            });
        }

        function addRow() {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td><input type="text" name="items[0][asset_category]" class="ivf-cell-input"></td>
                <td><input type="text" name="items[0][account_code]" class="ivf-cell-input" required></td>
                <td><input type="text" name="items[0][partner_name]" class="ivf-cell-input"></td>
                <td><input type="text" name="items[0][label]" class="ivf-cell-input" required></td>
                <td><input type="text" name="items[0][analytic_distribution]" class="ivf-cell-input"></td>
                <td><input type="number" min="0" step="0.01" name="items[0][debit]" class="ivf-cell-input text-right js-amount-debit" value="0"></td>
                <td><input type="number" min="0" step="0.01" name="items[0][credit]" class="ivf-cell-input text-right js-amount-credit" value="0"></td>
                <td style="text-align:center;">
                    <button type="button" class="btn-remove-row js-remove-row" data-locked="0">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            `;
            tableBody.appendChild(row);
            renumberRows();
            updateTotals();
            row.querySelector('input').focus();
        }

        if (addButton) { addButton.addEventListener('click', addRow); }

        if (tableBody) {
            tableBody.addEventListener('click', function (event) {
                const button = event.target.closest('.js-remove-row');
                if (!button) return;
                const row = button.closest('tr');
                if (!row) return;
                if (tableBody.querySelectorAll('tr').length <= 1) return;
                row.remove();
                renumberRows();
                updateTotals();
            });
            tableBody.addEventListener('input', function (event) {
                if (event.target.matches('.js-amount-debit, .js-amount-credit')) {
                    updateTotals();
                }
            });
        }

        Array.from(document.querySelectorAll('.js-submit-action')).forEach(function (button) {
            button.addEventListener('click', function () {
                actionInput.value = button.dataset.action || 'save_draft';
            });
        });

        if (form) {
            form.addEventListener('submit', function () {
                if (!actionInput.value) actionInput.value = 'save_draft';
            });
        }

        renumberRows();
        updateTotals();
    })();
</script>
@endsection