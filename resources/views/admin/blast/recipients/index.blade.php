@extends('layouts.app')
@section('title', 'Student Data')
@section('content')

<style>
    body {background: #f8fafc; font-family: 'Segoe UI', system-ui, sans-serif; color: #334155; margin: 0; padding: 0;}
    .container-fluid {padding: 10px; max-width: 1400px; margin: 0 auto;}
    
    .header {
        display: flex;
        align-items: flex-start;
        gap: 24px;
        margin-bottom: 28px;
    }
    
    .header-icon {
        flex-shrink: 0;
        width: 65px;
        height: 65px;
        color: #3b82f6;
        background: white;
        padding: 13px;
        border-radius: 16px;
        box-shadow: 0 4px 12px rgba(59, 130, 246, 0.15);
        border: 1px solid #e2e8f0;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .header-icon svg {
        width: 48px;
        height: 48px;
    }
    
    .header-content {
        flex: 1;
    }
    
    .header-title {
        font-size: 25px;
        font-weight: 700;
        color: #1e293b;
        margin-bottom: 9px;
        line-height: 1.2;
        display: flex;
        align-items: center;
        gap: 12px;
    }
    
    .header-subtitle {
        font-size: 15px;
        color: #64748b;
        line-height: 1.4;
    }
    
    .stats-row {display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-bottom: 24px;}
    .stat-card {background: white; border-radius: 16px; padding: 20px; border: 1px solid #e2e8f0; box-shadow: 0 2px 4px rgba(0,0,0,.04); transition: all 0.2s ease; display: flex; align-items: center; gap: 16px; height: 100px;}
    .stat-card:hover {border-color: #cbd5e1; box-shadow: 0 4px 12px rgba(0,0,0,.06);}
    .stat-icon {width: 48px; height: 48px; border-radius: 12px; display: flex; align-items: center; justify-content: center;}
    .stat-icon svg {width: 20px; height: 20px;}
    .stat-icon-total {background: #eff6ff;}
    .stat-icon-total svg {color: #3b82f6;}
    .stat-icon-lengkap {background: #f0fdf4;}
    .stat-icon-lengkap svg {color: #16a34a;}
    .stat-icon-kurang {background: #fefce8;}
    .stat-icon-kurang svg {color: #ca8a04;}
    .stat-icon-valid {background: #ecfdf5;}
    .stat-icon-valid svg {color: #10b981;}
    .stat-content {flex: 1;}
    .stat-label {font-size: 13px; color: #64748b; font-weight: 500; margin-bottom: 4px;}
    .stat-number {font-size: 28px; font-weight: 700; color: #1e293b; line-height: 1;}
    
    .data-section {
        display: flex;
        flex-direction: column;
        gap: 20px;
    }
    
    .table-card {background: white; border-radius: 16px; border: 1px solid #e2e8f0; box-shadow: 0 2px 4px rgba(0,0,0,.04); overflow: hidden; min-height: 400px;}
    .table-header {padding: 20px; border-bottom: 1px solid #e2e8f0;}
    .search-container {display: flex; gap: 12px;}
    .search-box {flex: 1; border: 1px solid #cbd5e1; border-radius: 10px; padding: 12px 16px 12px 46px; font-size: 14px; background: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='%2394a3b8' viewBox='0 0 16 16'%3E%3Cpath d='M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001c.03.04.062.078.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1.007 1.007 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0z'/%3E%3C/svg%3E") no-repeat 16px center/16px;}
    .search-box:focus {outline: none; border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59,130,246,0.1);}
    .search-box::placeholder {color: #94a3b8;}
    .btn-import, .btn-add {padding: 12px 16px; border-radius: 10px; font-size: 14px; font-weight: 500; cursor: pointer; display: flex; align-items: center; gap: 8px; transition: all 0.2s ease; white-space: nowrap;}
    .btn-import {border: 1px dashed #cbd5e1; background: white; color: #475569; position: relative;}
    .btn-import:hover {background: #f8fafc; border-color: #94a3b8;}
    .file-input {position: absolute; width: 100%; height: 100%; top: 0; left: 0; opacity: 0; cursor: pointer;}
    .btn-add {background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); color: white; border: none;}
    .btn-add:hover {background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%); transform: translateY(-1px); box-shadow: 0 4px 12px rgba(37,99,235,0.2);}
    .table-wrapper {overflow-x: auto; padding: 0 20px 20px;}
    .table {width: 100%; border-collapse: collapse; min-width: 1000px;}
    .table thead th {background: #f8fafc; padding: 14px 10px; text-align: left; font-size: 11px; font-weight: 600; color: #475569; text-transform: uppercase; letter-spacing: 0.5px; border-bottom: 1px solid #e2e8f0; white-space: nowrap;}
    .table tbody td {padding: 14px 10px; font-size: 13px; color: #334155; border-bottom: 1px solid #f1f5f9; vertical-align: top;}
    .table tbody tr:last-child td {border-bottom: none;}
    .table tbody tr:hover {background: #f8fafc;}
    .student-name {font-weight: 500; color: #1e293b; margin-bottom: 4px; line-height: 1.4; max-width: 120px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;}
    .student-class {display: inline-flex; background: #f1f5f9; padding: 4px 8px; border-radius: 6px; font-size: 12px; color: #475569; font-weight: 600;}
    .student-info, .student-phone, .student-email, .student-catatan {line-height: 1.4; word-break: break-word; font-size: 12px; overflow: hidden; text-overflow: ellipsis; max-height: 60px;}
    .student-info {max-width: 100px;}
    .student-phone {max-width: 100px; font-family: 'SF Mono', Monaco, monospace; font-size: 11px;}
    .student-email {max-width: 130px; font-size: 11px;}
    .student-catatan {max-width: 120px; color: #64748b; font-style: italic; font-size: 11px;}
    .badge-status {display: inline-flex; align-items: center; padding: 4px 8px; border-radius: 6px; font-size: 10px; font-weight: 600; letter-spacing: 0.3px; gap: 4px; white-space: nowrap; border: 1px solid;}
    .badge-lengkap {background: #f0fdf4; color: #16a34a; border-color: #bbf7d0;}
    .badge-kurang {background: #fefce8; color: #ca8a04; border-color: #fde047;}
    .badge-valid {background: #ecfdf5; color: #10b981; border-color: #a7f3d0;}
    .badge-invalid {background: #f8d7da; color: #842029; border-color: #f5c2c7;}
    .badge-perlu-verifikasi {background: #fef3c7; color: #d97706; border-color: #fde68a;}
    .badge-status svg {width: 10px; height: 10px;}
    .action-buttons {display: flex; gap: 6px; flex-wrap: wrap;}
    .btn-action {padding: 5px 10px; border-radius: 6px; font-size: 11px; font-weight: 500; border: 1px solid; background: white; cursor: pointer; transition: all 0.2s ease; display: flex; align-items: center; gap: 4px; white-space: nowrap;}
    .btn-edit {color: #3b82f6; border-color: #bfdbfe; background: #eff6ff;}
    .btn-edit:hover {background: #3b82f6; color: white; border-color: #3b82f6;}
    .btn-delete {color: #ef4444; border-color: #fecaca; background: #fef2f2;}
    .btn-delete:hover {background: #ef4444; color: white; border-color: #ef4444;}
    .btn-validate {color: #10b981; border-color: #a7f3d0; background: #ecfdf5;}
    .btn-validate:hover {background: #10b981; color: white; border-color: #10b981;}
    
    .activity-section {
        margin-top: 20px;
    }
    
    .activity-card {background: white; border-radius: 16px; border: 1px solid #e2e8f0; box-shadow: 0 2px 4px rgba(0,0,0,.04); overflow: hidden; height: 320px; display: flex; flex-direction: column;}
    .activity-header {padding: 16px; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; background: #f8fafc;}
    .activity-title {font-size: 14px; font-weight: 600; color: #1e293b; display: flex; align-items: center; gap: 8px;}
    .activity-title svg {width: 16px; height: 16px; color: #3b82f6;}
    .activity-count {background: #3b82f6; color: white; font-size: 10px; font-weight: 600; padding: 2px 6px; border-radius: 20px; min-width: 20px; text-align: center;}
    .activity-content {padding: 0; flex: 1; overflow-y: auto;}
    .activity-item {padding: 12px 16px; border-bottom: 1px solid #f1f5f9; transition: all 0.2s ease;}
    .activity-item:hover {background: #f8fafc;}
    .activity-item:last-child {border-bottom: none;}
    .activity-header-main {display: flex; align-items: center; gap: 8px; margin-bottom: 4px;}
    .activity-avatar {width: 28px; height: 28px; border-radius: 8px; background: #eff6ff; display: flex; align-items: center; justify-content: center; color: #3b82f6; font-weight: 600; font-size: 12px; flex-shrink: 0;}
    .activity-user {font-weight: 600; color: #1e293b; font-size: 12px; flex: 1; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;}
    .activity-detail {color: #64748b; font-size: 12px; margin-left: 36px; margin-bottom: 2px; line-height: 1.4; word-break: break-word; display: flex; align-items: center; gap: 6px;}
    .activity-action {color: #3b82f6; font-weight: 500; font-size: 11px; margin-left: 36px; margin-bottom: 4px; display: inline-flex; align-items: center; gap: 4px;}
    .activity-time {color: #94a3b8; font-size: 10px; display: flex; align-items: center; gap: 6px; margin-left: 36px;}
    .timestamp {font-family: 'SF Mono', Monaco, monospace; background: #f1f5f9; padding: 1px 4px; border-radius: 4px; font-size: 9px;}
    .btn-delete-all {display: flex; align-items: center; gap: 6px; color: #ef4444; font-size: 12px; font-weight: 500; background: #fef2f2; border: 1px solid #fecaca; border-radius: 6px; cursor: pointer; padding: 4px 8px; transition: all 0.2s ease;}
    .btn-delete-all:hover {background: #ef4444; color: white;}
    
    .activity-status-badge {display: inline-flex; align-items: center; gap: 4px; padding: 2px 6px; border-radius: 4px; font-size: 9px; font-weight: 600; margin-left: 8px;}
    .activity-status-valid {background: #ecfdf5; color: #10b981; border: 1px solid #a7f3d0;}
    .activity-status-invalid {background: #fef3c7; color: #d97706; border: 1px solid #fde68a;}
    
    .empty-state {text-align: center; padding: 30px 20px;}
    .empty-icon {width: 48px; height: 48px; margin: 0 auto 12px; color: #cbd5e1;}
    .empty-title {font-size: 14px; font-weight: 600; color: #64748b; margin-bottom: 6px;}
    .empty-subtitle {font-size: 12px; color: #94a3b8; line-height: 1.5; max-width: 300px; margin: 0 auto;}
    .table-empty-state {padding: 60px 20px;}
    
    .modal-content {border-radius: 16px; border: 1px solid #e2e8f0; box-shadow: 0 20px 25px -5px rgba(0,0,0,.1); overflow: hidden;}
    .modal-header, .modal-footer {padding: 24px; border-bottom: 1px solid #e2e8f0; background: #f8fafc;}
    .modal-footer {border-top: 1px solid #e2e8f0; border-bottom: none; display: flex; justify-content: flex-end; gap: 12px;}
    .modal-title {font-size: 20px; font-weight: 600; color: #1e293b; display: flex; align-items: center; gap: 12px;}
    .modal-title svg {width: 20px; height: 20px; color: #3b82f6;}
    .modal-body {padding: 24px;}
    .form-group {margin-bottom: 20px;}
    .form-label {display: block; font-size: 14px; font-weight: 500; color: #475569; margin-bottom: 8px;}
    .form-label::after {content: "*"; color: #ef4444; font-size: 12px;}
    .form-control {width: 100%; padding: 12px 16px; border: 1px solid #cbd5e1; border-radius: 10px; font-size: 14px; transition: all 0.2s ease; background: white; box-sizing: border-box;}
    .form-control:focus {outline: none; border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59,130,246,0.1);}
    select.form-control {appearance: none; background: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='%2364748b' viewBox='0 0 16 16'%3E%3Cpath d='M7.247 11.14 2.451 5.658C1.885 5.013 2.345 4 3.204 4h9.592a1 1 0 0 1 .753 1.659l-4.796 5.48a1 1 0 0 1-1.506 0z'/%3E%3C/svg%3E") no-repeat right 16px center/12px; padding-right: 40px;}
    .phone-input {position: relative;}
    .phone-prefix {position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #64748b; font-size: 14px; pointer-events: none;}
    .phone-input input {padding-left: 50px;}
    .form-hint {font-size: 12px; color: #94a3b8; margin-top: 6px;}
    .form-row {display: grid; grid-template-columns: 1fr 1fr; gap: 16px;}
    .btn-cancel {padding: 12px 24px; background: white; color: #64748b; border: 1px solid #cbd5e1; border-radius: 10px; font-size: 14px; font-weight: 500; cursor: pointer; transition: all 0.2s ease;}
    .btn-cancel:hover {background: #f8fafc; border-color: #94a3b8;}
    .btn-submit {padding: 12px 24px; background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); color: white; border: none; border-radius: 10px; font-size: 14px; font-weight: 600; cursor: pointer; transition: all 0.2s ease;}
    .btn-submit:hover {background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%); transform: translateY(-1px); box-shadow: 0 4px 12px rgba(37,99,235,0.2);}
    
    .toast {
        position: fixed;
        top: 20px;
        right: 20px;
        background: #10b981;
        color: white;
        padding: 12px 20px;
        border-radius: 10px;
        box-shadow: 0 8px 20px -5px rgba(0,0,0,.1);
        display: flex;
        align-items: center;
        gap: 10px;
        z-index: 9999;
        animation: slideIn 0.3s ease;
        min-width: 250px;
        max-width: 320px;
        font-size: 14px;
    }
    
    .toast-error {background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);}
    .toast-info {background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);}
    
    .import-progress {position: fixed; top: 50%; left: 50%; transform: translate(-50%,-50%); background: white; padding: 24px; border-radius: 16px; box-shadow: 0 20px 25px -5px rgba(0,0,0,.1); z-index: 10000; min-width: 300px; text-align: center;}
    .progress-bar {width: 100%; height: 8px; background: #e2e8f0; border-radius: 4px; margin: 16px 0; overflow: hidden;}
    .progress-fill {height: 100%; background: linear-gradient(90deg, #3b82f6 0%, #2563eb 100%); border-radius: 4px; transition: width 0.3s ease;}
    .progress-text {font-size: 14px; color: #64748b; margin-top: 8px;}
    
    @keyframes slideIn {
        from {transform: translateX(100%); opacity: 0;}
        to {transform: translateX(0); opacity: 1;}
    }
    
    .btn-close {
        background: none;
        border: none;
        font-size: 24px;
        color: #64748b;
        cursor: pointer;
        padding: 4px;
        transition: color 0.2s ease;
    }
    
    .btn-close:hover {
        color: #1e293b;
    }
</style>

<div class="container-fluid">
    <div class="header">
        <div class="header-icon">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.436 60.436 0 00-.491 6.347A48.627 48.627 0 0112 20.904a48.627 48.627 0 018.232-4.41 60.46 60.46 0 00-.491-6.347m-15.482 0a50.57 50.57 0 00-2.658-.813A59.905 59.905 0 0112 3.493a59.902 59.902 0 0110.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.697 50.697 0 0112 13.489a50.702 50.702 0 017.74-3.342M6.75 15a.75.75 0 100-1.5.75.75 0 000 1.5zm0 0v-3.675A55.378 55.378 0 0112 8.443m-7.007 11.55A5.981 5.981 0 006.75 15.75v-1.5" />
            </svg>
        </div>
        
        <div class="header-content">
            <h1 class="header-title">
                Recipient Data
            </h1>
            <p class="header-subtitle">Manajemen data penerima untuk kebutuhan blasting</p>
        </div>
    </div>

    <div class="data-section">
        <div class="stats-row">
            <div class="stat-card">
                <div class="stat-icon stat-icon-total">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                    </svg>
                </div>
                <div class="stat-content">
                    <div class="stat-label">Total Siswa</div>
                    <div class="stat-number">{{ $recipients->total() }}</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon stat-icon-lengkap">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div class="stat-content">
                    <div class="stat-label">Data Lengkap</div>
                    <div class="stat-number">{{ $completeCount ?? 0 }}</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon stat-icon-kurang">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                    </svg>
                </div>
                <div class="stat-content">
                    <div class="stat-label">Data Kurang</div>
                    <div class="stat-number">{{ $incompleteCount ?? 0 }}</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon stat-icon-valid">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div class="stat-content">
                    <div class="stat-label">Data Tervalidasi</div>
                    <div class="stat-number">{{ $validatedCount ?? 0 }}</div>
                </div>
            </div>
        </div>

        <div class="table-card">
            <div class="table-header">
                <div class="search-container">
                    <input type="text" class="search-box" placeholder="Cari nama siswa, kelas, atau wali..." id="searchInput">
                    
                    <form action="{{ route('admin.blast.recipients.import') }}" method="POST" enctype="multipart/form-data" class="d-inline">
                        @csrf
                        <button type="button" class="btn-import" id="importExcelBtn">
                            <svg width="14" height="14" fill="currentColor" viewBox="0 0 16 16">
                                <path d="M8.5 6.5a.5.5 0 0 0-1 0v3.793L6.354 9.146a.5.5 0 1 0-.708.708l2 2a.5.5 0 0 0 .708 0l2-2a.5.5 0 0 0-.708-.708L8.5 10.293V6.5z"/>
                                <path d="M14 14V4.5L9.5 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2zM9.5 3A1.5 1.5 0 0 0 11 4.5h2V14a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1h5.5v2z"/>
                            </svg>
                            Impor Excel
                            <input type="file" name="file" class="file-input" id="excelFileInput" accept=".xlsx,.xls,.csv" required>
                        </button>
                    </form>
                    
                    <a href="{{ route('admin.blast.recipients.create') }}" class="btn-add">
                        <svg width="14" height="14" fill="currentColor" viewBox="0 0 16 16">
                            <path d="M8 4a.5.5 0 0 1 .5.5v3h3a.5.5 0 0 1 0 1h-3v3a.5.5 0 0 1-1 0v-3h-3a.5.5 0 0 1 0-1h3v-3A.5.5 0 0 1 8 4z"/>
                        </svg>
                        Tambah Data
                    </a>
                </div>
            </div>
            
            <div class="table-wrapper">
                <table class="table">
                    <thead>
                        <tr>
                            <th style="width: 80px;">STATUS</th>
                            <th style="width: 120px;">NAMA SISWA</th>
                            <th style="width: 70px;">KELAS</th>
                            <th style="width: 100px;">NAMA WALI</th>
                            <th style="width: 100px;">NOMOR WA</th>
                            <th style="width: 130px;">EMAIL WALI</th>
                            <th style="width: 120px;">CATATAN</th>
                            <th style="width: 130px;">AKSI</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recipients as $r)
                            <tr>
                                <td>
                                    <div style="display: flex; flex-direction: column; gap: 4px;">
                                        @php
                                            $isComplete = $r->nama_siswa && $r->nama_wali && $r->wa_wali && $r->email_wali;
                                        @endphp
                                        <span class="badge-status {{ $isComplete ? 'badge-lengkap' : 'badge-kurang' }}">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="{{ $isComplete ? 'M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z' : 'M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z' }}" />
                                            </svg>
                                            {{ $isComplete ? 'LENGKAP' : 'KURANG' }}
                                        </span>
                                        
                                        @if($r->is_valid)
                                            <span class="badge-status badge-valid">
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                                VALID
                                            </span>
                                        @else
                                            <span class="badge-status badge-invalid">
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                                                </svg>
                                                INVALID
                                            </span>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <div class="student-name" title="{{ $r->nama_siswa }}">
                                        {{ $r->nama_siswa }}
                                    </div>
                                </td>
                                <td>
                                    <span class="student-class">{{ $r->kelas }}</span>
                                </td>
                                <td>
                                    <div class="student-info" title="{{ $r->nama_wali }}">
                                        {{ $r->nama_wali }}
                                    </div>
                                </td>
                                <td>
                                    <div class="student-phone" title="{{ $r->wa_wali }}">
                                        {{ $r->wa_wali }}
                                    </div>
                                </td>
                                <td>
                                    <div class="student-email" title="{{ $r->email_wali }}">
                                        {{ $r->email_wali }}
                                    </div>
                                </td>
                                <td>
                                    <div class="student-catatan" title="{{ $r->catatan ?? '-' }}">
                                        {{ $r->catatan ?? '-' }}
                                    </div>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="{{ route('admin.blast.recipients.edit', $r->id) }}" class="btn-action btn-edit">
                                            <svg width="10" height="10" fill="currentColor" viewBox="0 0 16 16">
                                                <path d="M12.146.146a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1 0 .708l-10 10a.5.5 0 0 1-.168.11l-5 2a.5.5 0 0 1-.65-.65l2-5a.5.5 0 0 1 .11-.168l10-10zM11.207 2.5 13.5 4.793 14.793 3.5 12.5 1.207 11.207 2.5zm1.586 3L10.5 3.207 4 9.707V10h.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.5h.293l6.5-6.5zm-9.761 5.175-.106.106-1.528 3.821 3.821-1.528.106-.106A.5.5 0 0 1 5 12.5V12h-.5a.5.5 0 0 1-.5-.5V11h-.5a.5.5 0 0 1-.468-.325z"/>
                                            </svg>
                                            Edit
                                        </a>
                                        
                                        <form method="POST" action="{{ route('admin.blast.recipients.destroy', $r->id) }}" class="d-inline" onsubmit="return confirm('Hapus data ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn-action btn-delete">
                                                <svg width="10" height="10" fill="currentColor" viewBox="0 0 16 16">
                                                    <path d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0V6z"/>
                                                    <path fill-rule="evenodd" d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1v1zM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4H4.118zM2.5 3V2h11v1h-11z"/>
                                                </svg>
                                                Hapus
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8">
                                    <div class="empty-state table-empty-state">
                                        <div class="empty-icon">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m5.231 13.481L15 17.25m-4.5-15H5.625c-.621 0-1.125.504-1.125 1.125v16.5c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9zm3.75 11.625a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                                            </svg>
                                        </div>
                                        <div class="empty-title">Belum Ada Data Siswa</div>
                                        <div class="empty-subtitle">Tambahkan data siswa baru dengan menekan tombol "Tambah Data" di atas atau impor dari file Excel</div>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            {{-- Pagination --}}
            @if($recipients->hasPages())
                <div class="table-header" style="border-top: 1px solid #e2e8f0; padding: 15px 20px;">
                    {{ $recipients->links() }}
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0">
                <button type="button" class="btn-close" data-bs-dismiss="modal">×</button>
            </div>
            <div class="modal-body text-center">
                <div class="mb-4">
                    <div style="width: 64px; height: 64px; margin: 0 auto 20px; color: #ef4444;">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                        </svg>
                    </div>
                    <h5 class="fw-bold mb-2">Hapus Data Siswa?</h5>
                    <p class="text-muted">Data yang dihapus tidak dapat dikembalikan</p>
                </div>
            </div>
            <div class="modal-footer border-0 justify-content-center">
                <button type="button" class="btn-cancel" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn-submit" style="background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);" id="confirmDeleteBtn">Hapus</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Search functionality
    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase().trim();
            const rows = document.querySelectorAll('.table tbody tr');
            
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(searchTerm) ? '' : 'none';
            });
        });
    }
    
    // Import form submission
    const excelFileInput = document.getElementById('excelFileInput');
    if (excelFileInput) {
        excelFileInput.addEventListener('change', function(e) {
            if (this.files.length > 0) {
                this.closest('form').submit();
            }
        });
    }
    
    // Toast notification
    @if(session('success'))
        showToast('{{ session('success') }}', 'success');
    @endif
    
    @if(session('error'))
        showToast('{{ session('error') }}', 'error');
    @endif
});

function showToast(message, type = 'success') {
    document.querySelectorAll('.toast').forEach(toast => toast.remove());
    
    let icon = '';
    if (type === 'success') {
        icon = `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>`;
    } else if (type === 'error') {
        icon = `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" /></svg>`;
    } else {
        icon = `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" /></svg>`;
    }
    
    const toast = document.createElement('div');
    toast.className = `toast ${type === 'error' ? 'toast-error' : type === 'info' ? 'toast-info' : ''}`;
    toast.innerHTML = `${icon}<div style="flex:1">${message}</div><button onclick="this.parentElement.remove()" style="background:none;border:none;color:white;cursor:pointer;font-size:18px;opacity:0.7">×</button>`;
    document.body.appendChild(toast);
    
    setTimeout(() => {
        if (toast.parentElement) {
            toast.remove();
        }
    }, 4000);
}
</script>
@endsection