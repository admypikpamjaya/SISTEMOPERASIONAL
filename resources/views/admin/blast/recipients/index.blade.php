@extends('layouts.app')
@section('title', 'Student Data')
@section('content')

<style>
    body {background: #f8fafc; font-family: 'Segoe UI', system-ui, sans-serif; color: #334155; margin: 0; padding: 0;}
    .container-fluid {padding: 24px; max-width: 1400px; margin: 0 auto;}
    
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
                Student Data
            </h1>
            <p class="header-subtitle">Kelola data siswa dan wali murid</p>
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
                    <div class="stat-number" id="totalSiswa">0</div>
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
                    <div class="stat-number" id="dataLengkap">0</div>
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
                    <div class="stat-number" id="dataKurang">0</div>
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
                    <div class="stat-number" id="dataValid">0</div>
                </div>
            </div>
        </div>

        <div class="table-card">
            <div class="table-header">
                <div class="search-container">
                    <input type="text" class="search-box" placeholder="Cari nama siswa, kelas, atau wali..." id="searchInput">
                    <button class="btn-import" id="importExcelBtn">
                        <svg width="14" height="14" fill="currentColor" viewBox="0 0 16 16">
                            <path d="M8.5 6.5a.5.5 0 0 0-1 0v3.793L6.354 9.146a.5.5 0 1 0-.708.708l2 2a.5.5 0 0 0 .708 0l2-2a.5.5 0 0 0-.708-.708L8.5 10.293V6.5z"/>
                            <path d="M14 14V4.5L9.5 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2zM9.5 3A1.5 1.5 0 0 0 11 4.5h2V14a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1h5.5v2z"/>
                        </svg>
                        Impor Excel
                        <input type="file" class="file-input" id="excelFileInput" accept=".xlsx,.xls,.csv">
                    </button>
                    <button class="btn-add" id="addStudentBtn">
                        <svg width="14" height="14" fill="currentColor" viewBox="0 0 16 16">
                            <path d="M8 4a.5.5 0 0 1 .5.5v3h3a.5.5 0 0 1 0 1h-3v3a.5.5 0 0 1-1 0v-3h-3a.5.5 0 0 1 0-1h3v-3A.5.5 0 0 1 8 4z"/>
                        </svg>
                        Tambah Data
                    </button>
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
                    <tbody id="studentTableBody">
                        <!-- Data akan ditampilkan di sini -->
                    </tbody>
                </table>
            </div>
        </div>
        
        <div class="activity-section">
            <div class="activity-card">
                <div class="activity-header">
                    <div class="activity-title">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 012.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />
                        </svg>
                        Activity Log
                        <span class="activity-count" id="activityCount">0</span>
                    </div>
                    <button class="btn-delete-all" id="deleteAllActivitiesBtn">
                        <svg width="12" height="12" fill="currentColor" viewBox="0 0 16 16">
                            <path d="M6.5 1h3a.5.5 0 0 1 .5.5v1H6v-1a.5.5 0 0 1 .5-.5ZM11 2.5v-1A1.5 1.5 0 0 0 9.5 0h-3A1.5 1.5 0 0 0 5 1.5v1H2.506a.58.58 0 0 0-.01 0H1.5a.5.5 0 0 0 0 1h.538l.853 10.66A2 2 0 0 0 4.885 16h6.23a2 2 0 0 0 1.994-1.84l.853-10.66h.538a.5.5 0 0 0 0-1h-.995a.59.59 0 0 0-.01 0H11Zm1.958 1-.846 10.58a1 1 0 0 1-.997.92h-6.23a1 1 0 0 1-.997-.92L3.042 3.5h9.916Zm-7.487 1a.5.5 0 0 1 .528.47l.5 8.5a.5.5 0 0 1-.998.06L5 5.03a.5.5 0 0 1 .47-.53Zm5.058 0a.5.5 0 0 1 .47.53l-.5 8.5a.5.5 0 1 1-.998-.06l.5-8.5a.5.5 0 0 1 .528-.47ZM8 4.5a.5.5 0 0 1 .5.5v8.5a.5.5 0 0 1-1 0V5a.5.5 0 0 1 .5-.5Z"/>
                        </svg>
                        Hapus Semua
                    </button>
                </div>
                
                <div class="activity-content" id="activityLog">
                    <!-- Activity log akan ditampilkan di sini -->
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="studentModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v6m3-3H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Tambah Data Siswa
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal">×</button>
            </div>
            <div class="modal-body">
                <form id="studentForm">
                    <div class="form-group">
                        <label class="form-label">Nama Siswa</label>
                        <input type="text" class="form-control" id="nama" placeholder="Masukkan nama lengkap siswa" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Kelas</label>
                        <div class="form-row">
                            <div>
                                <select class="form-control" id="tingkat" required>
                                    <option value="">Pilih Tingkat</option>
                                    <option value="1">Kelas 1</option>
                                    <option value="2">Kelas 2</option>
                                    <option value="3">Kelas 3</option>
                                    <option value="4">Kelas 4</option>
                                    <option value="5">Kelas 5</option>
                                    <option value="6">Kelas 6</option>
                                </select>
                            </div>
                            <div>
                                <select class="form-control" id="kelas" required>
                                    <option value="">Pilih Kelas</option>
                                    <option value="A">Kelas A</option>
                                    <option value="B">Kelas B</option>
                                    <option value="C">Kelas C</option>
                                    <option value="D">Kelas D</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-hint">Contoh: 4 A (Kelas 4-A)</div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Nama Wali Murid</label>
                        <input type="text" class="form-control" id="wali" placeholder="Masukkan nama wali murid" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Nomor WhatsApp</label>
                        <div class="phone-input">
                            <span class="phone-prefix">+62</span>
                            <input type="tel" class="form-control" id="wa" placeholder="81234567890" required>
                        </div>
                        <div class="form-hint">Format: +62 8xxxxxxxxxx (tanpa spasi, dimulai dengan 8)</div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Email Wali Murid</label>
                        <input type="email" class="form-control" id="email" placeholder="contoh@email.com" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Catatan <span style="color: #94a3b8; font-weight: normal;">(Opsional)</span></label>
                        <textarea class="form-control" id="catatan" rows="3" placeholder="Tambahkan catatan jika diperlukan..."></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-cancel" data-bs-dismiss="modal" id="cancelStudentBtn">Batal</button>
                <button type="button" class="btn-submit" id="saveStudentBtn">Tambah Data</button>
            </div>
        </div>
    </div>
</div>

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

<div class="modal fade" id="deleteAllActivitiesModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0">
                <button type="button" class="btn-close" data-bs-dismiss="modal">×</button>
            </div>
            <div class="modal-body text-center">
                <div class="mb-4">
                    <div style="width: 64px; height: 64px; margin: 0 auto 20px; color: #f59e0b;">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                        </svg>
                    </div>
                    <h5 class="fw-bold mb-2">Hapus Semua Aktivitas?</h5>
                    <p class="text-muted">Semua riwayat aktivitas akan dihapus secara permanen</p>
                </div>
            </div>
            <div class="modal-footer border-0 justify-content-center">
                <button type="button" class="btn-cancel" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn-submit" style="background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);" id="confirmDeleteAllActivitiesBtn">Hapus Semua</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
