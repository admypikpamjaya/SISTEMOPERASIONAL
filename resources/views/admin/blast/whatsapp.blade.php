@extends('layouts.app')

@section('title', 'WhatsApp Blast')

@section('content')
<div class="whatsapp-blasting-container">
    {{-- Header dengan Info dan Stats --}}
    <div class="header-section">
        <div class="header-left">
            <div class="app-icon">
                <svg width="32" height="32" viewBox="0 0 32 32" fill="none">
                    <path d="M16 2C8.268 2 2 8.268 2 16C2 18.368 2.588 20.596 3.612 22.556L2.132 28.388C2.024 28.796 2.192 29.224 2.552 29.44C2.728 29.548 2.924 29.6 3.12 29.6C3.296 29.6 3.472 29.56 3.632 29.48L9.444 26.388C11.404 27.412 13.632 28 16 28C23.732 28 30 21.732 30 16C30 8.268 23.732 2 16 2Z" fill="#25D366"/>
                    <path d="M23.36 19.96C23.04 20.92 21.64 21.76 20.56 22.04C19.84 22.24 18.92 22.4 16.04 21.28C12.36 19.84 10.04 16.08 9.84 15.8C9.64 15.52 8.2 13.6 8.2 11.6C8.2 9.6 9.24 8.64 9.64 8.2C10.04 7.76 10.52 7.64 10.84 7.64C10.96 7.64 11.08 7.64 11.2 7.64C11.6 7.64 12 7.64 12.36 8.52C12.76 9.48 13.76 11.48 13.88 11.72C14 11.96 14.12 12.28 13.96 12.56C13.8 12.84 13.68 13 13.48 13.24C13.28 13.48 13.04 13.76 12.88 13.92C12.68 14.12 12.44 14.32 12.68 14.72C12.92 15.12 13.76 16.48 14.96 17.52C16.52 18.88 17.8 19.32 18.24 19.52C18.68 19.72 18.96 19.68 19.2 19.4C19.44 19.12 20.4 18 20.68 17.6C20.96 17.2 21.24 17.28 21.64 17.44C22.04 17.6 24.04 18.6 24.48 18.8C24.92 19 25.2 19.12 25.32 19.32C25.44 19.52 25.44 20.48 23.36 19.96Z" fill="white"/>
                </svg>
            </div>
            <div class="app-info">
                <div class="app-title">WhatsApp Blast</div>
                <div class="app-subtitle">Kirim pesan massal ke WhatsApp</div>
            </div>
        </div>
    </div>

    {{-- Stats Cards --}}
    <div class="stats-container">
        <div class="stat-card">
            <div class="stat-content">
                <div class="stat-label">Total</div>
                <div class="stat-value" id="statTotal">{{ $activityStats['total'] ?? 0 }}</div>
            </div>
            <div class="stat-icon blue">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                    <path d="M17 21V19C17 17.9391 16.5786 16.9217 15.8284 16.1716C15.0783 15.4214 14.0609 15 13 15H5C3.93913 15 2.92172 15.4214 2.17157 16.1716C1.42143 16.9217 1 17.9391 1 19V21" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M9 11C11.2091 11 13 9.20914 13 7C13 4.79086 11.2091 3 9 3C6.79086 3 5 4.79086 5 7C5 9.20914 6.79086 11 9 11Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M23 21V19C22.9993 18.1137 22.7044 17.2528 22.1614 16.5523C21.6184 15.8519 20.8581 15.3516 20 15.13" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M16 3.13C16.8604 3.35031 17.623 3.85071 18.1676 4.55232C18.7122 5.25392 19.0078 6.11683 19.0078 7.005C19.0078 7.89318 18.7122 8.75608 18.1676 9.45769C17.623 10.1593 16.8604 10.6597 16 10.88" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-content">
                <div class="stat-label">Terkirim</div>
                <div class="stat-value" id="statSent">{{ $activityStats['sent'] ?? 0 }}</div>
            </div>
            <div class="stat-icon green">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                    <path d="M22 11.08V12C21.9988 14.1564 21.3005 16.2547 20.0093 17.9818C18.7182 19.7088 16.9033 20.9725 14.8354 21.5839C12.7674 22.1953 10.5573 22.1219 8.53447 21.3746C6.51168 20.6273 4.78465 19.2461 3.61096 17.4371C2.43727 15.628 1.87979 13.4881 2.02168 11.3363C2.16356 9.18455 2.99721 7.13631 4.39828 5.49706C5.79935 3.85781 7.69279 2.71537 9.79619 2.24013C11.8996 1.7649 14.1003 1.98232 16.07 2.85999" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M22 4L12 14.01L9 11.01" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-content">
                <div class="stat-label">Gagal</div>
                <div class="stat-value" id="statFailed">{{ $activityStats['failed'] ?? 0 }}</div>
            </div>
            <div class="stat-icon red">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                    <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M15 9L9 15" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M9 9L15 15" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-content">
                <div class="stat-label">Pending</div>
                <div class="stat-value" id="statPending">{{ $activityStats['pending'] ?? 0 }}</div>
            </div>
            <div class="stat-icon yellow">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                    <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M12 6V12L16 14" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </div>
        </div>
    </div>

    {{-- Main Content Grid --}}
    <div class="main-grid">
        {{-- FORM BLASTING DARI CODE PERTAMA --}}
        <form method="POST" action="{{ route('admin.blast.whatsapp.send') }}" enctype="multipart/form-data" id="whatsappBlastForm">
            @csrf
            {{-- Top Row - Penerima and Kotak Pesan --}}
            <div class="top-row">
                {{-- Left Column - Penerima --}}
                <div class="white-card recipient-card">
                    <div class="section-title">Penerima</div>
                    
                    <div class="phone-input-section">
                        <input type="text" class="phone-input-main" placeholder="Contoh: 6281234567890" id="phoneInput">
                        <button type="button" class="add-button" id="addPhoneBtn">
                            <span class="add-icon">+</span>
                        </button>
                    </div>

                    {{-- Excel Import Section dengan File Input Tersembunyi --}}
                    <input type="file" id="excelFileInput" accept=".xlsx,.xls,.csv" style="display: none;">
                    <div class="excel-import" id="excelImport">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
                            <path d="M14 2H6C5.46957 2 4.96086 2.21071 4.58579 2.58579C4.21071 2.96086 4 3.46957 4 4V20C4 20.5304 4.21071 21.0391 4.58579 21.4142C4.96086 21.7893 5.46957 22 6 22H18C18.5304 22 19.0391 21.7893 19.4142 21.4142C19.7893 21.0391 20 20.5304 20 20V8L14 2Z" stroke="#1D1D41" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M14 2V8H20" stroke="#1D1D41" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        <span>Impor Excel</span>
                    </div>

                    {{-- Info Excel Import --}}
                    <div class="excel-import-info" id="excelImportInfo" style="display: none; font-size: 12px; color: #666; margin-bottom: 10px; padding: 8px; background: #f8f9fa; border-radius: 6px;">
                        <div>Format Excel harus memiliki kolom: <strong>Nomor WhatsApp</strong> (opsional: Nama, Kelas)</div>
                    </div>

                    <div class="recipient-list" id="recipientList">
                        <div class="recipient-status">Belum ada penerima</div>
                    </div>

                    <div class="recipient-db-section">
                        <div class="recipient-db-header">
                            <span class="recipient-db-title">Recipient List DB</span>
                            <button type="button" class="btn-select-db" id="selectAllRecipientsBtn">
                                Select All
                            </button>
                        </div>
                        <div class="recipient-db-count">
                            Total valid recipient: {{ $recipients->count() }}
                        </div>
                        <div class="recipient-db-search">
                            <input
                                type="text"
                                id="recipientDbSearchInput"
                                class="recipient-db-search-input"
                                placeholder="Cari recipient DB..."
                            >
                        </div>
                        <div class="recipient-db-list">
                            @forelse($recipients as $recipient)
                                <label class="recipient-db-item" for="recipient_{{ $recipient->id }}">
                                    <input
                                        type="checkbox"
                                        class="recipient-db-checkbox"
                                        id="recipient_{{ $recipient->id }}"
                                        name="recipient_ids[]"
                                        value="{{ $recipient->id }}"
                                        data-phone="{{ $recipient->wa_wali }}"
                                    >
                                    <div class="recipient-db-info">
                                        <div class="recipient-db-name">
                                            {{ $recipient->nama_siswa }} ({{ $recipient->kelas }})
                                        </div>
                                        <div class="recipient-db-phone">
                                            {{ $recipient->nama_wali }} - {{ $recipient->wa_wali }}
                                        </div>
                                    </div>
                                </label>
                            @empty
                                <div class="recipient-db-empty">Tidak ada recipient WhatsApp valid.</div>
                            @endforelse
                        </div>
                    </div>

                    {{-- HIDDEN TARGETS FIELD DARI CODE PERTAMA --}}
                    <textarea name="targets" id="targetsField" class="field-input" style="display: none;" rows="3" placeholder="6281234567890, 6289876543210"></textarea>
                </div>

                {{-- Middle Column - Kotak Pesan --}}
                <div class="white-card message-card">
                    <div class="section-header">
                        <div class="section-title">Kotak Pesan</div>
                    </div>

                    {{-- Input fields untuk personalisasi --}}
                    <div class="form-group">
                        <label class="form-label">Nama Siswa:</label>
                        <input type="text" class="form-input" id="studentName" name="student_name" placeholder="Masukkan nama siswa">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Kelas:</label>
                        <input type="text" class="form-input" id="studentClass" name="student_class" placeholder="Masukkan kelas (contoh: 5A)">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Nama Wali:</label>
                        <input type="text" class="form-input" id="parentName" name="parent_name" placeholder="Masukkan nama wali">
                    </div>

                    <div class="template-section">
                        <label class="template-label">Template:</label>
                        <div class="template-selector">
                            <select class="template-select" id="templateSelect">
                                <option value="">Pilih Template</option>
                                <option value="reminder">Reminder Tagihan Sekolah</option>
                                <option value="payment">Informasi Pembayaran Sekolah</option>
                                <option value="notification">Pemberitahuan Tunggakan</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Template Blast DB:</label>
                        <select
                            name="template_id"
                            id="dbTemplateSelect"
                            class="form-input"
                            style="height: auto; padding: 12px 16px;"
                        >
                            <option value="">Tanpa template</option>
                            @foreach($templates as $template)
                                <option
                                    value="{{ $template->id }}"
                                    data-content="{{ e($template->content) }}"
                                >
                                    {{ $template->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Template Preview:</label>
                        <div id="dbTemplatePreview" class="template-preview-box">
                            Pilih template untuk melihat preview.
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Pesan Khusus Per Penerima:</label>
                        <div class="recipient-message-note">
                            Atur per penerima: pilih mode <b>manual</b>, <b>template</b>, atau <b>global</b>.
                        </div>
                        <div id="recipientMessageMatrix" class="recipient-message-matrix">
                            <div class="recipient-db-empty">
                                Pilih recipient DB atau tambah nomor WhatsApp manual untuk mengatur pesan khusus.
                            </div>
                        </div>
                        <input type="hidden" name="message_overrides" id="messageOverridesField">
                    </div>

                    <div class="selected-templates" id="selectedTemplates" style="display: none;">
                        <!-- Template tags will be added here dynamically -->
                    </div>

                    {{-- MESSAGE FIELD DARI CODE PERTAMA --}}
                    <div class="message-editor">
                        <textarea 
                            name="message" 
                            class="message-textarea" 
                            placeholder="Ketik pesan Anda di sini..." 
                            id="messageTextarea" 
                            rows="5"
                        ></textarea>
                        <label class="global-default-toggle">
                            <input type="checkbox" name="use_global_default" id="useGlobalDefaultToggle" value="1" checked>
                            Gunakan isi pesan global sebagai default penerima (override template jika tidak dipilih khusus).
                        </label>
                    </div>

                    <div class="message-footer">
                        <div class="attachment-buttons">
                            <button type="button" class="attach-btn" id="attachFile">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
                                    <path d="M21.44 11.05L12.25 20.24C11.1242 21.3658 9.59723 21.9983 8.005 21.9983C6.41277 21.9983 4.88583 21.3658 3.76 20.24C2.63417 19.1142 2.00166 17.5872 2.00166 15.995C2.00166 14.4028 2.63417 12.8758 3.76 11.75L12.33 3.18C13.0806 2.42944 14.0991 2.00667 15.16 2.00667C16.2209 2.00667 17.2394 2.42944 17.99 3.18C18.7406 3.93056 19.1633 4.94908 19.1633 6.01C19.1633 7.07092 18.7406 8.08944 17.99 8.84L9.41 17.41C9.03472 17.7853 8.52548 17.9967 7.995 17.9967C7.46452 17.9967 6.95528 17.7853 6.58 17.41C6.20472 17.0347 5.99333 16.5255 5.99333 15.995C5.99333 15.4645 6.20472 14.9553 6.58 14.58L15.07 6.1" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                                <span>Lampirkan File</span>
                            </button>
                        </div>
                        <div class="char-count" id="charCount">0 karakter</div>
                    </div>

                    {{-- ATTACHMENT FIELD DARI CODE PERTAMA --}}
                    <div class="form-group" style="margin-bottom: 15px; display: none;" id="attachmentContainer">
                        <label class="form-label">Lampiran (Opsional)</label>
                        <input 
                            type="file" 
                            name="attachments[]" 
                            class="form-input"
                            multiple 
                            accept=".pdf,.jpg,.jpeg,.png"
                        >
                        <small style="font-size: 11px; color: #999;">Maksimal 5MB per file. PDF / Image.</small>
                    </div>

                    {{-- SEND BUTTON DARI CODE PERTAMA --}}
                    <button type="submit" class="send-button" id="sendButton">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
                            <path d="M22 2L11 13" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M22 2L15 22L11 13L2 9L22 2Z" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        <span>Kirim Pesan</span>
                    </button>
                </div>
            </div>
        </form>

        {{-- Bottom Row - Activity Log (Full Width) --}}
        <div class="white-card activity-card">
            <div class="activity-header">
                <div class="section-title">Activity Log</div>
                <div class="search-small">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
                        <circle cx="11" cy="11" r="8" stroke="#999" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M21 21L16.65 16.65" stroke="#999" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    <input type="text" placeholder="Cari..." class="search-input-small" id="searchInput">
                </div>
            </div>

            <div class="activity-table">
                <div class="activity-table-header">
                    <div class="col-waktu">Detail Waktu</div>
                    <div class="col-siswa">Nama Siswa</div>
                    <div class="col-kelas">Kelas</div>
                    <div class="col-wali">Nama Wali</div>
                    <div class="col-wa">Nomor WhatsApp</div>
                    <div class="col-status">Status</div>
                </div>
                <div class="activity-table-body" id="activityLog">
                    <div class="activity-empty">Belum ada aktivitas</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Tips Section --}}
    <div class="tips-section">
        <div class="tips-icon">💡</div>
        <div class="tips-content-wrapper">
            <div class="tips-title">Tips</div>
            <div class="tips-list">
                <div class="tip-item">Sertakan kode negara pada nomor telepon (contoh: 6281234567890).</div>
                <div class="tip-item">Personalisasi pesan menggunakan variabel untuk engagement lebih baik.</div>
                <div class="tip-item">Hindari mengirim terlalu banyak pesan sekaligus untuk mencegah pemblokiran.</div>
            </div>
        </div>
    </div>
