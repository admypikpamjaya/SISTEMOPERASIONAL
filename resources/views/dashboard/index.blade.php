@extends('layouts.app')

@section('content')
@include('shared.modal')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=DM+Mono:wght@300;400;500&display=swap');

    :root {
        --blue-primary: #1a56db;
        --blue-dark: #1e3a8a;
        --blue-deeper: #0f2460;
        --blue-mid: #2563eb;
        --blue-light: #3b82f6;
        --blue-glow: rgba(37, 99, 235, 0.25);
        --accent-cyan: #06b6d4;
        --accent-green: #10b981;
        --accent-red: #ef4444;
        --accent-amber: #f59e0b;
        --surface-bg: #f0f4fd;
        --surface-card: #ffffff;
        --surface-dark: #0f172a;
        --text-primary: #0f172a;
        --text-secondary: #475569;
        --text-muted: #94a3b8;
        --border-light: rgba(37, 99, 235, 0.12);
        --shadow-sm: 0 1px 3px rgba(15, 23, 42, 0.08), 0 1px 2px rgba(15, 23, 42, 0.06);
        --shadow-md: 0 4px 16px rgba(15, 23, 42, 0.10), 0 2px 8px rgba(37, 99, 235, 0.08);
        --shadow-lg: 0 10px 40px rgba(15, 23, 42, 0.14), 0 4px 16px rgba(37, 99, 235, 0.12);
        --shadow-glow: 0 0 0 3px rgba(37, 99, 235, 0.15);
        --radius-sm: 10px;
        --radius-md: 14px;
        --radius-lg: 20px;
        --radius-xl: 28px;
    }

    body, .content-wrapper {
        background: var(--surface-bg) !important;
        font-family: 'Plus Jakarta Sans', sans-serif !important;
    }

    /* ─── Page Header ─────────────────────────── */
    .dash-page-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        flex-wrap: wrap;
        margin-bottom: 1.75rem;
        padding: 0;
        animation: slideDown 0.5s cubic-bezier(0.34, 1.56, 0.64, 1) both;
    }
    .dash-page-header-main {
        display: flex;
        align-items: center;
        gap: 1rem;
    }
    .dash-page-header .header-icon {
        width: 48px;
        height: 48px;
        border-radius: var(--radius-sm);
        background: linear-gradient(135deg, var(--blue-primary), var(--blue-light));
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1.3rem;
        box-shadow: var(--shadow-md);
        flex-shrink: 0;
    }
    .dash-page-header .header-text h1 {
        font-size: 1.45rem;
        font-weight: 800;
        color: var(--text-primary);
        margin: 0;
        letter-spacing: -0.02em;
        line-height: 1.2;
    }
    .dash-page-header .header-text p {
        font-size: 0.82rem;
        color: var(--text-muted);
        margin: 0;
        font-weight: 500;
    }
    /* ─── Welcome Card ────────────────────────── */
    .welcome-card {
        background: linear-gradient(135deg, var(--blue-deeper) 0%, var(--blue-dark) 50%, var(--blue-primary) 100%);
        border-radius: var(--radius-lg);
        padding: 1.6rem 2rem;
        position: relative;
        overflow: hidden;
        box-shadow: var(--shadow-lg);
        color: white;
        height: 100%;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        animation: fadeUp 0.55s ease both;
    }
    .welcome-card::before {
        content: '';
        position: absolute;
        top: -40px; right: -40px;
        width: 180px; height: 180px;
        background: rgba(255,255,255,0.06);
        border-radius: 50%;
    }
    .welcome-card::after {
        content: '';
        position: absolute;
        bottom: -60px; left: 30%;
        width: 250px; height: 250px;
        background: rgba(255,255,255,0.04);
        border-radius: 50%;
    }
    .welcome-card h3 {
        font-size: 1.1rem;
        font-weight: 700;
        margin: 0 0 0.25rem;
        position: relative;
        z-index: 1;
    }
    .welcome-card p {
        font-size: 0.8rem;
        opacity: 0.75;
        margin: 0;
        position: relative;
        z-index: 1;
    }
    .welcome-card .welcome-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        background: rgba(255,255,255,0.15);
        border: 1px solid rgba(255,255,255,0.2);
        border-radius: 999px;
        padding: 0.3rem 0.8rem;
        font-size: 0.75rem;
        font-weight: 600;
        color: rgba(255,255,255,0.9);
        margin-bottom: 0.75rem;
        width: fit-content;
        backdrop-filter: blur(6px);
        position: relative;
        z-index: 1;
    }
    .welcome-card .welcome-badge i {
        color: #fbbf24;
        font-size: 0.7rem;
    }

    /* ─── Saldo Widget ────────────────────────── */
    .saldo-card {
        background: var(--surface-card);
        border-radius: var(--radius-lg);
        padding: 1.5rem;
        box-shadow: var(--shadow-md);
        border: 1px solid var(--border-light);
        position: relative;
        overflow: hidden;
        height: 100%;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        animation: fadeUp 0.6s ease both;
        text-decoration: none !important;
        transition: all 0.3s ease;
    }
    .saldo-card:hover {
        box-shadow: var(--shadow-lg), var(--shadow-glow);
        transform: translateY(-2px);
    }
    .saldo-card::before {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0;
        height: 3px;
        background: linear-gradient(90deg, var(--blue-primary), var(--accent-cyan));
        border-radius: var(--radius-lg) var(--radius-lg) 0 0;
    }
    .saldo-label {
        font-size: 0.76rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        color: var(--text-muted);
        display: flex;
        align-items: center;
        gap: 0.4rem;
        margin-bottom: 0.5rem;
    }
    .saldo-label .dot {
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background: var(--accent-green);
        animation: pulse 2s infinite;
    }
    @keyframes pulse {
        0%, 100% { opacity: 1; transform: scale(1); }
        50% { opacity: 0.6; transform: scale(1.3); }
    }
    .saldo-value {
        font-size: 1.7rem;
        font-weight: 400;
        color: var(--text-primary);
        letter-spacing: -0.01em;
        font-family: 'Plus Jakarta Sans', sans-serif;
        line-height: 1.1;
        margin-bottom: 0.4rem;
    }
    .saldo-meta {
        font-size: 0.72rem;
        color: var(--text-muted);
        display: flex;
        align-items: center;
        gap: 0.3rem;
    }
    .saldo-footer {
        margin-top: 1rem;
        padding-top: 0.75rem;
        border-top: 1px solid var(--border-light);
    }
    .saldo-footer a {
        font-size: 0.78rem;
        font-weight: 600;
        color: var(--blue-primary);
        text-decoration: none;
        display: flex;
        align-items: center;
        gap: 0.35rem;
        transition: gap 0.2s;
    }
    .saldo-footer a:hover { gap: 0.6rem; }
    .saldo-icon {
        width: 42px;
        height: 42px;
        border-radius: var(--radius-sm);
        background: linear-gradient(135deg, rgba(37,99,235,0.12), rgba(6,182,212,0.12));
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--blue-primary);
        font-size: 1.1rem;
        margin-bottom: 0.75rem;
    }

    /* ─── Chart Cards ─────────────────────────── */
    .dash-chart-card {
        background: var(--surface-card);
        border-radius: var(--radius-lg);
        box-shadow: var(--shadow-md);
        border: 1px solid var(--border-light);
        overflow: hidden;
        margin-bottom: 1.25rem;
        transition: all 0.3s ease;
        cursor: pointer;
        animation: fadeUp 0.7s ease both;
        position: relative;
    }
    .dash-chart-card:hover {
        box-shadow: var(--shadow-lg);
        transform: translateY(-3px);
    }
    .dash-chart-card .chart-card-header {
        padding: 0.9rem 1.1rem 0.6rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        border-bottom: 1px solid rgba(0,0,0,0.04);
    }
    .dash-chart-card .chart-card-title {
        font-size: 0.85rem;
        font-weight: 700;
        color: var(--text-primary);
        display: flex;
        align-items: center;
        gap: 0.5rem;
        margin: 0;
    }
    .dash-chart-card .chart-card-title .title-icon {
        width: 26px;
        height: 26px;
        border-radius: 7px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 0.75rem;
        flex-shrink: 0;
    }
    .dash-chart-card .chart-card-body {
        padding: 0.5rem 0.75rem 0.5rem;
    }
    .dash-chart-card .chart-card-footer {
        padding: 0.5rem 1.1rem;
        font-size: 0.72rem;
        color: var(--text-muted);
        display: flex;
        align-items: center;
        gap: 0.3rem;
        background: rgba(0,0,0,0.015);
        border-top: 1px solid rgba(0,0,0,0.04);
    }
    .dash-chart-card canvas {
        width: 100% !important;
        height: 88px !important;
        display: block;
    }

    /* Card accent bar */
    .accent-income::before   { background: linear-gradient(90deg, var(--blue-primary), var(--accent-cyan)); }
    .accent-expense::before  { background: linear-gradient(90deg, var(--accent-red), var(--accent-amber)); }
    .accent-wa::before       { background: linear-gradient(90deg, var(--accent-green), #34d399); }
    .accent-email::before    { background: linear-gradient(90deg, var(--accent-cyan), #818cf8); }
    .dash-chart-card::before {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0;
        height: 3px;
        border-radius: var(--radius-lg) var(--radius-lg) 0 0;
    }

    /* Title icon colors */
    .icon-income  { background: rgba(37,99,235,0.12); color: var(--blue-primary); }
    .icon-expense { background: rgba(239,68,68,0.12); color: var(--accent-red); }
    .icon-wa      { background: rgba(16,185,129,0.12); color: var(--accent-green); }
    .icon-email   { background: rgba(6,182,212,0.12); color: var(--accent-cyan); }

    /* ─── Stats Badge ─────────────────────────── */
    .open-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
        background: rgba(37,99,235,0.08);
        color: var(--blue-primary);
        font-size: 0.68rem;
        font-weight: 700;
        padding: 0.2rem 0.5rem;
        border-radius: 999px;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        transition: background 0.2s;
    }
    .dash-chart-card:hover .open-badge {
        background: rgba(37,99,235,0.15);
    }

    /* ─── Section Labels ──────────────────────── */
    .section-label {
        font-size: 0.72rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.1em;
        color: var(--text-muted);
        margin: 1.5rem 0 0.75rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    .section-label::after {
        content: '';
        flex: 1;
        height: 1px;
        background: var(--border-light);
    }

    /* ─── Animations ──────────────────────────── */
    .maintenance-hub-card {
        padding: 0;
    }
    .maintenance-hub-grid {
        display: grid;
        grid-template-columns: minmax(320px, 1.1fr) minmax(320px, 1fr);
        min-height: 100%;
    }
    .maintenance-hub-hero {
        position: relative;
        padding: 1.55rem;
        border-right: 1px solid var(--border-light);
        background:
            radial-gradient(circle at top left, rgba(59,130,246,0.18), transparent 40%),
            linear-gradient(180deg, rgba(37,99,235,0.05), transparent 65%);
        overflow: hidden;
    }
    .maintenance-hub-hero::after {
        content: '';
        position: absolute;
        right: -24px;
        bottom: -36px;
        width: 160px;
        height: 160px;
        border-radius: 50%;
        background: rgba(37,99,235,0.06);
    }
    .maintenance-hub-kicker {
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        padding: 0.35rem 0.75rem;
        border-radius: 999px;
        background: rgba(37,99,235,0.10);
        color: var(--blue-primary);
        font-size: 0.72rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        margin-bottom: 1rem;
    }
    .maintenance-hub-kicker .dot {
        width: 7px;
        height: 7px;
        border-radius: 50%;
        background: var(--accent-green);
        box-shadow: 0 0 0 5px rgba(16,185,129,0.12);
    }
    .maintenance-hub-title {
        font-size: 1.35rem;
        font-weight: 800;
        color: var(--text-primary);
        line-height: 1.2;
        margin: 0 0 0.55rem;
        letter-spacing: -0.02em;
    }
    .maintenance-hub-description {
        font-size: 0.88rem;
        color: var(--text-secondary);
        line-height: 1.8;
        max-width: 540px;
        margin-bottom: 1.25rem;
        position: relative;
        z-index: 1;
    }
    .maintenance-hub-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 0.8rem;
        margin-bottom: 1.15rem;
        position: relative;
        z-index: 1;
    }
    .maintenance-hub-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.55rem;
        min-width: 180px;
        padding: 0.82rem 1.2rem;
        border-radius: 16px;
        font-size: 0.86rem;
        font-weight: 700;
        border: 1px solid transparent;
        transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease, background 0.2s ease;
        text-decoration: none !important;
    }
    .maintenance-hub-btn:hover {
        transform: translateY(-1px);
    }
    .maintenance-hub-btn-primary {
        color: #fff;
        background: linear-gradient(135deg, var(--blue-primary), var(--blue-light));
        box-shadow: 0 12px 24px rgba(37,99,235,0.22);
    }
    .maintenance-hub-btn-primary:hover {
        color: #fff;
        box-shadow: 0 16px 28px rgba(37,99,235,0.28);
    }
    .maintenance-hub-btn-secondary {
        color: var(--text-primary);
        background: rgba(255,255,255,0.72);
        border-color: rgba(37,99,235,0.14);
        box-shadow: 0 10px 22px rgba(15,23,42,0.06);
    }
    .maintenance-hub-btn-secondary:hover {
        color: var(--blue-primary);
        border-color: rgba(37,99,235,0.28);
        background: rgba(255,255,255,0.9);
    }
    .maintenance-hub-master {
        display: inline-flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 0.6rem;
        padding: 0.78rem 0.95rem;
        border-radius: 16px;
        background: rgba(15,23,42,0.035);
        border: 1px solid rgba(37,99,235,0.10);
        position: relative;
        z-index: 1;
    }
    .maintenance-hub-master-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        padding: 0.3rem 0.7rem;
        border-radius: 999px;
        background: rgba(16,185,129,0.12);
        color: #059669;
        font-size: 0.72rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.06em;
    }
    .maintenance-hub-master-email {
        font-size: 0.92rem;
        font-weight: 700;
        color: var(--text-primary);
        word-break: break-word;
    }
    .maintenance-hub-panel {
        padding: 1.55rem;
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }
    .maintenance-hub-stats {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 1rem;
    }
    .maintenance-hub-stat {
        border: 1px solid rgba(37,99,235,0.12);
        border-radius: 18px;
        padding: 1rem 1.05rem;
        background: linear-gradient(180deg, rgba(37,99,235,0.04), rgba(255,255,255,0.9));
        box-shadow: inset 0 1px 0 rgba(255,255,255,0.55);
    }
    .maintenance-hub-stat-label {
        font-size: 0.72rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        color: var(--text-muted);
        margin-bottom: 0.6rem;
    }
    .maintenance-hub-stat-value {
        font-size: 1.8rem;
        font-weight: 800;
        color: var(--text-primary);
        line-height: 1;
        margin-bottom: 0.5rem;
    }
    .maintenance-hub-stat-meta {
        font-size: 0.77rem;
        color: var(--text-secondary);
        line-height: 1.7;
    }
    .maintenance-hub-preview-card {
        flex: 1;
        border: 1px solid rgba(37,99,235,0.12);
        border-radius: 20px;
        padding: 1.1rem;
        background:
            radial-gradient(circle at top right, rgba(6,182,212,0.10), transparent 30%),
            linear-gradient(180deg, rgba(37,99,235,0.035), rgba(255,255,255,0.94));
    }
    .maintenance-hub-preview-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.8rem;
        margin-bottom: 0.85rem;
    }
    .maintenance-hub-preview-title {
        font-size: 0.9rem;
        font-weight: 700;
        color: var(--text-primary);
        margin: 0;
    }
    .maintenance-hub-count-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0.38rem 0.8rem;
        border-radius: 999px;
        background: rgba(6,182,212,0.12);
        color: #0891b2;
        font-size: 0.75rem;
        font-weight: 700;
        white-space: nowrap;
    }
    .maintenance-hub-preview-body {
        min-height: 90px;
        display: flex;
        flex-wrap: wrap;
        align-content: flex-start;
        gap: 0.7rem;
    }
    .maintenance-chip {
        display: inline-flex;
        align-items: center;
        gap: 0.55rem;
        padding: 0.72rem 0.9rem;
        border-radius: 14px;
        background: rgba(37,99,235,0.08);
        border: 1px solid rgba(37,99,235,0.12);
        color: var(--text-primary);
        max-width: 100%;
        box-shadow: inset 0 1px 0 rgba(255,255,255,0.55);
    }
    .maintenance-chip i {
        color: var(--blue-primary);
        font-size: 0.78rem;
    }
    .maintenance-chip span {
        font-size: 0.8rem;
        font-weight: 600;
        line-height: 1.5;
        word-break: break-word;
    }
    .maintenance-hub-empty {
        width: 100%;
        min-height: 92px;
        display: flex;
        align-items: center;
        justify-content: center;
        text-align: center;
        border-radius: 16px;
        border: 1px dashed rgba(37,99,235,0.20);
        background: rgba(15,23,42,0.025);
        color: var(--text-muted);
        font-size: 0.82rem;
        line-height: 1.7;
        padding: 1rem;
    }
    .maintenance-hub-note {
        margin-top: 0.9rem;
        font-size: 0.78rem;
        color: var(--text-muted);
        line-height: 1.7;
    }
    .maintenance-modal-shell {
        background: var(--surface-card);
    }
    .maintenance-modal-hero {
        padding: 1.4rem 1.4rem 1.2rem;
        border-bottom: 1px solid var(--border-light);
        background:
            radial-gradient(circle at top right, rgba(6,182,212,0.12), transparent 28%),
            linear-gradient(135deg, rgba(37,99,235,0.08), rgba(255,255,255,0.96));
    }
    .maintenance-modal-hero-kicker {
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        padding: 0.34rem 0.72rem;
        border-radius: 999px;
        background: rgba(37,99,235,0.10);
        color: var(--blue-primary);
        font-size: 0.71rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        margin-bottom: 0.8rem;
    }
    .maintenance-modal-hero-title {
        font-size: 1.2rem;
        font-weight: 800;
        color: var(--text-primary);
        margin-bottom: 0.45rem;
        letter-spacing: -0.02em;
    }
    .maintenance-modal-hero-text {
        font-size: 0.86rem;
        color: var(--text-secondary);
        line-height: 1.75;
        margin: 0;
    }
    .maintenance-modal-hero-badges {
        display: flex;
        flex-wrap: wrap;
        gap: 0.55rem;
        margin-top: 1rem;
    }
    .maintenance-soft-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        padding: 0.42rem 0.82rem;
        border-radius: 999px;
        font-size: 0.75rem;
        font-weight: 700;
    }
    .maintenance-soft-badge.master {
        background: rgba(16,185,129,0.12);
        color: #059669;
    }
    .maintenance-soft-badge.count {
        background: rgba(6,182,212,0.12);
        color: #0891b2;
    }
    .maintenance-modal-content {
        padding: 1.35rem;
    }
    .maintenance-modal-grid {
        display: grid;
        grid-template-columns: minmax(280px, 360px) minmax(0, 1fr);
        gap: 1rem;
    }
    .maintenance-modal-panel {
        border: 1px solid rgba(37,99,235,0.12);
        border-radius: 20px;
        background: linear-gradient(180deg, rgba(37,99,235,0.03), rgba(255,255,255,0.98));
        box-shadow: inset 0 1px 0 rgba(255,255,255,0.6);
        padding: 1.15rem;
    }
    .maintenance-modal-panel-title {
        font-size: 0.96rem;
        font-weight: 700;
        color: var(--text-primary);
        margin-bottom: 0.25rem;
    }
    .maintenance-modal-panel-text {
        font-size: 0.8rem;
        color: var(--text-muted);
        line-height: 1.7;
        margin-bottom: 1rem;
    }
    .maintenance-modal-master-box {
        border-radius: 16px;
        border: 1px solid rgba(16,185,129,0.18);
        background: rgba(16,185,129,0.08);
        padding: 0.9rem 1rem;
        margin-bottom: 1rem;
    }
    .maintenance-modal-master-label {
        font-size: 0.7rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        color: #059669;
        margin-bottom: 0.35rem;
    }
    .maintenance-modal-master-email {
        font-size: 0.9rem;
        font-weight: 700;
        color: var(--text-primary);
        word-break: break-word;
        margin-bottom: 0.3rem;
    }
    .maintenance-modal-master-note {
        font-size: 0.76rem;
        color: var(--text-secondary);
        line-height: 1.6;
    }
    .maintenance-modal-list {
        max-height: 430px;
        overflow: auto;
        padding-right: 0.1rem;
    }
    .maintenance-modal-item {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 1rem;
        padding: 0.95rem 1rem;
        border-radius: 16px;
        border: 1px solid rgba(37,99,235,0.10);
        background: rgba(255,255,255,0.85);
    }
    .maintenance-modal-item + .maintenance-modal-item {
        margin-top: 0.75rem;
    }
    .maintenance-modal-item-name {
        font-size: 0.9rem;
        font-weight: 700;
        color: var(--text-primary);
        margin-bottom: 0.2rem;
    }
    .maintenance-modal-item-email {
        font-size: 0.8rem;
        color: var(--text-secondary);
        line-height: 1.7;
        word-break: break-word;
        margin-bottom: 0.55rem;
    }
    .maintenance-modal-empty {
        min-height: 250px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        text-align: center;
        padding: 1.2rem;
        border-radius: 18px;
        border: 1px dashed rgba(37,99,235,0.20);
        color: var(--text-muted);
        background: rgba(15,23,42,0.025);
    }
    .maintenance-modal-empty i {
        font-size: 2rem;
        color: rgba(37,99,235,0.65);
        margin-bottom: 0.75rem;
    }
    @media (max-width: 1199.98px) {
        .maintenance-hub-grid,
        .maintenance-modal-grid {
            grid-template-columns: 1fr;
        }
        .maintenance-hub-hero {
            border-right: 0;
            border-bottom: 1px solid var(--border-light);
        }
    }
    @media (max-width: 767.98px) {
        .maintenance-hub-hero,
        .maintenance-hub-panel,
        .maintenance-modal-content,
        .maintenance-modal-hero {
            padding: 1.1rem;
        }
        .maintenance-hub-actions {
            flex-direction: column;
        }
        .maintenance-hub-btn {
            width: 100%;
            min-width: 100%;
        }
        .maintenance-hub-stats {
            grid-template-columns: 1fr;
        }
        .maintenance-hub-title {
            font-size: 1.15rem;
        }
    }
    @keyframes fadeUp {
        from { opacity: 0; transform: translateY(18px); }
        to   { opacity: 1; transform: translateY(0); }
    }
    @keyframes slideDown {
        from { opacity: 0; transform: translateY(-12px); }
        to   { opacity: 1; transform: translateY(0); }
    }

    .anim-delay-1 { animation-delay: 0.08s; }
    .anim-delay-2 { animation-delay: 0.14s; }
    .anim-delay-3 { animation-delay: 0.20s; }
    .anim-delay-4 { animation-delay: 0.26s; }
    .anim-delay-5 { animation-delay: 0.32s; }
