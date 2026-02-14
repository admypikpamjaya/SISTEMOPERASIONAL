@extends('layouts.app')

@section('title', 'Email Blast')

@section('content')
<div class="email-blasting-container">

    {{-- Header dengan Info dan Stats --}}
    <div class="header-section">
        <div class="header-left">
            <div class="app-icon">
                <svg width="32" height="32" viewBox="0 0 32 32" fill="none">
                    <path d="M28 6H4C2.9 6 2 6.9 2 8V24C2 25.1 2.9 26 4 26H28C29.1 26 30 25.1 30 24V8C30 6.9 29.1 6 28 6ZM28 8L16 15L4 8H28ZM28 24H4V10L16 17L28 10V24Z" fill="#4285F4"/>
                </svg>
            </div>
            <div class="app-info">
                <div class="app-title">Email Blast</div>
                <div class="app-subtitle">Kirim email ke banyak penerima</div>
            </div>
        </div>
    </div>

    {{-- Success Alert --}}
    @if(session('success'))
        <div class="success-alert">
            ✅ {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="error-alert">
            âš ï¸ {{ $errors->first() }}
        </div>
    @endif

    <div class="campaign-control-panel">
        <div class="campaign-control-title">Campaign Control</div>
        <div class="campaign-control-note">
            @if(session('campaign_id'))
                Campaign terakhir: <code>{{ session('campaign_id') }}</code>
            @else
                Masukkan Campaign ID untuk pause, resume, atau stop.
            @endif
        </div>
        <div class="campaign-control-note">
            UUID untuk Pause, Resume, dan Soft Stop bisa berbeda.
        </div>
        <div class="campaign-search-row">
            <input
                type="text"
                id="campaignSearchInput"
                class="campaign-control-input"
                placeholder="Cari Campaign UUID..."
                value="{{ session('campaign_id') }}"
            >
            <button type="button" id="campaignSearchBtn" class="campaign-btn info">Search UUID</button>
        </div>
        <div id="campaignSearchResults" class="campaign-search-results"></div>
        <div class="campaign-control-actions">
            <form method="POST" action="{{ route('admin.blast.campaign.pause') }}" class="campaign-control-form" data-action-type="pause">
                @csrf
                <div class="campaign-input-wrap">
                    <input
                        type="text"
                        id="pauseCampaignInput"
                        name="campaign_id"
                        class="campaign-control-input campaign-target-input"
                        data-target-action="pause"
                        placeholder="Campaign UUID untuk Pause"
                        required
                    >
                    <button type="button" class="campaign-clear-btn" data-clear-target="pause" aria-label="Clear Pause UUID">&times;</button>
                </div>
                <button type="submit" class="campaign-btn warning">Pause</button>
            </form>
            <form method="POST" action="{{ route('admin.blast.campaign.resume') }}" class="campaign-control-form" data-action-type="resume">
                @csrf
                <div class="campaign-input-wrap">
                    <input
                        type="text"
                        id="resumeCampaignInput"
                        name="campaign_id"
                        class="campaign-control-input campaign-target-input"
                        data-target-action="resume"
                        placeholder="Campaign UUID untuk Resume"
                        required
                    >
                    <button type="button" class="campaign-clear-btn" data-clear-target="resume" aria-label="Clear Resume UUID">&times;</button>
                </div>
                <button type="submit" class="campaign-btn success">Resume</button>
            </form>
            <form method="POST" action="{{ route('admin.blast.campaign.stop') }}" class="campaign-control-form" data-action-type="stop">
                @csrf
                <div class="campaign-input-wrap">
                    <input
                        type="text"
                        id="stopCampaignInput"
                        name="campaign_id"
                        class="campaign-control-input campaign-target-input"
                        data-target-action="stop"
                        placeholder="Campaign UUID untuk Soft Stop"
                        required
                    >
                    <button type="button" class="campaign-clear-btn" data-clear-target="stop" aria-label="Clear Stop UUID">&times;</button>
                </div>
                <button type="submit" class="campaign-btn danger">Soft Stop</button>
            </form>
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
                    <path d="M4 4H20C21.1 4 22 4.9 22 6V18C22 19.1 21.1 20 20 20H4C2.9 20 2 19.1 2 18V6C2 4.9 2.9 4 4 4Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M22 6L12 13L2 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
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
        {{-- Top Row - Penerima and Kotak Pesan --}}
        <form
            method="POST"
            action="{{ route('admin.blast.email.send') }}"
            enctype="multipart/form-data"
            class="email-form"
            id="emailForm"
        >
            @csrf
            <div class="top-row">
                {{-- Left Column - Penerima Email --}}
                <div class="white-card recipient-card">
                    <div class="section-title">Penerima Email</div>
                    
                    <div class="chip-input-section">
                        <div id="emailChips" class="chip-list"></div>
                        <input
                            type="email"
                            id="emailInput"
                            class="email-input-main"
                            placeholder="Ketik email lalu tekan Enter"
                        >
                    </div>

                    <small class="field-hint">
                        Tekan Enter untuk menambahkan email
                    </small>

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
                                        data-email="{{ $recipient->email_wali }}"
                                    >
                                    <div class="recipient-db-info">
                                        <div class="recipient-db-name">
                                            {{ $recipient->nama_siswa }} ({{ $recipient->kelas }})
                                        </div>
                                        <div class="recipient-db-email">
                                            {{ $recipient->nama_wali }} - {{ $recipient->email_wali }}
                                        </div>
                                    </div>
                                </label>
                            @empty
                                <div class="recipient-db-empty">Tidak ada recipient email valid.</div>
                            @endforelse
                        </div>
                    </div>

                    {{-- HIDDEN TEXTAREA (BACKEND COMPATIBLE) --}}
                    <textarea
                        name="targets"
                        id="targetsField"
                        hidden
                    ></textarea>

                    <div class="excel-import" id="excelImport">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
                            <path d="M14 2H6C5.46957 2 4.96086 2.21071 4.58579 2.58579C4.21071 2.96086 4 3.46957 4 4V20C4 20.5304 4.21071 21.0391 4.58579 21.4142C4.96086 21.7893 5.46957 22 6 22H18C18.5304 22 19.0391 21.7893 19.4142 21.4142C19.7893 21.0391 20 20.5304 20 20V8L14 2Z" stroke="#1D1D41" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M14 2V8H20" stroke="#1D1D41" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        <span>Impor Excel</span>
                    </div>

                </div>

                {{-- Middle Column - Kotak Pesan Email --}}
                <div class="white-card message-card">
                    <div class="section-header">
                        <div class="section-title">Kotak Pesan Email</div>
                    </div>

                    {{-- Student Information --}}
                    <div class="form-group">
                        <label class="form-label">Nama Siswa:</label>
                        <input
                            type="text"
                            name="student_name"
                            id="studentName"
                            class="form-input"
                            placeholder="Masukkan nama siswa"
                        >
                    </div>

                    <div class="form-group">
                        <label class="form-label">Kelas:</label>
                        <input
                            type="text"
                            name="student_class"
                            id="studentClass"
                            class="form-input"
                            placeholder="Masukkan kelas (contoh: 5A)"
                        >
                    </div>

                    <div class="form-group">
                        <label class="form-label">Nama Wali:</label>
                        <input
                            type="text"
                            name="parent_name"
                            id="parentName"
                            class="form-input"
                            placeholder="Masukkan nama wali"
                        >
                    </div>

                    {{-- Template Selection --}}
                    <div class="form-group">
                        <label class="form-label">Template:</label>
                        <select 
                            name="template" 
                            id="templateSelect" 
                            class="form-input"
                            style="height: auto; padding: 12px 16px;"
                        >
                            <option value="">Pilih Template</option>
                            <option value="reminder">Reminder Tagihan Sekolah</option>
                            <option value="payment">Informasi Pembayaran Sekolah</option>
                            <option value="notification">Pemberitahuan Tunggakan</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Announcement:</label>
                        <select
                            name="announcement_id"
                            id="announcementSelect"
                            class="form-input"
                            style="height: auto; padding: 12px 16px;"
                        >
                            <option value="">Pilih Announcement (opsional)</option>
                            @foreach($announcementOptions as $announcement)
                                <option
                                    value="{{ $announcement->id }}"
                                    data-title="{{ e($announcement->title) }}"
                                    data-message="{{ e($announcement->message) }}"
                                >
                                    {{ \Illuminate\Support\Str::limit($announcement->title, 80) }}
                                </option>
                            @endforeach
                        </select>
                        <small class="field-hint">Pilih announcement untuk mengisi subject dan pesan secara otomatis.</small>
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
                                Pilih recipient DB atau tambah email manual untuk mengatur pesan khusus.
                            </div>
                        </div>
                        <input type="hidden" name="message_overrides" id="messageOverridesField">
                    </div>

                    {{-- Subject --}}
                    <div class="form-group">
                        <label class="form-label">Subjek Email:</label>
                        <input
                            name="subject"
                            id="emailSubject"
                            class="form-input"
                            placeholder="Masukkan subjek email"
                            required
                        >
                    </div>

                    {{-- Message --}}
                    <div class="form-group">
                        <label class="form-label">Isi Email:</label>
                        <textarea
                            name="message"
                            id="messageTextarea"
                            class="message-textarea"
                            placeholder="Tulis isi email di sini..."
                            rows="8"
                        ></textarea>
                        <label class="global-default-toggle">
                            <input type="checkbox" name="use_global_default" id="useGlobalDefaultToggle" value="1" checked>
                            Gunakan isi email global sebagai default penerima (override template jika tidak dipilih khusus).
                        </label>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Pengaturan Pengiriman Lanjutan:</label>
                        <div class="delivery-settings-grid">
                            <div class="delivery-setting-item">
                                <label class="form-label" for="scheduledAtInput">Jadwal Kirim</label>
                                <input
                                    type="datetime-local"
                                    name="scheduled_at"
                                    id="scheduledAtInput"
                                    class="form-input"
                                    value="{{ old('scheduled_at') }}"
                                >
                            </div>
                            <div class="delivery-setting-item">
                                <label class="form-label" for="priorityInput">Queue Priority</label>
                                <select name="priority" id="priorityInput" class="form-input">
                                    <option value="high" @selected(old('priority') === 'high')>High</option>
                                    <option value="normal" @selected(old('priority', 'normal') === 'normal')>Normal</option>
                                    <option value="low" @selected(old('priority') === 'low')>Low</option>
                                </select>
                            </div>
                            <div class="delivery-setting-item">
                                <label class="form-label" for="rateLimitInput">Rate / menit</label>
                                <input
                                    type="number"
                                    min="1"
                                    name="rate_limit_per_minute"
                                    id="rateLimitInput"
                                    class="form-input"
                                    value="{{ old('rate_limit_per_minute', config('blast.rate_limits.email_per_minute', 90)) }}"
                                >
                            </div>
                            <div class="delivery-setting-item">
                                <label class="form-label" for="batchSizeInput">Batch Size</label>
                                <input
                                    type="number"
                                    min="1"
                                    name="batch_size"
                                    id="batchSizeInput"
                                    class="form-input"
                                    value="{{ old('batch_size', config('blast.batch.size', 50)) }}"
                                >
                            </div>
                            <div class="delivery-setting-item">
                                <label class="form-label" for="batchDelayInput">Delay Antar Batch (detik)</label>
                                <input
                                    type="number"
                                    min="0"
                                    name="batch_delay_seconds"
                                    id="batchDelayInput"
                                    class="form-input"
                                    value="{{ old('batch_delay_seconds', config('blast.batch.delay_seconds', 10)) }}"
                                >
                            </div>
                            <div class="delivery-setting-item">
                                <label class="form-label" for="retryAttemptsInput">Max Retry</label>
                                <input
                                    type="number"
                                    min="1"
                                    max="10"
                                    name="retry_attempts"
                                    id="retryAttemptsInput"
                                    class="form-input"
                                    value="{{ old('retry_attempts', config('blast.retry.max_attempts', 3)) }}"
                                >
                            </div>
                            <div class="delivery-setting-item delivery-wide">
                                <label class="form-label" for="retryBackoffInput">Backoff Retry (detik, pisahkan koma)</label>
                                <input
                                    type="text"
                                    name="retry_backoff_seconds"
                                    id="retryBackoffInput"
                                    class="form-input"
                                    value="{{ old('retry_backoff_seconds', implode(',', config('blast.retry.backoff_seconds', [30, 120, 300]))) }}"
                                    placeholder="30,120,300"
                                >
                            </div>
                        </div>
                        <small class="field-hint">
                            Kosongkan jadwal kirim untuk mengirim segera.
                        </small>
                    </div>

                   <div class="message-footer">
    <div class="attachment-buttons">
        <label class="attach-btn" for="fileAttachment">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
                <path d="M21.44 11.05L12.25 20.24C11.1242 21.3658 9.59723 21.9983 8.005 21.9983C6.41277 21.9983 4.88583 21.3658 3.76 20.24C2.63417 19.1142 2.00166 17.5872 2.00166 15.995C2.00166 14.4028 2.63417 12.8758 3.76 11.75L12.33 3.18C13.0806 2.42944 14.0991 2.00667 15.16 2.00667C16.2209 2.00667 17.2394 2.42944 17.99 3.18C18.7406 3.93056 19.1633 4.94908 19.1633 6.01C19.1633 7.07092 18.7406 8.08944 17.99 8.84L9.41 17.41C9.03472 17.7853 8.52548 17.9967 7.995 17.9967C7.46452 17.9967 6.95528 17.7853 6.58 17.41C6.20472 17.0347 5.99333 16.5255 5.99333 15.995C5.99333 15.4645 6.20472 14.9553 6.58 14.58L15.07 6.1"
                      stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            <span>Lampirkan File</span>
        </label>
        <input
            type="file"
            name="attachments[]"
            id="fileAttachment"
            class="field-input-file"
            multiple
            style="display: none;"
        >
    </div>
    <div class="char-count" id="charCount">0 karakter</div>
</div>

{{-- ✅ PREVIEW FILE (BARU) --}}
<div id="attachmentPreview"></div>

<div class="form-hint">
    Anda dapat memilih lebih dari satu file
</div>


                    <button type="submit" class="send-button" id="sendButton">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
                            <path d="M22 2L11 13" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M22 2L15 22L11 13L2 9L22 2Z" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        <span>Kirim Email</span>
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
                    <div class="col-email">Email Penerima</div>
                    <div class="col-email">Subjek</div>
                    <div class="col-attachment">Lampiran</div>
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
                <div class="tip-item">Pastikan email yang dimasukkan valid dan aktif.</div>
                <div class="tip-item">Gunakan subjek yang jelas dan menarik untuk meningkatkan tingkat buka.</div>
                <div class="tip-item">Personalisasi pesan menggunakan variabel untuk engagement lebih baik.</div>
                <div class="tip-item">Hindari penggunaan kata-kata yang masuk spam filter.</div>
            </div>
        </div>
    </div>
</div>

<style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
        font-family: 'Inter', 'Segoe UI', -apple-system, BlinkMacSystemFont, sans-serif;
    }

    .email-blasting-container {
        width: 100%;
        min-height: 100vh;
        padding: 30px;
        background: #f5f7fa;
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
        background: linear-gradient(90deg,#4F46E5,#9333EA);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    .app-subtitle {
        font-size: 14px;
        color: #6B7280;
    }
#attachmentPreview {
    margin-top: 8px;
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.attachment-item {
    padding: 8px 12px;
    font-size: 12px;
    background: #f8f9fa;
    border: 1px solid #E5E7EB;
    border-radius: 10px;
    color: #4B5563;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.recipient-db-section {
    margin-top: 14px;
    border: 1px solid #E5E7EB;
    border-radius: 12px;
    padding: 12px;
    background: #FAFBFF;
}

.recipient-db-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 8px;
}

