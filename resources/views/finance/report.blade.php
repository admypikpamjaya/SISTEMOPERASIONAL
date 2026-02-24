@extends('layouts.app')

@section('content')
@php
    $isEditMode = isset($editingReport) && is_array($editingReport);
    $editReportId = $isEditMode ? (string) data_get($editingReport, 'report_id') : null;
    $formAction = $isEditMode
        ? route('finance.report.update', $editReportId)
        : route('finance.report.store');
    $formTitle = $isEditMode ? 'Edit Snapshot Laba Rugi' : 'Input Laporan Laba Rugi';
    $submitLabel = $isEditMode ? 'Simpan Perubahan Snapshot' : 'Simpan Snapshot Laba Rugi';

    $defaultPeriodType = old('report_type', $defaults['period_type'] ?? 'MONTHLY');
    $defaultReportDate = old('report_date', $defaults['report_date'] ?? now()->toDateString());
    $defaultMonth = (int) old('month', $defaults['month'] ?? now()->month);
    $defaultYear = (int) old('year', $defaults['year'] ?? now()->year);
    $defaultOpeningBalance = old(
        'opening_balance',
        $isEditMode
            ? (float) data_get($editingReport, 'opening_balance', 0)
            : ($suggestedOpeningBalance ?? 0)
    );
    $accountOptions = collect($accountOptions ?? [])
        ->map(function ($item): array {
            return [
                'code' => strtoupper(trim((string) data_get($item, 'code'))),
                'name' => trim((string) data_get($item, 'name')),
                'type' => strtoupper(trim((string) data_get($item, 'type'))),
                'class_no' => (int) data_get($item, 'class_no', 0),
            ];
        })
        ->filter(fn ($item) => $item['code'] !== '' && $item['name'] !== '')
        ->unique('code')
        ->sortBy('code')
        ->values();
    $invoiceOptions = collect($invoiceOptions ?? [])
        ->map(fn ($item) => trim((string) $item))
        ->filter()
        ->unique()
        ->values();

    $defaultEntryRows = $isEditMode
        ? ((array) data_get($editingReport, 'entries', []))
        : [
            [
                'type' => 'INCOME',
                'line_code' => '',
                'line_label' => '',
                'invoice_number' => '',
                'description' => '',
                'amount' => '',
                'is_depreciation' => false,
            ],
        ];

    $entryRows = old('entries', !empty($entryRows ?? null) ? $entryRows : $defaultEntryRows);
    if (empty($entryRows)) {
        $entryRows = [
        [
            'type' => 'INCOME',
            'line_code' => '',
            'line_label' => '',
            'invoice_number' => '',
            'description' => '',
            'amount' => '',
            'is_depreciation' => false,
        ],
        ];
    }

    $existingEntryCodes = collect($entryRows)
        ->map(fn ($entry) => strtoupper(trim((string) data_get($entry, 'line_code', ''))))
        ->filter()
        ->unique()
        ->values();
    foreach ($existingEntryCodes as $existingCode) {
        if (!$accountOptions->contains(fn ($option) => $option['code'] === $existingCode)) {
            $accountOptions->push([
                'code' => $existingCode,
                'name' => '',
                'type' => '',
                'class_no' => 0,
            ]);
        }
    }

    $existingEntryNames = collect($entryRows)
        ->map(fn ($entry) => trim((string) data_get($entry, 'line_label', '')))
        ->filter()
        ->unique()
        ->values();
    foreach ($existingEntryNames as $existingName) {
        if (!$accountOptions->contains(fn ($option) => strcasecmp($option['name'], $existingName) === 0)) {
            $accountOptions->push([
                'code' => '',
                'name' => $existingName,
                'type' => '',
                'class_no' => 0,
            ]);
        }
    }

    $accountOptions = $accountOptions
        ->sortBy(function ($option) {
            $prefix = $option['code'] === '' ? 'ZZZZ' : $option['code'];
            return $prefix . '|' . strtoupper($option['name']);
        })
        ->values();

    $existingInvoiceNumbers = collect($entryRows)
        ->map(fn ($entry) => trim((string) data_get($entry, 'invoice_number', '')))
        ->filter()
        ->unique()
        ->values();
    $invoiceOptions = $invoiceOptions
        ->merge($existingInvoiceNumbers)
        ->filter()
        ->unique()
        ->values();
