@extends('layouts.app')

@section('title', __('app.blast.whatsapp_title'))

@section('content')

<style>
@import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');

:root {
    --navy:         #1e2a4a;
    --navy-light:   #2d3d66;
    --navy-mid:     #243156;
    --blue-primary: #2563eb;
    --blue-mid:     #3b82f6;
    --blue-light:   #dbeafe;
    --blue-lighter: #eff6ff;
    --blue-border:  #bfdbfe;
    --accent:       #1d4ed8;
    --wa-green:     #25d366;
    --wa-dark:      #128c7e;
    --text-dark:    #0f172a;
    --text-mid:     #1e293b;
    --text-muted:   #64748b;
    --text-light:   #94a3b8;
    --border:       #e2e8f0;
    --bg:           #f0f4fd;
    --white:        #ffffff;
    --green:        #16a34a;
    --green-bg:     #dcfce7;
    --green-border: #86efac;
    --red:          #dc2626;
    --red-bg:       #fee2e2;
    --red-border:   #fca5a5;
    --yellow:       #d97706;
    --yellow-bg:    #fef3c7;
    --yellow-border:#fcd34d;
    --shadow-sm:    0 1px 4px rgba(15,23,42,.06);
    --shadow:       0 4px 20px rgba(15,23,42,.09);
    --shadow-lg:    0 8px 32px rgba(15,23,42,.13);
    --shadow-blue:  0 8px 24px rgba(37,99,235,.18);
    --radius:       14px;
    --radius-sm:    9px;
    --radius-xs:    6px;
}

*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

body,
.content-wrapper,
.main-content { background: var(--bg) !important; font-family: 'Plus Jakarta Sans', sans-serif !important; }

/* ─── PAGE WRAPPER ─────────────────────────── */
.wa-page {
    padding: 20px;
    min-height: 100vh;
    background: var(--bg);
    font-family: 'Plus Jakarta Sans', sans-serif;
    color: var(--text-dark);
}

