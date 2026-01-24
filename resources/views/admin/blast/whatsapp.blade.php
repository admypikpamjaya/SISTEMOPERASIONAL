@extends('layouts.app')

@section('section_name', 'WhatsApp Blast')

@section('content')
<form
    method="POST"
    action="{{ route('admin.blast.whatsapp.send') }}"
    enctype="multipart/form-data"
>
@csrf

<div class="blast-page">

    {{-- Header --}}
    <div class="blast-header">
        <a href="{{ route('admin.blast.index') }}" class="btn-back">
            ← Kembali
        </a>

        <div>
            <h1 class="blast-title">WhatsApp Blast</h1>
            <p class="blast-subtitle">
                Kirim pesan WhatsApp ke banyak penerima secara efisien
            </p>
        </div>
    </div>

    {{-- Card --}}
    <div class="blast-card">

        {{-- TARGET --}}
        <div class="field">
            <label class="field-label">Target Nomor</label>
            <textarea
                name="targets"
                class="field-input"
                rows="3"
                placeholder="6281234567890, 6289876543210"
                required
            ></textarea>
            <small class="field-hint">
                Pisahkan nomor menggunakan koma
            </small>
        </div>

        {{-- MESSAGE --}}
        <div class="field">
            <label class="field-label">Pesan</label>
            <textarea
                name="message"
                class="field-input"
                rows="5"
                placeholder="Tulis pesan WhatsApp di sini..."
                required
            ></textarea>
        </div>

        {{-- ATTACHMENT --}}
        <div class="field">
            <label class="field-label">Lampiran (Opsional)</label>
            <input
                type="file"
                name="attachments[]"
                class="field-input field-input-file"
                multiple
                accept=".pdf,.jpg,.jpeg,.png"
            >
            <small class="field-hint">
                Maksimal 5MB per file. PDF / Image.
            </small>
        </div>

        {{-- ACTION --}}
        <div class="blast-action">
            <button type="submit" class="btn-primary">
                Kirim Pesan
            </button>
        </div>

    </div>
</div>
</form>

<style>
/* RESET */
*{
    box-sizing:border-box;
    margin:0;
    padding:0;
    font-family:'Inter','Segoe UI',sans-serif;
}

/* PAGE */
.blast-page{
    max-width:760px;
    margin:40px auto;
    padding:0 20px;
    color:#1D1D41;
}

/* HEADER */
.blast-header{
    display:flex;
    align-items:flex-start;
    gap:20px;
    margin-bottom:32px;
}

.btn-back{
    text-decoration:none;
    color:#555;
    font-size:14px;
    padding:8px 14px;
    border-radius:8px;
    background:#F2F3F7;
    transition:.2s;
}

.btn-back:hover{
    background:#E6E8F0;
}

/* TITLE */
.blast-title{
    font-size:28px;
    font-weight:700;
    margin-bottom:4px;
}

.blast-subtitle{
    font-size:14px;
    color:#777;
}

/* CARD */
.blast-card{
    background:#FFFFFF;
    border-radius:18px;
    padding:32px;
    box-shadow:
        0 10px 25px rgba(0,0,0,.06),
        0 2px 6px rgba(0,0,0,.04);
}

/* FIELD */
.field{
    margin-bottom:26px;
}

.field-label{
    display:block;
    font-size:14px;
    font-weight:600;
    margin-bottom:10px;
}

.field-input{
    width:100%;
    padding:16px 18px;
    border-radius:12px;
    border:1px solid #E1E3EB;
    font-size:14px;
    resize:vertical;
    background:#FAFBFF;
    transition:.2s;
}

.field-input:focus{
    outline:none;
    border-color:#4F6EF7;
    background:#FFFFFF;
    box-shadow:0 0 0 4px rgba(79,110,247,.12);
}

.field-input-file{
    padding:12px;
}

.field-hint{
    display:block;
    margin-top:6px;
    font-size:12px;
    color:#888;
}

/* ACTION */
.blast-action{
    display:flex;
    justify-content:flex-end;
    margin-top:36px;
}

.btn-primary{
    background:#4F6EF7;
    color:#FFFFFF;
    border:none;
    padding:14px 32px;
    font-size:15px;
    font-weight:600;
    border-radius:12px;
    cursor:pointer;
    transition:.2s;
}

.btn-primary:hover{
    background:#3F5CE0;
    box-shadow:0 6px 16px rgba(79,110,247,.35);
}

.btn-primary:active{
    transform:scale(.97);
}

/* RESPONSIVE */
@media (max-width:640px){
    .blast-card{
        padding:24px;
    }

    .blast-action{
        justify-content:stretch;
    }

    .btn-primary{
        width:100%;
    }

    .blast-header{
        flex-direction:column;
        gap:14px;
    }
}
</style>
@endsection
