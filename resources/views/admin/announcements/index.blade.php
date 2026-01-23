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

                    {{-- Priority, Category and Department --}}
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

                        {{-- Kategori --}}
                        <div class="form-group">
                            <label class="form-label">Kategori</label>
                            <div class="select-wrapper">
                                <select class="form-select" id="categorySelect">
                                    <option value="reminder" selected>Reminder</option>
                                    <option value="payment_info">Informasi Pembayaran</option>
                                    <option value="system_update">Update Sistem</option>
                                    <option value="general">Pengumuman Umum</option>
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
                                    <option value="Maintenance">Maintenance</option>
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
            <div class="right-content">
                {{-- List Header --}}
                <div class="list-header">
                    <div class="list-title-container">
                        <h2 class="list-title">Daftar Pengumuman</h2>
                    </div>
                    <div class="counter-badge">
                        <span id="announcementCount">0</span> pengumuman
                    </div>
                </div>

                {{-- Filter Section --}}
                <div class="filter-section">
                    <div class="filter-buttons">
                        <button class="filter-btn active" data-filter="all">Semua</button>
                        <button class="filter-btn" data-filter="reminder">Reminder</button>
                        <button class="filter-btn" data-filter="payment_info">Pembayaran</button>
                        <button class="filter-btn" data-filter="system_update">Sistem</button>
                        <button class="filter-btn" data-filter="general">Umum</button>
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
        grid-template-columns: 1fr 1fr;
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

    /* Search Section */
    .search-section {
        width: 100%;
    }

    .search-wrapper {
        display: flex;
        align-items: center;
        background: white;
        border: 1px solid #e0e0e0;
        border-radius: 12px;
        padding: 12px 20px;
        transition: all 0.2s ease;
        height: 56px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
        border: 1px solid #eaeaea;
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

    /* Main Content - UPDATED FOR ALIGNMENT */
    .main-content {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 30px;
        margin-bottom: 60px;
        align-items: stretch; /* Ini yang membuat kedua kolom sama tinggi */
        min-height: calc(100vh - 200px); /* Minimum height */
    }

    /* Left Column */
    .left-column {
        display: flex;
        flex-direction: column;
    }

    /* Form Card - UPDATED FOR ALIGNMENT */
    .form-card {
        background: white;
        border-radius: 12px;
        padding: 32px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
        border: 1px solid #eaeaea;
        flex: 1; /* Ini yang membuat card memenuhi tinggi parent */
        display: flex;
        flex-direction: column;
        height: 100%; /* Memastikan tinggi penuh */
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
        flex: 1; /* Membuat textarea bisa mengembang */
    }

    /* Form Row - Updated for 3 columns */
    .form-row {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 15px;
        margin-bottom: 20px;
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

    /* Submit Button - UPDATED FOR BOTTOM ALIGNMENT */
    .form-submit {
        margin-top: auto; /* Ini yang membuat button selalu di bawah */
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

    /* Right Column - UPDATED FOR ALIGNMENT */
    .right-column {
        display: flex;
        flex-direction: column;
        height: 100%;
    }

    .right-content {
        display: flex;
        flex-direction: column;
        height: 100%;
        flex: 1;
    }

    .list-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        flex-shrink: 0; /* Mencegah header menyusut */
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

    /* Filter Section */
    .filter-section {
        margin-bottom: 20px;
        padding: 15px;
        background: white;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
        border: 1px solid #eaeaea;
        flex-shrink: 0; /* Mencegah filter menyusut */
    }

    .filter-buttons {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
    }

    .filter-btn {
        padding: 8px 16px;
        border: 1px solid #e0e0e0;
        background: white;
        border-radius: 6px;
        font-size: 13px;
        font-weight: 500;
        color: #666;
        cursor: pointer;
        transition: all 0.2s ease;
        white-space: nowrap;
    }

    .filter-btn:hover {
        background: #f0f0f0;
        border-color: #007AFF;
        color: #007AFF;
    }

    .filter-btn.active {
        background: #007AFF;
        border-color: #007AFF;
        color: white;
    }

    /* Announcement List - UPDATED FOR ALIGNMENT */
    .announcement-list {
        flex: 1; /* Ini yang membuat list mengisi sisa ruang */
        background: white;
        border-radius: 12px;
        padding: 24px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
        border: 1px solid #eaeaea;
        overflow-y: auto;
        min-height: 500px; /* Minimum height */
        display: flex;
        flex-direction: column;
    }

    /* Empty State */
    .empty-state {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        height: 100%;
        text-align: center;
        flex: 1;
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
        position: relative;
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
        white-space: nowrap;
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

    /* Category Badge */
    .category-badge {
        display: inline-block;
        padding: 4px 10px;
        border-radius: 4px;
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.3px;
        margin-right: 8px;
        margin-bottom: 8px;
    }

    .category-reminder {
        background: rgba(52, 199, 89, 0.1);
        color: #155724;
    }

    .category-payment_info {
        background: rgba(0, 122, 255, 0.1);
        color: #004085;
    }

    .category-system_update {
        background: rgba(88, 86, 214, 0.1);
        color: #3d3b94;
    }

    .category-general {
        background: rgba(142, 142, 147, 0.1);
        color: #4a4a4a;
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

    /* Category Tags */
    .category-tags {
        display: flex;
        flex-wrap: wrap;
        margin-top: 12px;
        gap: 8px;
    }

    /* Responsive Design */
    @media (max-width: 1200px) {
        .announcement-container {
            padding: 30px;
        }

        .main-content {
            grid-template-columns: 1fr;
            gap: 30px;
            min-height: auto;
        }

        .header-section {
            grid-template-columns: 1fr;
            gap: 20px;
        }

        .form-row {
            grid-template-columns: 1fr;
            gap: 20px;
        }

        .form-textarea {
            min-height: 150px; /* Lebih tinggi di mobile */
        }
    }

    @media (max-width: 992px) {
        .form-row {
            grid-template-columns: repeat(2, 1fr);
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

        .form-row {
            grid-template-columns: 1fr;
        }

        .filter-buttons {
            overflow-x: auto;
            padding-bottom: 8px;
            flex-wrap: nowrap;
        }

        .filter-btn {
            flex-shrink: 0;
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
        const categorySelect = document.getElementById('categorySelect');
        const departmentSelect = document.getElementById('departmentSelect');
        const submitButton = document.getElementById('submitButton');
        const announcementList = document.getElementById('announcementList');
        const announcementCount = document.getElementById('announcementCount');
        const filterButtons = document.querySelectorAll('.filter-btn');
        
        // State
        let announcements = [];
        let nextId = 1;
        let currentFilter = 'all';

        // Priority Labels
        const priorityLabels = {
            low: { text: 'Rendah', class: 'priority-low' },
            medium: { text: 'Sedang', class: 'priority-medium' },
            high: { text: 'Tinggi', class: 'priority-high' }
        };

        // Category Labels
        const categoryLabels = {
            reminder: { text: 'Reminder', class: 'category-reminder', icon: '⏰' },
            payment_info: { text: 'Informasi Pembayaran', class: 'category-payment_info', icon: '💰' },
            system_update: { text: 'Update Sistem', class: 'category-system_update', icon: '🔄' },
            general: { text: 'Pengumuman Umum', class: 'category-general', icon: '📢' }
        };

        // Department Labels
        const departmentLabels = {
            IT: 'IT Support',
            Finance: 'Finance',
            Secretariat: 'Secretariat',
            Maintenance: 'Maintenance',
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
            card.dataset.category = announcement.category;
            
            const priority = priorityLabels[announcement.priority];
            const category = categoryLabels[announcement.category];
            const department = departmentLabels[announcement.department];
            const timeAgo = formatTime(announcement.createdAt);

            card.innerHTML = `
                <div class="card-header">
                    <h3 class="card-title">${announcement.title}</h3>
                    <span class="priority-badge ${priority.class}">${priority.text}</span>
                </div>
                <div class="category-tags">
                    <span class="category-badge ${category.class}">
                        ${category.icon} ${category.text}
                    </span>
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
        function renderAnnouncements(filterText = '', categoryFilter = currentFilter) {
            announcementList.innerHTML = '';
            
            const filteredAnnouncements = announcements.filter(announcement => {
                // Filter by category
                if (categoryFilter !== 'all' && announcement.category !== categoryFilter) {
                    return false;
                }
                
                // Filter by search text
                if (filterText) {
                    const searchText = filterText.toLowerCase();
                    return (
                        announcement.title.toLowerCase().includes(searchText) ||
                        announcement.content.toLowerCase().includes(searchText) ||
                        departmentLabels[announcement.department].toLowerCase().includes(searchText) ||
                        categoryLabels[announcement.category].text.toLowerCase().includes(searchText)
                    );
                }
                
                return true;
            });

            if (filteredAnnouncements.length === 0) {
                announcementList.innerHTML = `
                    <div class="empty-state">
                        <div class="empty-icon">📢</div>
                        <p class="empty-title">${filterText || categoryFilter !== 'all' ? 'Tidak ditemukan' : 'Belum ada pengumuman'}</p>
                        <p class="empty-subtitle">${filterText ? 'Coba kata kunci lain' : categoryFilter !== 'all' ? 'Tidak ada pengumuman dengan kategori ini' : 'Pengumuman yang dibuat akan muncul di sini'}</p>
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
        function addAnnouncement(title, content, priority, category, department) {
            const newAnnouncement = {
                id: nextId++,
                title,
                content,
                priority,
                category,
                department,
                createdAt: new Date()
            };

            announcements.unshift(newAnnouncement);
            renderAnnouncements(searchInput.value, currentFilter);
            
            // Clear form
            titleInput.value = '';
            contentInput.value = '';
            prioritySelect.value = 'medium';
            categorySelect.value = 'reminder';
            departmentSelect.value = 'IT';
            
            return newAnnouncement;
        }

        // Form Submission
        if (announcementForm) {
            announcementForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                const title = titleInput.value.trim();
                const content = contentInput.value.trim();
                const priority = prioritySelect.value;
                const category = categorySelect.value;
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
                    addAnnouncement(title, content, priority, category, department);
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
                renderAnnouncements(this.value.trim(), currentFilter);
            });
        }

        // Filter Buttons
        filterButtons.forEach(button => {
            button.addEventListener('click', function() {
                // Remove active class from all buttons
                filterButtons.forEach(btn => btn.classList.remove('active'));
                
                // Add active class to clicked button
                this.classList.add('active');
                
                // Update current filter
                currentFilter = this.dataset.filter;
                
                // Re-render announcements
                renderAnnouncements(searchInput.value, currentFilter);
            });
        });

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

        // Initialize
        renderAnnouncements();
    });
</script>
@endsection