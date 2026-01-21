@extends('layouts.app')

@section('content')
<div class="blast-page">

    {{-- Header --}}
    <div class="blast-header">
        <a href="http://127.0.0.1:8000/admin/blast" class="btn-back">
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
        <form method="POST" action="{{ route('admin.blast.whatsapp.send') }}">
            @csrf

            {{-- Targets --}}
            <div class="field">
                <label class="field-label">Target Nomor</label>
                <textarea
                    name="targets"
                    class="field-input"
                    placeholder="6281234567890, 6289876543210"
                ></textarea>
                <small class="field-hint">
                    Pisahkan nomor menggunakan koma
                </small>
            </div>

            {{-- Message --}}
            <div class="field">
                <label class="field-label">Pesan</label>
                <textarea
                    name="message"
                    class="field-input"
                    placeholder="Tulis pesan WhatsApp di sini..."
                ></textarea>
            </div>

            {{-- Action --}}
            <div class="blast-action">
                <button type="submit" class="btn-primary">
                    Kirim Pesan
                </button>
            </div>
        </form>
    </div>

</div>

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
    color:#1F2937;
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
    font-size:14px;
    padding:8px 14px;
    border-radius:10px;
    background:#F3F4F6;
    color:#4B5563;
    transition:background .2s;
}

.btn-back:hover{
    background:#E5E7EB;
}

/* TITLE */
.blast-title{
    font-size:28px;
    font-weight:700;
    background:linear-gradient(90deg,#4F46E5,#9333EA);
    -webkit-background-clip:text;
    -webkit-text-fill-color:transparent;
    margin-bottom:4px;
}

.blast-subtitle{
    font-size:14px;
    color:#6B7280;
}

/* CARD */
.blast-card{
    background:linear-gradient(
        180deg,
        #FFFFFF 0%,
        #F9FAFB 100%
    );
    border-radius:20px;
    padding:34px;
    box-shadow:
        0 20px 40px rgba(79,70,229,.08),
        0 4px 10px rgba(0,0,0,.05);
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
    color:#111827;
}

.field-input{
    width:100%;
    min-height:120px;
    padding:16px 18px;
    border-radius:14px;
    border:1px solid #E5E7EB;
    font-size:14px;
    resize:vertical;
    background:#FFFFFF;
    transition:border .2s, box-shadow .2s;
}

.field-input:focus{
    outline:none;
    border-color:#6366F1;
    box-shadow:0 0 0 4px rgba(99,102,241,.18);
}

.field-hint{
    display:block;
    margin-top:6px;
    font-size:12px;
    color:#6B7280;
}

/* ACTION */
.blast-action{
    display:flex;
    justify-content:flex-end;
    margin-top:36px;
}

.btn-primary{
    background:linear-gradient(
        90deg,
        #4F46E5,
        #7C3AED
    );
    color:#FFFFFF;
    border:none;
    padding:14px 36px;
    font-size:15px;
    font-weight:600;
    border-radius:14px;
    cursor:pointer;
    transition:transform .1s, box-shadow .2s;
}

.btn-primary:hover{
    box-shadow:0 10px 28px rgba(124,58,237,.35);
}

.btn-primary:active{
    transform:scale(.97);
}

/* RESPONSIVE */
@media (max-width:640px){
    .blast-card{
        padding:26px;
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
