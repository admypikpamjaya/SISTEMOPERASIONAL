@extends('layouts.app')

@section('section_name',
    isset($recipient)
        ? 'Edit Penerima Blasting'
        : 'Tambah Penerima Blasting'
)

@section('content')

@if ($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="card">
    <div class="card-body">
        <form method="POST"
              action="{{ isset($recipient)
                    ? route('admin.blast.recipients.update', $recipient->id)
                    : route('admin.blast.recipients.store') }}">

            @csrf
            @if(isset($recipient))
                @method('PUT')
            @endif

            <div class="mb-3">
                <label class="form-label">Nama Siswa</label>
                <input type="text"
                       name="nama_siswa"
                       class="form-control"
                       value="{{ old('nama_siswa', $recipient->nama_siswa ?? '') }}"
                       required>
            </div>

            <div class="mb-3">
                <label class="form-label">Kelas / Jenjang</label>
                <input type="text"
                       name="kelas"
                       class="form-control"
                       value="{{ old('kelas', $recipient->kelas ?? '') }}"
                       required>
            </div>

            <div class="mb-3">
                <label class="form-label">Nama Wali</label>
                <input type="text"
                       name="nama_wali"
                       class="form-control"
                       value="{{ old('nama_wali', $recipient->nama_wali ?? '') }}"
                       required>
            </div>

            <div class="mb-3">
                <label class="form-label">Email Wali</label>
                <input type="email"
                       name="email_wali"
                       class="form-control"
                       value="{{ old('email_wali', $recipient->email_wali ?? '') }}">
            </div>

            <div class="mb-3">
                <label class="form-label">WhatsApp Wali</label>
                <input type="text"
                       name="wa_wali"
                       class="form-control"
                       value="{{ old('wa_wali', $recipient->wa_wali ?? '') }}">
            </div>

            <div class="mb-3">
                <label class="form-label">Catatan (Opsional)</label>
                <textarea name="catatan"
                          class="form-control"
                          rows="3">{{ old('catatan', $recipient->catatan ?? '') }}</textarea>
            </div>

            <div class="d-flex gap-2">
                <button class="btn btn-primary">
                    {{ isset($recipient) ? 'Update' : 'Simpan' }}
                </button>

                <a href="{{ route('admin.blast.recipients.index') }}"
                   class="btn btn-secondary">
                    Kembali
                </a>
            </div>

        </form>
    </div>
</div>

@endsection
