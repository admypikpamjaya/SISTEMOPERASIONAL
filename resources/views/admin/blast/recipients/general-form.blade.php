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
    max-width: 780px;
    margin: 0 auto;
}

.general-manual-head {
    border: 1px solid #dbe4f0;
    border-radius: 14px;
    padding: 20px 22px;
    margin-bottom: 14px;
    background: linear-gradient(135deg, #0f766e, #0f172a);
    color: #fff;
}

.general-manual-title {
    font-size: 22px;
    font-weight: 800;
    margin: 0 0 5px;
}

.general-manual-sub {
    margin: 0;
    font-size: 13px;
    opacity: .92;
}

.general-manual-card {
    border: 1px solid #dbe4f0;
    border-radius: 14px;
    background: #fff;
    box-shadow: 0 10px 24px rgba(15, 23, 42, .06);
}

.general-manual-card-body {
    padding: 20px;
}

.general-manual-alert {
    border: 1px solid #fecaca;
    border-radius: 10px;
    background: #fef2f2;
    color: #991b1b;
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
    color: #334155;
}

.general-manual-input,
.general-manual-textarea {
    width: 100%;
    border: 1px solid #dbe4f0;
    border-radius: 9px;
    font-size: 13px;
    padding: 9px 11px;
    background: #fff;
}

.general-manual-input:focus,
.general-manual-textarea:focus {
    outline: none;
    border-color: #0f766e;
    box-shadow: 0 0 0 3px rgba(20, 184, 166, .14);
}

.general-manual-textarea {
    min-height: 110px;
    resize: vertical;
}

.general-manual-note {
    margin-top: 8px;
    font-size: 11.5px;
    color: #64748b;
}

.general-manual-actions {
    margin-top: 14px;
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
}

.general-manual-btn {
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

.general-manual-btn.primary {
    background: #0f766e;
    color: #fff;
}

.general-manual-btn.light {
    background: #f0fdfa;
    border-color: #dbe4f0;
    color: #334155;
}

@media (max-width: 760px) {
    .general-manual-grid {
        grid-template-columns: 1fr;
    }

    .general-manual-field.full {
        grid-column: span 1;
    }
}
</style>

<div class="general-manual-wrap">
    <div class="general-manual-head">
        <h2 class="general-manual-title">{{ $modeTitle }}</h2>
        <p class="general-manual-sub">{{ __('app.blast.general_recipient_manual_subtitle') }}</p>
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
