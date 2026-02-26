@extends('layouts.app')

@section('title', 'WhatsApp Blast')

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

/* ─── CAMPAIGN CONTROL ──────────────────────── */
.wa-campaign-panel { padding: 18px 22px; margin-bottom: 18px; }
.campaign-panel-head { display: flex; gap: 14px; align-items: flex-start; margin-bottom: 16px; }
.campaign-panel-icon {
    width: 42px; height: 42px; flex-shrink: 0;
    background: linear-gradient(135deg, var(--blue-primary), var(--blue-mid));
    border-radius: 11px; display: flex; align-items: center; justify-content: center;
    box-shadow: 0 4px 14px rgba(37,99,235,.3);
}
.campaign-panel-icon i { color: #fff; font-size: 16px; }
.campaign-panel-label  { font-size: 14px; font-weight: 800; color: var(--navy); margin-bottom: 3px; }
.campaign-panel-note   { font-size: 11.5px; color: var(--text-muted); line-height: 1.5; }

.campaign-search-row   { display: flex; gap: 8px; margin-bottom: 12px; align-items: center; }
.campaign-search-results {
    display: flex; flex-direction: column; gap: 6px;
    margin-bottom: 12px; max-height: 180px; overflow: auto;
}
.campaign-search-empty { font-size: 12px; color: var(--text-muted); }
.campaign-search-item {
    border: 1px solid var(--blue-border); border-radius: var(--radius-sm);
    padding: 8px 12px; display: flex; justify-content: space-between;
    gap: 8px; align-items: center; background: var(--blue-lighter);
}
.campaign-search-meta  { font-size: 11px; color: var(--text-mid); line-height: 1.5; }
.campaign-result-actions { display: flex; gap: 6px; flex-wrap: wrap; justify-content: flex-end; }

.campaign-control-actions {
    display: grid; gap: 10px;
    grid-template-columns: repeat(3, minmax(0,1fr));
}
.campaign-control-form { display: flex; gap: 7px; }
.campaign-input-wrap   { position: relative; flex: 1; }
.campaign-control-input {
    width: 100%; border: 1px solid var(--blue-border); border-radius: var(--radius-sm);
    padding: 8px 12px; font-size: 12.5px; font-family: inherit;
    background: var(--blue-lighter); color: var(--text-dark);
    transition: border-color .2s, box-shadow .2s;
}
.campaign-control-input:focus {
    outline: none; border-color: var(--blue-mid);
    box-shadow: 0 0 0 3px rgba(59,130,246,.15);
}
.campaign-target-input { padding-right: 30px; }
.campaign-clear-btn {
    position: absolute; right: 7px; top: 50%; transform: translateY(-50%);
    width: 20px; height: 20px; border: none; border-radius: 50%;
    background: var(--border); color: var(--text-mid); font-size: 13px;
    cursor: pointer; display: none; align-items: center; justify-content: center; padding: 0;
}
.campaign-clear-btn.visible { display: inline-flex; }

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
.recipient-db-search { margin-bottom: 8px; }
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

.activity-table { font-size: 12px; }
.activity-table-header {
    display: grid;
    grid-template-columns: 105px 1fr 100px 1fr 140px 110px 150px;
    gap: 10px; padding: 11px 16px;
    background: linear-gradient(90deg, var(--navy) 0%, var(--navy-light) 100%);
    border-radius: var(--radius-sm); font-weight: 700; color: #ffffff;
    margin-bottom: 6px; font-size: 11px; letter-spacing: .04em; text-transform: uppercase;
}
.activity-table-body { max-height: 320px; overflow-y: auto; }
.activity-table-body::-webkit-scrollbar { width: 5px; }
.activity-table-body::-webkit-scrollbar-thumb { background: var(--blue-border); border-radius: 4px; }
.activity-empty { text-align: center; color: var(--text-muted); padding: 56px 20px; font-size: 13px; font-weight: 500; }
.activity-row {
    display: grid;
    grid-template-columns: 105px 1fr 100px 1fr 140px 110px 150px;
    gap: 10px; padding: 11px 16px;
    border-bottom: 1px solid var(--blue-border);
    align-items: center; font-size: 12px; transition: background .12s;
}
.activity-row:hover { background: var(--blue-lighter); }
.waktu-date { font-size: 11.5px; color: var(--text-dark); font-weight: 700; margin-bottom: 1px; }
.waktu-time { font-size: 10px; color: var(--text-light); }
.siswa-name { font-size: 12px; color: var(--text-dark); font-weight: 600; line-height: 1.3; }
.wali-name, .col-kelas, .col-wa { font-size: 11.5px; color: var(--text-muted); line-height: 1.4; word-break: break-word; }
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
    .campaign-control-actions { grid-template-columns: 1fr; }
    .activity-table-header,
    .activity-row           { grid-template-columns: 90px 1fr 90px 1fr 120px 90px 130px; font-size: 11px; }
}
@media (max-width: 768px) {
    .wa-page                { padding: 12px; }
    .wa-stats-grid          { grid-template-columns: 1fr; }
    .campaign-search-row,
    .campaign-control-form  { flex-direction: column; }
    .activity-header-actions { width: 100%; flex-direction: column-reverse; align-items: stretch; }
    .activity-table-header  { display: none; }
    .activity-row           { grid-template-columns: 1fr; gap: 6px; padding: 12px; background: var(--blue-lighter); border-radius: var(--radius-sm); margin-bottom: 8px; }
    .search-small           { width: 100%; }
}
</style>

