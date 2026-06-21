@extends('layouts.app')
@section('title', __('app.blast.recipient_data_title'))
@section('content')

<style>
    @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500&display=swap');

    :root {
        --blue-primary: #1a56db;
        --blue-secondary: #1e40af;
        --blue-light: #dbeafe;
        --blue-mid: #3b82f6;
        --dark-nav: #1e2533;
        --bg-main: #f0f4f8;
        --white: #ffffff;
        --text-dark: #1a202c;
        --text-mid: #4a5568;
        --text-light: #718096;
        --border: #e2e8f0;
        --green: #059669;
        --green-bg: #ecfdf5;
        --green-border: #a7f3d0;
        --yellow: #d97706;
        --yellow-bg: #fffbeb;
        --yellow-border: #fde68a;
        --red: #dc2626;
        --red-bg: #fef2f2;
        --red-border: #fecaca;
        --shadow-sm: 0 1px 3px rgba(0,0,0,0.08);
        --shadow-md: 0 4px 16px rgba(0,0,0,0.1);
        --shadow-blue: 0 4px 20px rgba(26, 86, 219, 0.25);
        --radius: 12px;
        --radius-sm: 8px;
    }

    * { box-sizing: border-box; margin: 0; padding: 0; }

    body {
        background: var(--bg-main);
        font-family: 'Plus Jakarta Sans', sans-serif;
        color: var(--text-dark);
    }

    .page-wrapper {
        padding: 28px 32px;
        max-width: 1440px;
        margin: 0 auto;
    }

    /* ── PAGE HEADER ── */
    .page-header {
        display: flex;
        align-items: center;
        gap: 18px;
        margin-bottom: 28px;
    }

    .page-header-icon {
        width: 56px;
        height: 56px;
        border-radius: 14px;
        background: linear-gradient(135deg, var(--blue-primary) 0%, #2563eb 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: var(--shadow-blue);
        flex-shrink: 0;
    }

    .page-header-icon svg {
        width: 28px;
        height: 28px;
        color: white;
    }

    .page-header-text h1 {
        font-size: 22px;
        font-weight: 800;
        color: var(--text-dark);
        letter-spacing: -0.3px;
        line-height: 1.2;
    }

    .page-header-text p {
        font-size: 13px;
        color: var(--text-light);
        margin-top: 3px;
        font-weight: 500;
    }

    /* ── STATS GRID ── */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 16px;
        margin-bottom: 24px;
    }

    .stat-card {
        background: var(--white);
        border-radius: var(--radius);
        padding: 20px;
        border: 1px solid var(--border);
        box-shadow: var(--shadow-sm);
        display: flex;
        align-items: center;
        gap: 14px;
        transition: box-shadow 0.2s, transform 0.2s;
        position: relative;
        overflow: hidden;
    }

    .stat-card::before {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0;
        height: 3px;
        border-radius: var(--radius) var(--radius) 0 0;
    }

    .stat-card.total::before  { background: linear-gradient(90deg, #3b82f6, #1a56db); }
    .stat-card.lengkap::before { background: linear-gradient(90deg, #10b981, #059669); }
    .stat-card.kurang::before  { background: linear-gradient(90deg, #f59e0b, #d97706); }
    .stat-card.valid::before   { background: linear-gradient(90deg, #06b6d4, #0891b2); }

    .stat-card:hover {
        box-shadow: var(--shadow-md);
        transform: translateY(-2px);
    }

    .stat-icon {
        width: 46px; height: 46px;
        border-radius: 10px;
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0;
    }

    .stat-icon svg { width: 22px; height: 22px; }
    .stat-icon.total   { background: #eff6ff; color: #3b82f6; }
    .stat-icon.lengkap { background: var(--green-bg); color: var(--green); }
    .stat-icon.kurang  { background: var(--yellow-bg); color: var(--yellow); }
    .stat-icon.valid   { background: #ecfeff; color: #0891b2; }

    .stat-info { flex: 1; }
    .stat-label { font-size: 12px; font-weight: 600; color: var(--text-light); text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 5px; }
    .stat-number { font-size: 30px; font-weight: 800; color: var(--text-dark); line-height: 1; letter-spacing: -1px; }

    /* ── MAIN LAYOUT ── */
    .main-layout {
        display: grid;
        grid-template-columns: 1fr;
        gap: 20px;
        align-items: start;
    }

    /* ── TABLE CARD ── */
    .table-card {
        background: var(--white);
        border-radius: var(--radius);
        border: 1px solid var(--border);
        box-shadow: var(--shadow-sm);
        overflow: hidden;
    }

    .table-card-header {
        padding: 18px 20px;
        border-bottom: 1px solid var(--border);
        display: flex;
        align-items: center;
        justify-content: space-between;
        background: linear-gradient(135deg, #1a56db 0%, #1e40af 100%);
    }

    .table-card-title {
        font-size: 15px;
        font-weight: 700;
        color: white;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .table-card-title svg { width: 17px; height: 17px; opacity: 0.85; }

    .table-toolbar {
        display: flex;
        gap: 10px;
        align-items: center;
    }

    .search-box-wrap {
        position: relative;
        flex: 1;
    }

    .search-box-wrap svg {
        position: absolute;
        left: 12px; top: 50%;
        transform: translateY(-50%);
        width: 15px; height: 15px;
        color: #94a3b8;
        pointer-events: none;
    }

    .search-box {
        width: 100%;
        border: 1.5px solid var(--border);
        border-radius: var(--radius-sm);
        padding: 10px 14px 10px 38px;
        font-size: 13px;
        font-family: 'Plus Jakarta Sans', sans-serif;
        color: var(--text-dark);
        background: white;
        transition: border-color 0.2s, box-shadow 0.2s;
    }

    .search-box:focus { outline: none; border-color: var(--blue-primary); box-shadow: 0 0 0 3px rgba(26,86,219,0.1); }
    .search-box::placeholder { color: #94a3b8; }

    .toolbar-area {
        display: flex;
        flex-direction: column;
        background: var(--white);
    }

    .toolbar-section {
        padding: 18px 20px;
        border-bottom: 1px solid var(--border);
    }

    .toolbar-section:last-child {
        border-bottom: 0;
    }

    .toolbar-section-head {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 12px;
        color: var(--text-dark);
        font-size: 12px;
        font-weight: 800;
        text-transform: uppercase;
    }

    .toolbar-section-head i {
        width: 24px;
        height: 24px;
        border-radius: 6px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: var(--blue-light);
        color: var(--blue-primary);
        font-size: 11px;
    }

    .recipient-filter-form {
        display: grid;
        grid-template-columns: minmax(230px, 1.8fr) repeat(5, minmax(125px, 1fr)) auto;
        gap: 12px;
        align-items: end;
    }

    .filter-field {
        min-width: 0;
    }

    .filter-field label {
        display: flex;
        align-items: center;
        gap: 6px;
        min-height: 16px;
        margin: 0 0 6px;
        color: var(--text-light);
        font-size: 10px;
        font-weight: 800;
        text-transform: uppercase;
    }

    .filter-form-actions {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .filter-select {
        width: 100%;
        border: 1.5px solid var(--border);
        border-radius: var(--radius-sm);
        padding: 9px 30px 9px 11px;
        font-size: 12.5px;
        font-family: 'Plus Jakarta Sans', sans-serif;
        color: var(--text-dark);
        background: white;
        min-width: 0;
        height: 40px;
    }

    .filter-select:focus {
        outline: none;
        border-color: var(--blue-primary);
        box-shadow: 0 0 0 3px rgba(26,86,219,0.1);
    }

    .btn-filter,
    .btn-reset {
        padding: 9px 12px;
        border-radius: var(--radius-sm);
        font-size: 12px;
        font-weight: 700;
        border: 1.5px solid;
        cursor: pointer;
        font-family: 'Plus Jakarta Sans', sans-serif;
        text-decoration: none;
        white-space: nowrap;
        transition: all 0.2s;
        height: 40px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 7px;
    }

    .btn-filter {
        border-color: var(--blue-primary);
        color: var(--blue-primary);
        background: white;
    }

    .btn-filter:hover {
        background: #eff6ff;
    }

    .btn-reset {
        border-color: #cbd5e1;
        color: var(--text-mid);
        background: white;
    }

    .btn-reset:hover {
        background: #f1f5f9;
    }

    .btn-import {
        padding: 9px 14px;
        border-radius: var(--radius-sm);
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        display: flex; align-items: center; gap: 6px;
        transition: all 0.2s;
        white-space: nowrap;
        border: 1.5px dashed #94a3b8;
        background: white;
        color: var(--text-mid);
        position: relative;
        font-family: 'Plus Jakarta Sans', sans-serif;
        height: 40px;
    }

    .btn-import:hover { background: #f1f5f9; border-color: var(--blue-primary); color: var(--blue-primary); }

    .file-input {
        position: absolute; width: 100%; height: 100%;
        top: 0; left: 0; opacity: 0; cursor: pointer;
    }

    .btn-add {
        padding: 9px 16px;
        border-radius: var(--radius-sm);
        font-size: 13px;
        font-weight: 700;
        cursor: pointer;
        display: flex; align-items: center; gap: 6px;
        transition: all 0.2s;
        white-space: nowrap;
        border: none;
        background: linear-gradient(135deg, var(--blue-primary) 0%, #2563eb 100%);
        color: white;
        text-decoration: none;
        font-family: 'Plus Jakarta Sans', sans-serif;
        box-shadow: 0 2px 8px rgba(26,86,219,0.3);
        height: 40px;
    }

    .btn-add:hover {
        background: linear-gradient(135deg, #1d4ed8 0%, #1e40af 100%);
        transform: translateY(-1px);
        box-shadow: var(--shadow-blue);
    }

    .btn-delete-all {
        padding: 9px 14px;
        border-radius: var(--radius-sm);
        font-size: 13px;
        font-weight: 700;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 6px;
        transition: all 0.2s;
        white-space: nowrap;
        border: 1.5px solid #fecaca;
        background: #fef2f2;
        color: var(--red);
        font-family: 'Plus Jakarta Sans', sans-serif;
        height: 40px;
    }

    .btn-delete-all:hover {
        background: var(--red);
        border-color: var(--red);
        color: white;
    }

    .btn-delete-selected {
        padding: 9px 14px;
        border-radius: var(--radius-sm);
        font-size: 13px;
        font-weight: 700;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 6px;
        transition: all 0.2s;
        white-space: nowrap;
        border: 1.5px solid #fecaca;
        background: #ffffff;
        color: var(--red);
        font-family: 'Plus Jakarta Sans', sans-serif;
        height: 40px;
    }

    .btn-delete-selected:hover {
        background: #fee2e2;
    }

    .btn-delete-selected:disabled {
        opacity: 0.6;
        cursor: not-allowed;
        transform: none;
    }

    .toolbar-actions {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 12px 18px;
        width: 100%;
    }

    .toolbar-action-group {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 8px;
    }

    .toolbar-action-group.is-danger {
        margin-left: auto;
    }

    .bulk-group-panel {
        display: grid;
        grid-template-columns: repeat(5, minmax(0, 1fr));
        gap: 12px;
        align-items: end;
        padding: 16px;
        border: 1px solid var(--border);
        border-radius: 10px;
        background: #f8fafc;
    }

    .bulk-group-heading {
        grid-column: 1 / -1;
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 20px;
        padding-bottom: 12px;
        border-bottom: 1px solid var(--border);
    }

    .bulk-group-title {
        display: flex;
        align-items: center;
        gap: 9px;
        color: var(--text-dark);
        font-size: 13px;
        font-weight: 800;
    }

    .bulk-group-title i {
        color: var(--blue-primary);
    }

    .bulk-group-heading .bulk-group-help {
        max-width: 620px;
        text-align: right;
    }

    .bulk-group-field label {
        display: block;
        margin-bottom: 5px;
        color: var(--text-mid);
        font-size: 10px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .4px;
    }

    .bulk-group-field input,
    .bulk-group-field select {
        width: 100%;
        height: 38px;
        border: 1.5px solid var(--border);
        border-radius: var(--radius-sm);
        padding: 8px 10px;
        background: white;
        color: var(--text-dark);
        font: 12.5px 'Plus Jakarta Sans', sans-serif;
    }

    .bulk-group-help {
        color: var(--text-light);
        font-size: 11.5px;
        line-height: 1.5;
    }

    .bulk-group-actions {
        grid-column: 1 / -1;
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 10px;
        padding-top: 2px;
    }

    .bulk-apply-all {
        min-height: 40px;
        padding: 8px 12px;
        border: 1px solid var(--border);
        border-radius: var(--radius-sm);
        display: inline-flex;
        align-items: center;
        gap: 8px;
        color: var(--text-mid);
        background: var(--white);
        font-size: 12px;
        font-weight: 700;
        cursor: pointer;
        margin: 0;
    }

    .search-box-wrap {
        min-width: 0;
    }

    .search-box {
        height: 40px;
    }

    body.dark-mode .toolbar-area,
    body.dark-mode .toolbar-section {
        background: #0f192a;
        border-color: #26354c;
    }

    body.dark-mode .toolbar-section-head,
    body.dark-mode .bulk-group-title {
        color: #e7eef9;
    }

    body.dark-mode .toolbar-section-head i {
        background: #173b79;
        color: #8bb7ff;
    }

    body.dark-mode .filter-field label,
    body.dark-mode .bulk-group-field label,
    body.dark-mode .bulk-group-help {
        color: #91a6c3;
    }

    body.dark-mode .search-box,
    body.dark-mode .filter-select,
    body.dark-mode .bulk-group-field input,
    body.dark-mode .bulk-group-field select {
        background: #162236;
        border-color: #2a3b55;
        color: #edf4ff;
    }

    body.dark-mode .search-box::placeholder,
    body.dark-mode .bulk-group-field input::placeholder {
        color: #7186a5;
    }

    body.dark-mode .btn-filter,
    body.dark-mode .btn-reset,
    body.dark-mode .btn-import,
    body.dark-mode .bulk-apply-all {
        background: #162236;
        border-color: #304461;
        color: #dbe8fb;
    }

    body.dark-mode .btn-filter:hover,
    body.dark-mode .btn-reset:hover,
    body.dark-mode .btn-import:hover,
    body.dark-mode .bulk-apply-all:hover {
        background: #1d2d46;
        border-color: #4c7fd3;
        color: #ffffff;
    }

    body.dark-mode .bulk-group-panel {
        background: #111d30;
        border-color: #2a3b55;
    }

    body.dark-mode .bulk-group-heading {
        border-color: #2a3b55;
    }

    .bulk-checkbox {
        width: 16px;
        height: 16px;
        accent-color: var(--blue-primary);
        cursor: pointer;
    }

    /* ── TABLE ── */
    .table-scroll { overflow-x: auto; }

    table {
        width: 100%;
        border-collapse: collapse;
        min-width: 900px;
    }

    thead th {
        padding: 11px 14px;
        font-size: 10.5px;
        font-weight: 700;
        color: var(--text-light);
        text-transform: uppercase;
        letter-spacing: 0.6px;
        background: #f8fafc;
        border-bottom: 1.5px solid var(--border);
        white-space: nowrap;
        text-align: left;
    }

    tbody td {
        padding: 13px 14px;
        font-size: 13px;
        color: var(--text-dark);
        border-bottom: 1px solid #f1f5f9;
        vertical-align: middle;
    }

    tbody tr:last-child td { border-bottom: none; }

    tbody tr {
        transition: background 0.15s;
    }

    tbody tr:hover { background: #f8fafc; }

    .student-name {
        font-weight: 700;
        color: var(--text-dark);
        font-size: 13px;
        max-width: 130px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .class-badge {
        display: inline-flex;
        align-items: center;
        background: var(--blue-light);
        color: var(--blue-primary);
        font-size: 11px;
        font-weight: 700;
        padding: 3px 9px;
        border-radius: 20px;
        letter-spacing: 0.3px;
    }

    .cell-text {
        font-size: 12px;
        color: var(--text-mid);
        max-width: 120px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .cell-mono {
        font-family: 'JetBrains Mono', monospace;
        font-size: 11.5px;
        color: var(--text-mid);
        max-width: 115px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .cell-note {
        font-size: 11.5px;
        color: var(--text-light);
        font-style: italic;
        max-width: 110px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    /* ── STATUS BADGES ── */
    .status-wrap { display: flex; flex-direction: column; gap: 5px; }

    .badge {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 3px 8px;
        border-radius: 20px;
        font-size: 10px;
        font-weight: 700;
        letter-spacing: 0.4px;
        border: 1px solid;
        width: fit-content;
    }

    .badge svg { width: 9px; height: 9px; }
    .badge-lengkap { background: var(--green-bg); color: var(--green); border-color: var(--green-border); }
    .badge-kurang  { background: var(--yellow-bg); color: var(--yellow); border-color: var(--yellow-border); }
    .badge-valid   { background: #ecfeff; color: #0891b2; border-color: #a5f3fc; }
    .badge-invalid { background: var(--red-bg); color: var(--red); border-color: var(--red-border); }

    /* ── ACTION BUTTONS ── */
    .actions { display: flex; gap: 5px; }

    .btn-action {
        padding: 5px 10px;
        border-radius: 6px;
        font-size: 11.5px;
        font-weight: 600;
        border: 1.5px solid;
        cursor: pointer;
        display: flex; align-items: center; gap: 4px;
        transition: all 0.15s;
        white-space: nowrap;
        font-family: 'Plus Jakarta Sans', sans-serif;
        text-decoration: none;
    }

    .btn-edit   { color: var(--blue-primary); border-color: #bfdbfe; background: #eff6ff; }
    .btn-edit:hover { background: var(--blue-primary); color: white; border-color: var(--blue-primary); }
    .btn-delete { color: var(--red); border-color: var(--red-border); background: var(--red-bg); }
    .btn-delete:hover { background: var(--red); color: white; border-color: var(--red); }

    /* ── RIGHT SIDEBAR ── */
    .sidebar-panel {
        display: flex;
        flex-direction: column;
        gap: 16px;
    }

    .info-card {
        background: var(--white);
        border-radius: var(--radius);
        border: 1px solid var(--border);
        box-shadow: var(--shadow-sm);
        overflow: hidden;
    }

    .info-card-header {
        padding: 14px 18px;
        background: linear-gradient(135deg, #1a56db 0%, #1e40af 100%);
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .info-card-title {
        font-size: 13px;
        font-weight: 700;
        color: white;
        display: flex;
        align-items: center;
        gap: 7px;
    }

    .info-card-title svg { width: 15px; height: 15px; opacity: 0.85; }

    .count-pill {
        background: rgba(255,255,255,0.25);
        color: white;
        font-size: 10px;
        font-weight: 700;
        padding: 2px 7px;
        border-radius: 20px;
    }

    .info-card-body { padding: 16px; }

    /* Quick Summary Panel */
    .summary-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 10px 0;
        border-bottom: 1px dashed #e2e8f0;
    }

    .summary-item:last-child { border-bottom: none; padding-bottom: 0; }

    .summary-label {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 12.5px;
        color: var(--text-mid);
        font-weight: 500;
    }

    .summary-dot {
        width: 9px; height: 9px;
        border-radius: 50%;
        flex-shrink: 0;
    }

    .summary-value {
        font-size: 15px;
        font-weight: 800;
        color: var(--text-dark);
    }

    /* Import Guide Card */
    .guide-steps { display: flex; flex-direction: column; gap: 10px; }

    .guide-step {
        display: flex;
        gap: 10px;
        align-items: flex-start;
    }

    .step-num {
        width: 22px; height: 22px;
        border-radius: 50%;
        background: linear-gradient(135deg, var(--blue-primary), #2563eb);
        color: white;
        font-size: 10px;
        font-weight: 800;
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0;
        margin-top: 1px;
    }

    .step-text { font-size: 12px; color: var(--text-mid); line-height: 1.5; font-weight: 500; }
    .step-text strong { color: var(--text-dark); }

    /* ── PAGINATION ── */
    .pagination-wrap {
        padding: 14px 20px;
        border-top: 1px solid var(--border);
        background: #f8fafc;
    }

    /* ── EMPTY STATE ── */
    .empty-state {
        padding: 60px 20px;
        text-align: center;
    }

    .empty-icon {
        width: 64px; height: 64px;
        margin: 0 auto 16px;
        background: #f1f5f9;
        border-radius: 16px;
        display: flex; align-items: center; justify-content: center;
        color: #94a3b8;
    }

    .empty-icon svg { width: 32px; height: 32px; }

    .empty-title { font-size: 15px; font-weight: 700; color: var(--text-mid); margin-bottom: 6px; }
    .empty-subtitle { font-size: 12.5px; color: var(--text-light); line-height: 1.6; max-width: 280px; margin: 0 auto; }

    /* ── MODALS ── */
    .modal-content {
        border-radius: 16px;
        border: 1px solid var(--border);
        box-shadow: 0 20px 40px rgba(0,0,0,0.15);
        font-family: 'Plus Jakarta Sans', sans-serif;
    }

    .modal-header, .modal-footer {
        padding: 20px 24px;
        border-color: var(--border);
        background: #f8fafc;
    }

    .modal-title {
        font-size: 17px;
        font-weight: 700;
        color: var(--text-dark);
        display: flex; align-items: center; gap: 10px;
    }

    .modal-title svg { width: 18px; height: 18px; color: var(--blue-primary); }

    .modal-body { padding: 24px; }

    .btn-modal-cancel {
        padding: 10px 20px;
        background: white;
        color: var(--text-mid);
        border: 1.5px solid var(--border);
        border-radius: var(--radius-sm);
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        font-family: 'Plus Jakarta Sans', sans-serif;
        transition: all 0.2s;
    }

    .btn-modal-cancel:hover { background: #f1f5f9; }

    .btn-modal-danger {
        padding: 10px 20px;
        background: linear-gradient(135deg, var(--red), #b91c1c);
        color: white;
        border: none;
        border-radius: var(--radius-sm);
        font-size: 13px;
        font-weight: 700;
        cursor: pointer;
        font-family: 'Plus Jakarta Sans', sans-serif;
        transition: all 0.2s;
    }

    .btn-modal-danger:hover { box-shadow: 0 4px 12px rgba(220,38,38,0.3); transform: translateY(-1px); }

    /* ── TOAST ── */
    .toast {
        position: fixed; top: 20px; right: 20px;
        padding: 12px 18px;
        border-radius: 10px;
        box-shadow: var(--shadow-md);
        display: flex; align-items: center; gap: 10px;
        z-index: 9999;
        animation: slideIn 0.3s ease;
        min-width: 240px; max-width: 320px;
        font-size: 13px;
        font-family: 'Plus Jakarta Sans', sans-serif;
        font-weight: 600;
        color: white;
    }

    .toast-success { background: linear-gradient(135deg, #10b981, #059669); }
    .toast-error   { background: linear-gradient(135deg, var(--red), #b91c1c); }
    .toast-info    { background: linear-gradient(135deg, var(--blue-primary), #2563eb); }

    .toast svg { width: 18px; height: 18px; flex-shrink: 0; }

    @keyframes slideIn {
        from { transform: translateX(110%); opacity: 0; }
        to   { transform: translateX(0); opacity: 1; }
    }

    /* ── RESPONSIVE ── */
    @media (max-width: 1280px) {
        .main-layout { grid-template-columns: 1fr; }
        .stats-grid  { grid-template-columns: repeat(2, 1fr); }
        .sidebar-panel { display: grid; grid-template-columns: 1fr 1fr; }
        .bulk-group-panel { grid-template-columns: repeat(2, minmax(150px, 1fr)); }
        .recipient-filter-form { grid-template-columns: repeat(3, minmax(150px, 1fr)); }
        .filter-field:first-child { grid-column: span 2; }
        .filter-form-actions { grid-column: 1 / -1; }
    }

    @media (max-width: 640px) {
        .page-wrapper { padding: 16px; }
        .stats-grid  { grid-template-columns: 1fr; }
        .toolbar-section { padding: 16px; }
        .recipient-filter-form { grid-template-columns: 1fr; }
        .filter-field:first-child,
        .filter-form-actions { grid-column: auto; }
        .filter-form-actions,
        .filter-form-actions > * { width: 100%; }
        .toolbar-actions { align-items: stretch; }
        .toolbar-action-group,
        .toolbar-action-group.is-danger { width: 100%; margin-left: 0; }
        .toolbar-action-group > *,
        .toolbar-action-group form,
        .toolbar-action-group form > button { flex: 1 1 100%; width: 100%; }
        .bulk-group-panel { grid-template-columns: 1fr; }
        .bulk-group-heading { flex-direction: column; gap: 6px; }
        .bulk-group-heading .bulk-group-help { text-align: left; }
        .bulk-group-actions { flex-direction: column; align-items: stretch; }
        .bulk-group-actions > * { width: 100%; }
    }

    @media (max-width: 480px) {
        .page-header {
            align-items: flex-start;
            margin-bottom: 20px;
        }

        .page-header-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
        }

        .page-header-text h1 {
            font-size: 20px;
        }

        .stat-card {
            min-height: 88px;
        }

        .table-card-header {
            padding: 16px;
        }
    }
</style>

<div class="page-wrapper">

    {{-- ── HEADER ── --}}
    <div class="page-header">
        <div class="page-header-icon">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z" />
            </svg>
        </div>
        <div class="page-header-text">
            <h1>{{ __('app.blast.recipient_data_title') }}</h1>
            <p>{{ __('app.blast.recipient_data_subtitle') }}</p>
        </div>
    </div>

    {{-- ── STATS ── --}}
    <div class="stats-grid">
        <div class="stat-card total">
            <div class="stat-icon total">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                </svg>
            </div>
            <div class="stat-info">
                <div class="stat-label">{{ __('app.blast.total_students') }}</div>
                <div class="stat-number">{{ $totalRecipients ?? $recipients->total() }}</div>
            </div>
        </div>
        <div class="stat-card lengkap">
            <div class="stat-icon lengkap">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div class="stat-info">
                <div class="stat-label">{{ __('app.blast.complete_data') }}</div>
                <div class="stat-number">{{ $completeCount ?? $recipients->total() }}</div>
            </div>
        </div>
        <div class="stat-card kurang">
            <div class="stat-icon kurang">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                </svg>
            </div>
            <div class="stat-info">
                <div class="stat-label">{{ __('app.blast.incomplete_data') }}</div>
                <div class="stat-number">{{ $incompleteCount ?? 0 }}</div>
            </div>
        </div>
        <div class="stat-card valid">
            <div class="stat-icon valid">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" />
                </svg>
            </div>
            <div class="stat-info">
                <div class="stat-label">{{ __('app.blast.validated_data') }}</div>
                <div class="stat-number">{{ $validCount ?? $recipients->total() }}</div>
            </div>
        </div>
    </div>

    {{-- ── MAIN LAYOUT ── --}}
    <div class="main-layout">

        {{-- TABLE SECTION --}}
        <div class="table-card">
            {{-- Header Bar --}}
            <div class="table-card-header">
                <div class="table-card-title">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 6.75h12M8.25 12h12m-12 5.25h12M3.75 6.75h.007v.008H3.75V6.75zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zM3.75 12h.007v.008H3.75V12zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm-.375 5.25h.007v.008H3.75v-.008zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                    </svg>
                    {{ __('app.blast.recipient_list') }}
                </div>
                <span class="count-pill">{{ __('app.blast.student_count', ['count' => $recipients->total()]) }}</span>
            </div>

            {{-- Toolbar --}}
            <div class="toolbar-area">
                <div class="toolbar-section">
                    <div class="toolbar-section-head">
                        <i class="fas fa-filter"></i>
                        {{ __('app.blast.recipient_filter_title') }}
                    </div>
                    <form method="GET" action="{{ route('admin.blast.recipients.index') }}" class="recipient-filter-form">
                        <div class="filter-field">
                            <label for="searchInput"><i class="fas fa-search"></i> {{ __('app.blast.search') }}</label>
                            <div class="search-box-wrap">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                                </svg>
                                <input type="text" class="search-box" placeholder="{{ __('app.blast.search_student_placeholder') }}" id="searchInput" name="q" value="{{ $search ?? '' }}">
                            </div>
                        </div>
                        <div class="filter-field">
                            <label for="recipientClassFilter"><i class="fas fa-chalkboard"></i> {{ __('app.blast.class') }}</label>
                            <select name="kelas" id="recipientClassFilter" class="filter-select">
                                <option value="">{{ __('app.blast.all_classes') }}</option>
                                @foreach(($kelasOptions ?? collect()) as $kelasOption)
                                    <option value="{{ $kelasOption }}" @selected(($selectedClass ?? '') === $kelasOption)>{{ $kelasOption }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="filter-field">
                            <label for="recipientLevelFilter"><i class="fas fa-layer-group"></i> {{ __('app.blast.education_level') }}</label>
                            <select name="education_level" id="recipientLevelFilter" class="filter-select">
                                <option value="">{{ __('app.blast.all_education_levels') }}</option>
                                @foreach(($educationLevelOptions ?? []) as $levelValue => $levelLabel)
                                    <option value="{{ $levelValue }}" @selected(($selectedEducationLevel ?? '') === $levelValue)>{{ $levelLabel }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="filter-field">
                            <label for="recipientYearFilter"><i class="fas fa-calendar-alt"></i> {{ __('app.blast.academic_year') }}</label>
                            <select name="academic_year" id="recipientYearFilter" class="filter-select">
                                <option value="">{{ __('app.blast.all_academic_years') }}</option>
                                @foreach(($academicYearOptions ?? collect()) as $academicYearOption)
                                    <option value="{{ $academicYearOption }}" @selected(($selectedAcademicYear ?? '') === $academicYearOption)>{{ $academicYearOption }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="filter-field">
                            <label for="recipientStatusFilter"><i class="fas fa-user-check"></i> {{ __('app.blast.student_status') }}</label>
                            <select name="student_status" id="recipientStatusFilter" class="filter-select">
                                <option value="">{{ __('app.blast.all_student_statuses') }}</option>
                                @foreach(($studentStatusOptions ?? []) as $statusValue => $statusLabel)
                                    <option value="{{ $statusValue }}" @selected(($selectedStudentStatus ?? '') === $statusValue)>{{ __('app.blast.status_' . $statusValue) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="filter-field">
                            <label for="recipientPageSize"><i class="fas fa-list-ol"></i> {{ __('app.blast.per_page') }}</label>
                            <select name="per_page" id="recipientPageSize" class="filter-select">
                                @foreach(($allowedPerPage ?? [20, 50, 100, 200]) as $size)
                                    <option value="{{ $size }}" @selected((int) ($perPage ?? 50) === (int) $size)>{{ __('app.blast.per_page_suffix', ['count' => $size]) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="filter-form-actions">
                            <button type="submit" class="btn-add">
                                <i class="fas fa-search"></i>
                                {{ __('app.blast.apply_filter') }}
                            </button>
                            @if(!empty($search) || !empty($selectedClass) || !empty($selectedEducationLevel) || !empty($selectedAcademicYear) || !empty($selectedStudentStatus))
                                <a href="{{ route('admin.blast.recipients.index', ['per_page' => $perPage ?? 50]) }}" class="btn-reset">
                                    <i class="fas fa-undo"></i>
                                    {{ __('app.blast.reset') }}
                                </a>
                            @endif
                        </div>
                    </form>
                </div>

                <div class="toolbar-section">
                    <div class="toolbar-section-head">
                        <i class="fas fa-sliders-h"></i>
                        {{ __('app.blast.recipient_actions_title') }}
                    </div>
                    <div class="toolbar-actions">
                        <div class="toolbar-action-group">
                            <a href="{{ route('admin.blast.recipients.create') }}" class="btn-add">
                                <i class="fas fa-plus"></i>
                                {{ __('app.blast.add_data') }}
                            </a>
                            <form action="{{ route('admin.blast.recipients.import') }}" method="POST" enctype="multipart/form-data" class="d-inline">
                                @csrf
                                <input type="hidden" name="import_type" value="siswa">
                                <input type="hidden" name="education_level" value="{{ $selectedEducationLevel ?? '' }}">
                                <input type="hidden" name="academic_year" value="{{ $selectedAcademicYear ?? '' }}">
                                <input type="hidden" name="student_status" value="{{ $selectedStudentStatus ?: 'active' }}">
                                <button type="button" class="btn-import" id="importExcelBtn">
                                    <i class="fas fa-file-import"></i>
                                    {{ __('app.blast.import_excel') }}
                                    <input type="file" name="file" class="file-input" id="excelFileInput" accept=".xlsx,.xls,.csv" required>
                                </button>
                            </form>
                            <a
                                href="{{ route('admin.blast.recipients.export', array_filter([
                                    'q' => $search ?? null,
                                    'kelas' => $selectedClass ?? null,
                                    'education_level' => $selectedEducationLevel ?? null,
                                    'academic_year' => $selectedAcademicYear ?? null,
                                    'student_status' => $selectedStudentStatus ?? null,
                                ])) }}"
                                class="btn-filter"
                            >
                                <i class="fas fa-file-export"></i>
                                {{ __('app.blast.export_contacts') }}
                            </a>
                        </div>

                        <div class="toolbar-action-group">
                            <a href="{{ route('admin.blast.recipients.employees.index') }}" class="btn-filter">
                                <i class="fas fa-building"></i>
                                {{ __('app.blast.data_cooperative') }}
                            </a>
                            <a href="{{ route('admin.blast.recipients.employees-ypik.index') }}" class="btn-filter">
                                <i class="fas fa-id-badge"></i>
                                {{ __('app.blast.data_ypik_employees') }}
                            </a>
                            <a href="{{ route('admin.blast.recipients.employees-ypik-pamjaya.index') }}" class="btn-filter">
                                <i class="fas fa-address-card"></i>
                                {{ __('app.blast.data_ypik_pamjaya') }}
                            </a>
                        </div>

                        <div class="toolbar-action-group is-danger">
                            <form id="bulk-delete-form" method="POST" action="{{ route('admin.blast.recipients.bulk-delete') }}" class="d-inline">
                                @csrf
                                @method('DELETE')
                            </form>
                            <button type="submit" class="btn-delete-selected" id="bulkDeleteBtn" form="bulk-delete-form" disabled>
                                <i class="fas fa-trash-alt"></i>
                                {{ __('app.blast.delete_selected') }}
                            </button>

                            <form method="POST" action="{{ route('admin.blast.recipients.destroy-all') }}" class="d-inline" data-confirm-message="{{ __('app.blast.delete_all_students_confirm') }}" onsubmit="return confirm(this.dataset.confirmMessage)">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-delete-all">
                                    <i class="fas fa-trash"></i>
                                    {{ __('app.blast.delete_all') }}
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="toolbar-section">
                    <form method="POST" action="{{ route('admin.blast.recipients.bulk-group') }}" id="bulk-group-form" class="bulk-group-panel">
                        @csrf
                        <input type="hidden" name="filter_q" value="{{ $search ?? '' }}">
                        <input type="hidden" name="filter_kelas" value="{{ $selectedClass ?? '' }}">
                        <input type="hidden" name="filter_education_level" value="{{ $selectedEducationLevel ?? '' }}">
                        <input type="hidden" name="filter_academic_year" value="{{ $selectedAcademicYear ?? '' }}">
                        <input type="hidden" name="filter_student_status" value="{{ $selectedStudentStatus ?? '' }}">
                        <div class="bulk-group-heading">
                            <div class="bulk-group-title">
                                <i class="fas fa-random"></i>
                                {{ __('app.blast.bulk_group_title') }}
                            </div>
                            <div class="bulk-group-help">{{ __('app.blast.bulk_group_help') }}</div>
                        </div>
                        <div class="bulk-group-field">
                            <label for="bulk_target_class">{{ __('app.blast.target_class') }}</label>
                            <input type="text" name="kelas" id="bulk_target_class" placeholder="Contoh: 4A">
                        </div>
                        <div class="bulk-group-field">
                            <label for="bulk_target_level">{{ __('app.blast.target_education_level') }}</label>
                            <select name="education_level" id="bulk_target_level">
                                <option value="">-</option>
                                @foreach(($educationLevelOptions ?? []) as $levelValue => $levelLabel)
                                    <option value="{{ $levelValue }}">{{ $levelLabel }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="bulk-group-field">
                            <label for="bulk_target_year">{{ __('app.blast.target_academic_year') }}</label>
                            <input type="text" name="academic_year" id="bulk_target_year" placeholder="2026/2027">
                        </div>
                        <div class="bulk-group-field">
                            <label for="bulk_target_status">{{ __('app.blast.target_student_status') }}</label>
                            <select name="student_status" id="bulk_target_status">
                                <option value="">-</option>
                                @foreach(($studentStatusOptions ?? []) as $statusValue => $statusLabel)
                                    <option value="{{ $statusValue }}">{{ __('app.blast.status_' . $statusValue) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="bulk-group-field">
                            <label for="bulk_group_notes">{{ __('app.blast.change_notes') }}</label>
                            <input type="text" name="notes" id="bulk_group_notes" placeholder="{{ __('app.blast.bulk_group_title') }}">
                        </div>
                        <div class="bulk-group-actions">
                            <label class="bulk-apply-all">
                                <input type="checkbox" name="apply_all_filtered" value="1" id="applyAllFiltered">
                                {{ __('app.blast.apply_all_filtered') }}
                            </label>
                            <button type="submit" class="btn-add" id="bulkGroupBtn" disabled>
                                <i class="fas fa-check"></i>
                                {{ __('app.blast.apply_group_update') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Table --}}
            <div class="table-scroll">
                <table>
                    <thead>
                        <tr>
                            <th style="width:48px;">
                                <input type="checkbox" class="bulk-checkbox" id="selectAllStudents">
                            </th>
                            <th style="width:90px;">{{ __('app.blast.table_status') }}</th>
                            <th style="width:130px;">{{ __('app.blast.student_name') }}</th>
                            <th style="width:75px;">{{ __('app.blast.education_level') }}</th>
                            <th style="width:75px;">{{ __('app.blast.class') }}</th>
                            <th style="width:105px;">{{ __('app.blast.academic_year') }}</th>
                            <th style="width:105px;">{{ __('app.blast.student_status') }}</th>
                            <th style="width:120px;">{{ __('app.blast.guardian') }}</th>
                            <th style="width:170px;">{{ __('app.blast.guardian_whatsapp') }}</th>
                            <th style="width:145px;">{{ __('app.blast.guardian_email') }}</th>
                            <th style="width:110px;">{{ __('app.blast.notes') }}</th>
                            <th style="width:120px;">{{ __('app.blast.action') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recipients as $r)
                            @php
                                $isComplete = $r->nama_siswa && $r->nama_wali && ($r->wa_wali || $r->wa_wali_2) && $r->email_wali;
                                $waDisplay = trim(implode(' / ', array_filter([$r->wa_wali, $r->wa_wali_2])));
                            @endphp
                            <tr>
                                <td>
                                    <input
                                        type="checkbox"
                                        class="bulk-checkbox student-checkbox"
                                        name="selected_ids[]"
                                        value="{{ $r->id }}"
                                        form="bulk-delete-form"
                                    >
                                </td>
                                <td>
                                    <div class="status-wrap">
                                        <span class="badge {{ $isComplete ? 'badge-lengkap' : 'badge-kurang' }}">
                                            @if($isComplete)
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                                {{ __('app.blast.complete_upper') }}
                                            @else
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" /></svg>
                                                {{ __('app.blast.incomplete_upper') }}
                                            @endif
                                        </span>
                                        @if($r->is_valid)
                                            <span class="badge badge-valid">
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" /></svg>
                                                {{ __('app.blast.valid_upper') }}
                                            </span>
                                        @else
                                            <span class="badge badge-invalid">
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" /></svg>
                                                {{ __('app.blast.invalid_upper') }}
                                            </span>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <div class="student-name" title="{{ $r->nama_siswa }}">{{ $r->nama_siswa }}</div>
                                </td>
                                <td>
                                    <span class="class-badge">{{ $r->education_level ?: __('app.blast.not_assigned') }}</span>
                                </td>
                                <td>
                                    <span class="class-badge">{{ $r->kelas }}</span>
                                </td>
                                <td>
                                    <div class="cell-text">{{ $r->academic_year ?: __('app.blast.not_assigned') }}</div>
                                </td>
                                <td>
                                    <span class="badge {{ in_array($r->student_status, ['active'], true) ? 'badge-valid' : 'badge-kurang' }}">
                                        {{ __('app.blast.status_' . ($r->student_status ?: 'unassigned')) }}
                                    </span>
                                </td>
                                <td>
                                    <div class="cell-text" title="{{ $r->nama_wali }}">{{ $r->nama_wali }}</div>
                                </td>
                                <td>
                                    <div class="cell-mono" title="{{ $waDisplay !== '' ? $waDisplay : '-' }}">{{ $waDisplay !== '' ? $waDisplay : '-' }}</div>
                                </td>
                                <td>
                                    <div class="cell-text" title="{{ $r->email_wali }}">{{ $r->email_wali }}</div>
                                </td>
                                <td>
                                    <div class="cell-note" title="{{ $r->catatan ?? '-' }}">{{ $r->catatan ?? '-' }}</div>
                                </td>
                                <td>
                                    <div class="actions">
                                        <a href="{{ route('admin.blast.recipients.edit', $r->id) }}" class="btn-action btn-edit" title="{{ __('app.blast.edit_recipient') }}" aria-label="{{ __('app.blast.edit_recipient') }}">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:10px;height:10px;"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" /></svg>
                                        </a>
                                        <form method="POST" action="{{ route('admin.blast.recipients.destroy', $r->id) }}" class="d-inline" data-confirm-message="{{ __('app.blast.delete_data_confirm') }}" onsubmit="return confirm(this.dataset.confirmMessage)">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn-action btn-delete" title="{{ __('app.blast.delete_recipient') }}" aria-label="{{ __('app.blast.delete_recipient') }}">
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:10px;height:10px;"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" /></svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="12">
                                    <div class="empty-state">
                                        <div class="empty-icon">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m5.231 13.481L15 17.25m-4.5-15H5.625c-.621 0-1.125.504-1.125 1.125v16.5c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9zm3.75 11.625a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                                            </svg>
                                        </div>
                                        <div class="empty-title">{{ __('app.blast.no_student_data_title') }}</div>
                                        <div class="empty-subtitle">{{ __('app.blast.no_student_data_subtitle', ['button' => __('app.blast.add_data')]) }}</div>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($recipients->hasPages())
                <div class="pagination-wrap">
                    {{ $recipients->links() }}
                </div>
            @endif
        </div>

    </div>
</div>

{{-- Delete Confirmation Modal (unchanged functionality) --}}
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0">
                <button type="button" class="btn-close" data-bs-dismiss="modal">×</button>
            </div>
            <div class="modal-body text-center">
                <div style="width:60px;height:60px;margin:0 auto 16px;background:#fef2f2;border-radius:14px;display:flex;align-items:center;justify-content:center;color:#dc2626;">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width:30px;height:30px;">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                    </svg>
                </div>
                <h5 style="font-size:16px;font-weight:700;color:#1a202c;margin-bottom:6px;">{{ __('app.blast.delete_student_title') }}</h5>
                <p style="font-size:13px;color:#718096;">{{ __('app.blast.delete_student_desc') }}</p>
            </div>
            <div class="modal-footer border-0 justify-content-center">
                <button type="button" class="btn-modal-cancel" data-bs-dismiss="modal">{{ __('app.blast.cancel') }}</button>
                <button type="button" class="btn-modal-danger" id="confirmDeleteBtn">{{ __('app.blast.yes_delete') }}</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    window.recipientText = {
        deleteSelected: @json(__('app.blast.delete_selected')),
        chooseMinOneDelete: @json(__('app.blast.choose_min_one_recipient_delete')),
        deleteSelectedConfirm: @json(__('app.blast.delete_selected_students_confirm', ['count' => '__COUNT__'])),
        groupTargetRequired: @json(__('app.blast.recipient_group_target_required')),
    };

    // Import form submission
    const excelFileInput = document.getElementById('excelFileInput');
    if (excelFileInput) {
        excelFileInput.addEventListener('change', function() {
            if (this.files.length > 0) {
                this.closest('form').submit();
            }
        });
    }

    initBulkSelection();

    // Toast notifications
    @if(session('success'))
        showToast('{{ session('success') }}', 'success');
    @endif

    @if(session('error'))
        showToast('{{ session('error') }}', 'error');
    @endif
});

function showToast(message, type = 'success') {
    document.querySelectorAll('.toast').forEach(t => t.remove());

    const icons = {
        success: `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>`,
        error:   `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" /></svg>`,
        info:    `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" /></svg>`
    };

    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    toast.innerHTML = `${icons[type] || icons.info}<div style="flex:1">${message}</div><button onclick="this.parentElement.remove()" style="background:none;border:none;color:white;cursor:pointer;font-size:18px;line-height:1;opacity:0.8;">×</button>`;
    document.body.appendChild(toast);

    setTimeout(() => { if (toast.parentElement) toast.remove(); }, 4000);
}

function initBulkSelection() {
    const form = document.getElementById('bulk-delete-form');
    const selectAll = document.getElementById('selectAllStudents');
    const checkboxes = Array.from(document.querySelectorAll('.student-checkbox'));
    const deleteBtn = document.getElementById('bulkDeleteBtn');
    const groupForm = document.getElementById('bulk-group-form');
    const groupBtn = document.getElementById('bulkGroupBtn');
    const applyAllFiltered = document.getElementById('applyAllFiltered');

    if (!form || !deleteBtn) {
        if (deleteBtn) {
            deleteBtn.disabled = true;
        }
        return;
    }

    function updateState() {
        const selected = checkboxes.filter(cb => cb.checked);
        const selectedCount = selected.length;
        const totalCount = checkboxes.length;

        deleteBtn.disabled = selectedCount === 0;
        if (groupBtn) {
            groupBtn.disabled = selectedCount === 0 && !applyAllFiltered?.checked;
        }
        const text = window.recipientText || {};
        deleteBtn.textContent = selectedCount > 0
            ? `${text.deleteSelected || 'Delete Selected'} (${selectedCount})`
            : (text.deleteSelected || 'Delete Selected');

        if (selectAll) {
            selectAll.checked = selectedCount > 0 && selectedCount === totalCount;
            selectAll.indeterminate = selectedCount > 0 && selectedCount < totalCount;
        }
    }

    if (selectAll) {
        selectAll.addEventListener('change', () => {
            const checked = selectAll.checked;
            checkboxes.forEach(cb => { cb.checked = checked; });
            updateState();
        });
    }

    checkboxes.forEach(cb => cb.addEventListener('change', updateState));
    if (applyAllFiltered) {
        applyAllFiltered.addEventListener('change', updateState);
    }

    form.addEventListener('submit', (event) => {
        const selected = checkboxes.filter(cb => cb.checked);
        const text = window.recipientText || {};
        if (selected.length === 0) {
            event.preventDefault();
            alert(text.chooseMinOneDelete || 'Select at least one recipient to delete.');
            return;
        }

        const confirmText = (text.deleteSelectedConfirm || 'Delete __COUNT__ selected recipients?').replace('__COUNT__', selected.length);
        if (!confirm(confirmText)) {
            event.preventDefault();
        }
    });

    if (groupForm) {
        groupForm.addEventListener('submit', (event) => {
            const selected = checkboxes.filter(cb => cb.checked);
            const text = window.recipientText || {};
            const hasTarget = Array.from(groupForm.querySelectorAll('[name="kelas"], [name="education_level"], [name="academic_year"], [name="student_status"]'))
                .some(input => String(input.value || '').trim() !== '');

            groupForm.querySelectorAll('.bulk-group-recipient-id').forEach(input => input.remove());

            if (selected.length === 0 && !applyAllFiltered?.checked) {
                event.preventDefault();
                alert(text.chooseMinOneDelete || 'Select at least one recipient.');
                return;
            }

            if (!hasTarget) {
                event.preventDefault();
                alert(text.groupTargetRequired || 'Choose at least one target grouping field.');
                return;
            }

            selected.forEach(checkbox => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'selected_ids[]';
                input.value = checkbox.value;
                input.className = 'bulk-group-recipient-id';
                groupForm.appendChild(input);
            });
        });
    }

    updateState();
}
</script>

@endsection
