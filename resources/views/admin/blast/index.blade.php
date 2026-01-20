@extends('layouts.app')

@section('content')
<div class="whatsapp-blasting-container">

    <div class="page-title">Blasting WhatsApp</div>
    <div class="page-subtitle">Pesan massal ke banyak kontak WhatsApp</div>

    {{-- Search --}}
    <div class="white-card search-card">
        <div class="search-container">
            <span class="search-icon">🔍</span>
            <input id="searchInput" type="text" class="search-input" placeholder="Cari kontak, pesan, atau file..." />
        </div>
    </div>

    {{-- Main --}}
    <div class="main-content">

        {{-- Recipient --}}
        <div class="white-card recipient-card">
            <div class="section-title">Penerima</div>

            <div class="input-container">
                <input id="manualPhone" type="text" class="phone-input" placeholder="Masukkan nomor telepon lalu Enter" />
                <span class="input-prefix">+</span>
            </div>

            <div class="file-import" id="fileImport">
                <span>📁</span>
                <span>Impor File</span>
            </div>

            <div class="recipient-status" id="recipientInfo">Belum ada penerima</div>
            <div class="recipient-status">Maksimal File 10 MB</div>

            {{-- Dummy List --}}
            <ul id="recipientList" style="margin-top:10px;font-size:14px;color:#444;list-style:none;padding:0"></ul>
        </div>

        {{-- Message --}}
        <div class="white-card message-card">
            <div class="section-title">Kotak Pesan</div>

            <div class="message-editor">
                <textarea id="messageText" class="message-textarea" placeholder="Ketik pesan Anda di sini..."></textarea>
            </div>

            <div class="message-controls">
                <span class="char-count">0 karakter</span>
                <span class="sms-segments">0 segmen SMS</span>
            </div>

            <div class="attachment-buttons">
                <div class="attach-btn">📎 Lampirkan File</div>
                <div class="attach-btn">🖼️ Tambah Gambar</div>
            </div>

            <button id="sendButton" class="send-button">Kirim Pesan</button>
        </div>

    </div>

    {{-- Tips --}}
    <div class="tips-section">
        <div class="tips-title">Tips</div>
        <div class="tips-content">
            • Gunakan kode negara (+62)<br>
            • Personalisasi pesan<br>
            • Hindari spam berlebihan
        </div>
    </div>

</div>

<style>
/* === CSS TETAP SAMA (TIDAK DIUBAH) === */
*{box-sizing:border-box;margin:0;padding:0;font-family:'Segoe UI',sans-serif}
.whatsapp-blasting-container{max-width:1200px;margin:auto;padding:20px;color:#1D1D41}
.page-title{font-size:clamp(24px,4vw,32px);font-weight:700}
.page-subtitle{margin-bottom:24px;color:#666}
.white-card{background:#fff;border-radius:14px;padding:20px;box-shadow:0 4px 12px rgba(0,0,0,.08)}
.search-card{margin-bottom:24px}
.search-container{display:flex;align-items:center;gap:12px;background:#F5F5F5;padding:12px 16px;border-radius:10px}
.search-input{width:100%;border:none;background:transparent;font-size:16px}
.search-input:focus{outline:none}
.main-content{display:grid;grid-template-columns:1fr 2fr;gap:24px}
.section-title{font-size:18px;font-weight:600;margin-bottom:16px}
.input-container{position:relative;background:#F5F5F5;border-radius:10px;height:48px;margin-bottom:16px}
.phone-input{width:100%;height:100%;border:none;background:transparent;padding:0 44px 0 16px}
.phone-input:focus{outline:none}
.input-prefix{position:absolute;right:14px;top:50%;transform:translateY(-50%);font-weight:700}
.file-import{border:2px dashed #BDBDBD;height:48px;border-radius:10px;display:flex;align-items:center;justify-content:center;gap:10px;cursor:pointer;margin-bottom:16px}
.file-import:hover{border-color:#007BFF;background:#F0F7FF}
.recipient-status{margin-top:8px;text-align:center;font-size:14px;color:#888}
.message-editor{background:#F5F5F5;border-radius:10px;height:260px}
.message-textarea{width:100%;height:100%;border:none;resize:none;padding:16px;font-size:15px;background:transparent}
.message-textarea:focus{outline:none}
.message-controls{display:flex;justify-content:space-between;font-size:13px;color:#666;margin:12px 0}
.attachment-buttons{display:flex;flex-wrap:wrap;gap:12px;margin-bottom:16px}
.attach-btn{background:#F5F5F5;padding:10px 14px;border-radius:8px;cursor:pointer;font-size:14px}
.send-button{align-self:flex-end;padding:12px 24px;border:none;background:#007BFF;color:#fff;font-weight:600;border-radius:10px;cursor:pointer}
@media (max-width:900px){
    .main-content{grid-template-columns:1fr}
    .send-button{width:100%}
}
</style>

<script>
document.addEventListener('DOMContentLoaded', () => {

    let recipients = [];

    const list = document.getElementById('recipientList');
    const info = document.getElementById('recipientInfo');
    const searchInput = document.getElementById('searchInput');

    function render(filter = '') {
        list.innerHTML = '';
        const filtered = recipients.filter(r => r.includes(filter));
        filtered.forEach(r => {
            const li = document.createElement('li');
            li.textContent = r;
            list.appendChild(li);
        });
        info.textContent = `${filtered.length} penerima`;
    }

    // Manual input
    document.getElementById('manualPhone').addEventListener('keydown', e => {
        if (e.key === 'Enter' && e.target.value.trim()) {
            recipients.push(e.target.value.trim());
            e.target.value = '';
            render(searchInput.value);
        }
    });

    // Search
    searchInput.addEventListener('input', e => {
        render(e.target.value.trim());
    });

    // File import (dummy)
    document.getElementById('fileImport').addEventListener('click', () => {
        const input = document.createElement('input');
        input.type = 'file';
        input.accept = '.csv,.txt';

        input.onchange = e => {
            const file = e.target.files[0];
            if (!file) return;

            if (file.size > 10 * 1024 * 1024) {
                alert('Ukuran file melebihi 10 MB');
                return;
            }

            const reader = new FileReader();
            reader.onload = ev => {
                ev.target.result.split(/\r?\n/).forEach(line => {
                    const val = line.trim();
                    if (val) recipients.push(val);
                });
                render();
            };
            reader.readAsText(file);
        };
        input.click();
    });

    // Message counter
    const textarea = document.getElementById('messageText');
    const charCount = document.querySelector('.char-count');
    const smsSegments = document.querySelector('.sms-segments');

    textarea.addEventListener('input', () => {
        const len = textarea.value.length;
        charCount.textContent = `${len} karakter`;
        smsSegments.textContent = `${Math.ceil(len / 160)} segmen SMS`;
    });

    // Dummy send
    document.getElementById('sendButton').addEventListener('click', () => {
        if (!recipients.length || !textarea.value.trim()) {
            alert('Pesan atau penerima masih kosong');
            return;
        }
        alert(`Dummy Send Berhasil\n\nPenerima: ${recipients.length}\nPesan:\n${textarea.value}`);
    });

});
</script>
@endsection