</style>

{{-- Page Header --}}
<div class="dash-page-header">
    <div class="dash-page-header-main">
        <div class="header-icon"><i class="fas fa-tachometer-alt"></i></div>
        <div class="header-text">
            <h1>{{ __('app.dashboard.title') }}</h1>
            <p>{{ __('app.dashboard.subtitle') }}</p>
        </div>
    </div>
</div>

{{-- Top Row: Saldo + Welcome --}}
<div class="row">
    @if($showFinanceWidgets)
        <div class="col-lg-4 col-md-6 col-sm-12 mb-3">
            <div class="saldo-card">
                <div>
                    <div class="saldo-icon"><i class="fas fa-wallet"></i></div>
                    <div class="saldo-label">
                        <span class="dot"></span>
                        {{ __('app.dashboard.finance_balance') }}
                    </div>
                    <div class="saldo-value" id="dashboard-saldo-value">
                        Rp {{ number_format((float) ($saldo ?? 0), 2, ',', '.') }}
                    </div>
                    <div class="saldo-meta">
                        <i class="fas fa-clock" style="font-size:0.65rem;"></i>
                        {{ __('app.dashboard.updated_at') }}:&nbsp;<strong><span id="dashboard-saldo-updated">{{ $saldoUpdatedAt ?? '-' }}</span></strong>
                    </div>
                </div>
                <div class="saldo-footer">
                    <a href="{{ route('finance.report.snapshots') }}">
                        <i class="fas fa-chart-bar"></i>
                        {{ __('app.dashboard.view_snapshot') }}
                        <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>
        <div class="col-lg-8 col-md-6 col-sm-12 mb-3">
    @else
        <div class="col-12 mb-3">
    @endif
            <div class="welcome-card">
                <div>
                    <div class="welcome-badge">
                        <i class="fas fa-circle" style="font-size:0.45rem;color:#4ade80;"></i>
                        {{ __('app.dashboard.welcome_badge') }}
                    </div>
                    <h3>{{ __('app.dashboard.welcome_title') }}</h3>
                    <p>{{ __('app.dashboard.welcome_desc') }}</p>
                </div>
                <div style="display:flex; gap:.6rem; margin-top:1rem; position:relative; z-index:1;">
                    <div style="background:rgba(255,255,255,0.1);border:1px solid rgba(255,255,255,0.15);border-radius:10px;padding:.55rem .9rem;flex:1;backdrop-filter:blur(6px);">
                        <div style="font-size:.65rem;opacity:.7;text-transform:uppercase;letter-spacing:.08em;margin-bottom:.15rem;">{{ __('app.dashboard.active_modules') }}</div>
                        <div style="font-size:1.1rem;font-weight:800;">{{ ($showFinanceWidgets ? 1 : 0) + ($showBlastingWidgets ? 1 : 0) + 3 }}</div>
                    </div>
                    <div style="background:rgba(255,255,255,0.1);border:1px solid rgba(255,255,255,0.15);border-radius:10px;padding:.55rem .9rem;flex:1;backdrop-filter:blur(6px);">
                        <div style="font-size:.65rem;opacity:.7;text-transform:uppercase;letter-spacing:.08em;margin-bottom:.15rem;">{{ __('app.dashboard.refresh_data') }}</div>
                        <div style="font-size:1.1rem;font-weight:800;">60 Seconds</div>
                    </div>
                    <div style="background:rgba(255,255,255,0.1);border:1px solid rgba(255,255,255,0.15);border-radius:10px;padding:.55rem .9rem;flex:1;backdrop-filter:blur(6px);">
                        <div style="font-size:.65rem;opacity:.7;text-transform:uppercase;letter-spacing:.08em;margin-bottom:.15rem;">{{ __('app.dashboard.status') }}</div>
                        <div style="font-size:1.1rem;font-weight:800;color:#4ade80;">{{ __('app.dashboard.online') }}</div>
                    </div>
                </div>
            </div>
        </div>