</div>

<style>
/* Semua CSS yang sudah ada tetap di sini */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Roboto', 'Oxygen', 'Ubuntu', sans-serif;
}

.whatsapp-blasting-container {
    width: 100%;
    min-height: 100vh;
    padding: 30px;
}

/* Header Section */
.header-section {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 25px;
}

.header-left {
    display: flex;
    align-items: center;
    gap: 15px;
}

.app-icon {
    width: 50px;
    height: 50px;
    background: white;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.app-info {
    color: #1D1D41;
}

.app-title {
    font-size: 24px;
    font-weight: 700;
    margin-bottom: 2px;
    color: #1D1D41;
}

.app-subtitle {
    font-size: 14px;
    color: #666;
}

/* Stats Container */
.stats-container {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 20px;
    margin-bottom: 25px;
}

.stat-card {
    background: white;
    border-radius: 12px;
    padding: 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    transition: transform 0.2s, box-shadow 0.2s;
}

.stat-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.15);
}

.stat-content {
    flex: 1;
}

.stat-label {
    font-size: 14px;
    color: #666;
    margin-bottom: 8px;
}

.stat-value {
    font-size: 32px;
    font-weight: 700;
    color: #1D1D41;
}

.stat-icon {
    width: 48px;
    height: 48px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.stat-icon.blue {
    background: rgba(59, 130, 246, 0.1);
    color: #3b82f6;
}

.stat-icon.green {
    background: rgba(34, 197, 94, 0.1);
    color: #22c55e;
}

.stat-icon.red {
    background: rgba(239, 68, 68, 0.1);
    color: #ef4444;
}

.stat-icon.yellow {
    background: rgba(251, 191, 36, 0.1);
    color: #fbbf24;
}

/* Main Grid */
.main-grid {
    display: flex;
    flex-direction: column;
    gap: 20px;
    margin-bottom: 25px;
}

/* Top Row - Penerima and Kotak Pesan */
.top-row {
    display: grid;
    grid-template-columns: 1fr 1.8fr;
    gap: 20px;
}

.white-card {
    background: white;
    border-radius: 12px;
    padding: 25px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.section-title {
    font-size: 18px;
    font-weight: 600;
    color: #1D1D41;
    margin-bottom: 20px;
}

/* Form Groups */
.form-group {
    margin-bottom: 15px;
}

.form-label {
    display: block;
    font-size: 13px;
    color: #666;
    font-weight: 500;
    margin-bottom: 8px;
}

.form-input {
    width: 100%;
    height: 40px;
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    padding: 0 12px;
    font-size: 14px;
    outline: none;
    transition: border-color 0.2s;
}

.form-input:focus {
    border-color: #3b82f6;
}

/* Recipient Card */
.phone-input-section {
    display: flex;
    gap: 10px;
    margin-bottom: 15px;
}

.phone-input-main {
    flex: 1;
    height: 45px;
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    padding: 0 15px;
    font-size: 14px;
    outline: none;
    transition: border-color 0.2s;
}

.phone-input-main:focus {
    border-color: #3b82f6;
}

.add-button {
    width: 45px;
    height: 45px;
    background: #3b82f6;
    border: none;
    border-radius: 8px;
    color: white;
    font-size: 24px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: background 0.2s;
}

.add-button:hover {
    background: #2563eb;
}

.add-icon {
    line-height: 1;
}

.excel-import {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 12px;
    background: #f8f9fa;
    border: 1px dashed #d0d0d0;
    border-radius: 8px;
    cursor: pointer;
    margin-bottom: 20px;
    transition: all 0.2s;
}

.excel-import:hover {
    background: #e9ecef;
    border-color: #3b82f6;
}

.excel-import span {
    font-size: 14px;
    color: #1D1D41;
    font-weight: 500;
}

.recipient-list {
    min-height: 150px;
    max-height: 400px;
    overflow-y: auto;
}

.recipient-status {
    text-align: center;
    color: #999;
    padding: 40px 20px;
    font-size: 14px;
}

.recipient-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px 12px;
    background: #f8f9fa;
    border-radius: 6px;
    margin-bottom: 8px;
    border: 1px solid #e9ecef;
}

.recipient-number {
    font-size: 13px;
    color: #1D1D41;
}

.remove-recipient {
    background: none;
    border: none;
    color: #ef4444;
    cursor: pointer;
    font-size: 20px;
    width: 24px;
    height: 24px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 4px;
    transition: background 0.2s;
}

.remove-recipient:hover {
    background: rgba(239, 68, 68, 0.1);
}

.recipient-db-section {
    margin-top: 14px;
    border: 1px solid #e5e7eb;
    border-radius: 10px;
    padding: 10px;
    background: #f9fafb;
}

.recipient-db-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 6px;
}

