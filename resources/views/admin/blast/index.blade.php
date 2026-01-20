@extends('layouts.app')

@section('content')
<div class="whatsapp-blasting-container">

    {{-- Page Title --}}
    <div class="page-title">Blasting WhatsApp</div>
    <div class="page-subtitle">Pesan massal ke banyak kontak WhatsApp</div>

    {{-- Search Bar Card --}}
    <div class="white-card search-card">
        <div class="search-container">
            <div class="search-icon">🔍</div>
            <input type="text" class="search-input" placeholder="Cari apapun di sini...">
        </div>
    </div>

    {{-- Main Content --}}
    <div class="main-content">
        {{-- Recipient Card --}}
        <div class="white-card recipient-card">
            <div class="section-title">Penerima</div>
            
            {{-- Phone Number Input --}}
            <div class="input-container">
                <div class="input-placeholder">Masukkan nomor telepon</div>
                <div class="input-prefix">+</div>
            </div>

            {{-- Excel Import --}}
            <div class="excel-import">
                <div class="excel-icon">📁</div>
                <div class="excel-text">Impor dari Excel</div>
            </div>

            {{-- Recipient Status --}}
            <div class="recipient-status">Belum ada penerima</div>
        </div>

        {{-- Message Card --}}
        <div class="white-card message-card">
            <div class="section-title">Kotak Pesan</div>
            
            <div class="message-editor">
                <div class="message-placeholder">Ketik pesan Anda di sini...</div>
            </div>

            {{-- Message Controls --}}
            <div class="message-controls">
                <div class="char-count">0 karakter</div>
                <div class="sms-segments">0 segmen SMS</div>
            </div>

            {{-- Attachment Buttons --}}
            <div class="attachment-buttons">
                <div class="attach-btn">
                    <div class="attach-icon">📎</div>
                    <div class="attach-text">Lampirkan File</div>
                </div>
                <div class="attach-btn">
                    <div class="attach-icon">🖼️</div>
                    <div class="attach-text">Tambah Gambar</div>
                </div>
            </div>

            {{-- Send Button --}}
            <button class="send-button">
                Kirim Pesan
            </button>
        </div>
    </div>

    {{-- Tips Section --}}
    <div class="tips-section">
        <div class="tips-title">Tips</div>
        <div class="tips-content">
            • Sertakan kode negara pada nomor telepon (contoh: +6281234567890).<br>
            • Personalisasi pesan menggunakan variabel untuk engagement lebih baik.<br>
            • Hindari mengirim terlalu banyak pesan sekaligus untuk mencegah pemblokiran.
        </div>
    </div>

    {{-- Logout --}}
    <div class="logout">
        <div class="logout-icon">🚪</div>
        <div class="logout-text">Keluar</div>
    </div>
</div>

