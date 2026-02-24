@extends('layouts.app')

@section('content')
@php
    $nowWib = now(config('app.timezone'));
@endphp

<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

<style>
    /* ── Design Tokens — Blue palette (matches Finance Dashboard & sidebar) ── */
    :root {
        --p1:        #3B82F6;
        --p2:        #2563EB;
        --p3:        #1D4ED8;
        --grad:      linear-gradient(135deg, #3B82F6 0%, #2563EB 55%, #1D4ED8 100%);
        --grad-hero: linear-gradient(135deg, #1E3A5F 0%, #1E40AF 55%, #2563EB 100%);
        --surface:   #FFFFFF;
        --surface-alt: #F8FAFF;
        --border:    #DBEAFE;
        --text:      #1E293B;
        --muted:     #64748B;
        --success:   #22C55E;
        --s-bg:      #F0FDF4;
        --s-b:       #BBF7D0;
        --warn:      #F59E0B;
        --w-bg:      #FFFBEB;
        --w-b:       #FDE68A;
        --danger:    #EF4444;
        --d-bg:      #FFF1F2;
        --d-b:       #FECDD3;
        --shadow:    0 4px 24px rgba(37,99,235,.09);
        --shadow-lg: 0 8px 32px rgba(37,99,235,.16);
        --radius:    18px;
        --radius-sm: 11px;
        --font:      'Plus Jakarta Sans', 'Nunito', 'Segoe UI', sans-serif;
    }

    .ad, .ad * {
        font-family: var(--font) !important;
        box-sizing: border-box;
    }

    /* Font Awesome protection */
    .ad .fas, .ad .far, .ad .fab {
        font-family: 'Font Awesome 5 Free' !important;
        font-style: normal !important;
        -webkit-font-smoothing: antialiased;
        display: inline-block;
        line-height: 1;
        vertical-align: middle;
    }

    /* ── Card ── */
    .ad-card {
        background: var(--surface);
        border-radius: var(--radius);
        box-shadow: var(--shadow);
        border: 1.5px solid var(--border);
        overflow: hidden;
        margin-bottom: 24px;
        transition: box-shadow .2s, transform .2s;
    }
    .ad-card:hover { box-shadow: var(--shadow-lg); transform: translateY(-2px); }

    /* ── Card header ── */
    .ad-card-header {
        background: var(--grad);
        padding: 15px 22px;
        display: flex; align-items: center;
        justify-content: space-between; gap: 12px;
    }
    .ad-card-header-left { display: flex; align-items: center; gap: 12px; }
    .ad-card-header .hicon {
        width: 30px; height: 30px;
        background: rgba(255,255,255,.18);
        border-radius: 8px;
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0;
    }
    .ad-card-header .hicon .fas { font-size: .88rem !important; color: #fff !important; }
    .ad-card-header h3 { margin: 0; font-size: 1rem; font-weight: 700; color: #fff; }

    /* Result card header — dark gradient */
    .ad-card-header-result { background: var(--grad-hero); }

    /* ── Card body ── */
    .ad-card-body { padding: 22px; }
    .ad-card-body-p0 { padding: 0; }

    /* ── Card footer ── */
    .ad-card-footer {
        padding: 14px 22px;
        background: var(--surface-alt);
        border-top: 1.5px solid var(--border);
    }

    /* ── Form groups ── */
    .ad-form-group { margin-bottom: 18px; }
    .ad-form-group label {
        display: flex; align-items: center; gap: 6px;
        font-size: .74rem; font-weight: 700;
        color: var(--muted); text-transform: uppercase;
        letter-spacing: .04em; margin-bottom: 6px;
    }
    .ad-form-group label .fas {
        font-size: .78rem !important;
        color: var(--p1) !important;
        width: 14px; text-align: center;
    }

    .ad-input, .ad-select {
        width: 100%;
        background: var(--surface-alt);
        border: 1.5px solid var(--border);
        border-radius: var(--radius-sm);
        padding: 10px 14px;
        font-size: .9rem; color: var(--text);
        font-family: var(--font) !important;
        outline: none;
        transition: border-color .2s, box-shadow .2s;
        height: auto; appearance: auto;
    }
    .ad-input:focus, .ad-select:focus {
        border-color: var(--p1);
        box-shadow: 0 0 0 3px rgba(59,130,246,.14);
        background: #fff;
    }
    .ad-input[type="number"] { -moz-appearance: textfield; }
    .ad-input[type="number"]::-webkit-outer-spin-button,
    .ad-input[type="number"]::-webkit-inner-spin-button { -webkit-appearance: none; }

    /* Input group (Rp prefix) */
    .ad-input-group { display: flex; align-items: stretch; }
    .ad-input-group .ad-prefix {
        background: #EFF6FF;
        border: 1.5px solid var(--border);
        border-right: none;
        border-radius: var(--radius-sm) 0 0 var(--radius-sm);
        padding: 10px 14px;
        color: var(--p1); font-weight: 700; font-size: .9rem;
        display: flex; align-items: center;
        white-space: nowrap;
    }
    .ad-input-group .ad-input {
        border-radius: 0 var(--radius-sm) var(--radius-sm) 0;
    }

    .ad-hint {
        font-size: .77rem; color: var(--muted);
        margin-top: 5px; display: flex; align-items: center; gap: 5px;
    }
    .ad-hint .fas { font-size: .72rem !important; color: var(--muted) !important; }

    /* ── Submit button ── */
    .ad-btn-submit {
        width: 100%;
        display: flex; align-items: center; justify-content: center; gap: 9px;
        padding: 12px 20px;
        background: var(--grad);
        color: #fff !important;
        border: none; border-radius: var(--radius-sm);
        font-size: .95rem; font-weight: 700;
        cursor: pointer;
        transition: transform .15s, box-shadow .15s;
        font-family: var(--font) !important;
    }
    .ad-btn-submit:hover { transform: translateY(-1px); box-shadow: 0 4px 16px rgba(37,99,235,.30); }
    .ad-btn-submit .fas { font-size: .92rem !important; color: #fff !important; }
    .ad-btn-submit:disabled { opacity: .65; pointer-events: none; }

    /* ── Result DL ── */
    .ad-result-list { margin: 0; }
    .ad-result-row {
        display: flex; align-items: center;
        padding: 11px 0;
        border-bottom: 1px dashed var(--border);
    }
    .ad-result-row:last-child { border-bottom: none; }
    .ad-result-label {
        flex: 0 0 52%;
        font-size: .84rem; font-weight: 600; color: var(--muted);
        display: flex; align-items: center; gap: 7px;
    }
    .ad-result-label .fas { font-size: .78rem !important; color: var(--p1) !important; width: 15px; text-align: center; }
    .ad-result-value {
        flex: 1;
        font-size: .9rem; font-weight: 700; color: var(--text);
        text-align: right;
    }

    /* pill chips inside result */
    .ad-chip {
        display: inline-flex; align-items: center;
        background: var(--grad);
        color: #fff !important;
        padding: 3px 13px; border-radius: 20px;
        font-size: .78rem; font-weight: 700;
    }

    /* ── Alerts ── */
    .ad-alert {
        border-radius: var(--radius-sm);
        padding: 12px 16px;
        font-size: .88rem; font-weight: 600;
        margin-bottom: 16px;
        display: flex; align-items: center; gap: 8px;
    }
    .ad-alert .fas { font-size: .88rem !important; }
    .ad-alert.d-none { display: none !important; }
    .ad-alert-success { background: var(--s-bg); color: #15803D; border: 1.5px solid var(--s-b); }
    .ad-alert-success .fas { color: #15803D !important; }
    .ad-alert-danger  { background: var(--d-bg); color: #BE123C; border: 1.5px solid var(--d-b); }
    .ad-alert-danger .fas  { color: #BE123C !important; }
    .ad-alert-warning { background: var(--w-bg); color: #92400E; border: 1.5px solid var(--w-b); }
    .ad-alert-warning .fas { color: #92400E !important; }

    /* ── Table ── */
    .ad-table { width: 100%; border-collapse: separate; border-spacing: 0; }
    .ad-table thead th {
        font-size: .71rem; font-weight: 800;
        text-transform: uppercase; letter-spacing: .05em;
        color: var(--muted); padding: 12px 16px;
        background: var(--surface-alt);
        border-bottom: 2px solid var(--border);
        white-space: nowrap;
    }
    .ad-table thead th .fas, .ad-table thead th .far {
        font-size: .72rem !important; color: var(--p1) !important;
        margin-right: 4px; opacity: .85;
    }
    .ad-table tbody tr { transition: background .15s; }
    .ad-table tbody tr:hover { background: #F0F6FF; }
    .ad-table tbody td {
        padding: 13px 16px; vertical-align: middle;
        border-bottom: 1px solid var(--border);
        font-size: .88rem; color: var(--text);
    }
    .ad-table tbody tr:last-child td { border-bottom: none; }

    .ad-badge {
        display: inline-flex; align-items: center; gap: 4px;
        background: var(--grad); color: #fff;
        padding: 3px 11px; border-radius: 20px;
        font-size: .73rem; font-weight: 700;
    }

    .ad-action-group {
        display: inline-flex;
        gap: 6px;
        flex-wrap: wrap;
    }
    .ad-action-link {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        border: 1px solid #bfdbfe;
        background: #eff6ff;
        color: #1d4ed8;
        border-radius: 9px;
        padding: 4px 8px;
        font-size: .72rem;
        font-weight: 700;
        text-decoration: none;
    }
    .ad-action-link:hover {
        text-decoration: none;
        color: #1e3a8a;
        background: #dbeafe;
    }

    .ad-asset-code { font-weight: 700; color: var(--text); }
    .ad-asset-sub  { font-size: .76rem; color: var(--muted); margin-top: 1px; }

    /* ── Empty state ── */
    .ad-empty { text-align: center; padding: 52px 24px; }
    .ad-empty .ei { font-size: 2.8rem; color: #BFDBFE; margin-bottom: 12px; display: block; }
    .ad-empty .ei .fas { font-size: 2.8rem !important; color: #BFDBFE !important; }
    .ad-empty p { color: var(--muted); font-size: .92rem; margin: 0; }

    /* ── Loading spinner ── */
    .btn-loading { position: relative; pointer-events: none; opacity: .7; }
    .btn-loading::after {
        content: '';
        position: absolute; width: 18px; height: 18px;
        top: 50%; left: 50%;
        margin: -9px 0 0 -9px;
        border: 2px solid #fff; border-top-color: transparent;
        border-radius: 50%;
        animation: adSpin .6s linear infinite;
    }
    @keyframes adSpin { to { transform: rotate(360deg); } }

    @media (max-width: 768px) {
        .ad-card-body { padding: 16px; }
        .ad-card-header { flex-direction: column; align-items: flex-start; }
    }
</style>

<div class="ad">

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
            <i class="fas fa-calculator" style="font-size:1.35rem; color:#fff;"></i>
        </div>
        <div>
            <h1 style="font-size:1.3rem; font-weight:800; color:var(--text); margin:0 0 2px; line-height:1.2;">
                Asset Depreciation
            </h1>
            <p style="font-size:.8rem; color:var(--muted); font-weight:500; margin:0;">
                Kalkulasi penyusutan aset metode garis lurus
            </p>
        </div>
    </div>

    {{-- ══ Row 1 : Form + Result ══ --}}
    <div class="row">

        {{-- ── Form Kalkulasi ── --}}
        <div class="col-lg-6 col-md-12">
            <div class="ad-card">
                <div class="ad-card-header">
                    <div class="ad-card-header-left">
                        <span class="hicon"><i class="fas fa-calculator"></i></span>
                        <h3>Kalkulasi Penyusutan Garis Lurus</h3>
                    </div>
                </div>

                <form id="depreciation-form" action="{{ route('finance.depreciation.calc') }}" method="POST">
                    @csrf
                    <div class="ad-card-body">

                        {{-- Asset ID --}}
                        <div class="ad-form-group">
                            <label for="asset_id">
                                <i class="fas fa-barcode"></i> Asset ID
                            </label>
                            <select id="asset_id" name="asset_id" class="ad-select" required>
                                <option value="">Pilih asset dari database</option>
                                @foreach(($assets ?? collect()) as $asset)
                                    <option value="{{ $asset->id }}">
                                        #{{ $asset->id }} - {{ $asset->account_code }} ({{ $asset->category }})
                                    </option>
                                @endforeach
                            </select>
                            <div class="ad-hint">
                                <i class="fas fa-database"></i>
                                Total asset tersedia: {{ ($assets ?? collect())->count() }}
                            </div>
                        </div>

                        {{-- Nilai Perolehan --}}
                        <div class="ad-form-group">
                            <label for="acquisition_cost">
                                <i class="fas fa-money-bill-wave"></i> Nilai Perolehan
                            </label>
                            <div class="ad-input-group">
                                <span class="ad-prefix">Rp</span>
                                <input
                                    type="number" id="acquisition_cost" name="acquisition_cost"
                                    class="ad-input" min="0" step="0.01" required
                                >
                            </div>
                        </div>

                        {{-- Umur Manfaat --}}
                        <div class="ad-form-group">
                            <label for="useful_life_months">
                                <i class="fas fa-clock"></i> Umur Manfaat (bulan)
                            </label>
                            <input
                                type="number" id="useful_life_months" name="useful_life_months"
                                class="ad-input" min="1" required
                            >
                        </div>

                        {{-- Bulan & Tahun --}}
                        <div class="form-row" style="gap:0 12px;">
                            <div class="ad-form-group col-md-6" style="padding:0;">
                                <label for="month">
                                    <i class="fas fa-calendar-alt"></i> Bulan
                                </label>
                                <select id="month" name="month" class="ad-select" required>
                                    @for($m = 1; $m <= 12; $m++)
                                        <option value="{{ $m }}" {{ $nowWib->month === $m ? 'selected' : '' }}>
                                            {{ \Carbon\Carbon::create()->month($m)->format('F') }}
                                        </option>
                                    @endfor
                                </select>
                            </div>
                            <div class="ad-form-group col-md-6" style="padding:0;">
                                <label for="year">
                                    <i class="fas fa-calendar-check"></i> Tahun
                                </label>
                                <input
                                    type="number" id="year" name="year"
                                    class="ad-input" min="1900" max="2100"
                                    value="{{ $nowWib->year }}" required
                                >
                            </div>
                        </div>

                    </div>

                    <div class="ad-card-footer">
                        <button type="submit" class="ad-btn-submit" id="submit-btn">
                            <i class="fas fa-calculator"></i>
                            <span>Hitung Penyusutan</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- ── Hasil Perhitungan ── --}}
        <div class="col-lg-6 col-md-12">
            <div class="ad-card">
                <div class="ad-card-header ad-card-header-result">
                    <div class="ad-card-header-left">
                        <span class="hicon"><i class="fas fa-chart-line"></i></span>
                        <h3>Hasil Perhitungan</h3>
                    </div>
                </div>
                <div class="ad-card-body">

                    {{-- Alert --}}
                    <div id="depreciation-alert" class="ad-alert d-none" role="alert"></div>

                    {{-- Result list --}}
                    <div class="ad-result-list">

                        <div class="ad-result-row">
                            <div class="ad-result-label">
                                <i class="fas fa-barcode"></i> Asset ID
                            </div>
                            <div class="ad-result-value" id="result-asset-id">
                                <span class="ad-chip">-</span>
                            </div>
                        </div>

                        <div class="ad-result-row">
                            <div class="ad-result-label">
                                <i class="fas fa-money-bill-wave"></i> Nilai Perolehan
                            </div>
                            <div class="ad-result-value" id="result-acquisition-cost">-</div>
                        </div>

                        <div class="ad-result-row">
                            <div class="ad-result-label">
                                <i class="fas fa-hourglass-half"></i> Umur Bulan
                            </div>
                            <div class="ad-result-value" id="result-useful-life">
                                <span class="ad-chip">-</span>
                            </div>
                        </div>

                        <div class="ad-result-row">
                            <div class="ad-result-label">
                                <i class="fas fa-divide"></i> Penyusutan / Bulan
                            </div>
                            <div class="ad-result-value" id="result-depreciation-per-month">-</div>
                        </div>

                        <div class="ad-result-row">
                            <div class="ad-result-label">
                                <i class="fas fa-calendar-alt"></i> Periode
                            </div>
                            <div class="ad-result-value" id="result-period">
                                <span class="ad-chip">-</span>
                            </div>
                        </div>

                        <div class="ad-result-row">
                            <div class="ad-result-label">
                                <i class="far fa-clock"></i> Waktu Hitung (WIB)
                            </div>
                            <div class="ad-result-value" id="result-calculated-at" style="font-size:.84rem;">-</div>
                        </div>

                    </div>
                </div>
            </div>
        </div>

    </div>

    {{-- ══ Row 2 : Log Table ══ --}}
    <div class="row">
        <div class="col-12">
            <div class="ad-card">
                <div class="ad-card-header">
                    <div class="ad-card-header-left">
                        <span class="hicon"><i class="fas fa-history"></i></span>
                        <h3>Log Kalkulasi Penyusutan</h3>
                    </div>
                </div>

                <div class="ad-card-body-p0" style="overflow-x:auto;">
                    <table class="ad-table">
                        <thead>
                            <tr>
                                <th><i class="fas fa-hashtag"></i>#</th>
                                <th><i class="far fa-clock"></i>Waktu Hitung (WIB)</th>
                                <th><i class="fas fa-cube"></i>Asset</th>
                                <th><i class="fas fa-calendar-alt"></i>Periode</th>
                                <th><i class="fas fa-money-bill-wave"></i>Nilai Perolehan</th>
                                <th><i class="fas fa-hourglass-half"></i>Umur (Bln)</th>
                                <th><i class="fas fa-divide"></i>Penyusutan / Bulan</th>
                                <th><i class="far fa-user"></i>Dihitung Oleh</th>
                                <th><i class="fas fa-link"></i>Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="depreciation-log-body">
                            @forelse(($logs ?? collect()) as $index => $log)
                                <tr>
                                    <td>
                                        <span class="ad-badge">{{ $index + 1 }}</span>
                                    </td>
                                    <td style="font-size:.84rem;">
                                        {{ $log->calculated_at?->timezone(config('app.timezone'))->format('d/m/Y H:i:s') ?? '-' }}
                                    </td>
                                    <td>
                                        <div class="ad-asset-code">{{ $log->asset?->account_code ?? '-' }}</div>
                                        <div class="ad-asset-sub">ID: {{ $log->asset_id }}</div>
                                    </td>
                                    <td>
                                        <span class="ad-badge">{{ sprintf('%02d/%04d', (int) $log->period_month, (int) $log->period_year) }}</span>
                                    </td>
                                    <td style="font-weight:700; color:var(--p2);">
                                        Rp {{ number_format((float) $log->acquisition_cost, 2, ',', '.') }}
                                    </td>
                                    <td style="font-weight:600; text-align:center;">
                                        {{ (int) $log->useful_life_months }}
                                    </td>
                                    <td style="font-weight:700; color:#16A34A;">
                                        Rp {{ number_format((float) $log->depreciation_per_month, 2, ',', '.') }}
                                    </td>
                                    <td>
                                        <div style="display:flex; align-items:center; gap:7px;">
                                            <span style="width:26px;height:26px;background:#EFF6FF;border-radius:50%;border:1.5px solid #BFDBFE;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                                <i class="fas fa-user" style="font-size:.68rem !important; color:var(--p1) !important;"></i>
                                            </span>
                                            <span style="font-weight:600; font-size:.87rem;">{{ $log->calculator?->name ?? '-' }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="ad-action-group">
                                            <a class="ad-action-link" href="{{ route('finance.depreciation.logs.show', ['log' => $log->id]) }}">
                                                <i class="fas fa-eye"></i> Detail
                                            </a>
                                            <a class="ad-action-link" href="{{ route('finance.depreciation.logs.download', ['log' => $log->id]) }}">
                                                <i class="fas fa-file-pdf"></i> PDF
                                            </a>
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr id="depreciation-log-empty-row">
                                    <td colspan="9">
                                        <div class="ad-empty">
                                            <span class="ei"><i class="fas fa-calculator"></i></span>
                                            <p>Belum ada log kalkulasi penyusutan.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

</div>{{-- /ad --}}
@endsection

@section('js')
<script>
    (function () {
        const form      = document.getElementById('depreciation-form');
        const submitBtn = document.getElementById('submit-btn');
        const alertBox  = document.getElementById('depreciation-alert');
        const logBody   = document.getElementById('depreciation-log-body');
        const logShowUrlTemplate = @json(route('finance.depreciation.logs.show', ['log' => '__LOG_ID__']));
        const logDownloadUrlTemplate = @json(route('finance.depreciation.logs.download', ['log' => '__LOG_ID__']));

        /* ── alert helper ── */
        function setAlert(type, message) {
            const iconMap = { success: 'fa-check-circle', danger: 'fa-times-circle', warning: 'fa-exclamation-triangle' };
            alertBox.className = 'ad-alert ad-alert-' + type;
            alertBox.innerHTML = `<i class="fas ${iconMap[type] || ''}"></i> ${message}`;
            if (type === 'success') setTimeout(() => alertBox.classList.add('d-none'), 5000);
        }

        function formatNumber(value) {
            const n = Number(value);
            if (Number.isNaN(n)) return '-';
            return n.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }

        function escapeHtml(v) {
            return String(v ?? '')
                .replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;')
                .replace(/"/g,'&quot;').replace(/'/g,'&#039;');
        }

        function renumberLogRows() {
            if (!logBody) return;
            Array.from(logBody.querySelectorAll('tr'))
                .filter(r => r.id !== 'depreciation-log-empty-row')
                .forEach((row, i) => {
                    const b = row.querySelector('td:first-child .ad-badge');
                    if (b) b.textContent = String(i + 1);
                });
        }

        function prependLogRow(log) {
            if (!logBody || !log) return;
            const empty = document.getElementById('depreciation-log-empty-row');
            if (empty) empty.remove();

            const logId = String(log.id || '');
            const detailUrl = logShowUrlTemplate.replace('__LOG_ID__', encodeURIComponent(logId));
            const downloadUrl = logDownloadUrlTemplate.replace('__LOG_ID__', encodeURIComponent(logId));

            const row = document.createElement('tr');
            row.innerHTML = `
                <td><span class="ad-badge">1</span></td>
                <td style="font-size:.84rem;">${escapeHtml(log.calculated_at_label || '-')}</td>
                <td>
                    <div class="ad-asset-code">${escapeHtml(log.asset_account_code || '-')}</div>
                    <div class="ad-asset-sub">ID: ${escapeHtml(log.asset_id || '-')}</div>
                </td>
                <td><span class="ad-badge">${escapeHtml(log.period_label || '-')}</span></td>
                <td style="font-weight:700;color:var(--p2);">Rp ${formatNumber(log.acquisition_cost)}</td>
                <td style="font-weight:600;text-align:center;">${escapeHtml(log.useful_life_months ?? '-')}</td>
                <td style="font-weight:700;color:#16A34A;">Rp ${formatNumber(log.depreciation_per_month)}</td>
                <td>
                    <div style="display:flex;align-items:center;gap:7px;">
                        <span style="width:26px;height:26px;background:#EFF6FF;border-radius:50%;border:1.5px solid #BFDBFE;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                            <i class="fas fa-user" style="font-size:.68rem !important;color:var(--p1) !important;"></i>
                        </span>
                        <span style="font-weight:600;font-size:.87rem;">${escapeHtml(log.calculated_by_name || '-')}</span>
                    </div>
                </td>
                <td>
                    <span class="ad-action-group">
                        <a class="ad-action-link" href="${escapeHtml(detailUrl)}">
                            <i class="fas fa-eye"></i> Detail
                        </a>
                        <a class="ad-action-link" href="${escapeHtml(downloadUrl)}">
                            <i class="fas fa-file-pdf"></i> PDF
                        </a>
                    </span>
                </td>
            `;
            logBody.prepend(row);
            renumberLogRows();
        }

        /* ── Form submit ── */
        form.addEventListener('submit', async function (e) {
            e.preventDefault();
            alertBox.classList.add('d-none');
            submitBtn.classList.add('btn-loading');
            submitBtn.disabled = true;

            try {
                const resp = await fetch(form.action, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    },
                    body: new FormData(form)
                });

                const payload = await resp.json();

                if (!resp.ok) {
                    const err = payload.errors
                        ? Object.values(payload.errors)[0][0]
                        : (payload.message || 'Gagal menghitung penyusutan.');
                    setAlert('danger', err);
                    return;
                }

                const data = payload.data || {};

                document.getElementById('result-asset-id').innerHTML =
                    `<span class="ad-chip">${data.asset_id || '-'}</span>`;
                document.getElementById('result-acquisition-cost').textContent =
                    'Rp ' + formatNumber(data.acquisition_cost);
                document.getElementById('result-useful-life').innerHTML =
                    `<span class="ad-chip">${data.useful_life_months ?? '-'}</span>`;
                document.getElementById('result-depreciation-per-month').textContent =
                    'Rp ' + formatNumber(data.depreciation_per_month);

                const periodLabel = (data.period_month && data.period_year)
                    ? String(data.period_month).padStart(2, '0') + '/' + data.period_year
                    : '-';
                document.getElementById('result-period').innerHTML =
                    `<span class="ad-chip">${periodLabel}</span>`;
                document.getElementById('result-calculated-at').textContent =
                    data.calculated_at || '-';

                if (data.log_saved && data.log) {
                    prependLogRow(data.log);
                    setAlert('success', payload.message || 'Perhitungan berhasil.');
                } else {
                    setAlert('warning', payload.message || 'Perhitungan berhasil, tetapi log belum tersimpan.');
                }

            } catch (err) {
                setAlert('danger', 'Terjadi kesalahan saat mengirim request.');
                console.error(err);
            } finally {
                submitBtn.classList.remove('btn-loading');
                submitBtn.disabled = false;
            }
        });

        /* ── Input validation ── */
        document.getElementById('acquisition_cost').addEventListener('input', function () {
            if (this.value < 0) this.value = 0;
        });
        document.getElementById('useful_life_months').addEventListener('input', function () {
            if (this.value < 1) this.value = 1;
        });
    })();
</script>
@endsection
