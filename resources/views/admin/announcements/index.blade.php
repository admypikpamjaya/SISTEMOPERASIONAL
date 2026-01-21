@extends('layouts.app')

@section('content')
<div class="announcement-page">

    {{-- Header --}}
    <div class="announcement-header">
       

        <div>
            <h1 class="announcement-title">Announcement</h1>
            <p class="announcement-subtitle">
                Buat pengumuman untuk ditampilkan ke seluruh pengguna
            </p>
        </div>
    </div>

    {{-- Success Alert --}}
    @if (session('success'))
        <div class="success-alert">
            ✅ {{ session('success') }}
        </div>
    @endif

    {{-- Card --}}
    <div class="announcement-card">
        <form method="POST" action="/admin/announcements">
            @csrf

            {{-- Title --}}
            <div class="field">
                <label class="field-label">Judul</label>
                <input
                    type="text"
                    name="title"
                    class="field-input field-input-single"
                    placeholder="Judul pengumuman"
                    required
                >
            </div>

            {{-- Message --}}
            <div class="field">
                <label class="field-label">Pesan</label>
                <textarea
                    name="message"
                    class="field-input"
                    placeholder="Tulis isi pengumuman di sini..."
                    required
                ></textarea>
            </div>

            {{-- Action --}}
            <div class="announcement-action">
                <button type="submit" class="btn-primary">
                    Publikasikan
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
.announcement-page{
    max-width:760px;
    margin:40px auto;
    padding:0 20px;
    color:#1F2937;
}

/* HEADER */
.announcement-header{
    display:flex;
    align-items:flex-start;
    gap:20px;
    margin-bottom:28px;
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
.announcement-title{
    font-size:28px;
    font-weight:700;
    background:linear-gradient(90deg,#4F46E5,#9333EA);
    -webkit-background-clip:text;
    -webkit-text-fill-color:transparent;
    margin-bottom:4px;
}

.announcement-subtitle{
    font-size:14px;
    color:#6B7280;
}

/* SUCCESS */
.success-alert{
    margin-bottom:20px;
    padding:14px 18px;
    border-radius:12px;
    background:linear-gradient(
        90deg,
        #ECFEFF,
        #F0F9FF
    );
    color:#0369A1;
    font-size:14px;
}

/* CARD */
.announcement-card{
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

/* ACTION */
.announcement-action{
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
    .announcement-card{
        padding:26px;
    }

    .announcement-action{
        justify-content:stretch;
    }

    .btn-primary{
        width:100%;
    }

    .announcement-header{
        flex-direction:column;
        gap:14px;
    }
}
</style>
@endsection
