@extends('layouts.app')

@section('section_name', 'Email Blast')

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
                Kirim email ke banyak penerima dengan lampiran opsional
            </p>
        </div>
    </div>

    {{-- Success Alert --}}
    @if(session('success'))
        <div class="success-alert">
            ✅ {{ session('success') }}
        </div>
    @endif

    {{-- Card --}}
    <div class="email-card">
        <form
            method="POST"
            action="{{ route('admin.blast.email.send') }}"
            enctype="multipart/form-data"
        >
            @csrf

            {{-- TARGET EMAIL (CHIP INPUT) --}}
            <div class="field">
                <label class="field-label">Target Email</label>

                <div class="chip-input-wrapper">
                    <div id="emailChips" class="chip-list"></div>
                    <input
                        type="email"
                        id="emailInput"
                        class="chip-input"
                        placeholder="Ketik email lalu tekan Enter"
                    >
                </div>

                <small class="field-hint">
                    Tekan Enter untuk menambahkan email
                </small>

                {{-- HIDDEN TEXTAREA (BACKEND COMPATIBLE) --}}
                <textarea
                    name="targets"
                    id="targetsField"
                    hidden
                    required
                ></textarea>
            </div>

            {{-- Subject --}}
            <div class="field">
                <label class="field-label">Subject</label>
                <input
                    name="subject"
                    class="field-input field-input-single"
                    placeholder="Judul email"
                    required
                >
            </div>

            {{-- Message --}}
            <div class="field">
                <label class="field-label">Message</label>
                <textarea
                    name="message"
                    class="field-input"
                    rows="6"
                    placeholder="Tulis isi email di sini..."
                    required
                ></textarea>
            </div>

            {{-- Attachments --}}
            <div class="field">
                <label class="field-label">Attachments (optional)</label>
                <input
                    type="file"
                    name="attachments[]"
                    class="field-input field-input-file"
                    multiple
                >
                <small class="field-hint">
                    Anda dapat memilih lebih dari satu file
                </small>
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
*{box-sizing:border-box;margin:0;padding:0;font-family:'Inter','Segoe UI',sans-serif}

/* PAGE */
.email-page{max-width:780px;margin:40px auto;padding:0 20px;color:#1F2937}

/* HEADER */
.email-header{display:flex;gap:20px;margin-bottom:28px}
.btn-back{padding:8px 14px;border-radius:10px;background:#F3F4F6;color:#4B5563;text-decoration:none}
.btn-back:hover{background:#E5E7EB}

.email-title{
    font-size:28px;
    font-weight:700;
    background:linear-gradient(90deg,#4F46E5,#9333EA);
    -webkit-background-clip:text;
    -webkit-text-fill-color:transparent;
}
.email-subtitle{font-size:14px;color:#6B7280}

/* SUCCESS */
.success-alert{
    margin-bottom:20px;
    padding:14px 18px;
    border-radius:12px;
    background:linear-gradient(90deg,#ECFEFF,#F0F9FF);
    color:#0369A1;
}

/* CARD */
.email-card{
    background:linear-gradient(180deg,#FFFFFF 0%,#F9FAFB 100%);
    border-radius:20px;
    padding:34px;
    box-shadow:0 20px 40px rgba(79,70,229,.08);
}

/* FIELD */
.field{margin-bottom:26px}
.field-label{font-weight:600;margin-bottom:10px;display:block}
.field-hint{font-size:12px;color:#6B7280;margin-top:6px}

/* CHIP INPUT */
.chip-input-wrapper{
    display:flex;
    flex-wrap:wrap;
    gap:8px;
    padding:12px;
    border-radius:14px;
    border:1px solid #E5E7EB;
    background:#FFFFFF;
}

.chip-list{display:flex;gap:8px;flex-wrap:wrap}

.chip{
    background:linear-gradient(90deg,#4F46E5,#7C3AED);
    color:#fff;
    padding:6px 10px;
    border-radius:999px;
    font-size:13px;
    display:flex;
    align-items:center;
    gap:6px;
}

.chip button{
    background:none;
    border:none;
    color:#fff;
    cursor:pointer;
    font-size:14px;
}

.chip-input{
    border:none;
    flex:1;
    min-width:180px;
    font-size:14px;
}
.chip-input:focus{outline:none}

/* INPUT */
.field-input{width:100%;padding:16px;border-radius:14px;border:1px solid #E5E7EB}
.field-input-single{height:48px}
.field-input-file{padding:12px}

/* ACTION */
.email-action{display:flex;justify-content:flex-end;margin-top:36px}
.btn-primary{
    background:linear-gradient(90deg,#4F46E5,#7C3AED);
    color:#fff;
    padding:14px 36px;
    border:none;
    border-radius:14px;
    font-weight:600;
    cursor:pointer;
}
.btn-primary:hover{box-shadow:0 10px 28px rgba(124,58,237,.35)}
</style>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const emailInput = document.getElementById('emailInput');
    const chipList = document.getElementById('emailChips');
    const targetsField = document.getElementById('targetsField');
    let emails = [];

    function syncTargets() {
        targetsField.value = emails.join(',');
    }

    function addChip(email) {
        if (emails.includes(email)) return;

        emails.push(email);
        syncTargets();

        const chip = document.createElement('div');
        chip.className = 'chip';
        chip.innerHTML = `${email} <button type="button">×</button>`;

        chip.querySelector('button').onclick = () => {
            emails = emails.filter(e => e !== email);
            chip.remove();
            syncTargets();
        };

        chipList.appendChild(chip);
    }

    emailInput.addEventListener('keydown', e => {
        if (e.key === 'Enter') {
            e.preventDefault();
            const value = emailInput.value.trim();
            if (value && value.includes('@')) {
                addChip(value);
                emailInput.value = '';
            }
        }
    });
});
</script>
@endsection
