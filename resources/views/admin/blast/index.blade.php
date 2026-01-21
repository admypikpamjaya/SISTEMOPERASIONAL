@extends('layouts.app')

@section('content')
<div class="blast-menu-page">

    <div class="blast-menu-card">
        <h2 class="blast-menu-title">Blast Message</h2>
        <p class="blast-menu-subtitle">
            Pilih jenis pesan yang ingin Anda kirim
        </p>

        <div class="blast-menu-actions">
            <a href="{{ route('admin.blast.whatsapp') }}" class="blast-btn whatsapp">
                <span class="icon">💬</span>
                <div class="text">
                    <strong>WhatsApp Blast</strong>
                    <small>Kirim pesan WhatsApp massal</small>
                </div>
            </a>

            <a href="{{ route('admin.blast.email') }}" class="blast-btn email">
                <span class="icon">📧</span>
                <div class="text">
                    <strong>Email Blast</strong>
                    <small>Kirim email ke banyak penerima</small>
                </div>
            </a>
        </div>
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
.blast-menu-page{
    min-height:70vh;
    display:flex;
    align-items:center;
    justify-content:center;
    padding:20px;
}

/* CARD */
.blast-menu-card{
    background:linear-gradient(
        180deg,
        #FFFFFF 0%,
        #F9FAFB 100%
    );
    border-radius:22px;
    padding:40px 36px;
    width:100%;
    max-width:520px;
    text-align:center;
    box-shadow:
        0 20px 40px rgba(0,0,0,.08),
        0 4px 10px rgba(0,0,0,.05);
}

/* TITLE */
.blast-menu-title{
    font-size:28px;
    font-weight:700;
    margin-bottom:6px;
    background:linear-gradient(90deg,#4F46E5,#9333EA);
    -webkit-background-clip:text;
    -webkit-text-fill-color:transparent;
}

.blast-menu-subtitle{
    font-size:14px;
    color:#6B7280;
    margin-bottom:34px;
}

/* ACTIONS */
.blast-menu-actions{
    display:flex;
    flex-direction:column;
    gap:18px;
}

/* BUTTON CARD */
.blast-btn{
    display:flex;
    align-items:center;
    gap:16px;
    padding:18px 22px;
    border-radius:16px;
    text-decoration:none;
    color:#111827;
    background:#FFFFFF;
    border:1px solid #E5E7EB;
    transition:transform .15s, box-shadow .2s, border .2s;
}

.blast-btn:hover{
    transform:translateY(-2px);
    box-shadow:0 12px 28px rgba(0,0,0,.12);
}

/* ICON */
.blast-btn .icon{
    font-size:28px;
}

/* TEXT */
.blast-btn .text{
    text-align:left;
}
.blast-btn strong{
    display:block;
    font-size:16px;
}
.blast-btn small{
    font-size:13px;
    color:#6B7280;
}

/* VARIANT */
.blast-btn.whatsapp:hover{
    border-color:#25D366;
}

.blast-btn.email:hover{
    border-color:#6366F1;
}

/* RESPONSIVE */
@media (max-width:480px){
    .blast-menu-card{
        padding:32px 24px;
    }
}
</style>
@endsection
