@extends('layouts.app')

@section('section_name', 'Diskusi')

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
        --text-muted: #64748b;
        --text-light: #94a3b8;
        --border: #e2e8f0;
        --bg-main: #f0f4fd;
        --white: #ffffff;
        --success: #16a34a;
        --warning-bg: #fffbeb;
        --warning-border: #fcd34d;
        --warning-text: #92400e;
        --pin-bg: #fff7d6;
        --shadow-sm: 0 1px 4px rgba(15,23,42,.06);
        --shadow: 0 4px 24px rgba(15,23,42,.10);
        --shadow-lg: 0 8px 32px rgba(15,23,42,.13);
        --radius: 14px;
        --radius-sm: 9px;
    }

    *, *::before, *::after { box-sizing: border-box; }

    body, .content-wrapper {
        background: var(--bg-main) !important;
        font-family: 'Plus Jakarta Sans', sans-serif !important;
    }

    .content-wrapper > .content-header { display: none; }
    .content-wrapper > .content { padding: .6rem; }
    .content-wrapper > .content > .container-fluid { padding: 0; max-width: none; }

    /* ─── LAYOUT ─────────────────────────────── */
    .dc-wrap {
        display: grid;
        grid-template-columns: 270px minmax(0,1fr);
        gap: 12px;
        height: calc(100vh - 74px);
        min-height: calc(100vh - 74px);
    }

    /* ─── SIDEBAR ────────────────────────────── */
    .dc-card { background: var(--white); border-radius: var(--radius); box-shadow: var(--shadow); }
    .dc-side {
        background: var(--navy) !important;
        border-radius: var(--radius);
        box-shadow: var(--shadow-lg);
        padding: 0;
        overflow: hidden;
        display: flex;
        flex-direction: column;
    }

    .dc-side-header {
        padding: 20px 18px 16px;
        background: linear-gradient(135deg, var(--navy-light) 0%, var(--navy) 100%);
        border-bottom: 1px solid rgba(255,255,255,.08);
        position: relative;
        overflow: hidden;
    }
    .dc-side-header::before {
        content: '';
        position: absolute;
        top: -30px; right: -30px;
        width: 100px; height: 100px;
        background: radial-gradient(circle, rgba(59,130,246,.25) 0%, transparent 70%);
        border-radius: 50%;
    }
    .dc-side-icon {
        width: 42px; height: 42px;
        background: linear-gradient(135deg, var(--blue-primary), var(--blue-mid));
        border-radius: 11px;
        display: flex; align-items: center; justify-content: center;
        margin-bottom: 12px;
        box-shadow: 0 4px 14px rgba(37,99,235,.4);
    }
    .dc-side-icon i { color: #fff; font-size: 17px; }

    .dc-side h4 {
        margin: 0 0 3px;
        font-weight: 800;
        font-size: 17px;
        color: #fff;
        letter-spacing: -.3px;
        position: relative;
    }
    .dc-side p {
        margin: 0;
        color: rgba(255,255,255,.5);
        font-size: 11.5px;
        font-weight: 500;
        position: relative;
    }

    .dc-channel-list {
        flex: 1;
        overflow-y: auto;
        padding: 12px 10px;
    }
    .dc-channel-list::-webkit-scrollbar { width: 4px; }
    .dc-channel-list::-webkit-scrollbar-track { background: transparent; }
    .dc-channel-list::-webkit-scrollbar-thumb { background: rgba(255,255,255,.15); border-radius: 4px; }

    .dc-channel-label {
        font-size: 10px;
        font-weight: 700;
        color: rgba(255,255,255,.35);
        letter-spacing: .08em;
        text-transform: uppercase;
        padding: 4px 8px 8px;
    }

    .dc-channel {
        display: flex;
        align-items: center;
        gap: 9px;
        padding: 9px 11px;
        border-radius: 10px;
        color: rgba(255,255,255,.65);
        text-decoration: none;
        font-weight: 600;
        font-size: 13px;
        transition: all .18s ease;
        margin-bottom: 2px;
    }
    .dc-channel i { font-size: 12px; opacity: .7; }
    .dc-channel:hover {
        background: rgba(255,255,255,.08);
        color: #fff;
        text-decoration: none;
    }
    .dc-channel.active {
        background: linear-gradient(135deg, var(--blue-primary), var(--blue-mid));
        color: #fff;
        box-shadow: 0 4px 12px rgba(37,99,235,.35);
    }
    .dc-channel.active i { opacity: 1; }

    /* ─── MAIN PANEL ─────────────────────────── */
    .dc-main {
        display: flex;
        flex-direction: column;
        height: 100%;
        min-height: 0;
        overflow: hidden;
        border-radius: var(--radius);
        box-shadow: var(--shadow-lg);
        background: var(--white);
    }

    /* ─── HEADER ─────────────────────────────── */
    .dc-head {
        padding: 0 18px;
        height: 64px;
        border-bottom: 1px solid var(--border);
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
        background: linear-gradient(135deg, var(--navy) 0%, var(--navy-light) 100%);
        border-radius: var(--radius) var(--radius) 0 0;
        position: relative;
        overflow: hidden;
    }
    .dc-head::after {
        content: '';
        position: absolute;
        bottom: -20px; right: 60px;
        width: 80px; height: 80px;
        background: radial-gradient(circle, rgba(59,130,246,.18) 0%, transparent 70%);
    }

    .dc-head-left { display: flex; align-items: center; gap: 12px; position: relative; }
    .dc-head-channel-icon {
        width: 36px; height: 36px;
        background: rgba(255,255,255,.12);
        border-radius: 9px;
        display: flex; align-items: center; justify-content: center;
        border: 1px solid rgba(255,255,255,.15);
    }
    .dc-head-channel-icon i { color: rgba(255,255,255,.85); font-size: 14px; }

    .dc-head h5 {
        margin: 0;
        font-size: 16px;
        font-weight: 800;
        color: #fff;
        letter-spacing: -.2px;
    }
    .dc-head small {
        display: block;
        font-size: 11px;
        color: rgba(255,255,255,.5);
        font-weight: 500;
        margin-top: 1px;
    }

    .dc-head-r { display: flex; align-items: center; gap: 10px; position: relative; }
    .dc-search {
        width: 220px;
        max-width: 36vw;
        border: 1px solid rgba(255,255,255,.18);
        border-radius: 9px;
        padding: 7px 11px 7px 32px;
        font-size: 12px;
        font-family: inherit;
        font-weight: 500;
        background: rgba(255,255,255,.1);
        color: #fff;
        transition: all .2s;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='14' height='14' viewBox='0 0 24 24' fill='none' stroke='rgba(255,255,255,.5)' stroke-width='2.5' stroke-linecap='round' stroke-linejoin='round'%3E%3Ccircle cx='11' cy='11' r='8'/%3E%3Cpath d='m21 21-4.35-4.35'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: 9px center;
    }
    .dc-search::placeholder { color: rgba(255,255,255,.4); }
    .dc-search:focus {
        outline: none;
        border-color: var(--blue-mid);
        background-color: rgba(255,255,255,.15);
        box-shadow: 0 0 0 3px rgba(59,130,246,.2);
    }

    .dc-live-badge {
        display: flex;
        align-items: center;
        gap: 5px;
        background: rgba(22,163,74,.15);
        border: 1px solid rgba(22,163,74,.3);
        border-radius: 999px;
        padding: 5px 10px;
    }
    .dc-live-dot {
        width: 7px; height: 7px;
        background: #22c55e;
        border-radius: 50%;
        animation: blink 1.5s infinite;
        box-shadow: 0 0 6px rgba(34,197,94,.6);
    }
    @keyframes blink {
        0%, 100% { opacity: 1; }
        50% { opacity: .3; }
    }
    .dc-live {
        font-size: 11px;
        font-weight: 800;
        color: #4ade80;
        letter-spacing: .04em;
    }

    /* ─── PIN BOX ────────────────────────────── */
    .dc-pin-box {
        padding: 8px 14px 10px;
        border-bottom: 1px solid var(--border);
        background: linear-gradient(90deg, #fffbeb 0%, #fefce8 100%);
        max-height: 105px;
        overflow: auto;
    }
    .dc-pin-title {
        font-size: 10.5px;
        font-weight: 800;
        color: var(--warning-text);
        letter-spacing: .06em;
        text-transform: uppercase;
        margin-bottom: 6px;
        display: flex;
        align-items: center;
        gap: 5px;
    }
    #dcPinList { display: flex; flex-direction: column; gap: 5px; }
    .dc-pin-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
        width: 100%;
        text-align: left;
        background: rgba(255,255,255,.7);
        border: 1px solid var(--warning-border);
        border-radius: 8px;
        padding: 5px 8px;
        cursor: pointer;
        transition: background .15s;
    }
    .dc-pin-item:hover { background: #fff; }
    .dc-pin-main { min-width: 0; display: flex; align-items: center; gap: 7px; }
    .dc-pin-text {
        font-size: 12px;
        color: var(--text-dark);
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        max-width: 56vw;
        font-weight: 500;
    }
    .dc-pin-meta { font-size: 11px; color: #78716c; white-space: nowrap; }
    .dc-pin-doc {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        font-size: 11px;
        font-weight: 700;
        color: var(--accent);
        text-decoration: none;
        white-space: nowrap;
    }
    .dc-pin-doc:hover { text-decoration: underline; }
    .dc-pin-empty { font-size: 12px; color: var(--text-muted); font-style: italic; }

    /* ─── MESSAGE LIST ───────────────────────── */
    .dc-list {
        flex: 1;
        min-height: 0;
        overflow-y: auto;
        padding: 16px 16px 8px;
        background: var(--bg-main);
        scroll-behavior: smooth;
    }
    .dc-list::-webkit-scrollbar { width: 5px; }
    .dc-list::-webkit-scrollbar-track { background: transparent; }
    .dc-list::-webkit-scrollbar-thumb { background: var(--border); border-radius: 4px; }

    .dc-empty {
        text-align: center;
        color: var(--text-muted);
        font-size: 13px;
        font-weight: 500;
        padding: 40px 24px;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 10px;
    }
    .dc-empty::before {
        content: '💬';
        font-size: 32px;
        display: block;
    }

    /* ─── MESSAGE BUBBLE ─────────────────────── */
    .dc-msg {
        display: flex;
        gap: 10px;
        max-width: 88%;
        margin-bottom: 12px;
        animation: msgIn .22s ease;
    }
    @keyframes msgIn {
        from { opacity: 0; transform: translateY(8px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .dc-msg.own { margin-left: auto; flex-direction: row-reverse; }
    .dc-msg.selected .dc-bub,
    .dc-msg.selected .dc-vn {
        box-shadow: 0 0 0 3px rgba(37,99,235,.3);
    }
    .dc-msg.hl .dc-bub,
    .dc-msg.hl .dc-vn {
        box-shadow: 0 0 0 3px rgba(245,158,11,.35);
        background: #fffbeb !important;
    }

    .dc-av {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        background: linear-gradient(135deg, var(--navy), var(--blue-primary));
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 800;
        font-size: 12px;
        flex-shrink: 0;
        box-shadow: 0 2px 8px rgba(37,99,235,.25);
    }
    .dc-msg.own .dc-av {
        background: linear-gradient(135deg, var(--blue-primary), var(--blue-mid));
    }

    .dc-body { min-width: 0; width: 100%; }
    .dc-meta {
        display: flex;
        justify-content: space-between;
        gap: 6px;
        font-size: 11px;
        margin-bottom: 5px;
        align-items: center;
    }
    .dc-meta-l { display: flex; gap: 5px; flex-wrap: wrap; align-items: center; }
    .dc-meta strong { font-size: 12.5px; color: var(--text-dark); font-weight: 700; }
    .dc-role {
        background: var(--blue-light);
        color: var(--accent);
        border-radius: 999px;
        padding: 1px 8px;
        font-weight: 700;
        font-size: 10.5px;
    }
    .dc-time { color: var(--text-light); font-size: 10.5px; }
    .dc-pin-badge {
        background: var(--warning-bg);
        border: 1px solid var(--warning-border);
        border-radius: 999px;
        padding: 1px 7px;
        color: var(--warning-text);
        font-size: 10px;
        font-weight: 700;
    }

    .dc-bub {
        background: var(--white);
        border: 1px solid var(--border);
        border-radius: 4px 12px 12px 12px;
        padding: 9px 12px;
        font-size: 13px;
        white-space: pre-wrap;
        word-break: break-word;
        line-height: 1.55;
        color: var(--text-dark);
        box-shadow: var(--shadow-sm);
        transition: box-shadow .15s;
    }
    .dc-msg.own .dc-bub {
        background: linear-gradient(135deg, var(--blue-primary) 0%, var(--blue-mid) 100%);
        color: #fff;
        border-color: transparent;
        border-radius: 12px 4px 12px 12px;
        box-shadow: 0 4px 12px rgba(37,99,235,.2);
    }

    .dc-file {
        margin-top: 7px;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: var(--blue-lighter);
        border: 1px solid var(--blue-border);
        border-radius: 8px;
        padding: 6px 10px;
        font-size: 12px;
        font-weight: 700;
        color: var(--accent);
        text-decoration: none;
        transition: background .15s;
    }
    .dc-file:hover { background: var(--blue-light); text-decoration: none; }

    .dc-photo-link {
        display: inline-block;
        margin-top: 7px;
        border-radius: 10px;
        overflow: hidden;
        border: 1px solid var(--blue-border);
        background: #fff;
    }
    .dc-photo-link:hover { text-decoration: none; }
    .dc-photo {
        display: block;
        max-width: min(320px, 100%);
        max-height: 280px;
        object-fit: cover;
    }
    .dc-photo-meta {
        padding: 6px 8px;
        font-size: 11px;
        color: var(--text-muted);
        border-top: 1px solid var(--blue-border);
        background: var(--blue-lighter);
        font-weight: 600;
    }

    .dc-reply-box {
        margin-bottom: 7px;
        border-left: 3px solid var(--blue-mid);
        background: var(--blue-lighter);
        border-radius: 8px;
        padding: 6px 8px;
    }
    .dc-msg.own .dc-reply-box {
        background: rgba(255,255,255,.2);
        border-left-color: rgba(255,255,255,.8);
    }
    .dc-reply-sender {
        font-size: 11px;
        font-weight: 800;
        color: var(--accent);
        margin-bottom: 2px;
    }
    .dc-msg.own .dc-reply-sender { color: #fff; }
    .dc-reply-text {
        font-size: 11.5px;
        color: #334155;
        line-height: 1.35;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .dc-msg.own .dc-reply-text { color: rgba(255,255,255,.9); }

    .dc-vn {
        margin-top: 7px;
        border: 1px solid var(--blue-border);
        background: var(--blue-lighter);
        border-radius: 10px;
        padding: 8px 10px;
    }
    .dc-vn span {
        display: block;
        font-size: 11px;
        color: var(--accent);
        font-weight: 700;
        margin-bottom: 4px;
    }
    .dc-vn audio { width: min(330px, 100%); height: 32px; }

    /* ─── FORM ───────────────────────────────── */
    .dc-form {
        position: sticky;
        bottom: 0;
        background: var(--white);
        border-top: 1px solid var(--border);
        padding: 12px 16px 14px;
        z-index: 3;
    }

    /* Action bar */
    .dc-act {
        display: flex;
        gap: 6px;
        align-items: center;
        flex-wrap: wrap;
        background: var(--blue-lighter);
        border: 1px solid var(--blue-border);
        border-radius: 10px;
        padding: 8px 10px;
        margin-bottom: 10px;
    }
    .dc-act-label { font-size: 12px; font-weight: 700; color: var(--accent); }
    .dc-act-btn {
        border: 1px solid var(--border);
        border-radius: 7px;
        background: var(--white);
        padding: 5px 10px;
        font-size: 11.5px;
        font-weight: 700;
        color: #334155;
        cursor: pointer;
        transition: all .15s;
        font-family: inherit;
    }
    .dc-act-btn:hover { background: var(--bg-main); }
    .dc-act-btn.pin { background: #f0fdf4; border-color: #86efac; color: #166534; }
    .dc-act-btn.pin:hover { background: #dcfce7; }
    .dc-act-btn.warn { background: #fef2f2; border-color: #fecaca; color: #991b1b; }
    .dc-act-btn.warn:hover { background: #fee2e2; }
    .dc-act-sel {
        border: 1px solid var(--border);
        border-radius: 7px;
        background: var(--white);
        padding: 5px 10px;
        font-size: 11.5px;
        font-weight: 700;
        color: #334155;
        font-family: inherit;
        cursor: pointer;
    }

    /* Textarea */
    .dc-ta {
        width: 100%;
        min-height: 74px;
        max-height: 160px;
        resize: vertical;
        border: 1px solid var(--border);
        border-radius: 10px;
        padding: 9px 12px;
        font-size: 13px;
        font-family: inherit;
        font-weight: 500;
        color: var(--text-dark);
        background: var(--bg-main);
        transition: border-color .2s, box-shadow .2s;
        line-height: 1.5;
    }
    .dc-ta:focus {
        outline: none;
        border-color: var(--blue-mid);
        background: var(--white);
        box-shadow: 0 0 0 3px rgba(59,130,246,.15);
    }
    .dc-ta::placeholder { color: var(--text-light); }

    /* Voice preview */
    .dc-vn-preview {
        margin-top: 8px;
        border: 1px solid var(--blue-border);
        background: var(--blue-lighter);
        border-radius: 10px;
        padding: 7px 10px;
    }
    .dc-vn-preview span {
        display: block;
        font-size: 11px;
        color: var(--accent);
        font-weight: 700;
        margin-bottom: 4px;
    }
    .dc-vn-preview audio { width: min(360px, 100%); height: 34px; }

    .dc-reply-preview {
        margin-top: 8px;
        border: 1px solid var(--blue-border);
        background: var(--blue-lighter);
        border-radius: 10px;
        padding: 8px 10px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
    }
    .dc-reply-preview-main { min-width: 0; }
    .dc-reply-preview-title {
        font-size: 11px;
        color: var(--accent);
        font-weight: 800;
        display: flex;
        align-items: center;
        gap: 5px;
        margin-bottom: 3px;
    }
    .dc-reply-preview-text {
        font-size: 12px;
        color: #334155;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        max-width: 540px;
    }
    .dc-reply-preview-cancel {
        border: 1px solid var(--border);
        background: #fff;
        border-radius: 7px;
        width: 30px;
        height: 30px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: #64748b;
        cursor: pointer;
    }
    .dc-reply-preview-cancel:hover { background: var(--bg-main); }

    /* Row buttons */
    .dc-row {
        margin-top: 10px;
        display: flex;
        gap: 7px;
        align-items: center;
        flex-wrap: wrap;
    }

    .dc-f-trg {
        position: relative;
        display: inline-flex;
        gap: 6px;
        align-items: center;
        background: var(--blue-lighter);
        border: 1px solid var(--blue-border);
        border-radius: 8px;
        padding: 7px 11px;
        font-size: 12px;
        font-weight: 700;
        color: var(--accent);
        cursor: pointer;
        transition: background .15s;
    }
    .dc-f-trg:hover { background: var(--blue-light); }
    .dc-file-in { position: absolute; opacity: 0; width: 1px; height: 1px; pointer-events: none; }

    .dc-note {
        font-size: 11.5px;
        color: var(--text-muted);
        font-weight: 500;
    }

    .dc-rec {
        border: 1px solid var(--border);
        border-radius: 8px;
        background: var(--white);
        padding: 7px 11px;
        font-size: 12px;
        font-weight: 700;
        font-family: inherit;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        transition: all .15s;
        color: #334155;
    }
    .dc-rec:hover { background: var(--bg-main); }
    .dc-rec.active {
        background: #fee2e2;
        border-color: #fca5a5;
        color: #b91c1c;
    }
    .dc-rec-clear {
        border: 1px solid var(--border);
        border-radius: 8px;
        background: var(--white);
        padding: 7px 11px;
        font-size: 12px;
        font-weight: 700;
        font-family: inherit;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        transition: background .15s;
        color: #334155;
    }
    .dc-rec-clear:hover { background: var(--bg-main); }

    .dc-send {
        margin-left: auto;
        border: none;
        border-radius: 9px;
        background: linear-gradient(135deg, var(--blue-primary), var(--blue-mid));
        color: #fff;
        padding: 8px 18px;
        font-size: 13px;
        font-weight: 700;
        font-family: inherit;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        box-shadow: 0 4px 14px rgba(37,99,235,.3);
        transition: opacity .15s, transform .12s;
    }
    .dc-send:hover { opacity: .92; transform: translateY(-1px); }
    .dc-send:disabled { opacity: .6; cursor: not-allowed; transform: none; }

    .dc-emoji-picker {
        position: absolute;
        bottom: 58px;
        left: 16px;
        z-index: 20;
        width: min(300px, calc(100vw - 52px));
        background: #fff;
        border: 1px solid var(--blue-border);
        border-radius: 12px;
        box-shadow: var(--shadow);
        padding: 10px;
    }
    .dc-emoji-list {
        display: grid;
        grid-template-columns: repeat(8, minmax(0, 1fr));
        gap: 6px;
    }
    .dc-emoji-btn {
        border: 1px solid transparent;
        background: transparent;
        border-radius: 7px;
        font-size: 18px;
        line-height: 1;
        padding: 6px;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }
    .dc-emoji-btn:hover {
        background: var(--blue-lighter);
        border-color: var(--blue-border);
    }

    /* ─── RESPONSIVE ─────────────────────────── */
    @media(max-width:991px){
        .dc-wrap { grid-template-columns: 1fr; height: calc(100vh - 70px); }
        .dc-side { display: none; }
        .dc-search { width: 160px; }
    }
    @media(max-width:576px){
        .dc-send { width: 100%; margin-left: 0; justify-content: center; }
        .dc-msg { max-width: 100%; }
    }
</style>

<div class="dc-wrap">
    {{-- ── SIDEBAR ── --}}
    <aside class="dc-side">
        <div class="dc-side-header">
            <div class="dc-side-icon">
                <i class="fas fa-comments"></i>
            </div>
            <h4>Diskusi Tim</h4>
            <p>Chat, voice note, pin berdurasi, dan hapus pesan sendiri.</p>
        </div>
        <div class="dc-channel-list">
            <div class="dc-channel-label">Channels</div>
            @foreach($channels as $channel)
                <a href="{{ route('discussion.index', ['channel' => $channel->id]) }}"
                   class="dc-channel {{ (int) $activeChannel->id === (int) $channel->id ? 'active' : '' }}">
                    <i class="fas fa-hashtag"></i> {{ $channel->name }}
                </a>
            @endforeach
        </div>
    </aside>

    {{-- ── MAIN ── --}}
    <section class="dc-card dc-main">

        {{-- Header --}}
        <div class="dc-head">
            <div class="dc-head-left">
                <div class="dc-head-channel-icon">
                    <i class="fas fa-hashtag"></i>
                </div>
                <div>
                    <h5>{{ $activeChannel->name }}</h5>
                    <small>{{ $activeChannel->description ?: 'Kanal diskusi bersama lintas role.' }}</small>
                </div>
            </div>
            <div class="dc-head-r">
                <input id="dcSearch" class="dc-search" type="text" placeholder="Cari pesan...">
                <div class="dc-live-badge">
                    <span class="dc-live-dot"></span>
                    <span class="dc-live">LIVE</span>
                </div>
            </div>
        </div>

        {{-- Pinned --}}
        <div class="dc-pin-box">
            <div class="dc-pin-title">
                <i class="fas fa-thumbtack"></i> Pesan Dipin
            </div>
            <div id="dcPinList"></div>
            <div id="dcPinEmpty" class="dc-pin-empty">Belum ada pesan dipin.</div>
        </div>

        {{-- Message List --}}
        <div id="dcList" class="dc-list">
            <div id="dcEmpty" class="dc-empty">Belum ada pesan. Mulai diskusi sekarang.</div>
        </div>

        {{-- Form --}}
        <form id="dcForm" class="dc-form" enctype="multipart/form-data">
            <input type="hidden" name="channel_id" value="{{ (int) $activeChannel->id }}">

            <div id="dcAction" class="dc-act" style="display:none;">
                <span id="dcActionLabel" class="dc-act-label">Pesan terpilih</span>
                <select id="dcPinDuration" class="dc-act-sel">
                    <option value="24h">24 Jam</option>
                    <option value="48h">48 Jam</option>
                    <option value="1w" selected>1 Minggu</option>
                    <option value="1m">1 Bulan</option>
                </select>
                <button type="button" id="dcPinAction" class="dc-act-btn pin"><i class="fas fa-thumbtack"></i> Pin Chat</button>
                <button type="button" id="dcReplyAction" class="dc-act-btn"><i class="fas fa-reply"></i> Reply</button>
                <button type="button" id="dcUnpin" class="dc-act-btn"><i class="fas fa-times"></i> Lepas Pin</button>
                <button type="button" id="dcDelete" class="dc-act-btn warn"><i class="fas fa-trash-alt"></i> Hapus</button>
                <button type="button" id="dcCancelSel" class="dc-act-btn"><i class="fas fa-ban"></i> Batal</button>
            </div>

            <input type="hidden" id="dcReplyToMessageId" name="reply_to_message_id" value="">
            <div id="dcReplyPreview" class="dc-reply-preview" style="display:none;">
                <div class="dc-reply-preview-main">
                    <div class="dc-reply-preview-title">
                        <i class="fas fa-reply"></i> Membalas <span id="dcReplySender">-</span>
                    </div>
                    <div id="dcReplyText" class="dc-reply-preview-text">-</div>
                </div>
                <button type="button" id="dcReplyCancel" class="dc-reply-preview-cancel">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <textarea id="dcMessage" name="message" class="dc-ta" placeholder="Ketik pesan untuk semua role..."></textarea>

            <div id="dcVoicePreviewWrap" class="dc-vn-preview" style="display:none;">
                <span id="dcVoicePreviewLabel">Preview voice note</span>
                <audio id="dcVoicePreview" controls preload="metadata"></audio>
            </div>

            <div class="dc-row">
                <label class="dc-f-trg">
                    <i class="fas fa-paperclip"></i> Lampiran
                    <input id="dcFile" class="dc-file-in" type="file" name="attachment"
                           accept=".jpg,.jpeg,.png,.pdf,.doc,.docx,.xls,.xlsx,.csv,.txt,.zip,.rar">
                </label>
                <span id="dcFileName" class="dc-note">Tidak ada file.</span>

                <input id="dcVoice" class="dc-file-in" type="file" name="voice_note"
                       accept=".webm,.ogg,.oga,.mp3,.wav,.wave,.m4a,.aac,.mp4,.3gp,.amr,audio/*">
                <button id="dcRec" type="button" class="dc-rec">
                    <i class="fas fa-microphone"></i> Voice Note
                </button>
                <button id="dcRecClear" type="button" class="dc-rec-clear" style="display:none;">
                    <i class="fas fa-times"></i> Hapus VN
                </button>
                <span id="dcVoiceStat" class="dc-note">Belum ada voice note.</span>

                <button id="dcEmojiToggle" type="button" class="dc-rec" aria-label="Emoji">
                    <i class="far fa-smile"></i> Emoji
                </button>

                <div id="dcEmojiPicker" class="dc-emoji-picker" style="display:none;">
                    <div id="dcEmojiList" class="dc-emoji-list"></div>
                </div>

                <button id="dcSend" type="submit" class="dc-send">
                    <i class="fas fa-paper-plane"></i> Kirim
                </button>
            </div>
        </form>
    </section>
</div>
@endsection

@section('js')
<script>
(function(){
    const POLL=2000,currentUserId=@json($currentUserId),channelId=@json((int)$activeChannel->id);
    const fetchUrl=@json(route('discussion.messages')),storeUrl=@json(route('discussion.messages.store'));
    const pinTpl=@json(route('discussion.messages.pin',['message'=>'__ID__'])),delTpl=@json(route('discussion.messages.destroy',['message'=>'__ID__']));
    const initialMessages=@json($initialMessages),initialPinned=@json($initialPinnedMessages);
    const form=document.getElementById('dcForm'),list=document.getElementById('dcList'),empty=document.getElementById('dcEmpty');
    const pinList=document.getElementById('dcPinList'),pinEmpty=document.getElementById('dcPinEmpty');
    const msgInput=document.getElementById('dcMessage'),fileInput=document.getElementById('dcFile'),fileName=document.getElementById('dcFileName');
    const voiceInput=document.getElementById('dcVoice'),voiceStat=document.getElementById('dcVoiceStat'),voicePrev=document.getElementById('dcVoicePreview');
    const voicePrevWrap=document.getElementById('dcVoicePreviewWrap'),voicePrevLabel=document.getElementById('dcVoicePreviewLabel');
    const recBtn=document.getElementById('dcRec'),clearRecBtn=document.getElementById('dcRecClear'),sendBtn=document.getElementById('dcSend'),searchInput=document.getElementById('dcSearch');
    const actionBox=document.getElementById('dcAction'),actionLabel=document.getElementById('dcActionLabel'),pinDurationSel=document.getElementById('dcPinDuration'),pinActionBtn=document.getElementById('dcPinAction'),replyBtn=document.getElementById('dcReplyAction'),unpinBtn=document.getElementById('dcUnpin'),delBtn=document.getElementById('dcDelete'),cancelSelBtn=document.getElementById('dcCancelSel');
    const replyInput=document.getElementById('dcReplyToMessageId'),replyPreview=document.getElementById('dcReplyPreview'),replySender=document.getElementById('dcReplySender'),replyText=document.getElementById('dcReplyText'),replyCancelBtn=document.getElementById('dcReplyCancel');
    const emojiToggle=document.getElementById('dcEmojiToggle'),emojiPicker=document.getElementById('dcEmojiPicker'),emojiList=document.getElementById('dcEmojiList');
    const EMOJIS=['\u{1F600}','\u{1F601}','\u{1F602}','\u{1F923}','\u{1F60A}','\u{1F60D}','\u{1F970}','\u{1F60E}','\u{1F44D}','\u{1F64F}','\u{1F44F}','\u{1F525}','\u{1F4AF}','\u{1F389}','\u{2764}\u{FE0F}','\u{1F91D}','\u{1F605}','\u{1F914}','\u{1F64C}','\u{1F44C}','\u{1F607}','\u{1F973}','\u{1F634}','\u{1F932}'];
    const st={lastId:0,known:new Set(),pinned:new Set(),map:new Map(),polling:false,submit:false,pinning:false,mr:null,stream:null,chunks:[],timer:null,sec:0,selectedId:null,selectedMine:false,selectedPinned:false,selectedHasAttachment:false,previewUrl:null,discardRecording:false,searchQuery:'',emojiOpen:false};
    const fsize=(b)=>{b=Number(b||0);if(!b)return'';const u=['B','KB','MB','GB'];let i=0;while(b>=1024&&i<u.length-1){b/=1024;i++;}return`${b.toFixed(b>=10||i===0?0:1)} ${u[i]}`};
    const ftime=(s)=>`${String(Math.floor(s/60)).padStart(2,'0')}:${String(s%60).padStart(2,'0')}`;
    const esc=(v)=>String(v??'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
    const escAttr=(v)=>String(v??'').replace(/&/g,'&amp;').replace(/"/g,'&quot;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
    const initial=(n)=>((n||'?').trim().charAt(0)||'?').toUpperCase();
    const nearBottom=()=>list.scrollHeight-list.scrollTop-list.clientHeight<140,toBottom=()=>{list.scrollTop=list.scrollHeight};
    const pinUrl=(id)=>pinTpl.replace('__ID__',String(id)),delUrl=(id)=>delTpl.replace('__ID__',String(id));
    const msgPreview=(m)=>{const t=String(m?.message||'').trim();if(t)return t.length>90?`${t.slice(0,90)}...`:t;if(m?.voice_note_url||m?.voice_note_name)return'[Voice Note]';if(m?.attachment_name)return`${m?.attachment_is_image?'[Foto]':'[Lampiran]'} ${m.attachment_name}`;return'(Pesan)'};
    function rememberMsg(m){const id=Number(m?.id||0);if(!id)return;const prev=st.map.get(id)||{};st.map.set(id,{...prev,...m})}
    function setReply(m){if(!m?.id)return;replyInput.value=String(m.id);replySender.textContent=String(m?.sender?.name||'Pengguna');replyText.textContent=msgPreview(m);replyPreview.style.display='flex';selectMessage(null);msgInput.focus()}
    function clearReply(){replyInput.value='';replySender.textContent='-';replyText.textContent='-';replyPreview.style.display='none'}
    function renderEmojis(){emojiList.innerHTML='';EMOJIS.forEach((v)=>{const b=document.createElement('button');b.type='button';b.className='dc-emoji-btn';b.dataset.emoji=v;b.setAttribute('aria-label',`Emoji ${v}`);b.textContent=v;emojiList.appendChild(b)})}
    function toggleEmoji(force){st.emojiOpen=typeof force==='boolean'?force:!st.emojiOpen;emojiPicker.style.display=st.emojiOpen?'block':'none'}
    function addEmoji(v){const s=typeof msgInput.selectionStart==='number'?msgInput.selectionStart:msgInput.value.length,e=typeof msgInput.selectionEnd==='number'?msgInput.selectionEnd:msgInput.value.length;msgInput.value=`${msgInput.value.slice(0,s)}${v}${msgInput.value.slice(e)}`;const c=s+String(v).length;msgInput.focus();msgInput.setSelectionRange(c,c)}
    function updateFile(){const f=fileInput.files?.[0];fileName.textContent=f?f.name:'Tidak ada file.'}
    function clearPreview(){if(st.previewUrl){URL.revokeObjectURL(st.previewUrl);st.previewUrl=null}voicePrev.removeAttribute('src');voicePrevWrap.style.display='none'}
    function syncPreview(){const f=voiceInput.files?.[0];if(!f){clearPreview();return}clearPreview();st.previewUrl=URL.createObjectURL(f);voicePrev.src=st.previewUrl;voicePrevLabel.textContent=`Preview voice note (${fsize(f.size)})`;voicePrevWrap.style.display='block'}
    function updateRecBtn(){const rec=!!(st.mr&&st.mr.state==='recording');recBtn.classList.toggle('active',rec);recBtn.innerHTML=rec?'<i class="fas fa-stop"></i> Stop Rekam':'<i class="fas fa-microphone"></i> Voice Note'}
    function updateVoice(){if(st.mr&&st.mr.state==='recording'){voiceStat.textContent=`Merekam... ${ftime(st.sec)}`;return}const f=voiceInput.files?.[0];if(f){voiceStat.textContent=`Voice note siap (${fsize(f.size)})`;clearRecBtn.style.display='inline-flex';syncPreview();return}voiceStat.textContent='Belum ada voice note.';clearRecBtn.style.display='none';clearPreview()}
    function stopTracks(){if(st.stream){st.stream.getTracks().forEach(t=>t.stop());st.stream=null}}
    function clearTimer(){if(st.timer){clearInterval(st.timer);st.timer=null}}
    function clearVoice(){if(st.mr&&st.mr.state==='recording'){st.discardRecording=true;st.mr.stop()}voiceInput.value='';st.sec=0;clearTimer();stopTracks();st.mr=null;st.chunks=[];updateRecBtn();updateVoice()}
    function setVoice(blob){const mt=(blob.type||'audio/webm').toLowerCase();let ext='webm';if(mt.includes('ogg'))ext='ogg';else if(mt.includes('mp3')||mt.includes('mpeg'))ext='mp3';else if(mt.includes('wav'))ext='wav';else if(mt.includes('mp4')||mt.includes('m4a')||mt.includes('aac'))ext='m4a';const f=new File([blob],`voice-note-${Date.now()}.${ext}`,{type:blob.type||'audio/webm'});const dt=new DataTransfer();dt.items.add(f);voiceInput.files=dt.files;updateVoice()}
    async function recToggle(){if(st.submit)return;if(st.mr&&st.mr.state==='recording'){st.mr.stop();return}if(!window.MediaRecorder||!navigator.mediaDevices?.getUserMedia){Notification.warning('Browser ini belum mendukung perekaman voice note.');return}
        try{const s=await navigator.mediaDevices.getUserMedia({audio:true});stopTracks();st.stream=s;st.chunks=[];st.sec=0;const m=['audio/webm;codecs=opus','audio/webm','audio/ogg;codecs=opus','audio/ogg','audio/mp4'].find(v=>MediaRecorder.isTypeSupported(v));st.mr=m?new MediaRecorder(s,{mimeType:m}):new MediaRecorder(s);
            st.mr.ondataavailable=(e)=>{if(e.data?.size)st.chunks.push(e.data)};
            st.mr.onstop=()=>{const b=new Blob(st.chunks,{type:st.mr?.mimeType||'audio/webm'});if(!st.discardRecording&&b.size>0)setVoice(b);st.discardRecording=false;clearTimer();stopTracks();st.mr=null;st.chunks=[];st.sec=0;updateRecBtn();updateVoice()};
            st.mr.start();clearTimer();st.timer=setInterval(()=>{st.sec+=1;updateVoice()},1000);updateRecBtn();updateVoice();
        }catch(e){Notification.error('Akses mikrofon ditolak atau tidak tersedia.');clearVoice()}}
    function visibleMessageCount(){return Array.from(list.querySelectorAll('.dc-msg[data-message-id]')).filter((n)=>n.style.display!=='none').length}
    function toggleEmpty(){const count=visibleMessageCount();if(count>0){empty.style.display='none';return}empty.style.display='block';empty.textContent=st.searchQuery?'Tidak ada chat yang cocok.':'Belum ada pesan. Mulai diskusi sekarang.'}
    function refreshAction(){if(!st.selectedId){actionBox.style.display='none';return}actionBox.style.display='flex';actionLabel.textContent=`Pesan terpilih #${st.selectedId}`;pinActionBtn.innerHTML=`<i class="fas fa-thumbtack"></i> ${st.selectedHasAttachment?'Pin Lampiran':'Pin Chat'}`;unpinBtn.style.display=st.selectedPinned?'inline-block':'none';delBtn.style.display=st.selectedMine?'inline-block':'none'}
    function selectMessage(node){list.querySelectorAll('.dc-msg.selected').forEach(n=>n.classList.remove('selected'));if(!node){st.selectedId=null;st.selectedMine=false;st.selectedPinned=false;st.selectedHasAttachment=false;refreshAction();return}
        node.classList.add('selected');st.selectedId=Number(node.dataset.messageId||0);st.selectedMine=node.dataset.isMine==='1';st.selectedPinned=node.dataset.isPinned==='1';st.selectedHasAttachment=node.dataset.hasAttachment==='1';refreshAction()}
    function applySearch(){const q=String(st.searchQuery||'').trim().toLowerCase();list.querySelectorAll('.dc-msg[data-message-id]').forEach((node)=>{const text=String(node.dataset.searchText||'').toLowerCase();const ok=q===''||text.includes(q);node.style.display=ok?'':'none'});const selectedNode=st.selectedId?list.querySelector(`[data-message-id="${st.selectedId}"]`):null;if(selectedNode&&selectedNode.style.display==='none')selectMessage(null);toggleEmpty()}
    function applyPin(m){if(!m?.id)return;const id=Number(m.id);if(m.is_pinned)st.pinned.add(id);else st.pinned.delete(id);rememberMsg(m);const node=list.querySelector(`[data-message-id="${id}"]`);if(!node)return;node.dataset.isPinned=m.is_pinned?'1':'0';const badge=node.querySelector('.dc-pin-badge');if(badge)badge.style.display=m.is_pinned?'inline-flex':'none';if(st.selectedId===id){st.selectedPinned=!!m.is_pinned;refreshAction()}}
    function jump(id){const t=document.getElementById(`dc-msg-${id}`);if(!t){Notification.warning('Pesan belum ada di tampilan ini.');return}if(t.style.display==='none'){st.searchQuery='';searchInput.value='';applySearch()}t.scrollIntoView({behavior:'smooth',block:'center'});t.classList.add('hl');setTimeout(()=>t.classList.remove('hl'),1500);selectMessage(t)}
    function renderPins(arr){pinList.innerHTML='';st.pinned.clear();arr=Array.isArray(arr)?arr:[];if(!arr.length){pinEmpty.style.display='block';document.querySelectorAll('.dc-msg[data-message-id]').forEach(n=>applyPin({id:Number(n.dataset.messageId),is_pinned:false}));return}pinEmpty.style.display='none';
        arr.forEach(m=>{const id=Number(m.id||0);if(id)st.pinned.add(id);rememberMsg(m);const b=document.createElement('div');b.className='dc-pin-item';b.dataset.jumpMessageId=String(id);const meta=[];if(m.pinned_by_name)meta.push(`oleh ${esc(m.pinned_by_name)}`);if(m.pin_expires_at_label)meta.push(`sampai ${esc(m.pin_expires_at_label)}`);
            const icon=m.attachment_is_image?'far fa-image':'far fa-file-alt',label=m.attachment_is_image?'Foto':'Dok';
            const docLink=(m.attachment_url&&m.attachment_name)?`<a class="dc-pin-doc" href="${escAttr(m.attachment_url)}" target="_blank" rel="noopener noreferrer"><i class="${icon}"></i> ${label}</a>`:'';
            b.innerHTML=`<div class="dc-pin-main"><span class="dc-pin-text">${esc(msgPreview(m))}</span>${docLink}</div>${meta.length?`<span class="dc-pin-meta">${meta.join(' | ')}</span>`:''}`;pinList.appendChild(b)});
        document.querySelectorAll('.dc-msg[data-message-id]').forEach(n=>{const id=Number(n.dataset.messageId);applyPin({id,is_pinned:st.pinned.has(id)})})}
    function mkMsg(m){const id=Number(m.id||0),own=!!m.is_mine||String(m?.sender?.id||'')===String(currentUserId||''),p=!!m.is_pinned||st.pinned.has(id);const n=document.createElement('div');n.id=`dc-msg-${id}`;n.className=`dc-msg ${own?'own':'other'}`;n.dataset.messageId=String(id);n.dataset.isMine=own?'1':'0';n.dataset.isPinned=p?'1':'0';n.dataset.hasAttachment=(m.attachment_url&&m.attachment_name)?'1':'0';
        n.dataset.searchText=[m?.sender?.name,m?.sender?.role,m?.message,m?.attachment_name,m?.voice_note_name,m?.reply_to?.message,m?.reply_to?.sender_name].filter(Boolean).join(' ').toLowerCase();
        const pinBadge=`<span class="dc-pin-badge" style="display:${p?'inline-flex':'none'}"><i class="fas fa-thumbtack" style="font-size:9px"></i> Pinned</span>`;
        const reply=m.reply_to?`<div class="dc-reply-box"${m.reply_to?.id?` data-reply-target-id="${Number(m.reply_to.id)||0}"`:''}><div class="dc-reply-sender">${esc(m.reply_to?.sender_name||'Pengguna')}</div><div class="dc-reply-text">${esc(msgPreview(m.reply_to))}</div></div>`:'';
        const msg=m.message?`<div class="dc-bub">${esc(m.message)}</div>`:'';
        const vn=m.voice_note_url?`<div class="dc-vn"><span><i class="fas fa-microphone" style="font-size:10px"></i> Voice Note ${m.voice_note_size?`(${fsize(m.voice_note_size)})`:''}</span><audio controls preload="none" src="${escAttr(m.voice_note_url)}"></audio></div>`:'';
        let f='';if(m.attachment_url&&m.attachment_name){const label=`${esc(m.attachment_name)}${m.attachment_size?` (${fsize(m.attachment_size)})`:''}`;if(m.attachment_is_image&&m.attachment_preview_url){f=`<a class="dc-photo-link" href="${escAttr(m.attachment_url)}" target="_blank" rel="noopener noreferrer"><img class="dc-photo" src="${escAttr(m.attachment_preview_url)}" alt="${escAttr(m.attachment_name)}" loading="lazy"><div class="dc-photo-meta">${label}</div></a>`}else{f=`<a class="dc-file" href="${escAttr(m.attachment_url)}" target="_blank" rel="noopener noreferrer"><i class="fas fa-file-download" style="font-size:11px"></i>${label}</a>`}}
        n.innerHTML=`<div class="dc-av">${esc(initial(m?.sender?.name||''))}</div><div class="dc-body"><div class="dc-meta"><div class="dc-meta-l"><strong>${esc(m?.sender?.name||'Pengguna')}</strong><span class="dc-role">${esc(m?.sender?.role||'-')}</span><span class="dc-time">${esc(m.created_at_label||'-')}</span>${pinBadge}</div></div>${reply}${msg}${vn}${f}</div>`;
        return n}
    function addMsg(m,force){const id=Number(m?.id||0);if(!id)return false;rememberMsg(m);if(st.known.has(id)){applyPin(m);return false}st.known.add(id);st.lastId=Math.max(st.lastId,id);const n=mkMsg(m);list.appendChild(n);applyPin(m);applySearch();if(force)toBottom();return true}
    function removeMsg(id){const mid=Number(id||0);const node=list.querySelector(`[data-message-id="${mid}"]`);if(node)node.remove();st.map.delete(mid);if(Number(replyInput.value||0)===mid)clearReply();if(st.selectedId===mid)selectMessage(null);applySearch()}
    function renderInitial(){(Array.isArray(initialMessages)?initialMessages:[]).forEach(m=>addMsg(m,false));applySearch();toBottom()}
    function poll(){if(st.polling)return;st.polling=true;const should=nearBottom();Http.get(fetchUrl,{channel_id:channelId,after_id:st.lastId}).done((r)=>{if(Array.isArray(r?.pinned_messages))renderPins(r.pinned_messages);let fresh=false;(Array.isArray(r?.messages)?r.messages:[]).forEach(m=>{fresh=addMsg(m,false)||fresh});if(r?.latest_id)st.lastId=Math.max(st.lastId,Number(r.latest_id)||0);if(fresh&&should&&st.searchQuery==='')toBottom()}).always(()=>st.polling=false)}
    function pinSelected(duration){if(!st.selectedId||st.pinning)return;st.pinning=true;Http.post(pinUrl(st.selectedId),{channel_id:channelId,action:'pin',pin_duration:duration}).done((r)=>{if(r?.data)applyPin(r.data);if(Array.isArray(r?.pinned_messages))renderPins(r.pinned_messages)}).fail((e)=>Notification.error(e)).always(()=>st.pinning=false)}
    function unpinSelected(){if(!st.selectedId||st.pinning)return;st.pinning=true;Http.post(pinUrl(st.selectedId),{channel_id:channelId,action:'unpin'}).done((r)=>{if(r?.data)applyPin(r.data);if(Array.isArray(r?.pinned_messages))renderPins(r.pinned_messages)}).fail((e)=>Notification.error(e)).always(()=>st.pinning=false)}
    function replySelected(){if(!st.selectedId)return;const m=st.map.get(st.selectedId);if(!m){Notification.warning('Pesan tidak ditemukan.');return}setReply(m)}
    async function deleteSelected(){if(!st.selectedId)return;if(!st.selectedMine){Notification.warning('Anda hanya bisa menghapus pesan milik sendiri.');return}const confirm=await Notification.confirmation('Yakin ingin menghapus pesan ini?');if(!confirm.isConfirmed)return;
        Http.delete(delUrl(st.selectedId),{channel_id:channelId}).done((r)=>{removeMsg(st.selectedId);if(Array.isArray(r?.pinned_messages))renderPins(r.pinned_messages)}).fail((e)=>Notification.error(e))}
    function submit(e){e.preventDefault();if(st.submit)return;if(st.mr&&st.mr.state==='recording'){Notification.warning('Stop rekaman voice note dulu sebelum kirim pesan.');return}
        const txt=msgInput.value.trim(),hasFile=fileInput.files?.length>0,hasVoice=voiceInput.files?.length>0;if(!txt&&!hasFile&&!hasVoice){Notification.warning('Isi pesan, upload file, atau kirim voice note.');return}
        st.submit=true;sendBtn.disabled=true;Http.post(storeUrl,new FormData(form)).done((r)=>{if(r?.data)addMsg(r.data,true);if(Array.isArray(r?.pinned_messages))renderPins(r.pinned_messages);form.reset();updateFile();clearVoice();clearReply();toggleEmoji(false);msgInput.focus()}).fail((er)=>Notification.error(er)).always(()=>{st.submit=false;sendBtn.disabled=false})}
    form.addEventListener('submit',submit);fileInput.addEventListener('change',updateFile);recBtn.addEventListener('click',recToggle);clearRecBtn.addEventListener('click',clearVoice);
    replyBtn.addEventListener('click',replySelected);replyCancelBtn.addEventListener('click',clearReply);
    searchInput.addEventListener('input',()=>{st.searchQuery=searchInput.value||'';applySearch()});
    pinActionBtn.addEventListener('click',()=>pinSelected(String(pinDurationSel.value||'1w')));unpinBtn.addEventListener('click',unpinSelected);delBtn.addEventListener('click',deleteSelected);cancelSelBtn.addEventListener('click',()=>selectMessage(null));
    pinList.addEventListener('click',(e)=>{if(e.target.closest('.dc-pin-doc'))return;const t=e.target.closest('[data-jump-message-id]');if(t)jump(Number(t.dataset.jumpMessageId||0))});
    list.addEventListener('click',(e)=>{if(e.target===list){selectMessage(null);return}const replyNode=e.target.closest('[data-reply-target-id]');if(replyNode){jump(Number(replyNode.dataset.replyTargetId||0));return}if(e.target.closest('a,audio,button'))return;const row=e.target.closest('.dc-msg');if(row)selectMessage(row)});
    emojiToggle.addEventListener('click',(e)=>{e.preventDefault();e.stopPropagation();toggleEmoji()});
    emojiList.addEventListener('click',(e)=>{const t=e.target.closest('[data-emoji]');if(!t)return;addEmoji(String(t.dataset.emoji||''));toggleEmoji(false)});
    document.addEventListener('click',(e)=>{if(!st.emojiOpen)return;if(emojiPicker.contains(e.target)||emojiToggle.contains(e.target))return;toggleEmoji(false)});
    document.addEventListener('keydown',(e)=>{if(e.key==='Escape')toggleEmoji(false)});
    renderEmojis();renderInitial();renderPins(initialPinned);updateFile();updateRecBtn();updateVoice();clearReply();refreshAction();setInterval(poll,POLL);
})();
</script>
@endsection