.recipient-db-title {
    font-size: 13px;
    font-weight: 700;
    color: #374151;
}

.recipient-db-count {
    font-size: 12px;
    color: #6B7280;
    margin-bottom: 8px;
}

.recipient-db-search {
    margin-bottom: 10px;
}

.recipient-db-search-input {
    width: 100%;
    border: 1px solid #D1D5DB;
    border-radius: 8px;
    padding: 8px 10px;
    font-size: 12px;
    color: #111827;
    background: #FFFFFF;
}

.recipient-db-search-input:focus {
    outline: none;
    border-color: #A5B4FC;
    box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.12);
}

.btn-select-db {
    border: 1px solid #4F46E5;
    color: #4F46E5;
    background: #EEF2FF;
    border-radius: 8px;
    font-size: 12px;
    padding: 4px 10px;
    cursor: pointer;
}

.recipient-db-list {
    max-height: 180px;
    overflow-y: auto;
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.recipient-db-item {
    display: flex;
    align-items: flex-start;
    gap: 10px;
    padding: 8px 10px;
    border: 1px solid #E5E7EB;
    border-radius: 10px;
    background: #FFFFFF;
    cursor: pointer;
}

.recipient-db-item:hover {
    border-color: #C7D2FE;
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

.recipient-db-email {
    font-size: 11px;
    color: #6B7280;
}

.recipient-db-empty {
    font-size: 12px;
    color: #6B7280;
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

    .btn-back {
        padding: 10px 16px;
        border-radius: 10px;
        background: #F3F4F6;
        color: #4B5563;
        text-decoration: none;
        font-size: 14px;
        font-weight: 500;
        transition: background 0.2s;
    }

    .btn-back:hover {
        background: #E5E7EB;
    }

    /* Success Alert */
    .success-alert {
        margin-bottom: 25px;
        padding: 14px 18px;
        border-radius: 12px;
        background: linear-gradient(90deg,#ECFEFF,#F0F9FF);
        color: #0369A1;
        font-size: 14px;
    }

    .error-alert {
        margin-bottom: 25px;
        padding: 14px 18px;
        border-radius: 12px;
        background: linear-gradient(90deg,#FEF2F2,#FFF1F2);
        color: #B91C1C;
        font-size: 14px;
    }

    .campaign-control-panel {
        background: #FFFFFF;
        border: 1px solid #E5E7EB;
        border-radius: 12px;
        padding: 16px;
        margin-bottom: 25px;
    }

    .campaign-control-title {
        font-size: 14px;
        font-weight: 700;
        color: #1F2937;
        margin-bottom: 6px;
    }

    .campaign-control-note {
        font-size: 12px;
        color: #6B7280;
        margin-bottom: 12px;
    }

    .campaign-control-actions {
        display: grid;
        gap: 10px;
        grid-template-columns: repeat(3, minmax(0, 1fr));
    }

    .campaign-search-row {
        display: flex;
        gap: 8px;
        margin-bottom: 10px;
        align-items: center;
    }

    .campaign-search-results {
        display: flex;
        flex-direction: column;
        gap: 6px;
        margin-bottom: 12px;
        max-height: 180px;
        overflow: auto;
    }

    .campaign-search-empty {
        font-size: 12px;
        color: #6B7280;
    }

    .campaign-search-item {
        border: 1px solid #E5E7EB;
        border-radius: 8px;
        padding: 8px 10px;
        display: flex;
        justify-content: space-between;
        gap: 8px;
        align-items: center;
    }

    .campaign-search-meta {
        font-size: 11px;
        color: #4B5563;
        line-height: 1.4;
    }

    .campaign-result-actions {
        display: flex;
        gap: 6px;
        flex-wrap: wrap;
        justify-content: flex-end;
    }

    .campaign-control-form {
        display: flex;
        gap: 8px;
    }

    .campaign-input-wrap {
        position: relative;
        flex: 1;
    }

    .campaign-control-input {
        flex: 1;
        border: 1px solid #D1D5DB;
        border-radius: 8px;
        padding: 8px 10px;
        font-size: 12px;
    }

    .campaign-target-input {
        padding-right: 28px;
    }

    .campaign-clear-btn {
        position: absolute;
        right: 6px;
        top: 50%;
        transform: translateY(-50%);
        width: 20px;
        height: 20px;
        border: none;
        border-radius: 999px;
        background: #E5E7EB;
        color: #1F2937;
        font-size: 14px;
        line-height: 1;
        cursor: pointer;
        display: none;
        align-items: center;
        justify-content: center;
        padding: 0;
    }

    .campaign-clear-btn.visible {
        display: inline-flex;
    }

    .campaign-btn {
        border: none;
        border-radius: 8px;
        padding: 8px 10px;
        color: #FFFFFF;
        font-size: 12px;
        font-weight: 600;
        cursor: pointer;
    }

    .campaign-btn.warning {
        background: #F59E0B;
    }

    .campaign-btn.success {
        background: #16A34A;
    }

    .campaign-btn.danger {
        background: #DC2626;
    }

    .campaign-btn.info {
        background: #2563EB;
    }

    .campaign-btn.tiny {
        padding: 6px 8px;
        font-size: 11px;
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
        border-radius: 20px;
        padding: 25px;
        box-shadow: 0 20px 40px rgba(79,70,229,.08);
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
        font-size: 14px;
        color: #666;
        font-weight: 600;
        margin-bottom: 8px;
    }

    .form-input {
        width: 100%;
        height: 48px;
        border: 1px solid #E5E7EB;
        border-radius: 14px;
        padding: 0 16px;
        font-size: 14px;
        outline: none;
        transition: border-color 0.2s;
    }

    .form-input:focus {
        border-color: #4F46E5;
    }

    /* Recipient Card - Chip Input Styling */
    .chip-input-section {
        border: 1px solid #E5E7EB;
        border-radius: 14px;
        padding: 12px;
        min-height: 60px;
        background: #FFFFFF;
        margin-bottom: 8px;
    }

    .chip-list {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-bottom: 8px;
    }

    .chip {
        background: linear-gradient(90deg, #4F46E5, #7C3AED);
        color: #fff;
        padding: 6px 12px;
        border-radius: 999px;
        font-size: 13px;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    .chip button {
        background: none;
        border: none;
        color: #fff;
        cursor: pointer;
        font-size: 14px;
        line-height: 1;
        padding: 0;
        width: 18px;
        height: 18px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        transition: background 0.2s;
    }

    .chip button:hover {
        background: rgba(255, 255, 255, 0.2);
    }

    .email-input-main {
        width: 100%;
        border: none;
        padding: 8px 0;
        font-size: 14px;
        outline: none;
        background: transparent;
    }

    .field-hint {
        font-size: 12px;
        color: #6B7280;
        margin-top: 6px;
        display: block;
        margin-bottom: 15px;
    }

    .delivery-settings-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 12px;
    }

    .delivery-setting-item {
        display: flex;
        flex-direction: column;
        gap: 6px;
    }

    .delivery-setting-item .form-label {
        margin-bottom: 0;
        font-size: 12px;
    }

    .delivery-wide {
        grid-column: 1 / -1;
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
        border-color: #4F46E5;
    }

    .excel-import span {
        font-size: 14px;
        color: #1D1D41;
        font-weight: 500;
    }

    /* Message Card */
    .section-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
    }

    .message-textarea {
        width: 100%;
        height: 180px;
        border: 1px solid #E5E7EB;
        border-radius: 14px;
        padding: 16px;
        font-size: 14px;
        resize: vertical;
        outline: none;
        font-family: inherit;
        transition: border-color 0.2s;
        line-height: 1.5;
    }

    .message-textarea:focus {
        border-color: #4F46E5;
    }

    .variable-buttons {
        display: flex;
        gap: 8px;
        margin-bottom: 15px;
        flex-wrap: wrap;
    }

    .var-btn {
        padding: 8px 16px;
        background: white;
        border: 1px solid #E5E7EB;
        border-radius: 8px;
        font-size: 12px;
        color: #666;
        cursor: pointer;
        transition: all 0.2s;
    }

    .var-btn:hover {
        background: #4F46E5;
        color: white;
        border-color: #4F46E5;
    }

    .message-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 10px;
    }

    .attachment-buttons {
        display: flex;
        gap: 10px;
    }

    .attach-btn {
        display: flex;
        align-items: center;
        gap: 6px;
        padding: 10px 16px;
        background: #f8f9fa;
        border: 1px solid #E5E7EB;
        border-radius: 10px;
        cursor: pointer;
        transition: all 0.2s;
        font-size: 13px;
        color: #666;
    }

    .attach-btn:hover {
        background: #e9ecef;
        border-color: #4F46E5;
        color: #4F46E5;
    }

    .attach-btn svg {
        stroke: currentColor;
    }

    .char-count {
        font-size: 12px;
        color: #999;
    }

    .form-hint {
        font-size: 12px;
        color: #6B7280;
        margin-top: 6px;
        margin-bottom: 20px;
    }

    .send-button {
        width: 100%;
        height: 48px;
        background: linear-gradient(90deg,#4F46E5,#7C3AED);
        color: white;
        border: none;
        border-radius: 14px;
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
        box-shadow: 0 10px 28px rgba(124,58,237,.35);
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
        grid-template-columns: 120px 1fr 80px 1fr 180px 1fr 100px 100px;
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
        grid-template-columns: 120px 1fr 80px 1fr 180px 1fr 100px 100px;
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

    .col-email {
        font-size: 11px;
        color: #666;
        word-break: break-all;
        line-height: 1.4;
    }

    .col-siswa, .col-kelas, .col-wali {
        font-size: 11px;
        color: #666;
        word-break: break-word;
        line-height: 1.4;
    }

    .col-subject {
        font-size: 12px;
        color: #1D1D41;
        font-weight: 500;
        word-break: break-word;
        line-height: 1.3;
    }

    .col-attachment {
        font-size: 11px;
        color: #666;
    }

    .status-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 4px;
        padding: 6px 12px;
        border-radius: 10px;
        font-size: 11px;
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
        width: 6px;
        height: 6px;
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

        .campaign-control-actions {
            grid-template-columns: 1fr;
        }

        .activity-table-header,
        .activity-row {
            grid-template-columns: 100px 1fr 80px 1fr 150px 1fr 90px 90px;
            font-size: 11px;
        }
    }

    @media (max-width: 1200px) {
        .activity-table-header,
        .activity-row {
            grid-template-columns: 100px 1fr 1fr 1fr 1fr;
            grid-template-areas: 
                "waktu siswa kelas wali email"
                "subject subject attachment attachment status";
        }
        
        .col-waktu { grid-area: waktu; }
        .col-siswa { grid-area: siswa; }
        .col-kelas { grid-area: kelas; }
        .col-wali { grid-area: wali; }
        .col-email { grid-area: email; }
        .col-subject { grid-area: subject; }
        .col-attachment { grid-area: attachment; }
        .col-status { grid-area: status; }
    }

    @media (max-width: 768px) {
        .email-blasting-container {
            padding: 20px;
        }

        .header-section {
            flex-direction: column;
            align-items: flex-start;
            gap: 15px;
        }

        .btn-back {
            align-self: flex-start;
        }

        .stats-container {
            grid-template-columns: 1fr;
        }

        .delivery-settings-grid {
            grid-template-columns: 1fr;
        }

        .campaign-control-form {
            flex-direction: column;
        }

        .campaign-search-row {
            flex-direction: column;
            align-items: stretch;
        }

        .app-title {
            font-size: 22px;
        }

        .activity-table-header {
            display: none;
        }
    .attachment-remove {
    background: none;
    border: none;
    color: #ef4444;
    font-size: 14px;
    cursor: pointer;
    padding: 2px 6px;
    border-radius: 6px;
}

.attachment-remove:hover {
    background: rgba(239, 68, 68, 0.1);
}

        .activity-row {
            grid-template-columns: 1fr;
            gap: 8px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 8px;
            margin-bottom: 10px;
        }

        .col-waktu::before { content: 'Detail Waktu: '; font-weight: 600; }
        .col-siswa::before { content: 'Nama Siswa: '; font-weight: 600; }
        .col-kelas::before { content: 'Kelas: '; font-weight: 600; }
        .col-wali::before { content: 'Nama Wali: '; font-weight: 600; }
        .col-email::before { content: 'Email Penerima: '; font-weight: 600; }
        .col-subject::before { content: 'Subject: '; font-weight: 600; }
        .col-attachment::before { content: 'Lampiran: '; font-weight: 600; }
        .col-status::before { content: 'Status: '; font-weight: 600; }
    }

    /* Scrollbar Styling */
    .activity-table-body::-webkit-scrollbar {
        width: 6px;
    }

    .activity-table-body::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 10px;
    }

    .activity-table-body::-webkit-scrollbar-thumb {
        background: #c1c1c1;
        border-radius: 10px;
    }

    .activity-table-body::-webkit-scrollbar-thumb:hover {
        background: #a8a8a8;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        // Chip Input functionality FROM CODE PERTAMA (Working Blasting)
        const emailInput = document.getElementById('emailInput');
        const chipList = document.getElementById('emailChips');
        const targetsField = document.getElementById('targetsField');
        const studentName = document.getElementById('studentName');
        const studentClass = document.getElementById('studentClass');
        const parentName = document.getElementById('parentName');
        const templateSelect = document.getElementById('templateSelect');
        const announcementSelect = document.getElementById('announcementSelect');
        const dbTemplateSelect = document.getElementById('dbTemplateSelect');
        const dbTemplatePreview = document.getElementById('dbTemplatePreview');
        const emailSubject = document.getElementById('emailSubject');
        const messageTextarea = document.getElementById('messageTextarea');
        const scheduledAtInput = document.getElementById('scheduledAtInput');
        const priorityInput = document.getElementById('priorityInput');
        const rateLimitInput = document.getElementById('rateLimitInput');
        const batchSizeInput = document.getElementById('batchSizeInput');
        const batchDelayInput = document.getElementById('batchDelayInput');
        const retryAttemptsInput = document.getElementById('retryAttemptsInput');
        const retryBackoffInput = document.getElementById('retryBackoffInput');
        const charCount = document.getElementById('charCount');
        const sendButton = document.getElementById('sendButton');
        const selectAllRecipientsBtn = document.getElementById('selectAllRecipientsBtn');
        const recipientDbSearchInput = document.getElementById('recipientDbSearchInput');
        const recipientDbList = document.querySelector('.recipient-db-list');
        const recipientDbItems = Array.from(document.querySelectorAll('.recipient-db-item'));
        const recipientDbCheckboxes = document.querySelectorAll('.recipient-db-checkbox');
        const recipientMessageMatrix = document.getElementById('recipientMessageMatrix');
        const messageOverridesField = document.getElementById('messageOverridesField');
        
        let emails = [];
        const overrideState = {};
        const attachmentBufferByKey = {};

        // Template definitions
        const templates = {
            reminder: {
                subject: "Reminder Tagihan Sekolah - {nama_siswa} Kelas {kelas}",
                message: `Yth. Bapak/Ibu {nama_wali},

Kami ingin mengingatkan bahwa tagihan sekolah untuk {nama_siswa} (Kelas {kelas}) akan jatuh tempo pada {jatuh_tempo}.

Detail Tagihan:
- Jumlah: Rp {tagihan}
- Jatuh Tempo: {jatuh_tempo}
- Status: Belum Lunas

Mohon untuk segera melakukan pembayaran melalui:
1. Transfer Bank: BCA 1234567890 a/n Sekolah Terpadu
2. Tunai di Sekolah: Kantor TU, jam 08.00-15.00

Untuk konfirmasi pembayaran, silakan hubungi:
- Ibu Sari: 0812-3456-7890
- Kantor Sekolah: (021) 1234567

Terima kasih atas perhatian dan kerjasamanya.

Hormat kami,
Administrasi Sekolah
Sekolah Terpadu Jakarta`
            },
            payment: {
                subject: "Informasi Pembayaran Sekolah - {nama_siswa} Kelas {kelas}",
                message: `Kepada Yth. Bapak/Ibu {nama_wali},

Berikut informasi pembayaran sekolah untuk periode Januari 2024:

Nama Siswa: {nama_siswa}
Kelas: {kelas}

Rincian Pembayaran:
1. SPP Bulanan: Rp 500.000
2. Uang Kegiatan: Rp 200.000
3. Uang Buku: Rp 150.000
───────────────────────
Total: Rp {tagihan}

Batas Pembayaran: {jatuh_tempo}

Cara Pembayaran:
✅ Transfer Bank: BCA 1234567890
✅ Tunai di Sekolah
✅ E-Wallet: OVO/DANA

Setelah pembayaran, harap kirim bukti transfer ke WhatsApp: 0812-3456-7890

Jika sudah membayar, abaikan email ini.

Terima kasih.

Salam,
Bendahara Sekolah`
            },
            notification: {
                subject: "Pemberitahuan Tunggakan - {nama_siswa} Kelas {kelas}",
                message: `KEPADA YTH.
BAPAK/IBU {nama_wali}
ORANG TUA/WALI SISWA
{nama_siswa} - KELAS {kelas}

DENGAN HORMAT,

Kami informasikan bahwa hingga saat ini, terdapat tunggakan pembayaran sekolah untuk:
- Nama Siswa: {nama_siswa}
- Kelas: {kelas}
- Total Tunggakan: Rp {tagihan}
- Jatuh Tempo: {jatuh_tempo}
- Keterlambatan: 15 hari

PENTING:
1. Mohon segera melakukan pelunasan maksimal 3 hari setelah email ini diterima
2. Pembayaran dapat dilakukan melalui transfer atau langsung ke sekolah
3. Keterlambatan pembayaran dapat mengakibatkan:
   - Pembatasan mengikuti ujian
   - Tidak dapat menerima rapor
   - Tidak diperbolehkan mengikuti kegiatan sekolah

Untuk konfirmasi dan informasi lebih lanjut, silakan hubungi:
📞 0812-3456-7890 (Ibu Sari - Bendahara)
🏫 Kantor Sekolah: (021) 1234567

Kami harap Bapak/Ibu dapat memahami situasi ini dan segera menyelesaikan kewajiban pembayaran.

HORMAT KAMI,
KEPALA SEKOLAH
SEKOLAH TERPADU JAKARTA`
            }
        };

        // === FROM CODE PERTAMA (Working Chip Input) ===
        function syncTargets() {
            targetsField.value = emails.join(',');
        }

        function addChip(email) {
            if (emails.includes(email)) return;

            emails.push(email);
            syncTargets();

            const chip = document.createElement('div');
            chip.className = 'chip';
            chip.setAttribute('data-email', email);
            chip.innerHTML = `${email} <button type="button">×</button>`;

            chip.querySelector('button').onclick = () => {
                emails = emails.filter(e => e !== email);
                delete overrideState[`manual:${email.trim().toLowerCase()}`];
                delete attachmentBufferByKey[`manual:${email.trim().toLowerCase()}`];
                chip.remove();
                syncTargets();
                renderRecipientMessageMatrix();
            };

            chipList.appendChild(chip);
            renderRecipientMessageMatrix();
        }

        function removeManualEmailByAddress(email) {
            const normalized = email.trim().toLowerCase();

            emails = emails.filter(
                e => e.trim().toLowerCase() !== normalized
            );

            delete overrideState[`manual:${normalized}`];
            delete attachmentBufferByKey[`manual:${normalized}`];
            syncTargets();

            chipList.querySelectorAll('.chip').forEach((chip) => {
                const chipEmail = (chip.getAttribute('data-email') || '').trim().toLowerCase();
                if (chipEmail === normalized) {
                    chip.remove();
                }
            });
        }

        function removeDbRecipientById(recipientId) {
            recipientDbCheckboxes.forEach((cb) => {
                if (cb.value === recipientId) {
                    cb.checked = false;
                }
            });

            delete overrideState[`db:${recipientId}`];
            delete attachmentBufferByKey[`db:${recipientId}`];
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

        function escapeHtml(value) {
            return String(value)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#39;');
        }

        function getSelectedRecipients() {
            const recipients = [];

            recipientDbCheckboxes.forEach((cb) => {
                if (!cb.checked) {
                    return;
                }

                const key = `db:${cb.value}`;
                const label = cb.closest('.recipient-db-item')
                    ?.querySelector('.recipient-db-name')
                    ?.textContent?.trim() || cb.value;

                recipients.push({
                    key,
                    label: `DB - ${label}`,
                    kind: 'db',
                    ref: cb.value,
                });
            });

            emails.forEach((email) => {
                recipients.push({
                    key: `manual:${email.trim().toLowerCase()}`,
                    label: `Manual - ${email}`,
                    kind: 'manual',
                    ref: email,
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
                    overrides[key] = { mode: 'manual', message };
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
                        Pilih recipient DB atau tambah email manual untuk mengatur pesan khusus.
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
                const radioGroup = `override_mode_${key.replace(/[^a-zA-Z0-9_-]/g, '_')}`;
                const modeClass = `mode-${effectiveMode}`;
                const badgeText = effectiveMode === 'template'
                    ? 'Template'
                    : (effectiveMode === 'global' ? 'Global' : 'Manual');
                const hintText = effectiveMode === 'template'
                    ? 'Menggunakan template blast DB untuk penerima ini.'
                    : (effectiveMode === 'global'
                        ? 'Menggunakan isi email global untuk penerima ini.'
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

        // Email input event - FROM CODE PERTAMA
        if (emailInput) {
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
        }

        if (selectAllRecipientsBtn && recipientDbCheckboxes.length > 0) {
            let allRecipientSelected = false;

            selectAllRecipientsBtn.addEventListener('click', function () {
                allRecipientSelected = !allRecipientSelected;
                recipientDbCheckboxes.forEach(cb => {
                    cb.checked = allRecipientSelected;
                });

                selectAllRecipientsBtn.textContent = allRecipientSelected
                    ? 'Unselect All'
                    : 'Select All';

                renderRecipientMessageMatrix();
            });
        }

        recipientDbCheckboxes.forEach((cb) => {
            cb.addEventListener('change', () => {
                renderRecipientMessageMatrix();
            });
        });

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

        if (dbTemplateSelect) {
            dbTemplateSelect.addEventListener('change', updateDbTemplatePreview);
            updateDbTemplatePreview();
        }

        if (recipientMessageMatrix) {
            recipientMessageMatrix.addEventListener('click', function (event) {
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
                    removeManualEmailByAddress(ref);
                }

                if (key) {
                    delete overrideState[key];
                    delete attachmentBufferByKey[key];
                }

                renderRecipientMessageMatrix();
            });

            recipientMessageMatrix.addEventListener('change', function (event) {
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
                                ? 'Menggunakan isi email global untuk penerima ini.'
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

            recipientMessageMatrix.addEventListener('input', function (event) {
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

        // Template selection event
        if (announcementSelect) {
            announcementSelect.addEventListener('change', function() {
                const selectedOption = this.options[this.selectedIndex];
                if (!selectedOption || !selectedOption.value) {
                    return;
                }

                const title = (selectedOption.getAttribute('data-title') || '').trim();
                const message = selectedOption.getAttribute('data-message') || '';

                if (title !== '') {
                    emailSubject.value = `[Announcement] ${title}`;
                }

                if (message.trim() !== '') {
                    messageTextarea.value = message;
                    messageTextarea.dispatchEvent(new Event('input'));
                }
            });
        }

        if (templateSelect) {
            templateSelect.addEventListener('change', function() {
                const selectedTemplate = this.value;
                
                if (selectedTemplate && templates[selectedTemplate]) {
                    const template = templates[selectedTemplate];
                    
                    // Fill subject with placeholders
                    let subject = template.subject
                        .replace('{nama_siswa}', studentName.value || '{nama_siswa}')
                        .replace('{kelas}', studentClass.value || '{kelas}');
                    
                    // Fill message with placeholders
                    let message = template.message
                        .replace(/{nama_siswa}/g, studentName.value || '{nama_siswa}')
                        .replace(/{kelas}/g, studentClass.value || '{kelas}')
                        .replace(/{nama_wali}/g, parentName.value || '{nama_wali}');
                    
                    emailSubject.value = subject;
                    messageTextarea.value = message;
                    
                    // Trigger character count update
                    messageTextarea.dispatchEvent(new Event('input'));
                }
            });
        }

        // Auto-update template when student info changes
        [studentName, studentClass, parentName].forEach(input => {
            input.addEventListener('input', function() {
                if (templateSelect.value && templates[templateSelect.value]) {
                    const template = templates[templateSelect.value];
                    
                    let subject = template.subject
                        .replace('{nama_siswa}', studentName.value || '{nama_siswa}')
                        .replace('{kelas}', studentClass.value || '{kelas}');
                    
                    let message = template.message
                        .replace(/{nama_siswa}/g, studentName.value || '{nama_siswa}')
                        .replace(/{kelas}/g, studentClass.value || '{kelas}')
                        .replace(/{nama_wali}/g, parentName.value || '{nama_wali}');
                    
                    emailSubject.value = subject;
                    messageTextarea.value = message;
                    messageTextarea.dispatchEvent(new Event('input'));
                }
            });
        });

        // Variable buttons functionality
        const varButtons = document.querySelectorAll('.var-btn');
        varButtons.forEach(btn => {
            btn.addEventListener('click', function() {
                const variable = this.getAttribute('data-variable');
                const cursorPos = messageTextarea.selectionStart;
                const textBefore = messageTextarea.value.substring(0, cursorPos);
                const textAfter = messageTextarea.value.substring(cursorPos);
                
                messageTextarea.value = textBefore + '{' + variable + '}' + textAfter;
                messageTextarea.focus();
                
                const newPos = cursorPos + variable.length + 2;
                messageTextarea.setSelectionRange(newPos, newPos);
                
                messageTextarea.dispatchEvent(new Event('input'));
            });
        });

        // Character count functionality
        if (messageTextarea && charCount) {
            function updateCharCount() {
                const charLength = messageTextarea.value.length;
                charCount.textContent = `${charLength} karakter`;
            }

            messageTextarea.addEventListener('input', updateCharCount);
            updateCharCount();
        }

        // File attachment button
        const attachBtn = document.querySelector('.attach-btn');
       const fileAttachment = document.getElementById('fileAttachment');
const preview = document.getElementById('attachmentPreview');

let fileBuffer = new DataTransfer();

if (fileAttachment && preview) {
    fileAttachment.addEventListener('change', function () {
        Array.from(this.files).forEach(file => {
            fileBuffer.items.add(file);
        });

        syncFiles();
    });
}

function syncFiles() {
    preview.innerHTML = '';
    fileAttachment.files = fileBuffer.files;

    Array.from(fileBuffer.files).forEach((file, index) => {
        const item = document.createElement('div');
        item.className = 'attachment-item';

        item.innerHTML = `
            <span>${file.name} (${(file.size / 1024).toFixed(1)} KB)</span>
            <button type="button" class="attachment-remove">×</button>
        `;

        item.querySelector('.attachment-remove').addEventListener('click', () => {
            removeFile(index);
        });

        preview.appendChild(item);
    });
}

function removeFile(index) {
    const newBuffer = new DataTransfer();

    Array.from(fileBuffer.files).forEach((file, i) => {
        if (i !== index) {
            newBuffer.items.add(file);
        }
    });

    fileBuffer = newBuffer;
    syncFiles();
}



        // Excel import button - FROM CODE KEDUA
        const excelImport = document.getElementById('excelImport');

        if (excelImport) {
            excelImport.addEventListener('click', function() {
                const fileInput = document.createElement('input');
                fileInput.type = 'file';
                fileInput.accept = '.xlsx,.xls,.csv';
                
                fileInput.addEventListener('change', function(e) {
                    if (e.target.files.length > 0) {
                        const fileName = e.target.files[0].name;
                        
                        // Simulate reading Excel file
                        alert(`File "${fileName}" berhasil diimpor!`);
                        
                        // Demo: Add multiple emails from Excel
                        const demoEmails = [
                            'wali1@example.com',
                            'wali2@example.com', 
                            'wali3@example.com',
                            'wali4@example.com',
                            'wali5@example.com',
                            'wali6@example.com',
                            'wali7@example.com',
                            'wali8@example.com',
                            'wali9@example.com',
                            'wali10@example.com'
                        ];
                        
                        // Clear existing emails first
                        emails = [];
                        chipList.innerHTML = '';
                        
                        // Add all demo emails
                        demoEmails.forEach(email => addChip(email));
                        
                        alert(`${demoEmails.length} email berhasil diimpor dari file Excel!`);
                    }
                });
                
                fileInput.click();
            });
        }

        // Stats variables
        const statTotal = document.getElementById('statTotal');
        const statSent = document.getElementById('statSent');
        const statFailed = document.getElementById('statFailed');
        const statPending = document.getElementById('statPending');

        // Activity log functionality
        const activityLog = document.getElementById('activityLog');
        const searchInput = document.getElementById('searchInput');
        const campaignSearchInput = document.getElementById('campaignSearchInput');
        const campaignSearchBtn = document.getElementById('campaignSearchBtn');
        const campaignSearchResults = document.getElementById('campaignSearchResults');
        const campaignTargetInputs = Array.from(document.querySelectorAll('.campaign-target-input'));
        const campaignClearButtons = Array.from(document.querySelectorAll('.campaign-clear-btn'));
        const activityApiUrl = @json(route('admin.blast.activity'));
        const campaignApiUrl = @json(route('admin.blast.campaigns'));
        const activityChannel = 'email';
        let activities = @json($activityLogs ?? []);
        let isRefreshingActivities = false;

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
                row.setAttribute('data-campaign-id', String(activity.campaignId || ''));
                
                const statusClass = activity.status === 'success' ? 'success' : 
                                  activity.status === 'failed' ? 'failed' : 'pending';
                const statusText = activity.status === 'success' ? 'Terkirim' : 
                                 activity.status === 'failed' ? 'Gagal' : 'Pending';
                
                row.innerHTML = `
                    <div class="col-waktu">
                        <div class="waktu-date">${activity.date}</div>
                        <div class="waktu-time">${activity.time}</div>
                    </div>
                    <div class="col-siswa">${activity.studentName}</div>
                    <div class="col-kelas">${activity.studentClass}</div>
                    <div class="col-wali">${activity.parentName}</div>
                    <div class="col-email">${activity.email}</div>
                    <div class="col-subject">${activity.subject}</div>
                    <div class="col-attachment">${activity.attachments}</div>
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
                return String(activity.email || '').toLowerCase().includes(searchTerm) ||
                    String(activity.subject || '').toLowerCase().includes(searchTerm) ||
                    String(activity.studentName || '').toLowerCase().includes(searchTerm) ||
                    String(activity.parentName || '').toLowerCase().includes(searchTerm) ||
                    String(activity.campaignId || '').toLowerCase().includes(searchTerm);
            });

            renderActivities(filtered);
        }

        function syncCampaignClearButtons() {
            campaignClearButtons.forEach((button) => {
                const target = button.getAttribute('data-clear-target');
                const input = campaignTargetInputs.find((item) => item.getAttribute('data-target-action') === target);
                const hasValue = input ? input.value.trim() !== '' : false;
                button.classList.toggle('visible', hasValue);
            });
        }

        function applyCampaignIdToTarget(campaignId, targetAction) {
            const input = campaignTargetInputs.find((item) => item.getAttribute('data-target-action') === targetAction);
            if (!input) {
                return;
            }

            input.value = campaignId;
            syncCampaignClearButtons();
            input.focus();
        }

        function renderCampaignResults(campaigns) {
            if (!campaignSearchResults) {
                return;
            }

            campaignSearchResults.innerHTML = '';
            if (!Array.isArray(campaigns) || campaigns.length === 0) {
                const empty = document.createElement('div');
                empty.className = 'campaign-search-empty';
                empty.textContent = 'Campaign tidak ditemukan.';
                campaignSearchResults.appendChild(empty);
                return;
            }

            campaigns.forEach((campaign) => {
                const item = document.createElement('div');
                item.className = 'campaign-search-item';

                const meta = document.createElement('div');
                meta.className = 'campaign-search-meta';
                meta.innerHTML = `
                    <div><strong>${campaign.id}</strong></div>
                    <div>Status: ${campaign.status} | Priority: ${campaign.priority}</div>
                    <div>Total: ${campaign.stats?.total ?? 0} | Sent: ${campaign.stats?.sent ?? 0} | Failed: ${campaign.stats?.failed ?? 0} | Pending: ${campaign.stats?.pending ?? 0}</div>
                `;

                const actions = document.createElement('div');
                actions.className = 'campaign-result-actions';

                const actionButtons = [
                    { target: 'pause', label: 'Ke Pause', className: 'warning' },
                    { target: 'resume', label: 'Ke Resume', className: 'success' },
                    { target: 'stop', label: 'Ke Soft', className: 'danger' },
                ];

                actionButtons.forEach((action) => {
                    const button = document.createElement('button');
                    button.type = 'button';
                    button.className = `campaign-btn ${action.className} tiny`;
                    button.textContent = action.label;
                    button.addEventListener('click', () => {
                        const campaignId = String(campaign.id || '');
                        if (campaignSearchInput) {
                            campaignSearchInput.value = campaignId;
                        }

                        applyCampaignIdToTarget(campaignId, action.target);
                        if (searchInput) {
                            searchInput.value = campaignId;
                        }
                        renderActivitiesWithCurrentFilter();
                    });
                    actions.appendChild(button);
                });

                item.appendChild(meta);
                item.appendChild(actions);
                campaignSearchResults.appendChild(item);
            });
        }

        async function searchCampaignsByUuid() {
            if (!campaignApiUrl || !campaignSearchResults) {
                return;
            }

            const keyword = (campaignSearchInput?.value || '').trim();
            campaignSearchResults.innerHTML = '<div class="campaign-search-empty">Mencari campaign...</div>';

            try {
                const response = await fetch(
                    `${campaignApiUrl}?channel=${encodeURIComponent(activityChannel)}&q=${encodeURIComponent(keyword)}`,
                    {
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                    }
                );

                if (!response.ok) {
                    throw new Error('Search failed');
                }

                const payload = await response.json();
                renderCampaignResults(payload.campaigns || []);
                syncCampaignClearButtons();
            } catch (error) {
                campaignSearchResults.innerHTML = '<div class="campaign-search-empty">Gagal mencari campaign.</div>';
            }
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

        if (campaignSearchBtn) {
            campaignSearchBtn.addEventListener('click', function () {
                searchCampaignsByUuid();
            });
        }

        if (campaignSearchInput) {
            campaignSearchInput.addEventListener('keydown', function (event) {
                if (event.key === 'Enter') {
                    event.preventDefault();
                    searchCampaignsByUuid();
                }
            });
        }

        campaignTargetInputs.forEach((input) => {
            input.addEventListener('input', function () {
                syncCampaignClearButtons();
            });
        });

        campaignClearButtons.forEach((button) => {
            button.addEventListener('click', function () {
                const target = button.getAttribute('data-clear-target');
                const input = campaignTargetInputs.find((item) => item.getAttribute('data-target-action') === target);
                if (!input) {
                    return;
                }

                input.value = '';
                syncCampaignClearButtons();
                input.focus();
            });
        });

        // === KEY FIX: FORM SUBMISSION FROM CODE PERTAMA ===
        // In Code Pertama, form submission works because it doesn't have e.preventDefault()
        // We'll keep the validation but remove the preventDefault() that blocks form submission
        
        const emailForm = document.getElementById('emailForm');
        renderRecipientMessageMatrix();
        
        if (emailForm) {
            emailForm.addEventListener('submit', function(e) {
                const activeOverrides = syncMessageOverridesField();
                const selectedDbRecipients = Array.from(
                    document.querySelectorAll('.recipient-db-checkbox:checked')
                );
                const hasDbRecipients = selectedDbRecipients.length > 0;
                const hasManualTargets = emails.length > 0;
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
                    alert('Isi Email global wajib diisi jika ada penerima dengan mode Global.');
                    messageTextarea.focus();
                    return;
                }

                if (!hasDbRecipients && !hasManualTargets) {
                    e.preventDefault();
                    alert('Pilih recipient dari DB atau tambahkan email manual terlebih dahulu!');
                    emailInput.focus();
                    return;
                }

                if (!emailSubject.value.trim()) {
                    e.preventDefault();
                    alert('Masukkan subject email terlebih dahulu!');
                    emailSubject.focus();
                    return;
                }

                if (!hasDbTemplate && !hasGlobalMessage && !hasPerRecipientContent) {
                    e.preventDefault();
                    alert('Masukkan isi pesan, pilih template, atau atur pesan khusus per penerima!');
                    messageTextarea.focus();
                    return;
                }

                const rateLimitValue = Number(rateLimitInput?.value || 0);
                const batchSizeValue = Number(batchSizeInput?.value || 0);
                const batchDelayValue = Number(batchDelayInput?.value || 0);
                const retryAttemptsValue = Number(retryAttemptsInput?.value || 0);

                if (!Number.isFinite(rateLimitValue) || rateLimitValue < 1) {
                    e.preventDefault();
                    alert('Rate per menit minimal 1.');
                    rateLimitInput?.focus();
                    return;
                }

                if (!Number.isFinite(batchSizeValue) || batchSizeValue < 1) {
                    e.preventDefault();
                    alert('Batch size minimal 1.');
                    batchSizeInput?.focus();
                    return;
                }

                if (!Number.isFinite(batchDelayValue) || batchDelayValue < 0) {
                    e.preventDefault();
                    alert('Delay antar batch tidak boleh negatif.');
                    batchDelayInput?.focus();
                    return;
                }

                if (!Number.isFinite(retryAttemptsValue) || retryAttemptsValue < 1) {
                    e.preventDefault();
                    alert('Max retry minimal 1.');
                    retryAttemptsInput?.focus();
                    return;
                }

                const retryBackoffRaw = String(retryBackoffInput?.value || '').trim();
                if (retryBackoffRaw !== '') {
                    const allValid = retryBackoffRaw
                        .split(',')
                        .map((item) => item.trim())
                        .filter((item) => item !== '')
                        .every((item) => /^\d+$/.test(item));

                    if (!allValid) {
                        e.preventDefault();
                        alert('Format backoff retry harus angka yang dipisahkan koma, contoh: 30,120,300');
                        retryBackoffInput?.focus();
                        return;
                    }
                }

                let scheduleInfo = 'Campaign akan dikirim segera.';
                if (scheduledAtInput && scheduledAtInput.value) {
                    const scheduleDate = new Date(scheduledAtInput.value);
                    if (Number.isNaN(scheduleDate.getTime())) {
                        e.preventDefault();
                        alert('Format jadwal kirim tidak valid.');
                        scheduledAtInput.focus();
                        return;
                    }

                    scheduleInfo = `Campaign dijadwalkan pada ${scheduledAtInput.value}.`;
                }

                const selectedTargets = hasManualTargets
                    ? [...emails]
                    : selectedDbRecipients.map((cb) => cb.getAttribute('data-email') || cb.value);

                const confirmation = confirm(
                    `${scheduleInfo}\nPriority: ${priorityInput?.value || 'normal'}\nEmail akan diproses ke ${selectedTargets.length} penerima. Lanjutkan?`
                );

                if (!confirmation) {
                    e.preventDefault();
                    return false;
                }

                sendButton.disabled = true;
                sendButton.innerHTML = '<span>Mengirim Blast Email...</span>';
            });
        }

        // Initial render
        renderActivitiesWithCurrentFilter();
        updateStats();
        filterRecipientDbList();
        searchCampaignsByUuid();
        syncCampaignClearButtons();
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