@endphp

<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

<style>
    /* ── Design Tokens ── */
    :root {
        --p1:       #3B82F6;
        --p2:       #2563EB;
        --p3:       #1D4ED8;
        --grad:     linear-gradient(135deg, #3B82F6 0%, #2563EB 55%, #1D4ED8 100%);
        --surface:  #FFFFFF;
        --surf-alt: #F8FAFF;
        --border:   #DBEAFE;
        --text:     #1E293B;
        --muted:    #64748B;
        --success:  #22C55E;
        --s-bg:     #F0FDF4;
        --s-b:      #BBF7D0;
        --danger:   #EF4444;
        --d-bg:     #FFF1F2;
        --d-b:      #FECDD3;
        --shadow:   0 4px 24px rgba(37,99,235,.09);
        --shadow-lg:0 8px 32px rgba(37,99,235,.16);
        --radius:   18px;
        --radius-sm:11px;
        --font:     'Plus Jakarta Sans','Nunito','Segoe UI',sans-serif;
    }

    .fr, .fr * { font-family: var(--font) !important; box-sizing: border-box; }

    .fr .fas, .fr .far, .fr .fab {
        font-family: 'Font Awesome 5 Free' !important;
        font-style: normal !important;
        -webkit-font-smoothing: antialiased;
        display: inline-block; line-height: 1; vertical-align: middle;
    }

    /* ── Card ── */
    .fr-card {
        background: var(--surface);
        border-radius: var(--radius);
        box-shadow: var(--shadow);
        border: 1.5px solid var(--border);
        overflow: hidden;
        margin-bottom: 24px;
        transition: box-shadow .2s, transform .2s;
    }
    .fr-card:hover { box-shadow: var(--shadow-lg); transform: translateY(-2px); }

    .fr-card-header {
        background: var(--grad);
        padding: 15px 22px;
        display: flex; align-items: center;
        justify-content: space-between; gap: 12px;
    }
    .fr-card-header-left { display: flex; align-items: center; gap: 12px; }
    .fr-card-header .hicon {
        width: 30px; height: 30px;
        background: rgba(255,255,255,.18);
        border-radius: 8px;
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0;
    }
    .fr-card-header .hicon .fas { font-size: .88rem !important; color: #fff !important; }
    .fr-card-header h3 { margin: 0; font-size: 1rem; font-weight: 700; color: #fff; }

    .fr-card-body { padding: 22px; }

    .fr-card-footer {
        padding: 14px 22px;
        background: var(--surf-alt);
        border-top: 1.5px solid var(--border);
        display: flex; align-items: center;
        justify-content: space-between; gap: 10px;
        flex-wrap: wrap;
    }

    /* ── Validation alert ── */
    .fr-alert-danger {
        background: var(--d-bg); border: 1.5px solid var(--d-b);
        border-radius: var(--radius-sm); color: #BE123C;
        padding: 14px 18px; margin-bottom: 20px; font-size: .9rem;
    }

    /* ── Estimated balance box ── */
    .fr-balance-box {
        background: var(--surf-alt);
        border: 1.5px solid var(--border);
        border-radius: var(--radius-sm);
        padding: 13px 18px;
        margin-bottom: 20px;
        display: flex; align-items: center; gap: 10px;
    }
    .fr-balance-box .bal-icon {
        width: 34px; height: 34px;
        background: var(--grad);
        border-radius: 9px;
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0;
    }
    .fr-balance-box .bal-icon .fas { font-size: .82rem !important; color: #fff !important; }
    .fr-balance-box .bal-label { font-size: .76rem; font-weight: 700; color: var(--muted); text-transform: uppercase; letter-spacing: .04em; }
    .fr-balance-box .bal-value { font-size: 1.05rem; font-weight: 800; color: var(--p2); line-height: 1.1; }

    /* ── Form labels & inputs ── */
    .fr-form-group { margin-bottom: 16px; }
    .fr-form-group label {
        display: flex; align-items: center; gap: 6px;
        font-size: .74rem; font-weight: 700;
        color: var(--muted); text-transform: uppercase;
        letter-spacing: .04em; margin-bottom: 6px;
    }
    .fr-form-group label .fas {
        font-size: .76rem !important; color: var(--p1) !important;
        width: 13px; text-align: center;
    }

    .fr-input, .fr-select {
        width: 100%;
        background: var(--surf-alt);
        border: 1.5px solid var(--border);
        border-radius: var(--radius-sm);
        padding: 9px 13px;
        font-size: .9rem; color: var(--text);
        font-family: var(--font) !important;
        outline: none; appearance: auto; height: auto;
        transition: border-color .2s, box-shadow .2s;
    }
    .fr-input:focus, .fr-select:focus {
        border-color: var(--p1);
        box-shadow: 0 0 0 3px rgba(59,130,246,.14);
        background: #fff;
    }

    /* Rp prefix group */
    .fr-input-group { display: flex; align-items: stretch; }
    .fr-input-group .fr-prefix {
        background: #EFF6FF;
        border: 1.5px solid var(--border); border-right: none;
        border-radius: var(--radius-sm) 0 0 var(--radius-sm);
        padding: 9px 13px;
        color: var(--p1); font-weight: 700; font-size: .9rem;
        display: flex; align-items: center;
    }
    .fr-input-group .fr-input { border-radius: 0 var(--radius-sm) var(--radius-sm) 0; }

    /* ── Buttons ── */
    .fr-btn {
        display: inline-flex; align-items: center; gap: 7px;
        padding: 9px 18px; border-radius: var(--radius-sm);
        font-size: .88rem; font-weight: 700;
        cursor: pointer; border: none;
        transition: transform .15s, box-shadow .15s;
        text-decoration: none; font-family: var(--font) !important;
        white-space: nowrap; line-height: 1;
    }
    .fr-btn:hover { transform: translateY(-1px); box-shadow: 0 4px 14px rgba(37,99,235,.20); text-decoration: none; }
    .fr-btn .fas { font-size: .86rem !important; }

    .fr-btn-primary  { background: var(--grad); color: #fff !important; }
    .fr-btn-primary .fas { color: #fff !important; }

    .fr-btn-success  { background: linear-gradient(135deg,#22C55E,#16A34A); color: #fff !important; }
    .fr-btn-success .fas { color: #fff !important; }

    .fr-btn-outline  { background: var(--surface); color: var(--p1) !important; border: 1.5px solid var(--border); }
    .fr-btn-outline .fas { color: var(--p1) !important; }

    .fr-btn-light    { background: rgba(255,255,255,.18); color: #fff !important; border: 1.5px solid rgba(255,255,255,.28); }
    .fr-btn-light .fas { color: #fff !important; }
    .fr-btn-light:hover { background: rgba(255,255,255,.30); color: #fff !important; }

    .fr-btn-add      { background: var(--surf-alt); color: var(--p1) !important; border: 1.5px solid var(--border); }
    .fr-btn-add .fas { color: var(--p1) !important; }

    /* ── Table ── */
    .fr-table-wrap { overflow-x: auto; }
    .fr-table { width: 100%; border-collapse: separate; border-spacing: 0; }
    .fr-table thead th {
        font-size: .70rem; font-weight: 800;
        text-transform: uppercase; letter-spacing: .05em;
        color: var(--muted); padding: 11px 12px;
        background: var(--surf-alt);
        border-bottom: 2px solid var(--border);
        white-space: nowrap;
    }
    .fr-table thead th .fas {
        font-size: .70rem !important; color: var(--p1) !important;
        margin-right: 4px; opacity: .85;
    }
    .fr-table tbody tr { transition: background .15s; }
    .fr-table tbody tr:hover { background: #F0F6FF; }
    .fr-table tbody td {
        padding: 9px 10px; vertical-align: middle;
        border-bottom: 1px solid var(--border);
    }
    .fr-table tbody tr:last-child td { border-bottom: none; }

    /* inline inputs inside table */
    .fr-table .td-input, .fr-table .td-select {
        width: 100%;
        background: var(--surf-alt);
        border: 1.5px solid var(--border);
        border-radius: 8px;
        padding: 7px 10px;
        font-size: .83rem; color: var(--text);
        font-family: var(--font) !important;
        outline: none; appearance: auto; height: auto;
        transition: border-color .2s, box-shadow .2s;
    }
    .fr-table .td-input:focus, .fr-table .td-select:focus {
        border-color: var(--p1);
        box-shadow: 0 0 0 2px rgba(59,130,246,.13);
        background: #fff;
    }
    .fr-table .td-input[type="number"] { -moz-appearance: textfield; }
    .fr-table .td-input[type="number"]::-webkit-outer-spin-button,
    .fr-table .td-input[type="number"]::-webkit-inner-spin-button { -webkit-appearance: none; }

    /* INCOME / EXPENSE type badge select */
    .fr-table .td-select option[value="INCOME"]  { color: #16A34A; }
    .fr-table .td-select option[value="EXPENSE"] { color: #BE123C; }

    /* depreciation checkbox */
    .fr-table .td-check {
        width: 18px; height: 18px;
        accent-color: var(--p1); cursor: pointer;
    }

    /* remove row button */
    .fr-btn-remove {
        display: inline-flex; align-items: center; justify-content: center;
        width: 30px; height: 30px;
        background: var(--d-bg); color: var(--danger) !important;
        border: 1.5px solid var(--d-b);
        border-radius: 8px; cursor: pointer;
        transition: background .15s, transform .15s;
        font-family: var(--font) !important;
    }
    .fr-btn-remove .fas { font-size: .76rem !important; color: var(--danger) !important; }
    .fr-btn-remove:hover { background: var(--danger); transform: translateY(-1px); }
    .fr-btn-remove:hover .fas { color: #fff !important; }
    .fr-btn-remove:disabled { opacity: .4; pointer-events: none; }

    @media (max-width: 991px) {
        .fr-card-body { padding: 16px; }
        .fr-card-header { flex-direction: column; align-items: flex-start; }
    }
</style>

<div class="fr">

    {{-- ── Brand Header ── --}}
    <div style="display:flex; align-items:center; gap:14px; margin-bottom:24px;">
        <div style="
            width:52px; height:52px;
            background: var(--grad);
            border-radius:14px;
            display:flex; align-items:center; justify-content:center;
            box-shadow: 0 4px 16px rgba(37,99,235,.28);
            flex-shrink:0;
        ">
            <i class="fas fa-file-invoice-dollar" style="font-size:1.3rem; color:#fff;"></i>
        </div>
        <div>
            <h1 style="font-size:1.3rem; font-weight:800; color:var(--text); margin:0 0 2px; line-height:1.2;">
                Input Finance Report
            </h1>
            <p style="font-size:.8rem; color:var(--muted); font-weight:500; margin:0;">
                Input & simpan snapshot laporan laba rugi
            </p>
        </div>
    </div>

    {{-- ── Main Card ── --}}
    <div class="row">
        <div class="col-12">
            <div class="fr-card">

                {{-- Card Header --}}
                <div class="fr-card-header">
                    <div class="fr-card-header-left">
                        <span class="hicon"><i class="fas fa-clipboard-list"></i></span>
                        <h3>{{ $formTitle }}</h3>
                    </div>
                    <a
                        href="{{ route('finance.report.snapshots', ['period_type' => 'MONTHLY', 'month' => now()->month, 'year' => now()->year]) }}"
                        class="fr-btn fr-btn-light"
                    >
                        <i class="fas fa-list"></i>
                        <span>Buka Snapshot Laporan</span>
                    </a>
                </div>

                <form method="POST" action="{{ $formAction }}" id="profit-loss-form">
                    @csrf
                    @if($isEditMode)
                        @method('PUT')
                        <input type="hidden" name="report_type" value="{{ $defaultPeriodType }}">
                        <input type="hidden" name="report_date" value="{{ $defaultReportDate }}">
                        <input type="hidden" name="month" value="{{ $defaultMonth }}">
                        <input type="hidden" name="year" value="{{ $defaultYear }}">
                    @endif

                    <div class="fr-card-body">

                        {{-- Validation errors --}}
                        @if($errors->any())
                            <div class="fr-alert-danger">
                                <strong>⚠ Validasi gagal:</strong>
                                <ul style="margin:8px 0 0; padding-left:18px;">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        {{-- Period filter row --}}
                        <div class="form-row" style="gap:0 12px;">

                            <div class="fr-form-group col-md-2" style="padding:0;">
                                <label for="report_type_create">
                                    <i class="fas fa-layer-group"></i> Periode
                                </label>
                                <select name="report_type" id="report_type_create" class="fr-select" {{ $isEditMode ? 'disabled' : '' }} required>
                                    <option value="DAILY"   {{ $defaultPeriodType === 'DAILY'   ? 'selected' : '' }}>Harian</option>
                                    <option value="MONTHLY" {{ $defaultPeriodType === 'MONTHLY' ? 'selected' : '' }}>Bulanan</option>
                                    <option value="YEARLY"  {{ $defaultPeriodType === 'YEARLY'  ? 'selected' : '' }}>Tahunan</option>
                                </select>
                            </div>

                            <div class="fr-form-group col-md-2" id="report_date_group" style="padding:0;">
                                <label for="report_date_create">
                                    <i class="fas fa-calendar-day"></i> Tanggal
                                </label>
                                <input
                                    type="date" name="report_date" id="report_date_create"
                                    class="fr-input" value="{{ $defaultReportDate }}" {{ $isEditMode ? 'disabled' : '' }}
                                >
                            </div>

                            <div class="fr-form-group col-md-2" id="month_group" style="padding:0;">
                                <label for="month_create">
                                    <i class="fas fa-calendar-alt"></i> Bulan
                                </label>
                                <select name="month" id="month_create" class="fr-select" {{ $isEditMode ? 'disabled' : '' }}>
                                    @for($m = 1; $m <= 12; $m++)
                                        <option value="{{ $m }}" {{ $defaultMonth === $m ? 'selected' : '' }}>{{ $m }}</option>
                                    @endfor
                                </select>
                            </div>

                            <div class="fr-form-group col-md-2" id="year_group" style="padding:0;">
                                <label for="year_create">
                                    <i class="fas fa-calendar-check"></i> Tahun
                                </label>
                                <input
                                    type="number" name="year" id="year_create"
                                    class="fr-input" min="1900" max="2100"
                                    value="{{ $defaultYear }}" {{ $isEditMode ? 'disabled' : '' }}
                                >
                            </div>

                            <div class="fr-form-group col-md-4" style="padding:0;">
                                <label for="opening_balance_create">
                                    <i class="fas fa-wallet"></i> Saldo Awal
                                </label>
                                <div class="fr-input-group">
                                    <span class="fr-prefix">Rp</span>
                                    <input
                                        type="number" name="opening_balance" id="opening_balance_create"
                                        class="fr-input" step="0.01" min="0"
                                        value="{{ $defaultOpeningBalance }}" required
                                    >
                                </div>
                            </div>

                        </div>{{-- /form-row --}}

                        {{-- Estimated ending balance --}}
                        <div class="fr-balance-box">
                            <div class="bal-icon"><i class="fas fa-coins"></i></div>
                            <div>
                                <div class="bal-label">Estimasi Saldo Akhir</div>
                                <div class="bal-value" id="estimated-ending-balance">Rp 0,00</div>
                            </div>
                        </div>

                        {{-- Entry lines table --}}
                        <div style="font-size:.78rem;color:var(--muted);margin:0 0 10px;">
                            Kode akun, nama akun, dan nomor faktur tersedia dari database melalui dropdown, tetap bisa diketik manual.
                        </div>
                        <div class="fr-table-wrap">
                            <table class="fr-table" id="profit-loss-lines-table">
                                <thead>
                                    <tr>
                                        <th style="width:120px;"><i class="fas fa-exchange-alt"></i>Jenis</th>
                                        <th style="width:130px;"><i class="fas fa-barcode"></i>Kode Akun</th>
                                        <th style="width:200px;"><i class="fas fa-tag"></i>Nama Akun</th>
                                        <th style="width:180px;"><i class="fas fa-file-invoice"></i>Nomor Faktur</th>
                                        <th><i class="fas fa-align-left"></i>Keterangan</th>
                                        <th style="width:160px;"><i class="fas fa-money-bill-wave"></i>Nominal</th>
                                        <th style="width:110px; text-align:center;"><i class="fas fa-chart-line"></i>Penyusutan</th>
                                        <th style="width:60px; text-align:center;"><i class="fas fa-cog"></i></th>
                                    </tr>
                                </thead>
                                <tbody id="profit-loss-lines-body">
                                    @foreach($entryRows as $index => $entry)
                                        <tr>
                                            <td>
                                                <select name="entries[{{ $index }}][type]" class="td-select" required>
                                                    <option value="INCOME"  {{ ($entry['type'] ?? 'INCOME') === 'INCOME'  ? 'selected' : '' }}>INCOME</option>
                                                    <option value="EXPENSE" {{ ($entry['type'] ?? null)     === 'EXPENSE' ? 'selected' : '' }}>EXPENSE</option>
                                                </select>
                                            </td>
                                            <td>
                                                <input type="text" name="entries[{{ $index }}][line_code]"
                                                    class="td-input js-line-code"
                                                    value="{{ $entry['line_code'] ?? '' }}"
                                                    list="finance-line-code-options"
                                                    placeholder="Pilih kode akun / ketik manual"
                                                    required>
                                            </td>
                                            <td>
                                                <input type="text" name="entries[{{ $index }}][line_label]"
                                                    class="td-input js-line-label"
                                                    value="{{ $entry['line_label'] ?? '' }}"
                                                    list="finance-line-label-options"
                                                    placeholder="Pilih nama akun / ketik manual"
                                                    required>
                                            </td>
                                            <td>
                                                <input type="text" name="entries[{{ $index }}][invoice_number]"
                                                    class="td-input js-invoice-number"
                                                    value="{{ $entry['invoice_number'] ?? '' }}"
                                                    list="finance-invoice-number-options"
                                                    placeholder="Pilih no faktur / ketik manual"
                                                    maxlength="100">
                                            </td>
                                            <td>
                                                <input type="text" name="entries[{{ $index }}][description]"
                                                    class="td-input" value="{{ $entry['description'] ?? '' }}"
                                                    placeholder="Detail pemasukan/pengeluaran">
                                            </td>
                                            <td>
                                                <input type="number" name="entries[{{ $index }}][amount]"
                                                    class="td-input" min="0" step="0.01"
                                                    value="{{ $entry['amount'] ?? '' }}" required>
                                            </td>
                                            <td style="text-align:center;">
                                                <input type="checkbox" class="td-check"
                                                    name="entries[{{ $index }}][is_depreciation]"
                                                    value="1"
                                                    {{ !empty($entry['is_depreciation']) ? 'checked' : '' }}
                                                    style="width:18px;height:18px;accent-color:var(--p1);cursor:pointer;">
                                            </td>
                                            <td style="text-align:center;">
                                                <button type="button"
                                                    class="fr-btn-remove js-remove-row"
                                                    {{ count($entryRows) === 1 ? 'disabled' : '' }}>
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <datalist id="finance-line-code-options">
                            @foreach($accountOptions->filter(fn ($option) => $option['code'] !== '') as $option)
                                <option value="{{ $option['code'] }}">
                                    {{ $option['name'] !== '' ? ($option['code'] . ' - ' . $option['name']) : $option['code'] }}
                                </option>
                            @endforeach
                        </datalist>
                        <datalist id="finance-line-label-options">
                            @foreach($accountOptions->filter(fn ($option) => $option['name'] !== '')->unique(fn ($option) => strtoupper($option['name'])) as $option)
                                <option value="{{ $option['name'] }}">
                                    {{ $option['code'] !== '' ? ($option['name'] . ' (' . $option['code'] . ')') : $option['name'] }}
                                </option>
                            @endforeach
                        </datalist>
                        <datalist id="finance-invoice-number-options">
                            @foreach($invoiceOptions as $invoiceOption)
                                <option value="{{ $invoiceOption }}">{{ $invoiceOption }}</option>
                            @endforeach
                        </datalist>

                    </div>{{-- /card-body --}}

                    <div class="fr-card-footer">
                        <button type="button" class="fr-btn fr-btn-add" id="add-profit-loss-line">
                            <i class="fas fa-plus"></i>
                            <span>Tambah Baris</span>
                        </button>
                        <button type="submit" class="fr-btn fr-btn-success">
                            <i class="fas fa-save"></i>
                            <span>{{ $submitLabel }}</span>
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </div>

</div>{{-- /fr --}}
@endsection

@section('js')
<script>
    (function () {
        const isEditMode = @json($isEditMode);
        const accountOptions = @json($accountOptions);
        const tableBody            = document.getElementById('profit-loss-lines-body');
        const addButton            = document.getElementById('add-profit-loss-line');
        const reportTypeSelect     = document.getElementById('report_type_create');
        const reportDateGroup      = document.getElementById('report_date_group');
        const reportDateInput      = document.getElementById('report_date_create');
        const monthGroup           = document.getElementById('month_group');
        const monthSelect          = document.getElementById('month_create');
        const yearGroup            = document.getElementById('year_group');
        const yearInput            = document.getElementById('year_create');
        const openingBalanceInput  = document.getElementById('opening_balance_create');
        const estimatedEndingBalance = document.getElementById('estimated-ending-balance');
        const accountByCode        = new Map();
        const accountByName        = new Map();

        if (!tableBody || !addButton || !reportTypeSelect || !openingBalanceInput || !estimatedEndingBalance) {
            return;
        }

        function normalizeLookup(value) {
            return String(value ?? '').trim().toUpperCase();
        }

        accountOptions.forEach(function (option) {
            const code = String(option.code ?? '').trim();
            const name = String(option.name ?? '').trim();
            const codeKey = normalizeLookup(code);
            const nameKey = normalizeLookup(name);

            if (codeKey) {
                accountByCode.set(codeKey, option);
            }
            if (nameKey && !accountByName.has(nameKey)) {
                accountByName.set(nameKey, option);
            }
        });

        function parseAmount(value) {
            const number = Number(value);
            return Number.isFinite(number) ? number : 0;
        }

        function formatRupiah(value) {
            return 'Rp ' + Number(value).toLocaleString('id-ID', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        }

        function updateEstimatedBalance() {
            let balance = parseAmount(openingBalanceInput.value);

            Array.from(tableBody.querySelectorAll('tr')).forEach(function (row) {
                const typeSelect       = row.querySelector('select[name*="[type]"]');
                const amountInput      = row.querySelector('input[name*="[amount]"]');
                const depreciationInput = row.querySelector('input[name*="[is_depreciation]"]');

                if (!typeSelect || !amountInput) return;

                const amount = parseAmount(amountInput.value);
                if (typeSelect.value === 'INCOME') {
                    balance += amount;
                    return;
                }

                const isDepreciation = depreciationInput ? depreciationInput.checked : false;
                if (!isDepreciation) balance -= amount;
            });

            estimatedEndingBalance.textContent = formatRupiah(balance);
        }

        function syncAccountByCode(row, forceLabelUpdate) {
            const codeInput = row.querySelector('input[name*="[line_code]"]');
            const labelInput = row.querySelector('input[name*="[line_label]"]');
            if (!codeInput || !labelInput) return;

            const account = accountByCode.get(normalizeLookup(codeInput.value));
            if (!account) return;

            if (forceLabelUpdate || normalizeLookup(labelInput.value) === '') {
                labelInput.value = String(account.name ?? '').trim();
            }
        }

        function syncAccountByName(row) {
            const codeInput = row.querySelector('input[name*="[line_code]"]');
            const labelInput = row.querySelector('input[name*="[line_label]"]');
            if (!codeInput || !labelInput) return;

            const account = accountByName.get(normalizeLookup(labelInput.value));
            if (!account) return;

            if (normalizeLookup(codeInput.value) === '') {
                codeInput.value = String(account.code ?? '').trim();
            }
        }

        function hydrateAccountRow(row) {
            const codeInput = row.querySelector('input[name*="[line_code]"]');
            const labelInput = row.querySelector('input[name*="[line_label]"]');
            if (!codeInput || !labelInput) return;

            if (normalizeLookup(codeInput.value) !== '') {
                syncAccountByCode(row, false);
                return;
            }

            if (normalizeLookup(labelInput.value) !== '') {
                syncAccountByName(row);
            }
        }

        function syncDepreciationCheckbox(row) {
            const typeSelect = row.querySelector('select[name*="[type]"]');
            const checkbox   = row.querySelector('input[type="checkbox"][name*="[is_depreciation]"]');
            if (!typeSelect || !checkbox) return;

            if (typeSelect.value === 'INCOME') {
                checkbox.checked  = false;
                checkbox.disabled = true;
            } else {
                checkbox.disabled = false;
            }
        }

        function renumberRows() {
            Array.from(tableBody.querySelectorAll('tr')).forEach(function (row, index) {
                row.querySelectorAll('input, select').forEach(function (input) {
                    const name = input.getAttribute('name');
                    if (!name) return;
                    input.setAttribute('name', name.replace(/entries\[\d+\]/, 'entries[' + index + ']'));
                });

                const removeButton = row.querySelector('.js-remove-row');
                if (removeButton) {
                    removeButton.disabled = tableBody.querySelectorAll('tr').length === 1;
                }

                hydrateAccountRow(row);
                syncDepreciationCheckbox(row);
            });
        }

        function createRow() {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>
                    <select name="entries[0][type]" class="td-select" required>
                        <option value="INCOME">INCOME</option>
                        <option value="EXPENSE">EXPENSE</option>
                    </select>
                </td>
                <td><input type="text" name="entries[0][line_code]" class="td-input js-line-code" list="finance-line-code-options" placeholder="Pilih kode akun / ketik manual" required></td>
                <td><input type="text" name="entries[0][line_label]" class="td-input js-line-label" list="finance-line-label-options" placeholder="Pilih nama akun / ketik manual" required></td>
                <td><input type="text" name="entries[0][invoice_number]" class="td-input js-invoice-number" list="finance-invoice-number-options" placeholder="Pilih no faktur / ketik manual" maxlength="100"></td>
                <td><input type="text" name="entries[0][description]" class="td-input" placeholder="Detail pemasukan/pengeluaran"></td>
                <td><input type="number" name="entries[0][amount]" class="td-input" min="0" step="0.01" required></td>
                <td style="text-align:center;">
                    <input type="checkbox" name="entries[0][is_depreciation]" value="1"
                        style="width:18px;height:18px;accent-color:var(--p1);cursor:pointer;">
                </td>
                <td style="text-align:center;">
                    <button type="button" class="fr-btn-remove js-remove-row">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            `;

            tableBody.appendChild(row);
            renumberRows();
            updateEstimatedBalance();
        }

        function syncPeriodFields() {
            const reportType = reportTypeSelect.value;
            const isDaily    = reportType === 'DAILY';
            const isMonthly  = reportType === 'MONTHLY';
            const isYearly   = reportType === 'YEARLY';

            reportDateGroup.style.display = isDaily   ? '' : 'none';
            monthGroup.style.display      = isMonthly ? '' : 'none';
            yearGroup.style.display       = (isMonthly || isYearly) ? '' : 'none';

            if (isEditMode) {
                reportDateInput.disabled = true;
                reportDateInput.required = false;
                monthSelect.disabled = true;
                monthSelect.required = false;
                yearInput.disabled = true;
                yearInput.required = false;
                return;
            }

            reportDateInput.disabled = !isDaily;
            reportDateInput.required = isDaily;

            monthSelect.disabled = !isMonthly;
            monthSelect.required = isMonthly;

            yearInput.disabled = !(isMonthly || isYearly);
            yearInput.required = (isMonthly || isYearly);
        }

        addButton.addEventListener('click', createRow);

        tableBody.addEventListener('click', function (event) {
            const button = event.target.closest('.js-remove-row');
            if (!button) return;

            const row = button.closest('tr');
            if (!row) return;

            if (tableBody.querySelectorAll('tr').length === 1) return;

            row.remove();
            renumberRows();
            updateEstimatedBalance();
        });

        tableBody.addEventListener('change', function (event) {
            if (event.target.matches('.js-line-code')) {
                const row = event.target.closest('tr');
                if (row) syncAccountByCode(row, true);
            }

            if (event.target.matches('.js-line-label')) {
                const row = event.target.closest('tr');
                if (row) syncAccountByName(row);
            }

            if (event.target.matches('select[name*="[type]"]')) {
                const row = event.target.closest('tr');
                if (row) syncDepreciationCheckbox(row);
            }

            if (event.target.matches('select[name*="[type]"], input[name*="[amount]"], input[name*="[is_depreciation]"]')) {
                updateEstimatedBalance();
            }
        });

        tableBody.addEventListener('input', function (event) {
            if (event.target.matches('input[name*="[amount]"]')) {
                updateEstimatedBalance();
            }

            if (event.target.matches('.js-line-code')) {
                const row = event.target.closest('tr');
                if (row) syncAccountByCode(row, false);
            }

            if (event.target.matches('.js-line-label')) {
                const row = event.target.closest('tr');
                if (row) syncAccountByName(row);
            }
        });

        reportTypeSelect.addEventListener('change', syncPeriodFields);
        openingBalanceInput.addEventListener('input', updateEstimatedBalance);

        renumberRows();
        syncPeriodFields();
        updateEstimatedBalance();
    })();
</script>
@endsection
