@extends('layouts.app')

@php
    $isYpik = ($variant ?? 'koperasi') === 'ypik';
    $isEditMode = (bool) ($isEdit ?? false);
    $dataset = $dataset ?? ($employee?->dataset ?? 'ypik');

    $title = $isYpik
        ? ($dataset === 'pam_jaya' ? __('app.blast.employee_pamjaya_title') : __('app.blast.employee_ypik_title'))
        : __('app.blast.employee_koperasi_title');
    $subtitle = $isYpik
        ? ($dataset === 'pam_jaya'
            ? __('app.blast.employee_pamjaya_manual_subtitle')
            : __('app.blast.employee_ypik_manual_subtitle'))
        : __('app.blast.employee_koperasi_manual_subtitle');
    $modeTitle = $isEditMode ? __('app.blast.edit_recipient_data') : __('app.blast.manual_recipient_input');

    $indexRouteName = $isYpik
        ? ($dataset === 'pam_jaya'
            ? 'admin.blast.recipients.employees-ypik-pamjaya.index'
            : 'admin.blast.recipients.employees-ypik.index')
        : 'admin.blast.recipients.employees.index';
    $storeRouteName = $isYpik
        ? 'admin.blast.recipients.employees-ypik.store'
        : 'admin.blast.recipients.employees.store';
    $updateRouteName = $isYpik
        ? 'admin.blast.recipients.employees-ypik.update'
        : 'admin.blast.recipients.employees.update';

    $accentColor = $isYpik ? '#0f766e' : '#1d4ed8';
    $accentSoft = $isYpik ? '#f0fdfa' : '#eff6ff';

    $formAction = $isEditMode
        ? route($updateRouteName, $employee->id)
        : route($storeRouteName);
@endphp

@section('title', $modeTitle . ' - ' . $title)

@section('content')
<style>
.emp-manual-wrap {
    max-width: 920px;
    margin: 0 auto;
}

.emp-manual-head {
    border: 1px solid #dbe4f0;
    border-radius: 14px;
    padding: 20px 22px;
    margin-bottom: 14px;
    background: linear-gradient(135deg, {{ $accentColor }}, #0f172a);
    color: #fff;
}

.emp-manual-title {
    font-size: 22px;
    font-weight: 800;
    margin: 0 0 5px;
}

.emp-manual-sub {
    margin: 0;
    font-size: 13px;
    opacity: .92;
}

.emp-manual-card {
    border: 1px solid #dbe4f0;
    border-radius: 14px;
    background: #fff;
    box-shadow: 0 10px 24px rgba(15, 23, 42, .06);
}

.emp-manual-card-body {
    padding: 20px;
}

.emp-manual-alert {
    border: 1px solid #fecaca;
    border-radius: 10px;
    background: #fef2f2;
    color: #991b1b;
    font-size: 12.5px;
    font-weight: 600;
    padding: 10px 12px;
    margin-bottom: 12px;
}

.emp-manual-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 12px;
}

.emp-manual-field.full {
    grid-column: span 2;
}

.emp-manual-label {
    display: block;
    margin-bottom: 6px;
    font-size: 12.5px;
    font-weight: 700;
    color: #334155;
}

.emp-manual-input,
.emp-manual-textarea {
    width: 100%;
    border: 1px solid #dbe4f0;
    border-radius: 9px;
    font-size: 13px;
    padding: 9px 11px;
    background: #fff;
}

.emp-manual-input:focus,
.emp-manual-textarea:focus {
    outline: none;
    border-color: {{ $accentColor }};
    box-shadow: 0 0 0 3px rgba(59, 130, 246, .14);
}

.emp-manual-textarea {
    min-height: 100px;
    resize: vertical;
}

.emp-manual-note {
    margin-top: 8px;
    font-size: 11.5px;
    color: #64748b;
}

.emp-manual-actions {
    margin-top: 14px;
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
}

