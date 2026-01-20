@extends('layouts.app')

@section('content')
<div class="blast-wrapper">

    <div class="blast-card">
        <h2 class="blast-title">WhatsApp Blast</h2>
        <p class="blast-subtitle">
            Kirim pesan WhatsApp ke banyak nomor sekaligus
        </p>

        <form method="POST" action="{{ route('admin.blast.whatsapp.send') }}">
            @csrf

            {{-- Targets --}}
            <div class="form-group">
                <label class="form-label">
                    Target Nomor
                    <span class="form-hint">(pisahkan dengan koma)</span>
                </label>
                <textarea
                    name="targets"
                    class="form-control"
                    placeholder="Contoh: 6281234567890,6289876543210"
                ></textarea>
            </div>

            {{-- Message --}}
            <div class="form-group">
                <label class="form-label">Pesan</label>
                <textarea
                    name="message"
                    class="form-control"
                    placeholder="Tulis pesan WhatsApp di sini..."
                ></textarea>
            </div>

            {{-- Action --}}
            <div class="form-action">
                <button type="submit" class="btn-submit">
                    🚀 Kirim WhatsApp Blast
                </button>
            </div>
        </form>
    </div>

</div>

<style>
/* WRAPPER */
.blast-wrapper{
    max-width:700px;
    margin:40px auto;
    padding:0 16px;
}

/* CARD */
.blast-card{
    background:#fff;
    border-radius:16px;
    padding:28px;
    box-shadow:0 10px 30px rgba(0,0,0,.08);
}

/* HEADER */
.blast-title{
    font-size:26px;
    font-weight:700;
    margin-bottom:6px;
    color:#1D1D41;
}
.blast-subtitle{
    font-size:14px;
    color:#666;
    margin-bottom:28px;
}

/* FORM */
.form-group{
    margin-bottom:22px;
}

.form-label{
    display:block;
    font-weight:600;
    margin-bottom:8px;
    color:#1D1D41;
}

.form-hint{
    font-weight:400;
    font-size:12px;
    color:#888;
    margin-left:6px;
}

/* INPUT */
.form-control{
    width:100%;
    min-height:120px;
    padding:14px 16px;
    font-size:14px;
    border-radius:10px;
    border:1px solid #DDD;
    resize:vertical;
    transition:border-color .2s, box-shadow .2s;
}

.form-control:focus{
    outline:none;
    border-color:#25D366;
    box-shadow:0 0 0 3px rgba(37,211,102,.15);
}

/* ACTION */
.form-action{
    text-align:right;
    margin-top:30px;
}

.btn-submit{
    background:#25D366;
    color:#fff;
    border:none;
    padding:12px 26px;
    font-size:15px;
    font-weight:600;
    border-radius:10px;
    cursor:pointer;
    transition:background .2s, transform .1s;
}

.btn-submit:hover{
    background:#1EBE5A;
}

.btn-submit:active{
    transform:scale(.97);
}

/* RESPONSIVE */
@media (max-width:600px){
    .blast-card{
        padding:22px;
    }
    .form-action{
        text-align:stretch;
    }
    .btn-submit{
        width:100%;
    }
}
</style>
@endsection
