@extends('layouts.app')

@section('title', 'Email Blast')

@section('content')

<style>
@import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');

:root {
    --navy: #1e2a4a;
    --navy-light: #2d3d66;
    --blue-primary: #2563eb;
    --blue-mid: #3b82f6;
    --blue-light: #dbeafe;
    --blue-lighter: #eff6ff;
    --blue-border: #bfdbfe;
    --accent: #1d4ed8;
    --text-dark: #0f172a;
    --text-mid: #1e293b;
    --text-muted: #64748b;
    --text-light: #94a3b8;
    --border: #e2e8f0;
    --border-blue: #bfdbfe;
    --bg: #f0f4fd;
    --white: #ffffff;
    --green: #16a34a;
    --green-bg: #dcfce7;
    --green-border: #86efac;
    --red: #dc2626;
    --red-bg: #fee2e2;
    --red-border: #fca5a5;
    --yellow: #d97706;
    --yellow-bg: #fef3c7;
    --yellow-border: #fcd34d;
    --shadow-sm: 0 1px 4px rgba(15,23,42,.06);
    --shadow: 0 4px 20px rgba(15,23,42,.09);
    --shadow-lg: 0 8px 32px rgba(15,23,42,.12);
    --shadow-blue: 0 8px 24px rgba(37,99,235,.15);
    --radius: 14px;
    --radius-sm: 9px;
}

*, *::before, *::after { box-sizing: border-box; }

body, .content-wrapper {
    background: var(--bg) !important;
    font-family: 'Plus Jakarta Sans', sans-serif !important;
}

/* ── CONTAINER ─────────────────────────── */
.eb-wrap {
    padding: 18px;
    min-height: 100vh;
    background: var(--bg);
}

/* ── PAGE HEADER ────────────────────────── */
.eb-page-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    padding: 18px 22px;
    background: linear-gradient(135deg, var(--navy) 0%, var(--navy-light) 100%);
    border-radius: var(--radius);
    margin-bottom: 18px;
    position: relative;
    overflow: hidden;
    box-shadow: var(--shadow-lg);
}
.eb-page-header::before {
    content: '';
    position: absolute;
    top: -40px; right: -40px;
    width: 160px; height: 160px;
    background: radial-gradient(circle, rgba(59,130,246,.22) 0%, transparent 70%);
    border-radius: 50%;
}
.eb-page-header::after {
    content: '';
    position: absolute;
    bottom: -30px; left: 30%;
    width: 120px; height: 120px;
    background: radial-gradient(circle, rgba(59,130,246,.12) 0%, transparent 70%);
    border-radius: 50%;
}

.eb-header-left {
    display: flex;
    align-items: center;
    gap: 16px;
    position: relative;
}