<style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    .whatsapp-blasting-container {
        width: 100%;
        min-height: 100vh;
        color: #1D1D41;
        padding: 20px 67px;
        position: relative;
    }

    /* Page Title Styles */
    .page-title {
        font-size: 32px;
        font-weight: 700;
        margin-top: 30px;
        color: #1D1D41;
    }

    .page-subtitle {
        font-size: 18px;
        color: rgba(29, 29, 65, 0.7);
        margin-top: 5px;
        margin-bottom: 30px;
    }

    /* White Card Styles */
    .white-card {
        background-color: #FFFFFF;
        border-radius: 15px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        padding: 25px;
        margin-bottom: 20px;
    }

    /* Search Card */
    .search-card {
        width: 100%;
        max-width: 400px;
        padding: 15px 25px;
        margin-bottom: 30px;
    }

    /* Search Bar Styles */
    .search-container {
        width: 100%;
        height: 40px;
        display: flex;
        align-items: center;
    }

    .search-icon {
        margin-right: 10px;
        color: rgba(29, 29, 65, 0.5);
        font-size: 18px;
    }

    .search-input {
        background: transparent;
        border: none;
        color: #1D1D41;
        width: 100%;
        font-size: 16px;
    }

    .search-input::placeholder {
        color: rgba(29, 29, 65, 0.5);
    }

    .search-input:focus {
        outline: none;
    }

    /* Main Content Layout */
    .main-content {
        display: flex;
        gap: 30px;
        margin-top: 10px;
    }

    /* Recipient Card */
    .recipient-card {
        flex: 1;
        min-height: 400px;
        display: flex;
        flex-direction: column;
    }

    /* Message Card */
    .message-card {
        flex: 2;
        min-height: 500px;
        display: flex;
        flex-direction: column;
    }

    /* Section Title */
    .section-title {
        font-size: 20px;
        font-weight: 600;
        margin-bottom: 20px;
        color: #1D1D41;
    }

    /* Input Container */
    .input-container {
        width: 100%;
        height: 50px;
        background-color: #F5F5F5;
        border-radius: 10px;
        position: relative;
        margin-bottom: 20px;
        border: 1px solid #E0E0E0;
    }

    .input-placeholder {
        position: absolute;
        left: 20px;
        top: 50%;
        transform: translateY(-50%);
        color: rgba(29, 29, 65, 0.7);
        font-size: 14px;
    }

    .input-prefix {
        position: absolute;
        right: 20px;
        top: 50%;
        transform: translateY(-50%);
        color: #1D1D41;
        font-size: 18px;
        font-weight: bold;
    }

    /* Excel Import */
    .excel-import {
        width: 100%;
        height: 50px;
        background-color: #F5F5F5;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        margin-bottom: 20px;
        cursor: pointer;
        transition: background-color 0.3s;
        border: 1px solid #E0E0E0;
    }

    .excel-import:hover {
        background-color: #E8E8E8;
    }

    .excel-icon {
        font-size: 20px;
        color: #1D1D41;
    }

    .excel-text {
        color: #1D1D41;
        font-size: 16px;
        font-weight: 500;
    }

    /* Recipient Status */
    .recipient-status {
        text-align: center;
        color: rgba(29, 29, 65, 0.5);
        font-size: 14px;
        margin-top: auto;
        padding: 20px 0;
    }

    /* Message Editor */
    .message-editor {
        width: 100%;
        height: 300px;
        background-color: #F5F5F5;
        border-radius: 10px;
        position: relative;
        margin-bottom: 20px;
        border: 1px solid #E0E0E0;
    }

    .message-placeholder {
        position: absolute;
        left: 25px;
        top: 25px;
        color: rgba(29, 29, 65, 0.7);
        font-size: 14px;
    }

    /* Message Controls */
    .message-controls {
        display: flex;
        justify-content: space-between;
        color: rgba(29, 29, 65, 0.7);
        font-size: 14px;
        margin-bottom: 20px;
    }

    /* Attachment Buttons */
    .attachment-buttons {
        display: flex;
        gap: 15px;
        margin-bottom: 30px;
    }

    .attach-btn {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 10px 20px;
        background-color: #F5F5F5;
        border-radius: 8px;
        cursor: pointer;
        transition: background-color 0.3s;
        border: 1px solid #E0E0E0;
    }

    .attach-btn:hover {
        background-color: #E8E8E8;
    }

    .attach-icon {
        font-size: 18px;
        color: #1D1D41;
    }

    .attach-text {
        font-size: 14px;
        color: #1D1D41;
    }

    /* Send Button */
    .send-button {
        width: 150px;
        height: 45px;
        background-color: #007BFF;
        color: white;
        border: none;
        border-radius: 10px;
        font-size: 16px;
        font-weight: 700;
        cursor: pointer;
        transition: background-color 0.3s;
        margin-left: auto;
        display: block;
    }

    .send-button:hover {
        background-color: #0056b3;
    }

    /* Tips Section */
    .tips-section {
        margin-top: 50px;
        padding: 25px;
        background-color: #FFFFFF;
        border-radius: 15px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }

    .tips-title {
        font-size: 22px;
        font-weight: 600;
        margin-bottom: 15px;
        color: #1D1D41;
    }

    .tips-content {
        font-size: 16px;
        color: rgba(29, 29, 65, 0.7);
        line-height: 1.6;
    }

    /* Logout */
    .logout {
        position: fixed;
        left: 67px;
        bottom: 40px;
        display: flex;
        align-items: center;
        gap: 10px;
        cursor: pointer;
        background-color: #FFFFFF;
        padding: 10px 20px;
        border-radius: 10px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }

    .logout:hover {
        background-color: #F5F5F5;
    }

    .logout-icon {
        font-size: 20px;
        color: #1D1D41;
    }

    .logout-text {
        font-size: 18px;
        color: #1D1D41;
        font-weight: 500;
    }

    /* Responsive Design */
    @media (max-width: 1200px) {
        .whatsapp-blasting-container {
            padding: 20px;
        }

        .main-content {
            flex-direction: column;
            gap: 20px;
        }

        .recipient-card, .message-card {
            min-height: auto;
        }

        .logout {
            position: relative;
            left: 0;
            bottom: 0;
            margin-top: 40px;
            display: inline-flex;
        }
    }

    /* Textarea Styling for Message Editor */
    .message-textarea {
        width: 100%;
        height: 100%;
        padding: 25px;
        border: none;
        border-radius: 10px;
        background-color: #F5F5F5;
        color: #1D1D41;
        font-size: 16px;
        resize: none;
        font-family: inherit;
    }

    .message-textarea:focus {
        outline: none;
        background-color: #FFFFFF;
        border: 1px solid #007BFF;
    }
