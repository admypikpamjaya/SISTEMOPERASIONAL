@extends('layouts.app')

@php
    $isEditMode = (bool) ($isEdit ?? false);
    $modeTitle = $isEditMode ? __('app.blast.edit_general_recipient') : __('app.blast.add_general_recipient');
    $formAction = $isEditMode
        ? route('admin.blast.recipients.general.update', $recipient->id)
        : route('admin.blast.recipients.general.store');
@endphp

@section('title', $modeTitle . ' - ' . __('app.blast.general_recipient_title'))

@section('content')
<style>
.general-manual-wrap {
    --gm-primary: var(--app-accent);
    --gm-primary-strong: var(--app-accent-strong);
    --gm-bg: var(--app-bg);
    --gm-surface: var(--app-surface);
    --gm-surface-soft: var(--app-surface-soft);
    --gm-border: var(--app-border);
    --gm-text: var(--app-text);
    --gm-text-soft: var(--app-text-soft);
    --gm-text-muted: var(--app-text-muted);
    --gm-danger: var(--danger-color, #ef4444);
    max-width: 880px;
    margin: 0 auto;
    color: var(--gm-text);
    font-family: 'Plus Jakarta Sans', 'Source Sans Pro', sans-serif;
}

.general-manual-head {
    border: 1px solid rgba(255, 255, 255, .16);
    border-radius: var(--radius-md, 14px);
    padding: 20px 22px;
    margin-bottom: 14px;
    background: var(--grad-hero);
    color: #fff;
    box-shadow: var(--app-shadow);
    display: flex;
    align-items: center;
    gap: 14px;
}

.general-manual-head-icon {
    width: 42px;
    height: 42px;
    border-radius: var(--radius-sm, 8px);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: rgba(255, 255, 255, .14);
    border: 1px solid rgba(255, 255, 255, .22);
    flex: 0 0 auto;
}

.general-manual-title {
    font-size: 20px;
    font-weight: 800;
    margin: 0 0 5px;
    letter-spacing: 0;
}

.general-manual-sub {
    margin: 0;
    font-size: 12.5px;
    line-height: 1.45;
    opacity: .92;
}

.general-manual-card {
    border: 1px solid var(--gm-border);
    border-radius: var(--radius-md, 14px);
    background: var(--gm-surface);
    box-shadow: var(--shadow-sm);
    overflow: hidden;
}

.general-manual-card-body {
    padding: 20px 22px 22px;
}

.general-manual-alert {
    border: 1px solid var(--red-border);
    border-radius: var(--radius-sm, 8px);
    background: var(--red-bg);
    color: var(--gm-danger);
    font-size: 12.5px;
    font-weight: 600;
    padding: 10px 12px;
    margin-bottom: 12px;
}

.general-manual-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 12px;
}

.general-manual-field.full {
    grid-column: span 2;
}

.general-manual-label {
    display: block;
    margin-bottom: 6px;
    font-size: 12.5px;
    font-weight: 700;
    color: var(--gm-text-soft);
}

.general-manual-input,
.general-manual-textarea {
    width: 100%;
    border: 1px solid var(--gm-border);
    border-radius: var(--radius-sm, 8px);
    font-size: 13px;
    padding: 9px 11px;
    background: var(--gm-surface);
    color: var(--gm-text);
    transition: border-color .15s ease, box-shadow .15s ease, background .15s ease;
}

.general-manual-input:focus,
.general-manual-textarea:focus {
    outline: none;
    border-color: var(--gm-primary);
    box-shadow: 0 0 0 3px var(--app-row-selected);
}

.general-manual-textarea {
    min-height: 110px;
    resize: vertical;
}

.general-manual-note {
    margin-top: 8px;
    font-size: 11.5px;
    color: var(--gm-text-muted);
}

.general-manual-actions {
    margin-top: 16px;
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
}

.general-manual-btn {
    min-height: 38px;
    border-radius: var(--radius-sm, 8px);
    border: 1px solid transparent;
    padding: 9px 12px;
    font-size: 12.5px;
    font-weight: 700;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    transition: background .15s ease, border-color .15s ease, box-shadow .15s ease, transform .15s ease;
}

.general-manual-btn:hover {
    transform: translateY(-1px);
    text-decoration: none;
}

.general-manual-btn.primary {
    background: linear-gradient(135deg, var(--gm-primary-strong), var(--gm-primary));
    border-color: transparent;
    color: #fff;
    box-shadow: 0 8px 18px var(--app-row-selected-strong);
}

.general-manual-btn.light {
    background: var(--gm-surface-soft);
    border-color: var(--gm-border);
    color: var(--gm-text-soft);
}

