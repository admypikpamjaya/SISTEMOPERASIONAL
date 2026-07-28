@extends('layouts.app')

@section('title', __('app.finance.student_arrears'))

@section('content')
@php
    $filters = $filters ?? [];
    $stats = $stats ?? [];
    $editRecord = $editRecord ?? null;
    $whatsappTemplates = $whatsappTemplates ?? collect();
    $kelasOptions = $kelasOptions ?? collect();
    $whatsappDevices = collect($whatsappDevices ?? []);
    $activeWhatsappDeviceId = $activeWhatsappDeviceId ?? null;
    $whatsappDeviceError = $whatsappDeviceError ?? null;
    $defaultSyncDate = now();
    $defaultSyncMonth = $defaultSyncMonth ?? __('app.finance.months.' . $defaultSyncDate->month) . ' ' . $defaultSyncDate->year;
    $formatRupiah = static fn ($value) => 'Rp ' . number_format((float) $value, 0, ',', '.');
    $selectedWhatsappDeviceId = old('device_id', '');
    $activeWhatsappDevice = $activeWhatsappDeviceId
        ? $whatsappDevices->first(fn ($device) => (string) ($device['deviceId'] ?? '') === (string) $activeWhatsappDeviceId)
        : null;
    $activeWhatsappDeviceLabel = is_array($activeWhatsappDevice)
        ? trim((string) ($activeWhatsappDevice['label'] ?? $activeWhatsappDeviceId))
        : $activeWhatsappDeviceId;

    $sourceLabels = [
        'excel' => __('app.finance.source_excel'),
        'manual' => __('app.finance.source_manual'),
        'database' => __('app.finance.source_student_db'),
    ];

    $matchLabels = [
        'matched' => __('app.finance.status_matched'),
        'unmatched' => __('app.finance.status_unmatched'),
        'multiple' => __('app.finance.status_multiple'),
        'manual' => __('app.finance.status_manual'),
    ];

    $blastLabels = [
        'draft' => __('app.finance.status_draft'),
        'queued' => __('app.finance.status_queued'),
        'sent' => __('app.finance.status_sent'),
        'failed' => __('app.finance.status_failed'),
    ];
@endphp

<style>
@import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');

:root {
    --tg-bg: #ecf2f9;
    --tg-card: #ffffff;
    --tg-border: #dce6f4;
    --tg-text: #0f172a;
    --tg-muted: #64748b;
    --tg-primary: #1e40af;
    --tg-primary-soft: #3b82f6;
    --tg-success: #16a34a;
    --tg-warning: #d97706;
    --tg-danger: #dc2626;
    --tg-shadow: 0 10px 28px rgba(15, 23, 42, .08);
}

.tg-shell {
    font-family: 'Plus Jakarta Sans', sans-serif;
    color: var(--tg-text);
    padding: 24px;
    min-height: calc(100vh - 60px);
    background:
        radial-gradient(circle at 92% -8%, rgba(59, 130, 246, .18) 0%, transparent 33%),
        radial-gradient(circle at -5% 25%, rgba(37, 99, 235, .10) 0%, transparent 40%),
        var(--tg-bg);
}

