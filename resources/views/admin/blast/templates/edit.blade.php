@extends('layouts.app')

@section('title', 'Edit Template Blast')

@section('content')
@php
    $cancelUrl = $returnTo ?: route('admin.blast.templates.index', ['channel' => strtolower((string) $template->channel)]);
    $isActiveOld = old('is_active');
    $isActiveChecked = $isActiveOld !== null ? (bool) $isActiveOld : (bool) $template->is_active;
@endphp

<style>
@import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');

.tplf-page {
    font-family: 'Plus Jakarta Sans', sans-serif;
    color: #0f172a;
    padding: 4px 2px 14px;
}

.tplf-head {
    border-radius: 14px;
    padding: 18px 20px;
    margin-bottom: 14px;
    background: linear-gradient(135deg, #102a66 0%, #1a56db 70%, #2563eb 100%);
    box-shadow: 0 12px 24px rgba(26,86,219,.22);
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
}

.tplf-title {
    font-size: 19px;
    font-weight: 800;
    color: #fff;
}

.tplf-sub {
    margin-top: 3px;
    font-size: 12px;
    color: rgba(255,255,255,.85);
}

.tplf-back {
    border: 1px solid rgba(255,255,255,.4);
    border-radius: 8px;
    color: #fff;
    background: rgba(255,255,255,.1);
    text-decoration: none;
    font-size: 12px;
    font-weight: 700;
    padding: 8px 11px;
    display: inline-flex;
    align-items: center;
    gap: 6px;
}

.tplf-card {
    border: 1px solid #dbe4f0;
    border-radius: 14px;
    background: #fff;
    box-shadow: 0 8px 18px rgba(15,23,42,.06);
    overflow: hidden;
}

.tplf-body {
    padding: 16px;
}

.tplf-alert {
    border-radius: 10px;
    border: 1px solid #fecaca;
    background: #fef2f2;
    color: #991b1b;
    font-size: 12.5px;
    font-weight: 600;
    padding: 10px 12px;
    margin-bottom: 12px;
}

.tplf-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0,1fr));
    gap: 12px;
}

.tplf-field-full {
    grid-column: span 2;
}

.tplf-label {
    display: block;
    margin-bottom: 6px;
    font-size: 12px;
    font-weight: 700;
    color: #334155;
}

.tplf-input,
.tplf-select,
.tplf-textarea {
    width: 100%;
    border: 1px solid #dbe4f0;
    border-radius: 8px;
    background: #f8fbff;
    color: #0f172a;
    font-size: 13px;
    font-family: inherit;
    transition: .15s;
}

.tplf-input,
.tplf-select {
    height: 38px;
    padding: 0 10px;
}

.tplf-textarea {
    min-height: 180px;
    padding: 10px;
    resize: vertical;
}

.tplf-input:focus,
.tplf-select:focus,
.tplf-textarea:focus {
    outline: none;
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(37,99,235,.14);
    background: #fff;
}

.tplf-hint {
    margin-top: 6px;
    font-size: 11.5px;
    color: #64748b;
    line-height: 1.45;
}

.tplf-check {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    font-size: 12.5px;
    font-weight: 600;
    color: #334155;
}

.tplf-footer {
    border-top: 1px solid #dbe4f0;
    padding: 12px 16px;
    background: #f8fbff;
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
}

.tplf-btn {
    border: 1px solid transparent;
    border-radius: 8px;
    font-size: 12px;
    font-weight: 700;
    line-height: 1.2;
    padding: 9px 12px;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 6px;
}

.tplf-btn.primary {
    color: #fff;
    background: linear-gradient(135deg, #1d4ed8, #2563eb);
}

.tplf-btn.light {
    color: #334155;
    background: #fff;
    border-color: #dbe4f0;
}

@media (max-width: 768px) {
    .tplf-grid {
        grid-template-columns: 1fr;
    }

    .tplf-field-full {
        grid-column: span 1;
    }
}
</style>

<div class="tplf-page">
    <div class="tplf-head">
        <div>
            <div class="tplf-title">Edit Template Blast</div>
            <div class="tplf-sub">Perbarui isi template agar sesuai kebutuhan blasting terbaru.</div>
        </div>
        <a href="{{ $cancelUrl }}" class="tplf-back">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
    </div>

    <div class="tplf-card">
        <form method="POST" action="{{ route('admin.blast.templates.update', ['id' => $template->id]) }}">
            @csrf
            @method('PUT')
            @if($returnTo)
                <input type="hidden" name="return_to" value="{{ $returnTo }}">
            @endif

            <div class="tplf-body">
                @if($errors->any())
                    <div class="tplf-alert">{{ $errors->first() }}</div>
                @endif

                <div class="tplf-grid">
                    <div>
                        <label for="channel" class="tplf-label">Channel <span class="text-danger">*</span></label>
                        <select name="channel" id="channel" class="tplf-select" required>
                            <option value="whatsapp" {{ old('channel', strtolower((string) $template->channel)) === 'whatsapp' ? 'selected' : '' }}>WhatsApp</option>
                            <option value="email" {{ old('channel', strtolower((string) $template->channel)) === 'email' ? 'selected' : '' }}>Email</option>
                        </select>
                    </div>

                    <div>
                        <label for="name" class="tplf-label">Nama Template <span class="text-danger">*</span></label>
                        <input
                            type="text"
                            id="name"
                            name="name"
                            class="tplf-input"
                            maxlength="150"
                            value="{{ old('name', $template->name) }}"
                            required
                        >
                    </div>

                    <div class="tplf-field-full">
                        <label for="content" class="tplf-label">Isi Template <span class="text-danger">*</span></label>
                        <textarea
                            id="content"
                            name="content"
                            class="tplf-textarea"
                            required
                        >{{ old('content', $template->content) }}</textarea>
                        <div class="tplf-hint">
                            Placeholder: <code>{nama_siswa}</code>, <code>{kelas}</code>, <code>{nama_wali}</code>, <code>{email}</code>, <code>{wa}</code>, <code>{wa_2}</code>, <code>{nama_karyawan}</code>, <code>{instansi}</code>.
                        </div>
                    </div>
                </div>

                <div class="mt-3">
                    <label class="tplf-check" for="is_active">
                        <input
                            type="checkbox"
                            value="1"
                            id="is_active"
                            name="is_active"
                            {{ $isActiveChecked ? 'checked' : '' }}
                        >
                        Template aktif
                    </label>
                </div>
            </div>

            <div class="tplf-footer">
                <button type="submit" class="tplf-btn primary">
                    <i class="fas fa-save"></i> Update Template
                </button>
                <a href="{{ $cancelUrl }}" class="tplf-btn light">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection

