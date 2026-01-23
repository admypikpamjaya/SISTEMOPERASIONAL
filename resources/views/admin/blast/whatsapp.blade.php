@extends('layouts.app')

@section('section_name', 'WhatsApp Blast')

@section('content')
<form
    method="POST"
    action="{{ route('admin.blast.whatsapp.send') }}"
    enctype="multipart/form-data"
    id="whatsappBlastForm"
>
@csrf

<div class="whatsapp-blasting-container">

    {{-- Header --}}
    <div class="title-section">
        <div class="page-title">WhatsApp Blast</div>
        <div class="page-subtitle">Kirim pesan massal ke banyak kontak WhatsApp</div>
    </div>

    {{-- Main Content --}}
    <div class="main-content">

        {{-- RECIPIENT --}}
        <div class="white-card recipient-card">
            <div class="section-title">Penerima</div>

            <div class="phone-input-section">
                <div class="input-container">
                    <input type="text" class="phone-input" id="phoneInput" placeholder="628xxxx">
                </div>
                <button type="button" class="add-button" id="addPhoneBtn">+</button>
            </div>

            <div class="recipient-list" id="recipientList">
                <div class="recipient-status">Belum ada penerima</div>
            </div>

            {{-- HIDDEN TARGETS (BACKEND COMPATIBLE) --}}
            <textarea name="targets" id="targetsField" hidden required></textarea>
        </div>

        {{-- MESSAGE --}}
        <div class="white-card message-card">
            <div class="section-title">Pesan</div>

            <div class="message-editor">
                <textarea
                    class="message-textarea"
                    id="messageTextarea"
                    name="message"
                    placeholder="Tulis pesan WhatsApp di sini..."
                    required
                ></textarea>
            </div>

            <div class="message-controls">
                <span id="charCount">0 karakter</span>
                <span id="smsSegments">0 segmen SMS</span>
            </div>

            {{-- ATTACHMENT --}}
            <div class="form-group mb-3">
                <label>Attachment (optional)</label>
                <input
                    type="file"
                    name="attachments[]"
                    class="form-control"
                    multiple
                    accept=".pdf,.jpg,.jpeg,.png"
                >
                <small class="text-muted">Max 5MB per file. PDF / Image.</small>
            </div>

            <button type="submit" class="send-button">
                Kirim Pesan
            </button>
        </div>

    </div>
</div>
</form>

<style>
.whatsapp-blasting-container{padding:20px}
.main-content{display:flex;gap:30px}
.white-card{background:#fff;border-radius:14px;padding:24px;box-shadow:0 4px 12px rgba(0,0,0,.08)}
.recipient-card{flex:1}
.message-card{flex:2}
.section-title{font-weight:600;margin-bottom:16px}
.phone-input-section{display:flex;gap:10px}
.input-container{flex:1;background:#F4F6F9;border-radius:10px;padding:0 14px}
.phone-input{border:none;background:transparent;height:48px;width:100%}
.add-button{width:48px;height:48px;border:none;border-radius:10px;background:#007BFF;color:#fff;font-size:22px}
.recipient-item{display:flex;justify-content:space-between;padding:8px 12px;background:#F5F5F5;border-radius:8px;margin-bottom:8px}
.message-editor{height:260px}
.message-textarea{width:100%;height:100%;border:none;padding:16px;resize:none}
.send-button{margin-top:20px;padding:12px 24px;border:none;border-radius:10px;background:#007BFF;color:#fff;font-weight:600}
</style>

<script>
document.addEventListener('DOMContentLoaded', () => {

    const phoneInput = document.getElementById('phoneInput');
    const addBtn = document.getElementById('addPhoneBtn');
    const list = document.getElementById('recipientList');
    const targetsField = document.getElementById('targetsField');
    const textarea = document.getElementById('messageTextarea');
    const charCount = document.getElementById('charCount');
    const smsSegments = document.getElementById('smsSegments');

    let numbers = [];

    function syncTargets() {
        targetsField.value = numbers.join(',');
    }

    function render() {
        list.innerHTML = '';
        if (numbers.length === 0) {
            list.innerHTML = '<div class="recipient-status">Belum ada penerima</div>';
            syncTargets();
            return;
        }

        numbers.forEach(num => {
            const el = document.createElement('div');
            el.className = 'recipient-item';
            el.innerHTML = `
                <span>${num}</span>
                <button type="button">×</button>
            `;
            el.querySelector('button').onclick = () => {
                numbers = numbers.filter(n => n !== num);
                render();
            };
            list.appendChild(el);
        });

        syncTargets();
    }

    function addNumber() {
        let val = phoneInput.value.trim();
        if (!val) return;
        if (!val.startsWith('+')) val = '+' + val;
        if (numbers.includes(val)) return;
        numbers.push(val);
        phoneInput.value = '';
        render();
    }

    addBtn.onclick = addNumber;
    phoneInput.addEventListener('keypress', e => {
        if (e.key === 'Enter') {
            e.preventDefault();
            addNumber();
        }
    });

    textarea.addEventListener('input', () => {
        const len = textarea.value.length;
        charCount.textContent = `${len} karakter`;
        smsSegments.textContent = `${Math.ceil(len / 160)} segmen SMS`;
    });

});
</script>
@endsection
