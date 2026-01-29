@extends('layouts.app')

@section('content')
<div class="container-fluid recipient-form-wrapper">

    {{-- Page Title --}}
    <div class="page-title">
        <h3>Tambah Penerima Blasting</h3>
        <p>Input data penerima WhatsApp & Email secara manual atau impor Excel</p>
    </div>

    {{-- FORM MANUAL --}}
    <div class="card form-card">
        <div class="card-header">
            <strong>Form Input Manual</strong>
        </div>

        <div class="card-body">
            <form action="{{ route('admin.blast.recipients.store') }}" method="POST">
                @csrf

                <div class="form-grid">
                    <div class="form-group">
                        <label>Nama Siswa</label>
                        <input type="text" name="nama_siswa" required>
                    </div>

                    <div class="form-group">
                        <label>Kelas</label>
                        <input type="text" name="kelas" required>
                    </div>

                    <div class="form-group">
                        <label>Nama Wali</label>
                        <input type="text" name="nama_wali" required>
                    </div>

                    <div class="form-group">
                        <label>Email Wali</label>
                        <input type="email" name="email_wali">
                    </div>

                    <div class="form-group">
                        <label>WhatsApp Wali</label>
                        <input type="text" name="wa_wali">
                    </div>

                    <div class="form-group full">
                        <label>Catatan</label>
                        <textarea name="catatan" rows="3"></textarea>
                    </div>
                </div>

                <div class="form-actions">
                    <button class="btn btn-primary">Simpan</button>
                    <a href="{{ route('admin.blast.recipients.index') }}" class="btn btn-secondary">
                        Kembali
                    </a>
                </div>
            </form>
        </div>
    </div>

    {{-- IMPORT EXCEL --}}
    <div class="card import-card">
        <div class="card-header">
            <strong>Import Data via Excel</strong>
            <small class="text-muted d-block">
                Gunakan format Excel yang telah disediakan
            </small>
        </div>

        <div class="card-body">
            <form action="{{ route('admin.blast.recipients.import') }}"
                  method="POST"
                  enctype="multipart/form-data"
                  class="import-form">
                @csrf
                <input type="file" name="file" required>
                <button class="btn btn-success">Import Excel</button>
            </form>
        </div>
    </div>

</div>

<style>
/* ===== WRAPPER ===== */
.recipient-form-wrapper {
    padding: 20px;
    max-width: 1100px;
}

/* ===== TITLE ===== */
.page-title h3 {
    font-weight: 600;
    margin-bottom: 4px;
}
.page-title p {
    font-size: 14px;
    color: #6c757d;
    margin-bottom: 20px;
}

/* ===== CARD ===== */
.card {
    border-radius: 6px;
    margin-bottom: 20px;
}

.card-header {
    background: #f8f9fa;
    font-size: 14px;
}

/* ===== FORM GRID ===== */
.form-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 16px;
}

.form-group {
    display: flex;
    flex-direction: column;
}

.form-group.full {
    grid-column: span 2;
}

.form-group label {
    font-size: 13px;
    font-weight: 600;
    margin-bottom: 6px;
    color: #495057;
}

.form-group input,
.form-group textarea {
    padding: 8px 10px;
    border-radius: 4px;
    border: 1px solid #ced4da;
    font-size: 14px;
}

.form-group input:focus,
.form-group textarea:focus {
    outline: none;
    border-color: #0d6efd;
}

/* ===== ACTION ===== */
.form-actions {
    margin-top: 20px;
    display: flex;
    gap: 10px;
}

/* ===== IMPORT ===== */
.import-form {
    display: flex;
    align-items: center;
    gap: 10px;
}

/* ===== RESPONSIVE ===== */
@media (max-width: 768px) {
    .form-grid {
        grid-template-columns: 1fr;
    }
    .form-group.full {
        grid-column: span 1;
    }
}
</style>
@endsection