.eb-app-icon {
    width: 52px; height: 52px;
    background: linear-gradient(135deg, var(--blue-primary), var(--blue-mid));
    border-radius: 13px;
    display: flex; align-items: center; justify-content: center;
    box-shadow: 0 6px 18px rgba(37,99,235,.4);
    flex-shrink: 0;
}
.eb-app-icon svg path { fill: #fff; }

.eb-header-text {}
.eb-header-title {
    font-size: 22px;
    font-weight: 800;
    color: #fff;
    letter-spacing: -.4px;
    margin-bottom: 2px;
}
.eb-header-sub {
    font-size: 12.5px;
    color: rgba(255,255,255,.55);
    font-weight: 500;
}

/* ── ALERTS ─────────────────────────────── */
.eb-alert {
    padding: 13px 16px;
    border-radius: var(--radius-sm);
    font-size: 13.5px;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 9px;
    margin-bottom: 14px;
}
.eb-alert.success { background: #ecfdf5; color: #065f46; border: 1px solid #a7f3d0; }
.eb-alert.error { background: #fef2f2; color: #991b1b; border: 1px solid #fecaca; }

/* ── CARD BASE ──────────────────────────── */
.eb-card {
    background: var(--white);
    border: 1px solid var(--border-blue);
    border-radius: var(--radius);
    box-shadow: var(--shadow);
}

/* ── SECTION TITLE ──────────────────────── */
.eb-section-title {
    font-size: 15px;
    font-weight: 800;
    color: var(--navy);
    letter-spacing: -.2px;
    margin-bottom: 16px;
    display: flex;
    align-items: center;
    gap: 8px;
}
.eb-section-title i {
    width: 28px; height: 28px;
    background: var(--blue-lighter);
    border-radius: 7px;
    display: flex; align-items: center; justify-content: center;
    color: var(--blue-primary);
    font-size: 12px;
    flex-shrink: 0;
}

/* ── CAMPAIGN CONTROL ───────────────────── */
.eb-campaign-panel {
    padding: 18px 20px;
    margin-bottom: 18px;
}
.eb-campaign-header {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    margin-bottom: 14px;
}
.eb-campaign-icon {
    width: 40px; height: 40px;
    background: linear-gradient(135deg, var(--blue-primary), var(--blue-mid));
    border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
    box-shadow: 0 4px 12px rgba(37,99,235,.3);
}
.eb-campaign-icon i { color: #fff; font-size: 15px; }
.eb-campaign-info {}
.eb-campaign-info h5 {
    font-size: 14px;
    font-weight: 800;
    color: var(--navy);
    margin: 0 0 3px;
}
.eb-campaign-info p {
    font-size: 11.5px;
    color: var(--text-muted);
    margin: 0;
    line-height: 1.4;
}

.eb-search-row {
    display: flex;
    gap: 8px;
    margin-bottom: 12px;
    align-items: center;
}

.eb-control-grid {
    display: grid;
    gap: 10px;
    grid-template-columns: repeat(3, minmax(0, 1fr));
}

.campaign-search-results {
    display: flex;
    flex-direction: column;
    gap: 6px;
    margin-bottom: 12px;
    max-height: 180px;
    overflow: auto;
}
.campaign-search-empty { font-size: 12px; color: var(--text-muted); }
.campaign-search-item {
    border: 1px solid var(--border-blue);
    border-radius: var(--radius-sm);
    padding: 8px 10px;
    display: flex;
    justify-content: space-between;
    gap: 8px;
    align-items: center;
    background: var(--blue-lighter);
}
.campaign-search-meta { font-size: 11px; color: var(--text-mid); line-height: 1.4; }
.campaign-result-actions { display: flex; gap: 6px; flex-wrap: wrap; justify-content: flex-end; }

.campaign-control-form { display: flex; gap: 7px; }
.campaign-input-wrap { position: relative; flex: 1; }
.campaign-control-input {
    flex: 1; width: 100%;
    border: 1px solid var(--border-blue);
    border-radius: var(--radius-sm);
    padding: 8px 12px;
    font-size: 12px;
    font-family: inherit;
    background: var(--blue-lighter);
    color: var(--text-dark);
    transition: border-color .2s, box-shadow .2s;
}
.campaign-control-input:focus {
    outline: none;
    border-color: var(--blue-mid);
    box-shadow: 0 0 0 3px rgba(59,130,246,.15);
}
.campaign-target-input { padding-right: 28px; }
.campaign-clear-btn {
    position: absolute; right: 7px; top: 50%; transform: translateY(-50%);
    width: 20px; height: 20px;
    border: none; border-radius: 50%;
    background: var(--border); color: var(--text-mid);
    font-size: 13px; line-height: 1; cursor: pointer;
    display: none; align-items: center; justify-content: center; padding: 0;
}
.campaign-clear-btn.visible { display: inline-flex; }

/* ── BUTTONS ─────────────────────────────── */
.campaign-btn {
    border: none;
    border-radius: var(--radius-sm);
    padding: 8px 13px;
    color: #fff;
    font-size: 12px;
    font-weight: 700;
    font-family: inherit;
    cursor: pointer;
    transition: opacity .15s, transform .12s, box-shadow .15s;
    white-space: nowrap;
}
.campaign-btn:hover { opacity: .9; transform: translateY(-1px); }
.campaign-btn.info { background: linear-gradient(135deg, var(--blue-primary), var(--blue-mid)); box-shadow: 0 4px 12px rgba(37,99,235,.25); }
.campaign-btn.warning { background: linear-gradient(135deg, #f59e0b, #d97706); box-shadow: 0 4px 12px rgba(245,158,11,.25); }
.campaign-btn.success { background: linear-gradient(135deg, #22c55e, #16a34a); box-shadow: 0 4px 12px rgba(34,197,94,.25); }
.campaign-btn.danger { background: linear-gradient(135deg, #ef4444, #dc2626); box-shadow: 0 4px 12px rgba(239,68,68,.25); }
.campaign-btn.tiny { padding: 5px 9px; font-size: 11px; }

/* ── STATS ──────────────────────────────── */
.eb-stats-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 14px;
    margin-bottom: 18px;
}

.eb-stat-card {
    background: var(--white);
    border: 1px solid var(--border-blue);
    border-radius: var(--radius);
    padding: 18px 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    box-shadow: var(--shadow);
    transition: transform .2s, box-shadow .2s;
    position: relative;
    overflow: hidden;
}
.eb-stat-card::before {
    content: '';
    position: absolute;
    top: 0; left: 0;
    width: 4px; height: 100%;
    border-radius: 4px 0 0 4px;
}
.eb-stat-card.blue::before { background: var(--blue-primary); }
.eb-stat-card.green::before { background: #22c55e; }
.eb-stat-card.red::before { background: #ef4444; }
.eb-stat-card.yellow::before { background: #f59e0b; }
.eb-stat-card:hover { transform: translateY(-2px); box-shadow: var(--shadow-blue); }

.eb-stat-content {}
.eb-stat-label { font-size: 11.5px; color: var(--text-muted); font-weight: 600; letter-spacing: .04em; text-transform: uppercase; margin-bottom: 6px; }
.eb-stat-value { font-size: 30px; font-weight: 800; color: var(--navy); letter-spacing: -1px; line-height: 1; }

.eb-stat-icon {
    width: 46px; height: 46px;
    border-radius: 12px;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
}
.eb-stat-icon.blue { background: var(--blue-light); color: var(--blue-primary); }
.eb-stat-icon.green { background: #dcfce7; color: #16a34a; }
.eb-stat-icon.red { background: #fee2e2; color: #dc2626; }
.eb-stat-icon.yellow { background: #fef3c7; color: #d97706; }

/* ── MAIN GRID ──────────────────────────── */
.eb-main-grid {
    display: flex;
    flex-direction: column;
    gap: 16px;
    margin-bottom: 18px;
}
.eb-top-row {
    display: grid;
    grid-template-columns: 380px minmax(0,1fr);
    gap: 16px;
}

/* ── RECIPIENT CARD ─────────────────────── */
.eb-recipient-card { padding: 20px; }
.eb-message-card { padding: 20px; }

/* Chip input */
.chip-input-section {
    border: 1px solid var(--border-blue);
    border-radius: var(--radius-sm);
    padding: 10px 12px;
    min-height: 54px;
    background: var(--blue-lighter);
    margin-bottom: 6px;
    transition: border-color .2s, box-shadow .2s;
}
.chip-input-section:focus-within {
    border-color: var(--blue-mid);
    box-shadow: 0 0 0 3px rgba(59,130,246,.15);
}
.chip-list { display: flex; flex-wrap: wrap; gap: 6px; margin-bottom: 6px; }
.chip {
    background: linear-gradient(135deg, var(--navy), var(--blue-primary));
    color: #fff;
    padding: 5px 11px;
    border-radius: 999px;
    font-size: 12px;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 5px;
    box-shadow: 0 2px 6px rgba(37,99,235,.22);
}
.chip button {
    background: none; border: none; color: #fff; cursor: pointer;
    font-size: 13px; line-height: 1; padding: 0;
    width: 16px; height: 16px;
    display: flex; align-items: center; justify-content: center;
    border-radius: 50%;
    transition: background .15s;
}
.chip button:hover { background: rgba(255,255,255,.2); }
.email-input-main {
    width: 100%; border: none; padding: 6px 0;
    font-size: 13px; font-family: inherit; font-weight: 500;
    outline: none; background: transparent; color: var(--text-dark);
}
.email-input-main::placeholder { color: var(--text-light); }

.field-hint { font-size: 11.5px; color: var(--text-muted); display: block; margin-top: 4px; margin-bottom: 12px; }

/* Recipient DB */
.recipient-db-section {
    margin-top: 12px;
    border: 1px solid var(--border-blue);
    border-radius: var(--radius-sm);
    padding: 12px;
    background: var(--blue-lighter);
}
.recipient-db-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px; }
.recipient-db-title { font-size: 12.5px; font-weight: 700; color: var(--navy); }
.recipient-db-count { font-size: 11.5px; color: var(--text-muted); margin-bottom: 8px; }
.recipient-db-filters {
    margin-bottom: 8px;
    display: grid;
    grid-template-columns: minmax(0, 1fr) 130px;
    gap: 6px;
}
.recipient-db-search-input {
    width: 100%;
    border: 1px solid var(--border-blue);
    border-radius: 8px;
    padding: 7px 10px;
    font-size: 12px;
    font-family: inherit;
    color: var(--text-dark);
    background: var(--white);
    transition: border-color .2s;
}
.recipient-db-search-input:focus { outline: none; border-color: var(--blue-mid); }
.recipient-db-class-filter {
    width: 100%;
    border: 1px solid var(--border-blue);
    border-radius: 8px;
    padding: 7px 10px;
    font-size: 12px;
    font-family: inherit;
    color: var(--text-dark);
    background: var(--white);
    transition: border-color .2s;
}
.recipient-db-class-filter:focus { outline: none; border-color: var(--blue-mid); }
.btn-select-db {
    border: 1px solid var(--blue-primary);
    color: var(--blue-primary);
    background: var(--white);
    border-radius: 7px;
    font-size: 11.5px;
    font-weight: 700;
    font-family: inherit;
    padding: 4px 10px;
    cursor: pointer;
    transition: background .15s;
}
.btn-select-db:hover { background: var(--blue-light); }
.recipient-db-list { max-height: 180px; overflow-y: auto; display: flex; flex-direction: column; gap: 5px; }
.recipient-db-list::-webkit-scrollbar { width: 4px; }
.recipient-db-list::-webkit-scrollbar-thumb { background: var(--border-blue); border-radius: 4px; }
.recipient-db-item {
    display: flex;
    align-items: flex-start;
    gap: 9px;
    padding: 8px 10px;
    border: 1px solid var(--border);
    border-radius: 9px;
    background: var(--white);
    cursor: pointer;
    transition: border-color .15s;
}
.recipient-db-item:hover { border-color: var(--blue-border); }
.recipient-db-checkbox { margin-top: 2px; accent-color: var(--blue-primary); }
.recipient-db-info { display: flex; flex-direction: column; gap: 2px; }
.recipient-db-name { font-size: 12px; font-weight: 700; color: var(--text-dark); }
.recipient-db-email { font-size: 11px; color: var(--text-muted); }
.recipient-db-empty { font-size: 12px; color: var(--text-muted); font-style: italic; padding: 6px 0; }

/* Excel Import */
.excel-import {
    display: flex; align-items: center; justify-content: center; gap: 8px;
    padding: 11px 12px;
    background: var(--blue-lighter);
    border: 1.5px dashed var(--blue-border);
    border-radius: var(--radius-sm);
    cursor: pointer; margin-bottom: 0; margin-top: 12px;
    transition: all .2s;
    font-size: 13px; color: var(--accent); font-weight: 600;
}
.excel-import:hover { background: var(--blue-light); border-color: var(--blue-primary); }

/* ── FORM ELEMENTS ──────────────────────── */
.eb-form-group { margin-bottom: 14px; }
.eb-label {
    display: block;
    font-size: 12px;
    font-weight: 700;
    color: var(--text-mid);
    letter-spacing: .03em;
    margin-bottom: 6px;
}
.eb-input, .eb-select, .eb-textarea {
    width: 100%;
    border: 1px solid var(--border-blue);
    border-radius: var(--radius-sm);
    padding: 9px 12px;
    font-size: 13px;
    font-family: inherit;
    font-weight: 500;
    color: var(--text-dark);
    background: var(--blue-lighter);
    transition: border-color .2s, box-shadow .2s;
}
.eb-input:focus, .eb-select:focus, .eb-textarea:focus {
    outline: none;
    border-color: var(--blue-mid);
    background: var(--white);
    box-shadow: 0 0 0 3px rgba(59,130,246,.15);
}
.eb-input::placeholder, .eb-textarea::placeholder { color: var(--text-light); }
.eb-select { height: auto; padding: 9px 12px; }
.eb-textarea { min-height: 160px; resize: vertical; line-height: 1.55; }

/* Template preview */
.template-preview-box {
    min-height: 90px;
    border: 1px solid var(--border-blue);
    border-radius: var(--radius-sm);
    background: var(--blue-lighter);
    padding: 10px 12px;
    font-size: 12px;
    color: var(--text-muted);
    white-space: pre-wrap;
    font-style: italic;
}

/* Recipient message override */
.recipient-message-note { font-size: 11.5px; color: var(--text-muted); margin-bottom: 8px; }
.recipient-message-matrix {
    max-height: 240px;
    overflow-y: auto;
    border: 1px solid var(--border-blue);
    border-radius: var(--radius-sm);
    background: var(--white);
    padding: 10px;
    display: flex;
    flex-direction: column;
    gap: 10px;
}
.message-override-item { border: 1px solid var(--border); border-radius: 9px; background: #fafafa; padding: 10px; }
.message-override-item.mode-template { border-color: #a7f3d0; background: #f0fdf4; }
.message-override-item.mode-manual { border-color: var(--border-blue); background: var(--blue-lighter); }
.message-override-item.mode-global { border-color: var(--yellow-border); background: var(--yellow-bg); }
.message-override-head { display: flex; justify-content: space-between; align-items: center; gap: 8px; margin-bottom: 8px; }
.message-override-actions { display: flex; align-items: center; gap: 6px; }
.message-override-title { font-size: 12px; font-weight: 700; color: var(--text-dark); }
.message-override-badge { font-size: 10px; font-weight: 700; border-radius: 999px; padding: 3px 8px; }
.message-override-badge.mode-template { background: #d1fae5; color: #065f46; }
.message-override-badge.mode-manual { background: var(--blue-light); color: var(--accent); }
.message-override-badge.mode-global { background: var(--yellow-bg); color: var(--yellow); }
.message-override-remove { width: 22px; height: 22px; border: 1px solid var(--red-border); border-radius: 50%; background: var(--red-bg); color: var(--red); font-size: 14px; line-height: 1; cursor: pointer; display: inline-flex; align-items: center; justify-content: center; transition: .15s; }
.message-override-remove:hover { background: #fecaca; }
.message-override-file-wrap { margin-top: 8px; }
.message-override-file-label { font-size: 11px; font-weight: 600; color: var(--text-mid); margin-bottom: 6px; }
.message-override-file-input { font-size: 11px; width: 100%; }
.message-override-file-list { margin-top: 6px; display: flex; flex-direction: column; gap: 4px; }
.message-override-file-item { display: flex; justify-content: space-between; align-items: center; gap: 6px; padding: 4px 8px; border: 1px solid var(--border); border-radius: 6px; background: var(--white); font-size: 11px; color: var(--text-mid); }
.message-override-file-remove { border: 1px solid var(--red-border); background: var(--red-bg); color: var(--red); border-radius: 50%; width: 18px; height: 18px; font-size: 11px; line-height: 1; cursor: pointer; }
.message-override-file-empty { font-size: 11px; color: var(--text-light); }
.message-override-mode { display: flex; gap: 12px; font-size: 12px; color: var(--text-mid); margin-bottom: 8px; }
.message-override-mode label { display: flex; align-items: center; gap: 4px; margin: 0; font-weight: 600; }
.message-override-text { width: 100%; min-height: 72px; border: 1px solid var(--border-blue); border-radius: 8px; padding: 8px 10px; font-size: 12px; font-family: inherit; outline: none; resize: vertical; transition: border-color .2s; }
.message-override-text:focus { border-color: var(--blue-mid); }
.message-override-text:disabled { background: #f3f4f6; color: var(--text-light); }
.message-override-hint { font-size: 11px; color: var(--text-muted); margin-top: 6px; }
.global-default-toggle { display: flex; align-items: flex-start; gap: 8px; margin-top: 10px; font-size: 12px; color: var(--text-muted); font-weight: 500; }

/* Message footer */
.eb-msg-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 10px;
    flex-wrap: wrap;
    gap: 8px;
}
.attachment-buttons { display: flex; gap: 8px; }
.attach-btn {
    display: flex; align-items: center; gap: 6px;
    padding: 8px 13px;
    background: var(--blue-lighter);
    border: 1px solid var(--border-blue);
    border-radius: var(--radius-sm);
    cursor: pointer; transition: all .15s;
    font-size: 12.5px; font-weight: 600; color: var(--accent); font-family: inherit;
}
.attach-btn:hover { background: var(--blue-light); border-color: var(--blue-primary); }
.field-input-file { display: none; }
.char-count { font-size: 11.5px; color: var(--text-light); font-weight: 500; }
.form-hint { font-size: 11.5px; color: var(--text-muted); margin-top: 6px; margin-bottom: 16px; }

/* Attachment preview */
#attachmentPreview { margin-top: 8px; display: flex; flex-direction: column; gap: 5px; }
.attachment-item {
    padding: 7px 11px;
    font-size: 12px;
    background: var(--blue-lighter);
    border: 1px solid var(--border-blue);
    border-radius: 8px;
    color: var(--text-mid);
    display: flex; justify-content: space-between; align-items: center;
    font-weight: 500;
}
.attachment-remove {
    background: none; border: none; color: var(--red);
    font-size: 15px; cursor: pointer; padding: 2px 6px; border-radius: 6px;
}
.attachment-remove:hover { background: var(--red-bg); }

/* Send Button */
.eb-send-btn {
    width: 100%;
    height: 48px;
    background: linear-gradient(135deg, var(--navy), var(--blue-primary));
    color: #fff;
    border: none;
    border-radius: var(--radius-sm);
    font-size: 14px;
    font-weight: 700;
    font-family: inherit;
    cursor: pointer;
    display: flex; align-items: center; justify-content: center; gap: 8px;
    box-shadow: 0 6px 20px rgba(37,99,235,.3);
    transition: transform .2s, box-shadow .2s, opacity .15s;
    letter-spacing: .02em;
}
.eb-send-btn:hover { transform: translateY(-2px); box-shadow: 0 10px 28px rgba(37,99,235,.38); }
.eb-send-btn:disabled { opacity: .6; cursor: not-allowed; transform: none; }

/* ── ACTIVITY LOG ────────────────────────── */
.eb-activity-card { padding: 20px; }
.activity-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; flex-wrap: wrap; gap: 10px; }
.activity-header-actions { display: flex; align-items: center; gap: 10px; }
.search-small {
    display: flex; align-items: center; gap: 7px;
    padding: 8px 11px;
    background: var(--blue-lighter);
    border: 1px solid var(--border-blue);
    border-radius: var(--radius-sm);
    width: 240px;
}
.search-input-small { flex: 1; border: none; background: transparent; outline: none; font-size: 12.5px; font-family: inherit; font-weight: 500; color: var(--text-dark); }
.search-input-small::placeholder { color: var(--text-light); }
.activity-clear-form { display: flex; }

.activity-table { font-size: 12px; }
.activity-table-header {
    display: grid;
    grid-template-columns: 120px 1fr 80px 1fr 180px 1fr 100px 100px 130px;
    gap: 10px;
    padding: 10px 14px;
    background: linear-gradient(90deg, var(--navy) 0%, var(--navy-light) 100%);
    border-radius: var(--radius-sm);
    font-weight: 700;
    color: #ffffff;
    margin-bottom: 6px;
    font-size: 11px;
    letter-spacing: .04em;
    text-transform: uppercase;
}
.activity-table-header > div { color: #ffffff !important; }
.activity-table-body { max-height: 300px; overflow-y: auto; }
.activity-table-body::-webkit-scrollbar { width: 5px; }
.activity-table-body::-webkit-scrollbar-thumb { background: var(--border-blue); border-radius: 4px; }
.activity-empty { text-align: center; color: var(--text-muted); padding: 50px 20px; font-size: 13px; font-weight: 500; }
.activity-row {
    display: grid;
    grid-template-columns: 120px 1fr 80px 1fr 180px 1fr 100px 100px 130px;
    gap: 10px;
    padding: 10px 14px;
    border-bottom: 1px solid var(--border-blue);
    align-items: center;
    font-size: 12px;
    transition: background .12s;
}
.activity-row:hover { background: var(--blue-lighter); }
.waktu-date { font-size: 11px; color: var(--text-dark); font-weight: 700; margin-bottom: 1px; }
.waktu-time { font-size: 10px; color: var(--text-light); }
.col-email, .col-siswa, .col-kelas, .col-wali { font-size: 11.5px; color: var(--text-muted); word-break: break-word; line-height: 1.4; }
.col-subject { font-size: 12px; color: var(--text-dark); font-weight: 600; word-break: break-word; line-height: 1.3; }
.col-attachment { font-size: 11px; color: var(--text-muted); }
.col-action { display: flex; align-items: center; gap: 5px; flex-wrap: wrap; }

.activity-action-btn {
    border: 1px solid transparent;
    border-radius: 999px;
    padding: 4px 9px;
    font-size: 10.5px;
    font-weight: 700;
    font-family: inherit;
    cursor: pointer;
    line-height: 1.2;
    transition: opacity .15s;
}
.activity-action-btn:disabled { opacity: .6; cursor: not-allowed; }
.activity-action-btn.retry { background: var(--blue-lighter); color: var(--accent); border-color: var(--border-blue); }
.activity-action-btn.delete { background: var(--red-bg); color: var(--red); border-color: var(--red-border); }

.status-badge {
    display: inline-flex; align-items: center; gap: 5px;
    padding: 5px 11px;
    border-radius: 999px;
    font-size: 11px;
    font-weight: 700;
    white-space: nowrap;
}
.status-badge::before { content: ''; width: 6px; height: 6px; border-radius: 50%; background: currentColor; flex-shrink: 0; }
.status-badge.success { background: var(--green-bg); color: var(--green); }
.status-badge.failed { background: var(--red-bg); color: var(--red); }
.status-badge.pending { background: var(--yellow-bg); color: var(--yellow); }

/* ── TIPS SECTION ───────────────────────── */
.eb-tips {
    background: var(--white);
    border: 1px solid var(--border-blue);
    border-radius: var(--radius);
    padding: 16px 20px;
    display: flex;
    gap: 14px;
    align-items: flex-start;
    box-shadow: var(--shadow);
}
.eb-tips-icon {
    width: 36px; height: 36px;
    background: linear-gradient(135deg, var(--blue-primary), var(--blue-mid));
    border-radius: 9px;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
    box-shadow: 0 4px 10px rgba(37,99,235,.25);
}
.eb-tips-icon i { color: #fff; font-size: 14px; }
.eb-tips-body {}
.eb-tips-title { font-size: 13px; font-weight: 800; color: var(--navy); margin-bottom: 8px; }
.eb-tips-list { display: flex; flex-direction: column; gap: 4px; }
.tip-item {
    font-size: 12.5px; color: var(--text-muted);
    padding-left: 13px; position: relative; font-weight: 500;
}
.tip-item::before { content: '—'; position: absolute; left: 0; color: var(--blue-primary); font-weight: 700; }

/* ── RESPONSIVE ─────────────────────────── */
@media(max-width:1400px){
    .eb-top-row { grid-template-columns: 1fr; }
    .eb-stats-grid { grid-template-columns: repeat(2,1fr); }
    .eb-control-grid { grid-template-columns: 1fr; }
    .activity-table-header, .activity-row { grid-template-columns: 100px 1fr 80px 1fr 150px 1fr 90px 90px 120px; font-size: 11px; }
}
@media(max-width:768px){
    .eb-wrap { padding: 12px; }
    .eb-stats-grid { grid-template-columns: 1fr; }
    .eb-page-header { flex-direction: column; align-items: flex-start; }
    .activity-table-header { display: none; }
    .activity-row { grid-template-columns: 1fr; gap: 6px; padding: 12px; background: var(--blue-lighter); border-radius: var(--radius-sm); margin-bottom: 8px; }
    .eb-search-row { flex-direction: column; }
    .campaign-control-form { flex-direction: column; }
    .recipient-db-filters { grid-template-columns: 1fr; }
}
</style>

<div class="eb-wrap">

    {{-- ── PAGE HEADER ── --}}
    <div class="eb-page-header">
        <div class="eb-header-left">
            <div class="eb-app-icon">
                <svg width="28" height="28" viewBox="0 0 32 32" fill="none">
                    <path d="M28 6H4C2.9 6 2 6.9 2 8V24C2 25.1 2.9 26 4 26H28C29.1 26 30 25.1 30 24V8C30 6.9 29.1 6 28 6ZM28 8L16 15L4 8H28ZM28 24H4V10L16 17L28 10V24Z" fill="#fff"/>
                </svg>
            </div>
            <div class="eb-header-text">
                <div class="eb-header-title">Email Blast</div>
                <div class="eb-header-sub">Kirim email ke banyak penerima secara massal</div>
            </div>
        </div>
    </div>

    {{-- ── ALERTS ── --}}
    @if(session('success'))
        <div class="eb-alert success">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
        </div>
    @endif
    @if($errors->any())
        <div class="eb-alert error">
            <i class="fas fa-exclamation-triangle"></i> {{ $errors->first() }}
        </div>
    @endif

    {{-- ── CAMPAIGN CONTROL ── --}}
    <div class="eb-card eb-campaign-panel" style="margin-bottom:18px;">
        <div class="eb-campaign-header">
            <div class="eb-campaign-icon"><i class="fas fa-sliders-h"></i></div>
            <div class="eb-campaign-info">
                <h5>Campaign Control</h5>
                <p>Masukkan Campaign ID untuk pause, resume, atau stop. UUID untuk Pause, Resume, dan Soft Stop bisa berbeda.</p>
            </div>
        </div>

        <div class="eb-search-row">
            <input type="text" id="campaignSearchInput" class="campaign-control-input" placeholder="Cari Campaign UUID..." value="">
            <button type="button" id="campaignSearchBtn" class="campaign-btn info"><i class="fas fa-search" style="font-size:11px;margin-right:4px;"></i> Search UUID</button>
        </div>
        <div id="campaignSearchResults" class="campaign-search-results"></div>

        <div class="eb-control-grid">
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
    <div class="eb-stats-grid">
        <div class="eb-stat-card blue">
            <div class="eb-stat-content">
                <div class="eb-stat-label">Total</div>
                <div class="eb-stat-value" id="statTotal">{{ $activityStats['total'] ?? 0 }}</div>
            </div>
            <div class="eb-stat-icon blue">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none"><path d="M4 4H20C21.1 4 22 4.9 22 6V18C22 19.1 21.1 20 20 20H4C2.9 20 2 19.1 2 18V6C2 4.9 2.9 4 4 4Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M22 6L12 13L2 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </div>
        </div>
        <div class="eb-stat-card green">
            <div class="eb-stat-content">
                <div class="eb-stat-label">Terkirim</div>
                <div class="eb-stat-value" id="statSent">{{ $activityStats['sent'] ?? 0 }}</div>
            </div>
            <div class="eb-stat-icon green">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none"><path d="M22 11.08V12C21.9988 14.1564 21.3005 16.2547 20.0093 17.9818C18.7182 19.7088 16.9033 20.9725 14.8354 21.5839C12.7674 22.1953 10.5573 22.1219 8.53447 21.3746C6.51168 20.6273 4.78465 19.2461 3.61096 17.4371C2.43727 15.628 1.87979 13.4881 2.02168 11.3363C2.16356 9.18455 2.99721 7.13631 4.39828 5.49706C5.79935 3.85781 7.69279 2.71537 9.79619 2.24013C11.8996 1.7649 14.1003 1.98232 16.07 2.85999" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M22 4L12 14.01L9 11.01" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </div>
        </div>
        <div class="eb-stat-card red">
            <div class="eb-stat-content">
                <div class="eb-stat-label">Gagal</div>
                <div class="eb-stat-value" id="statFailed">{{ $activityStats['failed'] ?? 0 }}</div>
            </div>
            <div class="eb-stat-icon red">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/><path d="M15 9L9 15M9 9l6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
            </div>
        </div>
        <div class="eb-stat-card yellow">
            <div class="eb-stat-content">
                <div class="eb-stat-label">Pending</div>
                <div class="eb-stat-value" id="statPending">{{ $activityStats['pending'] ?? 0 }}</div>
            </div>
            <div class="eb-stat-icon yellow">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/><path d="M12 6V12L16 14" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </div>
        </div>
    </div>

    {{-- ── MAIN GRID ── --}}
    <div class="eb-main-grid">
        <form method="POST" action="{{ route('admin.blast.email.send') }}" enctype="multipart/form-data" class="email-form" id="emailForm">
            @csrf
            <div class="eb-top-row">

                {{-- ── LEFT: PENERIMA ── --}}
                <div class="eb-card eb-recipient-card">
                    <div class="eb-section-title"><i class="fas fa-users"></i> Penerima Email</div>

                    <div class="chip-input-section">
                        <div id="emailChips" class="chip-list"></div>
                        <input type="email" id="emailInput" class="email-input-main" placeholder="Ketik email lalu tekan Enter...">
                    </div>
                    <small class="field-hint"><i class="fas fa-info-circle" style="color:var(--blue-primary);margin-right:3px;"></i> Tekan Enter untuk menambahkan email</small>

                    <div class="recipient-db-section">
                        <div class="recipient-db-header">
                            <span class="recipient-db-title"><i class="fas fa-database" style="color:var(--blue-primary);margin-right:4px;font-size:11px;"></i> Recipient List DB</span>
                            <button type="button" class="btn-select-db" id="selectAllRecipientsBtn">Select All</button>
                        </div>
                        <div class="recipient-db-count">Total valid recipient: {{ $recipients->count() }}</div>
                        <div class="recipient-db-filters">
                            <input type="text" id="recipientDbSearchInput" class="recipient-db-search-input" placeholder="Cari recipient DB...">
                            <select id="recipientDbClassFilter" class="recipient-db-class-filter">
                                <option value="">Semua kelas</option>
                                @foreach(($recipientClasses ?? collect()) as $kelas)
                                    <option value="{{ strtolower(trim((string) $kelas)) }}">{{ $kelas }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="recipient-db-list">
                            @forelse($recipients as $recipient)
                                <label class="recipient-db-item" for="recipient_{{ $recipient->id }}" data-kelas="{{ strtolower(trim((string) $recipient->kelas)) }}">
                                    <input type="checkbox" class="recipient-db-checkbox" id="recipient_{{ $recipient->id }}" name="recipient_ids[]" value="{{ $recipient->id }}" data-email="{{ $recipient->email_wali }}" data-student-name="{{ $recipient->nama_siswa }}" data-student-class="{{ $recipient->kelas }}" data-parent-name="{{ $recipient->nama_wali }}">
                                    <div class="recipient-db-info">
                                        <div class="recipient-db-name">{{ $recipient->nama_siswa }} ({{ $recipient->kelas }})</div>
                                        <div class="recipient-db-email">{{ $recipient->nama_wali }} — {{ $recipient->email_wali }}</div>
                                    </div>
                                </label>
                            @empty
                                <div class="recipient-db-empty">Tidak ada recipient email valid.</div>
                            @endforelse
                        </div>
                    </div>

                    <textarea name="targets" id="targetsField" hidden></textarea>

                    <div class="excel-import" id="excelImport">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none"><path d="M14 2H6C5.46957 2 4.96086 2.21071 4.58579 2.58579C4.21071 2.96086 4 3.46957 4 4V20C4 20.5304 4.21071 21.0391 4.58579 21.4142C4.96086 21.7893 5.46957 22 6 22H18C18.5304 22 19.0391 21.7893 19.4142 21.4142C19.7893 21.0391 20 20.5304 20 20V8L14 2Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M14 2V8H20" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        <span>Impor Excel</span>
                    </div>
                </div>

                {{-- ── RIGHT: PESAN ── --}}
                <div class="eb-card eb-message-card">
                    <div class="eb-section-title"><i class="fas fa-envelope-open-text"></i> Kotak Pesan Email</div>

                    <div class="eb-form-group">
                        <label class="eb-label">Nama Siswa</label>
                        <input type="text" name="student_name" id="studentName" class="eb-input" placeholder="Masukkan nama siswa">
                    </div>
                    <div class="eb-form-group">
                        <label class="eb-label">Kelas</label>
                        <input type="text" name="student_class" id="studentClass" class="eb-input" placeholder="Contoh: 5A">
                    </div>
                    <div class="eb-form-group">
                        <label class="eb-label">Nama Wali</label>
                        <input type="text" name="parent_name" id="parentName" class="eb-input" placeholder="Masukkan nama wali">
                    </div>

                    <div class="eb-form-group">
                        <label class="eb-label">Template</label>
                        <select name="template" id="templateSelect" class="eb-select">
                            <option value="">Pilih Template</option>
                            <option value="reminder">Reminder Tagihan Sekolah</option>
                            <option value="payment">Informasi Pembayaran Sekolah</option>
                            <option value="notification">Pemberitahuan Tunggakan</option>
                        </select>
                    </div>

                    <div class="eb-form-group">
                        <label class="eb-label">Announcement</label>
                        <select name="announcement_id" id="announcementSelect" class="eb-select">
                            <option value="">Pilih Announcement (opsional)</option>
                            @foreach($announcementOptions as $announcement)
                                <option value="{{ $announcement->id }}" data-title="{{ e($announcement->title) }}" data-message="{{ e($announcement->message) }}">
                                    {{ \Illuminate\Support\Str::limit($announcement->title, 80) }}
                                </option>
                            @endforeach
                        </select>
                        <small class="field-hint">Pilih announcement untuk mengisi subject dan pesan secara otomatis.</small>
                    </div>

                    <div class="eb-form-group">
                        <label class="eb-label">Template Blast DB</label>
                        <select name="template_id" id="dbTemplateSelect" class="eb-select">
                            <option value="">Tanpa template</option>
                            @foreach($templates as $template)
                                <option value="{{ $template->id }}" data-content="{{ e($template->content) }}">{{ $template->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="eb-form-group">
                        <label class="eb-label">Template Preview</label>
                        <div id="dbTemplatePreview" class="template-preview-box">Pilih template untuk melihat preview.</div>
                    </div>

                    <div class="eb-form-group">
                        <label class="eb-label">Pesan Khusus Per Penerima</label>
                        <div class="recipient-message-note">Atur per penerima: pilih mode <b>manual</b>, <b>template</b>, atau <b>global</b>.</div>
                        <div id="recipientMessageMatrix" class="recipient-message-matrix">
                            <div class="recipient-db-empty">Pilih recipient DB atau tambah email manual untuk mengatur pesan khusus.</div>
                        </div>
                        <input type="hidden" name="message_overrides" id="messageOverridesField">
                    </div>

                    <div class="eb-form-group">
                        <label class="eb-label">Subjek Email <span style="color:var(--red);">*</span></label>
                        <input name="subject" id="emailSubject" class="eb-input" placeholder="Masukkan subjek email" required>
                    </div>

                    <div class="eb-form-group">
                        <label class="eb-label">Isi Email</label>
                        <textarea name="message" id="messageTextarea" class="eb-textarea" placeholder="Tulis isi email di sini..." rows="8"></textarea>
                        <label class="global-default-toggle">
                            <input type="checkbox" name="use_global_default" id="useGlobalDefaultToggle" value="1" checked>
                            Gunakan isi email global sebagai default penerima.
                        </label>
                    </div>

                    <div class="eb-form-group">
                        <label class="eb-label">Pengaturan Pengiriman Lanjutan</label>
                        <div class="recipient-message-note" style="margin-bottom:0;">Pengiriman Email diproses langsung. Fitur jadwal & delay dinonaktifkan.</div>
                        <input type="hidden" name="scheduled_at" id="scheduledAtInput" value="">
                        <input type="hidden" name="priority" id="priorityInput" value="normal">
                        <input type="hidden" name="rate_limit_per_minute" id="rateLimitInput" value="5000">
                        <input type="hidden" name="batch_size" id="batchSizeInput" value="2000">
                        <input type="hidden" name="batch_delay_seconds" id="batchDelayInput" value="0">
                        <input type="hidden" name="retry_attempts" id="retryAttemptsInput" value="1">
                        <input type="hidden" name="retry_backoff_seconds" id="retryBackoffInput" value="0">
                    </div>

                    <div class="eb-msg-footer">
                        <div class="attachment-buttons">
                            <label class="attach-btn" for="fileAttachment">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none"><path d="M21.44 11.05L12.25 20.24C11.1242 21.3658 9.59723 21.9983 8.005 21.9983C6.41277 21.9983 4.88583 21.3658 3.76 20.24C2.63417 19.1142 2.00166 17.5872 2.00166 15.995C2.00166 14.4028 2.63417 12.8758 3.76 11.75L12.33 3.18C13.0806 2.42944 14.0991 2.00667 15.16 2.00667C16.2209 2.00667 17.2394 2.42944 17.99 3.18C18.7406 3.93056 19.1633 4.94908 19.1633 6.01C19.1633 7.07092 18.7406 8.08944 17.99 8.84L9.41 17.41C9.03472 17.7853 8.52548 17.9967 7.995 17.9967C7.46452 17.9967 6.95528 17.7853 6.58 17.41C6.20472 17.0347 5.99333 16.5255 5.99333 15.995C5.99333 15.4645 6.20472 14.9553 6.58 14.58L15.07 6.1" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                Lampirkan File
                            </label>
                            <input type="file" name="attachments[]" id="fileAttachment" class="field-input-file" multiple>
                        </div>
                        <div class="char-count" id="charCount">0 karakter</div>
                    </div>

                    <div id="attachmentPreview"></div>
                    <div class="form-hint">Anda dapat memilih lebih dari satu file</div>

                    <button type="submit" class="eb-send-btn" id="sendButton">
                        <svg width="17" height="17" viewBox="0 0 24 24" fill="none"><path d="M22 2L11 13" stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/><path d="M22 2L15 22L11 13L2 9L22 2Z" stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        Kirim Email Blast
                    </button>
                </div>
            </div>
        </form>

        {{-- ── ACTIVITY LOG ── --}}
        <div class="eb-card eb-activity-card">
            <div class="activity-header">
                <div class="eb-section-title" style="margin-bottom:0;"><i class="fas fa-list-alt"></i> Activity Log</div>
                <div class="activity-header-actions">
                    <form method="POST" action="{{ route('admin.blast.activity.clear') }}" class="activity-clear-form" onsubmit="return confirm('Yakin ingin menghapus semua activity log Email?')">
                        @csrf
                        <input type="hidden" name="channel" value="email">
                        <button type="submit" class="campaign-btn danger tiny"><i class="fas fa-trash-alt" style="margin-right:4px;font-size:10px;"></i> Clear Log</button>
                    </form>
                    <div class="search-small">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none"><circle cx="11" cy="11" r="8" stroke="var(--text-light)" stroke-width="2"/><path d="M21 21L16.65 16.65" stroke="var(--text-light)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        <input type="text" placeholder="Cari aktivitas..." class="search-input-small" id="searchInput">
                    </div>
                </div>
            </div>

            <div class="activity-table">
                <div class="activity-table-header">
                    <div class="col-waktu">Waktu</div>
                    <div class="col-siswa">Siswa</div>
                    <div class="col-kelas">Kelas</div>
                    <div class="col-wali">Wali</div>
                    <div class="col-email">Email</div>
                    <div class="col-subject">Subjek</div>
                    <div class="col-attachment">Lampiran</div>
                    <div class="col-status">Status</div>
                    <div class="col-action">Aksi</div>
                </div>
                <div class="activity-table-body" id="activityLog">
                    <div class="activity-empty">Belum ada aktivitas</div>
                </div>
            </div>
        </div>
    </div>

    {{-- ── TIPS ── --}}
    <div class="eb-tips">
        <div class="eb-tips-icon"><i class="fas fa-lightbulb"></i></div>
        <div class="eb-tips-body">
            <div class="eb-tips-title">Tips Pengiriman Email</div>
            <div class="eb-tips-list">
                <div class="tip-item">Pastikan email yang dimasukkan valid dan aktif sebelum mengirim.</div>
                <div class="tip-item">Gunakan subjek yang jelas dan menarik untuk meningkatkan tingkat buka.</div>
                <div class="tip-item">Personalisasi pesan menggunakan variabel untuk engagement yang lebih baik.</div>
                <div class="tip-item">Hindari penggunaan kata-kata yang masuk spam filter email.</div>
            </div>
        </div>
    </div>

</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const flashSuccess = @json(session('success'));
        const flashError = @json(session('error') ?? ($errors->any() ? $errors->first() : null));

        function showResultAlert(type, message) {
            if (!message) return;
            if (window.Swal && typeof window.Swal.fire === 'function') {
                window.Swal.fire({ icon: type === 'success' ? 'success' : 'error', title: type === 'success' ? 'Berhasil' : 'Gagal', text: message, timer: 2600, showConfirmButton: false });
                return;
            }
            alert(message);
        }

        if (flashSuccess) showResultAlert('success', flashSuccess);
        else if (flashError) showResultAlert('error', flashError);

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
        const activityChannel = 'email';
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || document.querySelector('input[name="_token"]')?.value || '';
        let activities = @json($activityLogs ?? []);
        let isRefreshingActivities = false;

        let emails = [];
        const overrideState = {};
        const attachmentBufferByKey = {};

        const templates = {
            reminder: {
                subject: "Reminder Tagihan Sekolah - {nama_siswa} Kelas {kelas}",
                message: `Yth. Bapak/Ibu {nama_wali},\n\nKami ingin mengingatkan bahwa tagihan sekolah untuk {nama_siswa} (Kelas {kelas}) akan jatuh tempo pada {jatuh_tempo}.\n\nDetail Tagihan:\n- Jumlah: Rp {tagihan}\n- Jatuh Tempo: {jatuh_tempo}\n- Status: Belum Lunas\n\nMohon untuk segera melakukan pembayaran.\n\nTerima kasih.\n\nHormat kami,\nAdministrasi Sekolah`
            },
            payment: {
                subject: "Informasi Pembayaran Sekolah - {nama_siswa} Kelas {kelas}",
                message: `Kepada Yth. Bapak/Ibu {nama_wali},\n\nBerikut informasi pembayaran sekolah:\n\nNama Siswa: {nama_siswa}\nKelas: {kelas}\nTotal: Rp {tagihan}\nBatas Pembayaran: {jatuh_tempo}\n\nTerima kasih.\n\nSalam,\nBendahara Sekolah`
            },
            notification: {
                subject: "Pemberitahuan Tunggakan - {nama_siswa} Kelas {kelas}",
                message: `KEPADA YTH.\nBAPAK/IBU {nama_wali}\n\nTerdapat tunggakan pembayaran:\n- Nama Siswa: {nama_siswa}\n- Kelas: {kelas}\n- Total Tunggakan: Rp {tagihan}\n- Jatuh Tempo: {jatuh_tempo}\n\nMohon segera melakukan pelunasan.\n\nHORMAT KAMI,\nKEPALA SEKOLAH`
            }
        };

        function syncTargets() { targetsField.value = emails.join(','); }

        function addChip(email) {
            if (emails.includes(email)) return;
            emails.push(email);
            syncTargets();
            const chip = document.createElement('div');
            chip.className = 'chip';
            chip.setAttribute('data-email', email);
            chip.innerHTML = `${email} <button type="button">&times;</button>`;
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
            emails = emails.filter(e => e.trim().toLowerCase() !== normalized);
            delete overrideState[`manual:${normalized}`];
            delete attachmentBufferByKey[`manual:${normalized}`];
            syncTargets();
            chipList.querySelectorAll('.chip').forEach(chip => {
                if ((chip.getAttribute('data-email') || '').trim().toLowerCase() === normalized) chip.remove();
            });
        }

        function removeDbRecipientById(recipientId) {
            recipientDbCheckboxes.forEach(cb => { if (cb.value === recipientId) cb.checked = false; });
            delete overrideState[`db:${recipientId}`];
            delete attachmentBufferByKey[`db:${recipientId}`];
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
                selectAllRecipientsBtn.textContent = 'Select Visible';
                return;
            }

            selectAllRecipientsBtn.disabled = false;
            const allVisibleChecked = visibleCheckboxes.every(cb => cb.checked);
            selectAllRecipientsBtn.textContent = allVisibleChecked ? 'Unselect Visible' : 'Select Visible';
        }

        function syncRecipientProfileFromDbSelection(preferredRecipient = null) {
            if (!studentName || !studentClass || !parentName) return;
            const sourceRecipient = getPrimaryCheckedDbRecipient(preferredRecipient);
            if (!sourceRecipient) return;
            studentName.value = (sourceRecipient.getAttribute('data-student-name') || '').trim();
            studentClass.value = (sourceRecipient.getAttribute('data-student-class') || '').trim();
            parentName.value = (sourceRecipient.getAttribute('data-parent-name') || '').trim();
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

        function escapeHtml(value) { return String(value).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#39;'); }

        function getSelectedRecipients() {
            const recipients = [];
            recipientDbCheckboxes.forEach(cb => {
                if (!cb.checked) return;
                const key = `db:${cb.value}`;
                const label = cb.closest('.recipient-db-item')?.querySelector('.recipient-db-name')?.textContent?.trim() || cb.value;
                recipients.push({ key, label: `DB - ${label}`, kind: 'db', ref: cb.value });
            });
            emails.forEach(email => { recipients.push({ key: `manual:${email.trim().toLowerCase()}`, label: `Manual - ${email}`, kind: 'manual', ref: email }); });
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
                recipientMessageMatrix.innerHTML = `<div class="recipient-db-empty">Pilih recipient DB atau tambah email manual untuk mengatur pesan khusus.</div>`;
                syncMessageOverridesField(); return;
            }
            recipientMessageMatrix.innerHTML = recipients.map(({ key, label, kind, ref }) => {
                const state = overrideState[key] || {};
                const mode = (state.mode || 'manual').toLowerCase();
                const manualChecked = mode === 'manual'; const templateChecked = mode === 'template'; const globalChecked = mode === 'global';
                const effectiveMode = templateChecked ? 'template' : (globalChecked ? 'global' : 'manual');
                const message = escapeHtml(state.message || '');
                const keyToken = keyToToken(key);
                const radioGroup = `override_mode_${key.replace(/[^a-zA-Z0-9_-]/g, '_')}`;
                const modeClass = `mode-${effectiveMode}`;
                const badgeText = effectiveMode === 'template' ? 'Template' : (effectiveMode === 'global' ? 'Global' : 'Manual');
                const hintText = effectiveMode === 'template' ? 'Menggunakan template blast DB untuk penerima ini.' : (effectiveMode === 'global' ? 'Menggunakan isi email global untuk penerima ini.' : 'Gunakan isi manual khusus untuk penerima ini.');
                const textPlaceholder = effectiveMode === 'template' ? 'Mode template aktif untuk penerima ini.' : (effectiveMode === 'global' ? 'Mode global aktif untuk penerima ini.' : 'Isi pesan khusus untuk penerima ini...');
                return `<div class="message-override-item ${modeClass}" data-key="${escapeHtml(key)}" data-kind="${escapeHtml(kind)}" data-ref="${escapeHtml(ref)}"><div class="message-override-head"><div class="message-override-title">${escapeHtml(label)}</div><div class="message-override-actions"><span class="message-override-badge ${modeClass}">${badgeText}</span><button type="button" class="message-override-remove" title="Hapus">&times;</button></div></div><div class="message-override-mode"><label><input type="radio" name="${radioGroup}" class="message-override-mode-input" data-mode="manual" ${manualChecked ? 'checked' : ''}> Manual</label><label><input type="radio" name="${radioGroup}" class="message-override-mode-input" data-mode="template" ${templateChecked ? 'checked' : ''}> Template</label><label><input type="radio" name="${radioGroup}" class="message-override-mode-input" data-mode="global" ${globalChecked ? 'checked' : ''}> Global</label></div><textarea class="message-override-text" placeholder="${textPlaceholder}" ${(templateChecked || globalChecked) ? 'disabled' : ''}>${message}</textarea><div class="message-override-file-wrap"><div class="message-override-file-label">File Khusus Penerima (opsional)</div><input type="hidden" name="attachment_override_keys[${keyToken}]" value="${escapeHtml(key)}"><input type="file" class="message-override-file-input" name="attachment_overrides[${keyToken}][]" multiple><div class="message-override-file-list"></div></div><div class="message-override-hint">${hintText}</div></div>`;
            }).join('');
            recipientMessageMatrix.querySelectorAll('.message-override-item').forEach(item => {
                const key = item.getAttribute('data-key');
                if (key) renderAttachmentPreview(item, key);
            });
            syncMessageOverridesField();
        }

        if (emailInput) {
            emailInput.addEventListener('keydown', e => {
                if (e.key === 'Enter') { e.preventDefault(); const value = emailInput.value.trim(); if (value && value.includes('@')) { addChip(value); emailInput.value = ''; } }
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

        recipientDbCheckboxes.forEach(cb => { cb.addEventListener('change', () => { syncRecipientProfileFromDbSelection(cb); renderRecipientMessageMatrix(); updateSelectAllRecipientsBtnLabel(); }); });

        function updateDbTemplatePreview() {
            if (!dbTemplateSelect || !dbTemplatePreview) return;
            const selectedOption = dbTemplateSelect.options[dbTemplateSelect.selectedIndex];
            const content = selectedOption ? selectedOption.getAttribute('data-content') : '';
            const templateName = selectedOption && selectedOption.value ? selectedOption.textContent.trim() : '';
            dbTemplatePreview.textContent = content && content.trim().length > 0 ? `Template: ${templateName}\n\n${content}` : 'Pilih template untuk melihat preview.';
        }

        if (dbTemplateSelect) { dbTemplateSelect.addEventListener('change', updateDbTemplatePreview); updateDbTemplatePreview(); }

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
                if (kind === 'manual' && ref) removeManualEmailByAddress(ref);
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
                if (fileInput) { const buffer = ensureAttachmentBuffer(key); Array.from(fileInput.files || []).forEach(file => buffer.items.add(file)); renderAttachmentPreview(item, key); return; }
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
                    if (hint) hint.textContent = isTemplate ? 'Menggunakan template blast DB untuk penerima ini.' : (isGlobal ? 'Menggunakan isi email global untuk penerima ini.' : 'Gunakan isi manual khusus untuk penerima ini.');
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

        if (announcementSelect) {
            announcementSelect.addEventListener('change', function() {
                const selectedOption = this.options[this.selectedIndex];
                if (!selectedOption || !selectedOption.value) return;
                const title = (selectedOption.getAttribute('data-title') || '').trim();
                const message = selectedOption.getAttribute('data-message') || '';
                if (title !== '') emailSubject.value = `[Announcement] ${title}`;
                if (message.trim() !== '') { messageTextarea.value = message; messageTextarea.dispatchEvent(new Event('input')); }
            });
        }

        if (templateSelect) {
            templateSelect.addEventListener('change', function() {
                const selectedTemplate = this.value;
                if (selectedTemplate && templates[selectedTemplate]) {
                    const template = templates[selectedTemplate];
                    let subject = template.subject.replace('{nama_siswa}', studentName.value || '{nama_siswa}').replace('{kelas}', studentClass.value || '{kelas}');
                    let message = template.message.replace(/{nama_siswa}/g, studentName.value || '{nama_siswa}').replace(/{kelas}/g, studentClass.value || '{kelas}').replace(/{nama_wali}/g, parentName.value || '{nama_wali}');
                    emailSubject.value = subject;
                    messageTextarea.value = message;
                    messageTextarea.dispatchEvent(new Event('input'));
                }
            });
        }

        [studentName, studentClass, parentName].forEach(input => {
            input.addEventListener('input', function() {
                if (templateSelect.value && templates[templateSelect.value]) {
                    const template = templates[templateSelect.value];
                    let subject = template.subject.replace('{nama_siswa}', studentName.value || '{nama_siswa}').replace('{kelas}', studentClass.value || '{kelas}');
                    let message = template.message.replace(/{nama_siswa}/g, studentName.value || '{nama_siswa}').replace(/{kelas}/g, studentClass.value || '{kelas}').replace(/{nama_wali}/g, parentName.value || '{nama_wali}');
                    emailSubject.value = subject;
                    messageTextarea.value = message;
                    messageTextarea.dispatchEvent(new Event('input'));
                }
            });
        });

        const varButtons = document.querySelectorAll('.var-btn');
        varButtons.forEach(btn => {
            btn.addEventListener('click', function() {
                const variable = this.getAttribute('data-variable');
                const cursorPos = messageTextarea.selectionStart;
                messageTextarea.value = messageTextarea.value.substring(0, cursorPos) + '{' + variable + '}' + messageTextarea.value.substring(cursorPos);
                messageTextarea.focus();
                const newPos = cursorPos + variable.length + 2;
                messageTextarea.setSelectionRange(newPos, newPos);
                messageTextarea.dispatchEvent(new Event('input'));
            });
        });

        if (messageTextarea && charCount) {
            function updateCharCount() { charCount.textContent = `${messageTextarea.value.length} karakter`; }
            messageTextarea.addEventListener('input', updateCharCount);
            updateCharCount();
        }

        const fileAttachment = document.getElementById('fileAttachment');
        const preview = document.getElementById('attachmentPreview');
        let fileBuffer = new DataTransfer();

        if (fileAttachment && preview) {
            fileAttachment.addEventListener('change', function() {
                Array.from(this.files).forEach(file => fileBuffer.items.add(file));
                syncFiles();
            });
        }

        function syncFiles() {
            preview.innerHTML = '';
            fileAttachment.files = fileBuffer.files;
            Array.from(fileBuffer.files).forEach((file, index) => {
                const item = document.createElement('div');
                item.className = 'attachment-item';
                item.innerHTML = `<span><i class="fas fa-file" style="color:var(--blue-primary);margin-right:5px;font-size:11px;"></i>${file.name} (${(file.size / 1024).toFixed(1)} KB)</span><button type="button" class="attachment-remove">&times;</button>`;
                item.querySelector('.attachment-remove').addEventListener('click', () => removeFile(index));
                preview.appendChild(item);
            });
        }

        function removeFile(index) {
            const newBuffer = new DataTransfer();
            Array.from(fileBuffer.files).forEach((file, i) => { if (i !== index) newBuffer.items.add(file); });
            fileBuffer = newBuffer;
            syncFiles();
        }

        const excelImport = document.getElementById('excelImport');
        if (excelImport) {
            excelImport.addEventListener('click', function() {
                const fileInput = document.createElement('input');
                fileInput.type = 'file';
                fileInput.accept = '.xlsx,.xls,.csv';
                fileInput.addEventListener('change', function(e) {
                    if (e.target.files.length > 0) {
                        const fileName = e.target.files[0].name;
                        alert(`File "${fileName}" berhasil diimpor!`);
                        const demoEmails = ['wali1@example.com','wali2@example.com','wali3@example.com','wali4@example.com','wali5@example.com'];
                        emails = []; chipList.innerHTML = '';
                        demoEmails.forEach(email => addChip(email));
                        alert(`${demoEmails.length} email berhasil diimpor dari file Excel!`);
                    }
                });
                fileInput.click();
            });
        }

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
                row.innerHTML = `<div class="col-waktu"><div class="waktu-date">${activity.date}</div><div class="waktu-time">${activity.time}</div></div><div class="col-siswa">${activity.studentName}</div><div class="col-kelas">${activity.studentClass}</div><div class="col-wali">${activity.parentName}</div><div class="col-email">${activity.email}</div><div class="col-subject">${activity.subject}</div><div class="col-attachment">${activity.attachments}</div><div class="col-status"><span class="status-badge ${statusClass}">${statusText}</span></div><div class="col-action">${actionButtons.length > 0 ? actionButtons.join('') : '-'}</div>`;
                activityLog.appendChild(row);
            });
        }

        function renderActivitiesWithCurrentFilter() {
            const searchTerm = (searchInput?.value || '').trim().toLowerCase();
            if (searchTerm === '') { renderActivities(); return; }
            const filtered = activities.filter(activity => String(activity.email || '').toLowerCase().includes(searchTerm) || String(activity.subject || '').toLowerCase().includes(searchTerm) || String(activity.studentName || '').toLowerCase().includes(searchTerm) || String(activity.parentName || '').toLowerCase().includes(searchTerm) || String(activity.campaignId || '').toLowerCase().includes(searchTerm));
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
                const hasValue = input ? input.value.trim() !== '' : false;
                button.classList.toggle('visible', hasValue);
            });
        }

        function applyCampaignIdToTarget(campaignId, targetAction) {
            const input = campaignTargetInputs.find(item => item.getAttribute('data-target-action') === targetAction);
            if (!input) return;
            input.value = campaignId;
            syncCampaignClearButtons();
            input.focus();
        }

        function renderCampaignResults(campaigns) {
            if (!campaignSearchResults) return;
            campaignSearchResults.innerHTML = '';
            if (!Array.isArray(campaigns) || campaigns.length === 0) { const empty = document.createElement('div'); empty.className = 'campaign-search-empty'; empty.textContent = 'Campaign tidak ditemukan.'; campaignSearchResults.appendChild(empty); return; }
            campaigns.forEach(campaign => {
                const item = document.createElement('div'); item.className = 'campaign-search-item';
                const meta = document.createElement('div'); meta.className = 'campaign-search-meta'; meta.innerHTML = `<div><strong>${campaign.id}</strong></div><div>Status: ${campaign.status} | Priority: ${campaign.priority}</div><div>Total: ${campaign.stats?.total ?? 0} | Sent: ${campaign.stats?.sent ?? 0} | Failed: ${campaign.stats?.failed ?? 0} | Pending: ${campaign.stats?.pending ?? 0}</div>`;
                const actions = document.createElement('div'); actions.className = 'campaign-result-actions';
                [{ target: 'pause', label: 'Ke Pause', className: 'warning' }, { target: 'resume', label: 'Ke Resume', className: 'success' }, { target: 'stop', label: 'Ke Soft Stop', className: 'danger' }].forEach(action => {
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

        if (searchInput) { searchInput.addEventListener('input', function() { renderActivitiesWithCurrentFilter(); }); }

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

        if (campaignSearchBtn) { campaignSearchBtn.addEventListener('click', function() { searchCampaignsByUuid(); }); }
        if (campaignSearchInput) { campaignSearchInput.addEventListener('keydown', function(event) { if (event.key === 'Enter') { event.preventDefault(); searchCampaignsByUuid(); } }); }
        campaignTargetInputs.forEach(input => { input.addEventListener('input', function() { syncCampaignClearButtons(); }); });
        campaignClearButtons.forEach(button => {
            button.addEventListener('click', function() {
                const target = button.getAttribute('data-clear-target');
                const input = campaignTargetInputs.find(item => item.getAttribute('data-target-action') === target);
                if (!input) return;
                input.value = ''; syncCampaignClearButtons(); input.focus();
            });
        });

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
            if (visibleCount === 0) { if (!emptySearch) { emptySearch = document.createElement('div'); emptySearch.className = 'recipient-db-empty recipient-db-empty-search'; emptySearch.textContent = 'Tidak ada recipient sesuai pencarian.'; recipientDbList.appendChild(emptySearch); } }
            else if (emptySearch) emptySearch.remove();
            updateSelectAllRecipientsBtnLabel();
        }

        if (recipientDbSearchInput) { recipientDbSearchInput.addEventListener('input', filterRecipientDbList); }
        if (recipientDbClassFilter) { recipientDbClassFilter.addEventListener('change', filterRecipientDbList); }

        const emailForm = document.getElementById('emailForm');
        syncRecipientProfileFromDbSelection();
        renderRecipientMessageMatrix();

        if (emailForm) {
            emailForm.addEventListener('submit', function(e) {
                const activeOverrides = syncMessageOverridesField();
                const selectedDbRecipients = Array.from(document.querySelectorAll('.recipient-db-checkbox:checked'));
                const hasDbRecipients = selectedDbRecipients.length > 0;
                const hasManualTargets = emails.length > 0;
                const hasDbTemplate = dbTemplateSelect && dbTemplateSelect.value.trim() !== '';
                const hasGlobalMessage = messageTextarea.value.trim() !== '';
                const overrideValues = Object.values(activeOverrides);
                const hasPerRecipientManual = overrideValues.some(o => o.mode === 'manual' && (o.message || '').trim() !== '');
                const hasPerRecipientTemplate = overrideValues.some(o => o.mode === 'template');
                const hasPerRecipientGlobal = overrideValues.some(o => o.mode === 'global');
                const hasPerRecipientContent = hasPerRecipientManual || (hasPerRecipientTemplate && hasDbTemplate) || (hasPerRecipientGlobal && hasGlobalMessage);
                if (hasPerRecipientTemplate && !hasDbTemplate) { e.preventDefault(); alert('Pilih "Template Blast DB" jika ada penerima yang menggunakan mode template.'); if (dbTemplateSelect) dbTemplateSelect.focus(); return; }
                if (hasPerRecipientGlobal && !hasGlobalMessage) { e.preventDefault(); alert('Isi Email global wajib diisi jika ada penerima dengan mode Global.'); messageTextarea.focus(); return; }
                if (!hasDbRecipients && !hasManualTargets) { e.preventDefault(); alert('Pilih recipient dari DB atau tambahkan email manual terlebih dahulu!'); emailInput.focus(); return; }
                if (!emailSubject.value.trim()) { e.preventDefault(); alert('Masukkan subject email terlebih dahulu!'); emailSubject.focus(); return; }
                if (!hasDbTemplate && !hasGlobalMessage && !hasPerRecipientContent) { e.preventDefault(); alert('Masukkan isi pesan, pilih template, atau atur pesan khusus per penerima!'); messageTextarea.focus(); return; }
                if (scheduledAtInput) scheduledAtInput.value = '';
                if (priorityInput) priorityInput.value = 'normal';
                if (rateLimitInput) rateLimitInput.value = '5000';
                if (batchSizeInput) batchSizeInput.value = '2000';
                if (batchDelayInput) batchDelayInput.value = '0';
                if (retryAttemptsInput) retryAttemptsInput.value = '1';
                if (retryBackoffInput) retryBackoffInput.value = '0';
                const selectedTargets = hasManualTargets ? [...emails] : selectedDbRecipients.map(cb => cb.getAttribute('data-email') || cb.value);
                const confirmation = confirm(`Campaign dikirim sekarang.\nEmail akan diproses ke ${selectedTargets.length} penerima. Lanjutkan?`);
                if (!confirmation) { e.preventDefault(); return false; }
                sendButton.disabled = true;
                sendButton.innerHTML = '<i class="fas fa-spinner fa-spin" style="margin-right:6px;"></i> Mengirim...';
            });
        }

        renderActivitiesWithCurrentFilter();
        updateStats();
        filterRecipientDbList();
        searchCampaignsByUuid();
        syncCampaignClearButtons();
        refreshActivityLogs();
        setInterval(() => { if (document.visibilityState !== 'hidden') refreshActivityLogs(); }, 5000);
    });
</script>
@endsection