</div>

@if($isSuperAdmin)
    <div class="section-label">
        <i class="fas fa-user-shield" style="color:var(--blue-primary);font-size:.7rem;"></i>
        Superadmin Tools
    </div>

    <div class="row">
        <div class="col-12 mb-3">
            <div class="saldo-card maintenance-hub-card" id="maintenance-recipient-summary-card">
                <div class="maintenance-hub-grid">
                    <div class="maintenance-hub-hero">
                        <div class="maintenance-hub-kicker">
                            <span class="dot"></span>
                            Superadmin Maintenance
                        </div>

                        <div class="saldo-icon mb-3"><i class="fas fa-envelope-open-text"></i></div>
                        <h3 class="maintenance-hub-title">Distribusi Report Maintenance</h3>
                        <div class="maintenance-hub-description">
                            Kelola email penerima report maintenance langsung dari dashboard superadmin. Email master tetap aktif, sementara email tambahan bisa ditambah atau dihapus kapan saja dengan tampilan yang lebih cepat dan rapi.
                        </div>

                        <div class="maintenance-hub-actions">
                            <button
                                type="button"
                                id="open-maintenance-recipient-add"
                                class="maintenance-hub-btn maintenance-hub-btn-primary"
                            >
                                <i class="fas fa-plus"></i>
                                Tambah Email
                            </button>
                            <button
                                type="button"
                                id="open-maintenance-recipient-manager"
                                class="maintenance-hub-btn maintenance-hub-btn-secondary"
                            >
                                <i class="fas fa-sliders-h"></i>
                                Kelola Daftar
                            </button>
                        </div>

                        <div class="maintenance-hub-master">
                            <span class="maintenance-hub-master-badge">
                                <i class="fas fa-lock"></i>
                                Master Aktif
                            </span>
                            <span class="maintenance-hub-master-email" id="maintenance-recipient-master-email">
                                {{ data_get($maintenanceNotificationRecipients, 'master') }}
                            </span>
                        </div>
                    </div>

                    <div class="maintenance-hub-panel">
                        <div class="maintenance-hub-stats">
                            <div class="maintenance-hub-stat">
                                <div class="maintenance-hub-stat-label">Total Tujuan</div>
                                <div class="maintenance-hub-stat-value" id="maintenance-recipient-total">
                                    {{ data_get($maintenanceNotificationRecipients, 'totalCount', 1) }}
                                </div>
                                <div class="maintenance-hub-stat-meta" id="maintenance-recipient-summary-text">
                                    Master tetap aktif, {{ data_get($maintenanceNotificationRecipients, 'additionalCount', 0) }} email tambahan tersimpan
                                </div>
                            </div>

                            <div class="maintenance-hub-stat">
                                <div class="maintenance-hub-stat-label">Email Master</div>
                                <div class="maintenance-hub-stat-value" style="font-size:1rem; line-height:1.45; word-break:break-word;">
                                    Terkunci
                                </div>
                                <div class="maintenance-hub-stat-meta">
                                    Email master di sisi kiri selalu menerima report maintenance dan tidak dapat dihapus.
                                </div>
                            </div>
                        </div>

                        <div class="maintenance-hub-preview-card">
                            <div class="maintenance-hub-preview-header">
                                <h4 class="maintenance-hub-preview-title">Email Tambahan Aktif</h4>
                                <span class="maintenance-hub-count-badge" id="maintenance-recipient-count-badge">
                                    {{ data_get($maintenanceNotificationRecipients, 'additionalCount', 0) }} email
                                </span>
                            </div>

                            <div class="maintenance-hub-preview-body" id="maintenance-recipient-preview">
                                @forelse((array) data_get($maintenanceNotificationRecipients, 'stored', []) as $recipient)
                                    <div class="maintenance-chip">
                                        <i class="fas fa-envelope"></i>
                                        <span>{{ data_get($recipient, 'label', data_get($recipient, 'email', '-')) }}</span>
                                    </div>
                                @empty
                                    <div class="maintenance-hub-empty">
                                        Belum ada email tambahan. Gunakan tombol <strong class="ml-1 mr-1">Tambah Email</strong> untuk menambahkan penerima baru.
                                    </div>
                                @endforelse
                            </div>

                            <div class="maintenance-hub-note">
                                Tombol <strong>Tambah Email</strong> membuka form penambahan cepat, sedangkan <strong>Kelola Daftar</strong> menampilkan daftar lengkap untuk tambah dan hapus email.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif

<div class="section-label">
    <i class="fas fa-box" style="color:var(--blue-primary);font-size:.7rem;"></i>
    {{ __('app.dashboard.asset_stats') }}
</div>

<div class="row">
    @foreach($assetStatisticsByUnit as $stat)
        @php
            $unitValue = $stat->unit;
            $unitLabel = $unitValue ?? 'Belum Di-assign';
        @endphp

        <div class="col-lg-4 col-md-6 col-sm-12 mb-3">
            <div class="saldo-card h-100">
                <div>
                    <div class="saldo-icon">
                        <i class="fas fa-box"></i>
                    </div>

                    <div class="saldo-label">
                        {{ $unitLabel }}
                    </div>

                    <div class="saldo-value">
                        {{ $stat->total_assets }}
                    </div>

                    <div class="saldo-meta">
                        {{ __('app.dashboard.total_assets') }}
                    </div>
                </div>

                <div class="saldo-footer">
                    @if($unitValue)
                        <a href="{{ route('asset-management.index', ['unit' => $unitValue]) }}">
                            {{ __('app.dashboard.view_assets') }} <i class="fas fa-arrow-right"></i>
                        </a>
                    @else
                        <a href="{{ route('asset-management.index') }}">
                            {{ __('app.dashboard.view_assets') }} <i class="fas fa-arrow-right"></i>
                        </a>
                    @endif
                </div>
            </div>
        </div>
    @endforeach
