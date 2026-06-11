@extends('layouts.app')

@section('content')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=DM+Mono:wght@400;500&display=swap');

    :root {
        --blue-primary: #1a56db;
        --blue-dark: #1e3a8a;
        --blue-deeper: #0f2460;
        --blue-mid: #2563eb;
        --blue-light: #3b82f6;
        --accent-cyan: #06b6d4;
        --accent-green: #10b981;
        --accent-red: #ef4444;
        --accent-amber: #f59e0b;
        --surface-bg: #f0f4fd;
        --surface-card: #ffffff;
        --text-primary: #0f172a;
        --text-secondary: #475569;
        --text-muted: #94a3b8;
        --border-light: rgba(37, 99, 235, 0.10);
        --border-table: #e2e8f0;
        --shadow-sm: 0 1px 3px rgba(15,23,42,0.07);
        --shadow-md: 0 4px 16px rgba(15,23,42,0.09), 0 2px 8px rgba(37,99,235,0.07);
        --shadow-lg: 0 10px 40px rgba(15,23,42,0.13), 0 4px 16px rgba(37,99,235,0.10);
        --radius-sm: 10px;
        --radius-md: 14px;
        --radius-lg: 20px;
    }

    body, .content-wrapper { background: var(--surface-bg) !important; font-family: 'Plus Jakarta Sans', sans-serif !important; }

    /* ── Page Header ──────────────────────────── */
    .sfr-page-header {
        display: flex; align-items: center; justify-content: space-between;
        margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;
        animation: fadeDown 0.45s ease both;
    }
    .sfr-header-left { display: flex; align-items: center; gap: 0.9rem; }
    .sfr-header-icon {
        width: 48px; height: 48px; border-radius: var(--radius-sm);
        background: linear-gradient(135deg, var(--blue-primary), var(--blue-light));
        display: flex; align-items: center; justify-content: center;
        color: white; font-size: 1.25rem; box-shadow: var(--shadow-md); flex-shrink: 0;
    }
    .sfr-header-title { font-size: 1.35rem; font-weight: 800; color: var(--text-primary); margin: 0; letter-spacing: -0.02em; line-height: 1.2; }
    .sfr-header-sub { font-size: 0.8rem; color: var(--text-muted); margin: 0.1rem 0 0; font-weight: 500; }

    /* ── Action Button ────────────────────────── */
    .btn-sfr-action {
        display: inline-flex; align-items: center; gap: 0.4rem;
        background: linear-gradient(135deg, var(--accent-green), #059669);
        color: white; font-size: 0.82rem; font-weight: 700;
        padding: 0.55rem 1.1rem; border-radius: var(--radius-sm);
        text-decoration: none; transition: all 0.25s;
        box-shadow: 0 3px 10px rgba(16,185,129,0.35);
    }
    .btn-sfr-action:hover { transform: translateY(-1px); box-shadow: 0 6px 20px rgba(16,185,129,0.4); color: white; text-decoration: none; }

    /* ── Filter Card ──────────────────────────── */
    .sfr-filter-card {
        background: white; border-radius: var(--radius-lg);
        box-shadow: var(--shadow-md); border: 1px solid var(--border-light);
        overflow: hidden; margin-bottom: 1.25rem;
        animation: fadeUp 0.5s ease both;
    }
    .sfr-filter-header {
        display: flex; align-items: center; justify-content: space-between;
        padding: 1rem 1.25rem; border-bottom: 1px solid var(--border-light);
        background: linear-gradient(135deg, var(--blue-deeper), var(--blue-dark));
    }
    .sfr-filter-header-title {
        display: flex; align-items: center; gap: 0.6rem;
        font-size: 0.9rem; font-weight: 700; color: white; margin: 0;
    }
    .sfr-filter-header-title .fh-icon {
        width: 28px; height: 28px; border-radius: 8px;
        background: rgba(255,255,255,0.15); display: flex; align-items: center;
        justify-content: center; font-size: 0.75rem; color: white;
    }
    .sfr-filter-body { padding: 1.25rem 1.25rem 0.5rem; }

    /* ── Form Controls ────────────────────────── */
    .sfr-form-group { margin-bottom: 1rem; }
    .sfr-label {
        display: flex; align-items: center; gap: 0.3rem;
        font-size: 0.72rem; font-weight: 700; text-transform: uppercase;
        letter-spacing: 0.07em; color: var(--text-muted); margin-bottom: 0.4rem;
    }
    .sfr-label i { font-size: 0.65rem; color: var(--blue-primary); }
    .sfr-control {
        width: 100%; border: 1.5px solid var(--border-table); border-radius: var(--radius-sm);
        padding: 0.5rem 0.75rem; font-size: 0.83rem; font-family: 'Plus Jakarta Sans', sans-serif;
        color: var(--text-primary); background: white; transition: all 0.2s;
        appearance: none; -webkit-appearance: none;
    }
    .sfr-control:focus {
        outline: none; border-color: var(--blue-primary);
        box-shadow: 0 0 0 3px rgba(37,99,235,0.12);
    }
    select.sfr-control {
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%2394a3b8' stroke-width='2.5'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
        background-repeat: no-repeat; background-position: right 0.75rem center;
        padding-right: 2rem;
    }
    .sfr-control:disabled { background: #f8fafc; color: var(--text-muted); cursor: not-allowed; }

    /* ── Filter Buttons ───────────────────────── */
    .sfr-filter-actions { display: flex; align-items: flex-end; gap: 0.6rem; padding-bottom: 1rem; }
    .btn-apply {
        display: inline-flex; align-items: center; gap: 0.4rem;
        background: linear-gradient(135deg, var(--blue-primary), var(--blue-mid));
        color: white; font-size: 0.82rem; font-weight: 700;
        padding: 0.55rem 1.2rem; border-radius: var(--radius-sm);
        border: none; cursor: pointer; transition: all 0.2s;
        box-shadow: 0 3px 10px rgba(37,99,235,0.35); font-family: inherit;
    }
    .btn-apply:hover { transform: translateY(-1px); box-shadow: 0 6px 18px rgba(37,99,235,0.4); }
    .btn-reset {
        display: inline-flex; align-items: center; gap: 0.4rem;
        background: white; border: 1.5px solid var(--border-table);
        color: var(--text-secondary); font-size: 0.82rem; font-weight: 600;
        padding: 0.5rem 1rem; border-radius: var(--radius-sm);
        text-decoration: none; transition: all 0.2s;
    }
    .btn-reset:hover { border-color: var(--blue-light); color: var(--text-primary); text-decoration: none; }

    /* ── Summary Cards ────────────────────────── */
    .sfr-summary-grid {
        display: grid; grid-template-columns: repeat(4, 1fr);
        gap: 0.9rem; margin-bottom: 1.25rem;
    }
    @media(max-width: 768px) { .sfr-summary-grid { grid-template-columns: repeat(2, 1fr); } }
    @media(max-width: 480px) { .sfr-summary-grid { grid-template-columns: 1fr; } }
    .sfr-summary-card {
        background: white; border-radius: var(--radius-md);
        border: 1px solid var(--border-light); padding: 1rem 1.1rem;
        box-shadow: var(--shadow-sm); transition: box-shadow 0.2s;
        animation: fadeUp 0.55s ease both; position: relative; overflow: hidden;
    }
    .sfr-summary-card:hover { box-shadow: var(--shadow-md); }
    .sfr-summary-card::before {
        content: ''; position: absolute; top: 0; left: 0; right: 0;
        height: 3px; border-radius: var(--radius-md) var(--radius-md) 0 0;
    }
    .sfr-summary-card.sc-count::before   { background: linear-gradient(90deg, var(--blue-primary), var(--accent-cyan)); }
    .sfr-summary-card.sc-opening::before { background: linear-gradient(90deg, var(--accent-amber), #fbbf24); }
    .sfr-summary-card.sc-ending::before  { background: linear-gradient(90deg, var(--blue-mid), var(--blue-light)); }
    .sfr-summary-card.sc-surplus::before { background: linear-gradient(90deg, var(--accent-green), #34d399); }
    .sc-label {
        font-size: 0.68rem; font-weight: 700; text-transform: uppercase;
        letter-spacing: 0.08em; color: var(--text-muted); margin-bottom: 0.35rem;
        display: flex; align-items: center; gap: 0.35rem;
    }
    .sc-icon {
        width: 20px; height: 20px; border-radius: 6px;
        display: flex; align-items: center; justify-content: center; font-size: 0.6rem;
    }
    .sc-icon-count   { background: rgba(37,99,235,0.1); color: var(--blue-primary); }
    .sc-icon-opening { background: rgba(245,158,11,0.1); color: var(--accent-amber); }
    .sc-icon-ending  { background: rgba(37,99,235,0.1); color: var(--blue-primary); }
    .sc-icon-surplus { background: rgba(16,185,129,0.1); color: var(--accent-green); }
    .sc-value {
        font-size: 1.25rem; font-weight: 400; color: var(--text-primary);
        font-family: 'Plus Jakarta Sans', sans-serif; line-height: 1.1; letter-spacing: -0.01em;
    }
    .sc-value.blue   { color: var(--blue-primary); }
    .sc-value.green  { color: var(--accent-green); }
    .sc-value.red    { color: var(--accent-red); }
    .sc-value.big    { font-size: 1.6rem; }

    .sfr-live-card {
        background: white; border-radius: var(--radius-lg);
        border: 1px solid var(--border-light); box-shadow: var(--shadow-md);
        overflow: hidden; margin-bottom: 1.25rem; animation: fadeUp 0.56s ease both;
    }
    .sfr-live-header {
        display: flex; align-items: center; justify-content: space-between;
        gap: 1rem; padding: 1rem 1.25rem; border-bottom: 1px solid var(--border-light);
        background: linear-gradient(135deg, rgba(26,86,219,0.08), rgba(6,182,212,0.08));
        flex-wrap: wrap;
    }
    .sfr-live-title {
        display: flex; align-items: center; gap: .6rem;
        margin: 0; color: var(--text-primary); font-size: .94rem; font-weight: 800;
    }
    .sfr-live-title span {
        width: 30px; height: 30px; border-radius: 9px;
        display: inline-flex; align-items: center; justify-content: center;
        background: rgba(37,99,235,.12); color: var(--blue-primary); font-size: .75rem;
    }
    .sfr-live-meta {
        display: flex; align-items: center; gap: .55rem; flex-wrap: wrap;
        color: var(--text-muted); font-size: .76rem; font-weight: 700;
    }
    .sfr-live-pill {
        display: inline-flex; align-items: center; gap: .35rem;
        padding: .28rem .62rem; border-radius: 999px;
        background: rgba(15,23,42,.05); color: var(--text-secondary);
    }
    .sfr-live-body { padding: 1.15rem 1.25rem 1.25rem; }
    .sfr-live-grid {
        display: grid; grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: .8rem; margin-bottom: 1rem;
    }
    @media(max-width: 991px) { .sfr-live-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
    @media(max-width: 520px) { .sfr-live-grid { grid-template-columns: 1fr; } }
    .sfr-live-metric {
        border: 1px solid var(--border-light); border-radius: var(--radius-md);
        background: #fbfdff; padding: .85rem .9rem; min-height: 92px;
    }
    .sfr-live-metric .metric-label {
        display: flex; align-items: center; gap: .35rem;
        color: var(--text-muted); font-size: .66rem; font-weight: 800;
        text-transform: uppercase; letter-spacing: .07em; margin-bottom: .38rem;
    }
    .sfr-live-metric .metric-value {
        color: var(--text-primary); font-size: 1rem; font-weight: 700;
        line-height: 1.25; word-break: break-word;
    }
    .sfr-live-metric .metric-value.big { font-size: 1.2rem; }
    .sfr-live-metric .metric-value.green { color: var(--accent-green); }
    .sfr-live-metric .metric-value.red { color: var(--accent-red); }
    .sfr-live-metric .metric-note { color: var(--text-muted); font-size: .72rem; font-weight: 600; margin-top: .25rem; }
    .sfr-live-panels {
        display: grid; grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: .85rem; margin-bottom: 1rem;
    }
    @media(max-width: 991px) { .sfr-live-panels { grid-template-columns: 1fr; } }
    .sfr-live-panel {
        border: 1px solid var(--border-light); border-radius: var(--radius-md);
        background: white; overflow: hidden;
    }
    .sfr-live-panel-title {
        display: flex; align-items: center; gap: .45rem;
        padding: .75rem .85rem; border-bottom: 1px solid var(--border-light);
        font-size: .78rem; font-weight: 800; color: var(--text-primary);
        background: #f8fafc;
    }
    .sfr-live-row {
        display: flex; align-items: center; justify-content: space-between; gap: .75rem;
        padding: .58rem .85rem; border-bottom: 1px solid rgba(226,232,240,.75);
        font-size: .78rem;
    }
    .sfr-live-row:last-child { border-bottom: none; }
    .sfr-live-row span:first-child { color: var(--text-secondary); font-weight: 600; }
    .sfr-live-row span:last-child { color: var(--text-primary); font-weight: 800; text-align: right; }
    .sfr-live-table-title {
        display: flex; align-items: center; gap: .45rem;
        color: var(--text-primary); font-size: .82rem; font-weight: 800;
        margin: .25rem 0 .7rem;
    }
    .sfr-live-empty {
        display: flex; align-items: center; gap: .55rem;
        padding: .8rem .9rem; border-radius: var(--radius-sm);
        border: 1px solid rgba(245,158,11,.22); background: rgba(245,158,11,.08);
        color: #92400e; font-size: .8rem; font-weight: 700;
    }

    /* ── Table Card ───────────────────────────── */
    .sfr-table-card {
        background: white; border-radius: var(--radius-lg);
        box-shadow: var(--shadow-md); border: 1px solid var(--border-light);
        overflow: hidden; animation: fadeUp 0.6s ease both;
    }
    .sfr-table-header {
        display: flex; align-items: center; justify-content: space-between;
        padding: 1rem 1.25rem; border-bottom: 1px solid var(--border-light);
        background: white;
    }
    .sfr-table-title {
        display: flex; align-items: center; gap: 0.6rem;
        font-size: 0.9rem; font-weight: 700; color: var(--text-primary); margin: 0;
    }
    .sfr-table-title .tt-icon {
        width: 28px; height: 28px; border-radius: 8px;
        background: rgba(37,99,235,0.1); display: flex; align-items: center;
        justify-content: center; font-size: 0.7rem; color: var(--blue-primary);
    }

    /* ── Data Table ───────────────────────────── */
    .sfr-table { width: 100%; border-collapse: collapse; font-size: 0.82rem; }
    .sfr-table th {
        background: #f8fafc; color: var(--text-muted);
        font-size: 0.68rem; font-weight: 700; text-transform: uppercase;
        letter-spacing: 0.07em; padding: 0.7rem 1rem;
        border-bottom: 2px solid var(--border-table); white-space: nowrap;
    }
    .sfr-table td {
        padding: 0.7rem 1rem; border-bottom: 1px solid var(--border-table);
        color: var(--text-secondary); vertical-align: middle;
    }
    .sfr-table tbody tr:last-child td { border-bottom: none; }
    .sfr-table tbody tr:hover td { background: rgba(37,99,235,0.025); }

    /* ── Badges ───────────────────────────────── */
    .badge-type {
        display: inline-flex; align-items: center; gap: 0.3rem;
        border-radius: 999px; padding: 0.22rem 0.65rem;
        font-size: 0.68rem; font-weight: 700; letter-spacing: 0.05em;
        white-space: nowrap;
    }
    .badge-type.daily    { background: rgba(245,158,11,0.1);  color: #92400e; }
    .badge-type.monthly  { background: rgba(37,99,235,0.1);   color: var(--blue-dark); }
    .badge-type.yearly   { background: rgba(139,92,246,0.1);  color: #5b21b6; }
    .badge-type.all      { background: rgba(16,185,129,0.1);  color: #065f46; }
    .badge-version {
        display: inline-flex; align-items: center; gap: 0.2rem;
        background: #f1f5f9; color: var(--text-secondary);
        font-size: 0.7rem; font-weight: 600;
        font-family: 'Plus Jakarta Sans', sans-serif;
        padding: 0.2rem 0.55rem; border-radius: 999px;
    }
    .badge-version i { font-size: 0.55rem; color: var(--blue-primary); }

    /* ── Amount cells ─────────────────────────── */
    .amount-cell {
        font-family: 'Plus Jakarta Sans', sans-serif;
        font-size: 0.82rem; font-weight: 400; white-space: nowrap;
        color: var(--text-secondary);
    }
    .amount-cell.positive { color: var(--accent-green); }
    .amount-cell.negative { color: var(--accent-red); }

    /* ── Preview Button ───────────────────────── */
    .btn-preview {
        display: inline-flex; align-items: center; gap: 0.3rem;
        background: rgba(37,99,235,0.08); color: var(--blue-primary);
        border: 1px solid rgba(37,99,235,0.2); border-radius: 8px;
        font-size: 0.75rem; font-weight: 700; padding: 0.35rem 0.75rem;
        text-decoration: none; transition: all 0.2s; white-space: nowrap;
    }
    .btn-preview:hover { background: var(--blue-primary); color: white; text-decoration: none; }
    .sfr-actions {
        display: flex; align-items: center; gap: 0.35rem; flex-wrap: wrap;
    }
    .btn-action {
        display: inline-flex; align-items: center; gap: 0.3rem;
        border: 1px solid transparent; border-radius: 8px;
        font-size: 0.75rem; font-weight: 700; padding: 0.35rem 0.75rem;
        text-decoration: none; transition: all 0.2s; white-space: nowrap;
        background: white;
        font-family: 'Plus Jakarta Sans', sans-serif;
    }
    .btn-edit {
        color: var(--accent-amber);
        border-color: rgba(245,158,11,0.28);
        background: rgba(245,158,11,0.08);
    }
    .btn-edit:hover { background: var(--accent-amber); color: white; text-decoration: none; }
    .btn-delete {
        color: var(--accent-red);
        border-color: rgba(239,68,68,0.28);
        background: rgba(239,68,68,0.08);
        cursor: pointer;
    }
    .btn-delete:hover { background: var(--accent-red); color: white; }
    .sfr-actions form { margin: 0; }

    /* ── Comparison Cell ──────────────────────── */
    .comp-label { font-size: 0.72rem; font-weight: 700; color: var(--text-primary); margin-bottom: 0.15rem; }
    .comp-row { font-size: 0.72rem; font-weight: 600; display: flex; align-items: center; gap: 0.25rem; }
    .comp-row.up   { color: var(--accent-green); }
    .comp-row.down { color: var(--accent-red); }
    .comp-none { font-size: 0.75rem; color: var(--text-muted); font-style: italic; }

    /* ── Empty State ──────────────────────────── */
    .sfr-empty-state {
        padding: 3rem 1rem; text-align: center;
    }
    .sfr-empty-icon {
        width: 56px; height: 56px; border-radius: var(--radius-md);
        background: rgba(37,99,235,0.07); display: flex; align-items: center;
        justify-content: center; font-size: 1.4rem; color: var(--text-muted);
        margin: 0 auto 1rem;
    }
    .sfr-empty-text { font-size: 0.85rem; color: var(--text-muted); font-weight: 500; }

    /* ── Table Footer / Pagination ────────────── */
    .sfr-table-footer {
        padding: 0.75rem 1.25rem; border-top: 1px solid var(--border-light);
        background: #fafbff;
    }
    .sfr-table-footer .pagination { margin: 0; }
    .sfr-table-footer .page-link {
        border-radius: 8px; font-size: 0.78rem; font-weight: 600;
        color: var(--text-secondary); border-color: var(--border-table);
        margin: 0 1px;
    }
    .sfr-table-footer .page-item.active .page-link {
        background: var(--blue-primary); border-color: var(--blue-primary); color: white;
    }

    /* ── Alert ────────────────────────────────── */
    .sfr-alert-empty {
        display: flex; align-items: center; gap: 0.7rem;
        background: rgba(245,158,11,0.08); border: 1px solid rgba(245,158,11,0.2);
        border-radius: var(--radius-md); padding: 0.9rem 1.1rem;
        font-size: 0.83rem; font-weight: 500; color: #92400e; margin: 1.25rem;
    }

    /* ── Animations ───────────────────────────── */
    @keyframes fadeUp   { from { opacity:0; transform:translateY(14px); } to { opacity:1; transform:translateY(0); } }
    @keyframes fadeDown { from { opacity:0; transform:translateY(-10px); } to { opacity:1; transform:translateY(0); } }
    .anim-d1 { animation-delay: 0.05s; }
    .anim-d2 { animation-delay: 0.10s; }
    .anim-d3 { animation-delay: 0.15s; }
    .anim-d4 { animation-delay: 0.20s; }
    body.dark-mode .sfr-page-header,
    body.dark-mode .sfr-filter-card,
    body.dark-mode .sfr-live-card,
    body.dark-mode .sfr-table-card {
        color: var(--app-text) !important;
    }

    body.dark-mode .sfr-filter-card,
    body.dark-mode .sfr-live-card,
    body.dark-mode .sfr-live-panel,
    body.dark-mode .sfr-table-card {
        background: var(--app-surface) !important;
        border-color: var(--app-border) !important;
        box-shadow: var(--app-shadow) !important;
    }

    body.dark-mode .sfr-filter-header,
    body.dark-mode .sfr-live-header,
    body.dark-mode .sfr-live-panel-title,
    body.dark-mode .sfr-table-header,
    body.dark-mode .sfr-table-footer {
        background: var(--app-surface-soft) !important;
        border-color: var(--app-border) !important;
    }

    body.dark-mode .sfr-summary-card,
    body.dark-mode .sfr-live-metric,
    body.dark-mode .sfr-alert-empty {
        background: var(--app-surface-soft) !important;
        border-color: var(--app-border) !important;
        box-shadow: none !important;
    }

    body.dark-mode .sfr-header-title,
    body.dark-mode .sfr-live-title,
    body.dark-mode .sfr-live-table-title,
    body.dark-mode .sfr-table-title,
    body.dark-mode .sc-value,
    body.dark-mode .sfr-live-metric .metric-value,
    body.dark-mode .sfr-live-row span:last-child,
    body.dark-mode .comp-label {
        color: var(--app-text) !important;
    }

    body.dark-mode .sfr-header-sub,
    body.dark-mode .sfr-live-meta,
    body.dark-mode .sfr-live-metric .metric-label,
    body.dark-mode .sfr-live-metric .metric-note,
    body.dark-mode .sfr-live-row span:first-child,
    body.dark-mode .sfr-label,
    body.dark-mode .amount-cell,
    body.dark-mode .comp-none,
    body.dark-mode .sfr-empty-text {
        color: var(--app-text-muted) !important;
    }

    body.dark-mode .sfr-live-pill {
        background: rgba(148, 163, 184, 0.14) !important;
        color: var(--app-text-soft) !important;
    }

    body.dark-mode .sfr-live-row {
        border-color: var(--app-border) !important;
    }

    body.dark-mode .sfr-control {
        background: var(--app-surface-soft) !important;
        border-color: var(--app-border) !important;
        color: var(--app-text) !important;
    }

    body.dark-mode .sfr-control:focus {
        background: var(--app-surface) !important;
        border-color: rgba(96, 165, 250, 0.36) !important;
        box-shadow: 0 0 0 3px rgba(96, 165, 250, 0.14) !important;
    }

    body.dark-mode .sfr-control option {
        background: var(--app-surface) !important;
        color: var(--app-text) !important;
    }

    body.dark-mode .sfr-table th {
        background: var(--app-surface-soft) !important;
        border-color: var(--app-border) !important;
        color: var(--app-text-muted) !important;
    }

    body.dark-mode .sfr-table td {
        background: transparent !important;
        border-color: var(--app-border) !important;
        color: var(--app-text-soft) !important;
    }

    body.dark-mode .sfr-table tbody tr:hover td {
        background: var(--app-row-hover) !important;
    }

    body.dark-mode .btn-reset,
    body.dark-mode .btn-action {
        background: var(--app-surface-soft) !important;
        border-color: var(--app-border) !important;
        color: var(--app-text) !important;
        box-shadow: none !important;
    }

    body.dark-mode .badge-version {
        background: rgba(148, 163, 184, 0.14) !important;
        color: var(--app-text-soft) !important;
    }
</style>

@php
    $periodType = strtoupper((string) ($filters['period_type'] ?? 'MONTHLY'));
    $reportDate = (string) ($filters['report_date'] ?? now()->toDateString());
    $month = (int) ($filters['month'] ?? now()->month);
    $year = (int) ($filters['year'] ?? now()->year);
    $comparisonType = strtoupper((string) ($filters['comparison_type'] ?? 'NONE'));
    $comparisonOffset = (int) ($filters['comparison_offset'] ?? 1);
    $comparisonDate = (string) ($filters['comparison_date'] ?? now()->toDateString());
    $selectedCategoryId = $filters['category_id'] ?? null;
    $financeCategoryOptions = $financeCategoryOptions ?? collect();
    $selectedCategoryName = optional($financeCategoryOptions->firstWhere('id', $selectedCategoryId))->name;
    $perPage = (int) request('per_page', 20);
    $totalEndingBalance = (float) data_get($totals ?? [], 'total_ending_balance', 0);
    $totalOpeningBalance = (float) data_get($totals ?? [], 'total_opening_balance', 0);
    $totalNetResult = (float) data_get($totals ?? [], 'total_net_result', 0);
    $totalCount = (int) data_get($totals ?? [], 'count', 0);
    $statementReportDate = $periodType === 'DAILY' ? $reportDate : null;
    $statementMonth = $periodType === 'MONTHLY' ? $month : null;
    $statementYear = in_array($periodType, ['MONTHLY', 'YEARLY'], true) ? $year : null;
    $statementRouteParams = array_filter([
        'period_type' => $periodType,
        'report_date' => $statementReportDate,
        'month' => $statementMonth,
        'year' => $statementYear,
        'category_id' => $selectedCategoryId,
    ], static fn ($value) => $value !== null && $value !== '');
    $actualJournalOverview = data_get($actualSummary ?? [], 'journal_overview', []);
    $actualBalanceSummary = data_get($actualSummary ?? [], 'balance_sheet.summary', []);
    $actualProfitLossTotals = data_get($actualSummary ?? [], 'profit_loss.totals', []);
    $actualLedgerSummary = data_get($actualSummary ?? [], 'general_ledger', []);
    $actualLatestPostedAt = data_get($actualJournalOverview, 'latest_posted_at')
        ? \Carbon\Carbon::parse((string) data_get($actualJournalOverview, 'latest_posted_at'))->timezone(config('app.timezone'))->format('d/m/Y H:i:s')
        : '-';
    $actualPostedCount = (int) data_get($actualJournalOverview, 'posted_invoice_count', 0);
    $actualPostedNominal = (float) data_get($actualJournalOverview, 'total_posted_nominal', 0);
    $actualIncome = (float) data_get($actualProfitLossTotals, 'income', 0);
    $actualExpense = (float) data_get($actualProfitLossTotals, 'expense', 0);
    $actualNetResult = (float) data_get($actualProfitLossTotals, 'net_result', 0);
    $actualInvoiceRows = $actualInvoices ?? collect();
@endphp

{{-- ── Page Header ──────────────────────────────────── --}}
<div class="sfr-page-header">
    <div class="sfr-header-left">
        <div class="sfr-header-icon"><i class="fas fa-chart-pie"></i></div>
        <div>
            <h1 class="sfr-header-title">{{ __('app.finance.snapshot_finance_report') }}</h1>
            <p class="sfr-header-sub">{{ __('app.finance.monitoring_snapshot_report') }}</p>
        </div>
    </div>
    <div style="display:flex; gap:.55rem; flex-wrap:wrap;">
        <a href="{{ route('finance.report.index') }}" class="btn-sfr-action">
            <i class="fas fa-plus"></i> {{ __('app.finance.input_report') }}
        </a>
        @permission('finance_balance_sheet.read')
            <a href="{{ route('finance.report.balance-sheet', $statementRouteParams) }}" class="btn-reset">
                <i class="fas fa-balance-scale"></i> {{ __('app.finance.balance_sheet') }}
            </a>
        @endpermission
        @permission('finance_profit_loss.read')
            <a href="{{ route('finance.report.profit-loss', $statementRouteParams) }}" class="btn-reset">
                <i class="fas fa-chart-area"></i> {{ __('app.finance.profit_loss') }}
            </a>
        @endpermission
        @permission('finance_general_ledger.read')
            <a href="{{ route('finance.report.general-ledger', $statementRouteParams) }}" class="btn-reset">
                <i class="fas fa-book-open"></i> {{ __('app.finance.general_ledger') }}
            </a>
        @endpermission
    </div>
</div>

{{-- ── Filter Card ──────────────────────────────────── --}}
<div class="sfr-filter-card">
    <div class="sfr-filter-header">
        <h3 class="sfr-filter-header-title">
            <span class="fh-icon"><i class="fas fa-filter"></i></span>
            {{ __('app.finance.snapshot_filter') }}
        </h3>
    </div>
    <div class="sfr-filter-body">
        <form method="GET" action="{{ route('finance.report.snapshots') }}" id="snapshot-filter-form">
            <div class="row">
                <div class="col-md-2 sfr-form-group" id="period_type_col">
                    <label class="sfr-label"><i class="fas fa-calendar-alt"></i> {{ __('app.finance.period') }}</label>
                    <select name="period_type" id="period_type" class="sfr-control">
                        <option value="ALL"     {{ $periodType === 'ALL'     ? 'selected' : '' }}>{{ __('app.finance.all_periods') }}</option>
                        <option value="DAILY"   {{ $periodType === 'DAILY'   ? 'selected' : '' }}>{{ __('app.finance.daily') }}</option>
                        <option value="MONTHLY" {{ $periodType === 'MONTHLY' ? 'selected' : '' }}>{{ __('app.finance.monthly') }}</option>
                        <option value="YEARLY"  {{ $periodType === 'YEARLY'  ? 'selected' : '' }}>{{ __('app.finance.yearly') }}</option>
                    </select>
                </div>

                <div class="col-md-3 sfr-form-group" id="report_date_group">
                    <label class="sfr-label"><i class="fas fa-calendar-day"></i> {{ __('app.finance.as_of_date') }}</label>
                    <input type="date" name="report_date" id="report_date" class="sfr-control" value="{{ $reportDate }}">
                </div>

                <div class="col-md-2 sfr-form-group" id="month_group">
                    <label class="sfr-label"><i class="fas fa-calendar-week"></i> {{ __('app.finance.month') }}</label>
                    <select name="month" id="month" class="sfr-control">
                        @for($m = 1; $m <= 12; $m++)
                            <option value="{{ $m }}" {{ $month === $m ? 'selected' : '' }}>{{ $m }}</option>
                        @endfor
                    </select>
                </div>

                <div class="col-md-2 sfr-form-group" id="year_group">
                    <label class="sfr-label"><i class="fas fa-calendar"></i> {{ __('app.finance.year') }}</label>
                    <input type="number" name="year" id="year" class="sfr-control" min="1900" max="2100" value="{{ $year }}">
                </div>

                <div class="col-md-3 sfr-form-group">
                    <label class="sfr-label"><i class="fas fa-tags"></i> Kategori Finance</label>
                    <select name="category_id" id="category_id" class="sfr-control">
                        <option value="">Semua kategori</option>
                        @foreach($financeCategoryOptions as $category)
                            <option value="{{ $category->id }}" {{ (string) $selectedCategoryId === (string) $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3 sfr-form-group">
                    <label class="sfr-label"><i class="fas fa-code-branch"></i> {{ __('app.finance.comparison') }}</label>
                    <select name="comparison_type" id="comparison_type" class="sfr-control">
                        <option value="NONE"                  {{ $comparisonType === 'NONE'                  ? 'selected' : '' }}>{{ __('app.finance.none') }}</option>
                        <option value="PREVIOUS_PERIOD"       {{ $comparisonType === 'PREVIOUS_PERIOD'       ? 'selected' : '' }}>{{ __('app.finance.previous_period') }}</option>
                        <option value="SAME_PERIOD_LAST_YEAR" {{ $comparisonType === 'SAME_PERIOD_LAST_YEAR' ? 'selected' : '' }}>{{ __('app.finance.same_period_last_year') }}</option>
                        <option value="SPECIFIC_DATE"         {{ $comparisonType === 'SPECIFIC_DATE'         ? 'selected' : '' }}>{{ __('app.finance.specific_date') }}</option>
                    </select>
                </div>
            </div>

            <div class="row">
                <div class="col-md-2 sfr-form-group" id="comparison_offset_group">
                    <label class="sfr-label"><i class="fas fa-arrows-alt-h"></i> {{ __('app.finance.period_distance') }}</label>
                    <input type="number" name="comparison_offset" id="comparison_offset" class="sfr-control" min="1" max="36" value="{{ max(1, $comparisonOffset) }}">
                </div>

                <div class="col-md-3 sfr-form-group" id="comparison_date_group">
                    <label class="sfr-label"><i class="fas fa-calendar-check"></i> {{ __('app.finance.comparison_date') }}</label>
                    <input type="date" name="comparison_date" id="comparison_date" class="sfr-control" value="{{ $comparisonDate }}">
                </div>

                <div class="col-md-2 sfr-form-group">
                    <label class="sfr-label"><i class="fas fa-list-ol"></i> {{ __('app.finance.per_page') }}</label>
                    <select name="per_page" id="per_page" class="sfr-control">
                        @foreach([10, 20, 50, 100] as $size)
                            <option value="{{ $size }}" {{ $perPage === $size ? 'selected' : '' }}>{{ $size }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-5 sfr-filter-actions">
                    <button type="submit" class="btn-apply">
                        <i class="fas fa-search"></i> {{ __('app.finance.apply_filter') }}
                    </button>
                    <a href="{{ route('finance.report.snapshots', ['period_type' => 'MONTHLY', 'month' => now()->month, 'year' => now()->year]) }}" class="btn-reset">
                        <i class="fas fa-redo"></i> {{ __('app.finance.reset') }}
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- ── Summary Cards ────────────────────────────────── --}}
<div class="sfr-live-card">
    <div class="sfr-live-header">
        <h3 class="sfr-live-title">
            <span><i class="fas fa-bolt"></i></span>
            Cuplikan Aktual Jurnal Terposting
        </h3>
        <div class="sfr-live-meta">
            <span class="sfr-live-pill"><i class="fas fa-calendar-check"></i> Periode: {{ $actualPeriodLabel ?? '-' }}</span>
            <span class="sfr-live-pill"><i class="fas fa-tags"></i> Kategori: {{ $selectedCategoryName ?: 'Semua' }}</span>
            <span class="sfr-live-pill"><i class="fas fa-clock"></i> Diperbarui WIB: {{ $actualLatestPostedAt }}</span>
        </div>
    </div>
    <div class="sfr-live-body">
        <div class="sfr-live-grid">
            <div class="sfr-live-metric">
                <div class="metric-label"><i class="fas fa-file-invoice-dollar"></i> Total Nominal Terposting</div>
                <div class="metric-value big">Rp {{ number_format($actualPostedNominal, 2, ',', '.') }}</div>
                <div class="metric-note">Sumber: finance_invoices.total_debit</div>
            </div>
            <div class="sfr-live-metric">
                <div class="metric-label"><i class="fas fa-layer-group"></i> Faktur/Jurnal Terposting</div>
                <div class="metric-value big">{{ number_format($actualPostedCount, 0, ',', '.') }}</div>
                <div class="metric-note">Status POSTED sesuai filter periode</div>
            </div>
            <div class="sfr-live-metric">
                <div class="metric-label"><i class="fas fa-chart-line"></i> Surplus (Defisit)</div>
                <div class="metric-value {{ $actualNetResult >= 0 ? 'green' : 'red' }}">Rp {{ number_format($actualNetResult, 2, ',', '.') }}</div>
                <div class="metric-note">Pemasukan dikurangi pengeluaran</div>
            </div>
            <div class="sfr-live-metric">
                <div class="metric-label"><i class="fas fa-book"></i> Total Buku Besar</div>
                <div class="metric-value">Rp {{ number_format((float) data_get($actualLedgerSummary, 'total_debit', 0), 2, ',', '.') }}</div>
                <div class="metric-note">{{ number_format((int) data_get($actualLedgerSummary, 'entry_count', 0), 0, ',', '.') }} baris jurnal</div>
            </div>
        </div>

        <div class="sfr-live-panels">
            <div class="sfr-live-panel">
                <div class="sfr-live-panel-title"><i class="fas fa-balance-scale"></i> Lembar Saldo</div>
                <div class="sfr-live-row"><span>Kas</span><span>Rp {{ number_format((float) data_get($actualBalanceSummary, 'kas_total', 0), 2, ',', '.') }}</span></div>
                <div class="sfr-live-row"><span>Piutang</span><span>Rp {{ number_format((float) data_get($actualBalanceSummary, 'piutang_total', 0), 2, ',', '.') }}</span></div>
                <div class="sfr-live-row"><span>Aset</span><span>Rp {{ number_format((float) data_get($actualBalanceSummary, 'aset_total', 0), 2, ',', '.') }}</span></div>
                <div class="sfr-live-row"><span>Liabilitas</span><span>Rp {{ number_format((float) data_get($actualBalanceSummary, 'liabilitas_total', 0), 2, ',', '.') }}</span></div>
            </div>
            <div class="sfr-live-panel">
                <div class="sfr-live-panel-title"><i class="fas fa-chart-area"></i> Laba Rugi</div>
                <div class="sfr-live-row"><span>Total Pemasukan</span><span>Rp {{ number_format($actualIncome, 2, ',', '.') }}</span></div>
                <div class="sfr-live-row"><span>Total Pengeluaran</span><span>Rp {{ number_format($actualExpense, 2, ',', '.') }}</span></div>
                <div class="sfr-live-row"><span>Surplus (Defisit)</span><span>Rp {{ number_format($actualNetResult, 2, ',', '.') }}</span></div>
                <div class="sfr-live-row"><span>Akun Laba Rugi</span><span>{{ number_format((int) data_get($actualSummary ?? [], 'profit_loss.income_count', 0) + (int) data_get($actualSummary ?? [], 'profit_loss.expense_count', 0), 0, ',', '.') }}</span></div>
            </div>
            <div class="sfr-live-panel">
                <div class="sfr-live-panel-title"><i class="fas fa-book-open"></i> Buku Besar</div>
                <div class="sfr-live-row"><span>Jumlah Akun</span><span>{{ number_format((int) data_get($actualLedgerSummary, 'account_count', 0), 0, ',', '.') }}</span></div>
                <div class="sfr-live-row"><span>Total Debit</span><span>Rp {{ number_format((float) data_get($actualLedgerSummary, 'total_debit', 0), 2, ',', '.') }}</span></div>
                <div class="sfr-live-row"><span>Total Kredit</span><span>Rp {{ number_format((float) data_get($actualLedgerSummary, 'total_credit', 0), 2, ',', '.') }}</span></div>
                <div class="sfr-live-row"><span>Selisih</span><span>Rp {{ number_format((float) data_get($actualLedgerSummary, 'balance_gap', 0), 2, ',', '.') }}</span></div>
            </div>
        </div>

        <div class="sfr-live-table-title">
            <i class="fas fa-receipt"></i> Jurnal Terposting Terbaru
        </div>
        @if($actualInvoiceRows->isNotEmpty())
            <div class="table-responsive">
                <table class="sfr-table">
                    <thead>
                        <tr>
                            <th>No. Jurnal</th>
                            <th>Tanggal Akuntansi</th>
                            <th>Tipe</th>
                            <th>Nama Jurnal</th>
                            <th>Total Debit</th>
                            <th>Total Kredit</th>
                            <th>Diposting</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($actualInvoiceRows as $invoice)
                            <tr>
                                <td><span class="amount-cell">{{ $invoice->invoice_no }}</span></td>
                                <td>{{ optional($invoice->accounting_date)->format('d/m/Y') ?? '-' }}</td>
                                <td>
                                    <span class="badge-type {{ strtoupper((string) $invoice->entry_type) === 'INCOME' ? 'all' : 'monthly' }}">
                                        {{ $invoice->entry_type }}
                                    </span>
                                </td>
                                <td>
                                    <div style="font-weight:700;color:var(--text-primary);">{{ $invoice->journal_name }}</div>
                                    @if(!empty($invoice->reference))
                                        <div style="font-size:.72rem;color:var(--text-muted);">{{ $invoice->reference }}</div>
                                    @endif
                                </td>
                                <td><span class="amount-cell">Rp {{ number_format((float) $invoice->total_debit, 2, ',', '.') }}</span></td>
                                <td><span class="amount-cell">Rp {{ number_format((float) $invoice->total_credit, 2, ',', '.') }}</span></td>
                                <td>{{ optional($invoice->posted_at)->timezone(config('app.timezone'))->format('d/m/Y H:i:s') ?? '-' }}</td>
                                <td>
                                    <a href="{{ route('finance.invoice.show', $invoice->id) }}" class="btn-preview">
                                        <i class="fas fa-eye"></i> Detail
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="sfr-live-empty">
                <i class="fas fa-info-circle"></i>
                Belum ada jurnal terposting untuk filter periode ini.
            </div>
        @endif
    </div>
</div>

<div class="sfr-summary-grid">
    <div class="sfr-summary-card sc-count anim-d1">
        <div class="sc-label"><span class="sc-icon sc-icon-count"><i class="fas fa-layer-group"></i></span> Snapshot Manual Tersimpan</div>
        <div class="sc-value big">{{ number_format($totalCount, 0, ',', '.') }}</div>
    </div>
    <div class="sfr-summary-card sc-opening anim-d2">
        <div class="sc-label"><span class="sc-icon sc-icon-opening"><i class="fas fa-wallet"></i></span> Saldo Awal Snapshot</div>
        <div class="sc-value" style="font-size:1rem;">Rp {{ number_format($totalOpeningBalance, 2, ',', '.') }}</div>
    </div>
    <div class="sfr-summary-card sc-ending anim-d3">
        <div class="sc-label"><span class="sc-icon sc-icon-ending"><i class="fas fa-wallet"></i></span> Saldo Akhir Snapshot</div>
        <div class="sc-value blue" style="font-size:1rem;">Rp {{ number_format($totalEndingBalance, 2, ',', '.') }}</div>
    </div>
    <div class="sfr-summary-card sc-surplus anim-d4">
        <div class="sc-label"><span class="sc-icon sc-icon-surplus"><i class="fas fa-balance-scale"></i></span> Surplus Snapshot</div>
        <div class="sc-value {{ $totalNetResult >= 0 ? 'green' : 'red' }}" style="font-size:1rem;">Rp {{ number_format($totalNetResult, 2, ',', '.') }}</div>
    </div>
</div>

{{-- ── Table Card ───────────────────────────────────── --}}
<div class="sfr-table-card">
    <div class="sfr-table-header">
        <h3 class="sfr-table-title">
            <span class="tt-icon"><i class="fas fa-table"></i></span>
            {{ __('app.finance.snapshot_report_list') }}
        </h3>
    </div>

    @if($reports->total() === 0)
        <div class="sfr-alert-empty">
            <i class="fas fa-exclamation-triangle"></i>
            Belum ada snapshot manual tersimpan untuk saringan ini. Cuplikan aktual di atas tetap diambil dari jurnal terposting.
        </div>
    @endif

    <div class="table-responsive">
        <table class="sfr-table">
            <thead>
                <tr>
                    <th>{{ __('app.finance.action') }}</th>
                    <th>{{ __('app.finance.period') }}</th>
                    <th>{{ __('app.finance.type') }}</th>
                    <th>{{ __('app.finance.version') }}</th>
                    <th>{{ __('app.finance.opening_balance') }}</th>
                    <th>{{ __('app.finance.ending_balance') }}</th>
                    <th>{{ __('app.finance.total_surplus_deficit') }}</th>
                    <th>{{ __('app.finance.comparison') }}</th>
                    <th>{{ __('app.finance.generated_at') }}</th>
                    <th>{{ __('app.finance.generated_by') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($reports as $report)
                    @php
                        $period = $report->period;
                        $rowPeriodType = strtoupper((string) ($period->period_type ?? $report->report_type));
                        $periodLabel = '-';
                        if ($period) {
                            if ($rowPeriodType === 'DAILY') {
                                $periodLabel = optional($period->start_date)->format('d/m/Y') ?? '-';
                            } elseif ($rowPeriodType === 'MONTHLY') {
                                $periodLabel = sprintf('%02d/%04d', (int) $period->month, (int) $period->year);
                            } else {
                                $periodLabel = (string) $period->year;
                            }
                        }
                        $openingBalance = (float) data_get($report->summary, 'opening_balance', 0);
                        $endingBalance  = (float) data_get($report->summary, 'ending_balance', 0);
                        $netResult      = (float) data_get($report->summary, 'net_result', 0);
                        $comparison     = $comparisons[$report->id] ?? null;

                        $typeBadgeClass = match($rowPeriodType) {
                            'DAILY'   => 'daily',
                            'MONTHLY' => 'monthly',
                            'YEARLY'  => 'yearly',
                            default   => 'all',
                        };
                        $typeIcon = match($rowPeriodType) {
                            'DAILY'   => 'fa-calendar-day',
                            'MONTHLY' => 'fa-calendar-week',
                            'YEARLY'  => 'fa-calendar',
                            default   => 'fa-layer-group',
                        };
                    @endphp
                    <tr>
                        <td>
                            <div class="sfr-actions">
                                <a href="{{ route('finance.report.show', $report->id) }}" class="btn-preview">
                                    <i class="fas fa-eye" style="font-size:.65rem;"></i> {{ __('app.finance.preview') }}
                                </a>
                                @permission('finance_report.generate')
                                    <a href="{{ route('finance.report.edit', $report->id) }}" class="btn-action btn-edit">
                                        <i class="fas fa-pen" style="font-size:.62rem;"></i> {{ __('app.finance.edit') }}
                                    </a>
                                    <form method="POST" action="{{ route('finance.report.destroy', $report->id) }}" onsubmit="return confirm(@json(__('app.finance.delete_snapshot_confirm')))">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-action btn-delete">
                                            <i class="fas fa-trash" style="font-size:.62rem;"></i> {{ __('app.finance.delete') }}
                                        </button>
                                    </form>
                                @endpermission
                            </div>
                        </td>
                        <td>
                            <span style="font-family:'Plus Jakarta Sans',sans-serif;font-size:.82rem;font-weight:500;color:var(--text-primary);">
                                {{ $periodLabel }}
                            </span>
                        </td>
                        <td>
                            <span class="badge-type {{ $typeBadgeClass }}">
                                <i class="fas {{ $typeIcon }}" style="font-size:.6rem;"></i>
                                {{ $rowPeriodType }}
                            </span>
                        </td>
                        <td>
                            <span class="badge-version">
                                <i class="fas fa-tag"></i> {{ $report->version_no }}
                            </span>
                        </td>
                        <td><span class="amount-cell">Rp {{ number_format($openingBalance, 2, ',', '.') }}</span></td>
                        <td><span class="amount-cell" style="color:var(--blue-primary);">Rp {{ number_format($endingBalance, 2, ',', '.') }}</span></td>
                        <td>
                            <span class="amount-cell {{ $netResult >= 0 ? 'positive' : 'negative' }}">
                                {{ $netResult >= 0 ? '' : '-' }}Rp {{ number_format(abs($netResult), 2, ',', '.') }}
                            </span>
                        </td>
                        <td>
                            @if(!$comparison)
                                <span class="comp-none">—</span>
                            @elseif(!data_get($comparison, 'available', false))
                                <div class="comp-label">{{ data_get($comparison, 'label', __('app.finance.comparison_default')) }}</div>
                                <div style="font-size:.72rem;color:var(--text-muted);">{{ data_get($comparison, 'message', __('app.finance.comparison_not_found')) }}</div>
                            @else
                                @php
                                    $diffNet     = (float) data_get($comparison, 'difference_net_result', 0);
                                    $diffBalance = (float) data_get($comparison, 'difference_ending_balance', 0);
                                @endphp
                                <div class="comp-label">{{ data_get($comparison, 'label', __('app.finance.comparison_default')) }}</div>
                                <div class="comp-row {{ $diffNet >= 0 ? 'up' : 'down' }}">
                                    <i class="fas fa-{{ $diffNet >= 0 ? 'arrow-up' : 'arrow-down' }}" style="font-size:.6rem;"></i>
                                    {{ __('app.finance.surplus_label') }}: {{ $diffNet >= 0 ? '+' : '' }}Rp {{ number_format($diffNet, 2, ',', '.') }}
                                </div>
                                <div class="comp-row {{ $diffBalance >= 0 ? 'up' : 'down' }}">
                                    <i class="fas fa-{{ $diffBalance >= 0 ? 'arrow-up' : 'arrow-down' }}" style="font-size:.6rem;"></i>
                                    {{ __('app.finance.balance_label') }}: {{ $diffBalance >= 0 ? '+' : '' }}Rp {{ number_format($diffBalance, 2, ',', '.') }}
                                </div>
                            @endif
                        </td>
                        <td>
                            <span style="font-family:'Plus Jakarta Sans',sans-serif;font-size:.75rem;color:var(--text-muted);">
                                {{ optional($report->generated_at)->format('Y-m-d H:i:s') ?? '-' }}
                            </span>
                        </td>
                        <td>
                            <div style="display:flex;align-items:center;gap:.45rem;">
                                <div style="width:26px;height:26px;border-radius:50%;background:linear-gradient(135deg,var(--blue-primary),var(--blue-light));display:flex;align-items:center;justify-content:center;color:white;font-size:.65rem;font-weight:800;flex-shrink:0;">
                                    {{ strtoupper(substr($report->user?->name ?? '?', 0, 1)) }}
                                </div>
                                <span style="font-size:.8rem;font-weight:600;color:var(--text-primary);">{{ $report->user?->name ?? '-' }}</span>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10">
                            <div class="sfr-empty-state">
                                <div class="sfr-empty-icon"><i class="fas fa-inbox"></i></div>
                                <div class="sfr-empty-text">Tidak ada snapshot manual tersimpan untuk saringan ini.</div>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="sfr-table-footer">
        {{ $reports->appends(request()->query())->links() }}
    </div>
</div>
@endsection

@section('js')
<script>
    (function () {
        const periodTypeSelect      = document.getElementById('period_type');
        const reportDateGroup       = document.getElementById('report_date_group');
        const reportDateInput       = document.getElementById('report_date');
        const monthGroup            = document.getElementById('month_group');
        const monthInput            = document.getElementById('month');
        const yearGroup             = document.getElementById('year_group');
        const yearInput             = document.getElementById('year');
        const comparisonTypeSelect  = document.getElementById('comparison_type');
        const comparisonOffsetGroup = document.getElementById('comparison_offset_group');
        const comparisonOffsetInput = document.getElementById('comparison_offset');
        const comparisonDateGroup   = document.getElementById('comparison_date_group');
        const comparisonDateInput   = document.getElementById('comparison_date');

        function syncPeriodFilter() {
            const periodType = periodTypeSelect.value;
            const isAll     = periodType === 'ALL';
            const isDaily   = periodType === 'DAILY';
            const isMonthly = periodType === 'MONTHLY';
            const isYearly  = periodType === 'YEARLY';

            reportDateGroup.style.display = isDaily   ? '' : 'none';
            monthGroup.style.display      = isMonthly ? '' : 'none';
            yearGroup.style.display       = (isMonthly || isYearly) ? '' : 'none';

            reportDateInput.disabled = !isDaily;
            reportDateInput.required = isDaily;
            monthInput.disabled      = !isMonthly;
            monthInput.required      = isMonthly;
            yearInput.disabled       = !(isMonthly || isYearly);
            yearInput.required       = (isMonthly || isYearly);

            if (isAll) { comparisonTypeSelect.value = 'NONE'; }
            comparisonTypeSelect.disabled = isAll;
        }

        function syncComparisonFilter() {
            const comparisonType = comparisonTypeSelect.value;
            const useOffset = comparisonType === 'PREVIOUS_PERIOD';
            const useDate   = comparisonType === 'SPECIFIC_DATE';

            comparisonOffsetGroup.style.display = useOffset ? '' : 'none';
            comparisonDateGroup.style.display   = useDate   ? '' : 'none';

            comparisonOffsetInput.disabled = !useOffset;
            comparisonOffsetInput.required = useOffset;
            comparisonDateInput.disabled   = !useDate;
            comparisonDateInput.required   = useDate;
        }

        periodTypeSelect.addEventListener('change', syncPeriodFilter);
        comparisonTypeSelect.addEventListener('change', syncComparisonFilter);

        syncPeriodFilter();
        syncComparisonFilter();
    })();
</script>
@endsection