.emp-manual-btn {
    border-radius: 9px;
    border: 1px solid transparent;
    padding: 9px 12px;
    font-size: 12.5px;
    font-weight: 700;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 6px;
}

.emp-manual-btn.primary {
    background: {{ $accentColor }};
    color: #fff;
}

.emp-manual-btn.light {
    background: {{ $accentSoft }};
    border-color: #dbe4f0;
    color: #334155;
}

@media (max-width: 760px) {
    .emp-manual-grid {
        grid-template-columns: 1fr;
    }

    .emp-manual-field.full {
        grid-column: span 1;
    }
}
</style>

<div class="emp-manual-wrap">
    <div class="emp-manual-head">
        <h2 class="emp-manual-title">{{ $modeTitle }}</h2>
        <p class="emp-manual-sub">{{ $title }}. {{ $subtitle }}</p>
    </div>

    <div class="emp-manual-card">
        <div class="emp-manual-card-body">
            @if ($errors->any())
                <div class="emp-manual-alert">{{ $errors->first() }}</div>
            @endif

            <form method="POST" action="{{ $formAction }}">
                @csrf
                @if($isEditMode)
                    @method('PUT')
                @endif
                @if($isYpik)
                    <input type="hidden" name="dataset" value="{{ $dataset }}">
                @endif

                <div class="emp-manual-grid">
                    <div class="emp-manual-field">
                        <label class="emp-manual-label">{{ __('app.blast.employee_name_required') }}</label>
                        <input
                            type="text"
                            name="nama_karyawan"
                            class="emp-manual-input"
                            value="{{ old('nama_karyawan', $employee?->nama_karyawan) }}"
                            required
                        >
                    </div>

                    <div class="emp-manual-field">
                        <label class="emp-manual-label">{{ __('app.blast.institution') }}</label>
                        <input
                            type="text"
                            name="instansi"
                            class="emp-manual-input"
                            value="{{ old('instansi', $employee?->instansi) }}"
                        >
                    </div>

                    <div class="emp-manual-field">
                        <label class="emp-manual-label">{{ __('app.blast.guardian_name_field') }}</label>
                        <input
                            type="text"
                            name="nama_wali"
                            class="emp-manual-input"
                            value="{{ old('nama_wali', $employee?->nama_wali) }}"
                        >
                    </div>

                    <div class="emp-manual-field">
                        <label class="emp-manual-label">{{ __('app.blast.whatsapp') }}</label>
                        <input
                            type="text"
                            name="wa_karyawan"
                            class="emp-manual-input"
                            value="{{ old('wa_karyawan', $employee?->wa_karyawan) }}"
                            placeholder="{{ __('app.blast.phone_placeholder') }}"
                        >
                    </div>

                    <div class="emp-manual-field full">
                        <label class="emp-manual-label">{{ __('app.blast.email') }}</label>
                        <input
                            type="email"
                            name="email_karyawan"
                            class="emp-manual-input"
                            value="{{ old('email_karyawan', $employee?->email_karyawan) }}"
                            placeholder="nama@domain.com"
                        >
                        <div class="emp-manual-note">{{ __('app.blast.contact_minimum_note') }}</div>
                    </div>

                    <div class="emp-manual-field full">
                        <label class="emp-manual-label">{{ __('app.blast.notes') }}</label>
                        <textarea name="catatan" class="emp-manual-textarea">{{ old('catatan', $employee?->catatan) }}</textarea>
                    </div>
                </div>

                <div class="emp-manual-actions">
                    <button type="submit" class="emp-manual-btn primary">
                        <i class="fas fa-save"></i>
                        {{ $isEditMode ? __('app.blast.update_data') : __('app.blast.save_data') }}
                    </button>
                    <a href="{{ route($indexRouteName) }}" class="emp-manual-btn light">
                        <i class="fas fa-arrow-left"></i> {{ __('app.blast.back') }}
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