.recipient-db-title {
    font-size: 12px;
    font-weight: 700;
    color: #1f2937;
}

.btn-select-db {
    border: 1px solid #cbd5e1;
    background: #ffffff;
    color: #334155;
    border-radius: 999px;
    padding: 4px 10px;
    font-size: 11px;
    cursor: pointer;
}

.btn-select-db:hover {
    background: #eef2ff;
    border-color: #818cf8;
    color: #3730a3;
}

.recipient-db-count {
    font-size: 11px;
    color: #64748b;
    margin-bottom: 8px;
}

.recipient-db-search {
    margin-bottom: 10px;
}

.recipient-db-search-input {
    width: 100%;
    border: 1px solid #d1d5db;
    border-radius: 8px;
    padding: 8px 10px;
    font-size: 12px;
    color: #1f2937;
    background: #fff;
}

.recipient-db-search-input:focus {
    outline: none;
    border-color: #a5b4fc;
    box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.12);
}

.recipient-db-list {
    max-height: 220px;
    overflow-y: auto;
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.recipient-db-item {
    display: flex;
    align-items: flex-start;
    gap: 8px;
    background: #ffffff;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    padding: 8px 9px;
    cursor: pointer;
}

.recipient-db-item:hover {
    border-color: #c7d2fe;
    background: #eef2ff;
}

.recipient-db-checkbox {
    margin-top: 2px;
}

.recipient-db-info {
    display: flex;
    flex-direction: column;
    gap: 2px;
}

.recipient-db-name {
    font-size: 12px;
    font-weight: 600;
    color: #111827;
}

.recipient-db-phone {
    font-size: 11px;
    color: #6b7280;
}

.recipient-db-empty {
    font-size: 12px;
    color: #6b7280;
}

/* Message Card */
.section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

.template-section {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 15px;
}

.template-label {
    font-size: 14px;
    color: #666;
    font-weight: 500;
    min-width: 70px;
}

.template-selector {
    flex: 1;
}

.template-select {
    width: 100%;
    height: 40px;
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    padding: 0 12px;
    font-size: 14px;
    outline: none;
    cursor: pointer;
    background: white;
}

.template-select:focus {
    border-color: #3b82f6;
}

.template-preview-box {
    min-height: 110px;
    border: 1px solid #E5E7EB;
    border-radius: 10px;
    background: #F9FAFB;
    padding: 10px 12px;
    font-size: 12px;
    color: #374151;
    white-space: pre-wrap;
}

.recipient-message-note {
    font-size: 12px;
    color: #6B7280;
    margin-bottom: 8px;
}

.recipient-message-matrix {
    max-height: 240px;
    overflow-y: auto;
    border: 1px solid #E5E7EB;
    border-radius: 10px;
    background: #FFFFFF;
    padding: 10px;
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.message-override-item {
    border: 1px solid #E5E7EB;
    border-radius: 10px;
    background: #FAFAFA;
    padding: 10px;
}

.message-override-item.mode-template {
    border-color: #A7F3D0;
    background: #F0FDF4;
}

.message-override-item.mode-manual {
    border-color: #C7D2FE;
    background: #EEF2FF;
}

.message-override-item.mode-global {
    border-color: #FDE68A;
    background: #FFFBEB;
}

.message-override-head {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 8px;
    margin-bottom: 8px;
}

.message-override-actions {
    display: flex;
    align-items: center;
    gap: 6px;
}

.message-override-title {
    font-size: 12px;
    font-weight: 700;
    color: #1F2937;
}

.message-override-badge {
    font-size: 10px;
    font-weight: 700;
    border-radius: 999px;
    padding: 3px 8px;
    letter-spacing: .2px;
}

.message-override-badge.mode-template {
    background: #D1FAE5;
    color: #065F46;
}

.message-override-badge.mode-manual {
    background: #E0E7FF;
    color: #3730A3;
}

.message-override-badge.mode-global {
    background: #FEF3C7;
    color: #92400E;
}

.message-override-remove {
    width: 22px;
    height: 22px;
    border: 1px solid #FCA5A5;
    border-radius: 999px;
    background: #FEF2F2;
    color: #B91C1C;
    font-size: 14px;
    line-height: 1;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    transition: .2s;
}

.message-override-remove:hover {
    background: #FEE2E2;
    border-color: #F87171;
}

.message-override-file-wrap {
    margin-top: 8px;
}

.message-override-file-label {
    font-size: 11px;
    font-weight: 600;
    color: #374151;
    margin-bottom: 6px;
}

.message-override-file-input {
    font-size: 11px;
    width: 100%;
}

.message-override-file-list {
    margin-top: 6px;
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.message-override-file-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 6px;
    padding: 4px 8px;
    border: 1px solid #E5E7EB;
    border-radius: 6px;
    background: #FFFFFF;
    font-size: 11px;
    color: #374151;
}

.message-override-file-remove {
    border: 1px solid #FCA5A5;
    background: #FEF2F2;
    color: #B91C1C;
    border-radius: 999px;
    width: 18px;
    height: 18px;
    font-size: 11px;
    line-height: 1;
    cursor: pointer;
}

.message-override-file-empty {
    font-size: 11px;
    color: #9CA3AF;
}

.message-override-mode {
    display: flex;
    gap: 12px;
    font-size: 12px;
    color: #374151;
    margin-bottom: 8px;
}

.message-override-mode label {
    display: flex;
    align-items: center;
    gap: 4px;
    margin: 0;
}

.message-override-text {
    width: 100%;
    min-height: 72px;
    border: 1px solid #D1D5DB;
    border-radius: 8px;
    padding: 8px 10px;
    font-size: 12px;
    outline: none;
    resize: vertical;
}

.message-override-text:disabled {
    background: #F3F4F6;
    color: #9CA3AF;
}

.message-override-hint {
    font-size: 11px;
    color: #6B7280;
    margin-top: 6px;
}

.global-default-toggle {
    display: flex;
    align-items: flex-start;
    gap: 8px;
    margin-top: 10px;
    font-size: 12px;
    color: #4B5563;
}

.selected-templates {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    margin-bottom: 15px;
    padding: 12px;
    background: #f8f9fa;
    border-radius: 8px;
    min-height: 50px;
}

.template-tag {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 6px 12px;
    background: white;
    border: 1px solid #e0e0e0;
    border-radius: 20px;
    font-size: 13px;
    color: #1D1D41;
}

.template-tag span {
    font-size: 12px;
}

.remove-tag {
    background: none;
    border: none;
    color: #999;
    cursor: pointer;
    font-size: 18px;
    line-height: 1;
    padding: 0;
    width: 16px;
    height: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    transition: all 0.2s;
}

.remove-tag:hover {
    background: #f0f0f0;
    color: #ef4444;
}

.message-editor {
    margin-bottom: 15px;
}

.message-textarea {
    width: 100%;
    height: 180px;
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    padding: 15px;
    font-size: 14px;
    resize: none;
    outline: none;
    font-family: inherit;
    transition: border-color 0.2s;
}

.message-textarea:focus {
    border-color: #3b82f6;
}

.message-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

.attachment-buttons {
    display: flex;
    gap: 10px;
}

.attach-btn {
    display: flex;
    align-items: center;
    gap: 6px;
    padding: 8px 14px;
    background: #f8f9fa;
    border: 1px solid #e0e0e0;
    border-radius: 6px;
    cursor: pointer;
    transition: all 0.2s;
    font-size: 13px;
    color: #666;
}

.attach-btn:hover {
    background: #e9ecef;
    border-color: #3b82f6;
    color: #3b82f6;
}

.attach-btn svg {
    stroke: currentColor;
}

.char-count {
    font-size: 12px;
    color: #999;
}

.send-button {
    width: 100%;
    height: 45px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border: none;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    transition: transform 0.2s, box-shadow 0.2s;
}

.send-button:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
}

.send-button:disabled {
    opacity: 0.6;
    cursor: not-allowed;
    transform: none;
}

/* Activity Card */
.activity-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

.search-small {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 8px 12px;
    background: #f8f9fa;
    border: 1px solid #e0e0e0;
    border-radius: 6px;
    width: 255px;
}

.search-input-small {
    flex: 1;
    border: none;
    background: transparent;
    outline: none;
    font-size: 13px;
}

.activity-table {
    font-size: 12px;
}

.activity-table-header {
    display: grid;
    grid-template-columns: 100px 1fr 100px 1fr 130px 100px;
    gap: 12px;
    padding: 12px;
    background: #f8f9fa;
    border-radius: 6px;
    font-weight: 600;
    color: #666;
    margin-bottom: 8px;
    font-size: 12px;
}

.activity-table-body {
    max-height: 300px;
    overflow-y: auto;
}

.activity-empty {
    text-align: center;
    color: #999;
    padding: 60px 20px;
    font-size: 14px;
}

.activity-row {
    display: grid;
    grid-template-columns: 100px 1fr 100px 1fr 130px 100px;
    gap: 12px;
    padding: 12px;
    border-bottom: 1px solid #f0f0f0;
    align-items: center;
    font-size: 12px;
}

.activity-row:hover {
    background: #f8f9fa;
}

.waktu-date {
    font-size: 11px;
    color: #1D1D41;
    margin-bottom: 2px;
    font-weight: 500;
}

.waktu-time {
    font-size: 10px;
    color: #999;
}

.siswa-name {
    font-size: 12px;
    color: #1D1D41;
    font-weight: 500;
    line-height: 1.3;
}

.wali-name {
    font-size: 12px;
    color: #666;
    line-height: 1.3;
    word-break: break-word;
}

.col-kelas,
.col-wa {
    font-size: 11px;
    color: #666;
    line-height: 1.3;
    word-break: break-word;
}

.status-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 4px;
    padding: 4px 8px;
    border-radius: 10px;
    font-size: 10px;
    font-weight: 600;
    white-space: nowrap;
}

