@extends('layouts.app')

@section('content')
<div class="whatsapp-blasting-container">

    {{-- Page Title and Search Bar Row --}}
    <div class="title-search-row">
        <div class="title-section">
            <div class="page-title">Blasting WhatsApp</div>
            <div class="page-subtitle">Kirim pesan massal ke banyak kontak WhatsApp</div>
        </div>
        
        {{-- Search Bar Card --}}
        <div class="white-card search-card">
            <div class="search-container">
                <div class="search-icon">🔍</div>
                <input type="text" class="search-input" placeholder="Cari yang Anda inginkan di sini...">
            </div>
        </div>
    </div>

    {{-- Main Content --}}
    <div class="main-content">
        {{-- Recipient Card --}}
        <div class="white-card recipient-card">
            <div class="section-title">Penerima</div>
            
            {{-- Phone Number Input with Add Button --}}
            <div class="phone-input-section">
                <div class="input-container">
                    <input type="text" class="phone-input" placeholder="Masukkan nomor telepon" id="phoneInput">
                </div>
                <button class="add-button" id="addPhoneBtn">
                    <span class="add-icon">+</span>
                </button>
            </div>

            {{-- Excel Import --}}
            <div class="excel-import">
                <div class="excel-icon">📁</div>
                <div class="excel-text">Impor dari Excel</div>
            </div>

            {{-- Recipient List --}}
            <div class="recipient-list" id="recipientList">
                <div class="recipient-status">Belum ada penerima</div>
            </div>
        </div>

        {{-- Message Card --}}
        <div class="white-card message-card">
            <div class="section-title">Kotak Pesan</div>
            
            <div class="message-editor">
                <textarea class="message-textarea" placeholder="Ketik pesan Anda di sini..." id="messageTextarea"></textarea>
            </div>

            {{-- Message Controls --}}
            <div class="message-controls">
                <div class="char-count" id="charCount">0 karakter</div>
                <div class="sms-segments" id="smsSegments">0 segmen SMS</div>
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
            <button class="send-button" id="sendButton">
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

    /* Title and Search Row */
    .title-search-row {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-top: 30px;
        margin-bottom: 30px;
        gap: 30px;
    }

    .title-section {
        flex: 1;
    }

    /* Page Title Styles */
    .page-title {
        font-size: 32px;
        font-weight: 700;
        color: #1D1D41;
    }

    .page-subtitle {
        font-size: 18px;
        color: rgba(29, 29, 65, 0.7);
        margin-top: 5px;
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
        max-width: 604px;
        padding: 15px 25px;
        align-self: flex-start;
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
        outline: none;
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
        width: 100%;
        max-width: 604px;
    }

    /* Section Title */
    .section-title {
        font-size: 20px;
        font-weight: 600;
        margin-bottom: 20px;
        color: #1D1D41;
    }

    /* Phone Input Section */
    .phone-input-section {
        display: flex;
        gap: 10px;
        align-items: center;
        margin-bottom: 20px;
    }

    /* Input Container */
    .input-container {
        width: 100%;
        height: 50px;
        background-color: #F4F6F9;
        border-radius: 10px;
        position: relative;
        border: 1px solid #E0E0E0;
        flex: 1;
        display: flex;
        align-items: center;
        padding: 0 15px;
    }

    .input-prefix {
        color: #1D1D41;
        font-size: 18px;
        font-weight: bold;
        margin-right: 8px;
    }

    .phone-input {
        background: transparent;
        border: none;
        color: #1D1D41;
        font-size: 16px;
        width: 100%;
        outline: none;
        height: 100%;
    }

    .phone-input::placeholder {
        color: rgba(29, 29, 65, 0.7);
        font-size: 14px;
    }

    /* Add Button */
    .add-button {
        width: 50px;
        height: 50px;
        background-color: #007BFF;
        border: none;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: background-color 0.3s;
        flex-shrink: 0;
    }

    .add-button:hover {
        background-color: #0056b3;
    }

    .add-icon {
        color: white;
        font-size: 24px;
        font-weight: bold;
        line-height: 1;
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

    /* Recipient List */
    .recipient-list {
        margin-top: auto;
        padding: 20px 0;
        max-height: 200px;
        overflow-y: auto;
    }

    .recipient-status {
        text-align: center;
        color: rgba(29, 29, 65, 0.5);
        font-size: 14px;
    }

    .recipient-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 8px 12px;
        background-color: #F5F5F5;
        border-radius: 8px;
        margin-bottom: 8px;
        border: 1px solid #E0E0E0;
    }

    .recipient-number {
        color: #1D1D41;
        font-size: 14px;
    }

    .remove-recipient {
        background: none;
        border: none;
        color: #dc3545;
        cursor: pointer;
        font-size: 18px;
        padding: 0;
        width: 24px;
        height: 24px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 4px;
    }

    .remove-recipient:hover {
        background-color: rgba(220, 53, 69, 0.1);
    }

    /* Message Editor */
    .message-editor {
        width: 100%;
        height: 300px;
        background-color: #F5F5F5;
        border-radius: 10px;
        margin-bottom: 20px;
        border: 1px solid #E0E0E0;
        overflow: hidden;
    }

    .message-textarea {
        width: 100%;
        height: 100%;
        padding: 20px;
        border: none;
        border-radius: 10px;
        background-color: #F5F5F5;
        color: #1D1D41;
        font-size: 16px;
        resize: none;
        font-family: inherit;
        outline: none;
    }

    .message-textarea::placeholder {
        color: rgba(29, 29, 65, 0.7);
    }

    .message-textarea:focus {
        outline: none;
        background-color: #FFFFFF;
        border: 1px solid #007BFF;
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

        .title-search-row {
            flex-direction: column;
            gap: 20px;
        }

        .search-card {
            max-width: 100%;
            width: 100%;
        }

        .main-content {
            flex-direction: column;
            gap: 20px;
        }

        .recipient-card, .message-card {
            min-height: auto;
            max-width: 100%;
        }

        .logout {
            position: relative;
            left: 0;
            bottom: 0;
            margin-top: 40px;
            display: inline-flex;
        }
    }

    /* Input Focus Effects */
    .input-container:focus-within {
        border-color: #007BFF;
        background-color: #FFFFFF;
    }

    .search-card:focus-within {
        box-shadow: 0 4px 12px rgba(0, 123, 255, 0.2);
    }
