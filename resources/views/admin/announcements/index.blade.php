@extends('layouts.app')

@section('content')
<div class="announcement-container">
    {{-- Header Section --}}
    <div class="header-section">
        <div class="header-content">
            <h1 class="main-title">Pengumuman</h1>
            <p class="subtitle">Sistem Operasional Yayasan</p>
        </div>
        
        {{-- Search Bar --}}
        <div class="search-section">
            <div class="search-wrapper">
                <div class="search-icon">🔍</div>
                <input type="text" class="search-input" placeholder="Cari pengumuman...">
            </div>
        </div>
    </div>

    {{-- Main Content --}}
    <div class="main-content">
        {{-- Left Column: Buat Pengumuman --}}
        <div class="left-column">
            <div class="form-card">
                <h2 class="form-title">Buat Pengumuman</h2>
                
                <form id="announcementForm">
                    {{-- Judul Pengumuman --}}
                    <div class="form-group">
                        <label class="form-label">Judul pengumuman</label>
                        <input 
                            type="text" 
                            class="form-input" 
                            placeholder="Masukkan judul pengumuman..."
                            id="titleInput"
                        >
                    </div>

                    {{-- Isi Pengumuman --}}
                    <div class="form-group">
                        <label class="form-label">Isi pengumuman</label>
                        <textarea 
                            class="form-textarea" 
                            placeholder="Tulis isi pengumuman di sini..."
                            rows="4"
                            id="contentInput"
                        ></textarea>
                    </div>

                    {{-- Priority and Department --}}
                    <div class="form-row">
                        {{-- Prioritas --}}
                        <div class="form-group">
                            <label class="form-label">Prioritas</label>
                            <div class="select-wrapper">
                                <select class="form-select" id="prioritySelect">
                                    <option value="low">Rendah</option>
                                    <option value="medium" selected>Sedang</option>
                                    <option value="high">Tinggi</option>
                                </select>
                            </div>
                        </div>

                        {{-- Departemen --}}
                        <div class="form-group">
                            <label class="form-label">Departemen</label>
                            <div class="select-wrapper">
                                <select class="form-select" id="departmentSelect">
                                    <option value="IT" selected>IT Support</option>
                                    <option value="Finance">Finance</option>
                                    <option value="Secretariat">Secretariat</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    {{-- Submit Button --}}
                    <div class="form-submit">
                        <button type="submit" class="submit-button" id="submitButton">
                            Publikasikan
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Right Column: Daftar Pengumuman --}}
        <div class="right-column">
            {{-- List Header --}}
            <div class="list-header">
                <div class="list-title-container">
                    <h2 class="list-title">Daftar Pengumuman</h2>
                </div>
                <div class="counter-badge">
                    <span id="announcementCount">0</span> pengumuman
                </div>
            </div>

            {{-- Announcement List --}}
            <div class="announcement-list" id="announcementList">
                {{-- Empty State --}}
                <div class="empty-state">
                    <div class="empty-icon">📢</div>
                    <p class="empty-title">Belum ada pengumuman</p>
                    <p class="empty-subtitle">Pengumuman yang dibuat akan muncul di sini</p>
                </div>
            </div>
        </div>
    </div>

</div>