.status-badge.success {
    background: rgba(34, 197, 94, 0.1);
    color: #22c55e;
}

.status-badge.failed {
    background: rgba(239, 68, 68, 0.1);
    color: #ef4444;
}

.status-badge.pending {
    background: rgba(251, 191, 36, 0.1);
    color: #fbbf24;
}

.status-badge::before {
    content: '';
    width: 5px;
    height: 5px;
    border-radius: 50%;
    background: currentColor;
}

/* Tips Section */
.tips-section {
    background: white;
    border-radius: 12px;
    padding: 20px 25px;
    display: flex;
    gap: 15px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    max-width: calc(100% - 0px);
}

.tips-icon {
    font-size: 28px;
    flex-shrink: 0;
}

.tips-content-wrapper {
    flex: 1;
}

.tips-title {
    font-size: 16px;
    font-weight: 600;
    color: #1D1D41;
    margin-bottom: 10px;
}

.tips-list {
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.tip-item {
    font-size: 13px;
    color: #666;
    padding-left: 12px;
    position: relative;
}

.tip-item::before {
    content: '•';
    position: absolute;
    left: 0;
    color: #667eea;
    font-weight: bold;
}

/* Responsive */
@media (max-width: 1400px) {
    .top-row {
        grid-template-columns: 1fr;
    }

    .stats-container {
        grid-template-columns: repeat(2, 1fr);
    }

    .activity-table-header,
    .activity-row {
        grid-template-columns: 90px 1fr 90px 1fr 120px 90px;
        font-size: 11px;
    }
}

@media (max-width: 768px) {
    .whatsapp-blasting-container {
        padding: 15px;
    }

    .stats-container {
        grid-template-columns: 1fr;
    }

    .app-title {
        font-size: 20px;
    }

    .activity-table-header {
        display: none;
    }

    .activity-row {
        grid-template-columns: 1fr;
        gap: 8px;
        padding: 15px;
        background: #f8f9fa;
        border-radius: 8px;
        margin-bottom: 10px;
    }

    .col-waktu,
    .col-siswa,
    .col-kelas,
    .col-wali,
    .col-wa,
    .col-status {
        display: flex;
        justify-content: space-between;
    }

    .col-waktu::before { content: 'Detail Waktu: '; font-weight: 600; }
    .col-siswa::before { content: 'Nama Siswa: '; font-weight: 600; }
    .col-kelas::before { content: 'Kelas: '; font-weight: 600; }
    .col-wali::before { content: 'Nama Wali: '; font-weight: 600; }
    .col-wa::before { content: 'Nomor WhatsApp: '; font-weight: 600; }
    .col-status::before { content: 'Status: '; font-weight: 600; }
}

/* Scrollbar Styling */
.recipient-list::-webkit-scrollbar,
.activity-table-body::-webkit-scrollbar {
    width: 6px;
}

.recipient-list::-webkit-scrollbar-track,
.activity-table-body::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 10px;
}

.recipient-list::-webkit-scrollbar-thumb,
.activity-table-body::-webkit-scrollbar-thumb {
    background: #c1c1c1;
    border-radius: 10px;
}

.recipient-list::-webkit-scrollbar-thumb:hover,
.activity-table-body::-webkit-scrollbar-thumb:hover {
    background: #a8a8a8;
}
</style>