.tg-hero {
    border-radius: 22px;
    padding: 28px 30px;
    margin-bottom: 18px;
    background: linear-gradient(135deg, #0f1a3d 0%, #1e3a8a 52%, #2563eb 100%);
    color: #fff;
    box-shadow: 0 16px 34px rgba(15, 26, 61, .28);
    position: relative;
    overflow: hidden;
}

.tg-hero::before {
    content: '';
    position: absolute;
    right: -80px;
    top: -80px;
    width: 240px;
    height: 240px;
    border-radius: 50%;
    background: radial-gradient(circle, rgba(255, 255, 255, .18) 0%, transparent 66%);
}

.tg-hero h1 {
    margin: 0 0 8px;
    font-size: 30px;
    font-weight: 800;
    letter-spacing: -.02em;
}

.tg-hero p {
    margin: 0;
    max-width: 760px;
    font-size: 14px;
    line-height: 1.65;
    opacity: .92;
}

.tg-alert {
    padding: 12px 14px;
    border-radius: 12px;
    font-size: 12.5px;
    font-weight: 700;
    margin-bottom: 12px;
}

.tg-alert.success {
    border: 1px solid #86efac;
    background: #f0fdf4;
    color: #166534;
}

.tg-alert.error {
    border: 1px solid #fecaca;
    background: #fef2f2;
    color: #991b1b;
}

.tg-alert.warn {
    border: 1px solid #fde68a;
    background: #fffbeb;
    color: #92400e;
}

.tg-metrics {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 12px;
    margin-bottom: 16px;
}

.tg-metric {
    border: 1px solid var(--tg-border);
    border-radius: 16px;
    background: var(--tg-card);
    padding: 16px;
    box-shadow: var(--tg-shadow);
}

.tg-metric .label {
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: .04em;
    font-weight: 800;
    color: var(--tg-muted);
    margin-bottom: 8px;
}

.tg-metric .value {
    font-size: 26px;
    font-weight: 800;
    color: #0b2d7a;
    line-height: 1.05;
}

.tg-panel {
    border: 1px solid var(--tg-border);
    border-radius: 18px;
    background: var(--tg-card);
    box-shadow: var(--tg-shadow);
    margin-bottom: 16px;
    overflow: hidden;
}

.tg-panel-head {
    padding: 14px 18px;
    border-bottom: 1px solid var(--tg-border);
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    background: linear-gradient(180deg, #f8fbff, #f3f8ff);
}

.tg-panel-title {
    margin: 0;
    font-size: 14px;
    font-weight: 800;
    color: #1e3a8a;
}

.tg-panel-note {
    margin: 0;
    font-size: 12px;
    color: var(--tg-muted);
}

.tg-panel-body {
    padding: 16px 18px 18px;
}

.tg-actions-grid {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 12px;
}

.tg-action-card {
    border: 1px solid var(--tg-border);
    border-radius: 14px;
    padding: 14px;
    background: #fbfdff;
}

.tg-action-label {
    font-size: 12px;
    font-weight: 800;
    margin-bottom: 8px;
    color: #1e3a8a;
}

.tg-row {
    display: flex;
    gap: 8px;
    align-items: center;
    flex-wrap: wrap;
}

.tg-form-grid {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 12px;
}

.tg-field {
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.tg-label {
    font-size: 12px;
    font-weight: 700;
    color: #334155;
}

.tg-input,
.tg-select {
    width: 100%;
    border: 1px solid var(--tg-border);
    border-radius: 10px;
    padding: 9px 11px;
    font-size: 12.5px;
    font-family: inherit;
    color: var(--tg-text);
    background: #fff;
}

.tg-input:focus,
.tg-select:focus {
    outline: none;
    border-color: #93c5fd;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, .16);
}

.tg-actions {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
}

.tg-btn {
    border: 1px solid transparent;
    border-radius: 10px;
    padding: 9px 13px;
    font-size: 12px;
    font-weight: 800;
    font-family: inherit;
    cursor: pointer;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    transition: .15s ease;
}

.tg-btn:hover {
    transform: translateY(-1px);
}

.tg-btn.primary {
    color: #fff;
    background: linear-gradient(135deg, #1d4ed8, #2563eb);
}

.tg-btn.ghost {
    color: #1d4ed8;
    border-color: #bfdbfe;
    background: #eff6ff;
}

.tg-btn.warn {
    color: #fff;
    background: linear-gradient(135deg, #d97706, #b45309);
}

.tg-btn.danger {
    color: #fff;
    background: linear-gradient(135deg, #dc2626, #b91c1c);
}

.tg-file-wrap {
    position: relative;
    overflow: hidden;
}

.tg-file-wrap input[type=file] {
    position: absolute;
    inset: 0;
    opacity: 0;
    cursor: pointer;
}

.tg-hint {
    margin-top: 10px;
    font-size: 12px;
    color: var(--tg-muted);
    line-height: 1.6;
}

.tg-table-wrap {
    border: 1px solid var(--tg-border);
    border-radius: 14px;
    overflow: auto;
}

.tg-table {
    width: 100%;
    border-collapse: collapse;
    min-width: 1120px;
}

.tg-table th {
    position: sticky;
    top: 0;
    z-index: 1;
    background: #f4f8ff;
    color: #5b6b85;
    text-transform: uppercase;
    letter-spacing: .05em;
    font-size: 10.5px;
    font-weight: 800;
    padding: 10px 12px;
    border-bottom: 1px solid var(--tg-border);
    white-space: nowrap;
}

.tg-checkbox {
    width: 16px;
    height: 16px;
    accent-color: #1d4ed8;
    cursor: pointer;
}

.tg-checkbox:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.tg-table td {
    font-size: 12.5px;
    padding: 11px 12px;
    border-bottom: 1px solid #edf2f9;
    vertical-align: top;
}

.tg-table tr:hover td {
    background: #f8fbff;
}

.tg-name {
    font-weight: 800;
    color: #0f172a;
}

.tg-meta {
    margin-top: 3px;
    color: var(--tg-muted);
    font-size: 11px;
}

.tg-badge {
    border-radius: 999px;
    padding: 4px 9px;
    font-size: 10.5px;
    font-weight: 800;
    display: inline-flex;
}

.tg-badge.match-matched { background: #dcfce7; color: #166534; }
.tg-badge.match-unmatched { background: #fee2e2; color: #991b1b; }
.tg-badge.match-multiple { background: #fef3c7; color: #92400e; }
.tg-badge.match-manual { background: #dbeafe; color: #1d4ed8; }

.tg-badge.blast-draft { background: #e2e8f0; color: #334155; }
.tg-badge.blast-queued { background: #dbeafe; color: #1d4ed8; }
.tg-badge.blast-sent { background: #dcfce7; color: #166534; }
.tg-badge.blast-failed { background: #fee2e2; color: #991b1b; }

.tg-inline-actions {
    display: flex;
    gap: 6px;
    flex-wrap: wrap;
}

.tg-empty {
    padding: 22px;
    text-align: center;
    color: var(--tg-muted);
    font-size: 13px;
}

@media (max-width: 1200px) {
    .tg-actions-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
}

@media (max-width: 1100px) {
    .tg-metrics {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .tg-form-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
}

@media (max-width: 760px) {
    .tg-shell {
        padding: 12px;
    }

    .tg-hero {
        padding: 20px 18px;
        border-radius: 16px;
    }

    .tg-hero h1 {
        font-size: 24px;
    }

    .tg-metrics,
    .tg-actions-grid,
    .tg-form-grid {
        grid-template-columns: 1fr;
    }
}
body.dark-mode .tg-shell {
    color: var(--app-text) !important;
}

body.dark-mode .tg-metric,
body.dark-mode .tg-panel,
body.dark-mode .tg-table-wrap {
    background: var(--app-surface) !important;
    border-color: var(--app-border) !important;
    box-shadow: var(--app-shadow) !important;
}

body.dark-mode .tg-panel-head,
body.dark-mode .tg-action-card {
    background: var(--app-surface-soft) !important;
    border-color: var(--app-border) !important;
}

body.dark-mode .tg-panel-title,
body.dark-mode .tg-name,
body.dark-mode .tg-metric .value {
    color: var(--app-text) !important;
}

body.dark-mode .tg-panel-note,
body.dark-mode .tg-label,
body.dark-mode .tg-meta,
body.dark-mode .tg-empty,
body.dark-mode .tg-hint {
    color: var(--app-text-muted) !important;
}

body.dark-mode .tg-input,
body.dark-mode .tg-select {
    background: var(--app-surface-soft) !important;
    border-color: var(--app-border) !important;
    color: var(--app-text) !important;
}

body.dark-mode .tg-input:focus,
body.dark-mode .tg-select:focus {
    background: var(--app-surface) !important;
    border-color: rgba(96, 165, 250, 0.36) !important;
    box-shadow: 0 0 0 3px rgba(96, 165, 250, 0.14) !important;
}

body.dark-mode .tg-select option {
    background: var(--app-surface) !important;
    color: var(--app-text) !important;
}

body.dark-mode .tg-table th {
    background: var(--app-surface-soft) !important;
    border-color: var(--app-border) !important;
    color: var(--app-text-muted) !important;
}

body.dark-mode .tg-table td {
    background: transparent !important;
    border-color: var(--app-border) !important;
    color: var(--app-text-soft) !important;
}

body.dark-mode .tg-table tr:hover td {
    background: var(--app-row-hover) !important;
}

body.dark-mode .tg-table tbody tr.is-selected td {
    background: var(--app-row-selected) !important;
}

body.dark-mode .tg-btn.ghost {
    background: var(--app-surface-soft) !important;
    border-color: var(--app-border) !important;
    color: #93c5fd !important;
}
</style>

<div class="tg-shell">
    <div
        id="tunggakanAutoSync"
        data-version="{{ $recordsVersion ?? '0' }}"
        data-url="{{ route('admin.blast.tunggakan.version') }}"
        data-editing="{{ $editRecord ? '1' : '0' }}"
        style="display:none;"
    ></div>

    <section class="tg-hero">
        <h1>{{ __('app.finance.student_arrears') }}</h1>
        <p>{{ __('app.finance.student_arrears_desc') }}</p>
    </section>

    @if(session('success'))
        <div class="tg-alert success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="tg-alert error">{{ session('error') }}</div>
    @endif
    @if(session('warning'))
        <div class="tg-alert warn">{{ session('warning') }}</div>
    @endif
    @if($errors->any())
        <div class="tg-alert error">{{ $errors->first() }}</div>
    @endif

    <section class="tg-metrics">
        <article class="tg-metric">
            <div class="label">{{ __('app.finance.total_data') }}</div>
            <div class="value">{{ number_format((int) ($stats['total_records'] ?? 0), 0, ',', '.') }}</div>
        </article>
        <article class="tg-metric">
            <div class="label">{{ __('app.finance.total_nominal') }}</div>
            <div class="value" style="font-size:20px;">{{ $formatRupiah($stats['total_nilai'] ?? 0) }}</div>
        </article>
        <article class="tg-metric">
            <div class="label">{{ __('app.finance.matched_recipient') }}</div>
            <div class="value">{{ number_format((int) ($stats['matched_records'] ?? 0), 0, ',', '.') }}</div>
        </article>
        <article class="tg-metric">
            <div class="label">{{ __('app.finance.blast_sent') }}</div>
            <div class="value">{{ number_format((int) ($stats['blast_sent_records'] ?? 0), 0, ',', '.') }}</div>
        </article>
    </section>

    <section class="tg-panel">
        <div class="tg-panel-head">
            <h2 class="tg-panel-title">{{ __('app.finance.data_integration') }}</h2>
            <p class="tg-panel-note">{{ __('app.finance.student_db_sync_note') }}</p>
        </div>
        <div class="tg-panel-body">
            <div class="tg-actions-grid">
                <div class="tg-action-card">
                    <div class="tg-action-label">{{ __('app.finance.import_excel') }}</div>
                    <form action="{{ route('admin.blast.tunggakan.import') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <label class="tg-btn primary tg-file-wrap">
                            {{ __('app.finance.choose_excel_file') }}
                            <input type="file" name="file" accept=".xlsx,.xls,.csv" onchange="if(this.files.length){ this.form.submit(); }" required>
                        </label>
                    </form>
                    <div class="tg-hint">
                        {!! __('app.finance.manual_import_format_hint', [
                            'format' => '<strong>'.e(__('app.finance.arrears_import_format_columns')).'</strong>',
                            'amount' => '<strong>3.100.000</strong>',
                            'rupiah_amount' => '<strong>Rp | 3.100.000</strong>',
                        ]) !!}
                    </div>
                </div>

                <div class="tg-action-card">
                    <div class="tg-action-label">{{ __('app.finance.sync_student_recipients') }}</div>
                    <form action="{{ route('admin.blast.tunggakan.sync-db') }}" method="POST" onsubmit="return confirm(@json(__('app.finance.sync_student_recipients_confirm')));">
                        @csrf
                        <div class="tg-row">
                            <input class="tg-input" style="min-width:200px;" type="text" name="bulan_sync" value="{{ old('bulan_sync', $defaultSyncMonth) }}" placeholder="{{ __('app.finance.period_sync_placeholder') }}">
                            <button type="submit" class="tg-btn warn">{{ __('app.finance.sync_db') }}</button>
                        </div>
                    </form>
                    <div class="tg-hint">{{ __('app.finance.student_sync_auto_match_hint') }}</div>
                </div>

                <div class="tg-action-card">
                    <div class="tg-action-label">{{ __('app.finance.whatsapp_arrears_blast') }}</div>
                    <form id="tunggakan-blast-form" action="{{ route('admin.blast.tunggakan.blast-whatsapp') }}" method="POST">
                        @csrf
                        <div class="tg-field" style="margin-bottom:10px;">
                            <select class="tg-select" name="template_id">
                                <option value="">{{ __('app.finance.default_arrears_template') }}</option>
                                @foreach($whatsappTemplates as $template)
                                    <option value="{{ $template->id }}">{{ $template->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="tg-field" style="margin-bottom:10px;">
                            <select class="tg-select" name="device_id">
                                <option value="">
                                    {{ $activeWhatsappDeviceLabel ? __('app.finance.follow_active_gateway_with_device', ['device' => $activeWhatsappDeviceLabel]) : __('app.finance.follow_active_gateway') }}
                                </option>
                                @foreach($whatsappDevices as $device)
                                    @php
                                        $deviceId = trim((string) ($device['deviceId'] ?? ''));
                                        $deviceLabel = trim((string) ($device['label'] ?? $deviceId));
                                        $deviceStatus = trim((string) ($device['status'] ?? ''));
                                        $deviceNotes = [];

                                        if ((bool) ($device['isActive'] ?? false)) {
                                            $deviceNotes[] = __('app.finance.active_status');
                                        }

                                        if ($deviceStatus !== '') {
                                            $deviceNotes[] = strtolower($deviceStatus);
                                        }

                                        $deviceText = $deviceLabel !== '' ? $deviceLabel : $deviceId;
                                        if ($deviceId !== '' && $deviceLabel !== $deviceId) {
                                            $deviceText .= ' (' . $deviceId . ')';
                                        }

                                        if ($deviceNotes !== []) {
                                            $deviceText .= ' - ' . implode(', ', array_unique($deviceNotes));
                                        }
                                    @endphp
                                    @if($deviceId !== '')
                                        <option value="{{ $deviceId }}" @selected($selectedWhatsappDeviceId === $deviceId)>{{ $deviceText }}</option>
                                    @endif
                                @endforeach
                            </select>
                        </div>
                        <div class="tg-field" style="margin-bottom:10px;">
                            <label class="tg-label" for="blastLimitInput">{{ __('app.finance.blast_limit') }}</label>
                            <input
                                class="tg-input"
                                id="blastLimitInput"
                                type="number"
                                name="blast_limit"
                                min="1"
                                max="10000"
                                step="1"
                                value="{{ old('blast_limit') }}"
                                placeholder="{{ __('app.finance.blast_limit_placeholder') }}"
                            >
                        </div>
                        <div class="tg-actions">
                            <button type="submit" class="tg-btn primary" name="blast_mode" value="all">{{ __('app.finance.blast_all_draft_failed') }}</button>
                            <button type="submit" class="tg-btn ghost" name="blast_mode" value="selected" id="blastSelectedBtn" disabled>{{ __('app.finance.blast_selected') }}</button>
                        </div>
                    </form>
                    <div class="tg-hint">
                        {!! __('app.finance.arrears_blast_processing_hint', [
                            'matched' => '<strong>'.e(__('app.finance.matched_student_data')).'</strong>',
                            'phone' => '<strong>'.e(__('app.finance.valid_phone_number')).'</strong>',
                        ]) !!}
                        @if($whatsappDeviceError)
                            <br>{!! __('app.finance.device_load_failed', ['error' => '<strong>'.e($whatsappDeviceError).'</strong>']) !!}
                        @elseif($activeWhatsappDeviceLabel)
                            <br>{!! __('app.finance.active_device', ['device' => '<strong>'.e($activeWhatsappDeviceLabel).'</strong>']) !!}
                        @endif
                    </div>
                </div>

                <div class="tg-action-card">
                    <div class="tg-action-label">{{ __('app.finance.template_blasting') }}</div>
                    <form action="{{ route('admin.blast.tunggakan.template-default') }}" method="POST">
                        @csrf
                        <button type="submit" class="tg-btn ghost">{{ __('app.finance.generate_default_template') }}</button>
                    </form>
                    <div class="tg-hint">
                        {!! __('app.finance.placeholder_list', [
                            'placeholders' => '<strong>{Nama_Siswa}</strong>, <strong>{Nomor_VA}</strong>, <strong>{Nominal}</strong>, <strong>{bulan_tunggakan}</strong>, <strong>{nilai_tunggakan_rupiah}</strong>, <strong>{nominal_tunggakan_rupiah}</strong>, <strong>{total_tunggakan_rupiah}</strong>, <strong>{tagihan_rupiah}</strong>',
                        ]) !!}
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="tg-panel">
        <div class="tg-panel-head">
            <h2 class="tg-panel-title">{{ __('app.finance.danger_zone') }}</h2>
            <p class="tg-panel-note">{{ __('app.finance.delete_all_arrears_note') }}</p>
        </div>
        <div class="tg-panel-body">
            <form action="{{ route('admin.blast.tunggakan.destroy-all') }}" method="POST" onsubmit="return confirm(@json(__('app.finance.delete_all_arrears_confirm')));">
                @csrf
                @method('DELETE')
                <button type="submit" class="tg-btn danger">{{ __('app.finance.delete_all_bills') }}</button>
            </form>
        </div>
    </section>

    <section class="tg-panel">
        <div class="tg-panel-head">
            <h2 class="tg-panel-title">{{ $editRecord ? __('app.finance.edit_arrears_data') : __('app.finance.manual_arrears_input') }}</h2>
            @if($editRecord)
                <a href="{{ route('admin.blast.tunggakan.index') }}" class="tg-btn ghost">{{ __('app.finance.cancel_edit') }}</a>
            @endif
        </div>
        <div class="tg-panel-body">
            <form action="{{ $editRecord ? route('admin.blast.tunggakan.update', $editRecord->id) : route('admin.blast.tunggakan.manual.store') }}" method="POST">
                @csrf
                @if($editRecord)
                    @method('PUT')
                @endif

                <div class="tg-form-grid">
                    <div class="tg-field">
                        <label class="tg-label">{{ __('app.finance.order_no_optional') }}</label>
                        <input class="tg-input" type="number" name="no_urut" min="1" max="999999" value="{{ old('no_urut', $editRecord?->no_urut) }}">
                    </div>
                    <div class="tg-field">
                        <label class="tg-label">{{ __('app.finance.class') }}</label>
                        <input class="tg-input" type="text" name="kelas" maxlength="100" value="{{ old('kelas', $editRecord?->kelas) }}" placeholder="{{ __('app.finance.class_placeholder') }}">
                    </div>
                    <div class="tg-field">
                        <label class="tg-label">{{ __('app.finance.student_name') }}</label>
                        <input class="tg-input" type="text" name="nama_murid" maxlength="255" value="{{ old('nama_murid', $editRecord?->nama_murid) }}" required>
                    </div>
                    <div class="tg-field">
                        <label class="tg-label">{{ __('app.finance.phone_no') }} ({{ __('app.finance.optional') }})</label>
                        <input class="tg-input" type="text" name="no_telepon" maxlength="30" value="{{ old('no_telepon', $editRecord?->no_telepon) }}" placeholder="{{ __('app.finance.phone_placeholder') }}">
                    </div>
                    <div class="tg-field">
                        <label class="tg-label">{{ __('app.finance.period_month') }}</label>
                        <input class="tg-input" type="text" name="bulan" maxlength="100" value="{{ old('bulan', $editRecord?->bulan) }}" placeholder="{{ __('app.finance.period_month_placeholder') }}" required>
                    </div>
                    <div class="tg-field" style="grid-column:1 / -1;">
                        <label class="tg-label">{{ __('app.finance.rupiah_value') }}</label>
                        <input class="tg-input" type="text" name="nilai" maxlength="50" value="{{ old('nilai', $editRecord ? $formatRupiah($editRecord->nilai) : null) }}" placeholder="{{ __('app.finance.rupiah_placeholder') }}" required>
                    </div>
                </div>

                <div class="tg-actions" style="margin-top:12px;">
                    <button type="submit" class="tg-btn primary">{{ $editRecord ? __('app.finance.save_changes') : __('app.finance.add_data') }}</button>
                </div>
            </form>
        </div>
    </section>

    <section class="tg-panel">
        <div class="tg-panel-head">
            <h2 class="tg-panel-title">{{ __('app.finance.arrears_data_list') }}</h2>
            <p class="tg-panel-note">{{ __('app.finance.needs_review_count', ['count' => number_format((int) ($stats['needs_review_records'] ?? 0), 0, ',', '.')]) }}</p>
        </div>
        <div class="tg-panel-body">
            <form method="GET" action="{{ route('admin.blast.tunggakan.index') }}" class="tg-form-grid" style="margin-bottom:14px;">
                <div class="tg-field">
                    <label class="tg-label">{{ __('app.finance.search') }}</label>
                    <input class="tg-input" type="text" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="{{ __('app.finance.arrears_search_placeholder') }}">
                </div>
                <div class="tg-field">
                    <label class="tg-label">{{ __('app.finance.class') }}</label>
                    <select class="tg-select" name="kelas">
                        <option value="">{{ __('app.finance.all_classes') }}</option>
                        @foreach($kelasOptions as $kelasOption)
                            <option value="{{ $kelasOption }}" @selected(($filters['kelas'] ?? '') === $kelasOption)>{{ $kelasOption }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="tg-field">
                    <label class="tg-label">{{ __('app.finance.source') }}</label>
                    <select class="tg-select" name="source_type">
                        <option value="all" @selected(($filters['source_type'] ?? 'all') === 'all')>{{ __('app.finance.all') }}</option>
                        <option value="excel" @selected(($filters['source_type'] ?? 'all') === 'excel')>{{ __('app.finance.source_excel') }}</option>
                        <option value="manual" @selected(($filters['source_type'] ?? 'all') === 'manual')>{{ __('app.finance.source_manual') }}</option>
                        <option value="database" @selected(($filters['source_type'] ?? 'all') === 'database')>{{ __('app.finance.source_student_db') }}</option>
                    </select>
                </div>
                <div class="tg-field">
                    <label class="tg-label">{{ __('app.finance.match_status') }}</label>
                    <select class="tg-select" name="match_status">
                        <option value="all" @selected(($filters['match_status'] ?? 'all') === 'all')>{{ __('app.finance.all') }}</option>
                        <option value="matched" @selected(($filters['match_status'] ?? 'all') === 'matched')>{{ __('app.finance.status_matched') }}</option>
                        <option value="unmatched" @selected(($filters['match_status'] ?? 'all') === 'unmatched')>{{ __('app.finance.status_unmatched') }}</option>
                        <option value="multiple" @selected(($filters['match_status'] ?? 'all') === 'multiple')>{{ __('app.finance.status_multiple') }}</option>
                        <option value="manual" @selected(($filters['match_status'] ?? 'all') === 'manual')>{{ __('app.finance.status_manual') }}</option>
                    </select>
                </div>
                <div class="tg-field">
                    <label class="tg-label">{{ __('app.finance.blast_status') }}</label>
                    <select class="tg-select" name="blast_status">
                        <option value="all" @selected(($filters['blast_status'] ?? 'all') === 'all')>{{ __('app.finance.all') }}</option>
                        <option value="draft" @selected(($filters['blast_status'] ?? 'all') === 'draft')>{{ __('app.finance.status_draft') }}</option>
                        <option value="queued" @selected(($filters['blast_status'] ?? 'all') === 'queued')>{{ __('app.finance.status_queued') }}</option>
                        <option value="sent" @selected(($filters['blast_status'] ?? 'all') === 'sent')>{{ __('app.finance.status_sent') }}</option>
                        <option value="failed" @selected(($filters['blast_status'] ?? 'all') === 'failed')>{{ __('app.finance.status_failed') }}</option>
                    </select>
                </div>
                <div class="tg-field">
                    <label class="tg-label">{{ __('app.finance.per_page') }}</label>
                    <select class="tg-select" name="per_page">
                        @foreach([20, 50, 100, 200] as $size)
                            <option value="{{ $size }}" @selected((int) ($filters['per_page'] ?? 50) === $size)>{{ $size }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="tg-field" style="justify-content:flex-end;">
                    <label class="tg-label">&nbsp;</label>
                    <div class="tg-actions">
                        <button type="submit" class="tg-btn primary">{{ __('app.finance.apply_filter') }}</button>
                        <a href="{{ route('admin.blast.tunggakan.index') }}" class="tg-btn ghost">{{ __('app.finance.reset') }}</a>
                    </div>
                </div>
            </form>

            <div class="tg-table-wrap">
                <table class="tg-table">
                    <thead>
                        <tr>
                            <th style="width:46px;">
                                <input type="checkbox" class="tg-checkbox" id="selectAllTunggakan">
                            </th>
                            <th style="width:52px;">{{ __('app.finance.no') }}</th>
                            <th>{{ __('app.finance.student_name') }}</th>
                            <th>{{ __('app.finance.maybank_va') }}</th>
                            <th>{{ __('app.finance.class') }}</th>
                            <th>{{ __('app.finance.month_short') }}</th>
                            <th>{{ __('app.finance.value') }}</th>
                            <th>{{ __('app.finance.phone_no') }}</th>
                            <th>{{ __('app.finance.source') }}</th>
                            <th>{{ __('app.finance.match_status') }}</th>
                            <th>{{ __('app.finance.blast_status') }}</th>
                            <th>{{ __('app.finance.notes_label') }}</th>
                            <th style="width:130px;">{{ __('app.finance.action') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($records as $record)
                            @php
                                $source = strtolower((string) optional($record->batch)->source_type);
                                $match = strtolower((string) $record->match_status);
                                $blast = strtolower((string) $record->blast_status);
                                $hasPhone = trim((string) $record->no_telepon) !== '';
                                $canMatch = $record->match_status === 'matched'
                                    && $record->recipient_source === 'siswa'
                                    && !empty($record->recipient_id);
                                $isSelectable = in_array($blast, ['draft', 'failed'], true) && ($canMatch || $hasPhone);
                                $selectHint = '';
                                if (!$isSelectable) {
                                    $selectHint = in_array($blast, ['draft', 'failed'], true)
                                        ? __('app.finance.not_matched_no_phone')
                                        : __('app.finance.blast_not_draft_failed');
                                }
                            @endphp
                            <tr>
                                <td>
                                    <input
                                        type="checkbox"
                                        class="tg-checkbox tunggakan-checkbox"
                                        name="selected_ids[]"
                                        value="{{ $record->id }}"
                                        form="tunggakan-blast-form"
                                        @disabled(!$isSelectable)
                                        @if(!$isSelectable) title="{{ $selectHint }}" @endif
                                    >
                                </td>
                                <td>{{ ($records->currentPage() - 1) * $records->perPage() + $loop->iteration }}</td>
                                <td>
                                    <div class="tg-name">{{ $record->nama_murid }}</div>
                                    @if(!empty($record->recipient_id))
                                        <div class="tg-meta">{{ __('app.finance.recipient_label') }}: {{ $record->recipient_source }} / {{ $record->recipient_id }}</div>
                                    @endif
                                </td>
                                <td>{{ $record->raw_payload['nomor_va'] ?? '-' }}</td>
                                <td>{{ $record->kelas ?? '-' }}</td>
                                <td>{{ $record->bulan }}</td>
                                <td>{{ $formatRupiah($record->nilai) }}</td>
                                <td>{{ $record->no_telepon ?: '-' }}</td>
                                <td>
                                    <span class="tg-meta">{{ $sourceLabels[$source] ?? ucfirst($source ?: '-') }}</span><br>
                                    <span class="tg-meta">{{ optional($record->batch)->source_reference ?? '-' }}</span>
                                </td>
                                <td>
                                    <span class="tg-badge match-{{ $match }}">{{ $matchLabels[$match] ?? strtoupper($match) }}</span>
                                </td>
                                <td>
                                    <span class="tg-badge blast-{{ $blast }}">{{ $blastLabels[$blast] ?? strtoupper($blast) }}</span>
                                </td>
                                <td><span class="tg-meta">{{ $record->match_notes ?? '-' }}</span></td>
                                <td>
                                    <div class="tg-inline-actions">
                                        <a class="tg-btn ghost" href="{{ route('admin.blast.tunggakan.index', array_merge(request()->query(), ['edit' => $record->id])) }}">{{ __('app.finance.edit') }}</a>
                                        <form method="POST" action="{{ route('admin.blast.tunggakan.destroy', $record->id) }}" onsubmit="return confirm(@json(__('app.finance.delete_arrears_confirm')));">
                                            @csrf
                                            @method('DELETE')
                                            <button class="tg-btn danger" type="submit">{{ __('app.finance.delete') }}</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="13" class="tg-empty">{{ __('app.finance.no_arrears_data') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($records->hasPages())
                <div style="margin-top:12px;">
                    {{ $records->links() }}
                </div>
            @endif
        </div>
    </section>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const syncNode = document.getElementById('tunggakanAutoSync');
    if (!syncNode) {
        return;
    }

    if (syncNode.dataset.editing === '1') {
        return;
    }

    const endpoint = syncNode.dataset.url;
    let currentVersion = String(syncNode.dataset.version || '0');
    let requestInFlight = false;

    const poll = async () => {
        if (requestInFlight || !endpoint) {
            return;
        }

        requestInFlight = true;

        try {
            const response = await fetch(endpoint, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                cache: 'no-store',
            });

            if (!response.ok) {
                return;
            }

            const payload = await response.json();
            const nextVersion = String(payload.version || '0');
            if (nextVersion !== currentVersion) {
                window.location.reload();
                return;
            }

            currentVersion = nextVersion;
        } catch (error) {
            // silent fail, retry di interval berikutnya
        } finally {
            requestInFlight = false;
        }
    };

    setInterval(poll, 10000);
});
</script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const blastForm = document.getElementById('tunggakan-blast-form');
    const blastSelectedBtn = document.getElementById('blastSelectedBtn');
    const blastLimitInput = document.getElementById('blastLimitInput');
    const selectAll = document.getElementById('selectAllTunggakan');
    const checkboxes = Array.from(document.querySelectorAll('.tunggakan-checkbox'));
    const eligibleCheckboxes = checkboxes.filter(cb => !cb.disabled);
    const financeText = {
        blastSelected: @json(__('app.finance.blast_selected')),
        blastSelectedWithCount: @json(__('app.finance.blast_selected_with_count', ['count' => '__COUNT__'])),
        blastSelectedConfirm: @json(__('app.finance.blast_selected_confirm', ['count' => '__COUNT__'])),
        blastAllConfirm: @json(__('app.finance.blast_all_confirm')),
        blastLimitConfirmSuffix: @json(__('app.finance.blast_limit_confirm_suffix', ['count' => '__COUNT__'])),
    };

    if (!blastForm || !blastSelectedBtn) {
        return;
    }

    const getSelectedCount = () => eligibleCheckboxes.filter(cb => cb.checked).length;
    const getBlastLimit = () => {
        if (!blastLimitInput) {
            return 0;
        }

        const value = Number.parseInt(String(blastLimitInput.value || ''), 10);
        return Number.isFinite(value) && value > 0 ? value : 0;
    };
    const withBlastLimitMessage = (message) => {
        const limit = getBlastLimit();
        if (limit <= 0) {
            return message;
        }

        return `${message}\n${financeText.blastLimitConfirmSuffix.replace('__COUNT__', limit)}`;
    };

    const updateSelectionState = () => {
        const selectedCount = getSelectedCount();
        blastSelectedBtn.disabled = selectedCount === 0;
        blastSelectedBtn.textContent = selectedCount > 0
            ? financeText.blastSelectedWithCount.replace('__COUNT__', selectedCount)
            : financeText.blastSelected;

        if (selectAll) {
            if (eligibleCheckboxes.length === 0) {
                selectAll.checked = false;
                selectAll.indeterminate = false;
                return;
            }

            const totalEligible = eligibleCheckboxes.length;
            selectAll.checked = selectedCount > 0 && selectedCount === totalEligible;
            selectAll.indeterminate = selectedCount > 0 && selectedCount < totalEligible;
        }
    };

    if (selectAll) {
        selectAll.addEventListener('change', () => {
            const isChecked = selectAll.checked;
            eligibleCheckboxes.forEach(cb => { cb.checked = isChecked; });
            updateSelectionState();
        });
    }

    eligibleCheckboxes.forEach(cb => cb.addEventListener('change', updateSelectionState));

    blastForm.addEventListener('submit', (event) => {
        const submitter = event.submitter;
        const mode = submitter ? submitter.value : 'all';
        const selectedCount = getSelectedCount();

        if (mode === 'selected') {
            if (selectedCount === 0) {
                event.preventDefault();
                alert(@json(__('app.finance.select_arrears_required')));
                return;
            }

            const confirmMessage = withBlastLimitMessage(
                financeText.blastSelectedConfirm.replace('__COUNT__', selectedCount)
            );

            if (!confirm(confirmMessage)) {
                event.preventDefault();
            }
            return;
        }

        if (!confirm(withBlastLimitMessage(financeText.blastAllConfirm))) {
            event.preventDefault();
        }
    });

    updateSelectionState();
});
</script>
@endsection