<style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
        background-color: #f8f9fa;
        color: #333;
    }

    .announcement-container {
        min-height: 100vh;
        padding: 40px 60px;
        position: relative;
        max-width: 1400px;
        margin: 0 auto;
    }

    /* Header Section */
    .header-section {
        display: grid;
        grid-template-columns: 1fr 1fr; /* Membuat grid 2 kolom yang sama lebar */
        gap: 30px;
        margin-bottom: 40px;
        align-items: start;
    }

    .header-content {
        /* Tidak perlu flex:1 karena sudah pakai grid */
    }

    .main-title {
        font-size: 32px;
        font-weight: 700;
        color: #1a1a1a;
        margin-bottom: 8px;
        letter-spacing: -0.5px;
    }

    .subtitle {
        font-size: 16px;
        color: #666;
        font-weight: 400;
    }

    /* Search Section - Diperbaiki agar sejajar dengan card */
    .search-section {
        width: 100%; /* Mengisi lebar kolom grid */
    }

    .search-wrapper {
        display: flex;
        align-items: center;
        background: white;
        border: 1px solid #e0e0e0;
        border-radius: 12px; /* Border radius sama dengan card */
        padding: 12px 20px; /* Padding lebih besar agar sesuai dengan card */
        transition: all 0.2s ease;
        height: 56px; /* Tinggi yang sesuai dengan card form */
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06); /* Shadow sama dengan card */
        border: 1px solid #eaeaea; /* Border sama dengan card */
    }

    .search-wrapper:focus-within {
        border-color: #007AFF;
        box-shadow: 0 0 0 3px rgba(0, 122, 255, 0.1);
    }

    .search-icon {
        margin-right: 12px;
        color: #8e8e93;
        font-size: 16px;
    }

    .search-input {
        flex: 1;
        border: none;
        outline: none;
        font-size: 15px;
        color: #1a1a1a;
        background: transparent;
        height: 100%;
    }

    .search-input::placeholder {
        color: #8e8e93;
    }

    /* Main Content */
    .main-content {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 30px;
        margin-bottom: 60px;
    }

    /* Form Card */
    .form-card {
        background: white;
        border-radius: 12px;
        padding: 32px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
        border: 1px solid #eaeaea;
        height: fit-content;
    }

    .form-title {
        font-size: 20px;
        font-weight: 600;
        color: #1a1a1a;
        margin-bottom: 28px;
        padding-bottom: 16px;
        border-bottom: 1px solid #f0f0f0;
    }

    /* Form Elements */
    .form-group {
        margin-bottom: 24px;
    }

    .form-label {
        display: block;
        font-size: 14px;
        font-weight: 500;
        color: #333;
        margin-bottom: 8px;
    }

    .form-input,
    .form-textarea,
    .form-select {
        width: 100%;
        padding: 12px 16px;
        border: 1px solid #e0e0e0;
        border-radius: 8px;
        font-size: 15px;
        color: #1a1a1a;
        background: #f8f9fa;
        transition: all 0.2s ease;
        font-family: inherit;
    }

    .form-input:focus,
    .form-textarea:focus,
    .form-select:focus {
        outline: none;
        border-color: #007AFF;
        background: white;
        box-shadow: 0 0 0 3px rgba(0, 122, 255, 0.1);
    }

    .form-input::placeholder,
    .form-textarea::placeholder {
        color: #8e8e93;
    }

    .form-textarea {
        resize: vertical;
        min-height: 120px;
        line-height: 1.6;
    }

    /* Form Row */
    .form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
    }

    .select-wrapper {
        position: relative;
    }

    .select-wrapper::after {
        content: '▼';
        position: absolute;
        right: 16px;
        top: 50%;
        transform: translateY(-50%);
        color: #8e8e93;
        font-size: 12px;
        pointer-events: none;
    }

    .form-select {
        appearance: none;
        cursor: pointer;
        padding-right: 40px;
    }

    /* Submit Button */
    .form-submit {
        margin-top: 32px;
        padding-top: 24px;
        border-top: 1px solid #f0f0f0;
    }

    .submit-button {
        width: 100%;
        padding: 14px;
        background: #007AFF;
        color: white;
        border: none;
        border-radius: 8px;
        font-size: 16px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s ease;
        letter-spacing: 0.3px;
    }

    .submit-button:hover {
        background: #0056CC;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(0, 122, 255, 0.2);
    }

    /* Right Column */
    .right-column {
        display: flex;
        flex-direction: column;
    }

    .list-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 24px;
    }

    .list-title-container {
        background: #007AFF;
        padding: 10px 24px;
        border-radius: 10px;
    }

    .list-title {
        font-size: 20px;
        font-weight: 600;
        color: white;
        margin: 0;
    }

    .counter-badge {
        background: white;
        padding: 8px 16px;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 500;
        color: #333;
        border: 1px solid #eaeaea;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
    }

    /* Announcement List */
    .announcement-list {
        flex: 1;
        min-height: 500px;
        background: white;
        border-radius: 12px;
        padding: 24px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
        border: 1px solid #eaeaea;
        overflow-y: auto;
    }

    /* Empty State */
    .empty-state {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        height: 400px;
        text-align: center;
    }

    .empty-icon {
        font-size: 48px;
        color: #e0e0e0;
        margin-bottom: 20px;
    }

    .empty-title {
        font-size: 18px;
        font-weight: 600;
        color: #666;
        margin-bottom: 8px;
    }

    .empty-subtitle {
        font-size: 14px;
        color: #8e8e93;
        max-width: 300px;
        line-height: 1.5;
    }

    /* Announcement Card */
    .announcement-card {
        background: white;
        border-radius: 10px;
        padding: 24px;
        margin-bottom: 16px;
        border: 1px solid #eaeaea;
        transition: all 0.2s ease;
    }

    .announcement-card:hover {
        border-color: #007AFF;
        box-shadow: 0 4px 12px rgba(0, 122, 255, 0.08);
    }

    .card-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 16px;
    }

    .card-title {
        font-size: 18px;
        font-weight: 600;
        color: #1a1a1a;
        margin: 0;
        flex: 1;
    }

    .priority-badge {
        padding: 6px 12px;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 600;
        margin-left: 12px;
    }

    .priority-low {
        background: rgba(40, 167, 69, 0.1);
        color: #155724;
    }

    .priority-medium {
        background: rgba(255, 193, 7, 0.1);
        color: #856404;
    }

    .priority-high {
        background: rgba(220, 53, 69, 0.1);
        color: #721c24;
    }

    .card-content {
        color: #555;
        font-size: 15px;
        line-height: 1.6;
        margin-bottom: 20px;
    }

    .card-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding-top: 20px;
        border-top: 1px solid #f0f0f0;
    }

    .department-info {
        display: flex;
        align-items: center;
        gap: 8px;
        color: #666;
        font-size: 14px;
        font-weight: 500;
    }

    .department-icon {
        color: #007AFF;
        font-size: 14px;
    }

    .timestamp {
        color: #8e8e93;
        font-size: 13px;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .time-icon {
        font-size: 12px;
    }

    /* User Info */
    .user-info {
        position: fixed;
        left: 40px;
        bottom: 40px;
        display: flex;
        align-items: center;
        gap: 12px;
        background: white;
        padding: 16px 20px;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        border: 1px solid #eaeaea;
        min-width: 180px;
    }

    .user-avatar {
        width: 40px;
        height: 40px;
        background: linear-gradient(135deg, #007AFF, #34C759);
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 18px;
        font-weight: 600;
    }

    .user-details {
        display: flex;
        flex-direction: column;
    }

    .user-name {
        font-size: 15px;
        font-weight: 600;
        color: #1a1a1a;
    }

    .user-role {
        font-size: 13px;
        color: #666;
        font-weight: 400;
        margin-top: 2px;
    }

    /* Responsive Design */
    @media (max-width: 1200px) {
        .announcement-container {
            padding: 30px;
        }

        .main-content {
            grid-template-columns: 1fr;
            gap: 30px;
        }

        .header-section {
            grid-template-columns: 1fr;
            gap: 20px;
        }

        .form-row {
            grid-template-columns: 1fr;
            gap: 20px;
        }

        .user-info {
            position: relative;
            left: 0;
            bottom: 0;
            margin-top: 40px;
            width: fit-content;
        }
    }

    @media (max-width: 768px) {
        .announcement-container {
            padding: 20px;
        }

        .header-section {
            grid-template-columns: 1fr;
            gap: 20px;
        }

        .search-section {
            width: 100%;
        }

        .list-header {
            flex-direction: column;
            align-items: stretch;
            gap: 16px;
        }

        .list-title-container {
            width: fit-content;
        }

        .counter-badge {
            align-self: flex-end;
        }

        .user-info {
            width: 100%;
            justify-content: center;
        }
    }

    /* Scrollbar Styling */
    .announcement-list::-webkit-scrollbar {
        width: 6px;
    }

    .announcement-list::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 3px;
    }

    .announcement-list::-webkit-scrollbar-thumb {
        background: #c1c1c1;
        border-radius: 3px;
    }

    .announcement-list::-webkit-scrollbar-thumb:hover {
        background: #a8a8a8;
    }

    /* Animations */
    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .announcement-card {
        animation: fadeIn 0.3s ease;
    }

    /* Utility Classes */
    .hidden {
        display: none !important;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Elements
        const searchInput = document.querySelector('.search-input');
        const announcementForm = document.getElementById('announcementForm');
        const titleInput = document.getElementById('titleInput');
        const contentInput = document.getElementById('contentInput');
        const prioritySelect = document.getElementById('prioritySelect');
        const departmentSelect = document.getElementById('departmentSelect');
        const submitButton = document.getElementById('submitButton');
        const announcementList = document.getElementById('announcementList');
        const announcementCount = document.getElementById('announcementCount');
        
        // State - TIDAK ADA DATA SAMPEL AWAL
        let announcements = [];
        let nextId = 1;

        // Priority Labels
        const priorityLabels = {
            low: { text: 'Rendah', class: 'priority-low' },
            medium: { text: 'Sedang', class: 'priority-medium' },
            high: { text: 'Tinggi', class: 'priority-high' }
        };

        // Department Labels
        const departmentLabels = {
            IT: 'IT Support',
            Finance: 'Finance',
            Secretariat: 'Secretariat'
        };

        // Time Format
        function formatTime(date) {
            const now = new Date();
            const diffMs = now - date;
            const diffSec = Math.floor(diffMs / 1000);
            const diffMin = Math.floor(diffSec / 60);
            const diffHour = Math.floor(diffMin / 60);
            const diffDay = Math.floor(diffHour / 24);

            if (diffSec < 60) return 'Baru saja';
            if (diffMin < 60) return `${diffMin} menit yang lalu`;
            if (diffHour < 24) return `${diffHour} jam yang lalu`;
            if (diffDay === 1) return 'Kemarin';
            return `${diffDay} hari yang lalu`;
        }

        // Create Announcement Card
        function createAnnouncementCard(announcement) {
            const card = document.createElement('div');
            card.className = 'announcement-card';
            
            const priority = priorityLabels[announcement.priority];
            const department = departmentLabels[announcement.department];
            const timeAgo = formatTime(announcement.createdAt);

            card.innerHTML = `
                <div class="card-header">
                    <h3 class="card-title">${announcement.title}</h3>
                    <span class="priority-badge ${priority.class}">${priority.text}</span>
                </div>
                <div class="card-content">
                    ${announcement.content.replace(/\n/g, '<br>')}
                </div>
                <div class="card-footer">
                    <div class="department-info">
                        <span class="department-icon">🏢</span>
                        <span>${department}</span>
                    </div>
                    <div class="timestamp">
                        <span class="time-icon">🕒</span>
                        <span>${timeAgo}</span>
                    </div>
                </div>
            `;

            return card;
        }

        // Render Announcements
        function renderAnnouncements(filterText = '') {
            announcementList.innerHTML = '';
            
            const filteredAnnouncements = announcements.filter(announcement => {
                if (!filterText) return true;
                const searchText = filterText.toLowerCase();
                return (
                    announcement.title.toLowerCase().includes(searchText) ||
                    announcement.content.toLowerCase().includes(searchText) ||
                    departmentLabels[announcement.department].toLowerCase().includes(searchText)
                );
            });

            if (filteredAnnouncements.length === 0) {
                announcementList.innerHTML = `
                    <div class="empty-state">
                        <div class="empty-icon">📢</div>
                        <p class="empty-title">${filterText ? 'Tidak ditemukan' : 'Belum ada pengumuman'}</p>
                        <p class="empty-subtitle">${filterText ? 'Coba kata kunci lain' : 'Pengumuman yang dibuat akan muncul di sini'}</p>
                    </div>
                `;
            } else {
                filteredAnnouncements.forEach(announcement => {
                    const card = createAnnouncementCard(announcement);
                    announcementList.appendChild(card);
                });
            }

            // Update counter
            announcementCount.textContent = filteredAnnouncements.length;
        }

        // Add New Announcement
        function addAnnouncement(title, content, priority, department) {
            const newAnnouncement = {
                id: nextId++,
                title,
                content,
                priority,
                department,
                createdAt: new Date()
            };

            announcements.unshift(newAnnouncement); // Add to beginning
            renderAnnouncements(searchInput.value);
            
            // Clear form
            titleInput.value = '';
            contentInput.value = '';
            prioritySelect.value = 'medium';
            departmentSelect.value = 'IT Support';
            
            return newAnnouncement;
        }

        // Form Submission
        if (announcementForm) {
            announcementForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                const title = titleInput.value.trim();
                const content = contentInput.value.trim();
                const priority = prioritySelect.value;
                const department = departmentSelect.value;

                // Validation
                if (!title) {
                    showAlert('Masukkan judul pengumuman terlebih dahulu!', 'error');
                    titleInput.focus();
                    return;
                }

                if (!content) {
                    showAlert('Masukkan isi pengumuman terlebih dahulu!', 'error');
                    contentInput.focus();
                    return;
                }

                // Disable button during submission
                submitButton.disabled = true;
                submitButton.textContent = 'Mengirim...';
                
                // Simulate API call
                setTimeout(() => {
                    addAnnouncement(title, content, priority, department);
                    showAlert('Pengumuman berhasil dibuat!', 'success');
                    
                    // Reset button
                    submitButton.disabled = false;
                    submitButton.textContent = 'Publikasikan';
                    
                    // Scroll to top of list
                    announcementList.scrollTop = 0;
                }, 500);
            });
        }

        // Search Functionality
        if (searchInput) {
            searchInput.addEventListener('input', function() {
                renderAnnouncements(this.value.trim());
            });
        }

        // Alert System
        function showAlert(message, type) {
            // Remove existing alert
            const existingAlert = document.querySelector('.custom-alert');
            if (existingAlert) {
                existingAlert.remove();
            }
            
            // Create alert
            const alert = document.createElement('div');
            alert.className = `custom-alert ${type}`;
            
            // Get icon based on type
            let icon = 'ℹ️';
            if (type === 'success') icon = '✅';
            if (type === 'error') icon = '❌';
            
            alert.innerHTML = `
                <div class="alert-content">
                    <span class="alert-icon">${icon}</span>
                    <span class="alert-message">${message}</span>
                    <button class="alert-close">×</button>
                </div>
            `;
            
            // Add styles
            const style = document.createElement('style');
            style.textContent = `
                .custom-alert {
                    position: fixed;
                    top: 20px;
                    right: 20px;
                    z-index: 1000;
                    animation: slideIn 0.3s ease;
                    min-width: 300px;
                    max-width: 400px;
                }
                
                .alert-content {
                    display: flex;
                    align-items: center;
                    padding: 16px;
                    border-radius: 8px;
                    font-size: 14px;
                    font-weight: 500;
                    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
                }
                
                .custom-alert.success .alert-content {
                    background: #d4edda;
                    color: #155724;
                    border: 1px solid #c3e6cb;
                }
                
                .custom-alert.error .alert-content {
                    background: #f8d7da;
                    color: #721c24;
                    border: 1px solid #f5c6cb;
                }
                
                .alert-icon {
                    margin-right: 12px;
                    font-size: 16px;
                }
                
                .alert-message {
                    flex: 1;
                }
                
                .alert-close {
                    background: none;
                    border: none;
                    color: inherit;
                    font-size: 20px;
                    cursor: pointer;
                    padding: 0;
                    margin-left: 12px;
                    opacity: 0.7;
                    transition: opacity 0.2s;
                    line-height: 1;
                    width: 24px;
                    height: 24px;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                }
                
                .alert-close:hover {
                    opacity: 1;
                }
                
                @keyframes slideIn {
                    from {
                        transform: translateX(100%);
                        opacity: 0;
                    }
                    to {
                        transform: translateX(0);
                        opacity: 1;
                    }
                }
                
                @keyframes slideOut {
                    from {
                        transform: translateX(0);
                        opacity: 1;
                    }
                    to {
                        transform: translateX(100%);
                        opacity: 0;
                    }
                }
            `;
            
            // Add to document
            if (!document.querySelector('style[data-alert-style]')) {
                style.setAttribute('data-alert-style', '');
                document.head.appendChild(style);
            }
            
            document.body.appendChild(alert);
            
            // Close button
            const closeBtn = alert.querySelector('.alert-close');
            closeBtn.addEventListener('click', function() {
                alert.style.animation = 'slideOut 0.3s ease forwards';
                setTimeout(() => {
                    if (alert.parentNode) {
                        alert.remove();
                    }
                }, 300);
            });
            
            // Auto remove after 5 seconds
            setTimeout(() => {
                if (alert.parentNode) {
                    alert.style.animation = 'slideOut 0.3s ease forwards';
                    setTimeout(() => {
                        if (alert.parentNode) {
                            alert.remove();
                        }
                    }, 300);
                }
            }, 5000);
        }

        // Inisialisasi tanpa data sampel - hanya "belum ada pengumuman"
        renderAnnouncements();
    });
</script>
@endsection