/* ─── PAGE HEADER ──────────────────────────── */
.wa-page-header {
    position: relative;
    overflow: hidden;
    display: flex;
    align-items: center;
    gap: 18px;
    padding: 20px 26px;
    background: linear-gradient(135deg, var(--navy) 0%, var(--navy-light) 100%);
    border-radius: var(--radius);
    margin-bottom: 18px;
    box-shadow: var(--shadow-lg);
}
.wa-page-header::before {
    content: '';
    position: absolute; top: -50px; right: -50px;
    width: 180px; height: 180px;
    background: radial-gradient(circle, rgba(37,211,102,.16) 0%, transparent 70%);
    border-radius: 50%; pointer-events: none;
}
.wa-page-header::after {
    content: '';
    position: absolute; bottom: -40px; left: 28%;
    width: 140px; height: 140px;
    background: radial-gradient(circle, rgba(59,130,246,.13) 0%, transparent 70%);
    border-radius: 50%; pointer-events: none;
}
.wa-header-icon {
    position: relative;
    width: 54px; height: 54px; flex-shrink: 0;
    background: linear-gradient(135deg, var(--wa-green), var(--wa-dark));
    border-radius: 14px;
    display: flex; align-items: center; justify-content: center;
    box-shadow: 0 6px 20px rgba(37,211,102,.42);
}
.wa-header-title  { font-size: 23px; font-weight: 800; color: #fff; letter-spacing: -.4px; line-height: 1.15; }
.wa-header-sub    { font-size: 13px; color: rgba(255,255,255,.55); font-weight: 500; margin-top: 2px; }
.wa-header-actions { margin-left: auto; display: flex; align-items: center; gap: 10px; }
.wa-header-btn {
    border: none; border-radius: 999px; padding: 8px 14px;
    font-size: 12px; font-weight: 800; letter-spacing: .02em;
    color: var(--navy); background: #ffffff; text-decoration: none;
    box-shadow: 0 6px 16px rgba(15,23,42,.18);
    display: inline-flex; align-items: center; gap: 6px;
}
.wa-header-btn:hover { opacity: .9; }

/* ─── ALERTS ────────────────────────────────── */
.wa-alert {
    display: flex; align-items: center; gap: 10px;
    padding: 13px 17px; border-radius: var(--radius-sm);
    font-size: 13.5px; font-weight: 600; margin-bottom: 14px;
}
.wa-alert.success { background: #ecfdf5; color: #065f46; border: 1px solid #a7f3d0; }
.wa-alert.error   { background: #fef2f2; color: #991b1b; border: 1px solid #fecaca; }

/* ─── SHARED CARD ───────────────────────────── */
.wa-card {
    background: var(--white);
    border: 1px solid var(--blue-border);
    border-radius: var(--radius);
    box-shadow: var(--shadow);
}

/* ─── SECTION TITLE ─────────────────────────── */
.s-title {
    display: flex; align-items: center; gap: 9px;
    font-size: 15px; font-weight: 800; color: var(--navy);
    letter-spacing: -.2px; margin-bottom: 16px;
}
.s-title .s-icon {
    width: 30px; height: 30px; flex-shrink: 0;
    background: var(--blue-lighter); border-radius: 8px;
    display: flex; align-items: center; justify-content: center;
    color: var(--blue-primary); font-size: 13px;
}

/* ─── ACTION BUTTONS ──────────────────────── */
/* ─── BUTTONS ───────────────────────────────── */
.campaign-btn {
    border: none; border-radius: var(--radius-sm); padding: 8px 14px;
    color: #fff; font-size: 12.5px; font-weight: 700; font-family: inherit;
    cursor: pointer; transition: opacity .15s, transform .12s, box-shadow .15s;
    white-space: nowrap;
}
.campaign-btn:hover           { opacity: .9; transform: translateY(-1px); }
.campaign-btn.info    { background: linear-gradient(135deg, var(--blue-primary), var(--blue-mid)); box-shadow: 0 4px 12px rgba(37,99,235,.25); }
.campaign-btn.warning { background: linear-gradient(135deg, #f59e0b, #d97706); box-shadow: 0 4px 12px rgba(245,158,11,.25); }
.campaign-btn.success { background: linear-gradient(135deg, #22c55e, #16a34a); box-shadow: 0 4px 12px rgba(34,197,94,.25); }
.campaign-btn.danger  { background: linear-gradient(135deg, #ef4444, #dc2626); box-shadow: 0 4px 12px rgba(239,68,68,.25); }
.campaign-btn.tiny    { padding: 5px 10px; font-size: 11px; }

/* ─── STATS GRID ────────────────────────────── */
.wa-stats-grid {
    display: grid;
    grid-template-columns: repeat(4,1fr);
    gap: 14px; margin-bottom: 18px;
}
.wa-stat-card {
    background: var(--white); border: 1px solid var(--blue-border);
    border-radius: var(--radius); padding: 18px 20px;
    display: flex; justify-content: space-between; align-items: center;
    box-shadow: var(--shadow); position: relative; overflow: hidden;
    transition: transform .2s, box-shadow .2s;
}
.wa-stat-card::before {
    content: ''; position: absolute;
    top: 0; left: 0; width: 4px; height: 100%; border-radius: 4px 0 0 4px;
}
.wa-stat-card.c-blue::before   { background: var(--blue-primary); }
.wa-stat-card.c-green::before  { background: #22c55e; }
.wa-stat-card.c-red::before    { background: #ef4444; }
.wa-stat-card.c-yellow::before { background: #f59e0b; }
.wa-stat-card:hover { transform: translateY(-2px); box-shadow: var(--shadow-blue); }
.stat-label { font-size: 11.5px; color: var(--text-muted); font-weight: 600; text-transform: uppercase; letter-spacing: .04em; margin-bottom: 6px; }
.stat-value { font-size: 32px; font-weight: 800; color: var(--navy); letter-spacing: -1.5px; line-height: 1; }
.stat-icon-box {
    width: 48px; height: 48px; border-radius: 12px;
    display: flex; align-items: center; justify-content: center; flex-shrink: 0;
}
.stat-icon-box.c-blue   { background: var(--blue-light);  color: var(--blue-primary); }
.stat-icon-box.c-green  { background: #dcfce7; color: #16a34a; }
.stat-icon-box.c-red    { background: #fee2e2; color: #dc2626; }
.stat-icon-box.c-yellow { background: #fef3c7; color: #d97706; }

/* ─── MAIN GRID ─────────────────────────────── */
.wa-device-card { padding: 18px 22px; margin-bottom: 18px; }
.wa-device-grid { display: grid; grid-template-columns: 1.1fr .9fr; gap: 16px; align-items: stretch; }
.wa-device-status-row { display: flex; align-items: center; gap: 10px; margin-bottom: 10px; }
.wa-device-status-badge {
    padding: 6px 12px; border-radius: 999px; font-size: 12px; font-weight: 800; letter-spacing: .02em;
    border: 1px solid transparent; display: inline-flex; align-items: center; gap: 6px;
}
.wa-device-status-badge::before { content: ''; width: 6px; height: 6px; border-radius: 50%; background: currentColor; }
.wa-device-status-badge.connected { background: var(--green-bg); color: var(--green); border-color: var(--green-border); }
.wa-device-status-badge.qr { background: var(--yellow-bg); color: var(--yellow); border-color: var(--yellow-border); }
.wa-device-status-badge.disconnected { background: var(--red-bg); color: var(--red); border-color: var(--red-border); }
.wa-device-status-badge.init { background: var(--blue-lighter); color: var(--blue-primary); border-color: var(--blue-border); }
.wa-device-sub { font-size: 12px; color: var(--text-muted); font-weight: 500; }
.wa-device-meta { display: grid; gap: 6px; margin: 12px 0 14px; }
.wa-device-meta-row { display: flex; justify-content: space-between; gap: 10px; font-size: 12.5px; }
.wa-device-meta-row .meta-label { color: var(--text-muted); font-weight: 600; }
.wa-device-meta-row .meta-value { color: var(--text-dark); font-weight: 700; text-align: right; word-break: break-all; }
.wa-device-actions { display: flex; align-items: center; gap: 12px; flex-wrap: wrap; }
.wa-device-hint { font-size: 11.5px; color: var(--text-muted); }

.wa-provider-info { padding: 16px 20px; margin-bottom: 16px; }
.wa-provider-grid { display: grid; grid-template-columns: 1.1fr .9fr; gap: 16px; align-items: center; }
.wa-provider-title { font-size: 13px; font-weight: 800; color: var(--navy); }
.wa-provider-note { font-size: 11.5px; color: var(--text-muted); margin-top: 4px; }
.wa-provider-badge {
    padding: 6px 12px; border-radius: 999px; font-size: 11px; font-weight: 800; letter-spacing: .02em;
    border: 1px solid transparent; display: inline-flex; align-items: center; gap: 6px;
}
.wa-provider-badge.gateway { background: var(--blue-lighter); color: var(--blue-primary); border-color: var(--blue-border); }
.wa-provider-badge.wablas { background: var(--yellow-bg); color: var(--yellow); border-color: var(--yellow-border); }
.wa-provider-row { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }

.wa-active-device {
    display: flex; flex-direction: column; gap: 6px;
    border: 1px dashed var(--blue-border); border-radius: var(--radius-sm);
    padding: 12px 14px; background: var(--blue-lighter);
}
.wa-active-device .label { font-size: 11px; font-weight: 700; color: var(--text-muted); }
.wa-active-device .value { font-size: 12.5px; font-weight: 800; color: var(--text-dark); }

.wa-device-select-grid {
    display: grid; grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 10px; margin-top: 8px;
}
.wa-device-select-item { display: flex; flex-direction: column; gap: 6px; }
.wa-device-select-item label { font-size: 12px; font-weight: 700; color: var(--text-muted); }
.wa-qr-box {
    border: 1px dashed var(--blue-border); border-radius: var(--radius-sm);
    padding: 14px; height: 100%; background: var(--blue-lighter);
    display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 8px;
}
.wa-qr-title { font-size: 12.5px; font-weight: 800; color: var(--navy); }
.wa-qr-img { max-width: 240px; width: 100%; border-radius: 10px; background: #fff; padding: 8px; border: 1px solid var(--blue-border); display: none; }
.wa-qr-placeholder { font-size: 12px; color: var(--text-muted); text-align: center; }

.wa-main-grid { display: flex; flex-direction: column; gap: 16px; margin-bottom: 18px; }

.wa-top-row {
    display: grid;
    grid-template-columns: 370px minmax(0,1fr);
    gap: 16px;
}

/* ─── RECIPIENT CARD ────────────────────────── */
.wa-recipient-card, .wa-message-card, .wa-activity-card { padding: 20px 22px; }

.phone-row { display: flex; gap: 8px; margin-bottom: 12px; }
.wa-phone-input {
    flex: 1; height: 44px;
    border: 1px solid var(--blue-border); border-radius: var(--radius-sm);
    padding: 0 13px; font-size: 13px; font-family: inherit; font-weight: 500;
    background: var(--blue-lighter); color: var(--text-dark);
    transition: border-color .2s, box-shadow .2s, background .2s;
}
.wa-phone-input:focus {
    outline: none; border-color: var(--blue-mid); background: var(--white);
    box-shadow: 0 0 0 3px rgba(59,130,246,.15);
}
.wa-phone-input::placeholder { color: var(--text-light); }

.wa-add-btn {
    width: 44px; height: 44px; flex-shrink: 0;
    background: linear-gradient(135deg, var(--navy), var(--blue-primary));
    border: none; border-radius: var(--radius-sm);
    color: #fff; font-size: 24px;
    cursor: pointer; display: flex; align-items: center; justify-content: center;
    box-shadow: 0 4px 14px rgba(37,99,235,.32);
    transition: opacity .15s, transform .12s;
}
.wa-add-btn:hover { opacity: .9; transform: translateY(-1px); }

.wa-excel-import {
    display: flex; align-items: center; justify-content: center; gap: 8px;
    padding: 11px 14px; margin-bottom: 12px;
    background: var(--blue-lighter); border: 1.5px dashed var(--blue-border);
    border-radius: var(--radius-sm); cursor: pointer;
    color: var(--accent); font-size: 13px; font-weight: 600;
    transition: all .2s;
}
.wa-excel-import:hover { background: var(--blue-light); border-color: var(--blue-primary); }

.wa-excel-info {
    font-size: 11.5px; color: var(--text-muted); margin-bottom: 10px;
    padding: 8px 11px; background: var(--blue-lighter);
    border: 1px solid var(--blue-border); border-radius: 8px; line-height: 1.5;
}

.recipient-list {
    min-height: 100px; max-height: 280px; overflow-y: auto;
    border: 1px solid var(--blue-border); border-radius: var(--radius-sm);
    background: var(--blue-lighter); padding: 8px; margin-bottom: 14px;
}
.recipient-list::-webkit-scrollbar { width: 4px; }
.recipient-list::-webkit-scrollbar-thumb { background: var(--blue-border); border-radius: 4px; }
.recipient-status { text-align: center; color: var(--text-muted); padding: 26px; font-size: 13px; font-weight: 500; font-style: italic; }

.recipient-item {
    display: flex; justify-content: space-between; align-items: center;
    padding: 8px 11px; background: var(--white);
    border: 1px solid var(--border); border-radius: 8px; margin-bottom: 6px;
    transition: border-color .15s;
}
.recipient-item:hover { border-color: var(--blue-border); }
.recipient-number { font-size: 13px; color: var(--text-dark); font-weight: 600; }
.remove-recipient {
    background: none; border: none; color: var(--red); cursor: pointer;
    font-size: 20px; width: 24px; height: 24px;
    display: flex; align-items: center; justify-content: center; border-radius: 4px; transition: background .15s;
}
.remove-recipient:hover { background: var(--red-bg); }

/* DB Section */
.recipient-db-section {
    border: 1px solid var(--blue-border); border-radius: var(--radius-sm);
    padding: 12px; background: var(--blue-lighter);
}
.recipient-db-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px; }
.recipient-db-title  { font-size: 12.5px; font-weight: 800; color: var(--navy); }
.recipient-db-count  { font-size: 11.5px; color: var(--text-muted); margin-bottom: 8px; }
.recipient-db-search-input {
    width: 100%; border: 1px solid var(--blue-border); border-radius: 8px;
    padding: 7px 11px; font-size: 12px; font-family: inherit;
    background: var(--white); color: var(--text-dark); transition: border-color .2s;
}
.recipient-db-search-input:focus { outline: none; border-color: var(--blue-mid); }
.recipient-db-class-filter {
    width: 100%; border: 1px solid var(--blue-border); border-radius: 8px;
    padding: 7px 11px; font-size: 12px; font-family: inherit;
    background: var(--white); color: var(--text-dark); transition: border-color .2s;
}
.recipient-db-class-filter:focus { outline: none; border-color: var(--blue-mid); }
.recipient-db-filters {
    margin-bottom: 8px;
    display: grid;
    grid-template-columns: minmax(0, 1fr) 130px;
    gap: 6px;
}
.recipient-db-list {
    max-height: 220px; overflow-y: auto;
    display: flex; flex-direction: column; gap: 6px;
}
.recipient-db-list::-webkit-scrollbar { width: 4px; }
.recipient-db-list::-webkit-scrollbar-thumb { background: var(--blue-border); border-radius: 4px; }
.recipient-db-item {
    display: flex; align-items: flex-start; gap: 9px;
    background: var(--white); border: 1px solid var(--border); border-radius: 9px;
    padding: 8px 10px; cursor: pointer; transition: border-color .15s, background .15s;
}
.recipient-db-item:hover { border-color: var(--blue-border); background: var(--blue-lighter); }
.recipient-db-checkbox { margin-top: 3px; accent-color: var(--blue-primary); }
.recipient-db-name  { font-size: 12px; font-weight: 700; color: var(--text-dark); }
.recipient-db-phone { font-size: 11px; color: var(--text-muted); margin-top: 1px; }
.recipient-db-empty { font-size: 12px; color: var(--text-muted); font-style: italic; }
.recipient-db-info  { display: flex; flex-direction: column; gap: 2px; }

.btn-select-db {
    border: 1px solid var(--blue-border); color: var(--accent);
    background: var(--white); border-radius: 999px;
    font-size: 11.5px; font-weight: 700; font-family: inherit;
    padding: 4px 11px; cursor: pointer; transition: background .15s;
}
.btn-select-db:hover { background: var(--blue-light); }

/* ─── FORM ELEMENTS ─────────────────────────── */
.form-group { margin-bottom: 14px; }
.form-label  { display: block; font-size: 12px; font-weight: 700; color: var(--text-mid); letter-spacing: .03em; margin-bottom: 6px; }
.form-input, .form-select, .form-textarea {
    width: 100%; border: 1px solid var(--blue-border); border-radius: var(--radius-sm);
    padding: 9px 13px; font-size: 13px; font-family: inherit; font-weight: 500;
    background: var(--blue-lighter); color: var(--text-dark);
    transition: border-color .2s, box-shadow .2s, background .2s;
}
.form-input:focus, .form-select:focus, .form-textarea:focus {
    outline: none; border-color: var(--blue-mid); background: var(--white);
    box-shadow: 0 0 0 3px rgba(59,130,246,.15);
}
.form-input::placeholder, .form-textarea::placeholder { color: var(--text-light); }
.form-select { height: auto; padding: 9px 13px; cursor: pointer; }
.form-textarea { min-height: 160px; resize: vertical; line-height: 1.6; }

/* Template row */
.template-section { display: flex; align-items: center; gap: 10px; margin-bottom: 14px; }
.template-label   { font-size: 12px; font-weight: 700; color: var(--text-mid); min-width: 72px; flex-shrink: 0; }
.template-select  { flex: 1; }
.template-actions {
    margin-top: 8px;
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
}
.template-action-link {
    border: 1px solid var(--blue-border);
    border-radius: var(--radius-xs);
    background: var(--white);
    color: var(--accent);
    text-decoration: none;
    font-size: 11px;
    font-weight: 700;
    padding: 6px 10px;
    transition: .15s;
}
.template-action-link:hover {
    border-color: var(--blue-mid);
    box-shadow: 0 3px 10px rgba(37,99,235,.12);
    color: var(--blue-primary);
}

.template-preview-box {
    min-height: 100px; border: 1px solid var(--blue-border);
    border-radius: var(--radius-sm); background: var(--blue-lighter);
    padding: 11px 13px; font-size: 12px; color: var(--text-muted);
    white-space: pre-wrap; font-style: italic; line-height: 1.55;
}

/* Selected templates */
.selected-templates {
    display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 14px;
    padding: 10px 12px; background: var(--blue-lighter);
    border: 1px solid var(--blue-border); border-radius: var(--radius-sm); min-height: 46px;
}
.template-tag {
    display: flex; align-items: center; gap: 7px;
    padding: 5px 12px; background: var(--white);
    border: 1px solid var(--blue-border); border-radius: 999px;
    font-size: 12px; color: var(--accent); font-weight: 600;
}
.remove-tag {
    background: none; border: none; color: var(--text-light); cursor: pointer;
    font-size: 16px; width: 16px; height: 16px;
    display: flex; align-items: center; justify-content: center; border-radius: 50%; transition: all .15s;
}
.remove-tag:hover { background: var(--red-bg); color: var(--red); }

/* Override matrix */
.recipient-message-note { font-size: 11.5px; color: var(--text-muted); margin-bottom: 8px; }
.recipient-message-matrix {
    max-height: 240px; overflow-y: auto;
    border: 1px solid var(--blue-border); border-radius: var(--radius-sm);
    background: var(--white); padding: 10px; display: flex; flex-direction: column; gap: 10px;
}
.message-override-item { border: 1px solid var(--border); border-radius: 10px; background: #fafafa; padding: 10px; }
.message-override-item.mode-template { border-color: #a7f3d0; background: #f0fdf4; }
.message-override-item.mode-manual   { border-color: var(--blue-border); background: var(--blue-lighter); }
.message-override-item.mode-global   { border-color: var(--yellow-border); background: var(--yellow-bg); }
.message-override-head { display: flex; justify-content: space-between; align-items: center; gap: 8px; margin-bottom: 8px; }
.message-override-actions { display: flex; align-items: center; gap: 6px; }
.message-override-title  { font-size: 12px; font-weight: 700; color: var(--text-dark); }
.message-override-badge  { font-size: 10px; font-weight: 700; border-radius: 999px; padding: 3px 8px; }
.message-override-badge.mode-template { background: #d1fae5; color: #065f46; }
.message-override-badge.mode-manual   { background: var(--blue-light); color: var(--accent); }
.message-override-badge.mode-global   { background: var(--yellow-bg); color: var(--yellow); }
.message-override-remove {
    width: 22px; height: 22px; border: 1px solid var(--red-border); border-radius: 50%;
    background: var(--red-bg); color: var(--red); font-size: 14px; cursor: pointer;
    display: inline-flex; align-items: center; justify-content: center; transition: .15s;
}
.message-override-remove:hover { background: #fecaca; }
.message-override-file-wrap  { margin-top: 8px; }
.message-override-file-label { font-size: 11px; font-weight: 600; color: var(--text-mid); margin-bottom: 6px; }
.message-override-file-input { font-size: 11px; width: 100%; }
.message-override-file-list  { margin-top: 6px; display: flex; flex-direction: column; gap: 4px; }
.message-override-file-item  {
    display: flex; justify-content: space-between; align-items: center; gap: 6px;
    padding: 4px 8px; border: 1px solid var(--border); border-radius: 6px;
    background: var(--white); font-size: 11px; color: var(--text-mid);
}
.message-override-file-remove {
    border: 1px solid var(--red-border); background: var(--red-bg); color: var(--red);
    border-radius: 50%; width: 18px; height: 18px; font-size: 11px; cursor: pointer;
}
.message-override-file-empty { font-size: 11px; color: var(--text-light); }
.message-override-mode { display: flex; gap: 12px; font-size: 12px; color: var(--text-mid); margin-bottom: 8px; }
.message-override-mode label { display: flex; align-items: center; gap: 4px; margin: 0; font-weight: 600; }
.message-override-text {
    width: 100%; min-height: 72px; border: 1px solid var(--blue-border);
    border-radius: 8px; padding: 8px 10px; font-size: 12px; font-family: inherit;
    outline: none; resize: vertical; transition: border-color .2s;
}
.message-override-text:focus    { border-color: var(--blue-mid); }
.message-override-text:disabled { background: #f3f4f6; color: var(--text-light); }
.message-override-hint { font-size: 11px; color: var(--text-muted); margin-top: 6px; }
.global-default-toggle {
    display: flex; align-items: flex-start; gap: 8px; margin-top: 10px;
    font-size: 12px; color: var(--text-muted); font-weight: 500;
}

/* Message editor */
.message-editor { margin-bottom: 14px; }

/* Footer + attach */
.message-footer { display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px; flex-wrap: wrap; gap: 8px; }
.attachment-buttons { display: flex; gap: 8px; }
.attach-btn {
    display: flex; align-items: center; gap: 7px;
    padding: 8px 14px; background: var(--blue-lighter);
    border: 1px solid var(--blue-border); border-radius: var(--radius-sm);
    cursor: pointer; color: var(--accent); font-size: 13px; font-weight: 600;
    font-family: inherit; transition: all .15s;
}
.attach-btn:hover { background: var(--blue-light); border-color: var(--blue-primary); }
.char-count { font-size: 11.5px; color: var(--text-light); font-weight: 500; }

.attachment-wrap {
    margin-bottom: 14px; border: 1px solid var(--blue-border);
    border-radius: var(--radius-sm); padding: 12px; background: var(--blue-lighter);
}
.attachment-wrap label { display: block; font-size: 12px; font-weight: 700; color: var(--text-mid); margin-bottom: 8px; }
.attachment-hint { font-size: 11px; color: var(--text-muted); margin-top: 6px; }

/* Send button */
.wa-send-btn {
    width: 100%; height: 50px;
    background: linear-gradient(135deg, var(--navy), var(--blue-primary));
    color: #fff; border: none; border-radius: var(--radius-sm);
    font-size: 14.5px; font-weight: 700; font-family: inherit; cursor: pointer;
    display: flex; align-items: center; justify-content: center; gap: 9px;
    box-shadow: 0 6px 22px rgba(37,99,235,.32);
    transition: transform .2s, box-shadow .2s, opacity .15s; letter-spacing: .02em;
}
.wa-send-btn:hover    { transform: translateY(-2px); box-shadow: 0 10px 28px rgba(37,99,235,.4); }
.wa-send-btn:disabled { opacity: .6; cursor: not-allowed; transform: none; }
.wa-gateway-note {
    margin-top: 10px;
    font-size: 12px;
    font-weight: 600;
    color: var(--yellow);
    background: var(--yellow-bg);
    border: 1px solid var(--yellow-border);
    border-radius: var(--radius-sm);
    padding: 8px 12px;
}

/* ─── ACTIVITY LOG ──────────────────────────── */
.activity-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; flex-wrap: wrap; gap: 10px; }
.activity-header-actions { display: flex; align-items: center; gap: 10px; }
.search-small {
    display: flex; align-items: center; gap: 7px; padding: 8px 12px;
    background: var(--blue-lighter); border: 1px solid var(--blue-border);
    border-radius: var(--radius-sm); width: 250px;
}
.search-input-small {
    flex: 1; border: none; background: transparent; outline: none;
    font-size: 12.5px; font-family: inherit; font-weight: 500; color: var(--text-dark);
}
.search-input-small::placeholder { color: var(--text-light); }
.activity-clear-form { display: flex; }

.activity-table { font-size: 12px; overflow-x: auto; }
.activity-table-header {
    display: grid;
    grid-template-columns: 95px minmax(130px,1fr) 80px minmax(130px,1fr) 125px 110px 100px 150px minmax(160px,1.2fr) 130px;
    gap: 10px; padding: 11px 16px;
    background: linear-gradient(90deg, var(--navy) 0%, var(--navy-light) 100%);
    border-radius: var(--radius-sm); font-weight: 700; color: #ffffff;
    margin-bottom: 6px; font-size: 11px; letter-spacing: .04em; text-transform: uppercase;
    min-width: 1320px;
}
.activity-table-body { max-height: 380px; overflow-y: auto; min-width: 1320px; }
.activity-table-body::-webkit-scrollbar { width: 5px; }
.activity-table-body::-webkit-scrollbar-thumb { background: var(--blue-border); border-radius: 4px; }
.activity-empty { text-align: center; color: var(--text-muted); padding: 56px 20px; font-size: 13px; font-weight: 500; }
.activity-row {
    display: grid;
    grid-template-columns: 95px minmax(130px,1fr) 80px minmax(130px,1fr) 125px 110px 100px 150px minmax(160px,1.2fr) 130px;
    gap: 10px; padding: 11px 16px;
    border-bottom: 1px solid var(--blue-border);
    align-items: center; font-size: 12px; transition: background .12s;
}
.activity-row:hover { background: var(--blue-lighter); }
.waktu-date { font-size: 11.5px; color: var(--text-dark); font-weight: 700; margin-bottom: 1px; }
.waktu-time { font-size: 10px; color: var(--text-light); }
.siswa-name { font-size: 12px; color: var(--text-dark); font-weight: 600; line-height: 1.3; }
.wali-name, .col-kelas, .col-wa { font-size: 11.5px; color: var(--text-muted); line-height: 1.4; word-break: break-word; }
.col-error { font-size: 11.5px; color: var(--text-muted); line-height: 1.4; word-break: break-word; }
.col-action { display: flex; align-items: center; gap: 5px; flex-wrap: wrap; }
.activity-action-btn {
    border: 1px solid transparent; border-radius: 999px;
    padding: 4px 10px; font-size: 10.5px; font-weight: 700;
    font-family: inherit; cursor: pointer; line-height: 1.2; transition: opacity .15s;
}
.activity-action-btn:disabled { opacity: .6; cursor: not-allowed; }
.activity-action-btn.retry  { background: var(--blue-lighter); color: var(--accent); border-color: var(--blue-border); }
.activity-action-btn.delete { background: var(--red-bg); color: var(--red); border-color: var(--red-border); }
.status-badge {
    display: inline-flex; align-items: center; gap: 5px;
    padding: 5px 11px; border-radius: 999px; font-size: 11px; font-weight: 700; white-space: nowrap;
}
.status-badge::before { content: ''; width: 6px; height: 6px; border-radius: 50%; background: currentColor; flex-shrink: 0; }
.status-badge.success { background: var(--green-bg); color: var(--green); }
.status-badge.failed  { background: var(--red-bg);   color: var(--red); }
.status-badge.pending { background: var(--yellow-bg); color: var(--yellow); }
.gateway-status-cell { display: flex; flex-direction: column; align-items: flex-start; gap: 4px; min-width: 0; }
.gateway-status-meta { color: var(--text-light); font-size: 9.5px; line-height: 1.35; word-break: break-all; }

/* ─── TIPS ──────────────────────────────────── */
.wa-tips {
    background: var(--white); border: 1px solid var(--blue-border);
    border-radius: var(--radius); padding: 16px 22px;
    display: flex; gap: 16px; align-items: flex-start; box-shadow: var(--shadow);
}
.wa-tips-icon {
    width: 38px; height: 38px; flex-shrink: 0;
    background: linear-gradient(135deg, var(--wa-green), var(--wa-dark));
    border-radius: 10px; display: flex; align-items: center; justify-content: center;
    box-shadow: 0 4px 12px rgba(37,211,102,.3);
    font-size: 17px;
}
.tips-title { font-size: 13px; font-weight: 800; color: var(--navy); margin-bottom: 8px; }
.tips-list  { display: flex; flex-direction: column; gap: 5px; }
.tip-item   {
    font-size: 12.5px; color: var(--text-muted);
    font-weight: 500; padding-left: 14px; position: relative;
}
.tip-item::before { content: '—'; position: absolute; left: 0; color: var(--wa-green); font-weight: 700; }

/* ─── RESPONSIVE ────────────────────────────── */
@media (max-width: 1400px) {
    .wa-top-row             { grid-template-columns: 1fr; }
    .wa-stats-grid          { grid-template-columns: repeat(2,1fr); }
    .wa-device-grid,
    .wa-provider-grid       { grid-template-columns: 1fr; }
    .wa-device-select-grid  { grid-template-columns: 1fr; }
    .activity-table-header,
    .activity-row           { font-size: 11px; }
}
@media (max-width: 768px) {
    .wa-page                { padding: 12px; }
    .wa-stats-grid          { grid-template-columns: 1fr; }
    .activity-header-actions { width: 100%; flex-direction: column-reverse; align-items: stretch; }
    .activity-table-header  { display: none; }
    .activity-row           { grid-template-columns: 1fr; gap: 6px; padding: 12px; background: var(--blue-lighter); border-radius: var(--radius-sm); margin-bottom: 8px; }
    .search-small           { width: 100%; }
    .recipient-db-filters   { grid-template-columns: 1fr; }
}
</style>

<div class="wa-page">
    @php
        $isSuperAdmin = auth()->check()
            && auth()->user()->role === \App\Enums\User\UserRole::IT_SUPPORT->value;
    @endphp

    {{-- ── PAGE HEADER ── --}}
    <div class="wa-page-header">
        <div class="wa-header-icon">
            <svg width="30" height="30" viewBox="0 0 16 16" aria-hidden="true">
                <path fill="#ffffff" d="M13.601 2.326A7.854 7.854 0 0 0 8.05 0C3.68 0 .118 3.562.118 7.932c0 1.4.366 2.767 1.06 3.97L0 16l4.22-1.106a7.9 7.9 0 0 0 3.83.977h.003c4.37 0 7.932-3.562 7.932-7.932a7.87 7.87 0 0 0-2.384-5.613zm-5.55 12.21h-.002a6.57 6.57 0 0 1-3.35-.92l-.24-.142-2.503.656.667-2.44-.156-.25a6.56 6.56 0 0 1-1.01-3.507c0-3.62 2.947-6.567 6.57-6.567 1.753 0 3.4.683 4.64 1.924a6.52 6.52 0 0 1 1.922 4.643c-.002 3.62-2.95 6.566-6.57 6.566zm3.6-4.9c-.197-.1-1.165-.575-1.345-.64-.18-.067-.312-.1-.444.1-.132.198-.51.64-.625.773-.115.132-.23.149-.427.05-.197-.1-.832-.307-1.585-.98-.585-.52-.98-1.162-1.095-1.36-.115-.198-.012-.305.087-.404.09-.09.198-.23.296-.345.099-.116.132-.198.198-.33.066-.132.033-.248-.017-.347-.05-.1-.444-1.07-.608-1.466-.16-.387-.323-.334-.444-.34l-.378-.006a.73.73 0 0 0-.53.248c-.18.198-.69.675-.69 1.646 0 .97.706 1.91.805 2.042.099.132 1.39 2.124 3.37 2.977.47.203.837.324 1.123.415.472.15.902.129 1.242.078.379-.056 1.165-.476 1.33-.936.165-.46.165-.855.116-.936-.05-.083-.18-.132-.378-.23z"/>
            </svg>
        </div>
        <div>
            <div class="wa-header-title">{{ __('app.blast.whatsapp_title') }}</div>
            <div class="wa-header-sub">{{ __('app.blast.whatsapp_subtitle') }}</div>
        </div>
        @if($isSuperAdmin)
            <div class="wa-header-actions">
                <a href="{{ route('admin.blast.whatsapp.manage') }}" class="wa-header-btn">
                    <i class="fas fa-mobile-alt"></i> {{ __('app.blast.manage_devices') }}
                </a>
            </div>
        @endif
    </div>

    {{-- === PROVIDER & DEVICE STATUS === --}}
    <div class="wa-card wa-provider-info" id="waDeviceCard">
        <div class="wa-provider-grid">
            <div>
                <div class="wa-provider-title">{{ __('app.blast.whatsapp_provider') }}</div>
                <div class="wa-provider-row" style="margin-top:6px;">
                    <span class="wa-provider-badge gateway" id="waProviderBadge">{{ __('app.blast.gateway') }}</span>
                    <span class="wa-device-status-badge init" id="waStatusBadge">{{ __('app.blast.loading') }}</span>
                </div>
                <div class="wa-provider-note" id="waProviderNote">{{ __('app.blast.provider_note') }}</div>
                <div class="wa-device-sub" id="waStatusSub">{{ __('app.blast.gateway_waiting') }}</div>
            </div>
            <div class="wa-active-device">
                <div class="label">{{ __('app.blast.active_device') }}</div>
                <div class="value" id="waActiveDevice">-</div>
                <div class="label">{{ __('app.blast.connected_number') }}</div>
                <div class="value" id="waDevicePhone">-</div>
                <div class="label">{{ __('app.blast.connected_since') }}</div>
                <div class="value" id="waDeviceSince">-</div>
            </div>
        </div>
    </div>

    {{-- ── ALERTS ── --}}
    @if(session('success'))
        <div class="wa-alert success">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none"><path d="M22 11.08V12C21.9988 14.1564 21.3005 16.2547 20.0093 17.9818C18.7182 19.7088 16.9033 20.9725 14.8354 21.5839C12.7674 22.1953 10.5573 22.1219 8.53447 21.3746C6.51168 20.6273 4.78465 19.2461 3.61096 17.4371C2.43727 15.628 1.87979 13.4881 2.02168 11.3363C2.16356 9.18455 2.99721 7.13631 4.39828 5.49706C5.79935 3.85781 7.69279 2.71537 9.79619 2.24013C11.8996 1.7649 14.1003 1.98232 16.07 2.85999" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M22 4L12 14.01L9 11.01" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
            {{ session('success') }}
        </div>
    @endif
    @if($errors->any())
        <div class="wa-alert error">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/><path d="M12 8V12M12 16H12.01" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
            {{ $errors->first() }}
        </div>
    @endif

    {{-- ── STATS ── --}}
    <div class="wa-stats-grid">
        <div class="wa-stat-card c-blue">
            <div>
                <div class="stat-label">{{ __('app.blast.total') }}</div>
                <div class="stat-value" id="statTotal">{{ $activityStats['total'] ?? 0 }}</div>
            </div>
            <div class="stat-icon-box c-blue">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none"><path d="M17 21V19C17 17.9391 16.5786 16.9217 15.8284 16.1716C15.0783 15.4214 14.0609 15 13 15H5C3.93913 15 2.92172 15.4214 2.17157 16.1716C1.42143 16.9217 1 17.9391 1 19V21" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M9 11C11.2091 11 13 9.20914 13 7C13 4.79086 11.2091 3 9 3C6.79086 3 5 4.79086 5 7C5 9.20914 6.79086 11 9 11Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M23 21V19C22.9993 18.1137 22.7044 17.2528 22.1614 16.5523C21.6184 15.8519 20.8581 15.3516 20 15.13" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M16 3.13C16.8604 3.35031 17.623 3.85071 18.1676 4.55232C18.7122 5.25392 19.0078 6.11683 19.0078 7.005C19.0078 7.89318 18.7122 8.75608 18.1676 9.45769C17.623 10.1593 16.8604 10.6597 16 10.88" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </div>
        </div>
        <div class="wa-stat-card c-green">
            <div>
                <div class="stat-label">{{ __('app.blast.sent') }}</div>
                <div class="stat-value" id="statSent">{{ $activityStats['sent'] ?? 0 }}</div>
            </div>
            <div class="stat-icon-box c-green">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none"><path d="M22 11.08V12C21.9988 14.1564 21.3005 16.2547 20.0093 17.9818C18.7182 19.7088 16.9033 20.9725 14.8354 21.5839C12.7674 22.1953 10.5573 22.1219 8.53447 21.3746C6.51168 20.6273 4.78465 19.2461 3.61096 17.4371C2.43727 15.628 1.87979 13.4881 2.02168 11.3363C2.16356 9.18455 2.99721 7.13631 4.39828 5.49706C5.79935 3.85781 7.69279 2.71537 9.79619 2.24013C11.8996 1.7649 14.1003 1.98232 16.07 2.85999" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M22 4L12 14.01L9 11.01" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </div>
        </div>
        <div class="wa-stat-card c-red">
            <div>
                <div class="stat-label">{{ __('app.blast.failed') }}</div>
                <div class="stat-value" id="statFailed">{{ $activityStats['failed'] ?? 0 }}</div>
            </div>
            <div class="stat-icon-box c-red">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/><path d="M15 9L9 15M9 9l6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
            </div>
        </div>
        <div class="wa-stat-card c-yellow">
            <div>
                <div class="stat-label">{{ __('app.blast.pending') }}</div>
                <div class="stat-value" id="statPending">{{ $activityStats['pending'] ?? 0 }}</div>
            </div>
            <div class="stat-icon-box c-yellow">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/><path d="M12 6V12L16 14" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </div>
        </div>
    </div>

    {{-- ── MAIN GRID ── --}}
    <div class="wa-main-grid">
        <form method="POST" action="{{ route('admin.blast.whatsapp.send') }}" enctype="multipart/form-data" id="whatsappBlastForm">
            @csrf
            <div class="wa-top-row">

                {{-- ── LEFT: PENERIMA ── --}}
                <div class="wa-card wa-recipient-card">
                    <div class="s-title">
                        <span class="s-icon">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none"><path d="M17 21V19C17 17.9391 16.5786 16.9217 15.8284 16.1716C15.0783 15.4214 14.0609 15 13 15H5C3.93913 15 2.92172 15.4214 2.17157 16.1716C1.42143 16.9217 1 17.9391 1 19V21" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M9 11C11.2091 11 13 9.20914 13 7C13 4.79086 11.2091 3 9 3C6.79086 3 5 4.79086 5 7C5 9.20914 6.79086 11 9 11Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </span>
                        {{ __('app.blast.recipients') }}
                    </div>

                    <div class="phone-row">
                        <input type="text" class="wa-phone-input" placeholder="6281234567890" id="phoneInput">
                        <button type="button" class="wa-add-btn" id="addPhoneBtn" title="{{ __('app.blast.add_number') }}">+</button>
                    </div>

                    <input type="file" id="excelFileInput" accept=".xlsx,.xls,.csv" style="display:none;">
                    <div class="wa-excel-import" id="excelImport">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none"><path d="M14 2H6C5.46957 2 4.96086 2.21071 4.58579 2.58579C4.21071 2.96086 4 3.46957 4 4V20C4 20.5304 4.21071 21.0391 4.58579 21.4142C4.96086 21.7893 5.46957 22 6 22H18C18.5304 22 19.0391 21.7893 19.4142 21.4142C19.7893 21.0391 20 20.5304 20 20V8L14 2Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M14 2V8H20" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        {{ __('app.blast.import_excel') }}
                    </div>

                    <div class="wa-excel-info" id="excelImportInfo" style="display:none;">
                        {!! __('app.blast.excel_format_whatsapp', ['column' => '<strong>'.e(__('app.blast.whatsapp_number')).'</strong>']) !!}
                    </div>

                    <div class="recipient-list" id="recipientList">
                        <div class="recipient-status">{{ __('app.blast.no_recipients') }}</div>
                    </div>

                    <div class="recipient-db-section">
                        <div class="recipient-db-header">
                            <span class="recipient-db-title">
                                <svg width="11" height="11" viewBox="0 0 24 24" fill="none" style="margin-right:4px;vertical-align:middle;color:var(--blue-primary);"><ellipse cx="12" cy="5" rx="9" ry="3" stroke="currentColor" stroke-width="2"/><path d="M3 5C3 5 3 12 3 19C3 20.657 7.03 22 12 22C16.97 22 21 20.657 21 19V5" stroke="currentColor" stroke-width="2"/><path d="M3 12C3 13.657 7.03 15 12 15C16.97 15 21 13.657 21 12" stroke="currentColor" stroke-width="2"/></svg>
                {{ __('app.blast.db_recipient_list') }}
                            </span>
                            <button type="button" class="btn-select-db" id="selectAllRecipientsBtn">{{ __('app.blast.select_all') }}</button>
                        </div>
                        <div class="recipient-db-count">{{ __('app.blast.valid_recipient_count', ['count' => $recipients->count()]) }}</div>
                        <div class="recipient-db-filters">
                            <input type="text" id="recipientDbSearchInput" class="recipient-db-search-input" placeholder="{{ __('app.blast.search_db_recipients') }}">
                            <select id="recipientDbClassFilter" class="recipient-db-class-filter">
                                <option value="">{{ __('app.blast.all_classes') }}</option>
                                @foreach(($recipientClasses ?? collect()) as $kelas)
                                    <option value="{{ strtolower(trim((string) $kelas)) }}">{{ $kelas }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="recipient-db-list">
                            @forelse($recipients as $recipient)
                                <label class="recipient-db-item" for="recipient_{{ $recipient->id }}" data-kelas="{{ strtolower(trim((string) $recipient->kelas)) }}">
                                    <input type="checkbox" class="recipient-db-checkbox" id="recipient_{{ $recipient->id }}" name="recipient_ids[]" value="{{ $recipient->id }}" data-phone="{{ $recipient->wa_wali }}" data-phone-2="{{ $recipient->wa_wali_2 }}" data-student-name="{{ $recipient->nama_siswa }}" data-student-class="{{ $recipient->kelas }}" data-parent-name="{{ $recipient->nama_wali }}">
                                    <div class="recipient-db-info">
                                        <div class="recipient-db-name">{{ $recipient->nama_siswa }} ({{ $recipient->kelas }})</div>
                                        <div class="recipient-db-phone">{{ $recipient->nama_wali }} - {{ trim(implode(' / ', array_filter([$recipient->wa_wali, $recipient->wa_wali_2]))) }}</div>
                                    </div>
                                </label>
                            @empty
                                <div class="recipient-db-empty">{{ __('app.blast.no_valid_whatsapp_recipients') }}</div>
                            @endforelse
                        </div>
                    </div>

                    <textarea name="targets" id="targetsField" style="display:none;" rows="3"></textarea>
                </div>

                {{-- ── RIGHT: PESAN ── --}}
                <div class="wa-card wa-message-card">
                    <div class="s-title">
                        <span class="s-icon">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none"><path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </span>
                        {{ __('app.blast.message_box') }}
                    </div>

                    <div class="form-group">
                        <label class="form-label">{{ __('app.blast.student_name') }}</label>
                        <input type="text" class="form-input" id="studentName" name="student_name" placeholder="{{ __('app.blast.student_name_placeholder') }}">
                    </div>
                    <div class="form-group">
                        <label class="form-label">{{ __('app.blast.class') }}</label>
                        <input type="text" class="form-input" id="studentClass" name="student_class" placeholder="{{ __('app.blast.class_placeholder') }}">
                    </div>
                    <div class="form-group">
                        <label class="form-label">{{ __('app.blast.guardian_name') }}</label>
                        <input type="text" class="form-input" id="parentName" name="parent_name" placeholder="{{ __('app.blast.guardian_name_placeholder') }}">
                    </div>

                    <div class="template-section">
                        <label class="template-label">{{ __('app.blast.template') }}:</label>
                        <select class="form-input template-select" id="templateSelect">
                            <option value="">{{ __('app.blast.select_template') }}</option>
                            <option value="reminder">{{ __('app.blast.school_bill_reminder') }}</option>
                            <option value="payment">{{ __('app.blast.school_payment_info') }}</option>
                            <option value="notification">{{ __('app.blast.arrears_notification') }}</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">{{ __('app.blast.announcement') }}</label>
                        <select name="announcement_id" id="announcementSelect" class="form-input">
                            <option value="">{{ __('app.blast.select_announcement_optional') }}</option>
                            @foreach($announcementOptions as $announcement)
                                <option value="{{ $announcement->id }}" data-message="{{ e($announcement->message) }}">
                                    {{ \Illuminate\Support\Str::limit($announcement->title, 80) }}
                                </option>
                            @endforeach
                        </select>
                        <small style="font-size:11.5px;color:var(--text-muted);margin-top:4px;display:block;">{{ __('app.blast.announcement_autofill_help') }}</small>
                    </div>

                    <div class="form-group">
                        <label class="form-label">{{ __('app.blast.db_message_template') }}</label>
                        <select name="template_id" id="dbTemplateSelect" class="form-input">
                            <option value="">{{ __('app.blast.no_template') }}</option>
                            @foreach($templates as $template)
                                <option value="{{ $template->id }}" data-content="{{ e($template->content) }}">{{ $template->name }}</option>
                            @endforeach
                        </select>
                        <div class="template-actions">
                            <a
                                href="{{ route('admin.blast.templates.create', ['channel' => 'whatsapp', 'return_to' => url()->full()]) }}"
                                class="template-action-link"
                            >
                                + {{ __('app.blast.create_template') }}
                            </a>
                            <a
                                href="{{ route('admin.blast.templates.index', ['channel' => 'whatsapp']) }}"
                                class="template-action-link"
                            >
                                {{ __('app.blast.manage_template') }}
                            </a>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">{{ __('app.blast.template_preview') }}</label>
                        <div id="dbTemplatePreview" class="template-preview-box">{{ __('app.blast.select_template_preview') }}</div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">{{ __('app.blast.custom_message_per_recipient') }}</label>
                        <div class="recipient-message-note">{!! __('app.blast.recipient_message_mode_note', ['manual' => '<b>'.e(__('app.blast.manual_mode')).'</b>', 'template' => '<b>'.e(__('app.blast.template_mode')).'</b>', 'global' => '<b>'.e(__('app.blast.global_mode')).'</b>']) !!}</div>
                        <div id="recipientMessageMatrix" class="recipient-message-matrix">
                            <div class="recipient-db-empty">{{ __('app.blast.recipient_override_empty') }}</div>
                        </div>
                        <input type="hidden" name="message_overrides" id="messageOverridesField">
                    </div>

                    <div class="selected-templates" id="selectedTemplates" style="display:none;"></div>

                    <div class="message-editor">
                        <textarea name="message" class="form-textarea" placeholder="{{ __('app.blast.global_message_placeholder') }}" id="messageTextarea" rows="5"></textarea>
                        <label class="global-default-toggle">
                            <input type="checkbox" name="use_global_default" id="useGlobalDefaultToggle" value="1" checked>
                            {{ __('app.blast.use_global_default') }}
                        </label>
                    </div>

                    <div class="form-group">
                        <label class="form-label">{{ __('app.blast.delivery_device') }}</label>
                        <div class="recipient-message-note" style="margin-bottom:0;">{{ __('app.blast.delivery_device_note') }}</div>
                        <div class="wa-device-select-grid">
                            <div class="wa-device-select-item">
                                <label for="deviceStudentSelect">{{ __('app.blast.student_parent_device') }}</label>
                                <select id="deviceStudentSelect" name="device_student" class="form-input">
                                    <option value="">{{ __('app.blast.default') }}</option>
                                </select>
                            </div>
                            <div class="wa-device-select-item">
                                <label for="deviceEmployeeSelect">{{ __('app.blast.employee_device') }}</label>
                                <select id="deviceEmployeeSelect" name="device_employee" class="form-input">
                                    <option value="">{{ __('app.blast.default') }}</option>
                                </select>
                            </div>
                            <div class="wa-device-select-item">
                                <label for="deviceManualSelect">{{ __('app.blast.manual_device') }}</label>
                                <select id="deviceManualSelect" name="device_manual" class="form-input">
                                    <option value="">{{ __('app.blast.follow_student') }}</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">{{ __('app.blast.advanced_delivery_settings') }}</label>
                        <div class="recipient-message-note" style="margin-bottom:0;">{{ __('app.blast.delivery_settings_disabled') }}</div>
                        <input type="hidden" name="scheduled_at"          id="scheduledAtInput"    value="">
                        <input type="hidden" name="priority"              id="priorityInput"       value="normal">
                        <input type="hidden" name="rate_limit_per_minute" id="rateLimitInput"      value="5000">
                        <input type="hidden" name="batch_size"            id="batchSizeInput"      value="2000">
                        <input type="hidden" name="batch_delay_seconds"   id="batchDelayInput"     value="0">
                        <input type="hidden" name="retry_attempts"        id="retryAttemptsInput"  value="1">
                        <input type="hidden" name="retry_backoff_seconds" id="retryBackoffInput"   value="0">
                    </div>

                    <div class="message-footer">
                        <div class="attachment-buttons">
                            <button type="button" class="attach-btn" id="attachFile">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none"><path d="M21.44 11.05L12.25 20.24C11.1242 21.3658 9.59723 21.9983 8.005 21.9983C6.41277 21.9983 4.88583 21.3658 3.76 20.24C2.63417 19.1142 2.00166 17.5872 2.00166 15.995C2.00166 14.4028 2.63417 12.8758 3.76 11.75L12.33 3.18C13.0806 2.42944 14.0991 2.00667 15.16 2.00667C16.2209 2.00667 17.2394 2.42944 17.99 3.18C18.7406 3.93056 19.1633 4.94908 19.1633 6.01C19.1633 7.07092 18.7406 8.08944 17.99 8.84L9.41 17.41C9.03472 17.7853 8.52548 17.9967 7.995 17.9967C7.46452 17.9967 6.95528 17.7853 6.58 17.41C6.20472 17.0347 5.99333 16.5255 5.99333 15.995C5.99333 15.4645 6.20472 14.9553 6.58 14.58L15.07 6.1" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                {{ __('app.blast.attach_file') }}
                            </button>
                        </div>
                        <div class="char-count" id="charCount">{{ __('app.blast.characters', ['count' => 0]) }}</div>
                    </div>

                    <div class="attachment-wrap" id="attachmentContainer" style="display:none;">
                        <label>
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" style="margin-right:5px;vertical-align:middle;color:var(--blue-primary);"><path d="M21.44 11.05L12.25 20.24C11.1242 21.3658 9.59723 21.9983 8.005 21.9983C6.41277 21.9983 4.88583 21.3658 3.76 20.24C2.63417 19.1142 2.00166 17.5872 2.00166 15.995C2.00166 14.4028 2.63417 12.8758 3.76 11.75L12.33 3.18" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            {{ __('app.blast.attachment_optional') }}
                        </label>
                        <input type="file" name="attachments[]" class="form-input" multiple accept=".pdf,.jpg,.jpeg,.png">
                        <div class="attachment-hint">{{ __('app.blast.attachment_hint') }}</div>
                    </div>

                    <button type="submit" class="wa-send-btn" id="sendButton">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M22 2L11 13" stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/><path d="M22 2L15 22L11 13L2 9L22 2Z" stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        {{ __('app.blast.send_whatsapp_message') }}
                    </button>
                    <div class="wa-gateway-note" id="waGatewayStatusNote" style="display:none;"></div>
                </div>
            </div>
        </form>

        {{-- ── ACTIVITY LOG ── --}}
        <div class="wa-card wa-activity-card">
            <div class="activity-header">
                <div class="s-title" style="margin-bottom:0;">
                    <span class="s-icon">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none"><path d="M14 2H6C5.46957 2 4.96086 2.21071 4.58579 2.58579C4.21071 2.96086 4 3.46957 4 4V20C4 20.5304 4.21071 21.0391 4.58579 21.4142C4.96086 21.7893 5.46957 22 6 22H18C18.5304 22 19.0391 21.7893 19.4142 21.4142C19.7893 21.0391 20 20.5304 20 20V8L14 2Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </span>
                    {{ __('app.blast.activity_log') }}
                </div>
                <div class="activity-header-actions">
                    <form method="POST" action="{{ route('admin.blast.activity.clear') }}" class="activity-clear-form" onsubmit="return confirm(@json(__('app.blast.clear_whatsapp_log_confirm')))">
                        @csrf
                        <input type="hidden" name="channel" value="whatsapp">
                        <button type="submit" class="campaign-btn danger tiny">
                            <svg width="11" height="11" viewBox="0 0 24 24" fill="none" style="margin-right:4px;vertical-align:middle;"><path d="M3 6H5H21M19 6V20C19 20.5304 18.7893 21.0391 18.4142 21.4142C18.0391 21.7893 17.5304 22 17 22H7C6.46957 22 5.96086 21.7893 5.58579 21.4142C5.21071 21.0391 5 20.5304 5 20V6M8 6V4C8 3.46957 8.21071 2.96086 8.58579 2.58579C8.96086 2.21071 9.46957 2 10 2H14C14.5304 2 15.0391 2.21071 15.4142 2.58579C15.7893 2.96086 16 3.46957 16 4V6" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            {{ __('app.blast.clear_log') }}
                        </button>
                    </form>
                    <div class="search-small">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none"><circle cx="11" cy="11" r="8" stroke="var(--text-light)" stroke-width="2"/><path d="M21 21L16.65 16.65" stroke="var(--text-light)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        <input type="text" placeholder="{{ __('app.blast.search_activity') }}" class="search-input-small" id="searchInput">
                    </div>
                </div>
            </div>

            <div class="activity-table">
                <div class="activity-table-header">
                    <div>{{ __('app.blast.time_detail') }}</div>
                    <div>{{ __('app.blast.student_name') }}</div>
                    <div>{{ __('app.blast.class') }}</div>
                    <div>{{ __('app.blast.guardian_name') }}</div>
                    <div>{{ __('app.blast.whatsapp_number') }}</div>
                    <div>{{ __('app.blast.device') }}</div>
                    <div>{{ __('app.blast.status') }}</div>
                    <div>{{ __('app.blast.gateway_status') }}</div>
                    <div>{{ __('app.blast.error') }}</div>
                    <div>{{ __('app.blast.action') }}</div>
                </div>
                <div class="activity-table-body" id="activityLog">
                    <div class="activity-empty">{{ __('app.blast.no_activity') }}</div>
                </div>
            </div>
        </div>
    </div>

    {{-- ── TIPS ── --}}
    <div class="wa-tips">
        <div class="wa-tips-icon">💡</div>
        <div>
            <div class="tips-title">{{ __('app.blast.whatsapp_sending_tips') }}</div>
            <div class="tips-list">
                <div class="tip-item">{{ __('app.blast.tip_country_code') }}</div>
                <div class="tip-item">{{ __('app.blast.tip_variables') }}</div>
                <div class="tip-item">{{ __('app.blast.tip_limit') }}</div>
            </div>
        </div>
    </div>

</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const flashSuccess = @json(session('success'));
        const flashError = @json(session('error') ?? ($errors->any() ? $errors->first() : null));
        const blastText = {
            successTitle: @json(__('app.blast.success_title')),
            failedTitle: @json(__('app.blast.failed_title')),
            gateway: @json(__('app.blast.gateway')),
            defaultLabel: @json(__('app.blast.default')),
            followStudent: @json(__('app.blast.follow_student')),
            connectedQrNotNeeded: @json(__('app.blast.connected_qr_not_needed')),
            qrWaiting: @json(__('app.blast.qr_waiting')),
            gatewayUnreachable: @json(__('app.blast.gateway_unreachable')),
            noCustomFiles: @json(__('app.blast.no_custom_files')),
            deleteFile: @json(__('app.blast.delete_file')),
            selectVisible: @json(__('app.blast.select_visible')),
            unselectVisible: @json(__('app.blast.unselect_visible')),
            dbLabel: @json(__('app.blast.db_label')),
            manualLabel: @json(__('app.blast.manual_label')),
            recipientOverrideEmpty: @json(__('app.blast.recipient_override_empty')),
            template: @json(__('app.blast.template')),
            manualMode: @json(__('app.blast.manual_label')),
            templateMode: @json(__('app.blast.template')),
            globalMode: 'Global',
            removeRecipient: @json(__('app.blast.remove_recipient')),
            templateModeHint: @json(__('app.blast.template_mode_hint')),
            globalModeHint: @json(__('app.blast.global_mode_hint')),
            manualModeHint: @json(__('app.blast.manual_mode_hint')),
            templateModePlaceholder: @json(__('app.blast.template_mode_placeholder')),
            globalModePlaceholder: @json(__('app.blast.global_mode_placeholder')),
            manualModePlaceholder: @json(__('app.blast.manual_mode_placeholder')),
            recipientCustomFile: @json(__('app.blast.recipient_custom_file')),
            invalidPhoneFormat: @json(__('app.blast.invalid_phone_format')),
            phoneAlreadyAdded: @json(__('app.blast.phone_already_added')),
            noRecipients: @json(__('app.blast.no_recipients')),
            unsupportedFileFormat: @json(__('app.blast.unsupported_file_format')),
            processing: @json(__('app.blast.processing')),
            emptyExcelFile: @json(__('app.blast.empty_excel_file')),
            whatsappColumnMissing: @json(__('app.blast.whatsapp_column_missing')),
            importSuccessMessage: @json(__('app.blast.import_success_message')),
            duplicateNumbersSkipped: @json(__('app.blast.duplicate_numbers_skipped')),
            invalidNumbersSkipped: @json(__('app.blast.invalid_numbers_skipped')),
            customMessagesRead: @json(__('app.blast.custom_messages_read')),
            importResult: @json(__('app.blast.import_result')),
            importResultSummary: @json(__('app.blast.import_result_summary')),
            customMessagesFilled: @json(__('app.blast.custom_messages_filled')),
            excelReadError: @json(__('app.blast.excel_read_error')),
            fileReadError: @json(__('app.blast.file_read_error')),
            importExcel: @json(__('app.blast.import_excel')),
            selectTemplatePreview: @json(__('app.blast.select_template_preview')),
            noRecipientSearchResults: @json(__('app.blast.no_recipient_search_results')),
            noActivity: @json(__('app.blast.no_activity')),
            noSearchResults: @json(__('app.blast.no_search_results')),
            done: @json(__('app.blast.done')),
            sent: @json(__('app.blast.sent')),
            failed: @json(__('app.blast.failed')),
            pending: @json(__('app.blast.pending')),
            gatewayQueued: @json(__('app.blast.gateway_queued')),
            gatewayProcessing: @json(__('app.blast.gateway_processing')),
            gatewayCompleted: @json(__('app.blast.gateway_completed')),
            gatewayFailed: @json(__('app.blast.gateway_failed')),
            gatewayCancelled: @json(__('app.blast.gateway_cancelled')),
            gatewayUnknown: @json(__('app.blast.gateway_unknown')),
            gatewayLegacyQueued: @json(__('app.blast.gateway_legacy_queued')),
            gatewayReference: @json(__('app.blast.gateway_reference')),
            gatewayMessageId: @json(__('app.blast.gateway_message_id')),
            gatewaySender: @json(__('app.blast.gateway_sender')),
            sendFailedMessage: @json(__('app.blast.send_failed_message')),
            retry: @json(__('app.blast.retry')),
            delete: @json(__('app.blast.delete')),
            characters: @json(__('app.blast.characters')),
            providerWablasNote: @json(__('app.blast.provider_wablas_note')),
            providerGatewayNote: @json(__('app.blast.provider_gateway_note')),
            wablasActiveNote: @json(__('app.blast.wablas_active_note')),
            gatewayNotConnectedNote: @json(__('app.blast.gateway_not_connected_note')),
            statusConnected: @json(__('app.blast.status_connected')),
            statusQr: @json(__('app.blast.status_qr')),
            statusDisconnected: @json(__('app.blast.status_disconnected')),
            statusInit: @json(__('app.blast.status_init')),
            statusConnectedSub: @json(__('app.blast.status_connected_sub')),
            statusQrSub: @json(__('app.blast.status_qr_sub')),
            statusDisconnectedSub: @json(__('app.blast.status_disconnected_sub')),
            statusInitSub: @json(__('app.blast.status_init_sub')),
            activityLogProcessFailed: @json(__('app.blast.activity_log_process_failed')),
            retryLogConfirm: @json(__('app.blast.retry_log_confirm')),
            deleteLogConfirm: @json(__('app.blast.delete_log_confirm')),
            retrying: @json(__('app.blast.retrying')),
            deleting: @json(__('app.blast.deleting')),
            actionProcessed: @json(__('app.blast.action_processed')),
            templateModeRequiresDbTemplate: @json(__('app.blast.template_mode_requires_db_template')),
            globalMessageRequired: @json(__('app.blast.global_message_required')),
            recipientRequired: @json(__('app.blast.recipient_required')),
            messageRequired: @json(__('app.blast.message_required')),
            sendConfirm: @json(__('app.blast.send_confirm')),
            sending: @json(__('app.blast.sending')),
        };

        function translateBlastTemplate(template, replacements = {}) {
            return String(template || '').replace(/:([A-Za-z0-9_]+)/g, (match, key) => {
                return Object.prototype.hasOwnProperty.call(replacements, key) ? replacements[key] : match;
            });
        }

        function showResultAlert(type, message) {
            if (!message) {
                return;
            }

            if (window.Swal && typeof window.Swal.fire === 'function') {
                window.Swal.fire({
                    icon: type === 'success' ? 'success' : 'error',
                    title: type === 'success' ? blastText.successTitle : blastText.failedTitle,
                    text: message,
                    timer: 2600,
                    showConfirmButton: false,
                });
                return;
            }

            alert(message);
        }

        if (flashSuccess) {
            showResultAlert('success', flashSuccess);
        } else if (flashError) {
            showResultAlert('error', flashError);
        }

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
        const activityDeleteApiUrl = @json(route('admin.blast.activity.delete'));
        const activityRetryApiUrl = @json(route('admin.blast.activity.retry'));
        const activityChannel = 'whatsapp';
        const providerState = @json($providerState ?? null);
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
            || document.querySelector('input[name="_token"]')?.value
            || '';
        const excelImport = document.getElementById('excelImport');
        const excelFileInput = document.getElementById('excelFileInput');
        const excelImportInfo = document.getElementById('excelImportInfo');
        
        const studentName = document.getElementById('studentName');
        const studentClass = document.getElementById('studentClass');
        const parentName = document.getElementById('parentName');
        const templateSelect = document.getElementById('templateSelect');
        const announcementSelect = document.getElementById('announcementSelect');
        const selectedTemplatesContainer = document.getElementById('selectedTemplates');
        const dbTemplateSelect = document.getElementById('dbTemplateSelect');
        const dbTemplatePreview = document.getElementById('dbTemplatePreview');
        const scheduledAtInput = document.getElementById('scheduledAtInput');
        const priorityInput = document.getElementById('priorityInput');
        const rateLimitInput = document.getElementById('rateLimitInput');
        const batchSizeInput = document.getElementById('batchSizeInput');
        const batchDelayInput = document.getElementById('batchDelayInput');
        const retryAttemptsInput = document.getElementById('retryAttemptsInput');
        const retryBackoffInput = document.getElementById('retryBackoffInput');
        const selectAllRecipientsBtn = document.getElementById('selectAllRecipientsBtn');
        const recipientDbSearchInput = document.getElementById('recipientDbSearchInput');
        const recipientDbClassFilter = document.getElementById('recipientDbClassFilter');
        const recipientDbList = document.querySelector('.recipient-db-list');
        const recipientDbItems = Array.from(document.querySelectorAll('.recipient-db-item'));
        const recipientDbCheckboxes = document.querySelectorAll('.recipient-db-checkbox');
        const recipientMessageMatrix = document.getElementById('recipientMessageMatrix');
        const messageOverridesField = document.getElementById('messageOverridesField');
        const statTotal = document.getElementById('statTotal');
        const statSent = document.getElementById('statSent');
        const statFailed = document.getElementById('statFailed');
        const statPending = document.getElementById('statPending');
        const gatewayStatusUrl = @json(route('admin.blast.whatsapp.gateway-status'));
        const gatewayDevicesUrl = @json(route('admin.blast.whatsapp.gateway-devices'));
        const waGatewayStatusNote = document.getElementById('waGatewayStatusNote');
        const waDeviceCard = document.getElementById('waDeviceCard');
        const waProviderBadge = document.getElementById('waProviderBadge');
        const waProviderNote = document.getElementById('waProviderNote');
        const waRefreshStatusBtn = document.getElementById('waRefreshStatusBtn');
        const waStatusBadge = document.getElementById('waStatusBadge');
        const waStatusSub = document.getElementById('waStatusSub');
        const waDevicePhone = document.getElementById('waDevicePhone');
        const waDeviceSince = document.getElementById('waDeviceSince');
        const waActiveDevice = document.getElementById('waActiveDevice');
        const waQrImage = document.getElementById('waQrImage');
        const waQrPlaceholder = document.getElementById('waQrPlaceholder');
        const deviceStudentSelect = document.getElementById('deviceStudentSelect');
        const deviceEmployeeSelect = document.getElementById('deviceEmployeeSelect');
        const deviceManualSelect = document.getElementById('deviceManualSelect');

        let selectedTemplates = [];
        let activities = @json($activityLogs ?? []);
        let isRefreshingActivities = false;
        let recipientNumbers = [];
        let gatewayDevicesCache = [];
        let gatewayActiveDeviceId = null;
        let gatewayActiveStatus = 'init';
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
            if (!phone) return null;
            phone = phone.replace(/\D+/g, '');
            if (!phone) return null;
            if (phone.startsWith('0')) phone = '62' + phone.substring(1);
            else if (phone.startsWith('8')) phone = '62' + phone;
            if (!phone.startsWith('62')) return null;
            if (phone.length < 10 || phone.length > 15) return null;
            return phone;
        }

        function keyToToken(key) {
            const base64 = btoa(unescape(encodeURIComponent(key)));
            return base64.replace(/=+$/g, '').replace(/\+/g, '-').replace(/\//g, '_');
        }

        function formatGatewayTime(value) {
            if (!value) return '-';
            const date = new Date(value);
            if (Number.isNaN(date.getTime())) return value;
            return date.toLocaleString('id-ID', { dateStyle: 'medium', timeStyle: 'short' });
        }

        function normalizeGatewayPhone(user) {
            if (!user || !user.id) return '-';
            const raw = String(user.id);
            const localPart = raw.includes('@') ? raw.split('@')[0] : raw;
            return localPart.includes(':') ? localPart.split(':')[0] : localPart;
        }

        function normalizeGatewayDeviceId(value) {
            return String(value || '').trim().toLowerCase();
        }

        function isGatewayDeviceConnected(device) {
            return String(device?.status || '').toLowerCase() === 'connected';
        }

        function findGatewayDevice(deviceId) {
            const normalized = normalizeGatewayDeviceId(deviceId);
            if (!normalized) return null;
            return gatewayDevicesCache.find(device => normalizeGatewayDeviceId(device.deviceId) === normalized) || null;
        }

        function getSelectedGatewayDeviceIds() {
            return [deviceStudentSelect?.value, deviceEmployeeSelect?.value, deviceManualSelect?.value]
                .map(normalizeGatewayDeviceId)
                .filter(Boolean)
                .filter((value, index, list) => list.indexOf(value) === index);
        }

        function getFirstConnectedGatewayDevice() {
            return gatewayDevicesCache.find(isGatewayDeviceConnected) || null;
        }

        function getPreferredGatewayDevice() {
            const selectedIds = getSelectedGatewayDeviceIds();
            for (const deviceId of selectedIds) {
                const device = findGatewayDevice(deviceId);
                if (isGatewayDeviceConnected(device)) return device;
            }

            const activeDevice = findGatewayDevice(gatewayActiveDeviceId);
            if (isGatewayDeviceConnected(activeDevice)) return activeDevice;

            return getFirstConnectedGatewayDevice();
        }

        let currentProviderMode = 'gateway';

        function updateProviderBadge(provider) {
            if (!waProviderBadge) return;
            const value = String(provider || '').toLowerCase();
            const isWablas = value === 'wablas';
            currentProviderMode = isWablas ? 'wablas' : 'gateway';
            waProviderBadge.classList.toggle('gateway', !isWablas);
            waProviderBadge.classList.toggle('wablas', isWablas);
            waProviderBadge.textContent = isWablas ? 'Wablas' : blastText.gateway;
            if (waProviderNote) {
                waProviderNote.textContent = isWablas
                    ? blastText.providerWablasNote
                    : blastText.providerGatewayNote;
            }
        }

        function populateDeviceSelect(selectEl, devices, placeholderLabel) {
            if (!selectEl) return;
            const current = selectEl.value;
            selectEl.innerHTML = '';
            const placeholder = document.createElement('option');
            placeholder.value = '';
            placeholder.textContent = placeholderLabel;
            selectEl.appendChild(placeholder);
            devices.forEach(device => {
                const option = document.createElement('option');
                option.value = device.deviceId;
                option.dataset.status = String(device.status || '');
                const phone = normalizeGatewayPhone(device.user);
                const deviceName = device.label
                    ? `${device.label} (${device.deviceId})`
                    : device.deviceId;
                option.textContent = phone && phone !== '-'
                    ? `${deviceName} - ${phone}`
                    : deviceName;
                selectEl.appendChild(option);
            });
            if (current) {
                selectEl.value = current;
            }
        }

        async function refreshDeviceSelects() {
            if (!gatewayDevicesUrl) return;
            try {
                const response = await fetch(gatewayDevicesUrl, {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                });
                if (!response.ok) return;
                const payload = await response.json();
                if (payload?.success === false) return;
                const data = payload?.data || {};
                gatewayActiveDeviceId = data.activeDeviceId || gatewayActiveDeviceId;
                gatewayDevicesCache = Array.isArray(data.devices) ? data.devices : [];
                const devices = gatewayDevicesCache;
                populateDeviceSelect(deviceStudentSelect, devices, blastText.defaultLabel || @json(__('app.blast.default')));
                populateDeviceSelect(deviceEmployeeSelect, devices, blastText.defaultLabel || @json(__('app.blast.default')));
                populateDeviceSelect(deviceManualSelect, devices, blastText.followStudent || @json(__('app.blast.follow_student')));
                updateGatewaySendState(gatewayActiveStatus);
                const preferredDevice = getPreferredGatewayDevice();
                if (preferredDevice && !isGatewayDeviceConnected(findGatewayDevice(gatewayActiveDeviceId))) {
                    updateGatewayUi({
                        ...preferredDevice,
                        activeDeviceId: preferredDevice.deviceId,
                    });
                }
            } catch (error) {
                // ignore
            }
        }

        function updateGatewaySendState(status) {
            if (!sendButton) return;
            if (currentProviderMode === 'wablas') {
                sendButton.disabled = false;
                if (waGatewayStatusNote) {
                    waGatewayStatusNote.style.display = 'block';
                    waGatewayStatusNote.textContent = blastText.wablasActiveNote;
                }
                return;
            }
            gatewayActiveStatus = String(status || '').toLowerCase();
            const selectedIds = getSelectedGatewayDeviceIds();
            const selectedConnected = selectedIds.some(deviceId => isGatewayDeviceConnected(findGatewayDevice(deviceId)));
            const activeConnected = gatewayActiveStatus === 'connected'
                || isGatewayDeviceConnected(findGatewayDevice(gatewayActiveDeviceId));
            const fallbackConnected = Boolean(getFirstConnectedGatewayDevice());
            const connected = selectedIds.length > 0
                ? selectedConnected
                : (activeConnected || fallbackConnected);
            sendButton.disabled = !connected;
            if (waGatewayStatusNote) {
                if (connected) {
                    waGatewayStatusNote.style.display = 'none';
                } else {
                    waGatewayStatusNote.style.display = 'block';
                    waGatewayStatusNote.textContent = blastText.gatewayNotConnectedNote;
                }
            }
        }

        async function refreshGatewayStatusForSend() {
            if (!gatewayStatusUrl) return;
            try {
                const preferredDeviceId = getPreferredGatewayDevice()?.deviceId
                    || getSelectedGatewayDeviceIds()[0]
                    || '';
                const statusUrl = preferredDeviceId
                    ? `${gatewayStatusUrl}?device_id=${encodeURIComponent(preferredDeviceId)}`
                    : gatewayStatusUrl;
                const response = await fetch(statusUrl, {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                });
                if (!response.ok) {
                    updateGatewaySendState('disconnected');
                    return;
                }
                const payload = await response.json();
                const data = payload?.data || payload;
                gatewayActiveDeviceId = data?.activeDeviceId || gatewayActiveDeviceId;
                updateGatewaySendState(data?.status || 'disconnected');
            } catch (error) {
                updateGatewaySendState('disconnected');
            }
        }

        function updateGatewayUi(data) {
            if (!waDeviceCard) return;
            const status = String(data?.status || 'disconnected').toLowerCase();
            const labelMap = {
                connected: blastText.statusConnected,
                qr: blastText.statusQr,
                disconnected: blastText.statusDisconnected,
                init: blastText.statusInit
            };
            const subMap = {
                connected: blastText.statusConnectedSub,
                qr: blastText.statusQrSub,
                disconnected: blastText.statusDisconnectedSub,
                init: blastText.statusInitSub
            };

            if (waStatusBadge) {
                waStatusBadge.classList.remove('connected', 'qr', 'disconnected', 'init');
                waStatusBadge.classList.add(labelMap[status] ? status : 'disconnected');
                waStatusBadge.textContent = labelMap[status] || blastText.statusDisconnected;
            }

            if (waStatusSub) {
                waStatusSub.textContent = subMap[status] || 'Status tidak diketahui.';
            }

            if (waDevicePhone) {
                waDevicePhone.textContent = normalizeGatewayPhone(data?.user);
            }

            if (waDeviceSince) {
                waDeviceSince.textContent = formatGatewayTime(data?.connectedAt);
            }

            if (waActiveDevice) {
                waActiveDevice.textContent = data?.deviceId || data?.activeDeviceId || '-';
            }

            const qrData = data?.qrDataUrl || '';
            if (waQrImage && waQrPlaceholder) {
                if (status === 'qr' && qrData) {
                    waQrImage.src = qrData;
                    waQrImage.style.display = 'block';
                    waQrPlaceholder.style.display = 'none';
                } else {
                    waQrImage.style.display = 'none';
                    waQrPlaceholder.style.display = 'block';
                    waQrPlaceholder.textContent = status === 'connected'
                        ? blastText.connectedQrNotNeeded
                        : blastText.qrWaiting;
                }
            }
        }

        async function fetchGatewayStatus() {
            if (!waDeviceCard || !gatewayStatusUrl) return;
            try {
                const preferredDeviceId = getPreferredGatewayDevice()?.deviceId
                    || getSelectedGatewayDeviceIds()[0]
                    || '';
                const statusUrl = preferredDeviceId
                    ? `${gatewayStatusUrl}?device_id=${encodeURIComponent(preferredDeviceId)}`
                    : gatewayStatusUrl;
                const response = await fetch(statusUrl, {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                });
                if (!response.ok) {
                    throw new Error(blastText.gatewayUnreachable);
                }
                const payload = await response.json();
                if (payload?.success === false) {
                    updateGatewayUi({ status: 'disconnected' });
                    if (waStatusSub && payload?.message) waStatusSub.textContent = payload.message;
                    return;
                }
                const data = payload?.data || {};
                gatewayActiveDeviceId = data?.activeDeviceId || gatewayActiveDeviceId;
                updateGatewayUi(data);
            } catch (error) {
                updateGatewayUi({ status: 'disconnected' });
                if (waStatusSub) waStatusSub.textContent = blastText.gatewayUnreachable;
            }
        }

        function ensureAttachmentBuffer(key) {
            if (!attachmentBufferByKey[key]) attachmentBufferByKey[key] = new DataTransfer();
            return attachmentBufferByKey[key];
        }

        function removeAttachmentFileByIndex(key, index) {
            const currentBuffer = ensureAttachmentBuffer(key);
            const nextBuffer = new DataTransfer();
            Array.from(currentBuffer.files).forEach((file, i) => { if (i !== index) nextBuffer.items.add(file); });
            attachmentBufferByKey[key] = nextBuffer;
        }

        function renderAttachmentPreview(item, key) {
            const input = item.querySelector('.message-override-file-input');
            const list = item.querySelector('.message-override-file-list');
            const buffer = ensureAttachmentBuffer(key);
            if (!input || !list) return;
            input.files = buffer.files;
            if (buffer.files.length === 0) { list.innerHTML = `<div class="message-override-file-empty">${blastText.noCustomFiles}</div>`; return; }
            list.innerHTML = Array.from(buffer.files).map((file, index) => `<div class="message-override-file-item"><span>${escapeHtml(file.name)}</span><button type="button" class="message-override-file-remove" data-index="${index}" title="${blastText.deleteFile}">&times;</button></div>`).join('');
        }

        function removeManualRecipientByNumber(phone) {
            const normalized = normalizePhone(phone);
            if (!normalized) return;
            recipientNumbers = recipientNumbers.filter(item => item !== normalized);
            delete overrideState['manual:' + normalized];
            delete attachmentBufferByKey['manual:' + normalized];
            recipientList.querySelectorAll('.recipient-item').forEach(item => {
                if ((item.getAttribute('data-phone') || '') === normalized) item.remove();
            });
        }

        function removeDbRecipientById(recipientId) {
            recipientDbCheckboxes.forEach(cb => { if (cb.value === recipientId) cb.checked = false; });
            delete overrideState['db:' + recipientId];
            delete attachmentBufferByKey['db:' + recipientId];
            syncRecipientProfileFromDbSelection();
        }

        function getPrimaryCheckedDbRecipient(preferredRecipient = null) {
            if (preferredRecipient && preferredRecipient.checked) return preferredRecipient;
            return Array.from(recipientDbCheckboxes).find(cb => cb.checked) || null;
        }

        function getVisibleRecipientDbCheckboxes() {
            return recipientDbItems
                .filter(item => item.style.display !== 'none')
                .map(item => item.querySelector('.recipient-db-checkbox'))
                .filter(checkbox => checkbox);
        }

        function updateSelectAllRecipientsBtnLabel() {
            if (!selectAllRecipientsBtn) return;
            const visibleCheckboxes = getVisibleRecipientDbCheckboxes();
            if (visibleCheckboxes.length === 0) {
                selectAllRecipientsBtn.disabled = true;
                selectAllRecipientsBtn.textContent = blastText.selectVisible;
                return;
            }

            selectAllRecipientsBtn.disabled = false;
            const allVisibleChecked = visibleCheckboxes.every(cb => cb.checked);
            selectAllRecipientsBtn.textContent = allVisibleChecked ? blastText.unselectVisible : blastText.selectVisible;
        }

        function syncRecipientProfileFromDbSelection(preferredRecipient = null) {
            if (!studentName || !studentClass || !parentName) return;
            const sourceRecipient = getPrimaryCheckedDbRecipient(preferredRecipient);
            if (!sourceRecipient) return;
            studentName.value = (sourceRecipient.getAttribute('data-student-name') || '').trim();
            studentClass.value = (sourceRecipient.getAttribute('data-student-class') || '').trim();
            parentName.value = (sourceRecipient.getAttribute('data-parent-name') || '').trim();
        }

        function getSelectedRecipients() {
            const recipients = [];
            recipientDbCheckboxes.forEach(cb => {
                if (!cb.checked) return;
                const key = 'db:' + cb.value;
                const label = cb.closest('.recipient-db-item')?.querySelector('.recipient-db-name')?.textContent?.trim() || cb.value;
                recipients.push({ key, label: blastText.dbLabel + ' - ' + label, kind: 'db', ref: cb.value });
            });
            recipientNumbers.forEach(phone => {
                recipients.push({ key: 'manual:' + phone, label: blastText.manualLabel + ' - ' + phone, kind: 'manual', ref: phone });
            });
            return recipients;
        }

        function getActiveMessageOverrides() {
            const overrides = {};
            getSelectedRecipients().forEach(({ key }) => {
                const state = overrideState[key] || {};
                const mode = (state.mode || 'manual').toLowerCase();
                const message = (state.message || '').trim();
                if (mode === 'template') { overrides[key] = { mode: 'template', message: '' }; return; }
                if (mode === 'global') { overrides[key] = { mode: 'global', message: '' }; return; }
                if (message !== '') overrides[key] = { mode: 'manual', message };
            });
            return overrides;
        }

        function syncMessageOverridesField() {
            if (!messageOverridesField) return {};
            const overrides = getActiveMessageOverrides();
            messageOverridesField.value = JSON.stringify(overrides);
            return overrides;
        }

        function renderRecipientMessageMatrix() {
            if (!recipientMessageMatrix) return;
            const recipients = getSelectedRecipients();
            if (recipients.length === 0) {
                recipientMessageMatrix.innerHTML = `<div class="recipient-db-empty">${blastText.recipientOverrideEmpty}</div>`;
                syncMessageOverridesField();
                return;
            }
            recipientMessageMatrix.innerHTML = recipients.map(({ key, label, kind, ref }) => {
                const state = overrideState[key] || {};
                const mode = (state.mode || 'manual').toLowerCase();
                const manualChecked = mode === 'manual';
                const templateChecked = mode === 'template';
                const globalChecked = mode === 'global';
                const effectiveMode = templateChecked ? 'template' : (globalChecked ? 'global' : 'manual');
                const message = escapeHtml(state.message || '');
                const keyToken = keyToToken(key);
                const radioGroup = 'override_mode_' + key.replace(/[^a-zA-Z0-9_-]/g, '_');
                const modeClass = 'mode-' + effectiveMode;
                const badgeText = effectiveMode === 'template' ? blastText.templateMode : (effectiveMode === 'global' ? blastText.globalMode : blastText.manualMode);
                const hintText = effectiveMode === 'template' ? blastText.templateModeHint : (effectiveMode === 'global' ? blastText.globalModeHint : blastText.manualModeHint);
                const textPlaceholder = effectiveMode === 'template' ? blastText.templateModePlaceholder : (effectiveMode === 'global' ? blastText.globalModePlaceholder : blastText.manualModePlaceholder);
                return `<div class="message-override-item ${modeClass}" data-key="${escapeHtml(key)}" data-kind="${escapeHtml(kind)}" data-ref="${escapeHtml(ref)}"><div class="message-override-head"><div class="message-override-title">${escapeHtml(label)}</div><div class="message-override-actions"><span class="message-override-badge ${modeClass}">${badgeText}</span><button type="button" class="message-override-remove" title="${blastText.removeRecipient}">&times;</button></div></div><div class="message-override-mode"><label><input type="radio" name="${radioGroup}" class="message-override-mode-input" data-mode="manual" ${manualChecked ? 'checked' : ''}> ${blastText.manualMode}</label><label><input type="radio" name="${radioGroup}" class="message-override-mode-input" data-mode="template" ${templateChecked ? 'checked' : ''}> ${blastText.templateMode}</label><label><input type="radio" name="${radioGroup}" class="message-override-mode-input" data-mode="global" ${globalChecked ? 'checked' : ''}> ${blastText.globalMode}</label></div><textarea class="message-override-text" placeholder="${textPlaceholder}" ${(templateChecked || globalChecked) ? 'disabled' : ''}>${message}</textarea><div class="message-override-file-wrap"><div class="message-override-file-label">${blastText.recipientCustomFile}</div><input type="hidden" name="attachment_override_keys[${keyToken}]" value="${escapeHtml(key)}"><input type="file" class="message-override-file-input" name="attachment_overrides[${keyToken}][]" multiple><div class="message-override-file-list"></div></div><div class="message-override-hint">${hintText}</div></div>`;
            }).join('');
            recipientMessageMatrix.querySelectorAll('.message-override-item').forEach(item => {
                const key = item.getAttribute('data-key');
                if (key) renderAttachmentPreview(item, key);
            });
            syncMessageOverridesField();
        }

        function updateDbTemplatePreview() {
            if (!dbTemplateSelect || !dbTemplatePreview) return;
            const selectedOption = dbTemplateSelect.options[dbTemplateSelect.selectedIndex];
            const content = selectedOption ? selectedOption.getAttribute('data-content') : '';
            const templateName = selectedOption && selectedOption.value ? selectedOption.textContent.trim() : '';
            dbTemplatePreview.textContent = content && content.trim().length > 0 ? `${blastText.template}: ${templateName}\n\n${content}` : blastText.selectTemplatePreview;
        }

        function addRecipient(phoneNumber = null, showAlert = true) {
            const source = phoneNumber === null ? phoneInput.value : phoneNumber;
            const phone = normalizePhone(source);
            if (!phone) { if (showAlert) alert(blastText.invalidPhoneFormat); return false; }
            if (recipientNumbers.includes(phone)) { if (showAlert) alert(blastText.phoneAlreadyAdded); return false; }
            const statusElement = recipientList.querySelector('.recipient-status');
            if (statusElement) statusElement.remove();
            recipientNumbers.push(phone);
            const recipientItem = document.createElement('div');
            recipientItem.className = 'recipient-item';
            recipientItem.setAttribute('data-phone', phone);
            recipientItem.innerHTML = `<span class="recipient-number">${escapeHtml(phone)}</span><button type="button" class="remove-recipient" title="${blastText.delete}">&times;</button>`;
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
                    newStatus.textContent = blastText.noRecipients;
                    recipientList.appendChild(newStatus);
                }
            });
            return true;
        }

        if (addPhoneBtn) addPhoneBtn.addEventListener('click', function() { addRecipient(null, true); });
        if (phoneInput) phoneInput.addEventListener('keypress', function(e) { if (e.key === 'Enter') { e.preventDefault(); addRecipient(null, true); } });
        if (excelImport) excelImport.addEventListener('click', function() { excelFileInput.click(); });
        if (excelFileInput) excelFileInput.addEventListener('change', handleExcelImport);

        function handleExcelImport(event) {
            const file = event.target.files[0];
            if (!file) return;
            const validExtensions = ['.xlsx', '.xls', '.csv'];
            const fileExtension = '.' + file.name.split('.').pop().toLowerCase();
            if (!validExtensions.includes(fileExtension)) { alert(blastText.unsupportedFileFormat); excelFileInput.value = ''; return; }
            excelImport.innerHTML = `<svg width="15" height="15" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/></svg><span>${blastText.processing}</span>`;
            const reader = new FileReader();
            reader.onload = function(e) {
                try {
                    const data = new Uint8Array(e.target.result);
                    const workbook = XLSX.read(data, { type: 'array' });
                    const firstSheetName = workbook.SheetNames[0];
                    const worksheet = workbook.Sheets[firstSheetName];
                    const jsonData = XLSX.utils.sheet_to_json(worksheet, { header: 1 });
                    if (jsonData.length === 0) { alert(blastText.emptyExcelFile); resetExcelImport(); return; }
                    const headers = jsonData[0].map(h => h ? h.toString().toLowerCase() : '');
                    const whatsappIndex = headers.findIndex(h => h.includes('whatsapp') || h.includes('wa') || h.includes('nomor') || h.includes('no') || h.includes('phone') || h.includes('telepon'));
                    const messageIndex = headers.findIndex(h => h.includes('message') || h.includes('pesan') || h.includes('msg') || h.includes('text') || h.includes('isi'));
                    if (whatsappIndex === -1) { alert(blastText.whatsappColumnMissing); resetExcelImport(); return; }
                    let importedCount = 0, duplicateCount = 0, invalidCount = 0, messageApplied = 0;
                    for (let i = 1; i < jsonData.length; i++) {
                        const row = jsonData[i];
                        if (!row[whatsappIndex]) continue;
                        const phone = normalizePhone(row[whatsappIndex].toString().trim());
                        if (!phone) { invalidCount++; continue; }
                        if (recipientNumbers.includes(phone)) {
                            duplicateCount++;
                        } else {
                            if (addRecipient(phone, false)) importedCount++;
                        }
                        const messageCell = messageIndex !== -1 ? row[messageIndex] : '';
                        const customMessage = messageCell !== undefined && messageCell !== null
                            ? String(messageCell).trim()
                            : '';
                        if (customMessage !== '') {
                            overrideState['manual:' + phone] = { mode: 'manual', message: customMessage };
                            messageApplied++;
                        }
                    }
                    updateTargetsField();
                    renderRecipientMessageMatrix();
                    excelFileInput.value = '';
                    let resultMessage = translateBlastTemplate(blastText.importSuccessMessage, { count: importedCount });
                    if (duplicateCount > 0) resultMessage += `\n${translateBlastTemplate(blastText.duplicateNumbersSkipped, { count: duplicateCount })}`;
                    if (invalidCount > 0) resultMessage += `\n${translateBlastTemplate(blastText.invalidNumbersSkipped, { count: invalidCount })}`;
                    if (messageApplied > 0) resultMessage += `\n${translateBlastTemplate(blastText.customMessagesRead, { count: messageApplied })}`;
                    alert(resultMessage);
                    excelImportInfo.innerHTML = `<strong>${blastText.importResult}:</strong> ${translateBlastTemplate(blastText.importResultSummary, { count: importedCount })}${messageApplied > 0 ? `, ${translateBlastTemplate(blastText.customMessagesFilled, { count: messageApplied })}` : ''}`;
                    excelImportInfo.style.display = 'block';
                } catch (error) {
                    alert(blastText.excelReadError);
                } finally { resetExcelImport(); }
            };
            reader.onerror = function() { alert(blastText.fileReadError); resetExcelImport(); };
            reader.readAsArrayBuffer(file);
        }

        function updateTargetsField() { targetsField.value = recipientNumbers.join(', '); }

        function resetExcelImport() {
            excelImport.innerHTML = `<svg width="15" height="15" viewBox="0 0 24 24" fill="none"><path d="M14 2H6C5.46957 2 4.96086 2.21071 4.58579 2.58579C4.21071 2.96086 4 3.46957 4 4V20C4 20.5304 4.21071 21.0391 4.58579 21.4142C4.96086 21.7893 5.46957 22 6 22H18C18.5304 22 19.0391 21.7893 19.4142 21.4142C19.7893 21.0391 20 20.5304 20 20V8L14 2Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M14 2V8H20" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>${blastText.importExcel}`;
        }

        const templates = {
            'reminder': { name: @json(__('app.blast.school_bill_reminder')), content: @json(__('app.blast.default_template_reminder_content')) },
            'payment': { name: @json(__('app.blast.school_payment_info')), content: @json(__('app.blast.default_template_payment_content')) },
            'notification': { name: @json(__('app.blast.arrears_notification')), content: @json(__('app.blast.default_template_notification_content')) }
        };

        function renderSelectedTemplates() {
            selectedTemplatesContainer.innerHTML = '';
            if (selectedTemplates.length === 0) { selectedTemplatesContainer.style.display = 'none'; return; }
            selectedTemplatesContainer.style.display = 'flex';
            selectedTemplates.forEach(templateKey => {
                const template = templates[templateKey];
                const tagElement = document.createElement('div');
                tagElement.className = 'template-tag';
                tagElement.innerHTML = `<span>${template.name}</span><button type="button" class="remove-tag" data-template="${templateKey}">&times;</button>`;
                selectedTemplatesContainer.appendChild(tagElement);
                tagElement.querySelector('.remove-tag').addEventListener('click', function() {
                    selectedTemplates = selectedTemplates.filter(t => t !== this.getAttribute('data-template'));
                    renderSelectedTemplates();
                });
            });
        }

        if (announcementSelect) {
            announcementSelect.addEventListener('change', function() {
                const selectedOption = this.options[this.selectedIndex];
                if (!selectedOption || !selectedOption.value) return;
                const message = selectedOption.getAttribute('data-message') || '';
                if (message.trim() === '') return;
                messageTextarea.value = message;
                updateCharCount();
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
                        if (studentName.value) content = content.replace(/{nama siswa}/g, studentName.value);
                        if (studentClass.value) content = content.replace(/{kelas}/g, studentClass.value);
                        if (parentName.value) content = content.replace(/{nama wali}/g, parentName.value);
                        messageTextarea.value = content;
                        updateCharCount();
                    }
                    this.value = '';
                }
            });
        }

        if (selectAllRecipientsBtn && recipientDbCheckboxes.length > 0) {
            selectAllRecipientsBtn.addEventListener('click', function() {
                const visibleCheckboxes = getVisibleRecipientDbCheckboxes();
                if (visibleCheckboxes.length === 0) return;

                const shouldCheck = visibleCheckboxes.some(cb => !cb.checked);
                visibleCheckboxes.forEach(cb => cb.checked = shouldCheck);
                syncRecipientProfileFromDbSelection();
                renderRecipientMessageMatrix();
                updateSelectAllRecipientsBtnLabel();
            });
        }

        recipientDbCheckboxes.forEach(cb => { cb.addEventListener('change', function() { syncRecipientProfileFromDbSelection(this); renderRecipientMessageMatrix(); updateSelectAllRecipientsBtnLabel(); }); });
        if (dbTemplateSelect) dbTemplateSelect.addEventListener('change', updateDbTemplatePreview);

        if (recipientMessageMatrix) {
            recipientMessageMatrix.addEventListener('click', function(event) {
                const fileRemoveBtn = event.target.closest('.message-override-file-remove');
                if (fileRemoveBtn) {
                    const item = fileRemoveBtn.closest('.message-override-item');
                    const key = item ? item.getAttribute('data-key') : null;
                    const index = Number(fileRemoveBtn.getAttribute('data-index'));
                    if (item && key && Number.isInteger(index)) { removeAttachmentFileByIndex(key, index); renderAttachmentPreview(item, key); }
                    return;
                }
                const removeBtn = event.target.closest('.message-override-remove');
                if (!removeBtn) return;
                const item = removeBtn.closest('.message-override-item');
                if (!item) return;
                const key = item.getAttribute('data-key');
                const kind = item.getAttribute('data-kind');
                const ref = item.getAttribute('data-ref');
                if (kind === 'db' && ref) removeDbRecipientById(ref);
                if (kind === 'manual' && ref) {
                    removeManualRecipientByNumber(ref);
                    updateTargetsField();
                    if (recipientList.querySelectorAll('.recipient-item').length === 0) {
                        const newStatus = document.createElement('div');
                        newStatus.className = 'recipient-status';
                        newStatus.textContent = blastText.noRecipients;
                        recipientList.appendChild(newStatus);
                    }
                }
                if (key) { delete overrideState[key]; delete attachmentBufferByKey[key]; }
                renderRecipientMessageMatrix();
            });

            recipientMessageMatrix.addEventListener('change', function(event) {
                const item = event.target.closest('.message-override-item');
                if (!item) return;
                const key = item.getAttribute('data-key');
                if (!key) return;
                if (!overrideState[key]) overrideState[key] = { mode: 'manual', message: '' };
                const fileInput = event.target.closest('.message-override-file-input');
                if (fileInput) {
                    const buffer = ensureAttachmentBuffer(key);
                    Array.from(fileInput.files || []).forEach(file => buffer.items.add(file));
                    renderAttachmentPreview(item, key);
                    return;
                }
                const modeInput = event.target.closest('.message-override-mode-input');
                if (modeInput) {
                    overrideState[key].mode = modeInput.getAttribute('data-mode') || 'manual';
                    const textarea = item.querySelector('.message-override-text');
                    const mode = overrideState[key].mode;
                    const isTemplate = mode === 'template'; const isGlobal = mode === 'global';
                    item.classList.toggle('mode-template', isTemplate);
                    item.classList.toggle('mode-manual', mode === 'manual');
                    item.classList.toggle('mode-global', isGlobal);
                    const badge = item.querySelector('.message-override-badge');
                    if (badge) { badge.classList.toggle('mode-template', isTemplate); badge.classList.toggle('mode-manual', mode === 'manual'); badge.classList.toggle('mode-global', isGlobal); badge.textContent = isTemplate ? blastText.templateMode : (isGlobal ? blastText.globalMode : blastText.manualMode); }
                    const hint = item.querySelector('.message-override-hint');
                    if (hint) hint.textContent = isTemplate ? blastText.templateModeHint : (isGlobal ? blastText.globalModeHint : blastText.manualModeHint);
                    if (textarea) { textarea.disabled = isTemplate || isGlobal; textarea.placeholder = isTemplate ? blastText.templateModePlaceholder : (isGlobal ? blastText.globalModePlaceholder : blastText.manualModePlaceholder); }
                }
                syncMessageOverridesField();
            });

            recipientMessageMatrix.addEventListener('input', function(event) {
                const textarea = event.target.closest('.message-override-text');
                if (!textarea) return;
                const item = textarea.closest('.message-override-item');
                const key = item ? item.getAttribute('data-key') : null;
                if (!key) return;
                if (!overrideState[key]) overrideState[key] = { mode: 'manual', message: '' };
                overrideState[key].message = textarea.value || '';
                syncMessageOverridesField();
            });
        }

        [studentName, studentClass, parentName].forEach(input => {
            input.addEventListener('input', function() {
                if (messageTextarea.value) {
                    let content = messageTextarea.value;
                    if (studentName.value) content = content.replace(/{nama siswa}/g, studentName.value);
                    if (studentClass.value) content = content.replace(/{kelas}/g, studentClass.value);
                    if (parentName.value) content = content.replace(/{nama wali}/g, parentName.value);
                    messageTextarea.value = content;
                    updateCharCount();
                }
            });
        });

        function updateCharCount() { charCount.textContent = translateBlastTemplate(blastText.characters, { count: messageTextarea.value.length }); }
        if (messageTextarea) { messageTextarea.addEventListener('input', updateCharCount); updateCharCount(); }

        function filterRecipientDbList() {
            if (!recipientDbList || recipientDbItems.length === 0) return;
            const searchTerm = (recipientDbSearchInput?.value || '').trim().toLowerCase();
            const classFilterValue = (recipientDbClassFilter?.value || '').trim().toLowerCase();
            let visibleCount = 0;
            recipientDbItems.forEach(item => {
                const itemClass = (item.getAttribute('data-kelas') || '').trim().toLowerCase();
                const isSearchMatch = searchTerm === '' || (item.textContent || '').toLowerCase().includes(searchTerm);
                const isClassMatch = classFilterValue === '' || itemClass === classFilterValue;
                const isMatch = isSearchMatch && isClassMatch;
                item.style.display = isMatch ? '' : 'none';
                if (isMatch) visibleCount += 1;
            });
            let emptySearch = recipientDbList.querySelector('.recipient-db-empty-search');
            if (visibleCount === 0) {
                if (!emptySearch) { emptySearch = document.createElement('div'); emptySearch.className = 'recipient-db-empty recipient-db-empty-search'; emptySearch.textContent = blastText.noRecipientSearchResults; recipientDbList.appendChild(emptySearch); }
            } else if (emptySearch) emptySearch.remove();
            updateSelectAllRecipientsBtnLabel();
        }

        if (recipientDbSearchInput) recipientDbSearchInput.addEventListener('input', filterRecipientDbList);
        if (recipientDbClassFilter) recipientDbClassFilter.addEventListener('change', filterRecipientDbList);

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

        function renderActivities(filteredActivities = activities) {
            activityLog.innerHTML = '';
            if (filteredActivities.length === 0) {
                const el = document.createElement('div'); el.className = 'activity-empty'; el.textContent = activities.length === 0 ? blastText.noActivity : blastText.noSearchResults; activityLog.appendChild(el); return;
            }
            filteredActivities.forEach(activity => {
                const row = document.createElement('div'); row.className = 'activity-row'; row.setAttribute('data-campaign-id', String(activity.campaignId || ''));
                const statusClass = activity.status === 'success' ? 'success' : activity.status === 'failed' ? 'failed' : 'pending';
                const statusText = activity.status === 'success' ? blastText.done : activity.status === 'failed' ? blastText.failed : blastText.pending;
                const providerStatus = String(activity.providerStatus || 'unknown').toLowerCase();
                const providerCompleted = ['completed', 'delivered', 'delivery_ack', 'done', 'played', 'read', 'sent', 'server_ack', 'success'].includes(providerStatus);
                const providerFailed = providerStatus === 'failed';
                const providerCancelled = providerStatus === 'cancelled';
                const providerProcessing = ['active', 'processing'].includes(providerStatus);
                const providerQueued = ['delayed', 'paused', 'pending', 'prioritized', 'queued', 'waiting', 'waiting-children'].includes(providerStatus);
                const providerLegacyQueued = providerStatus === 'legacy_queued';
                const providerStatusClass = providerCompleted ? 'success' : providerFailed ? 'failed' : 'pending';
                const providerStatusText = providerCompleted
                    ? blastText.gatewayCompleted
                    : providerFailed
                        ? blastText.gatewayFailed
                        : providerCancelled
                            ? blastText.gatewayCancelled
                        : providerProcessing
                            ? blastText.gatewayProcessing
                            : providerQueued
                                ? blastText.gatewayQueued
                                : providerLegacyQueued
                                    ? blastText.gatewayLegacyQueued
                                : blastText.gatewayUnknown;
                const logId = Number(activity.logId || 0);
                const canRetry = Boolean(activity.canRetry) && logId > 0;
                const errorText = activity.status === 'failed'
                    ? (activity.errorMessage || activity.responseMessage || blastText.sendFailedMessage)
                    : '-';
                const actionButtons = [];
                if (canRetry) actionButtons.push(`<button type="button" class="activity-action-btn retry" data-action="retry" data-log-id="${logId}">${blastText.retry}</button>`);
                if (logId > 0) actionButtons.push(`<button type="button" class="activity-action-btn delete" data-action="delete" data-log-id="${logId}">${blastText.delete}</button>`);
                const deviceText = activity.deviceLabel || activity.deviceId || '-';
                const providerDetails = [];
                if (activity.providerReference) providerDetails.push(`${blastText.gatewayReference}: ${activity.providerReference}`);
                if (activity.providerMessageId) providerDetails.push(`${blastText.gatewayMessageId}: ${activity.providerMessageId}`);
                if (activity.providerSenderPhone) providerDetails.push(`${blastText.gatewaySender}: ${activity.providerSenderPhone}`);
                const providerMeta = providerDetails.length > 0
                    ? `<div class="gateway-status-meta">${escapeHtml(providerDetails.join(' | '))}</div>`
                    : '';
                row.innerHTML = `<div class="col-waktu"><div class="waktu-date">${escapeHtml(activity.date)}</div><div class="waktu-time">${escapeHtml(activity.time)}</div></div><div class="col-siswa"><div class="siswa-name">${escapeHtml(activity.studentName)}</div></div><div class="col-kelas">${escapeHtml(activity.studentClass)}</div><div class="col-wali"><div class="wali-name">${escapeHtml(activity.parentName)}</div></div><div class="col-wa">${escapeHtml(activity.phone)}</div><div class="col-device">${escapeHtml(deviceText)}</div><div class="col-status"><span class="status-badge ${statusClass}">${statusText}</span></div><div class="gateway-status-cell"><span class="status-badge ${providerStatusClass}">${providerStatusText}</span>${providerMeta}</div><div class="col-error">${escapeHtml(errorText)}</div><div class="col-action">${actionButtons.length > 0 ? actionButtons.join('') : '-'}</div>`;
                activityLog.appendChild(row);
            });
        }

        function renderActivitiesWithCurrentFilter() {
            const searchTerm = (searchInput?.value || '').trim().toLowerCase();
            if (searchTerm === '') { renderActivities(); return; }
            const filtered = activities.filter(activity => String(activity.studentName || '').toLowerCase().includes(searchTerm) || String(activity.parentName || '').toLowerCase().includes(searchTerm) || String(activity.phone || '').toLowerCase().includes(searchTerm) || String(activity.studentClass || '').toLowerCase().includes(searchTerm) || String(activity.deviceLabel || '').toLowerCase().includes(searchTerm) || String(activity.deviceId || '').toLowerCase().includes(searchTerm) || String(activity.campaignId || '').toLowerCase().includes(searchTerm));
            renderActivities(filtered);
        }

        async function submitActivityLogAction(action, logId) {
            const endpoint = action === 'retry' ? activityRetryApiUrl : activityDeleteApiUrl;
            const response = await fetch(endpoint, { method: 'POST', headers: { 'Accept': 'application/json', 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': csrfToken }, body: JSON.stringify({ channel: activityChannel, log_id: Number(logId) }) });
            let payload = null;
            try { payload = await response.json(); } catch (error) { payload = null; }
            if (!response.ok) throw new Error(payload?.message || blastText.activityLogProcessFailed);
            return payload;
        }

        async function refreshActivityLogs() {
            if (isRefreshingActivities) return;
            isRefreshingActivities = true;
            try {
                const response = await fetch(`${activityApiUrl}?channel=${encodeURIComponent(activityChannel)}`, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } });
                if (!response.ok) return;
                const payload = await response.json();
                if (Array.isArray(payload.logs)) activities = payload.logs;
                if (payload && typeof payload === 'object' && payload.stats) {
                    if (statTotal) statTotal.textContent = Number(payload.stats.total ?? 0);
                    if (statSent) statSent.textContent = Number(payload.stats.sent ?? 0);
                    if (statFailed) statFailed.textContent = Number(payload.stats.failed ?? 0);
                    if (statPending) statPending.textContent = Number(payload.stats.pending ?? 0);
                } else { updateStats(); }
                renderActivitiesWithCurrentFilter();
            } catch (error) { } finally { isRefreshingActivities = false; }
        }

        if (searchInput) searchInput.addEventListener('input', function() { renderActivitiesWithCurrentFilter(); });

        if (activityLog) {
            activityLog.addEventListener('click', async function(event) {
                const actionBtn = event.target.closest('.activity-action-btn');
                if (!actionBtn) return;
                const action = String(actionBtn.getAttribute('data-action') || '');
                const logId = Number(actionBtn.getAttribute('data-log-id') || 0);
                if (!['retry', 'delete'].includes(action) || logId <= 0) return;
                if (!window.confirm(action === 'retry' ? blastText.retryLogConfirm : blastText.deleteLogConfirm)) return;
                const originalText = actionBtn.textContent || '';
                actionBtn.disabled = true; actionBtn.textContent = action === 'retry' ? blastText.retrying : blastText.deleting;
                try { const payload = await submitActivityLogAction(action, logId); showResultAlert('success', payload?.message || blastText.actionProcessed); await refreshActivityLogs(); }
                catch (error) { showResultAlert('error', error?.message || blastText.activityLogProcessFailed); }
                finally { actionBtn.disabled = false; actionBtn.textContent = originalText; }
            });
        }

        const whatsappBlastForm = document.getElementById('whatsappBlastForm');
        if (whatsappBlastForm) {
            whatsappBlastForm.addEventListener('submit', function(e) {
                const activeOverrides = syncMessageOverridesField();
                const selectedDbRecipients = Array.from(document.querySelectorAll('.recipient-db-checkbox:checked'));
                const hasDbRecipients = selectedDbRecipients.length > 0;
                const hasManualTargets = recipientNumbers.length > 0;
                const hasDbTemplate = dbTemplateSelect && dbTemplateSelect.value.trim() !== '';
                const hasGlobalMessage = messageTextarea.value.trim() !== '';
                const overrideValues = Object.values(activeOverrides);
                const hasPerRecipientManual = overrideValues.some(o => o.mode === 'manual' && (o.message || '').trim() !== '');
                const hasPerRecipientTemplate = overrideValues.some(o => o.mode === 'template');
                const hasPerRecipientGlobal = overrideValues.some(o => o.mode === 'global');
                const hasPerRecipientContent = hasPerRecipientManual || (hasPerRecipientTemplate && hasDbTemplate) || (hasPerRecipientGlobal && hasGlobalMessage);
                if (hasPerRecipientTemplate && !hasDbTemplate) { e.preventDefault(); alert(blastText.templateModeRequiresDbTemplate); if (dbTemplateSelect) dbTemplateSelect.focus(); return; }
                if (hasPerRecipientGlobal && !hasGlobalMessage) { e.preventDefault(); alert(blastText.globalMessageRequired); messageTextarea.focus(); return; }
                if (!hasDbRecipients && !hasManualTargets) { e.preventDefault(); alert(blastText.recipientRequired); phoneInput.focus(); return; }
                if (!hasDbTemplate && !hasGlobalMessage && !hasPerRecipientContent) { e.preventDefault(); alert(blastText.messageRequired); messageTextarea.focus(); return; }
                if (scheduledAtInput) scheduledAtInput.value = '';
                if (priorityInput) priorityInput.value = 'normal';
                if (rateLimitInput) rateLimitInput.value = '5000';
                if (batchSizeInput) batchSizeInput.value = '2000';
                if (batchDelayInput) batchDelayInput.value = '0';
                if (retryAttemptsInput) retryAttemptsInput.value = '1';
                if (retryBackoffInput) retryBackoffInput.value = '0';
                const dbPhones = [];
                selectedDbRecipients.forEach(cb => {
                    [cb.getAttribute('data-phone') || '', cb.getAttribute('data-phone-2') || '']
                        .map(phone => normalizePhone(phone))
                        .filter(phone => phone !== null)
                        .forEach(phone => dbPhones.push(phone));
                });
                const allTargetPhones = Array.from(new Set(recipientNumbers.concat(dbPhones)));
                const confirmation = confirm(translateBlastTemplate(blastText.sendConfirm, { count: allTargetPhones.length }));
                if (!confirmation) { e.preventDefault(); return false; }
                sendButton.disabled = true;
                sendButton.innerHTML = `<svg width="16" height="16" viewBox="0 0 24 24" fill="none" style="animation:spin 1s linear infinite;"><circle cx="12" cy="12" r="10" stroke="white" stroke-width="2" stroke-dasharray="30" stroke-linecap="round"/></svg> ${blastText.sending}`;
                return true;
            });
        }

        if (attachFile) attachFile.addEventListener('click', function() { attachmentContainer.style.display = 'block'; });

        (function applyCreatedTemplateFromQuery() {
            if (!dbTemplateSelect) return;
            const params = new URLSearchParams(window.location.search);
            const createdTemplateId = (params.get('template_created') || '').trim();
            if (!createdTemplateId) return;

            const hasOption = Array.from(dbTemplateSelect.options).some(option => option.value === createdTemplateId);
            if (!hasOption) return;

            dbTemplateSelect.value = createdTemplateId;
            updateDbTemplatePreview();

            const cleanUrl = new URL(window.location.href);
            cleanUrl.searchParams.delete('template_created');
            window.history.replaceState({}, '', cleanUrl.toString());
        })();

        updateCharCount();
        updateStats();
        renderActivitiesWithCurrentFilter();
        filterRecipientDbList();
        updateDbTemplatePreview();
        syncRecipientProfileFromDbSelection();
        renderRecipientMessageMatrix();
        syncMessageOverridesField();
        refreshActivityLogs();

        if (providerState && providerState.current) {
            updateProviderBadge(providerState.current);
        } else {
            updateProviderBadge('gateway');
        }

        updateGatewaySendState('init');
        refreshGatewayStatusForSend();
        refreshDeviceSelects();
        [deviceStudentSelect, deviceEmployeeSelect, deviceManualSelect].forEach(selectEl => {
            if (!selectEl) return;
            selectEl.addEventListener('change', function() {
                updateGatewaySendState(gatewayActiveStatus);
                refreshGatewayStatusForSend();
                fetchGatewayStatus();
            });
        });
        setInterval(() => {
            if (document.visibilityState !== 'hidden') refreshGatewayStatusForSend();
        }, 5000);
        setInterval(() => {
            if (document.visibilityState !== 'hidden') refreshDeviceSelects();
        }, 12000);

        if (waDeviceCard) {
            fetchGatewayStatus();
            if (waRefreshStatusBtn) {
                waRefreshStatusBtn.addEventListener('click', function() {
                    fetchGatewayStatus();
                });
            }
            setInterval(() => {
                if (document.visibilityState !== 'hidden') fetchGatewayStatus();
            }, 5000);
        }

        setInterval(() => { if (document.visibilityState !== 'hidden') refreshActivityLogs(); }, 5000);
    });
</script>

<style>
@keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
</style>

@endsection

