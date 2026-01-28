@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <h4 class="mb-3">Edit Penerima Blasting</h4>

    <form method="POST"
          action="{{ route('admin.blast.recipients.update', $recipient->id) }}">
        @csrf
        @method('PUT')

        <div class="mb-2">
            <label>Nama Siswa</label>
            <input type="text" name="nama_siswa"
                   value="{{ old('nama_siswa', $recipient->nama_siswa) }}"
                   class="form-control" required>
        </div>

        <div class="mb-2">
            <label>Kelas</label>
            <input type="text" name="kelas"
                   value="{{ old('kelas', $recipient->kelas) }}"
                   class="form-control" required>
        </div>

        <div class="mb-2">
            <label>Nama Wali</label>
            <input type="text" name="nama_wali"
                   value="{{ old('nama_wali', $recipient->nama_wali) }}"
                   class="form-control" required>
        </div>

        <div class="mb-2">
            <label>Email Wali</label>
            <input type="email" name="email_wali"
                   value="{{ old('email_wali', $recipient->email_wali) }}"
                   class="form-control">
        </div>

        <div class="mb-3">
            <label>WhatsApp Wali</label>
            <input type="text" name="wa_wali"
                   value="{{ old('wa_wali', $recipient->wa_wali) }}"
                   class="form-control">
        </div>

        <button class="btn btn-primary">Update</button>
        <a href="{{ route('admin.blast.recipients.index') }}"
           class="btn btn-secondary">Batal</a>
    </form>
</div>
@endsection
