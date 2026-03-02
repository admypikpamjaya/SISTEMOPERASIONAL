@extends('layouts.app')

@section('title', 'Buat Template Blast')

@section('content')
@php
    $cancelUrl = $returnTo ?: route('admin.blast.templates.index', ['channel' => $channel]);
    $isActiveOld = old('is_active');
    $isActiveChecked = $isActiveOld !== null ? (bool) $isActiveOld : true;
@endphp

<div class="card shadow-sm border-0">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h3 class="card-title mb-0" style="float:none;">Buat Template Blast</h3>
        <a href="{{ $cancelUrl }}" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-arrow-left mr-1"></i> Kembali
        </a>
    </div>

    <form method="POST" action="{{ route('admin.blast.templates.store') }}">
        @csrf
        @if($returnTo)
            <input type="hidden" name="return_to" value="{{ $returnTo }}">
        @endif

        <div class="card-body">
            @if($errors->any())
                <div class="alert alert-danger">
                    {{ $errors->first() }}
                </div>
            @endif

            <div class="form-group">
                <label for="channel">Channel <span class="text-danger">*</span></label>
                <select name="channel" id="channel" class="form-control" required>
                    <option value="whatsapp" {{ old('channel', $channel) === 'whatsapp' ? 'selected' : '' }}>WhatsApp</option>
                    <option value="email" {{ old('channel', $channel) === 'email' ? 'selected' : '' }}>Email</option>
                </select>
            </div>

            <div class="form-group">
                <label for="name">Nama Template <span class="text-danger">*</span></label>
                <input
                    type="text"
                    id="name"
                    name="name"
                    class="form-control"
                    maxlength="150"
                    value="{{ old('name') }}"
                    placeholder="Contoh: Reminder Tagihan Bulanan"
                    required
                >
            </div>

            <div class="form-group">
                <label for="content">Isi Template <span class="text-danger">*</span></label>
                <textarea
                    id="content"
                    name="content"
                    rows="8"
                    class="form-control"
                    placeholder="Isi template pesan..."
                    required
                >{{ old('content') }}</textarea>
                <small class="form-text text-muted">
                    Placeholder yang didukung: <code>{nama_siswa}</code>, <code>{kelas}</code>, <code>{nama_wali}</code>, <code>{email}</code>, <code>{wa}</code>, <code>{wa_2}</code>.
                </small>
            </div>

            <div class="form-check">
                <input
                    class="form-check-input"
                    type="checkbox"
                    value="1"
                    id="is_active"
                    name="is_active"
                    {{ $isActiveChecked ? 'checked' : '' }}
                >
                <label class="form-check-label" for="is_active">
                    Template aktif
                </label>
            </div>
        </div>

        <div class="card-footer bg-white d-flex" style="gap:8px;">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save mr-1"></i> Simpan Template
            </button>
            <a href="{{ $cancelUrl }}" class="btn btn-outline-secondary">Batal</a>
        </div>
    </form>
</div>
@endsection
