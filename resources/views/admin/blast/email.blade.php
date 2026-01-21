@extends('layouts.app')

@section('content')
<div class="email-page">

    {{-- Header --}}
    <div class="email-header">
        <a href="http://127.0.0.1:8000/admin/blast" class="btn-back">
            ← Kembali
        </a>

        <div>
            <h1 class="email-title">Email Blast</h1>
            <p class="email-subtitle">
                Kirim email ke banyak penerima dengan cepat dan aman
            </p>
        </div>
    </div>

    {{-- Card --}}
    <div class="email-card">
        <form method="POST" action="{{ route('admin.blast.email.send') }}">
            @csrf

            {{-- Targets --}}
            <div class="field">
                <label class="field-label">Target Email</label>
                <textarea
                    name="targets"
                    class="field-input"
                    placeholder="email1@example.com, email2@example.com"
                ></textarea>
                <small class="field-hint">
                    Pisahkan email menggunakan koma
                </small>
            </div>

            {{-- Subject --}}
            <div class="field">
                <label class="field-label">Subject</label>
                <input
                    name="subject"
                    class="field-input field-input-single"
                    placeholder="Judul email"
                />
            </div>

            {{-- Message --}}
            <div class="field">
                <label class="field-label">Pesan</label>
                <textarea
                    name="message"
                    class="field-input"
                    placeholder="Tulis isi email di sini..."
                ></textarea>
            </div>

            {{-- Action --}}
            <div class="email-action">
                <button type="submit" class="btn-primary">
                    Kirim Email
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
.email-page{
    max-width:780px;
    margin:40px auto;
    padding:0 20px;
    color:#1F2937;
}

/* HEADER */
.email-header{
    display:flex;
    gap:20px;
    margin-bottom:32px;
}

.btn-back{
    text-decoration:none;
    color:#4B5563;
    font-size:14px;
    padding:8px 14px;
    border-radius:10px;
    background:#F3F4F6;
    transition:background .2s;
}

.btn-back:hover{
    background:#E5E7EB;
}

.email-title{
    font-size:28px;
    font-weight:700;
    background:linear-gradient(90deg,#4F46E5,#9333EA);
    -webkit-background-clip:text;
    -webkit-text-fill-color:transparent;
}

.email-subtitle{
    font-size:14px;
    color:#6B7280;
}

/* CARD */
.email-card{
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

.field-input-single{
    min-height:auto;
    height:48px;
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
.email-action{
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
    .email-card{
        padding:26px;
    }

    .email-action{
        justify-content:stretch;
    }

    .btn-primary{
        width:100%;
    }

    .email-header{
        flex-direction:column;
        gap:14px;
    }
}
</style>
@endsection