</div>

@if($showFinanceWidgets)
    <div class="section-label"><i class="fas fa-chart-line" style="color:var(--blue-primary);font-size:.7rem;"></i> {{ __('app.dashboard.finance_chart') }}</div>
    <div class="row">
        <div class="col-lg-6 col-md-6 col-sm-12">
            <div class="dash-chart-card accent-income dashboard-chart-card anim-delay-1" data-href="{{ data_get($incomeChart, 'url') }}">
                <div class="chart-card-header">
                    <h3 class="chart-card-title">
                        <span class="title-icon icon-income"><i class="fas fa-arrow-up"></i></span>
                        {{ __('app.dashboard.income') }}
                    </h3>
                    <span class="open-badge"><i class="fas fa-external-link-alt" style="font-size:.6rem;"></i> {{ __('app.dashboard.open') }}</span>
                </div>
                <div class="chart-card-body">
                    <canvas id="chart-income"></canvas>
                </div>
                <div class="chart-card-footer">
                    <i class="fas fa-info-circle" style="font-size:.7rem;"></i>
                    {{ __('app.dashboard.open_report') }}
                </div>
            </div>
        </div>
        <div class="col-lg-6 col-md-6 col-sm-12">
            <div class="dash-chart-card accent-expense dashboard-chart-card anim-delay-2" data-href="{{ data_get($expenseChart, 'url') }}">
                <div class="chart-card-header">
                    <h3 class="chart-card-title">
                        <span class="title-icon icon-expense"><i class="fas fa-arrow-down"></i></span>
                        {{ __('app.dashboard.expense') }}
                    </h3>
                    <span class="open-badge"><i class="fas fa-external-link-alt" style="font-size:.6rem;"></i> {{ __('app.dashboard.open') }}</span>
                </div>
                <div class="chart-card-body">
                    <canvas id="chart-expense"></canvas>
                </div>
                <div class="chart-card-footer">
                    <i class="fas fa-info-circle" style="font-size:.7rem;"></i>
                    {{ __('app.dashboard.open_report') }}
                </div>
            </div>
        </div>
    </div>
@endif

@if($showBlastingWidgets)
    <div class="section-label"><i class="fas fa-paper-plane" style="color:var(--blue-primary);font-size:.7rem;"></i> {{ __('app.dashboard.blast_chart') }}</div>
    <div class="row">
        <div class="col-lg-6 col-md-6 col-sm-12">
            <div class="dash-chart-card accent-email dashboard-chart-card anim-delay-3" data-href="{{ data_get($emailChart, 'url') }}">
                <div class="chart-card-header">
                    <h3 class="chart-card-title">
                        <span class="title-icon icon-email"><i class="fas fa-envelope"></i></span>
                        {{ __('app.dashboard.email_blast') }}
                    </h3>
                    <span class="open-badge"><i class="fas fa-external-link-alt" style="font-size:.6rem;"></i> {{ __('app.dashboard.open') }}</span>
                </div>
                <div class="chart-card-body">
                    <canvas id="chart-email"></canvas>
                </div>
                <div class="chart-card-footer">
                    <i class="fas fa-info-circle" style="font-size:.7rem;"></i>
                    {{ __('app.dashboard.open_report') }}
                </div>
            </div>
        </div>
        <div class="col-lg-6 col-md-6 col-sm-12">
            <div class="dash-chart-card accent-wa dashboard-chart-card anim-delay-4" data-href="{{ data_get($waChart, 'url') }}">
                <div class="chart-card-header">
                    <h3 class="chart-card-title">
                        <span class="title-icon icon-wa"><i class="fab fa-whatsapp"></i></span>
                        {{ __('app.dashboard.wa_blast') }}
                    </h3>
                    <span class="open-badge"><i class="fas fa-external-link-alt" style="font-size:.6rem;"></i> {{ __('app.dashboard.open') }}</span>
                </div>
                <div class="chart-card-body">
                    <canvas id="chart-wa"></canvas>
                </div>
                <div class="chart-card-footer">
                    <i class="fas fa-info-circle" style="font-size:.7rem;"></i>
                    {{ __('app.dashboard.open_report') }}
                </div>
            </div>
        </div>
    </div>
