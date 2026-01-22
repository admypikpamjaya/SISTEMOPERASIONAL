@extends('layouts.app')

@section('content')
<div class="email-blasting-container">

    {{-- Page Title and Search Bar Row --}}
    <div class="title-search-row">
        <div class="title-section">
            <div class="page-title">Email Blast</div>
            <div class="page-subtitle">Kirim email massal ke banyak penerima</div>
        </div>
        
        {{-- Search Bar Card --}}
        <div class="white-card search-card">
            <div class="search-container">
                <div class="search-icon">🔍</div>
                <input type="text" class="search-input" placeholder="Cari apapun di sini...">
            </div>
        </div>
    </div>

    {{-- Main Content --}}
    <div class="main-content">
        {{-- Recipient Card --}}
        <div class="white-card recipient-card">
            <div class="section-title">Penerima</div>
            
            {{-- Email Input with Add Button --}}
            <div class="email-input-section">
                <div class="input-container">
                    <input type="email" class="email-input" placeholder="Masukkan alamat email" id="emailInput">
                </div>
                <button class="add-button" id="addEmailBtn">
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

        {{-- Email Content Card --}}
        <div class="white-card email-content-card">
            <div class="section-title">Tulis Email</div>
            
            {{-- Email Subject --}}
            <div class="email-subject-section">
                <div class="section-label">Subjek</div>
                <input type="text" class="subject-input" placeholder="Masukkan subjek email" id="subjectInput">
            </div>

            {{-- Email Message --}}
            <div class="email-message-section">
                <div class="section-label">Pesan</div>
                <div class="message-editor">
                    <textarea class="message-textarea" placeholder="Ketik isi email Anda di sini..." id="messageTextarea"></textarea>
                </div>
            </div>

            {{-- Attachment Buttons --}}
            <div class="attachment-buttons">
                <a href="#" class="attach-btn" id="attachFile">
                    <div class="attach-icon">📎</div>
                    <div class="attach-text">Lampirkan File</div>
                </a>
                <a href="#" class="attach-btn" id="attachImage">
                    <div class="attach-icon">🖼️</div>
                    <div class="attach-text">Tambah Gambar</div>
                </a>
                <button class="send-button" id="sendButton">
                    Kirim Email
                </button>
            </div>
        </div>
    </div>

    {{-- Tips Section --}}
    <div class="tips-section">
        <div class="tips-title">Tips</div>
        <div class="tips-content">
            • Gunakan subjek yang jelas dan menarik untuk meningkatkan tingkat dibuka.<br>
            • Personalisasi email menggunakan variabel untuk engagement lebih baik.<br>
            • Buat email dengan kalimat yang mudah dipahami serta ajakan bertindak yang jelas.
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

    .email-blasting-container {
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

    /* Email Content Card */
    .email-content-card {
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

    /* Email Input Section */
    .email-input-section {
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

    .email-input {
        background: transparent;
        border: none;
        color: #1D1D41;
        font-size: 16px;
        width: 100%;
        outline: none;
        height: 100%;
    }

    .email-input::placeholder {
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

    .recipient-email {
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

    /* Email Subject Section */
    .email-subject-section {
        margin-bottom: 25px;
    }

    .section-label {
        font-size: 14px;
        color: rgba(29, 29, 65, 0.7);
        margin-bottom: 8px;
        font-weight: 500;
    }

    .subject-input {
        width: 100%;
        height: 50px;
        padding: 0 15px;
        background-color: #F5F5F5;
        border: 1px solid #E0E0E0;
        border-radius: 10px;
        font-size: 16px;
        color: #1D1D41;
        outline: none;
        transition: all 0.3s;
    }

    .subject-input:focus {
        border-color: #007BFF;
        background-color: #FFFFFF;
    }

    .subject-input::placeholder {
        color: rgba(29, 29, 65, 0.5);
    }

    /* Email Message Section */
    .email-message-section {
        flex: 1;
        display: flex;
        flex-direction: column;
        margin-bottom: 20px;
    }

    /* Message Editor */
    .message-editor {
        width: 100%;
        height: 300px;
        background-color: #F5F5F5;
        border-radius: 10px;
        border: 1px solid #E0E0E0;
        overflow: hidden;
        flex: 1;
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
        transition: background-color 0.3s;
    }

    .message-textarea::placeholder {
        color: rgba(29, 29, 65, 0.7);
    }

    .message-textarea:focus {
        background-color: #FFFFFF;
        border-color: #007BFF;
    }

    /* Email Controls */
    .email-controls {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
    }

    .char-count {
        color: rgba(29, 29, 65, 0.7);
        font-size: 14px;
    }

    .email-variables {
        display: flex;
        gap: 10px;
        align-items: center;
    }

    .variable-select {
        height: 35px;
        padding: 0 10px;
        border: 1px solid #E0E0E0;
        border-radius: 6px;
        background-color: #FFFFFF;
        color: #1D1D41;
        font-size: 14px;
        outline: none;
        cursor: pointer;
    }

    .variable-select:focus {
        border-color: #007BFF;
    }

    .insert-variable {
        height: 35px;
        padding: 0 15px;
        background-color: #28a745;
        color: white;
        border: none;
        border-radius: 6px;
        font-size: 14px;
        cursor: pointer;
        transition: background-color 0.3s;
    }

    .insert-variable:hover {
        background-color: #218838;
    }

    /* Attachment Buttons */
    .attachment-buttons {
        display: flex;
        gap: 15px;
        align-items: center;
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
        text-decoration: none;
        color: inherit;
    }

    .attach-btn:hover {
        background-color: #E8E8E8;
        text-decoration: none;
        color: inherit;
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
        .email-blasting-container {
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

        .recipient-card, .email-content-card {
            min-height: auto;
            max-width: 100%;
        }

        .attachment-buttons {
            flex-wrap: wrap;
        }

        .send-button {
            margin-left: 0;
            width: 100%;
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

    /* File Attachment Indicator */
    .attachment-indicator {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 8px 12px;
        background-color: #E8F4FD;
        border-radius: 6px;
        margin-bottom: 10px;
        border: 1px solid #B3D9FF;
    }

    .attachment-name {
        font-size: 14px;
        color: #1D1D41;
        flex: 1;
    }

    .remove-attachment {
        background: none;
        border: none;
        color: #dc3545;
        cursor: pointer;
        font-size: 16px;
    }

    .attachment-preview {
        display: flex;
        flex-direction: column;
        gap: 10px;
        margin-bottom: 15px;
    }
</style>

<script>
    // JavaScript untuk Email Blasting
    document.addEventListener('DOMContentLoaded', function() {
        // Elements
        const searchInput = document.querySelector('.search-input');
        const emailInput = document.getElementById('emailInput');
        const addEmailBtn = document.getElementById('addEmailBtn');
        const recipientList = document.getElementById('recipientList');
        const subjectInput = document.getElementById('subjectInput');
        const messageTextarea = document.getElementById('messageTextarea');
        const charCount = document.getElementById('charCount');
        const variableSelect = document.getElementById('variableSelect');
        const insertVariableBtn = document.getElementById('insertVariable');
        const sendButton = document.getElementById('sendButton');
        const logoutBtn = document.querySelector('.logout');
        const excelImport = document.querySelector('.excel-import');
        const attachFileBtn = document.getElementById('attachFile');
        const attachImageBtn = document.getElementById('attachImage');
        
        // State variables
        let attachments = [];
        let images = [];

        // Fokus efek untuk search input
        if (searchInput) {
            searchInput.addEventListener('focus', function() {
                this.parentElement.parentElement.style.boxShadow = '0 4px 12px rgba(0, 123, 255, 0.2)';
            });
            
            searchInput.addEventListener('blur', function() {
                this.parentElement.parentElement.style.boxShadow = '0 4px 12px rgba(0, 0, 0, 0.1)';
            });
        }

        // Fokus efek untuk email input
        if (emailInput) {
            emailInput.addEventListener('focus', function() {
                this.parentElement.style.borderColor = '#007BFF';
                this.parentElement.style.backgroundColor = '#FFFFFF';
            });
            
            emailInput.addEventListener('blur', function() {
                this.parentElement.style.borderColor = '#E0E0E0';
                this.parentElement.style.backgroundColor = '#F5F5F5';
            });

            // Enter key untuk menambahkan email
            emailInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    addRecipient();
                }
            });
        }

        // Fokus efek untuk subject input
        if (subjectInput) {
            subjectInput.addEventListener('focus', function() {
                this.style.borderColor = '#007BFF';
                this.style.backgroundColor = '#FFFFFF';
            });
            
            subjectInput.addEventListener('blur', function() {
                this.style.borderColor = '#E0E0E0';
                this.style.backgroundColor = '#F5F5F5';
            });
        }

        // Fungsi untuk validasi email
        function validateEmail(email) {
            const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return re.test(email);
        }

        // Fungsi untuk menambahkan penerima
        function addRecipient() {
            let email = emailInput.value.trim();
            
            // Validasi email
            if (!email) {
                alert('Masukkan alamat email terlebih dahulu!');
                return;
            }

            // Validasi format email
            if (!validateEmail(email)) {
                alert('Format email tidak valid!');
                return;
            }

            // Cek apakah email sudah ada di list
            const existingEmails = Array.from(recipientList.querySelectorAll('.recipient-email'))
                .map(el => el.textContent);
            
            if (existingEmails.includes(email)) {
                alert('Email ini sudah ditambahkan!');
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
                <span class="recipient-email">${email}</span>
                <button class="remove-recipient" title="Hapus">×</button>
            `;

            // Tambahkan ke list
            recipientList.appendChild(recipientItem);

            // Reset input
            emailInput.value = '';

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

        // Interaksi untuk tombol tambah email
        if (addEmailBtn) {
            addEmailBtn.addEventListener('click', addRecipient);
        }

        // Interaksi untuk textarea pesan
        if (messageTextarea) {
            // Update karakter
            function updateCharCount() {
                const charLength = messageTextarea.value.length;
                charCount.textContent = `${charLength} karakter`;
            }

            messageTextarea.addEventListener('input', updateCharCount);
            
            // Inisialisasi statistik
            updateCharCount();
        }

        // Interaksi untuk variabel
        if (insertVariableBtn && variableSelect) {
            insertVariableBtn.addEventListener('click', function() {
                const selectedVariable = variableSelect.value;
                if (selectedVariable) {
                    // Sisipkan variabel ke textarea
                    const textarea = messageTextarea;
                    const startPos = textarea.selectionStart;
                    const endPos = textarea.selectionEnd;
                    
                    textarea.value = textarea.value.substring(0, startPos) + 
                                   selectedVariable + 
                                   textarea.value.substring(endPos);
                    
                    // Update posisi kursor
                    textarea.selectionStart = textarea.selectionEnd = startPos + selectedVariable.length;
                    
                    // Fokus kembali ke textarea
                    textarea.focus();
                    
                    // Update karakter count
                    updateCharCount();
                }
            });
        }

        // Fungsi untuk menampilkan attachment
        function displayAttachment(file, type) {
            const attachmentPreview = document.querySelector('.attachment-preview') || 
                (function() {
                    const previewDiv = document.createElement('div');
                    previewDiv.className = 'attachment-preview';
                    document.querySelector('.email-content-card').insertBefore(
                        previewDiv, 
                        document.querySelector('.email-controls')
                    );
                    return previewDiv;
                })();

            const attachmentItem = document.createElement('div');
            attachmentItem.className = 'attachment-indicator';
            
            let icon = '📎';
            if (type === 'image') {
                icon = '🖼️';
            }
            
            attachmentItem.innerHTML = `
                <div>${icon}</div>
                <div class="attachment-name">${file.name}</div>
                <button class="remove-attachment" data-name="${file.name}" data-type="${type}">×</button>
            `;
            
            attachmentPreview.appendChild(attachmentItem);
            
            // Tambahkan event untuk tombol hapus attachment
            const removeBtn = attachmentItem.querySelector('.remove-attachment');
            removeBtn.addEventListener('click', function() {
                const fileName = this.getAttribute('data-name');
                const fileType = this.getAttribute('data-type');
                
                // Hapus dari array yang sesuai
                if (fileType === 'image') {
                    images = images.filter(img => img.name !== fileName);
                } else {
                    attachments = attachments.filter(att => att.name !== fileName);
                }
                
                // Hapus elemen dari DOM
                attachmentItem.remove();
                
                // Jika tidak ada attachment lagi, hapus container preview
                if (attachmentPreview.children.length === 0) {
                    attachmentPreview.remove();
                }
            });
        }

        // Interaksi untuk lampiran file
        if (attachFileBtn) {
            attachFileBtn.addEventListener('click', function(e) {
                e.preventDefault();
                
                const fileInput = document.createElement('input');
                fileInput.type = 'file';
                fileInput.accept = '*/*';
                fileInput.multiple = true;
                fileInput.style.display = 'none';
                
                fileInput.addEventListener('change', function(e) {
                    if (e.target.files.length > 0) {
                        Array.from(e.target.files).forEach(file => {
                            attachments.push(file);
                            displayAttachment(file, 'file');
                        });
                        
                        alert(`${e.target.files.length} file berhasil dipilih untuk dilampirkan.`);
                    }
                });
                
                document.body.appendChild(fileInput);
                fileInput.click();
                document.body.removeChild(fileInput);
            });
        }

        // Interaksi untuk lampiran gambar
        if (attachImageBtn) {
            attachImageBtn.addEventListener('click', function(e) {
                e.preventDefault();
                
                const fileInput = document.createElement('input');
                fileInput.type = 'file';
                fileInput.accept = 'image/*';
                fileInput.multiple = true;
                fileInput.style.display = 'none';
                
                fileInput.addEventListener('change', function(e) {
                    if (e.target.files.length > 0) {
                        Array.from(e.target.files).forEach(file => {
                            images.push(file);
                            displayAttachment(file, 'image');
                        });
                        
                        alert(`${e.target.files.length} gambar berhasil dipilih untuk dilampirkan.`);
                    }
                });
                
                document.body.appendChild(fileInput);
                fileInput.click();
                document.body.removeChild(fileInput);
            });
        }

        // Interaksi untuk tombol kirim email
        if (sendButton) {
            sendButton.addEventListener('click', function() {
                // Ambil semua email penerima
                const recipientEmails = Array.from(recipientList.querySelectorAll('.recipient-email'))
                    .map(el => el.textContent);
                
                const subject = subjectInput.value.trim();
                const message = messageTextarea.value.trim();
                
                // Validasi
                if (recipientEmails.length === 0) {
                    alert('Tambahkan setidaknya satu penerima terlebih dahulu!');
                    return;
                }
                
                if (!subject) {
                    alert('Masukkan subjek email terlebih dahulu!');
                    return;
                }
                
                if (!message) {
                    alert('Masukkan pesan email terlebih dahulu!');
                    return;
                }
                
                // Tampilkan konfirmasi
                const confirmation = confirm(
                    `Email akan dikirim ke ${recipientEmails.length} penerima:\n\n` +
                    `${recipientEmails.join(', ')}\n\n` +
                    `Subjek: ${subject}\n` +
                    `Pesan: ${message.substring(0, 100)}${message.length > 100 ? '...' : ''}\n\n` +
                    `Attachment: ${attachments.length + images.length} file\n\n` +
                    `Apakah Anda yakin?`
                );
                
                if (confirmation) {
                    // Simulasi pengiriman
                    sendButton.disabled = true;
                    sendButton.textContent = 'Mengirim...';
                    sendButton.style.backgroundColor = '#6c757d';
                    
                    // Simulasi proses pengiriman
                    setTimeout(() => {
                        alert(`Email berhasil dikirim ke ${recipientEmails.length} penerima!`);
                        sendButton.disabled = false;
                        sendButton.textContent = 'Kirim Email';
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
                        const dummyEmails = [
                            'john.doe@example.com',
                            'jane.smith@example.com',
                            'mark.wilson@example.com',
                            'sarah.jones@example.com'
                        ];
                        
                        // Hapus status "Belum ada penerima"
                        const statusElement = recipientList.querySelector('.recipient-status');
                        if (statusElement) {
                            statusElement.remove();
                        }
                        
                        // Tambahkan email dummy
                        dummyEmails.forEach(email => {
                            const recipientItem = document.createElement('div');
                            recipientItem.className = 'recipient-item';
                            recipientItem.innerHTML = `
                                <span class="recipient-email">${email}</span>
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
                        
                        alert(`File "${fileName}" berhasil diimpor. Menambahkan ${dummyEmails.length} kontak.`);
                    }
                });
                
                document.body.appendChild(fileInput);
                fileInput.click();
                document.body.removeChild(fileInput);
            });
        }

        // Update char count function
        function updateCharCount() {
            if (messageTextarea) {
                const charLength = messageTextarea.value.length;
                charCount.textContent = `${charLength} karakter`;
            }
        }
    });
</script>
@endsection