</style>

<script>
    // JavaScript untuk interaksi dasar
    document.addEventListener('DOMContentLoaded', function() {
        // Fokus ke input pencarian
        const searchInput = document.querySelector('.search-input');
        if (searchInput) {
            searchInput.addEventListener('focus', function() {
                this.parentElement.parentElement.style.boxShadow = '0 4px 12px rgba(0, 123, 255, 0.2)';
            });
            
            searchInput.addEventListener('blur', function() {
                this.parentElement.parentElement.style.boxShadow = '0 4px 12px rgba(0, 0, 0, 0.1)';
            });
        }

        // Interaksi untuk tombol kirim
        const sendButton = document.querySelector('.send-button');
        if (sendButton) {
            sendButton.addEventListener('click', function() {
                alert('Pesan akan dikirim ke semua penerima!');
                // Di sini bisa ditambahkan logika pengiriman pesan
            });
        }

        // Interaksi untuk tombol keluar
        const logoutBtn = document.querySelector('.logout');
        if (logoutBtn) {
            logoutBtn.addEventListener('click', function() {
                if (confirm('Apakah Anda yakin ingin keluar?')) {
                    window.location.href = '/logout';
                }
            });
        }

        // Interaksi untuk area pesan
        const messageEditor = document.querySelector('.message-editor');
        const messagePlaceholder = document.querySelector('.message-placeholder');
        const charCount = document.querySelector('.char-count');
        
        if (messageEditor && messagePlaceholder) {
            messageEditor.addEventListener('click', function() {
                // Cek apakah sudah ada textarea
                if (this.querySelector('.message-textarea')) return;
                
                // Buat textarea untuk editing
                const textarea = document.createElement('textarea');
                textarea.className = 'message-textarea';
                textarea.placeholder = 'Ketik pesan Anda di sini...';
                
                // Tambahkan event untuk menghitung karakter
                textarea.addEventListener('input', function() {
                    const charLength = this.value.length;
                    charCount.textContent = `${charLength} karakter`;
                    
                    // Hitung segmen SMS (asumsi 160 karakter per segmen)
                    const segments = Math.ceil(charLength / 160);
                    document.querySelector('.sms-segments').textContent = `${segments} segmen SMS`;
                });
                
                // Ganti placeholder dengan textarea
                messagePlaceholder.style.display = 'none';
                this.appendChild(textarea);
                textarea.focus();
                
                // Event ketika textarea kehilangan fokus
                textarea.addEventListener('blur', function() {
                    if (this.value.trim() === '') {
                        messagePlaceholder.style.display = 'block';
                        this.remove();
                    }
                });
            });
        }

        // Interaksi untuk import Excel
        const excelImport = document.querySelector('.excel-import');
        if (excelImport) {
            excelImport.addEventListener('click', function() {
                // Buat input file tersembunyi
                const fileInput = document.createElement('input');
                fileInput.type = 'file';
                fileInput.accept = '.xlsx,.xls,.csv';
                fileInput.style.display = 'none';
                
                fileInput.addEventListener('change', function(e) {
                    if (e.target.files.length > 0) {
                        const fileName = e.target.files[0].name;
                        alert(`File "${fileName}" berhasil dipilih untuk diimpor.`);
                        // Di sini bisa ditambahkan logika upload file
                    }
                });
                
                document.body.appendChild(fileInput);
                fileInput.click();
                document.body.removeChild(fileInput);
            });
        }

        // Interaksi untuk tombol lampiran
        const attachButtons = document.querySelectorAll('.attach-btn');
        attachButtons.forEach(button => {
            button.addEventListener('click', function() {
                const text = this.querySelector('.attach-text').textContent;
                alert(`Fitur "${text}" akan segera tersedia.`);
            });
        });
    });
</script>
@endsection