<script>
    let students = [], activities = [], currentStudentId = null, isEditing = false;
    const studentTableBody = document.getElementById('studentTableBody'),
          activityLog = document.getElementById('activityLog'),
          totalSiswa = document.getElementById('totalSiswa'),
          dataLengkap = document.getElementById('dataLengkap'),
          dataKurang = document.getElementById('dataKurang'),
          dataValid = document.getElementById('dataValid'),
          activityCount = document.getElementById('activityCount'),
          searchInput = document.getElementById('searchInput'),
          addStudentBtn = document.getElementById('addStudentBtn'),
          saveStudentBtn = document.getElementById('saveStudentBtn'),
          cancelStudentBtn = document.getElementById('cancelStudentBtn'),
          deleteAllActivitiesBtn = document.getElementById('deleteAllActivitiesBtn'),
          excelFileInput = document.getElementById('excelFileInput'),
          confirmDeleteBtn = document.getElementById('confirmDeleteBtn'),
          confirmDeleteAllActivitiesBtn = document.getElementById('confirmDeleteAllActivitiesBtn');

    document.addEventListener('DOMContentLoaded', () => {
        // Data kosong secara default
        students = [];
        activities = [];
        
        // Render tampilan kosong
        renderEmptyState();
        renderEmptyActivityLog();
        updateStats();
        setupEventListeners();
    });

    function renderEmptyState() {
        studentTableBody.innerHTML = `<tr><td colspan="8"><div class="empty-state table-empty-state">
            <div class="empty-icon">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m5.231 13.481L15 17.25m-4.5-15H5.625c-.621 0-1.125.504-1.125 1.125v16.5c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9zm3.75 11.625a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                </svg>
            </div>
            <div class="empty-title">Belum Ada Data Siswa</div>
            <div class="empty-subtitle">Tambahkan data siswa baru dengan menekan tombol "Tambah Data" di atas atau impor dari file Excel</div>
        </div></td></tr>`;
    }

    function renderEmptyActivityLog() {
        activityLog.innerHTML = `<div class="empty-state">
            <div class="empty-icon">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div class="empty-title">Belum Ada Aktivitas</div>
            <div class="empty-subtitle">Aktivitas akan muncul di sini saat Anda menambahkan atau mengubah data siswa</div>
        </div>`;
        activityCount.textContent = '0';
    }

    function openAddModal() {
        isEditing = false;
        currentStudentId = null;
        document.getElementById('studentForm').reset();
        document.getElementById('modalTitle').innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v6m3-3H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>Tambah Data Siswa`;
        document.getElementById('saveStudentBtn').textContent = 'Tambah Data';
        const studentModal = new bootstrap.Modal(document.getElementById('studentModal'));
        studentModal.show();
    }

    function openEditModal(studentId) {
        const student = students.find(s => s.id === studentId);
        if (!student) return;
        isEditing = true;
        currentStudentId = studentId;
        const [tingkat, kelas] = student.kelas.split(' ');
        document.getElementById('nama').value = student.nama;
        document.getElementById('tingkat').value = tingkat || '';
        document.getElementById('kelas').value = kelas || '';
        document.getElementById('wali').value = student.wali;
        document.getElementById('wa').value = student.wa.replace('+62', '');
        document.getElementById('email').value = student.email;
        document.getElementById('catatan').value = student.catatan || '';
        document.getElementById('modalTitle').innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 011.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" /></svg>Edit Data Siswa`;
        document.getElementById('saveStudentBtn').textContent = 'Update Siswa';
        const studentModal = new bootstrap.Modal(document.getElementById('studentModal'));
        studentModal.show();
    }

    function saveStudent() {
        const form = document.getElementById('studentForm');
        if (!form.checkValidity()) return form.reportValidity();
        const tingkat = document.getElementById('tingkat').value,
              kelas = document.getElementById('kelas').value,
              phoneInput = document.getElementById('wa').value.trim();
        if (!tingkat || !kelas) return showToast('Harap pilih tingkat dan kelas', 'error');
        if (!phoneInput) return showToast('Harap isi nomor WhatsApp', 'error');
        
        const studentData = {
            nama: document.getElementById('nama').value.trim(),
            kelas: `${tingkat} ${kelas}`,
            wali: document.getElementById('wali').value.trim(),
            wa: `+62${phoneInput}`,
            email: document.getElementById('email').value.trim(),
            catatan: document.getElementById('catatan').value.trim(),
            status: 'LENGKAP',
            createdAt: new Date(),
            isValidated: false,
            validatedBy: null,
            validatedAt: null
        };
        
        // Validasi
        if (!studentData.nama || !studentData.wali || !phoneInput || !studentData.email) 
            return showToast('Harap isi semua field yang wajib diisi', 'error');
        if (!/^8[0-9]{9,11}$/.test(phoneInput)) 
            return showToast('Nomor WA harus dimulai dengan 8 dan terdiri dari 10-12 digit', 'error');
        if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(studentData.email)) 
            return showToast('Format email tidak valid', 'error');
        
        if (isEditing) {
            // Edit data siswa yang sudah ada
            const index = students.findIndex(s => s.id === currentStudentId);
            if (index !== -1) {
                students[index] = {...students[index], ...studentData};
                addActivity('Data Diperbarui', `${studentData.nama} (${studentData.kelas})`, 'Admin', 'update');
                showToast('Data siswa berhasil diperbarui', 'success');
            }
        } else {
            // Tambah data siswa baru
            const newStudent = {
                id: students.length > 0 ? Math.max(...students.map(s => s.id)) + 1 : 1,
                ...studentData
            };
            students.unshift(newStudent);
            addActivity('Ditambahkan', `Siswa ${studentData.nama} (${studentData.kelas})`, 'Admin', 'add');
            showToast('Data siswa berhasil ditambahkan', 'success');
        }
        
        renderStudents();
        updateStats();
        bootstrap.Modal.getInstance(document.getElementById('studentModal')).hide();
    }

    function toggleValidation(studentId) {
        const student = students.find(s => s.id === studentId);
        if (!student) return;
        
        const currentUser = "Admin"; // Ini bisa diganti dengan user yang sedang login
        const now = new Date();
        
        if (student.isValidated) {
            // Jika sudah tervalidasi, batalkan validasi
            student.isValidated = false;
            student.validatedBy = null;
            student.validatedAt = null;
            addActivity('Validasi Dibatalkan', `${student.nama} (${student.kelas})`, currentUser, 'validation', 'invalid');
            showToast('Validasi data siswa dibatalkan', 'info');
        } else {
            // Jika belum tervalidasi, validasi data
            student.isValidated = true;
            student.validatedBy = currentUser;
            student.validatedAt = now;
            addActivity('Data Tervalidasi', `${student.nama} (${student.kelas}) - Data sudah diverifikasi`, currentUser, 'validation', 'valid');
            showToast('Data siswa berhasil divalidasi', 'success');
        }
        
        renderStudents();
        updateStats();
    }

    function renderStudents() {
        if (students.length === 0) {
            renderEmptyState();
            return;
        }
        
        let tableHTML = '';
        students.forEach(student => {
            const isComplete = student.nama && student.wali && student.wa && student.email;
            const status = isComplete ? 'LENGKAP' : 'KURANG';
            
            tableHTML += `<tr>
                <td>
                    <div style="display: flex; flex-direction: column; gap: 4px;">
                        <span class="badge-status ${isComplete?'badge-lengkap':'badge-kurang'}">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="${isComplete?'M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z':'M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z'}" />
                            </svg>${status}
                        </span>
                        ${student.isValidated ? 
                            `<span class="badge-status badge-valid">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>TERVALIDASI
                            </span>` : 
                            `<span class="badge-status badge-perlu-verifikasi">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                                </svg>PERLU VERIFIKASI
                            </span>`
                        }
                    </div>
                </td>
                <td><div class="student-name" title="${student.nama}">${student.nama}</div></td>
                <td><span class="student-class">${student.kelas}</span></td>
                <td><div class="student-info" title="${student.wali}">${student.wali}</div></td>
                <td><div class="student-phone" title="${student.wa}">${student.wa}</div></td>
                <td><div class="student-email" title="${student.email}">${student.email}</div></td>
                <td><div class="student-catatan" title="${student.catatan || '-'}">${student.catatan ? student.catatan : '-'}</div></td>
                <td>
                    <div class="action-buttons">
                        <button class="btn-action btn-edit" onclick="openEditModal(${student.id})">
                            <svg width="10" height="10" fill="currentColor" viewBox="0 0 16 16">
                                <path d="M12.146.146a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1 0 .708l-10 10a.5.5 0 0 1-.168.11l-5 2a.5.5 0 0 1-.65-.65l2-5a.5.5 0 0 1 .11-.168l10-10zM11.207 2.5 13.5 4.793 14.793 3.5 12.5 1.207 11.207 2.5zm1.586 3L10.5 3.207 4 9.707V10h.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.5h.293l6.5-6.5zm-9.761 5.175-.106.106-1.528 3.821 3.821-1.528.106-.106A.5.5 0 0 1 5 12.5V12h-.5a.5.5 0 0 1-.5-.5V11h-.5a.5.5 0 0 1-.468-.325z"/>
                            </svg>Edit
                        </button>
                        <button class="btn-action btn-validate" onclick="toggleValidation(${student.id})">
                            <svg width="10" height="10" fill="currentColor" viewBox="0 0 16 16">
                                <path d="M10.97 4.97a.75.75 0 0 1 1.07 1.05l-3.99 4.99a.75.75 0 0 1-1.08.02L4.324 8.384a.75.75 0 1 1 1.06-1.06l2.094 2.093 3.473-4.425a.267.267 0 0 1 .02-.022z"/>
                            </svg>${student.isValidated ? 'Batal Validasi' : 'Validasi'}
                        </button>
                        <button class="btn-action btn-delete" onclick="deleteStudent(${student.id})">
                            <svg width="10" height="10" fill="currentColor" viewBox="0 0 16 16">
                                <path d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0V6z"/>
                                <path fill-rule="evenodd" d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1v1zM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4H4.118zM2.5 3V2h11v1h-11z"/>
                            </svg>Hapus
                        </button>
                    </div>
                </td>
            </tr>`;
        });
        studentTableBody.innerHTML = tableHTML;
    }

    function deleteStudent(studentId) {
        currentStudentId = studentId;
        new bootstrap.Modal(document.getElementById('deleteModal')).show();
    }

    function confirmDelete() {
        const student = students.find(s => s.id === currentStudentId);
        const index = students.findIndex(s => s.id === currentStudentId);
        if (index !== -1) {
            students.splice(index, 1);
            addActivity('Dihapus', `Siswa ${student.nama} (${student.kelas})`, 'Admin', 'delete');
            renderStudents();
            updateStats();
            bootstrap.Modal.getInstance(document.getElementById('deleteModal')).hide();
            showToast('Data siswa berhasil dihapus', 'success');
        }
    }

    function deleteAllActivities() {
        if (activities.length === 0) return showToast('Tidak ada aktivitas untuk dihapus', 'info');
        new bootstrap.Modal(document.getElementById('deleteAllActivitiesModal')).show();
    }

    function confirmDeleteAllActivities() {
        const count = activities.length;
        activities = [];
        renderActivities();
        activityCount.textContent = '0';
        bootstrap.Modal.getInstance(document.getElementById('deleteAllActivitiesModal')).hide();
        showToast(`Semua aktivitas (${count}) berhasil dihapus`, 'success');
    }

    function addActivity(action, detail, user = 'Admin', type = 'general', status = null) {
        const now = new Date(),
              time = getRelativeTime(now),
              timestamp = formatTime(now),
              initials = user.charAt(0).toUpperCase(),
              newActivity = {
                id: activities.length + 1, 
                user, 
                initials, 
                action, 
                detail, 
                time, 
                timestamp, 
                type,
                status,
                createdAt: now
            };
        activities.unshift(newActivity);
        renderActivities();
    }

    function renderActivities() {
        if (activities.length === 0) {
            renderEmptyActivityLog();
            return;
        }
        
        let activityHTML = '';
        activities.forEach(activity => {
            let actionIcon = '', statusBadge = '';
            
            // Tentukan ikon berdasarkan aksi
            if (activity.action.includes('Validasi') || activity.action.includes('Tervalidasi')) {
                actionIcon = `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="10" height="10">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>`;
                
                if (activity.status === 'valid') {
                    statusBadge = `<span class="activity-status-badge activity-status-valid">✓ VALID</span>`;
                } else if (activity.status === 'invalid') {
                    statusBadge = `<span class="activity-status-badge activity-status-invalid">✗ PERLU VERIFIKASI</span>`;
                }
            } else if (activity.action === 'Ditambahkan') {
                actionIcon = `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="10" height="10">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v6m3-3H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>`;
            } else if (activity.action === 'Dihapus') {
                actionIcon = `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="10" height="10">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                </svg>`;
            } else if (activity.action === 'Data Diperbarui') {
                actionIcon = `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="10" height="10">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
                </svg>`;
            } else if (activity.action === 'Import Excel') {
                actionIcon = `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="10" height="10">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5" />
                </svg>`;
            } else if (activity.action === 'Validasi Dibatalkan') {
                actionIcon = `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="10" height="10">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                </svg>`;
            }
            
            activityHTML += `<div class="activity-item">
                <div class="activity-header-main">
                    <div class="activity-avatar">${activity.initials}</div>
                    <div class="activity-user">${activity.user}</div>
                    <div class="activity-time">${activity.time}</div>
                </div>
                <div class="activity-detail">
                    ${activity.detail}
                    ${statusBadge}
                </div>
                <div class="activity-action">
                    ${actionIcon} ${activity.action}
                </div>
                <div class="activity-time">
                    <span class="timestamp">${activity.timestamp}</span>
                </div>
            </div>`;
        });
        
        activityLog.innerHTML = activityHTML;
        activityCount.textContent = activities.length;
    }

    function updateStats() {
        totalSiswa.textContent = students.length;
        const complete = students.filter(s => s.nama && s.wali && s.wa && s.email).length,
              incomplete = students.length - complete,
              validated = students.filter(s => s.isValidated).length;
        
        dataLengkap.textContent = complete;
        dataKurang.textContent = incomplete;
        dataValid.textContent = validated;
    }

    function importExcelFile(file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            try {
                const data = new Uint8Array(e.target.result),
                      workbook = XLSX.read(data, { type: 'array' }),
                      firstSheet = workbook.Sheets[workbook.SheetNames[0]],
                      jsonData = XLSX.utils.sheet_to_json(firstSheet);
                if (jsonData.length === 0) return showToast('File Excel kosong atau format tidak sesuai', 'error');
                
                showImportProgress(0, jsonData.length);
                let importedCount = 0, validCount = 0;
                
                jsonData.forEach((row, index) => {
                    setTimeout(() => {
                        const studentData = {
                            nama: row['Nama Siswa'] || row['Nama'] || row['nama'] || '',
                            kelas: (row['Kelas'] || row['kelas'] || '').toString(),
                            wali: row['Nama Wali'] || row['Wali'] || row['wali'] || '',
                            wa: row['Nomor WA'] || row['WhatsApp'] || row['wa'] || '',
                            email: row['Email'] || row['Email Wali'] || row['email'] || '',
                            catatan: row['Catatan'] || row['catatan'] || '',
                            createdAt: new Date(),
                            isValidated: false,
                            validatedBy: null,
                            validatedAt: null
                        };
                        
                        if (studentData.kelas) {
                            const kelasMatch = studentData.kelas.match(/(\d+)\s*([A-D])/i);
                            if (kelasMatch) studentData.kelas = `${kelasMatch[1]} ${kelasMatch[2].toUpperCase()}`;
                        }
                        
                        if (studentData.wa) {
                            let phone = studentData.wa.toString().replace(/\s/g, '');
                            if (phone.startsWith('0')) phone = '62' + phone.substring(1);
                            else if (!phone.startsWith('62') && !phone.startsWith('+62')) phone = '62' + phone;
                            if (!phone.startsWith('+')) phone = '+' + phone;
                            studentData.wa = phone;
                        }
                        
                        if (studentData.nama && studentData.wali && studentData.wa && studentData.email) {
                            students.unshift({
                                id: students.length > 0 ? Math.max(...students.map(s => s.id)) + 1 : 1,
                                ...studentData,
                                status: 'LENGKAP'
                            });
                            validCount++;
                        }
                        
                        importedCount++;
                        const progress = Math.round((importedCount / jsonData.length) * 100);
                        showImportProgress(importedCount, jsonData.length, progress);
                        
                        if (importedCount === jsonData.length) {
                            setTimeout(() => {
                                hideImportProgress();
                                if (validCount > 0) {
                                    addActivity('Import Excel', `${validCount} data siswa diimpor`, 'Admin', 'import');
                                    renderStudents();
                                    updateStats();
                                    showToast(`Berhasil mengimpor ${validCount} data siswa dari file Excel`, 'success');
                                } else {
                                    showToast('Tidak ada data valid yang dapat diimpor. Periksa format file Excel', 'error');
                                }
                            }, 500);
                        }
                    }, index * 50);
                });
            } catch (error) {
                hideImportProgress();
                showToast('Error membaca file Excel: ' + error.message, 'error');
            }
        };
        reader.readAsArrayBuffer(file);
    }

    function showImportProgress(current, total, percent = 0) {
        let progressModal = document.getElementById('importProgressModal');
        if (!progressModal) {
            progressModal = document.createElement('div');
            progressModal.id = 'importProgressModal';
            progressModal.className = 'import-progress';
            progressModal.innerHTML = `<div style="margin-bottom:16px"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="32" height="32" style="color:#3b82f6"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5" /></svg></div>
            <h5 style="margin-bottom:8px;color:#1e293b;font-weight:600">Mengimpor Data</h5>
            <div class="progress-bar"><div class="progress-fill" style="width:${percent}%"></div></div>
            <div class="progress-text">${current} dari ${total} data diproses (${percent}%)</div>`;
            document.body.appendChild(progressModal);
        } else {
            const progressFill = progressModal.querySelector('.progress-fill'),
                  progressText = progressModal.querySelector('.progress-text');
            if (progressFill) progressFill.style.width = percent + '%';
            if (progressText) progressText.textContent = `${current} dari ${total} data diproses (${percent}%)`;
        }
    }

    function hideImportProgress() {
        const progressModal = document.getElementById('importProgressModal');
        if (progressModal) progressModal.remove();
    }

    function setupEventListeners() {
        addStudentBtn.addEventListener('click', openAddModal);
        saveStudentBtn.addEventListener('click', saveStudent);
        cancelStudentBtn.addEventListener('click', function() {
            const modal = bootstrap.Modal.getInstance(document.getElementById('studentModal'));
            if (modal) modal.hide();
        });
        
        deleteAllActivitiesBtn.addEventListener('click', deleteAllActivities);
        excelFileInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (!file) return;
            const validExtensions = ['.xlsx', '.xls', '.csv'],
                  fileExtension = '.' + file.name.split('.').pop().toLowerCase();
            if (!validExtensions.includes(fileExtension)) {
                showToast('Format file tidak didukung. Harap upload file Excel (.xlsx, .xls, .csv)', 'error');
                this.value = '';
                return;
            }
            if (file.size > 5 * 1024 * 1024) {
                showToast('File terlalu besar. Maksimal 5MB', 'error');
                this.value = '';
                return;
            }
            showToast('Memproses file Excel...', 'info');
            importExcelFile(file);
            this.value = '';
        });
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase().trim();
            if (!searchTerm) return renderStudents();
            const filtered = students.filter(student => 
                student.nama.toLowerCase().includes(searchTerm) ||
                student.kelas.toLowerCase().includes(searchTerm) ||
                student.wali.toLowerCase().includes(searchTerm) ||
                student.email.toLowerCase().includes(searchTerm) ||
                student.wa.includes(searchTerm) ||
                (student.catatan && student.catatan.toLowerCase().includes(searchTerm))
            );
            renderFilteredStudents(filtered);
        });
        confirmDeleteBtn.addEventListener('click', confirmDelete);
        confirmDeleteAllActivitiesBtn.addEventListener('click', confirmDeleteAllActivities);
    }

    function renderFilteredStudents(filteredStudents) {
        if (filteredStudents.length === 0) {
            studentTableBody.innerHTML = `<tr><td colspan="8"><div class="empty-state">
                <div class="empty-icon"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" /></svg></div>
                <div class="empty-title">Tidak ditemukan data siswa</div><div class="empty-subtitle">Coba dengan kata kunci yang berbeda</div></div></td></tr>`;
            return;
        }
        
        let tableHTML = '';
        filteredStudents.forEach(student => {
            const isComplete = student.nama && student.wali && student.wa && student.email;
            const status = isComplete ? 'LENGKAP' : 'KURANG';
            
            tableHTML += `<tr>
                <td>
                    <div style="display: flex; flex-direction: column; gap: 4px;">
                        <span class="badge-status ${isComplete?'badge-lengkap':'badge-kurang'}">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="${isComplete?'M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z':'M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z'}" />
                            </svg>${status}
                        </span>
                        ${student.isValidated ? 
                            `<span class="badge-status badge-valid">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>TERVALIDASI
                            </span>` : 
                            `<span class="badge-status badge-perlu-verifikasi">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                                </svg>PERLU VERIFIKASI
                            </span>`
                        }
                    </div>
                </td>
                <td><div class="student-name" title="${student.nama}">${student.nama}</div></td>
                <td><span class="student-class">${student.kelas}</span></td>
                <td><div class="student-info" title="${student.wali}">${student.wali}</div></td>
                <td><div class="student-phone" title="${student.wa}">${student.wa}</div></td>
                <td><div class="student-email" title="${student.email}">${student.email}</div></td>
                <td><div class="student-catatan" title="${student.catatan || '-'}">${student.catatan ? student.catatan : '-'}</div></td>
                <td>
                    <div class="action-buttons">
                        <button class="btn-action btn-edit" onclick="openEditModal(${student.id})">
                            <svg width="10" height="10" fill="currentColor" viewBox="0 0 16 16">
                                <path d="M12.146.146a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1 0 .708l-10 10a.5.5 0 0 1-.168.11l-5 2a.5.5 0 0 1-.65-.65l2-5a.5.5 0 0 1 .11-.168l10-10zM11.207 2.5 13.5 4.793 14.793 3.5 12.5 1.207 11.207 2.5zm1.586 3L10.5 3.207 4 9.707V10h.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.5h.293l6.5-6.5zm-9.761 5.175-.106.106-1.528 3.821 3.821-1.528.106-.106A.5.5 0 0 1 5 12.5V12h-.5a.5.5 0 0 1-.5-.5V11h-.5a.5.5 0 0 1-.468-.325z"/>
                            </svg>Edit
                        </button>
                        <button class="btn-action btn-validate" onclick="toggleValidation(${student.id})">
                            <svg width="10" height="10" fill="currentColor" viewBox="0 0 16 16">
                                <path d="M10.97 4.97a.75.75 0 0 1 1.07 1.05l-3.99 4.99a.75.75 0 0 1-1.08.02L4.324 8.384a.75.75 0 1 1 1.06-1.06l2.094 2.093 3.473-4.425a.267.267 0 0 1 .02-.022z"/>
                            </svg>${student.isValidated ? 'Batal Validasi' : 'Validasi'}
                        </button>
                        <button class="btn-action btn-delete" onclick="deleteStudent(${student.id})">
                            <svg width="10" height="10" fill="currentColor" viewBox="0 0 16 16">
                                <path d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0V6z"/>
                                <path fill-rule="evenodd" d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1v1zM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4H4.118zM2.5 3V2h11v1h-11z"/>
                            </svg>Hapus
                        </button>
                    </div>
                </td>
            </tr>`;
        });
        studentTableBody.innerHTML = tableHTML;
    }

    function formatTime(date) {
        return date.toLocaleTimeString('id-ID', {hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: false});
    }

    function getRelativeTime(date) {
        const now = new Date(),
              diffMs = now - date,
              diffMins = Math.floor(diffMs / (1000 * 60));
        if (diffMins < 1) return 'Baru saja';
        if (diffMins === 1) return '1 menit yang lalu';
        if (diffMins < 60) return `${diffMins} menit yang lalu`;
        const diffHours = Math.floor(diffMins / 60);
        if (diffHours === 1) return '1 jam yang lalu';
        if (diffHours < 24) return `${diffHours} jam yang lalu`;
        const diffDays = Math.floor(diffHours / 24);
        if (diffDays === 1) return '1 hari yang lalu';
        return `${diffDays} hari yang lalu`;
    }

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