</style>

<script>
    // JavaScript untuk interaksi dasar
    document.addEventListener('DOMContentLoaded', function() {
        // Elements
        const searchInput = document.querySelector('.search-input');
        const phoneInput = document.getElementById('phoneInput');
        const addPhoneBtn = document.getElementById('addPhoneBtn');
        const recipientList = document.getElementById('recipientList');
        const messageTextarea = document.getElementById('messageTextarea');
        const charCount = document.getElementById('charCount');
        const smsSegments = document.getElementById('smsSegments');
        const sendButton = document.getElementById('sendButton');
        const logoutBtn = document.querySelector('.logout');
        const excelImport = document.querySelector('.excel-import');
        const attachButtons = document.querySelectorAll('.attach-btn');

        // Fokus efek untuk search input
        if (searchInput) {
            searchInput.addEventListener('focus', function() {
                this.parentElement.parentElement.style.boxShadow = '0 4px 12px rgba(0, 123, 255, 0.2)';
            });
            
            searchInput.addEventListener('blur', function() {
                this.parentElement.parentElement.style.boxShadow = '0 4px 12px rgba(0, 0, 0, 0.1)';
            });
        }

        // Fokus efek untuk phone input
        if (phoneInput) {
            phoneInput.addEventListener('focus', function() {
                this.parentElement.style.borderColor = '#007BFF';
                this.parentElement.style.backgroundColor = '#FFFFFF';
            });
            
            phoneInput.addEventListener('blur', function() {
                this.parentElement.style.borderColor = '#E0E0E0';
                this.parentElement.style.backgroundColor = '#F5F5F5';
            });

            // Enter key untuk menambahkan nomor
            phoneInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    addRecipient();
                }
            });
        }

        // Fungsi untuk menambahkan penerima
        function addRecipient() {
            let phoneNumber = phoneInput.value.trim();
            
            // Validasi nomor telepon
            if (!phoneNumber) {
                alert('Masukkan nomor telepon terlebih dahulu!');
                return;
            }

            // Tambahkan + jika belum ada
            if (!phoneNumber.startsWith('+')) {
                phoneNumber = '+' + phoneNumber;
            }

            // Cek apakah nomor sudah ada di list
            const existingNumbers = Array.from(recipientList.querySelectorAll('.recipient-number'))
                .map(el => el.textContent);
            
            if (existingNumbers.includes(phoneNumber)) {
                alert('Nomor ini sudah ditambahkan!');
                return;
            }

            // Hapus status "Belum ada penerima"
            const statusElement = recipientList.querySelector('.recipient-status');
            if (statusElement) {
                statusElement.remove();
            }

            // Buat elemen penerima baru
            const recipientItem = document.createElement('div');
            recipientItem.className = 'recipient-item';
            recipientItem.innerHTML = `
                <span class="recipient-number">${phoneNumber}</span>
                <button class="remove-recipient" title="Hapus">×</button>
            `;

            // Tambahkan ke list
            recipientList.appendChild(recipientItem);

            // Reset input
            phoneInput.value = '';

            // Tambahkan event untuk tombol hapus
            const removeBtn = recipientItem.querySelector('.remove-recipient');
            removeBtn.addEventListener('click', function() {
                recipientItem.remove();
                
                // Jika tidak ada penerima lagi, tampilkan status
                if (recipientList.children.length === 0) {
                    const newStatus = document.createElement('div');
                    newStatus.className = 'recipient-status';
                    newStatus.textContent = 'Belum ada penerima';
                    recipientList.appendChild(newStatus);
                }
            });
        }

        // Interaksi untuk tombol tambah nomor
        if (addPhoneBtn) {
            addPhoneBtn.addEventListener('click', addRecipient);
        }

        // Interaksi untuk textarea pesan
        if (messageTextarea) {
            // Update karakter dan segmen SMS
            function updateMessageStats() {
                const charLength = messageTextarea.value.length;
                charCount.textContent = `${charLength} karakter`;
                
                // Hitung segmen SMS (asumsi 160 karakter per segmen)
                const segments = Math.ceil(charLength / 160);
                smsSegments.textContent = `${segments} segmen SMS`;
            }

            messageTextarea.addEventListener('input', updateMessageStats);
            
            // Inisialisasi statistik
            updateMessageStats();
        }

        // Interaksi untuk tombol kirim
        if (sendButton) {
            sendButton.addEventListener('click', function() {
                // Ambil semua nomor penerima
                const recipientNumbers = Array.from(recipientList.querySelectorAll('.recipient-number'))
                    .map(el => el.textContent);
                
                const message = messageTextarea.value.trim();
                
                // Validasi
                if (recipientNumbers.length === 0) {
                    alert('Tambahkan setidaknya satu penerima terlebih dahulu!');
                    return;
                }
                
                if (!message) {
                    alert('Masukkan pesan terlebih dahulu!');
                    return;
                }
                
                // Tampilkan konfirmasi
                const confirmation = confirm(
                    `Pesan akan dikirim ke ${recipientNumbers.length} penerima:\n\n` +
                    `${recipientNumbers.join(', ')}\n\n` +
                    `Pesan: ${message.substring(0, 100)}${message.length > 100 ? '...' : ''}\n\n` +
                    `Apakah Anda yakin?`
                );
                
                if (confirmation) {
                    // Simulasi pengiriman
                    sendButton.disabled = true;
                    sendButton.textContent = 'Mengirim...';
                    sendButton.style.backgroundColor = '#6c757d';
                    
                    // Simulasi proses pengiriman
                    setTimeout(() => {
                        alert(`Pesan berhasil dikirim ke ${recipientNumbers.length} penerima!`);
                        sendButton.disabled = false;
                        sendButton.textContent = 'Kirim Pesan';
                        sendButton.style.backgroundColor = '#007BFF';
                    }, 2000);
                }
            });
        }

        // Interaksi untuk tombol keluar
        if (logoutBtn) {
            logoutBtn.addEventListener('click', function() {
                if (confirm('Apakah Anda yakin ingin keluar?')) {
                    window.location.href = '/logout';
                }
            });
        }

        // Interaksi untuk import Excel
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
                        
                        // Simulasi import dari Excel (dummy data)
                        const dummyNumbers = [
                            '+6281234567890',
                            '+6289876543210',
                            '+6281122334455'
                        ];
                        
                        // Hapus status "Belum ada penerima"
                        const statusElement = recipientList.querySelector('.recipient-status');
                        if (statusElement) {
                            statusElement.remove();
                        }
                        
                        // Tambahkan nomor dummy
                        dummyNumbers.forEach(number => {
                            const recipientItem = document.createElement('div');
                            recipientItem.className = 'recipient-item';
                            recipientItem.innerHTML = `
                                <span class="recipient-number">${number}</span>
                                <button class="remove-recipient" title="Hapus">×</button>
                            `;
                            
                            recipientList.appendChild(recipientItem);
                            
                            // Tambahkan event untuk tombol hapus
                            const removeBtn = recipientItem.querySelector('.remove-recipient');
                            removeBtn.addEventListener('click', function() {
                                recipientItem.remove();
                                
                                // Jika tidak ada penerima lagi, tampilkan status
                                if (recipientList.children.length === 0) {
                                    const newStatus = document.createElement('div');
                                    newStatus.className = 'recipient-status';
                                    newStatus.textContent = 'Belum ada penerima';
                                    recipientList.appendChild(newStatus);
                                }
                            });
                        });
                        
                        alert(`File "${fileName}" berhasil diimpor. Menambahkan ${dummyNumbers.length} kontak.`);
                    }
                });
                
                document.body.appendChild(fileInput);
                fileInput.click();
                document.body.removeChild(fileInput);
            });
        }

        // Interaksi untuk tombol lampiran
        attachButtons.forEach(button => {
            button.addEventListener('click', function() {
                const text = this.querySelector('.attach-text').textContent;
                
                // Buat input file untuk lampiran
                const fileInput = document.createElement('input');
                fileInput.type = 'file';
                
                if (text.includes('Gambar')) {
                    fileInput.accept = 'image/*';
                } else {
                    fileInput.accept = '*/*';
                }
                
                fileInput.style.display = 'none';
                
                fileInput.addEventListener('change', function(e) {
                    if (e.target.files.length > 0) {
                        const fileName = e.target.files[0].name;
                        alert(`File "${fileName}" berhasil dipilih untuk dilampirkan.`);
                    }
                });
                
                document.body.appendChild(fileInput);
                fileInput.click();
                document.body.removeChild(fileInput);
            });
        });
    });
</script>
@endsection