@endif
@endsection

@section('js')
<script src="{{ asset('vendor/adminlte/plugins/chart.js/Chart.min.js') }}"></script>
<script>
    (function () {
        const isSuperAdmin = @json((bool) ($isSuperAdmin ?? false));
        const showFinanceWidgets = @json((bool) ($showFinanceWidgets ?? false));
        const showBlastingWidgets = @json((bool) ($showBlastingWidgets ?? false));
        const chartDataEndpoint = @json(route('dashboard.chart-data'));
        const maintenanceRecipientRoutes = isSuperAdmin ? {
            index: @json(route('dashboard.maintenance-notification-recipients.index')),
            store: @json(route('dashboard.maintenance-notification-recipients.store')),
            destroy: @json(route('dashboard.maintenance-notification-recipients.destroy', ['recipient' => '__RECIPIENT__']))
        } : null;
        const refreshIntervalMs = 60000;
        let isRefreshing = false;
        let maintenanceNotificationRecipients = @json($maintenanceNotificationRecipients ?? null);

        const numberFormatter = new Intl.NumberFormat('id-ID', {
            minimumFractionDigits: 0,
            maximumFractionDigits: 0
        });
        const currencyFormatter = new Intl.NumberFormat('id-ID', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
        const themePalettes = {
            light: {
                grid: 'rgba(60, 60, 60, 0.06)',
                tick: '#94a3b8',
                legend: '#64748b'
            },
            dark: {
                grid: 'rgba(148, 163, 184, 0.14)',
                tick: '#94a3b8',
                legend: '#cbd5e1'
            }
        };

        const saldoValueElement = document.getElementById('dashboard-saldo-value');
        const saldoUpdatedElement = document.getElementById('dashboard-saldo-updated');
        const maintenanceRecipientTotalElement = document.getElementById('maintenance-recipient-total');
        const maintenanceRecipientSummaryElement = document.getElementById('maintenance-recipient-summary-text');
        const maintenanceRecipientMasterElement = document.getElementById('maintenance-recipient-master-email');
        const maintenanceRecipientCountBadgeElement = document.getElementById('maintenance-recipient-count-badge');
        const maintenanceRecipientPreviewElement = document.getElementById('maintenance-recipient-preview');

        function formatCurrency(value) {
            const number = Number(value);
            return 'Rp ' + currencyFormatter.format(Number.isNaN(number) ? 0 : number);
        }

        const incomeCanvas = document.getElementById('chart-income');
        const expenseCanvas = document.getElementById('chart-expense');
        const waCanvas = document.getElementById('chart-wa');
        const emailCanvas = document.getElementById('chart-email');
        let incomeChart = null;
        let expenseChart = null;
        let waChart = null;
        let emailChart = null;

        function escapeHtml(value) {
            return String(value ?? '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        function buildMaintenanceRecipientChip(label) {
            return `
                <div class="maintenance-chip">
                    <i class="fas fa-envelope"></i>
                    <span>${escapeHtml(label || '')}</span>
                </div>
            `;
        }

        function buildMaintenanceRecipientEmptyState(message) {
            return `
                <div class="maintenance-hub-empty">
                    ${message}
                </div>
            `;
        }

        function updateMaintenanceRecipientSummary(payload) {
            if (!isSuperAdmin || !payload || typeof payload !== 'object') {
                return;
            }

            maintenanceNotificationRecipients = payload;

            if (maintenanceRecipientTotalElement) {
                maintenanceRecipientTotalElement.textContent = String(payload.totalCount ?? 1);
            }

            if (maintenanceRecipientSummaryElement) {
                maintenanceRecipientSummaryElement.textContent = `Master tetap aktif, ${payload.additionalCount ?? 0} email tambahan tersimpan`;
            }

            if (maintenanceRecipientMasterElement) {
                maintenanceRecipientMasterElement.textContent = payload.master || '-';
            }

            if (maintenanceRecipientCountBadgeElement) {
                maintenanceRecipientCountBadgeElement.textContent = `${payload.additionalCount ?? 0} email`;
            }

            if (maintenanceRecipientPreviewElement) {
                const storedRecipients = Array.isArray(payload.stored) ? payload.stored : [];

                maintenanceRecipientPreviewElement.innerHTML = storedRecipients.length > 0
                    ? storedRecipients.map((recipient) => buildMaintenanceRecipientChip(recipient.label || recipient.email || '')).join('')
                    : buildMaintenanceRecipientEmptyState('Belum ada email tambahan. Gunakan tombol <strong class="ml-1 mr-1">Tambah Email</strong> untuk menambahkan penerima baru.');
            }
        }

        function buildMaintenanceRecipientManagerBody(mode = 'manage') {
            const config = maintenanceNotificationRecipients && typeof maintenanceNotificationRecipients === 'object'
                ? maintenanceNotificationRecipients
                : {
                    master: '',
                    stored: [],
                    additionalCount: 0,
                    totalCount: 0
                };

            const storedRecipients = Array.isArray(config.stored) ? config.stored : [];
            const storedRecipientsHtml = storedRecipients.length > 0
                ? storedRecipients.map((recipient) => `
                    <div class="maintenance-modal-item">
                        <div>
                            <div class="maintenance-modal-item-name">${escapeHtml(recipient.name || 'Tanpa nama')}</div>
                            <div class="maintenance-modal-item-email">${escapeHtml(recipient.email || '')}</div>
                            ${buildMaintenanceRecipientChip(recipient.label || recipient.email || '')}
                        </div>
                        <button
                            type="button"
                            class="btn btn-sm btn-outline-danger delete-maintenance-recipient-button"
                            data-id="${escapeHtml(recipient.id || '')}"
                            data-label="${escapeHtml(recipient.label || recipient.email || '')}"
                            style="border-radius:12px; min-width:44px;"
                        >
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                `).join('')
                : `
                    <div class="maintenance-modal-empty">
                        <i class="fas fa-envelope-open-text"></i>
                        <div class="font-weight-bold mb-2">Belum ada email tambahan</div>
                        <div style="max-width:320px; line-height:1.7;">
                            Klik tombol <strong>+ Tambah Email</strong> untuk menambahkan penerima report maintenance selain email master.
                        </div>
                    </div>
                `;

            return `
                <div class="maintenance-modal-shell">
                    <div class="maintenance-modal-hero">
                        <div>
                            <div class="maintenance-modal-hero-kicker">
                                <i class="fas fa-paper-plane"></i>
                                Email Maintenance
                            </div>
                            <div class="maintenance-modal-hero-title">
                                Kelola penerima report maintenance
                            </div>
                            <p class="maintenance-modal-hero-text">
                                Email master akan selalu ikut menerima report. Gunakan form di bawah untuk menambahkan email tambahan yang bisa dipanggil otomatis dari halaman maintenance report.
                            </p>
                            <div class="maintenance-modal-hero-badges">
                                <span class="maintenance-soft-badge master">
                                    <i class="fas fa-lock"></i>Email master aktif
                                </span>
                                <span class="maintenance-soft-badge count">
                                    <i class="fas fa-users"></i>${escapeHtml(config.additionalCount ?? 0)} email tambahan
                                </span>
                                <span class="maintenance-soft-badge count">
                                    <i class="fas fa-window-restore"></i>${mode === 'add' ? 'Mode tambah cepat' : 'Mode kelola daftar'}
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="maintenance-modal-content">
                        <div class="maintenance-modal-grid">
                            <div class="maintenance-modal-panel">
                                <div class="maintenance-modal-panel-title">Tambah Email Baru</div>
                                <div class="maintenance-modal-panel-text">
                                    Tambahkan email penerima report maintenance dengan nama PIC atau keterangan agar daftar lebih mudah dibaca.
                                </div>

                                <div class="maintenance-modal-master-box">
                                    <div class="maintenance-modal-master-label">Email Master</div>
                                    <div class="maintenance-modal-master-email">${escapeHtml(config.master || '-')}</div>
                                    <div class="maintenance-modal-master-note">Email ini tetap aktif otomatis dan tidak dapat dihapus.</div>
                                </div>

                                <form id="maintenance-recipient-form">
                                    <div class="form-group">
                                        <label for="maintenance-recipient-name">Nama / Keterangan</label>
                                        <input type="text" id="maintenance-recipient-name" name="name" class="form-control" placeholder="Contoh: Kepala Sekolah TK">
                                    </div>
                                    <div class="form-group">
                                        <label for="maintenance-recipient-email">Email Penerima</label>
                                        <input type="email" id="maintenance-recipient-email" name="email" class="form-control" placeholder="nama@email.com" required>
                                    </div>
                                    <button type="submit" class="maintenance-hub-btn maintenance-hub-btn-primary w-100" style="min-width:100%;">
                                        <i class="fas fa-plus"></i>
                                        Tambah Email
                                    </button>
                                </form>
                            </div>

                            <div class="maintenance-modal-panel">
                                <div class="d-flex flex-wrap align-items-center justify-content-between mb-2" style="gap:.75rem;">
                                    <div>
                                        <div class="maintenance-modal-panel-title mb-1">Daftar Email Tambahan</div>
                                        <div class="maintenance-modal-panel-text mb-0">
                                            Daftar ini dipanggil otomatis dari halaman maintenance report dan tetap bisa ditambah atau dihapus kapan saja.
                                        </div>
                                    </div>
                                    <span class="maintenance-soft-badge count">
                                        <i class="fas fa-envelope"></i>${escapeHtml(config.additionalCount ?? 0)} email aktif
                                    </span>
                                </div>

                                <div class="maintenance-modal-list">
                                    ${storedRecipientsHtml}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }

        async function loadMaintenanceRecipientConfig() {
            if (!isSuperAdmin || !maintenanceRecipientRoutes) {
                return maintenanceNotificationRecipients;
            }

            const response = await Http.get(maintenanceRecipientRoutes.index);
            const payload = response?.data ?? null;

            if (payload) {
                updateMaintenanceRecipientSummary(payload);
            }

            return payload;
        }

        function showMaintenanceRecipientManager(mode = 'manage') {
            modal.show(
                mode === 'add' ? 'Tambah Email Maintenance' : 'Kelola Email Maintenance',
                buildMaintenanceRecipientManagerBody(mode),
                '',
                {
                    dialogClass: 'modal-xl modal-dialog-scrollable',
                    bodyClass: 'modal-body p-0'
                }
            );

            if (mode === 'add') {
                setTimeout(() => {
                    const emailInput = document.getElementById('maintenance-recipient-email');
                    if (emailInput) {
                        emailInput.focus();
                    }
                }, 150);
            }
        }

        function isDarkTheme() {
            if (window.ThemeManager) {
                return window.ThemeManager.isDark();
            }

            return document.body.classList.contains('dark-mode');
        }

        function getActiveThemePalette() {
            return isDarkTheme() ? themePalettes.dark : themePalettes.light;
        }

        function createGridLines(color) {
            return {
                display: true,
                color: color
            };
        }

        function createScales(options = {}) {
            const palette = getActiveThemePalette();
            const useCurrencyAxis = Boolean(options.currency);
            const yAxisTicks = {
                beginAtZero: true,
                maxTicksLimit: 3,
                fontColor: palette.tick,
                fontSize: 10
            };

            if (useCurrencyAxis) {
                yAxisTicks.callback = (value) => 'Rp ' + numberFormatter.format(value);
            }

            return {
                xAxes: [{
                    gridLines: createGridLines(palette.grid),
                    ticks: {
                        maxTicksLimit: 4,
                        fontColor: palette.tick,
                        fontSize: 10
                    }
                }],
                yAxes: [{
                    ticks: yAxisTicks,
                    gridLines: createGridLines(palette.grid)
                }]
            };
        }

        function createLegend(display) {
            const palette = getActiveThemePalette();

            return {
                display: display,
                labels: {
                    boxWidth: 9,
                    fontSize: 10,
                    fontColor: palette.legend
                }
            };
        }

        if (showFinanceWidgets && incomeCanvas) {
            incomeChart = new Chart(incomeCanvas.getContext('2d'), {
                type: 'line',
                data: {
                    labels: @json(data_get($incomeChart, 'labels', [])),
                    datasets: [{
                        label: 'Income',
                        data: @json(data_get($incomeChart, 'values', [])),
                        borderColor: '#2563eb',
                        backgroundColor: 'rgba(37, 99, 235, 0.12)',
                        fill: true,
                        tension: 0.4,
                        pointRadius: 2.5,
                        pointBackgroundColor: '#2563eb',
                        borderWidth: 2.5
                    }]
                },
                options: {
                    maintainAspectRatio: false,
                    responsive: true,
                    legend: createLegend(false),
                    scales: createScales({ currency: true })
                }
            });
        }

        if (showFinanceWidgets && expenseCanvas) {
            expenseChart = new Chart(expenseCanvas.getContext('2d'), {
                type: 'line',
                data: {
                    labels: @json(data_get($expenseChart, 'labels', [])),
                    datasets: [
                        {
                            label: 'Pengeluaran',
                            data: @json(data_get($expenseChart, 'expenseValues', [])),
                            borderColor: '#ef4444',
                            backgroundColor: 'rgba(239, 68, 68, 0.1)',
                            fill: false, tension: 0.4, pointRadius: 2.5, borderWidth: 2.5,
                            pointBackgroundColor: '#ef4444'
                        },
                        {
                            label: 'Penyusutan',
                            data: @json(data_get($expenseChart, 'depreciationValues', [])),
                            borderColor: '#f59e0b',
                            backgroundColor: 'rgba(245, 158, 11, 0.1)',
                            fill: false, tension: 0.4, pointRadius: 2.5, borderWidth: 2.5,
                            pointBackgroundColor: '#f59e0b'
                        }
                    ]
                },
                options: {
                    maintainAspectRatio: false,
                    responsive: true,
                    legend: createLegend(true),
                    scales: createScales({ currency: true })
                }
            });
        }

        if (showBlastingWidgets && waCanvas) {
            waChart = new Chart(waCanvas.getContext('2d'), {
                type: 'line',
                data: {
                    labels: @json(data_get($waChart, 'labels', [])),
                    datasets: [{
                        label: 'Blast WA',
                        data: @json(data_get($waChart, 'values', [])),
                        borderColor: '#10b981',
                        backgroundColor: 'rgba(16, 185, 129, 0.12)',
                        fill: true, tension: 0.4, pointRadius: 2.5, borderWidth: 2.5,
                        pointBackgroundColor: '#10b981'
                    }]
                },
                options: {
                    maintainAspectRatio: false,
                    responsive: true,
                    legend: createLegend(false),
                    scales: createScales()
                }
            });
        }

        if (showBlastingWidgets && emailCanvas) {
            emailChart = new Chart(emailCanvas.getContext('2d'), {
                type: 'line',
                data: {
                    labels: @json(data_get($emailChart, 'labels', [])),
                    datasets: [{
                        label: 'Blast Email',
                        data: @json(data_get($emailChart, 'values', [])),
                        borderColor: '#06b6d4',
                        backgroundColor: 'rgba(6, 182, 212, 0.12)',
                        fill: true, tension: 0.4, pointRadius: 2.5, borderWidth: 2.5,
                        pointBackgroundColor: '#06b6d4'
                    }]
                },
                options: {
                    maintainAspectRatio: false,
                    responsive: true,
                    legend: createLegend(false),
                    scales: createScales()
                }
            });
        }

        function refreshChartTheme() {
            if (incomeChart) {
                incomeChart.options.legend = createLegend(false);
                incomeChart.options.scales = createScales({ currency: true });
                incomeChart.update();
            }

            if (expenseChart) {
                expenseChart.options.legend = createLegend(true);
                expenseChart.options.scales = createScales({ currency: true });
                expenseChart.update();
            }

            if (waChart) {
                waChart.options.legend = createLegend(false);
                waChart.options.scales = createScales();
                waChart.update();
            }

            if (emailChart) {
                emailChart.options.legend = createLegend(false);
                emailChart.options.scales = createScales();
                emailChart.update();
            }
        }

        function applyDashboardData(payload) {
            if (!payload || typeof payload !== 'object') return;

            if (showFinanceWidgets && saldoValueElement) {
                saldoValueElement.textContent = formatCurrency(payload.saldo ?? 0);
            }
            if (showFinanceWidgets && saldoUpdatedElement) {
                saldoUpdatedElement.textContent = payload.saldoUpdatedAt || '-';
            }

            if (incomeChart && payload.incomeChart) {
                incomeChart.data.labels = payload.incomeChart.labels || [];
                incomeChart.data.datasets[0].data = payload.incomeChart.values || [];
                incomeChart.update();
            }

            if (expenseChart && payload.expenseChart) {
                expenseChart.data.labels = payload.expenseChart.labels || [];
                expenseChart.data.datasets[0].data = payload.expenseChart.expenseValues || [];
                expenseChart.data.datasets[1].data = payload.expenseChart.depreciationValues || [];
                expenseChart.update();
            }

            if (showBlastingWidgets && waChart && payload.waChart) {
                waChart.data.labels = payload.waChart.labels || [];
                waChart.data.datasets[0].data = payload.waChart.values || [];
                waChart.update();
            }

            if (showBlastingWidgets && emailChart && payload.emailChart) {
                emailChart.data.labels = payload.emailChart.labels || [];
                emailChart.data.datasets[0].data = payload.emailChart.values || [];
                emailChart.update();
            }

            if (isSuperAdmin && payload.maintenanceNotificationRecipients) {
                updateMaintenanceRecipientSummary(payload.maintenanceNotificationRecipients);
            }
        }

        async function refreshDashboardData() {
            if (isRefreshing) return;
            isRefreshing = true;
            try {
                const response = await fetch(chartDataEndpoint, {
                    method: 'GET',
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                });
                if (!response.ok) return;
                const payload = await response.json();
                applyDashboardData(payload);
            } catch (error) {
                // Ignore intermittent network failure and try again in next interval.
            } finally {
                isRefreshing = false;
            }
        }

        document.querySelectorAll('.dashboard-chart-card').forEach((card) => {
            card.addEventListener('click', () => {
                const targetUrl = card.getAttribute('data-href');
                if (targetUrl) window.location.href = targetUrl;
            });
        });

        if (isSuperAdmin) {
            updateMaintenanceRecipientSummary(maintenanceNotificationRecipients);

            $(document).on('click', '#open-maintenance-recipient-add, #open-maintenance-recipient-manager', async function (event) {
                event.preventDefault();
                const mode = event.currentTarget.id === 'open-maintenance-recipient-add' ? 'add' : 'manage';
                showMaintenanceRecipientManager(mode);
                Loading.show();

                try {
                    const latestPayload = await loadMaintenanceRecipientConfig();
                    if (latestPayload) {
                        showMaintenanceRecipientManager(mode);
                    }
                } catch (error) {
                    Notification.warning('Popup tetap dibuka, tetapi daftar email terbaru belum berhasil dimuat dari server.');
                } finally {
                    Loading.hide();
                }
            });

            $(document).on('submit', '#maintenance-recipient-form', async function (event) {
                event.preventDefault();

                const form = event.currentTarget;
                if (!form.checkValidity()) {
                    form.reportValidity();
                    return;
                }

                Loading.show();

                try {
                    const response = await Http.post(
                        maintenanceRecipientRoutes.store,
                        new FormData(form)
                    );

                    updateMaintenanceRecipientSummary(response?.data ?? null);
                    showMaintenanceRecipientManager('add');
                    Notification.success(response?.message || 'Email maintenance berhasil ditambahkan.');
                } catch (error) {
                    Notification.error(error);
                } finally {
                    Loading.hide();
                }
            });

            $(document).on('click', '.delete-maintenance-recipient-button', async function () {
                const recipientId = $(this).data('id');
                const recipientLabel = $(this).data('label') || 'email ini';

                const confirmation = await Notification.confirmation(
                    `Hapus ${recipientLabel} dari daftar email maintenance?`
                );
                if (!confirmation.isConfirmed) {
                    return;
                }

                Loading.show();

                try {
                    const response = await Http.delete(
                        maintenanceRecipientRoutes.destroy.replace('__RECIPIENT__', recipientId)
                    );

                    updateMaintenanceRecipientSummary(response?.data ?? null);
                    showMaintenanceRecipientManager('manage');
                    Notification.success(response?.message || 'Email maintenance berhasil dihapus.');
                } catch (error) {
                    Notification.error(error);
                } finally {
                    Loading.hide();
                }
            });
        }

        window.addEventListener('app:theme-change', refreshChartTheme);

        setInterval(refreshDashboardData, refreshIntervalMs);
    })();
</script>
@endsection
