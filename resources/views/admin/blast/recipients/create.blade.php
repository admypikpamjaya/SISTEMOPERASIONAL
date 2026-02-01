@extends('layouts.app')
@section('title', 'Tambah Data Siswa')
@section('content')

<style>
    /* ===== VARIABLES ===== */
    :root {
        --primary-color: #3b82f6;
        --primary-hover: #2563eb;
        --secondary-color: #64748b;
        --success-color: #10b981;
        --danger-color: #ef4444;
        --light-bg: #f8fafc;
        --border-color: #e2e8f0;
        --text-primary: #1e293b;
        --text-secondary: #64748b;
        --card-shadow: 0 2px 4px rgba(0,0,0,.04), 0 1px 2px rgba(0,0,0,.06);
        --card-shadow-hover: 0 4px 12px rgba(0,0,0,.08);
    }

    /* ===== PAGE HEADER ===== */
    .page-header {
        margin-bottom: 32px;
        display: flex;
        align-items: flex-start;
        gap: 20px;
    }

    .header-icon {
        flex-shrink: 0;
        width: 64px;
        height: 64px;
        background: white;
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--primary-color);
        box-shadow: var(--card-shadow);
        border: 1px solid var(--border-color);
    }

    .header-icon svg {
        width: 32px;
        height: 32px;
    }

    .header-content {
        flex: 1;
    }

    .header-content h1 {
        font-size: 28px;
        font-weight: 700;
        color: var(--text-primary);
        margin-bottom: 8px;
        line-height: 1.2;
    }

    .header-content p {
        font-size: 15px;
        color: var(--text-secondary);
        line-height: 1.5;
        max-width: 600px;
    }

    /* ===== FORM SECTION ===== */
    .form-section {
        display: grid;
        grid-template-columns: 1fr 400px;
        gap: 24px;
        margin-bottom: 24px;
    }

    @media (max-width: 992px) {
        .form-section {
            grid-template-columns: 1fr;
        }
    }

    /* ===== MAIN FORM CARD ===== */
    .form-card {
        background: white;
        border-radius: 20px;
        border: 1px solid var(--border-color);
        box-shadow: var(--card-shadow);
        overflow: hidden;
        transition: all 0.3s ease;
    }

    .form-card:hover {
        box-shadow: var(--card-shadow-hover);
    }

    .card-header {
        padding: 24px;
        border-bottom: 1px solid var(--border-color);
        background: var(--light-bg);
    }

    .card-header strong {
        font-size: 18px;
        font-weight: 600;
        color: var(--text-primary);
        display: block;
        margin-bottom: 6px;
    }

    .card-header .subtitle {
        font-size: 14px;
        color: var(--text-secondary);
        line-height: 1.4;
    }

    .card-body {
        padding: 32px;
    }

    /* ===== FORM STYLES ===== */
    .form-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 24px;
        margin-bottom: 32px;
    }

    @media (max-width: 768px) {
        .form-grid {
            grid-template-columns: 1fr;
        }
    }

    .form-group {
        display: flex;
        flex-direction: column;
        position: relative;
    }

    .form-group.full {
        grid-column: span 2;
    }

    @media (max-width: 768px) {
        .form-group.full {
            grid-column: span 1;
        }
    }

    .form-group label {
        font-size: 14px;
        font-weight: 600;
        color: var(--text-primary);
        margin-bottom: 10px;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .form-group label::after {
        content: "*";
        color: var(--danger-color);
        font-size: 12px;
        margin-left: 2px;
    }

    .form-group label[for="catatan"]::after,
    .form-group label[for="email_wali"]::after,
    .form-group label[for="wa_wali"]::after {
        content: "";
        display: none;
    }

    .form-group input,
    .form-group textarea {
        padding: 14px 16px;
        border: 1.5px solid var(--border-color);
        border-radius: 12px;
        font-size: 15px;
        color: var(--text-primary);
        background: white;
        transition: all 0.2s ease;
        width: 100%;
        box-sizing: border-box;
    }

    .form-group input:focus,
    .form-group textarea:focus {
        outline: none;
        border-color: var(--primary-color);
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    }

    .form-group input::placeholder,
    .form-group textarea::placeholder {
        color: #94a3b8;
        font-size: 14px;
    }

    .form-group textarea {
        min-height: 100px;
        resize: vertical;
        line-height: 1.5;
        font-family: inherit;
    }

    .form-group .form-hint {
        font-size: 12px;
        color: var(--text-secondary);
        margin-top: 8px;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .form-hint svg {
        width: 14px;
        height: 14px;
        flex-shrink: 0;
    }

    /* ===== FORM ACTIONS ===== */
    .form-actions {
        display: flex;
        justify-content: flex-end;
        gap: 12px;
        padding-top: 24px;
        border-top: 1px solid var(--border-color);
        margin-top: 24px;
    }

    .btn {
        padding: 12px 28px;
        border-radius: 10px;
        font-size: 15px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s ease;
        border: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        text-decoration: none;
        min-width: 120px;
    }

    .btn-secondary {
        background: white;
        color: var(--text-secondary);
        border: 1.5px solid var(--border-color);
    }

    .btn-secondary:hover {
        background: var(--light-bg);
        border-color: var(--secondary-color);
        transform: translateY(-1px);
    }

    .btn-primary {
        background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-hover) 100%);
        color: white;
        border: none;
    }

    .btn-primary:hover {
        background: linear-gradient(135deg, var(--primary-hover) 0%, #1d4ed8 100%);
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(37, 99, 235, 0.2);
    }

    .btn-primary svg {
        width: 16px;
        height: 16px;
    }

    /* ===== IMPORT CARD ===== */
    .import-card {
        background: white;
        border-radius: 20px;
        border: 1px solid var(--border-color);
        box-shadow: var(--card-shadow);
        overflow: hidden;
        height: fit-content;
        position: sticky;
        top: 24px;
    }

    .import-card .card-header {
        background: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%);
        border-bottom: 1px solid #a7f3d0;
    }

    .import-card .card-header strong {
        color: #065f46;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .import-card .card-header strong svg {
        width: 20px;
        height: 20px;
        color: #10b981;
    }

    .import-card .card-body {
        padding: 24px;
    }

    .import-form {
        display: flex;
        flex-direction: column;
        gap: 20px;
    }

    .file-input-wrapper {
        position: relative;
        border: 2px dashed #cbd5e1;
        border-radius: 12px;
        padding: 40px 20px;
        text-align: center;
        transition: all 0.3s ease;
        background: var(--light-bg);
        cursor: pointer;
    }

    .file-input-wrapper:hover {
        border-color: var(--primary-color);
        background: rgba(59, 130, 246, 0.05);
    }

    .file-input-wrapper.dragover {
        border-color: var(--primary-color);
        background: rgba(59, 130, 246, 0.1);
    }

    .file-input-wrapper svg {
        width: 48px;
        height: 48px;
        color: #94a3b8;
        margin-bottom: 16px;
    }

    .file-input-wrapper h4 {
        font-size: 16px;
        font-weight: 600;
        color: var(--text-primary);
        margin-bottom: 8px;
    }

    .file-input-wrapper p {
        font-size: 14px;
        color: var(--text-secondary);
        margin-bottom: 16px;
    }

    .file-input-wrapper input[type="file"] {
        position: absolute;
        width: 100%;
        height: 100%;
        top: 0;
        left: 0;
        opacity: 0;
        cursor: pointer;
    }

    .btn-success {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        color: white;
        width: 100%;
        padding: 14px 24px;
        font-weight: 600;
    }

    .btn-success:hover {
        background: linear-gradient(135deg, #059669 0%, #047857 100%);
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(16, 185, 129, 0.2);
    }

    .btn-success svg {
        width: 18px;
        height: 18px;
    }

    /* ===== TEMPLATE INFO ===== */
    .template-info {
        margin-top: 24px;
        padding: 20px;
        background: #fff7ed;
        border-radius: 12px;
        border: 1px solid #fed7aa;
    }

    .template-info h5 {
        font-size: 15px;
        font-weight: 600;
        color: #c2410c;
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 12px;
    }

    .template-info h5 svg {
        width: 18px;
        height: 18px;
    }

    .template-info ul {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .template-info li {
        font-size: 13px;
        color: #92400e;
        margin-bottom: 6px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .template-info li svg {
        width: 14px;
        height: 14px;
        flex-shrink: 0;
        color: #f97316;
    }

    /* ===== VALIDATION ERROR ===== */
    .error-message {
        font-size: 12px;
        color: var(--danger-color);
        margin-top: 6px;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .form-group.has-error input,
    .form-group.has-error textarea {
        border-color: var(--danger-color);
    }

    .form-group.has-error input:focus,
    .form-group.has-error textarea:focus {
        box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1);
    }

    /* ===== TOAST NOTIFICATION ===== */
    .toast {
        position: fixed;
        top: 24px;
        right: 24px;
        background: var(--success-color);
        color: white;
        padding: 16px 24px;
        border-radius: 12px;
        box-shadow: 0 8px 24px rgba(0,0,0,.1);
        display: flex;
        align-items: center;
        gap: 12px;
        z-index: 10000;
        animation: slideIn 0.3s ease;
        min-width: 300px;
        max-width: 400px;
    }

    .toast-error {
        background: linear-gradient(135deg, var(--danger-color) 0%, #dc2626 100%);
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

    /* ===== BACK BUTTON ===== */
    .back-link {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        color: var(--text-secondary);
        text-decoration: none;
        font-size: 14px;
        font-weight: 500;
        margin-bottom: 20px;
        padding: 8px 12px;
        border-radius: 8px;
        transition: all 0.2s ease;
    }

    .back-link:hover {
        color: var(--primary-color);
        background: rgba(59, 130, 246, 0.1);
    }

    .back-link svg {
        width: 16px;
        height: 16px;
    }
</style>

<div class="recipient-form-wrapper">
    {{-- Back Button --}}
    <a href="{{ route('admin.blast.recipients.index') }}" class="back-link">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
        </svg>
        Kembali ke Recipient Data
    </a>

    {{-- Page Header --}}
    <div class="page-header">
        <div class="header-icon">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zM4 19.235v-.11a6.375 6.375 0 0112.75 0v.109A12.318 12.318 0 0110.374 21c-2.331 0-4.512-.645-6.374-1.766z" />
            </svg>
        </div>
        <div class="header-content">
            <h1>Tambah Data Siswa</h1>
            <p>Input data penerima WhatsApp & Email secara manual atau impor melalui file Excel</p>
        </div>
    </div>

    <div class="form-section">
        {{-- FORM MANUAL --}}
        <div class="form-card">
            <div class="card-header">
                <strong>Form Input Manual</strong>
                <div class="subtitle">Isi semua informasi yang diperlukan untuk menambahkan data penerima baru</div>
            </div>

            <div class="card-body">
                <form action="{{ route('admin.blast.recipients.store') }}" method="POST" id="recipientForm">
                    @csrf

                    <div class="form-grid">
                        {{-- Nama Siswa --}}
                        <div class="form-group {{ $errors->has('nama_siswa') ? 'has-error' : '' }}">
                            <label for="nama_siswa">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="16" height="16">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                                </svg>
                                Nama Siswa
                            </label>
                            <input type="text" 
                                   name="nama_siswa" 
                                   id="nama_siswa" 
                                   placeholder="Masukkan nama siswa"
                                   value="{{ old('nama_siswa') }}"
                                   required>
                            @if($errors->has('nama_siswa'))
                                <div class="error-message">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="14" height="14">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                                    </svg>
                                    {{ $errors->first('nama_siswa') }}
                                </div>
                            @endif
                            <div class="form-hint">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="14" height="14">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" />
                                </svg>
                                Contoh: Audy Sava
                            </div>
                        </div>

                        {{-- Kelas --}}
                        <div class="form-group {{ $errors->has('kelas') ? 'has-error' : '' }}">
                            <label for="kelas">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="16" height="16">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.436 60.436 0 00-.491 6.347A48.627 48.627 0 0112 20.904a48.627 48.627 0 018.232-4.41 60.46 60.46 0 00-.491-6.347m-15.482 0a50.57 50.57 0 00-2.658-.813A59.905 59.905 0 0112 3.493a59.902 59.902 0 0110.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.697 50.697 0 0112 13.489a50.702 50.702 0 017.74-3.342M6.75 15a.75.75 0 100-1.5.75.75 0 000 1.5zm0 0v-3.675A55.378 55.378 0 0112 8.443m-7.007 11.55A5.981 5.981 0 006.75 15.75v-1.5" />
                                </svg>
                                Kelas
                            </label>
                            <input type="text" 
                                   name="kelas" 
                                   id="kelas" 
                                   placeholder="Contoh: 3A"
                                   value="{{ old('kelas') }}"
                                   required>
                            @if($errors->has('kelas'))
                                <div class="error-message">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="14" height="14">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                                    </svg>
                                    {{ $errors->first('kelas') }}
                                </div>
                            @endif
                            <div class="form-hint">
                                Format: Tingkat Kelas
                            </div>
                        </div>

                        {{-- Nama Wali --}}
                        <div class="form-group {{ $errors->has('nama_wali') ? 'has-error' : '' }}">
                            <label for="nama_wali">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="16" height="16">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                                </svg>
                                Nama Wali
                            </label>
                            <input type="text" 
                                   name="nama_wali" 
                                   id="nama_wali" 
                                   placeholder="Masukkan nama wali"
                                   value="{{ old('nama_wali') }}"
                                   required>
                            @if($errors->has('nama_wali'))
                                <div class="error-message">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="14" height="14">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                                    </svg>
                                    {{ $errors->first('nama_wali') }}
                                </div>
                            @endif
                        </div>

                        {{-- WhatsApp Wali --}}
                        <div class="form-group {{ $errors->has('wa_wali') ? 'has-error' : '' }}">
                            <label for="wa_wali">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="16" height="16">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 002.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 01-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 00-1.091-.852H4.5A2.25 2.25 0 002.25 4.5v2.25z" />
                                </svg>
                                WhatsApp Wali
                            </label>
                            <input type="text" 
                                   name="wa_wali" 
                                   id="wa_wali" 
                                   placeholder="+62 812 3456 789"
                                   value="{{ old('wa_wali') }}">
                            @if($errors->has('wa_wali'))
                                <div class="error-message">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="14" height="14">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                                    </svg>
                                    {{ $errors->first('wa_wali') }}
                                </div>
                            @endif
                            <div class="form-hint">
                                Gunakan format: +62 812 3456 789
                            </div>
                        </div>

                        {{-- Email Wali --}}
                        <div class="form-group {{ $errors->has('email_wali') ? 'has-error' : '' }}">
                            <label for="email_wali">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="16" height="16">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" />
                                </svg>
                                Email Wali
                            </label>
                            <input type="email" 
                                   name="email_wali" 
                                   id="email_wali" 
                                   placeholder="email@contoh.com"
                                   value="{{ old('email_wali') }}">
                            @if($errors->has('email_wali'))
                                <div class="error-message">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="14" height="14">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                                    </svg>
                                    {{ $errors->first('email_wali') }}
                                </div>
                            @endif
                            <div class="form-hint">
                                Pastikan email aktif untuk menerima blasting
                            </div>
                        </div>

                        {{-- Catatan --}}
                        <div class="form-group full {{ $errors->has('catatan') ? 'has-error' : '' }}">
                            <label for="catatan">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="16" height="16">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                                </svg>
                                Catatan (Opsional)
                            </label>
                            <textarea name="catatan" 
                                      id="catatan" 
                                      placeholder="Tambahkan catatan jika diperlukan...">{{ old('catatan') }}</textarea>
                            @if($errors->has('catatan'))
                                <div class="error-message">
                                    <svg xmlns="http://www.w3.org2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="14" height="14">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                                    </svg>
                                    {{ $errors->first('catatan') }}
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="form-actions">
                        <a href="{{ route('admin.blast.recipients.index') }}" class="btn btn-secondary">
                            Batal
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="18" height="18">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                            </svg>
                            Tambah Data
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- IMPORT EXCEL --}}
        <div class="import-card">
            <div class="card-header">
                <strong>
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="20" height="20">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5" />
                    </svg>
                    Import Data via Excel
                </strong>
                <div class="subtitle">Unggah file Excel untuk impor data secara massal</div>
            </div>

            <div class="card-body">
                <form action="{{ route('admin.blast.recipients.import') }}"
                      method="POST"
                      enctype="multipart/form-data"
                      class="import-form"
                      id="importForm">
                    @csrf
                    
                    <div class="file-input-wrapper" id="fileDropZone">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m6.75 12l-3-3m0 0l-3 3m3-3v6m-1.5-15H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                        </svg>
                        <h4>Unggah File Excel</h4>
                        <p>Klik untuk memilih file atau tarik file ke sini</p>
                        <div style="font-size: 12px; color: #94a3b8;">
                            Format yang didukung: .xlsx, .xls, .csv
                        </div>
                        <input type="file" name="file" id="excelFile" accept=".xlsx,.xls,.csv" required>
                    </div>

                    <button type="submit" class="btn btn-success" id="importBtn">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="18" height="18">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Impor Excel
                    </button>
                </form>

                {{-- Template Info --}}
                <div class="template-info">
                    <h5>
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25zM6.75 12h.008v.008H6.75V12zm0 3h.008v.008H6.75V15zm0 3h.008v.008H6.75V18z" />
                        </svg>
                        Format Excel yang Disarankan
                    </h5>
                    <ul>
                        <li>
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                            </svg>
                            Kolom 1: Nama Siswa
                        </li>
                        <li>
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                            </svg>
                            Kolom 2: Kelas
                        </li>
                        <li>
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                            </svg>
                            Kolom 3: Nama Wali
                        </li>
                        <li>
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                            </svg>
                            Kolom 4: WhatsApp Wali
                        </li>
                        <li>
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                            </svg>
                            Kolom 5: Email Wali
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // File drop zone functionality
    const fileDropZone = document.getElementById('fileDropZone');
    const excelFile = document.getElementById('excelFile');
    const importForm = document.getElementById('importForm');
    const importBtn = document.getElementById('importBtn');

    if (fileDropZone && excelFile) {
        // Click to select file
        fileDropZone.addEventListener('click', function(e) {
            if (e.target !== excelFile) {
                excelFile.click();
            }
        });

        // Drag and drop functionality
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            fileDropZone.addEventListener(eventName, preventDefaults, false);
        });

        function preventDefaults(e) {
            e.preventDefault();
            e.stopPropagation();
        }

        ['dragenter', 'dragover'].forEach(eventName => {
            fileDropZone.addEventListener(eventName, highlight, false);
        });

        ['dragleave', 'drop'].forEach(eventName => {
            fileDropZone.addEventListener(eventName, unhighlight, false);
        });

        function highlight() {
            fileDropZone.classList.add('dragover');
        }

        function unhighlight() {
            fileDropZone.classList.remove('dragover');
        }

        // Handle file drop
        fileDropZone.addEventListener('drop', function(e) {
            const dt = e.dataTransfer;
            const files = dt.files;
            
            if (files.length > 0) {
                excelFile.files = files;
                updateFileDisplay(files[0]);
            }
        });

        // Handle file selection
        excelFile.addEventListener('change', function(e) {
            if (this.files.length > 0) {
                updateFileDisplay(this.files[0]);
            }
        });

        function updateFileDisplay(file) {
            const fileInfo = fileDropZone.querySelector('p');
            const fileName = document.createElement('div');
            fileName.style.fontSize = '13px';
            fileName.style.fontWeight = '600';
            fileName.style.color = '#1e293b';
            fileName.style.marginTop = '8px';
            fileName.innerHTML = `
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="14" height="14" style="margin-right: 6px; vertical-align: middle;">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                </svg>
                ${file.name} (${formatFileSize(file.size)})
            `;
            
            // Remove existing file name if any
            const existingFileName = fileDropZone.querySelector('.file-name-display');
            if (existingFileName) {
                existingFileName.remove();
            }
            
            fileName.classList.add('file-name-display');
            fileDropZone.appendChild(fileName);
            
            // Update import button text
            if (importBtn) {
                importBtn.innerHTML = `
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="18" height="18">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Import "${file.name}"
                `;
            }
        }

        function formatFileSize(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        }
    }

    // Form validation
    const recipientForm = document.getElementById('recipientForm');
    if (recipientForm) {
        recipientForm.addEventListener('submit', function(e) {
            const requiredFields = recipientForm.querySelectorAll('[required]');
            let isValid = true;
            
            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    isValid = false;
                    field.style.borderColor = '#ef4444';
                }
            });
            
            if (!isValid) {
                e.preventDefault();
                showToast('Harap isi semua field yang wajib diisi', 'error');
            }
        });
    }

    // Toast notification
    function showToast(message, type = 'success') {
        // Remove existing toasts
        document.querySelectorAll('.toast').forEach(toast => toast.remove());
        
        let icon = '';
        let className = 'toast';
        
        if (type === 'success') {
            icon = `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="20" height="20"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>`;
        } else if (type === 'error') {
            icon = `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="20" height="20"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" /></svg>`;
            className += ' toast-error';
        }
        
        const toast = document.createElement('div');
        toast.className = className;
        toast.innerHTML = `
            ${icon}
            <div style="flex: 1">${message}</div>
            <button onclick="this.parentElement.remove()" style="background:none;border:none;color:white;cursor:pointer;font-size:20px;opacity:0.7">×</button>
        `;
        
        document.body.appendChild(toast);
        
        setTimeout(() => {
            if (toast.parentElement) {
                toast.remove();
            }
        }, 4000);
    }

    // Format phone number input dengan +62
    const phoneInput = document.getElementById('wa_wali');
    if (phoneInput) {
        phoneInput.addEventListener('input', function(e) {
            // Hapus semua karakter non-digit
            let value = e.target.value.replace(/\D/g, '');
            
            // Jika diawali dengan 0, ubah menjadi 62
            if (value.startsWith('0')) {
                value = '62' + value.substring(1);
            }
            
            // Jika belum ada kode negara, tambahkan 62
            if (!value.startsWith('62') && value.length > 0) {
                value = '62' + value;
            }
            
            // Format: +62 xxx xxx xxxx
            let formattedValue = '';
            if (value.length > 0) {
                formattedValue = '+' + value.substring(0, 2); // +62
                
                if (value.length > 2) {
                    formattedValue += ' ' + value.substring(2, 5); // spasi + 3 digit pertama
                }
                if (value.length > 5) {
                    formattedValue += ' ' + value.substring(5, 8); // spasi + 3 digit berikutnya
                }
                if (value.length > 8) {
                    formattedValue += ' ' + value.substring(8, 12); // spasi + 4 digit terakhir
                }
            }
            
            e.target.value = formattedValue;
        });
        
        // Juga format saat halaman dimuat jika ada value lama
        if (phoneInput.value) {
            let value = phoneInput.value.replace(/\D/g, '');
            
            if (value.startsWith('0')) {
                value = '62' + value.substring(1);
            }
            
            if (!value.startsWith('62') && value.length > 0) {
                value = '62' + value;
            }
            
            let formattedValue = '';
            if (value.length > 0) {
                formattedValue = '+' + value.substring(0, 2);
                
                if (value.length > 2) {
                    formattedValue += ' ' + value.substring(2, 5);
                }
                if (value.length > 5) {
                    formattedValue += ' ' + value.substring(5, 8);
                }
                if (value.length > 8) {
                    formattedValue += ' ' + value.substring(8, 12);
                }
            }
            
            phoneInput.value = formattedValue;
        }
    }

    // Show validation errors from server
    @if($errors->any())
        setTimeout(() => {
            showToast('Terdapat kesalahan dalam pengisian form. Harap periksa kembali.', 'error');
        }, 500);
    @endif

    // Show success message
    @if(session('success'))
        setTimeout(() => {
            showToast('{{ session('success') }}', 'success');
        }, 500);
    @endif

    // Show error message
    @if(session('error'))
        setTimeout(() => {
            showToast('{{ session('error') }}', 'error');
        }, 500);
    @endif
});
</script>

@endsection