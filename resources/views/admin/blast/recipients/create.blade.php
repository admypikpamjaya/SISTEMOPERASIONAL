@extends('layouts.app')

@section('content')
<div class="container">
    <h4>Tambah Penerima Blasting</h4>

    {{-- FORM MANUAL --}}
    <form action="{{ route('admin.blast.recipients.store') }}" method="POST">
        @csrf

        <div class="mb-3">
            <label>Nama Siswa</label>
            <input type="text" name="nama_siswa" class="form-control" required>
        </div>

        <div class="mb-3">
            <label>Kelas</label>
            <input type="text" name="kelas" class="form-control" required>
        </div>

        <div class="mb-3">
            <label>Nama Wali</label>
            <input type="text" name="nama_wali" class="form-control" required>
        </div>

        <div class="mb-3">
            <label>Email Wali</label>
            <input type="email" name="email_wali" class="form-control">
        </div>

        <div class="mb-3">
            <label>WhatsApp Wali</label>
            <input type="text" name="wa_wali" class="form-control">
        </div>

        <button class="btn btn-primary">Simpan</button>
        <a href="{{ route('admin.blast.recipients.index') }}" class="btn btn-secondary">
            Kembali
        </a>
    </form>

    <hr>

    {{-- IMPORT EXCEL --}}
    <h5>Import Excel</h5>
    <form action="{{ route('admin.blast.recipients.import') }}"
          method="POST"
          enctype="multipart/form-data">
        @csrf
        <input type="file" name="file" class="form-control mb-2" required>
        <button class="btn btn-success">Import</button>
    </form>
</div>
@endsection