body.dark-mode .general-manual-head {
    box-shadow: 0 18px 38px rgba(0, 0, 0, .24);
}

body.dark-mode .general-manual-card {
    box-shadow: none;
}

body.dark-mode .general-manual-btn.primary {
    box-shadow: none;
}

body.dark-mode .general-manual-input,
body.dark-mode .general-manual-textarea,
body.dark-mode .general-manual-btn.light {
    background: var(--gm-surface-soft);
}

@media (max-width: 760px) {
    .general-manual-grid {
        grid-template-columns: 1fr;
    }

    .general-manual-field.full {
        grid-column: span 1;
    }

    .general-manual-head {
        align-items: flex-start;
    }

    .general-manual-actions,
    .general-manual-btn {
        width: 100%;
    }
}
</style>

<div class="general-manual-wrap">
    <div class="general-manual-head">
        <div class="general-manual-head-icon">
            <i class="{{ $isEditMode ? 'fas fa-pen' : 'fas fa-plus' }}"></i>
        </div>
        <div>
            <h2 class="general-manual-title">{{ $modeTitle }}</h2>
            <p class="general-manual-sub">{{ __('app.blast.general_recipient_manual_subtitle') }}</p>
        </div>
    </div>

    <div class="general-manual-card">
        <div class="general-manual-card-body">
            @if ($errors->any())
                <div class="general-manual-alert">{{ $errors->first() }}</div>
            @endif

            <form method="POST" action="{{ $formAction }}">
                @csrf
                @if($isEditMode)
                    @method('PUT')
                @endif

                <div class="general-manual-grid">
                    <div class="general-manual-field">
                        <label class="general-manual-label">{{ __('app.blast.general_name_required') }}</label>
                        <input
                            type="text"
                            name="nama"
                            class="general-manual-input"
                            value="{{ old('nama', $recipient?->nama) }}"
                            required
                        >
                    </div>

                    <div class="general-manual-field">
                        <label class="general-manual-label">{{ __('app.blast.general_event_name') }}</label>
                        <input
                            type="text"
                            name="event_name"
                            class="general-manual-input"
                            value="{{ old('event_name', $recipient?->event_name) }}"
                            placeholder="{{ __('app.blast.general_event_placeholder') }}"
                        >
                    </div>

                    <div class="general-manual-field">
                        <label class="general-manual-label">{{ __('app.blast.whatsapp') }}</label>
                        <input
                            type="text"
                            name="whatsapp"
                            class="general-manual-input"
                            value="{{ old('whatsapp', $recipient?->whatsapp) }}"
                            placeholder="{{ __('app.blast.phone_placeholder') }}"
                            required
                        >
                        <div class="general-manual-note">{{ __('app.blast.general_format_note') }}</div>
                    </div>

                    <div class="general-manual-field">
                        <label class="general-manual-label">{{ __('app.blast.institution') }}</label>
                        <input
                            type="text"
                            name="instansi"
                            class="general-manual-input"
                            value="{{ old('instansi', $recipient?->instansi) }}"
                            placeholder="{{ __('app.blast.general_institution_placeholder') }}"
                        >
                    </div>

                    <div class="general-manual-field">
                        <label class="general-manual-label">{{ __('app.blast.email') }}</label>
                        <input
                            type="email"
                            name="email"
                            class="general-manual-input"
                            value="{{ old('email', $recipient?->email) }}"
                            placeholder="{{ __('app.blast.general_email_placeholder') }}"
                        >
                    </div>

                    <div class="general-manual-field full">
                        <label class="general-manual-label">{{ __('app.blast.certificate_link') }}</label>
                        <input
                            type="text"
                            name="sertifikat"
                            class="general-manual-input"
                            value="{{ old('sertifikat', $recipient?->sertifikat) }}"
                            placeholder="{{ __('app.blast.certificate_link_placeholder') }}"
                        >
                    </div>

                    <div class="general-manual-field full">
                        <label class="general-manual-label">{{ __('app.blast.notes') }}</label>
                        <textarea name="catatan" class="general-manual-textarea" placeholder="{{ __('app.blast.general_notes_placeholder') }}">{{ old('catatan', $recipient?->catatan) }}</textarea>
                    </div>
                </div>

                <div class="general-manual-actions">
                    <button type="submit" class="general-manual-btn primary">
                        <i class="fas fa-save"></i>
                        {{ $isEditMode ? __('app.blast.update_data') : __('app.blast.save_data') }}
                    </button>
                    <a href="{{ route('admin.blast.recipients.general.index') }}" class="general-manual-btn light">
                        <i class="fas fa-arrow-left"></i> {{ __('app.blast.back') }}
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
