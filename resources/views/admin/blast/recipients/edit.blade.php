@extends('layouts.app')
@section('title', 'Edit Data Siswa')
@section('content')

<style>
    /* ===== VARIABLES ===== */
    :root {
        --primary-color: #3b82f6;
        --primary-hover: #2563eb;
        --secondary-color: #64748b;
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
        grid-template-columns: 1fr;
        gap: 24px;
        margin-bottom: 24px;
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
        background: #10b981;
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
                <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
            </svg>
        </div>
        <div class="header-content">
            <h1>Edit Data Siswa</h1>
            <p>Perbarui data penerima WhatsApp & Email</p>
        </div>
    </div>

    <div class="form-section">
        {{-- FORM EDIT --}}
        <div class="form-card">
            <div class="card-header">
                <strong>Form Edit Data Penerima</strong>
                <div class="subtitle">Perbarui informasi yang diperlukan untuk data penerima</div>
            </div>

            <div class="card-body">
                <form method="POST"
                      action="{{ route('admin.blast.recipients.update', $recipient->id) }}"
                      id="recipientForm">
                    @csrf
                    @method('PUT')

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
                                   value="{{ old('nama_siswa', $recipient->nama_siswa) }}"
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
                                   value="{{ old('kelas', $recipient->kelas) }}"
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
                                   value="{{ old('nama_wali', $recipient->nama_wali) }}"
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
                                   value="{{ old('wa_wali', $recipient->wa_wali) }}">
                            @if($errors->has('wa_wali'))
                                <div class="error-message">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="14" height="14">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                                    </svg>
                                    {{ $errors->first('wa_wali') }}
                                </div>
                            @endif
                            <div class="form-hint">
                                Gunakan format: +62 812 3456 789 (maksimal 13 digit setelah +62)
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
                                   value="{{ old('email_wali', $recipient->email_wali) }}">
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
                                      placeholder="Tambahkan catatan jika diperlukan...">{{ old('catatan', $recipient->catatan) }}</textarea>
                            @if($errors->has('catatan'))
                                <div class="error-message">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="14" height="14">
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
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 3.75V16.5L12 14.25 7.5 16.5V3.75m9 0H18A2.25 2.25 0 0120.25 6v12A2.25 2.25 0 0118 20.25H6A2.25 2.25 0 013.75 18V6A2.25 2.25 0 016 3.75h1.5m9 0h-9" />
                            </svg>
                            Perbarui Data
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
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

    // Format phone number input dengan +62 (maksimal 13 digit setelah kode negara)
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
            
            // Batasi panjang maksimal: 2 (kode negara) + 13 = 15 digit total
            const maxLength = 15; // 62 + 13 digit
            if (value.length > maxLength) {
                value = value.substring(0, maxLength);
            }
            
            // Format: +62 xxx xxx xxxx
            let formattedValue = '';
            if (value.length > 0) {
                formattedValue = '+' + value.substring(0, 2); // +62
                
                if (value.length > 2) {
                    // Ambil maksimal 13 digit setelah +62
                    const digits = value.substring(2, Math.min(value.length, 15));
                    
                    // Format dengan spasi setiap 3-4 digit
                    if (digits.length <= 3) {
                        formattedValue += ' ' + digits;
                    } else if (digits.length <= 6) {
                        formattedValue += ' ' + digits.substring(0, 3) + ' ' + digits.substring(3);
                    } else if (digits.length <= 9) {
                        formattedValue += ' ' + digits.substring(0, 3) + ' ' + digits.substring(3, 6) + ' ' + digits.substring(6);
                    } else if (digits.length <= 13) {
                        formattedValue += ' ' + digits.substring(0, 3) + ' ' + digits.substring(3, 6) + ' ' + digits.substring(6, 9) + ' ' + digits.substring(9, 13);
                    }
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
            
            // Batasi panjang maksimal: 2 (kode negara) + 13 = 15 digit total
            const maxLength = 15;
            if (value.length > maxLength) {
                value = value.substring(0, maxLength);
            }
            
            let formattedValue = '';
            if (value.length > 0) {
                formattedValue = '+' + value.substring(0, 2);
                
                if (value.length > 2) {
                    // Ambil maksimal 13 digit setelah +62
                    const digits = value.substring(2, Math.min(value.length, 15));
                    
                    // Format dengan spasi setiap 3-4 digit
                    if (digits.length <= 3) {
                        formattedValue += ' ' + digits;
                    } else if (digits.length <= 6) {
                        formattedValue += ' ' + digits.substring(0, 3) + ' ' + digits.substring(3);
                    } else if (digits.length <= 9) {
                        formattedValue += ' ' + digits.substring(0, 3) + ' ' + digits.substring(3, 6) + ' ' + digits.substring(6);
                    } else if (digits.length <= 13) {
                        formattedValue += ' ' + digits.substring(0, 3) + ' ' + digits.substring(3, 6) + ' ' + digits.substring(6, 9) + ' ' + digits.substring(9, 13);
                    }
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