<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const phoneInput = document.getElementById('phoneInput');
        const addPhoneBtn = document.getElementById('addPhoneBtn');
        const recipientList = document.getElementById('recipientList');
        const messageTextarea = document.getElementById('messageTextarea');
        const charCount = document.getElementById('charCount');
        const sendButton = document.getElementById('sendButton');
        const targetsField = document.getElementById('targetsField');
        const attachmentContainer = document.getElementById('attachmentContainer');
        const attachFile = document.getElementById('attachFile');
        const activityLog = document.getElementById('activityLog');
        const searchInput = document.getElementById('searchInput');
        const activityApiUrl = @json(route('admin.blast.activity'));
        const activityChannel = 'whatsapp';
        const excelImport = document.getElementById('excelImport');
        const excelFileInput = document.getElementById('excelFileInput');
        const excelImportInfo = document.getElementById('excelImportInfo');
        
        // Form inputs
        const studentName = document.getElementById('studentName');
        const studentClass = document.getElementById('studentClass');
        const parentName = document.getElementById('parentName');
        const templateSelect = document.getElementById('templateSelect');
        const selectedTemplatesContainer = document.getElementById('selectedTemplates');
        const dbTemplateSelect = document.getElementById('dbTemplateSelect');
        const dbTemplatePreview = document.getElementById('dbTemplatePreview');
        const selectAllRecipientsBtn = document.getElementById('selectAllRecipientsBtn');
        const recipientDbSearchInput = document.getElementById('recipientDbSearchInput');
        const recipientDbList = document.querySelector('.recipient-db-list');
        const recipientDbItems = Array.from(document.querySelectorAll('.recipient-db-item'));
        const recipientDbCheckboxes = document.querySelectorAll('.recipient-db-checkbox');
        const recipientMessageMatrix = document.getElementById('recipientMessageMatrix');
        const messageOverridesField = document.getElementById('messageOverridesField');

        // Stats
        const statTotal = document.getElementById('statTotal');
        const statSent = document.getElementById('statSent');
        const statFailed = document.getElementById('statFailed');
        const statPending = document.getElementById('statPending');

        let selectedTemplates = [];
        let activities = @json($activityLogs ?? []);
        let isRefreshingActivities = false;
        let recipientNumbers = [];
        const overrideState = {};
        const attachmentBufferByKey = {};

        function escapeHtml(value) {
            return String(value)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#39;');
        }

        function normalizePhone(rawPhone) {
            let phone = String(rawPhone || '').trim();
            if (!phone) {
                return null;
            }

            phone = phone.replace(/\D+/g, '');
            if (!phone) {
                return null;
            }

            if (phone.startsWith('0')) {
                phone = '62' + phone.substring(1);
            } else if (phone.startsWith('8')) {
                phone = '62' + phone;
            }

            if (!phone.startsWith('62')) {
                return null;
            }

            if (phone.length < 10 || phone.length > 15) {
                return null;
            }

            return phone;
        }

        function keyToToken(key) {
            const base64 = btoa(unescape(encodeURIComponent(key)));
            return base64
                .replace(/=+$/g, '')
                .replace(/\+/g, '-')
                .replace(/\//g, '_');
        }

        function ensureAttachmentBuffer(key) {
            if (!attachmentBufferByKey[key]) {
                attachmentBufferByKey[key] = new DataTransfer();
            }

            return attachmentBufferByKey[key];
        }

        function removeAttachmentFileByIndex(key, index) {
            const currentBuffer = ensureAttachmentBuffer(key);
            const nextBuffer = new DataTransfer();

            Array.from(currentBuffer.files).forEach((file, i) => {
                if (i !== index) {
                    nextBuffer.items.add(file);
                }
            });

            attachmentBufferByKey[key] = nextBuffer;
        }

        function renderAttachmentPreview(item, key) {
            const input = item.querySelector('.message-override-file-input');
            const list = item.querySelector('.message-override-file-list');
            const buffer = ensureAttachmentBuffer(key);

            if (!input || !list) {
                return;
            }

            input.files = buffer.files;

            if (buffer.files.length === 0) {
                list.innerHTML = '<div class="message-override-file-empty">Tidak ada file khusus</div>';
                return;
            }

            list.innerHTML = Array.from(buffer.files).map((file, index) => `
                <div class="message-override-file-item">
                    <span>${escapeHtml(file.name)}</span>
                    <button
                        type="button"
                        class="message-override-file-remove"
                        data-index="${index}"
                        title="Hapus file"
                    >&times;</button>
                </div>
            `).join('');
        }

        function removeManualRecipientByNumber(phone) {
            const normalized = normalizePhone(phone);
            if (!normalized) {
                return;
            }

            recipientNumbers = recipientNumbers.filter(
                (item) => item !== normalized
            );

            delete overrideState['manual:' + normalized];
            delete attachmentBufferByKey['manual:' + normalized];

            recipientList.querySelectorAll('.recipient-item').forEach((item) => {
                if ((item.getAttribute('data-phone') || '') === normalized) {
                    item.remove();
                }
            });
        }

        function removeDbRecipientById(recipientId) {
            recipientDbCheckboxes.forEach((cb) => {
                if (cb.value === recipientId) {
                    cb.checked = false;
                }
            });

            delete overrideState['db:' + recipientId];
            delete attachmentBufferByKey['db:' + recipientId];
        }

        function getSelectedRecipients() {
            const recipients = [];

            recipientDbCheckboxes.forEach((cb) => {
                if (!cb.checked) {
                    return;
                }

                const key = 'db:' + cb.value;
                const label = cb.closest('.recipient-db-item')
                    ?.querySelector('.recipient-db-name')
                    ?.textContent?.trim() || cb.value;

                recipients.push({
                    key: key,
                    label: 'DB - ' + label,
                    kind: 'db',
                    ref: cb.value,
                });
            });

            recipientNumbers.forEach((phone) => {
                recipients.push({
                    key: 'manual:' + phone,
                    label: 'Manual - ' + phone,
                    kind: 'manual',
                    ref: phone,
                });
            });

            return recipients;
        }

        function getActiveMessageOverrides() {
            const overrides = {};

            getSelectedRecipients().forEach(({ key }) => {
                const state = overrideState[key] || {};
                const mode = (state.mode || 'manual').toLowerCase();
                const message = (state.message || '').trim();

                if (mode === 'template') {
                    overrides[key] = { mode: 'template', message: '' };
                    return;
                }

                if (mode === 'global') {
                    overrides[key] = { mode: 'global', message: '' };
                    return;
                }

                if (message !== '') {
                    overrides[key] = { mode: 'manual', message: message };
                }
            });

            return overrides;
        }

        function syncMessageOverridesField() {
            if (!messageOverridesField) {
                return {};
            }

            const overrides = getActiveMessageOverrides();
            messageOverridesField.value = JSON.stringify(overrides);
            return overrides;
        }

        function renderRecipientMessageMatrix() {
            if (!recipientMessageMatrix) {
                return;
            }

            const recipients = getSelectedRecipients();

            if (recipients.length === 0) {
                recipientMessageMatrix.innerHTML = `
                    <div class="recipient-db-empty">
                        Pilih recipient DB atau tambah nomor WhatsApp manual untuk mengatur pesan khusus.
                    </div>
                `;
                syncMessageOverridesField();
                return;
            }

            recipientMessageMatrix.innerHTML = recipients.map(({ key, label, kind, ref }) => {
                const state = overrideState[key] || {};
                const mode = (state.mode || 'manual').toLowerCase();
                const manualChecked = mode === 'manual';
                const templateChecked = mode === 'template';
                const globalChecked = mode === 'global';
                const effectiveMode = templateChecked
                    ? 'template'
                    : (globalChecked ? 'global' : 'manual');
                const message = escapeHtml(state.message || '');
                const keyToken = keyToToken(key);
                const radioGroup = 'override_mode_' + key.replace(/[^a-zA-Z0-9_-]/g, '_');
                const modeClass = 'mode-' + effectiveMode;
                const badgeText = effectiveMode === 'template'
                    ? 'Template'
                    : (effectiveMode === 'global' ? 'Global' : 'Manual');
                const hintText = effectiveMode === 'template'
                    ? 'Menggunakan template blast DB untuk penerima ini.'
                    : (effectiveMode === 'global'
                        ? 'Menggunakan isi pesan WA global untuk penerima ini.'
                        : 'Gunakan isi manual khusus untuk penerima ini.');
                const textPlaceholder = effectiveMode === 'template'
                    ? 'Mode template aktif untuk penerima ini.'
                    : (effectiveMode === 'global'
                        ? 'Mode global aktif untuk penerima ini.'
                        : 'Isi pesan khusus untuk penerima ini...');

                return `
                    <div class="message-override-item ${modeClass}" data-key="${escapeHtml(key)}" data-kind="${escapeHtml(kind)}" data-ref="${escapeHtml(ref)}">
                        <div class="message-override-head">
                            <div class="message-override-title">${escapeHtml(label)}</div>
                            <div class="message-override-actions">
                                <span class="message-override-badge ${modeClass}">${badgeText}</span>
                                <button type="button" class="message-override-remove" title="Hapus penerima ini">&times;</button>
                            </div>
                        </div>
                        <div class="message-override-mode">
                            <label>
                                <input type="radio" name="${radioGroup}" class="message-override-mode-input" data-mode="manual" ${manualChecked ? 'checked' : ''}>
                                Manual
                            </label>
                            <label>
                                <input type="radio" name="${radioGroup}" class="message-override-mode-input" data-mode="template" ${templateChecked ? 'checked' : ''}>
                                Template
                            </label>
                            <label>
                                <input type="radio" name="${radioGroup}" class="message-override-mode-input" data-mode="global" ${globalChecked ? 'checked' : ''}>
                                Global
                            </label>
                        </div>
                        <textarea
                            class="message-override-text"
                            placeholder="${textPlaceholder}"
                            ${(templateChecked || globalChecked) ? 'disabled' : ''}
                        >${message}</textarea>
                        <div class="message-override-file-wrap">
                            <div class="message-override-file-label">File Khusus Penerima (opsional)</div>
                            <input
                                type="hidden"
                                name="attachment_override_keys[${keyToken}]"
                                value="${escapeHtml(key)}"
                            >
                            <input
                                type="file"
                                class="message-override-file-input"
                                name="attachment_overrides[${keyToken}][]"
                                multiple
                            >
                            <div class="message-override-file-list"></div>
                        </div>
                        <div class="message-override-hint">${hintText}</div>
                    </div>
                `;
            }).join('');

            recipientMessageMatrix.querySelectorAll('.message-override-item').forEach((item) => {
                const key = item.getAttribute('data-key');
                if (!key) {
                    return;
                }

                renderAttachmentPreview(item, key);
            });

            syncMessageOverridesField();
        }

        function updateDbTemplatePreview() {
            if (!dbTemplateSelect || !dbTemplatePreview) {
                return;
            }

            const selectedOption = dbTemplateSelect.options[dbTemplateSelect.selectedIndex];
            const content = selectedOption ? selectedOption.getAttribute('data-content') : '';
            const templateName = selectedOption && selectedOption.value
                ? selectedOption.textContent.trim()
                : '';

            dbTemplatePreview.textContent = content && content.trim().length > 0
                ? `Template: ${templateName}\n\n${content}`
                : 'Pilih template untuk melihat preview.';
        }

        // Add recipient function
        function addRecipient(phoneNumber = null, showAlert = true) {
            const source = phoneNumber === null ? phoneInput.value : phoneNumber;
            const phone = normalizePhone(source);

            if (!phone) {
                if (showAlert) alert('Format nomor telepon tidak valid! Gunakan format: 6281234567890');
                return false;
            }

            if (recipientNumbers.includes(phone)) {
                if (showAlert) alert('Nomor ini sudah ditambahkan!');
                return false;
            }

            const statusElement = recipientList.querySelector('.recipient-status');
            if (statusElement) {
                statusElement.remove();
            }

            recipientNumbers.push(phone);

            const recipientItem = document.createElement('div');
            recipientItem.className = 'recipient-item';
            recipientItem.setAttribute('data-phone', phone);
            recipientItem.innerHTML = `
                <span class="recipient-number">${escapeHtml(phone)}</span>
                <button type="button" class="remove-recipient" title="Hapus">&times;</button>
            `;

            recipientList.appendChild(recipientItem);
            phoneInput.value = '';

            updateTargetsField();
            renderRecipientMessageMatrix();

            const removeBtn = recipientItem.querySelector('.remove-recipient');
            removeBtn.addEventListener('click', function() {
                removeManualRecipientByNumber(phone);
                updateTargetsField();
                renderRecipientMessageMatrix();

                if (recipientList.querySelectorAll('.recipient-item').length === 0) {
                    const newStatus = document.createElement('div');
                    newStatus.className = 'recipient-status';
                    newStatus.textContent = 'Belum ada penerima';
                    recipientList.appendChild(newStatus);
                }
            });

            return true;
        }

        if (addPhoneBtn) {
            addPhoneBtn.addEventListener('click', function() {
                addRecipient(null, true);
            });
        }

        if (phoneInput) {
            phoneInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    addRecipient(null, true);
                }
            });
        }

        // Excel Import Functionality
        if (excelImport) {
            excelImport.addEventListener('click', function() {
                excelFileInput.click();
            });
        }

        if (excelFileInput) {
            excelFileInput.addEventListener('change', handleExcelImport);
        }

        function handleExcelImport(event) {
            const file = event.target.files[0];
            if (!file) return;

            // Validasi file
            const validExtensions = ['.xlsx', '.xls', '.csv'];
            const fileExtension = '.' + file.name.split('.').pop().toLowerCase();
            
            if (!validExtensions.includes(fileExtension)) {
                alert('Format file tidak didukung! Silakan upload file Excel (.xlsx, .xls) atau CSV.');
                excelFileInput.value = '';
                return;
            }

            // Show loading indicator
            excelImport.innerHTML = `
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
                    <circle cx="12" cy="12" r="10" stroke="#1D1D41" stroke-width="2" stroke-linecap="round"/>
                    <path d="M12 2C6.47715 2 2 6.47715 2 12C2 17.5228 6.47715 22 12 22C17.5228 22 22 17.5228 22 12C22 9.27455 20.9097 6.80375 19.1414 5" stroke="#1D1D41" stroke-width="2" stroke-linecap="round"/>
                </svg>
                <span>Memproses...</span>
            `;

            const reader = new FileReader();
            
            reader.onload = function(e) {
                try {
                    const data = new Uint8Array(e.target.result);
                    const workbook = XLSX.read(data, { type: 'array' });
                    
                    // Ambil sheet pertama
                    const firstSheetName = workbook.SheetNames[0];
                    const worksheet = workbook.Sheets[firstSheetName];
                    
                    // Konversi ke JSON
                    const jsonData = XLSX.utils.sheet_to_json(worksheet, { header: 1 });
                    
                    // Validasi header
                    if (jsonData.length === 0) {
                        alert('File Excel kosong!');
                        resetExcelImport();
                        return;
                    }

                    // Cari kolom nomor WhatsApp
                    const headers = jsonData[0].map(h => h ? h.toString().toLowerCase() : '');
                    const whatsappIndex = headers.findIndex(h => 
                        h.includes('whatsapp') || h.includes('wa') || h.includes('nomor') || h.includes('no') || h.includes('phone') || h.includes('telepon')
                    );

                    if (whatsappIndex === -1) {
                        alert('Tidak ditemukan kolom "Nomor WhatsApp" dalam file Excel! Pastikan file memiliki kolom dengan nama: WhatsApp, WA, Nomor, No, Phone, atau Telepon.');
                        resetExcelImport();
                        return;
                    }

                    // Proses data mulai dari baris kedua (indeks 1)
                    let importedCount = 0;
                    let duplicateCount = 0;
                    let invalidCount = 0;

                    for (let i = 1; i < jsonData.length; i++) {
                        const row = jsonData[i];
                        if (!row[whatsappIndex]) continue;

                        const phone = normalizePhone(row[whatsappIndex].toString().trim());
                        if (!phone) {
                            invalidCount++;
                            continue;
                        }

                        if (recipientNumbers.includes(phone)) {
                            duplicateCount++;
                            continue;
                        }

                        if (addRecipient(phone, false)) {
                            importedCount++;
                        }
                    }

                    // Update hidden field
                    updateTargetsField();
                    renderRecipientMessageMatrix();

                    // Reset file input
                    excelFileInput.value = '';

                    // Tampilkan hasil import
                    let resultMessage = `Berhasil mengimpor ${importedCount} nomor WhatsApp.`;
                    
                    if (duplicateCount > 0) {
                        resultMessage += `\n${duplicateCount} nomor duplikat dilewati.`;
                    }
                    
                    if (invalidCount > 0) {
                        resultMessage += `\n${invalidCount} nomor tidak valid dilewati.`;
                    }
                    
                    alert(resultMessage);

                    // Show import info
                    excelImportInfo.innerHTML = `
                        <div><strong>Format Excel:</strong> Harus memiliki kolom: <strong>Nomor WhatsApp</strong></div>
                        <div><strong>Hasil Import:</strong> ${importedCount} nomor berhasil ditambahkan</div>
                    `;
                    excelImportInfo.style.display = 'block';

                } catch (error) {
                    console.error('Error reading Excel file:', error);
                    alert('Terjadi kesalahan saat membaca file Excel. Pastikan format file benar.');
                } finally {
                    resetExcelImport();
                }
            };

            reader.onerror = function() {
                alert('Gagal membaca file!');
                resetExcelImport();
            };

            reader.readAsArrayBuffer(file);
        }

        function updateTargetsField() {
            targetsField.value = recipientNumbers.join(', ');
        }

        function resetExcelImport() {
            excelImport.innerHTML = `
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
                    <path d="M14 2H6C5.46957 2 4.96086 2.21071 4.58579 2.58579C4.21071 2.96086 4 3.46957 4 4V20C4 20.5304 4.21071 21.0391 4.58579 21.4142C4.96086 21.7893 5.46957 22 6 22H18C18.5304 22 19.0391 21.7893 19.4142 21.4142C19.7893 21.0391 20 20.5304 20 20V8L14 2Z" stroke="#1D1D41" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M14 2V8H20" stroke="#1D1D41" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                <span>Impor Excel</span>
            `;
        }

        // Template functionality
        const templates = {
            'reminder': {
                name: 'Reminder Tagihan Sekolah',
                content: 'Yth. Bapak/Ibu {nama wali},\n\nKami ingin mengingatkan bahwa tagihan sekolah untuk {nama siswa} kelas {kelas} sebesar {tagihan} akan jatuh tempo pada {jatuh tempo}.\n\nMohon segera melakukan pembayaran. Terima kasih.\n\nSalam,\nSOY YPIK PAM JAYA'
            },
            'payment': {
                name: 'Informasi Pembayaran Sekolah',
                content: 'Kepada Yth. Bapak/Ibu {nama wali},\n\nBerikut informasi pembayaran untuk {nama siswa} kelas {kelas}:\n- Tagihan: {tagihan}\n- Jatuh Tempo: {jatuh tempo}\n\nPembayaran dapat dilakukan melalui:\n- Transfer Bank\n- Pembayaran Langsung ke Sekolah\n\nTerima kasih.'
            },
            'notification': {
                name: 'Pemberitahuan Tunggakan',
                content: 'Kepada Yth. Bapak/Ibu {nama wali},\n\nDengan hormat, kami informasikan bahwa terdapat tunggakan pembayaran untuk {nama siswa} kelas {kelas} sebesar {tagihan}.\n\nBatas pembayaran telah melewati tanggal {jatuh tempo}. Mohon segera melakukan pembayaran.\n\nHubungi kami jika ada pertanyaan.\n\nHormat kami,\nSOY YPIK PAM JAYA'
            }
        };

        function renderSelectedTemplates() {
            selectedTemplatesContainer.innerHTML = '';
            
            if (selectedTemplates.length === 0) {
                selectedTemplatesContainer.style.display = 'none';
                return;
            }

            selectedTemplatesContainer.style.display = 'flex';
            
            selectedTemplates.forEach(templateKey => {
                const template = templates[templateKey];
                const tagElement = document.createElement('div');
                tagElement.className = 'template-tag';
                tagElement.innerHTML = `
                    <span>${template.name}</span>
                    <button type="button" class="remove-tag" data-template="${templateKey}">&times;</button>
                `;
                selectedTemplatesContainer.appendChild(tagElement);

                const removeBtn = tagElement.querySelector('.remove-tag');
                removeBtn.addEventListener('click', function() {
                    const templateToRemove = this.getAttribute('data-template');
                    selectedTemplates = selectedTemplates.filter(t => t !== templateToRemove);
                    renderSelectedTemplates();
                });
            });
        }

        if (templateSelect) {
            templateSelect.addEventListener('change', function() {
                const selectedTemplate = this.value;
                
                if (selectedTemplate && templates[selectedTemplate]) {
                    if (!selectedTemplates.includes(selectedTemplate)) {
                        selectedTemplates.push(selectedTemplate);
                        renderSelectedTemplates();
                        
                        let content = templates[selectedTemplate].content;
                        
                        // Replace placeholders with form values
                        if (studentName.value) {
                            content = content.replace(/{nama siswa}/g, studentName.value);
                        }
                        if (studentClass.value) {
                            content = content.replace(/{kelas}/g, studentClass.value);
                        }
                        if (parentName.value) {
                            content = content.replace(/{nama wali}/g, parentName.value);
                        }
                        
                        messageTextarea.value = content;
                        updateCharCount();
                    }
                    
                    this.value = '';
                }
            });
        }

        if (selectAllRecipientsBtn && recipientDbCheckboxes.length > 0) {
            let allRecipientSelected = false;

            selectAllRecipientsBtn.addEventListener('click', function() {
                allRecipientSelected = !allRecipientSelected;
                recipientDbCheckboxes.forEach((cb) => {
                    cb.checked = allRecipientSelected;
                });

                selectAllRecipientsBtn.textContent = allRecipientSelected
                    ? 'Unselect All'
                    : 'Select All';

                renderRecipientMessageMatrix();
            });
        }

        recipientDbCheckboxes.forEach((cb) => {
            cb.addEventListener('change', function() {
                renderRecipientMessageMatrix();
            });
        });

        if (dbTemplateSelect) {
            dbTemplateSelect.addEventListener('change', updateDbTemplatePreview);
        }

        if (recipientMessageMatrix) {
            recipientMessageMatrix.addEventListener('click', function(event) {
                const fileRemoveBtn = event.target.closest('.message-override-file-remove');
                if (fileRemoveBtn) {
                    const item = fileRemoveBtn.closest('.message-override-item');
                    const key = item ? item.getAttribute('data-key') : null;
                    const index = Number(fileRemoveBtn.getAttribute('data-index'));

                    if (item && key && Number.isInteger(index)) {
                        removeAttachmentFileByIndex(key, index);
                        renderAttachmentPreview(item, key);
                    }

                    return;
                }

                const removeBtn = event.target.closest('.message-override-remove');
                if (!removeBtn) {
                    return;
                }

                const item = removeBtn.closest('.message-override-item');
                if (!item) {
                    return;
                }

                const key = item.getAttribute('data-key');
                const kind = item.getAttribute('data-kind');
                const ref = item.getAttribute('data-ref');

                if (kind === 'db' && ref) {
                    removeDbRecipientById(ref);
                }

                if (kind === 'manual' && ref) {
                    removeManualRecipientByNumber(ref);
                    updateTargetsField();

                    if (recipientList.querySelectorAll('.recipient-item').length === 0) {
                        const newStatus = document.createElement('div');
                        newStatus.className = 'recipient-status';
                        newStatus.textContent = 'Belum ada penerima';
                        recipientList.appendChild(newStatus);
                    }
                }

                if (key) {
                    delete overrideState[key];
                    delete attachmentBufferByKey[key];
                }

                renderRecipientMessageMatrix();
            });

            recipientMessageMatrix.addEventListener('change', function(event) {
                const item = event.target.closest('.message-override-item');
                if (!item) {
                    return;
                }

                const key = item.getAttribute('data-key');
                if (!key) {
                    return;
                }

                if (!overrideState[key]) {
                    overrideState[key] = { mode: 'manual', message: '' };
                }

                const fileInput = event.target.closest('.message-override-file-input');
                if (fileInput) {
                    const buffer = ensureAttachmentBuffer(key);
                    Array.from(fileInput.files || []).forEach((file) => {
                        buffer.items.add(file);
                    });

                    renderAttachmentPreview(item, key);
                    return;
                }

                const modeInput = event.target.closest('.message-override-mode-input');
                if (modeInput) {
                    overrideState[key].mode = modeInput.getAttribute('data-mode') || 'manual';

                    const textarea = item.querySelector('.message-override-text');
                    const mode = overrideState[key].mode;
                    const isTemplate = mode === 'template';
                    const isGlobal = mode === 'global';

                    item.classList.toggle('mode-template', isTemplate);
                    item.classList.toggle('mode-manual', mode === 'manual');
                    item.classList.toggle('mode-global', isGlobal);

                    const badge = item.querySelector('.message-override-badge');
                    if (badge) {
                        badge.classList.toggle('mode-template', isTemplate);
                        badge.classList.toggle('mode-manual', mode === 'manual');
                        badge.classList.toggle('mode-global', isGlobal);
                        badge.textContent = isTemplate
                            ? 'Template'
                            : (isGlobal ? 'Global' : 'Manual');
                    }

                    const hint = item.querySelector('.message-override-hint');
                    if (hint) {
                        hint.textContent = isTemplate
                            ? 'Menggunakan template blast DB untuk penerima ini.'
                            : (isGlobal
                                ? 'Menggunakan isi pesan WA global untuk penerima ini.'
                                : 'Gunakan isi manual khusus untuk penerima ini.');
                    }

                    if (textarea) {
                        textarea.disabled = isTemplate || isGlobal;
                        textarea.placeholder = isTemplate
                            ? 'Mode template aktif untuk penerima ini.'
                            : (isGlobal
                                ? 'Mode global aktif untuk penerima ini.'
                                : 'Isi pesan khusus untuk penerima ini...');
                    }
                }

                syncMessageOverridesField();
            });

            recipientMessageMatrix.addEventListener('input', function(event) {
                const textarea = event.target.closest('.message-override-text');
                if (!textarea) {
                    return;
                }

                const item = textarea.closest('.message-override-item');
                const key = item ? item.getAttribute('data-key') : null;
                if (!key) {
                    return;
                }

                if (!overrideState[key]) {
                    overrideState[key] = { mode: 'manual', message: '' };
                }

                overrideState[key].message = textarea.value || '';
                syncMessageOverridesField();
            });
        }

        // Update message when form inputs change
        [studentName, studentClass, parentName].forEach(input => {
            input.addEventListener('input', function() {
                if (messageTextarea.value) {
                    let content = messageTextarea.value;
                    
                    if (studentName.value) {
                        content = content.replace(/{nama siswa}/g, studentName.value);
                    }
                    if (studentClass.value) {
                        content = content.replace(/{kelas}/g, studentClass.value);
                    }
                    if (parentName.value) {
                        content = content.replace(/{nama wali}/g, parentName.value);
                    }
                    
                    messageTextarea.value = content;
                    updateCharCount();
                }
            });
        });

        // Character count
        function updateCharCount() {
            const charLength = messageTextarea.value.length;
            charCount.textContent = `${charLength} karakter`;
        }

        if (messageTextarea) {
            messageTextarea.addEventListener('input', updateCharCount);
            updateCharCount();
        }

        function filterRecipientDbList() {
            if (!recipientDbList || recipientDbItems.length === 0) {
                return;
            }

            const searchTerm = (recipientDbSearchInput?.value || '').trim().toLowerCase();
            let visibleCount = 0;

            recipientDbItems.forEach((item) => {
                const candidate = (item.textContent || '').toLowerCase();
                const isMatch = searchTerm === '' || candidate.includes(searchTerm);

                item.style.display = isMatch ? '' : 'none';
                if (isMatch) {
                    visibleCount += 1;
                }
            });

            let emptySearch = recipientDbList.querySelector('.recipient-db-empty-search');
            if (visibleCount === 0) {
                if (!emptySearch) {
                    emptySearch = document.createElement('div');
                    emptySearch.className = 'recipient-db-empty recipient-db-empty-search';
                    emptySearch.textContent = 'Tidak ada recipient sesuai pencarian.';
                    recipientDbList.appendChild(emptySearch);
                }
            } else if (emptySearch) {
                emptySearch.remove();
            }
        }

        if (recipientDbSearchInput) {
            recipientDbSearchInput.addEventListener('input', filterRecipientDbList);
        }

        // Update stats
        function updateStats() {
            const total = activities.length;
            const sent = activities.filter(a => a.status === 'success').length;
            const failed = activities.filter(a => a.status === 'failed').length;
            const pending = activities.filter(a => a.status === 'pending').length;

            if (statTotal) statTotal.textContent = total;
            if (statSent) statSent.textContent = sent;
            if (statFailed) statFailed.textContent = failed;
            if (statPending) statPending.textContent = pending;
        }

        // Render activities
        function renderActivities(filteredActivities = activities) {
            activityLog.innerHTML = '';

            if (filteredActivities.length === 0) {
                const emptyElement = document.createElement('div');
                emptyElement.className = 'activity-empty';
                emptyElement.textContent = activities.length === 0 ? 'Belum ada aktivitas' : 'Tidak ada hasil pencarian';
                activityLog.appendChild(emptyElement);
                return;
            }

            filteredActivities.forEach(activity => {
                const row = document.createElement('div');
                row.className = 'activity-row';
                
                const statusClass = activity.status === 'success' ? 'success' : 
                                  activity.status === 'failed' ? 'failed' : 'pending';
                const statusText = activity.status === 'success' ? 'Terkirim' : 
                                 activity.status === 'failed' ? 'Gagal' : 'Pending';
                
                row.innerHTML = `
                    <div class="col-waktu">
                        <div class="waktu-date">${activity.date}</div>
                        <div class="waktu-time">${activity.time}</div>
                    </div>
                    <div class="col-siswa">
                        <div class="siswa-name">${activity.studentName}</div>
                    </div>
                    <div class="col-kelas">${activity.studentClass}</div>
                    <div class="col-wali">
                        <div class="wali-name">${activity.parentName}</div>
                    </div>
                    <div class="col-wa">${activity.phone}</div>
                    <div class="col-status">
                        <span class="status-badge ${statusClass}">${statusText}</span>
                    </div>
                `;
                
                activityLog.appendChild(row);
            });
        }

        function renderActivitiesWithCurrentFilter() {
            const searchTerm = (searchInput?.value || '').trim().toLowerCase();

            if (searchTerm === '') {
                renderActivities();
                return;
            }

            const filtered = activities.filter((activity) => {
                return String(activity.studentName || '').toLowerCase().includes(searchTerm) ||
                    String(activity.parentName || '').toLowerCase().includes(searchTerm) ||
                    String(activity.phone || '').toLowerCase().includes(searchTerm) ||
                    String(activity.studentClass || '').toLowerCase().includes(searchTerm);
            });

            renderActivities(filtered);
        }

        async function refreshActivityLogs() {
            if (isRefreshingActivities) {
                return;
            }

            isRefreshingActivities = true;

            try {
                const response = await fetch(
                    `${activityApiUrl}?channel=${encodeURIComponent(activityChannel)}`,
                    {
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                    }
                );

                if (!response.ok) {
                    return;
                }

                const payload = await response.json();
                if (Array.isArray(payload.logs)) {
                    activities = payload.logs;
                }

                if (payload && typeof payload === 'object' && payload.stats) {
                    if (statTotal) statTotal.textContent = Number(payload.stats.total ?? 0);
                    if (statSent) statSent.textContent = Number(payload.stats.sent ?? 0);
                    if (statFailed) statFailed.textContent = Number(payload.stats.failed ?? 0);
                    if (statPending) statPending.textContent = Number(payload.stats.pending ?? 0);
                } else {
                    updateStats();
                }

                renderActivitiesWithCurrentFilter();
            } catch (error) {
                // Keep UI stable when polling fails.
            } finally {
                isRefreshingActivities = false;
            }
        }

        // Search functionality
        if (searchInput) {
            searchInput.addEventListener('input', function() {
                renderActivitiesWithCurrentFilter();
            });
        }

        // Form submission - KEEP THE ORIGINAL FORM SUBMISSION FROM CODE 1
        const whatsappBlastForm = document.getElementById('whatsappBlastForm');
        if (whatsappBlastForm) {
            whatsappBlastForm.addEventListener('submit', function(e) {
                const activeOverrides = syncMessageOverridesField();
                const selectedDbRecipients = Array.from(
                    document.querySelectorAll('.recipient-db-checkbox:checked')
                );
                const hasDbRecipients = selectedDbRecipients.length > 0;
                const hasManualTargets = recipientNumbers.length > 0;
                const hasDbTemplate = dbTemplateSelect && dbTemplateSelect.value.trim() !== '';
                const hasGlobalMessage = messageTextarea.value.trim() !== '';
                const overrideValues = Object.values(activeOverrides);
                const hasPerRecipientManual = overrideValues.some(
                    (override) => override.mode === 'manual' && (override.message || '').trim() !== ''
                );
                const hasPerRecipientTemplate = overrideValues.some(
                    (override) => override.mode === 'template'
                );
                const hasPerRecipientGlobal = overrideValues.some(
                    (override) => override.mode === 'global'
                );
                const hasPerRecipientContent = hasPerRecipientManual
                    || (hasPerRecipientTemplate && hasDbTemplate)
                    || (hasPerRecipientGlobal && hasGlobalMessage);

                if (hasPerRecipientTemplate && !hasDbTemplate) {
                    e.preventDefault();
                    alert('Pilih "Template Blast DB" jika ada penerima yang menggunakan mode template.');
                    if (dbTemplateSelect) {
                        dbTemplateSelect.focus();
                    }
                    return;
                }

                if (hasPerRecipientGlobal && !hasGlobalMessage) {
                    e.preventDefault();
                    alert('Isi pesan global wajib diisi jika ada penerima dengan mode Global.');
                    messageTextarea.focus();
                    return;
                }

                if (!hasDbRecipients && !hasManualTargets) {
                    e.preventDefault();
                    alert('Pilih recipient dari DB atau tambahkan nomor WhatsApp manual terlebih dahulu!');
                    phoneInput.focus();
                    return;
                }

                if (!hasDbTemplate && !hasGlobalMessage && !hasPerRecipientContent) {
                    e.preventDefault();
                    alert('Masukkan isi pesan, pilih template, atau atur pesan khusus per penerima!');
                    messageTextarea.focus();
                    return;
                }

                const dbPhones = selectedDbRecipients
                    .map((cb) => normalizePhone(cb.getAttribute('data-phone') || ''))
                    .filter((phone) => phone !== null);
                const allTargetPhones = Array.from(new Set(recipientNumbers.concat(dbPhones)));

                const confirmation = confirm(
                    `Pesan akan dikirim ke ${allTargetPhones.length} penerima. Lanjutkan?`
                );

                if (!confirmation) {
                    e.preventDefault();
                    return false;
                }
                return true;
            });
        }

        // Attachment button
        if (attachFile) {
            attachFile.addEventListener('click', function() {
                attachmentContainer.style.display = 'block';
            });
        }

        // Initialize
        updateCharCount();
        updateStats();
        renderActivitiesWithCurrentFilter();
        filterRecipientDbList();
        updateDbTemplatePreview();
        renderRecipientMessageMatrix();
        syncMessageOverridesField();
        refreshActivityLogs();

        const activityPollIntervalMs = 5000;
        setInterval(() => {
            if (document.visibilityState === 'hidden') {
                return;
            }

            refreshActivityLogs();
        }, activityPollIntervalMs);
    });
</script>
@endsection