<div class="wa-page">

    {{-- ── PAGE HEADER ── --}}
    <div class="wa-page-header">
        <div class="wa-header-icon">
            <svg width="30" height="30" viewBox="0 0 16 16" aria-hidden="true">
                <path fill="#ffffff" d="M13.601 2.326A7.854 7.854 0 0 0 8.05 0C3.68 0 .118 3.562.118 7.932c0 1.4.366 2.767 1.06 3.97L0 16l4.22-1.106a7.9 7.9 0 0 0 3.83.977h.003c4.37 0 7.932-3.562 7.932-7.932a7.87 7.87 0 0 0-2.384-5.613zm-5.55 12.21h-.002a6.57 6.57 0 0 1-3.35-.92l-.24-.142-2.503.656.667-2.44-.156-.25a6.56 6.56 0 0 1-1.01-3.507c0-3.62 2.947-6.567 6.57-6.567 1.753 0 3.4.683 4.64 1.924a6.52 6.52 0 0 1 1.922 4.643c-.002 3.62-2.95 6.566-6.57 6.566zm3.6-4.9c-.197-.1-1.165-.575-1.345-.64-.18-.067-.312-.1-.444.1-.132.198-.51.64-.625.773-.115.132-.23.149-.427.05-.197-.1-.832-.307-1.585-.98-.585-.52-.98-1.162-1.095-1.36-.115-.198-.012-.305.087-.404.09-.09.198-.23.296-.345.099-.116.132-.198.198-.33.066-.132.033-.248-.017-.347-.05-.1-.444-1.07-.608-1.466-.16-.387-.323-.334-.444-.34l-.378-.006a.73.73 0 0 0-.53.248c-.18.198-.69.675-.69 1.646 0 .97.706 1.91.805 2.042.099.132 1.39 2.124 3.37 2.977.47.203.837.324 1.123.415.472.15.902.129 1.242.078.379-.056 1.165-.476 1.33-.936.165-.46.165-.855.116-.936-.05-.083-.18-.132-.378-.23z"/>
            </svg>
        </div>
        <div>
            <div class="wa-header-title">WhatsApp Blast</div>
            <div class="wa-header-sub">Kirim pesan massal ke WhatsApp secara efisien</div>
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

    {{-- ── CAMPAIGN CONTROL ── --}}
    <div class="wa-card wa-campaign-panel">
        <div class="campaign-panel-head">
            <div class="campaign-panel-icon">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M12 20h9M16.5 3.5a2.121 2.121 0 013 3L7 19l-4 1 1-4L16.5 3.5z" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </div>
            <div>
                <div class="campaign-panel-label">Campaign Control</div>
                <div class="campaign-panel-note">Masukkan Campaign ID untuk pause, resume, atau stop. UUID untuk Pause, Resume, dan Soft Stop bisa berbeda.</div>
            </div>
        </div>

        <div class="campaign-search-row">
            <input type="text" id="campaignSearchInput" class="campaign-control-input" placeholder="Cari Campaign UUID..." value="">
            <button type="button" id="campaignSearchBtn" class="campaign-btn info">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" style="margin-right:5px;vertical-align:middle;"><circle cx="11" cy="11" r="8" stroke="white" stroke-width="2"/><path d="M21 21L16.65 16.65" stroke="white" stroke-width="2" stroke-linecap="round"/></svg>Search UUID
            </button>
        </div>
        <div id="campaignSearchResults" class="campaign-search-results"></div>

        <div class="campaign-control-actions">
            <form method="POST" action="{{ route('admin.blast.campaign.pause') }}" class="campaign-control-form" data-action-type="pause">
                @csrf
                <div class="campaign-input-wrap">
                    <input type="text" id="pauseCampaignInput" name="campaign_id" class="campaign-control-input campaign-target-input" data-target-action="pause" placeholder="UUID untuk Pause" required>
                    <button type="button" class="campaign-clear-btn" data-clear-target="pause" aria-label="Clear">&times;</button>
                </div>
                <button type="submit" class="campaign-btn warning">Pause</button>
            </form>
            <form method="POST" action="{{ route('admin.blast.campaign.resume') }}" class="campaign-control-form" data-action-type="resume">
                @csrf
                <div class="campaign-input-wrap">
                    <input type="text" id="resumeCampaignInput" name="campaign_id" class="campaign-control-input campaign-target-input" data-target-action="resume" placeholder="UUID untuk Resume" required>
                    <button type="button" class="campaign-clear-btn" data-clear-target="resume" aria-label="Clear">&times;</button>
                </div>
                <button type="submit" class="campaign-btn success">Resume</button>
            </form>
            <form method="POST" action="{{ route('admin.blast.campaign.stop') }}" class="campaign-control-form" data-action-type="stop">
                @csrf
                <div class="campaign-input-wrap">
                    <input type="text" id="stopCampaignInput" name="campaign_id" class="campaign-control-input campaign-target-input" data-target-action="stop" placeholder="UUID untuk Soft Stop" required>
                    <button type="button" class="campaign-clear-btn" data-clear-target="stop" aria-label="Clear">&times;</button>
                </div>
                <button type="submit" class="campaign-btn danger">Soft Stop</button>
            </form>
        </div>
    </div>

    {{-- ── STATS ── --}}
    <div class="wa-stats-grid">
        <div class="wa-stat-card c-blue">
            <div>
                <div class="stat-label">Total</div>
                <div class="stat-value" id="statTotal">{{ $activityStats['total'] ?? 0 }}</div>
            </div>
            <div class="stat-icon-box c-blue">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none"><path d="M17 21V19C17 17.9391 16.5786 16.9217 15.8284 16.1716C15.0783 15.4214 14.0609 15 13 15H5C3.93913 15 2.92172 15.4214 2.17157 16.1716C1.42143 16.9217 1 17.9391 1 19V21" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M9 11C11.2091 11 13 9.20914 13 7C13 4.79086 11.2091 3 9 3C6.79086 3 5 4.79086 5 7C5 9.20914 6.79086 11 9 11Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M23 21V19C22.9993 18.1137 22.7044 17.2528 22.1614 16.5523C21.6184 15.8519 20.8581 15.3516 20 15.13" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M16 3.13C16.8604 3.35031 17.623 3.85071 18.1676 4.55232C18.7122 5.25392 19.0078 6.11683 19.0078 7.005C19.0078 7.89318 18.7122 8.75608 18.1676 9.45769C17.623 10.1593 16.8604 10.6597 16 10.88" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </div>
        </div>
        <div class="wa-stat-card c-green">
            <div>
                <div class="stat-label">Terkirim</div>
                <div class="stat-value" id="statSent">{{ $activityStats['sent'] ?? 0 }}</div>
            </div>
            <div class="stat-icon-box c-green">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none"><path d="M22 11.08V12C21.9988 14.1564 21.3005 16.2547 20.0093 17.9818C18.7182 19.7088 16.9033 20.9725 14.8354 21.5839C12.7674 22.1953 10.5573 22.1219 8.53447 21.3746C6.51168 20.6273 4.78465 19.2461 3.61096 17.4371C2.43727 15.628 1.87979 13.4881 2.02168 11.3363C2.16356 9.18455 2.99721 7.13631 4.39828 5.49706C5.79935 3.85781 7.69279 2.71537 9.79619 2.24013C11.8996 1.7649 14.1003 1.98232 16.07 2.85999" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M22 4L12 14.01L9 11.01" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </div>
        </div>
        <div class="wa-stat-card c-red">
            <div>
                <div class="stat-label">Gagal</div>
                <div class="stat-value" id="statFailed">{{ $activityStats['failed'] ?? 0 }}</div>
            </div>
            <div class="stat-icon-box c-red">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/><path d="M15 9L9 15M9 9l6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
            </div>
        </div>
        <div class="wa-stat-card c-yellow">
            <div>
                <div class="stat-label">Pending</div>
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
                        Penerima
                    </div>

                    <div class="phone-row">
                        <input type="text" class="wa-phone-input" placeholder="Contoh: 6281234567890" id="phoneInput">
                        <button type="button" class="wa-add-btn" id="addPhoneBtn" title="Tambah Nomor">+</button>
                    </div>

                    <input type="file" id="excelFileInput" accept=".xlsx,.xls,.csv" style="display:none;">
                    <div class="wa-excel-import" id="excelImport">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none"><path d="M14 2H6C5.46957 2 4.96086 2.21071 4.58579 2.58579C4.21071 2.96086 4 3.46957 4 4V20C4 20.5304 4.21071 21.0391 4.58579 21.4142C4.96086 21.7893 5.46957 22 6 22H18C18.5304 22 19.0391 21.7893 19.4142 21.4142C19.7893 21.0391 20 20.5304 20 20V8L14 2Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M14 2V8H20" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        Impor Excel
                    </div>

                    <div class="wa-excel-info" id="excelImportInfo" style="display:none;">
                        Format Excel harus memiliki kolom: <strong>Nomor WhatsApp</strong> (opsional: Nama, Kelas)
                    </div>

                    <div class="recipient-list" id="recipientList">
                        <div class="recipient-status">Belum ada penerima</div>
                    </div>

                    <div class="recipient-db-section">
                        <div class="recipient-db-header">
                            <span class="recipient-db-title">
                                <svg width="11" height="11" viewBox="0 0 24 24" fill="none" style="margin-right:4px;vertical-align:middle;color:var(--blue-primary);"><ellipse cx="12" cy="5" rx="9" ry="3" stroke="currentColor" stroke-width="2"/><path d="M3 5C3 5 3 12 3 19C3 20.657 7.03 22 12 22C16.97 22 21 20.657 21 19V5" stroke="currentColor" stroke-width="2"/><path d="M3 12C3 13.657 7.03 15 12 15C16.97 15 21 13.657 21 12" stroke="currentColor" stroke-width="2"/></svg>
                                Recipient List DB
                            </span>
                            <button type="button" class="btn-select-db" id="selectAllRecipientsBtn">Select All</button>
                        </div>
                        <div class="recipient-db-count">Total valid recipient: {{ $recipients->count() }}</div>
                        <div class="recipient-db-search">
                            <input type="text" id="recipientDbSearchInput" class="recipient-db-search-input" placeholder="Cari recipient DB...">
                        </div>
                        <div class="recipient-db-list">
                            @forelse($recipients as $recipient)
                                <label class="recipient-db-item" for="recipient_{{ $recipient->id }}">
                                    <input type="checkbox" class="recipient-db-checkbox" id="recipient_{{ $recipient->id }}" name="recipient_ids[]" value="{{ $recipient->id }}" data-phone="{{ $recipient->wa_wali }}" data-phone-2="{{ $recipient->wa_wali_2 }}" data-student-name="{{ $recipient->nama_siswa }}" data-student-class="{{ $recipient->kelas }}" data-parent-name="{{ $recipient->nama_wali }}">
                                    <div class="recipient-db-info">
                                        <div class="recipient-db-name">{{ $recipient->nama_siswa }} ({{ $recipient->kelas }})</div>
                                        <div class="recipient-db-phone">{{ $recipient->nama_wali }} - {{ trim(implode(' / ', array_filter([$recipient->wa_wali, $recipient->wa_wali_2]))) }}</div>
                                    </div>
                                </label>
                            @empty
                                <div class="recipient-db-empty">Tidak ada recipient WhatsApp valid.</div>
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
                        Kotak Pesan
                    </div>

                    <div class="form-group">
                        <label class="form-label">Nama Siswa</label>
                        <input type="text" class="form-input" id="studentName" name="student_name" placeholder="Masukkan nama siswa">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Kelas</label>
                        <input type="text" class="form-input" id="studentClass" name="student_class" placeholder="Contoh: 5A">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Nama Wali</label>
                        <input type="text" class="form-input" id="parentName" name="parent_name" placeholder="Masukkan nama wali">
                    </div>

                    <div class="template-section">
                        <label class="template-label">Template:</label>
                        <select class="form-input template-select" id="templateSelect">
                            <option value="">Pilih Template</option>
                            <option value="reminder">Reminder Tagihan Sekolah</option>
                            <option value="payment">Informasi Pembayaran Sekolah</option>
                            <option value="notification">Pemberitahuan Tunggakan</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Announcement</label>
                        <select name="announcement_id" id="announcementSelect" class="form-input">
                            <option value="">Pilih Announcement (opsional)</option>
                            @foreach($announcementOptions as $announcement)
                                <option value="{{ $announcement->id }}" data-message="{{ e($announcement->message) }}">
                                    {{ \Illuminate\Support\Str::limit($announcement->title, 80) }}
                                </option>
                            @endforeach
                        </select>
                        <small style="font-size:11.5px;color:var(--text-muted);margin-top:4px;display:block;">Pilih announcement untuk mengisi isi pesan otomatis.</small>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Template Blast DB</label>
                        <select name="template_id" id="dbTemplateSelect" class="form-input">
                            <option value="">Tanpa template</option>
                            @foreach($templates as $template)
                                <option value="{{ $template->id }}" data-content="{{ e($template->content) }}">{{ $template->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Template Preview</label>
                        <div id="dbTemplatePreview" class="template-preview-box">Pilih template untuk melihat preview.</div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Pesan Khusus Per Penerima</label>
                        <div class="recipient-message-note">Atur per penerima: pilih mode <b>manual</b>, <b>template</b>, atau <b>global</b>.</div>
                        <div id="recipientMessageMatrix" class="recipient-message-matrix">
                            <div class="recipient-db-empty">Pilih recipient DB atau tambah nomor WhatsApp manual untuk mengatur pesan khusus.</div>
                        </div>
                        <input type="hidden" name="message_overrides" id="messageOverridesField">
                    </div>

                    <div class="selected-templates" id="selectedTemplates" style="display:none;"></div>

                    <div class="message-editor">
                        <textarea name="message" class="form-textarea" placeholder="Ketik pesan Anda di sini..." id="messageTextarea" rows="5"></textarea>
                        <label class="global-default-toggle">
                            <input type="checkbox" name="use_global_default" id="useGlobalDefaultToggle" value="1" checked>
                            Gunakan isi pesan global sebagai default penerima.
                        </label>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Pengaturan Pengiriman Lanjutan</label>
                        <div class="recipient-message-note" style="margin-bottom:0;">Pengiriman WhatsApp diproses langsung. Fitur jadwal & delay dinonaktifkan.</div>
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
                                Lampirkan File
                            </button>
                        </div>
                        <div class="char-count" id="charCount">0 karakter</div>
                    </div>

                    <div class="attachment-wrap" id="attachmentContainer" style="display:none;">
                        <label>
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" style="margin-right:5px;vertical-align:middle;color:var(--blue-primary);"><path d="M21.44 11.05L12.25 20.24C11.1242 21.3658 9.59723 21.9983 8.005 21.9983C6.41277 21.9983 4.88583 21.3658 3.76 20.24C2.63417 19.1142 2.00166 17.5872 2.00166 15.995C2.00166 14.4028 2.63417 12.8758 3.76 11.75L12.33 3.18" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            Lampiran (Opsional)
                        </label>
                        <input type="file" name="attachments[]" class="form-input" multiple accept=".pdf,.jpg,.jpeg,.png">
                        <div class="attachment-hint">Maksimal 5MB per file. PDF / Image.</div>
                    </div>

                    <button type="submit" class="wa-send-btn" id="sendButton">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M22 2L11 13" stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/><path d="M22 2L15 22L11 13L2 9L22 2Z" stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        Kirim Pesan WhatsApp
                    </button>
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
                    Activity Log
                </div>
                <div class="activity-header-actions">
                    <form method="POST" action="{{ route('admin.blast.activity.clear') }}" class="activity-clear-form" onsubmit="return confirm('Yakin ingin menghapus semua activity log WhatsApp?')">
                        @csrf
                        <input type="hidden" name="channel" value="whatsapp">
                        <button type="submit" class="campaign-btn danger tiny">
                            <svg width="11" height="11" viewBox="0 0 24 24" fill="none" style="margin-right:4px;vertical-align:middle;"><path d="M3 6H5H21M19 6V20C19 20.5304 18.7893 21.0391 18.4142 21.4142C18.0391 21.7893 17.5304 22 17 22H7C6.46957 22 5.96086 21.7893 5.58579 21.4142C5.21071 21.0391 5 20.5304 5 20V6M8 6V4C8 3.46957 8.21071 2.96086 8.58579 2.58579C8.96086 2.21071 9.46957 2 10 2H14C14.5304 2 15.0391 2.21071 15.4142 2.58579C15.7893 2.96086 16 3.46957 16 4V6" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            Clear Log
                        </button>
                    </form>
                    <div class="search-small">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none"><circle cx="11" cy="11" r="8" stroke="var(--text-light)" stroke-width="2"/><path d="M21 21L16.65 16.65" stroke="var(--text-light)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        <input type="text" placeholder="Cari aktivitas..." class="search-input-small" id="searchInput">
                    </div>
                </div>
            </div>

            <div class="activity-table">
                <div class="activity-table-header">
                    <div>Detail Waktu</div>
                    <div>Nama Siswa</div>
                    <div>Kelas</div>
                    <div>Nama Wali</div>
                    <div>Nomor WhatsApp</div>
                    <div>Status</div>
                    <div>Aksi</div>
                </div>
                <div class="activity-table-body" id="activityLog">
                    <div class="activity-empty">Belum ada aktivitas</div>
                </div>
            </div>
        </div>
    </div>

    {{-- ── TIPS ── --}}
    <div class="wa-tips">
        <div class="wa-tips-icon">💡</div>
        <div>
            <div class="tips-title">Tips Pengiriman WhatsApp</div>
            <div class="tips-list">
                <div class="tip-item">Sertakan kode negara pada nomor telepon (contoh: 6281234567890).</div>
                <div class="tip-item">Personalisasi pesan menggunakan variabel untuk engagement lebih baik.</div>
                <div class="tip-item">Hindari mengirim terlalu banyak pesan sekaligus untuk mencegah pemblokiran.</div>
            </div>
        </div>
    </div>

</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const flashSuccess = @json(session('success'));
        const flashError = @json(session('error') ?? ($errors->any() ? $errors->first() : null));

        function showResultAlert(type, message) {
            if (!message) {
                return;
            }

            if (window.Swal && typeof window.Swal.fire === 'function') {
                window.Swal.fire({
                    icon: type === 'success' ? 'success' : 'error',
                    title: type === 'success' ? 'Berhasil' : 'Gagal',
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
        const campaignSearchInput = document.getElementById('campaignSearchInput');
        const campaignSearchBtn = document.getElementById('campaignSearchBtn');
        const campaignSearchResults = document.getElementById('campaignSearchResults');
        const campaignTargetInputs = Array.from(document.querySelectorAll('.campaign-target-input'));
        const campaignClearButtons = Array.from(document.querySelectorAll('.campaign-clear-btn'));
        const activityApiUrl = @json(route('admin.blast.activity'));
        const activityDeleteApiUrl = @json(route('admin.blast.activity.delete'));
        const activityRetryApiUrl = @json(route('admin.blast.activity.retry'));
        const campaignApiUrl = @json(route('admin.blast.campaigns'));
        const activityChannel = 'whatsapp';
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
        const recipientDbList = document.querySelector('.recipient-db-list');
        const recipientDbItems = Array.from(document.querySelectorAll('.recipient-db-item'));
        const recipientDbCheckboxes = document.querySelectorAll('.recipient-db-checkbox');
        const recipientMessageMatrix = document.getElementById('recipientMessageMatrix');
        const messageOverridesField = document.getElementById('messageOverridesField');
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
            if (buffer.files.length === 0) { list.innerHTML = '<div class="message-override-file-empty">Tidak ada file khusus</div>'; return; }
            list.innerHTML = Array.from(buffer.files).map((file, index) => `<div class="message-override-file-item"><span>${escapeHtml(file.name)}</span><button type="button" class="message-override-file-remove" data-index="${index}" title="Hapus file">&times;</button></div>`).join('');
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
                recipients.push({ key, label: 'DB - ' + label, kind: 'db', ref: cb.value });
            });
            recipientNumbers.forEach(phone => {
                recipients.push({ key: 'manual:' + phone, label: 'Manual - ' + phone, kind: 'manual', ref: phone });
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
                recipientMessageMatrix.innerHTML = `<div class="recipient-db-empty">Pilih recipient DB atau tambah nomor WhatsApp manual untuk mengatur pesan khusus.</div>`;
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
                const badgeText = effectiveMode === 'template' ? 'Template' : (effectiveMode === 'global' ? 'Global' : 'Manual');
                const hintText = effectiveMode === 'template' ? 'Menggunakan template blast DB untuk penerima ini.' : (effectiveMode === 'global' ? 'Menggunakan isi pesan WA global untuk penerima ini.' : 'Gunakan isi manual khusus untuk penerima ini.');
                const textPlaceholder = effectiveMode === 'template' ? 'Mode template aktif untuk penerima ini.' : (effectiveMode === 'global' ? 'Mode global aktif untuk penerima ini.' : 'Isi pesan khusus untuk penerima ini...');
                return `<div class="message-override-item ${modeClass}" data-key="${escapeHtml(key)}" data-kind="${escapeHtml(kind)}" data-ref="${escapeHtml(ref)}"><div class="message-override-head"><div class="message-override-title">${escapeHtml(label)}</div><div class="message-override-actions"><span class="message-override-badge ${modeClass}">${badgeText}</span><button type="button" class="message-override-remove" title="Hapus penerima ini">&times;</button></div></div><div class="message-override-mode"><label><input type="radio" name="${radioGroup}" class="message-override-mode-input" data-mode="manual" ${manualChecked ? 'checked' : ''}> Manual</label><label><input type="radio" name="${radioGroup}" class="message-override-mode-input" data-mode="template" ${templateChecked ? 'checked' : ''}> Template</label><label><input type="radio" name="${radioGroup}" class="message-override-mode-input" data-mode="global" ${globalChecked ? 'checked' : ''}> Global</label></div><textarea class="message-override-text" placeholder="${textPlaceholder}" ${(templateChecked || globalChecked) ? 'disabled' : ''}>${message}</textarea><div class="message-override-file-wrap"><div class="message-override-file-label">File Khusus Penerima (opsional)</div><input type="hidden" name="attachment_override_keys[${keyToken}]" value="${escapeHtml(key)}"><input type="file" class="message-override-file-input" name="attachment_overrides[${keyToken}][]" multiple><div class="message-override-file-list"></div></div><div class="message-override-hint">${hintText}</div></div>`;
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
            dbTemplatePreview.textContent = content && content.trim().length > 0 ? `Template: ${templateName}\n\n${content}` : 'Pilih template untuk melihat preview.';
        }

        function addRecipient(phoneNumber = null, showAlert = true) {
            const source = phoneNumber === null ? phoneInput.value : phoneNumber;
            const phone = normalizePhone(source);
            if (!phone) { if (showAlert) alert('Format nomor telepon tidak valid! Gunakan format: 6281234567890'); return false; }
            if (recipientNumbers.includes(phone)) { if (showAlert) alert('Nomor ini sudah ditambahkan!'); return false; }
            const statusElement = recipientList.querySelector('.recipient-status');
            if (statusElement) statusElement.remove();
            recipientNumbers.push(phone);
            const recipientItem = document.createElement('div');
            recipientItem.className = 'recipient-item';
            recipientItem.setAttribute('data-phone', phone);
            recipientItem.innerHTML = `<span class="recipient-number">${escapeHtml(phone)}</span><button type="button" class="remove-recipient" title="Hapus">&times;</button>`;
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

        if (addPhoneBtn) addPhoneBtn.addEventListener('click', function() { addRecipient(null, true); });
        if (phoneInput) phoneInput.addEventListener('keypress', function(e) { if (e.key === 'Enter') { e.preventDefault(); addRecipient(null, true); } });
        if (excelImport) excelImport.addEventListener('click', function() { excelFileInput.click(); });
        if (excelFileInput) excelFileInput.addEventListener('change', handleExcelImport);

        function handleExcelImport(event) {
            const file = event.target.files[0];
            if (!file) return;
            const validExtensions = ['.xlsx', '.xls', '.csv'];
            const fileExtension = '.' + file.name.split('.').pop().toLowerCase();
            if (!validExtensions.includes(fileExtension)) { alert('Format file tidak didukung! Silakan upload file Excel (.xlsx, .xls) atau CSV.'); excelFileInput.value = ''; return; }
            excelImport.innerHTML = `<svg width="15" height="15" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/></svg><span>Memproses...</span>`;
            const reader = new FileReader();
            reader.onload = function(e) {
                try {
                    const data = new Uint8Array(e.target.result);
                    const workbook = XLSX.read(data, { type: 'array' });
                    const firstSheetName = workbook.SheetNames[0];
                    const worksheet = workbook.Sheets[firstSheetName];
                    const jsonData = XLSX.utils.sheet_to_json(worksheet, { header: 1 });
                    if (jsonData.length === 0) { alert('File Excel kosong!'); resetExcelImport(); return; }
                    const headers = jsonData[0].map(h => h ? h.toString().toLowerCase() : '');
                    const whatsappIndex = headers.findIndex(h => h.includes('whatsapp') || h.includes('wa') || h.includes('nomor') || h.includes('no') || h.includes('phone') || h.includes('telepon'));
                    if (whatsappIndex === -1) { alert('Tidak ditemukan kolom "Nomor WhatsApp"!'); resetExcelImport(); return; }
                    let importedCount = 0, duplicateCount = 0, invalidCount = 0;
                    for (let i = 1; i < jsonData.length; i++) {
                        const row = jsonData[i];
                        if (!row[whatsappIndex]) continue;
                        const phone = normalizePhone(row[whatsappIndex].toString().trim());
                        if (!phone) { invalidCount++; continue; }
                        if (recipientNumbers.includes(phone)) { duplicateCount++; continue; }
                        if (addRecipient(phone, false)) importedCount++;
                    }
                    updateTargetsField();
                    renderRecipientMessageMatrix();
                    excelFileInput.value = '';
                    let resultMessage = `Berhasil mengimpor ${importedCount} nomor WhatsApp.`;
                    if (duplicateCount > 0) resultMessage += `\n${duplicateCount} nomor duplikat dilewati.`;
                    if (invalidCount > 0) resultMessage += `\n${invalidCount} nomor tidak valid dilewati.`;
                    alert(resultMessage);
                    excelImportInfo.innerHTML = `<strong>Hasil Import:</strong> ${importedCount} nomor berhasil ditambahkan`;
                    excelImportInfo.style.display = 'block';
                } catch (error) {
                    alert('Terjadi kesalahan saat membaca file Excel.');
                } finally { resetExcelImport(); }
            };
            reader.onerror = function() { alert('Gagal membaca file!'); resetExcelImport(); };
            reader.readAsArrayBuffer(file);
        }

        function updateTargetsField() { targetsField.value = recipientNumbers.join(', '); }

        function resetExcelImport() {
            excelImport.innerHTML = `<svg width="15" height="15" viewBox="0 0 24 24" fill="none"><path d="M14 2H6C5.46957 2 4.96086 2.21071 4.58579 2.58579C4.21071 2.96086 4 3.46957 4 4V20C4 20.5304 4.21071 21.0391 4.58579 21.4142C4.96086 21.7893 5.46957 22 6 22H18C18.5304 22 19.0391 21.7893 19.4142 21.4142C19.7893 21.0391 20 20.5304 20 20V8L14 2Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M14 2V8H20" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>Impor Excel`;
        }

        const templates = {
            'reminder': { name: 'Reminder Tagihan Sekolah', content: 'Yth. Bapak/Ibu {nama wali},\n\nKami ingin mengingatkan bahwa tagihan sekolah untuk {nama siswa} kelas {kelas} sebesar {tagihan} akan jatuh tempo pada {jatuh tempo}.\n\nMohon segera melakukan pembayaran. Terima kasih.\n\nSalam,\nSOY YPIK PAM JAYA' },
            'payment': { name: 'Informasi Pembayaran Sekolah', content: 'Kepada Yth. Bapak/Ibu {nama wali},\n\nBerikut informasi pembayaran untuk {nama siswa} kelas {kelas}:\n- Tagihan: {tagihan}\n- Jatuh Tempo: {jatuh tempo}\n\nTerima kasih.' },
            'notification': { name: 'Pemberitahuan Tunggakan', content: 'Kepada Yth. Bapak/Ibu {nama wali},\n\nTerdapat tunggakan pembayaran untuk {nama siswa} kelas {kelas} sebesar {tagihan}.\n\nBatas: {jatuh tempo}. Mohon segera bayar.\n\nHormat kami,\nSOY YPIK PAM JAYA' }
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
            let allRecipientSelected = false;
            selectAllRecipientsBtn.addEventListener('click', function() {
                allRecipientSelected = !allRecipientSelected;
                recipientDbCheckboxes.forEach(cb => cb.checked = allRecipientSelected);
                selectAllRecipientsBtn.textContent = allRecipientSelected ? 'Unselect All' : 'Select All';
                syncRecipientProfileFromDbSelection();
                renderRecipientMessageMatrix();
            });
        }

        recipientDbCheckboxes.forEach(cb => { cb.addEventListener('change', function() { syncRecipientProfileFromDbSelection(this); renderRecipientMessageMatrix(); }); });
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
                        newStatus.textContent = 'Belum ada penerima';
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
                    if (badge) { badge.classList.toggle('mode-template', isTemplate); badge.classList.toggle('mode-manual', mode === 'manual'); badge.classList.toggle('mode-global', isGlobal); badge.textContent = isTemplate ? 'Template' : (isGlobal ? 'Global' : 'Manual'); }
                    const hint = item.querySelector('.message-override-hint');
                    if (hint) hint.textContent = isTemplate ? 'Menggunakan template blast DB untuk penerima ini.' : (isGlobal ? 'Menggunakan isi pesan WA global untuk penerima ini.' : 'Gunakan isi manual khusus untuk penerima ini.');
                    if (textarea) { textarea.disabled = isTemplate || isGlobal; textarea.placeholder = isTemplate ? 'Mode template aktif untuk penerima ini.' : (isGlobal ? 'Mode global aktif untuk penerima ini.' : 'Isi pesan khusus untuk penerima ini...'); }
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

        function updateCharCount() { charCount.textContent = `${messageTextarea.value.length} karakter`; }
        if (messageTextarea) { messageTextarea.addEventListener('input', updateCharCount); updateCharCount(); }

        function filterRecipientDbList() {
            if (!recipientDbList || recipientDbItems.length === 0) return;
            const searchTerm = (recipientDbSearchInput?.value || '').trim().toLowerCase();
            let visibleCount = 0;
            recipientDbItems.forEach(item => {
                const isMatch = searchTerm === '' || (item.textContent || '').toLowerCase().includes(searchTerm);
                item.style.display = isMatch ? '' : 'none';
                if (isMatch) visibleCount += 1;
            });
            let emptySearch = recipientDbList.querySelector('.recipient-db-empty-search');
            if (visibleCount === 0) {
                if (!emptySearch) { emptySearch = document.createElement('div'); emptySearch.className = 'recipient-db-empty recipient-db-empty-search'; emptySearch.textContent = 'Tidak ada recipient sesuai pencarian.'; recipientDbList.appendChild(emptySearch); }
            } else if (emptySearch) emptySearch.remove();
        }

        if (recipientDbSearchInput) recipientDbSearchInput.addEventListener('input', filterRecipientDbList);

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
                const el = document.createElement('div'); el.className = 'activity-empty'; el.textContent = activities.length === 0 ? 'Belum ada aktivitas' : 'Tidak ada hasil pencarian'; activityLog.appendChild(el); return;
            }
            filteredActivities.forEach(activity => {
                const row = document.createElement('div'); row.className = 'activity-row'; row.setAttribute('data-campaign-id', String(activity.campaignId || ''));
                const statusClass = activity.status === 'success' ? 'success' : activity.status === 'failed' ? 'failed' : 'pending';
                const statusText = activity.status === 'success' ? 'Terkirim' : activity.status === 'failed' ? 'Gagal' : 'Pending';
                const logId = Number(activity.logId || 0);
                const canRetry = Boolean(activity.canRetry) && activity.status === 'failed' && logId > 0;
                const actionButtons = [];
                if (canRetry) actionButtons.push(`<button type="button" class="activity-action-btn retry" data-action="retry" data-log-id="${logId}">Retry</button>`);
                if (logId > 0) actionButtons.push(`<button type="button" class="activity-action-btn delete" data-action="delete" data-log-id="${logId}">Hapus</button>`);
                row.innerHTML = `<div class="col-waktu"><div class="waktu-date">${activity.date}</div><div class="waktu-time">${activity.time}</div></div><div class="col-siswa"><div class="siswa-name">${activity.studentName}</div></div><div class="col-kelas">${activity.studentClass}</div><div class="col-wali"><div class="wali-name">${activity.parentName}</div></div><div class="col-wa">${activity.phone}</div><div class="col-status"><span class="status-badge ${statusClass}">${statusText}</span></div><div class="col-action">${actionButtons.length > 0 ? actionButtons.join('') : '-'}</div>`;
                activityLog.appendChild(row);
            });
        }

        function renderActivitiesWithCurrentFilter() {
            const searchTerm = (searchInput?.value || '').trim().toLowerCase();
            if (searchTerm === '') { renderActivities(); return; }
            const filtered = activities.filter(activity => String(activity.studentName || '').toLowerCase().includes(searchTerm) || String(activity.parentName || '').toLowerCase().includes(searchTerm) || String(activity.phone || '').toLowerCase().includes(searchTerm) || String(activity.studentClass || '').toLowerCase().includes(searchTerm) || String(activity.campaignId || '').toLowerCase().includes(searchTerm));
            renderActivities(filtered);
        }

        async function submitActivityLogAction(action, logId) {
            const endpoint = action === 'retry' ? activityRetryApiUrl : activityDeleteApiUrl;
            const response = await fetch(endpoint, { method: 'POST', headers: { 'Accept': 'application/json', 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': csrfToken }, body: JSON.stringify({ channel: activityChannel, log_id: Number(logId) }) });
            let payload = null;
            try { payload = await response.json(); } catch (error) { payload = null; }
            if (!response.ok) throw new Error(payload?.message || 'Gagal memproses activity log.');
            return payload;
        }

        function syncCampaignClearButtons() {
            campaignClearButtons.forEach(button => {
                const target = button.getAttribute('data-clear-target');
                const input = campaignTargetInputs.find(item => item.getAttribute('data-target-action') === target);
                button.classList.toggle('visible', input ? input.value.trim() !== '' : false);
            });
        }

        function applyCampaignIdToTarget(campaignId, targetAction) {
            const input = campaignTargetInputs.find(item => item.getAttribute('data-target-action') === targetAction);
            if (!input) return;
            input.value = campaignId; syncCampaignClearButtons(); input.focus();
        }

        function renderCampaignResults(campaigns) {
            if (!campaignSearchResults) return;
            campaignSearchResults.innerHTML = '';
            if (!Array.isArray(campaigns) || campaigns.length === 0) {
                const empty = document.createElement('div'); empty.className = 'campaign-search-empty'; empty.textContent = 'Campaign tidak ditemukan.'; campaignSearchResults.appendChild(empty); return;
            }
            campaigns.forEach(campaign => {
                const item = document.createElement('div'); item.className = 'campaign-search-item';
                const meta = document.createElement('div'); meta.className = 'campaign-search-meta';
                meta.innerHTML = `<div><strong>${campaign.id}</strong></div><div>Status: ${campaign.status} | Priority: ${campaign.priority}</div><div>Total: ${campaign.stats?.total ?? 0} | Sent: ${campaign.stats?.sent ?? 0} | Failed: ${campaign.stats?.failed ?? 0} | Pending: ${campaign.stats?.pending ?? 0}</div>`;
                const actions = document.createElement('div'); actions.className = 'campaign-result-actions';
                [{ target: 'pause', label: 'Ke Pause', className: 'warning' }, { target: 'resume', label: 'Ke Resume', className: 'success' }, { target: 'stop', label: 'Ke Soft', className: 'danger' }].forEach(action => {
                    const button = document.createElement('button'); button.type = 'button'; button.className = `campaign-btn ${action.className} tiny`; button.textContent = action.label;
                    button.addEventListener('click', () => { const campaignId = String(campaign.id || ''); if (campaignSearchInput) campaignSearchInput.value = campaignId; applyCampaignIdToTarget(campaignId, action.target); if (searchInput) searchInput.value = campaignId; renderActivitiesWithCurrentFilter(); });
                    actions.appendChild(button);
                });
                item.appendChild(meta); item.appendChild(actions); campaignSearchResults.appendChild(item);
            });
        }

        async function searchCampaignsByUuid() {
            if (!campaignApiUrl || !campaignSearchResults) return;
            const keyword = (campaignSearchInput?.value || '').trim();
            campaignSearchResults.innerHTML = '<div class="campaign-search-empty">Mencari campaign...</div>';
            try {
                const response = await fetch(`${campaignApiUrl}?channel=${encodeURIComponent(activityChannel)}&q=${encodeURIComponent(keyword)}`, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } });
                if (!response.ok) throw new Error('Search failed');
                const payload = await response.json();
                renderCampaignResults(payload.campaigns || []);
                syncCampaignClearButtons();
            } catch (error) { campaignSearchResults.innerHTML = '<div class="campaign-search-empty">Gagal mencari campaign.</div>'; }
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
                if (!window.confirm(action === 'retry' ? 'Retry kirim ulang untuk log ini?' : 'Hapus log activity ini?')) return;
                const originalText = actionBtn.textContent || '';
                actionBtn.disabled = true; actionBtn.textContent = action === 'retry' ? 'Retry...' : 'Hapus...';
                try { const payload = await submitActivityLogAction(action, logId); showResultAlert('success', payload?.message || 'Aksi berhasil diproses.'); await refreshActivityLogs(); }
                catch (error) { showResultAlert('error', error?.message || 'Gagal memproses activity log.'); }
                finally { actionBtn.disabled = false; actionBtn.textContent = originalText; }
            });
        }

        if (campaignSearchBtn) campaignSearchBtn.addEventListener('click', function() { searchCampaignsByUuid(); });
        if (campaignSearchInput) campaignSearchInput.addEventListener('keydown', function(event) { if (event.key === 'Enter') { event.preventDefault(); searchCampaignsByUuid(); } });
        campaignTargetInputs.forEach(input => { input.addEventListener('input', function() { syncCampaignClearButtons(); }); });
        campaignClearButtons.forEach(button => {
            button.addEventListener('click', function() {
                const target = button.getAttribute('data-clear-target');
                const input = campaignTargetInputs.find(item => item.getAttribute('data-target-action') === target);
                if (!input) return;
                input.value = ''; syncCampaignClearButtons(); input.focus();
            });
        });

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
                if (hasPerRecipientTemplate && !hasDbTemplate) { e.preventDefault(); alert('Pilih "Template Blast DB" jika ada penerima yang menggunakan mode template.'); if (dbTemplateSelect) dbTemplateSelect.focus(); return; }
                if (hasPerRecipientGlobal && !hasGlobalMessage) { e.preventDefault(); alert('Isi pesan global wajib diisi jika ada penerima dengan mode Global.'); messageTextarea.focus(); return; }
                if (!hasDbRecipients && !hasManualTargets) { e.preventDefault(); alert('Pilih recipient dari DB atau tambahkan nomor WhatsApp manual terlebih dahulu!'); phoneInput.focus(); return; }
                if (!hasDbTemplate && !hasGlobalMessage && !hasPerRecipientContent) { e.preventDefault(); alert('Masukkan isi pesan, pilih template, atau atur pesan khusus per penerima!'); messageTextarea.focus(); return; }
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
                const confirmation = confirm(`Campaign dikirim sekarang.\nPriority: normal\nPesan akan diproses ke ${allTargetPhones.length} penerima. Lanjutkan?`);
                if (!confirmation) { e.preventDefault(); return false; }
                sendButton.disabled = true;
                sendButton.innerHTML = `<svg width="16" height="16" viewBox="0 0 24 24" fill="none" style="animation:spin 1s linear infinite;"><circle cx="12" cy="12" r="10" stroke="white" stroke-width="2" stroke-dasharray="30" stroke-linecap="round"/></svg> Mengirim...`;
                return true;
            });
        }

        if (attachFile) attachFile.addEventListener('click', function() { attachmentContainer.style.display = 'block'; });

        updateCharCount();
        updateStats();
        renderActivitiesWithCurrentFilter();
        filterRecipientDbList();
        updateDbTemplatePreview();
        syncRecipientProfileFromDbSelection();
        renderRecipientMessageMatrix();
        syncMessageOverridesField();
        searchCampaignsByUuid();
        syncCampaignClearButtons();
        refreshActivityLogs();

        setInterval(() => { if (document.visibilityState !== 'hidden') refreshActivityLogs(); }, 5000);
    });
</script>

<style>
@keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
</style>

@endsection

