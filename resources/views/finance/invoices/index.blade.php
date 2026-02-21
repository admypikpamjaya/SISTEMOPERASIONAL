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
        --accent-purple: #8b5cf6;
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

    .inv-page-header {
        display: flex; align-items: center; justify-content: space-between;
        margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;
        animation: fadeDown 0.4s ease both;
    }
    .inv-header-left { display: flex; align-items: center; gap: 0.9rem; }
    .inv-header-icon {
        width: 48px; height: 48px; border-radius: var(--radius-sm);
        background: linear-gradient(135deg, var(--blue-primary), var(--blue-light));
        display: flex; align-items: center; justify-content: center;
        color: white; font-size: 1.25rem; box-shadow: var(--shadow-md); flex-shrink: 0;
    }
    .inv-header-title { font-size: 1.35rem; font-weight: 800; color: var(--text-primary); margin: 0; letter-spacing: -0.02em; line-height: 1.2; }
    .inv-header-sub { font-size: 0.8rem; color: var(--text-muted); margin: 0.1rem 0 0; font-weight: 500; }

    .btn-inv-new {
        display: inline-flex; align-items: center; gap: 0.4rem;
        background: linear-gradient(135deg, var(--blue-primary), var(--blue-mid));
        color: white; font-size: 0.82rem; font-weight: 700;
        padding: 0.55rem 1.15rem; border-radius: var(--radius-sm);
        text-decoration: none; transition: all 0.25s;
        box-shadow: 0 3px 10px rgba(37,99,235,0.35);
    }
    .btn-inv-new:hover { transform: translateY(-1px); box-shadow: 0 6px 20px rgba(37,99,235,0.45); color: white; text-decoration: none; }

    .inv-filter-card {
        background: white; border-radius: var(--radius-lg);
        box-shadow: var(--shadow-md); border: 1px solid var(--border-light);
        overflow: hidden; margin-bottom: 1.25rem;
        animation: fadeUp 0.45s ease both;
    }
    .inv-filter-header {
        display: flex; align-items: center; padding: 1rem 1.25rem;
        border-bottom: 1px solid var(--border-light);
        background: linear-gradient(135deg, var(--blue-deeper), var(--blue-dark));
        gap: 0.6rem;
    }
    .inv-filter-header .fh-icon {
        width: 28px; height: 28px; border-radius: 8px;
        background: rgba(255,255,255,0.15); display: flex; align-items: center;
        justify-content: center; font-size: 0.75rem; color: white; flex-shrink: 0;
    }
    .inv-filter-header h3 { font-size: 0.9rem; font-weight: 700; color: white; margin: 0; }
    .inv-filter-body { padding: 1.2rem 1.25rem 0.5rem; }
    .inv-filter-form {
        display: flex; align-items: flex-end; gap: 0.75rem;
        flex-wrap: nowrap; overflow-x: auto; padding-bottom: 0.2rem;
    }
    .inv-filter-form::-webkit-scrollbar { height: 7px; }
    .inv-filter-form::-webkit-scrollbar-thumb { background: rgba(148,163,184,0.6); border-radius: 999px; }

    .inv-form-group { margin-bottom: 1rem; min-width: 130px; flex: 1 0 130px; }
    .inv-form-group.is-wide { min-width: 250px; flex-basis: 250px; }
    .inv-form-group.is-mid { min-width: 180px; flex-basis: 180px; }
    .inv-form-group.is-narrow { min-width: 110px; flex: 0 0 110px; }
    .inv-label {
        display: flex; align-items: center; gap: 0.3rem;
        font-size: 0.7rem; font-weight: 700; text-transform: uppercase;
        letter-spacing: 0.07em; color: var(--text-muted); margin-bottom: 0.4rem;
    }
    .inv-label i { font-size: 0.62rem; color: var(--blue-primary); }
    .inv-control {
        width: 100%; border: 1.5px solid var(--border-table); border-radius: var(--radius-sm);
        padding: 0.5rem 0.75rem; font-size: 0.83rem; font-family: 'Plus Jakarta Sans', sans-serif;
        color: var(--text-primary); background: white; transition: all 0.2s;
        appearance: none; -webkit-appearance: none;
    }
    .inv-control:focus { outline: none; border-color: var(--blue-primary); box-shadow: 0 0 0 3px rgba(37,99,235,0.12); }
    select.inv-control {
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%2394a3b8' stroke-width='2.5'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
        background-repeat: no-repeat; background-position: right 0.75rem center; padding-right: 2rem;
    }

    .inv-filter-actions {
        display: flex; align-items: flex-end; gap: 0.6rem; padding-bottom: 1rem;
        flex: 0 0 auto; margin-left: auto; white-space: nowrap;
    }
    .btn-apply {
        display: inline-flex; align-items: center; gap: 0.4rem;
        background: linear-gradient(135deg, var(--blue-primary), var(--blue-mid));
        color: white; font-size: 0.82rem; font-weight: 700;
        padding: 0.55rem 1.2rem; border-radius: var(--radius-sm);
        border: none; cursor: pointer; transition: all 0.2s;
        box-shadow: 0 3px 10px rgba(37,99,235,0.3); font-family: inherit;
    }
    .btn-apply:hover { transform: translateY(-1px); box-shadow: 0 5px 18px rgba(37,99,235,0.4); }
    .btn-reset {
        display: inline-flex; align-items: center; gap: 0.4rem;
        background: white; border: 1.5px solid var(--border-table);
        color: var(--text-secondary); font-size: 0.82rem; font-weight: 600;
        padding: 0.5rem 1rem; border-radius: var(--radius-sm);
        text-decoration: none; transition: all 0.2s;
    }
    .btn-reset:hover { border-color: var(--blue-light); color: var(--text-primary); text-decoration: none; }

    .inv-table-card {
        background: white; border-radius: var(--radius-lg);
        box-shadow: var(--shadow-md); border: 1px solid var(--border-light);
        overflow: hidden; animation: fadeUp 0.55s ease both;
    }
    .inv-table-header {
        display: flex; align-items: center; justify-content: space-between;
        padding: 1rem 1.25rem; border-bottom: 1px solid var(--border-light);
    }
    .inv-table-title {
        display: flex; align-items: center; gap: 0.6rem;
        font-size: 0.9rem; font-weight: 700; color: var(--text-primary); margin: 0;
    }
    .inv-table-title .tt-icon {
        width: 28px; height: 28px; border-radius: 8px;
        background: rgba(37,99,235,0.1); display: flex; align-items: center;
        justify-content: center; font-size: 0.7rem; color: var(--blue-primary);
    }

    .inv-grid-wrap { padding: 1rem 1.25rem 1.25rem; }
    .inv-grid {
        display: grid; gap: 1rem;
        grid-template-columns: repeat(1, minmax(0, 1fr));
    }
    @media (min-width: 768px) {
        .inv-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    }
    @media (min-width: 1200px) {
        .inv-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); }
    }
    .inv-item-card {
        min-height: 205px;
        border: 1px solid #d9e1ee;
        border-radius: var(--radius-md);
        background: #f8fafc;
        padding: 0.95rem 0.95rem 0.9rem;
        display: flex; flex-direction: column; gap: 0.7rem;
        transition: all 0.2s ease;
    }
    .inv-item-card:hover {
        transform: translateY(-2px);
        box-shadow: var(--shadow-sm);
        border-color: #c5d3eb;
    }
    .inv-item-top {
        display: flex; align-items: flex-start; justify-content: space-between; gap: 0.65rem;
    }
    .inv-item-title {
        font-size: 1rem; line-height: 1.3; font-weight: 700;
        color: #4e5a97; text-decoration: none;
        display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;
    }
    .inv-item-title:hover { color: var(--blue-primary); text-decoration: none; }
    .inv-item-menu-btn {
        width: 28px; height: 28px; border-radius: 50%;
        border: none; background: transparent; color: #4a5568;
        display: inline-flex; align-items: center; justify-content: center;
        cursor: pointer; padding: 0;
    }
    .inv-item-menu-btn::after { display: none; }
    .inv-item-menu-btn:hover { background: rgba(37,99,235,0.1); color: var(--blue-primary); }
    .inv-item-menu {
        border-radius: 10px; border: 1px solid var(--border-table);
        box-shadow: var(--shadow-sm); font-size: 0.8rem;
    }
    .inv-item-sub {
        display: flex; align-items: center; justify-content: space-between; gap: 0.5rem;
        font-size: 0.73rem; color: var(--text-muted);
    }
    .inv-item-sub .mono {
        font-family: 'DM Mono', monospace;
        color: #64748b;
    }
    .inv-item-tags { display: flex; align-items: center; gap: 0.45rem; flex-wrap: wrap; }
    .inv-item-amounts {
        display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 0.55rem;
    }
    .inv-item-amount {
        border: 1px solid var(--border-table);
        border-radius: 9px; padding: 0.45rem 0.55rem;
        background: #fff;
        display: flex; flex-direction: column; gap: 0.1rem;
    }
    .inv-item-amount span { font-size: 0.67rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: .05em; }
    .inv-item-amount strong { font-size: 0.79rem; font-weight: 700; }
    .inv-item-amount.debit strong { color: var(--blue-primary); }
    .inv-item-amount.credit strong { color: var(--accent-red); }
    .inv-item-footer {
        margin-top: auto;
        display: flex; align-items: center; justify-content: space-between; gap: 0.6rem;
    }
    .inv-item-main-btn {
        display: inline-flex; align-items: center; gap: 0.4rem;
        background: #6d5cae; color: #fff; border: none;
        border-radius: 8px; padding: 0.45rem 0.78rem;
        font-size: 0.78rem; font-weight: 700; text-decoration: none;
    }
    .inv-item-main-btn:hover { color: #fff; text-decoration: none; background: #5a4a9a; }
    .inv-item-main-btn i { font-size: 0.68rem; }

    .inv-table { width: 100%; border-collapse: collapse; font-size: 0.82rem; }
    .inv-table th {
        background: #f8fafc; color: var(--text-muted);
        font-size: 0.67rem; font-weight: 700; text-transform: uppercase;
        letter-spacing: 0.07em; padding: 0.7rem 0.9rem;
        border-bottom: 2px solid var(--border-table); white-space: nowrap;
    }
    .inv-table td {
        padding: 0.65rem 0.9rem; border-bottom: 1px solid var(--border-table);
        color: var(--text-secondary); vertical-align: middle;
    }
    .inv-table tbody tr:last-child td { border-bottom: none; }
    .inv-table tbody tr:hover td { background: rgba(37,99,235,0.025); }

    .cell-no {
        font-size: 0.72rem; color: var(--text-muted);
        font-family: 'Plus Jakarta Sans', sans-serif; text-align: center;
    }

    .inv-no-link {
        font-family: 'Plus Jakarta Sans', sans-serif; font-size: 0.8rem; font-weight: 700;
        color: var(--blue-primary); text-decoration: none; letter-spacing: 0;
        transition: color 0.15s;
    }
    .inv-no-link:hover { color: var(--blue-dark); text-decoration: underline; }

    .cell-date {
        font-family: 'Plus Jakarta Sans', sans-serif;
        font-size: 0.78rem; color: var(--text-secondary); white-space: nowrap;
    }

    .cell-amount {
        font-family: 'Plus Jakarta Sans', sans-serif;
        font-size: 0.82rem; font-weight: 400; text-align: right; white-space: nowrap;
    }
    .cell-amount.debit  { color: var(--blue-primary); }
    .cell-amount.credit { color: var(--accent-red); }

    .badge-type {
        display: inline-flex; align-items: center; gap: 0.3rem;
        border-radius: 999px; padding: 0.22rem 0.65rem;
        font-size: 0.68rem; font-weight: 700; letter-spacing: 0.04em; white-space: nowrap;
    }
    .badge-income  { background: rgba(16,185,129,0.1);  color: #065f46; }
    .badge-expense { background: rgba(239,68,68,0.1);   color: #991b1b; }

    .badge-status {
        display: inline-flex; align-items: center; gap: 0.28rem;
        border-radius: 999px; padding: 0.22rem 0.65rem;
        font-size: 0.68rem; font-weight: 700; letter-spacing: 0.04em; white-space: nowrap;
    }
    .badge-posted    { background: rgba(16,185,129,0.1);  color: #065f46; }
    .badge-draft     { background: rgba(245,158,11,0.1);  color: #92400e; }
    .badge-cancelled { background: rgba(100,116,139,0.1); color: #475569; }

    .action-group { display: flex; align-items: center; gap: 0.35rem; flex-wrap: wrap; }
    .btn-act {
        display: inline-flex; align-items: center; gap: 0.25rem;
        border-radius: 7px; font-size: 0.72rem; font-weight: 700;
        padding: 0.3rem 0.65rem; text-decoration: none; transition: all 0.18s;
        border: 1.5px solid transparent; cursor: pointer; white-space: nowrap;
        font-family: 'Plus Jakarta Sans', sans-serif;
    }
    .btn-act i { font-size: 0.62rem; }
    .btn-act-detail { background: rgba(37,99,235,0.08); color: var(--blue-primary); border-color: rgba(37,99,235,0.2); }
    .btn-act-detail:hover { background: var(--blue-primary); color: white; border-color: var(--blue-primary); text-decoration: none; }
    .btn-act-pdf    { background: rgba(239,68,68,0.08); color: #991b1b; border-color: rgba(239,68,68,0.22); }
    .btn-act-pdf:hover { background: var(--accent-red); color: white; border-color: var(--accent-red); text-decoration: none; }
    .btn-act-excel  { background: rgba(16,185,129,0.10); color: #065f46; border-color: rgba(16,185,129,0.25); }
    .btn-act-excel:hover { background: var(--accent-green); color: white; border-color: var(--accent-green); text-decoration: none; }
    .btn-act-edit   { background: rgba(245,158,11,0.08); color: #92400e; border-color: rgba(245,158,11,0.25); }
    .btn-act-edit:hover { background: var(--accent-amber); color: white; border-color: var(--accent-amber); text-decoration: none; }
    .btn-act-delete { background: rgba(239,68,68,0.08); color: #991b1b; border-color: rgba(239,68,68,0.2); }
    .btn-act-delete:hover { background: var(--accent-red); color: white; border-color: var(--accent-red); }
    .btn-act-delete[type="submit"] { background: rgba(239,68,68,0.08); color: #991b1b; border-color: rgba(239,68,68,0.2); }
    .btn-act-delete[type="submit"]:hover { background: var(--accent-red); color: white; border-color: var(--accent-red); }

    .creator-cell { display: flex; align-items: center; gap: 0.45rem; }
    .creator-avatar {
        width: 26px; height: 26px; border-radius: 50%; flex-shrink: 0;
        background: linear-gradient(135deg, var(--blue-primary), var(--blue-light));
        display: flex; align-items: center; justify-content: center;
        color: white; font-size: 0.62rem; font-weight: 800;
    }
    .creator-name { font-size: 0.8rem; font-weight: 600; color: var(--text-primary); }

    .inv-empty-state {
        grid-column: 1 / -1;
        padding: 3rem 1rem; text-align: center;
        border: 1px dashed var(--border-table);
        border-radius: var(--radius-md);
        background: #fff;
    }
    .inv-empty-icon {
        width: 56px; height: 56px; border-radius: var(--radius-md);
        background: rgba(37,99,235,0.07); display: flex; align-items: center;
        justify-content: center; font-size: 1.4rem; color: var(--text-muted);
        margin: 0 auto 1rem;
    }
    .inv-empty-text { font-size: 0.85rem; color: var(--text-muted); font-weight: 500; }
    .inv-table-footer {
        padding: 0.75rem 1.25rem; border-top: 1px solid var(--border-light);
        background: #fafbff;
    }
    .inv-table-footer .pagination { margin: 0; }
    .inv-table-footer .page-link {
        border-radius: 8px; font-size: 0.78rem; font-weight: 600;
        color: var(--text-secondary); border-color: var(--border-table); margin: 0 1px;
    }
    .inv-table-footer .page-item.active .page-link {
        background: var(--blue-primary); border-color: var(--blue-primary); color: white;
    }

    .cell-journal { font-size: 0.8rem; font-weight: 600; color: var(--text-primary); max-width: 160px; }
    .cell-ref     { font-size: 0.78rem; color: var(--text-muted); font-family: 'Plus Jakarta Sans', sans-serif; }

    @keyframes fadeUp   { from { opacity:0; transform:translateY(14px); } to { opacity:1; transform:translateY(0); } }
    @keyframes fadeDown { from { opacity:0; transform:translateY(-10px); } to { opacity:1; transform:translateY(0); } }
</style>

@php
    $filters = $filters ?? [];
    $journalOptions = $journalOptions ?? [];
@endphp

<div class="inv-page-header">
    <div class="inv-header-left">
        <div class="inv-header-icon"><i class="fas fa-file-invoice"></i></div>
        <div>
            <h1 class="inv-header-title">Faktur / Entri Jurnal</h1>
            <p class="inv-header-sub">Manajemen Invoice &amp; Jurnal Keuangan</p>
        </div>
    </div>
    <a href="{{ route('finance.invoice.create') }}" class="btn-inv-new">
        <i class="fas fa-plus"></i> Buat Faktur Baru
    </a>
</div>

<div class="inv-filter-card">
    <div class="inv-filter-header">
        <span class="fh-icon"><i class="fas fa-sliders-h"></i></span>
        <h3>Filter Faktur &amp; Jurnal</h3>
    </div>
    <div class="inv-filter-body">
        <form method="GET" action="{{ route('finance.invoice.index') }}">
            <div class="inv-filter-form">
                <div class="inv-form-group is-wide">
                    <label class="inv-label"><i class="fas fa-search"></i> Cari</label>
                    <input type="text" name="q" id="q" class="inv-control"
                        placeholder="No faktur / jurnal / referensi"
                        value="{{ $filters['q'] ?? '' }}">
                </div>
                <div class="inv-form-group">
                    <label class="inv-label"><i class="fas fa-toggle-on"></i> Status</label>
                    <select name="status" id="status" class="inv-control">
                        <option value="ALL"       {{ ($filters['status'] ?? 'ALL') === 'ALL'       ? 'selected' : '' }}>Semua</option>
                        <option value="DRAFT"     {{ ($filters['status'] ?? '') === 'DRAFT'         ? 'selected' : '' }}>Draft</option>
                        <option value="POSTED"    {{ ($filters['status'] ?? '') === 'POSTED'        ? 'selected' : '' }}>Terekam</option>
                        <option value="CANCELLED" {{ ($filters['status'] ?? '') === 'CANCELLED'     ? 'selected' : '' }}>Batal</option>
                    </select>
                </div>
                <div class="inv-form-group">
                    <label class="inv-label"><i class="fas fa-tags"></i> Jenis</label>
                    <select name="entry_type" id="entry_type" class="inv-control">
                        <option value="ALL"     {{ ($filters['entry_type'] ?? 'ALL') === 'ALL'     ? 'selected' : '' }}>Semua</option>
                        <option value="INCOME"  {{ ($filters['entry_type'] ?? '') === 'INCOME'     ? 'selected' : '' }}>Pemasukan</option>
                        <option value="EXPENSE" {{ ($filters['entry_type'] ?? '') === 'EXPENSE'    ? 'selected' : '' }}>Pengeluaran</option>
                    </select>
                </div>
                <div class="inv-form-group is-mid">
                    <label class="inv-label"><i class="fas fa-book"></i> Jurnal</label>
                    <select name="journal_name" id="journal_name" class="inv-control">
                        <option value="">Semua Jurnal</option>
                        @foreach($journalOptions as $journalOption)
                            <option value="{{ $journalOption }}" {{ ($filters['journal_name'] ?? '') === $journalOption ? 'selected' : '' }}>
                                {{ $journalOption }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="inv-form-group is-mid">
                    <label class="inv-label"><i class="fas fa-calendar-day"></i> Harian</label>
                    <input type="date" name="accounting_date" id="accounting_date" class="inv-control"
                        value="{{ $filters['accounting_date'] ?? '' }}">
                </div>
                <div class="inv-form-group is-narrow">
                    <label class="inv-label"><i class="fas fa-calendar-week"></i> Bulan</label>
                    <select name="month" id="month" class="inv-control">
                        <option value="">-</option>
                        @for($m = 1; $m <= 12; $m++)
                            <option value="{{ $m }}" {{ (int) ($filters['month'] ?? 0) === $m ? 'selected' : '' }}>
                                {{ sprintf('%02d', $m) }}
                            </option>
                        @endfor
                    </select>
                </div>
                <div class="inv-form-group is-narrow">
                    <label class="inv-label"><i class="fas fa-calendar"></i> Tahun</label>
                    <input type="number" name="year" id="year" class="inv-control"
                        min="1900" max="2100" value="{{ $filters['year'] ?? '' }}">
                </div>
                <div class="inv-form-group is-narrow">
                    <label class="inv-label"><i class="fas fa-list-ol"></i> Per Halaman</label>
                    <select name="per_page" id="per_page" class="inv-control">
                        @foreach([10, 15, 25, 50, 100] as $size)
                            <option value="{{ $size }}" {{ (int) ($filters['per_page'] ?? 15) === $size ? 'selected' : '' }}>
                                {{ $size }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="inv-filter-actions">
                    <button type="submit" class="btn-apply">
                        <i class="fas fa-filter"></i> Terapkan Filter
                    </button>
                    <a href="{{ route('finance.invoice.index') }}" class="btn-reset">
                        <i class="fas fa-sync"></i> Reset
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="inv-table-card">
    <div class="inv-table-header">
        <h3 class="inv-table-title">
            <span class="tt-icon"><i class="fas fa-receipt"></i></span>
            Daftar Faktur / Entri Jurnal
        </h3>
        <span style="font-size:0.75rem;color:var(--text-muted);font-weight:600;">
            Total: <strong style="color:var(--text-primary);">{{ $invoices->total() }}</strong> entri
        </span>
    </div>

    <div class="inv-grid-wrap">
        <div class="inv-grid">
            @forelse($invoices as $invoice)
                @php
                    $status = strtoupper((string) $invoice->status);
                    $statusClass = match($status) {
                        'POSTED'    => 'badge-posted',
                        'CANCELLED' => 'badge-cancelled',
                        default     => 'badge-draft',
                    };
                    $statusIcon = match($status) {
                        'POSTED'    => 'fa-check-circle',
                        'CANCELLED' => 'fa-times-circle',
                        default     => 'fa-clock',
                    };
                    $creatorName = $invoice->creator?->name ?? '-';
                    $creatorInitial = strtoupper(substr($creatorName, 0, 1));
                @endphp

                <div class="inv-item-card">
                    <div class="inv-item-top">
                        <a href="{{ route('finance.invoice.show', $invoice->id) }}" class="inv-item-title">
                            {{ $invoice->journal_name ?: $invoice->invoice_no }}
                        </a>
                        <div class="dropdown">
                            <button type="button" class="inv-item-menu-btn dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="fas fa-ellipsis-v"></i>
                            </button>
                            <div class="dropdown-menu dropdown-menu-right inv-item-menu">
                                <a class="dropdown-item" href="{{ route('finance.invoice.show', $invoice->id) }}">
                                    <i class="fas fa-eye mr-2"></i>Detail
                                </a>
                                <a class="dropdown-item" href="{{ route('finance.invoice.download', ['invoice' => $invoice->id, 'format' => 'pdf']) }}">
                                    <i class="fas fa-file-pdf mr-2"></i>Download PDF
                                </a>
                                <a class="dropdown-item" href="{{ route('finance.invoice.download', ['invoice' => $invoice->id, 'format' => 'excel']) }}">
                                    <i class="fas fa-file-excel mr-2"></i>Download Excel
                                </a>
                                <a class="dropdown-item" href="{{ route('finance.invoice.edit', $invoice->id) }}">
                                    <i class="fas fa-pen mr-2"></i>Edit
                                </a>
                                @if($invoice->status !== 'POSTED')
                                    <div class="dropdown-divider"></div>
                                    <form action="{{ route('finance.invoice.destroy', $invoice->id) }}" method="POST" onsubmit="return confirm('Hapus faktur ini?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="dropdown-item text-danger">
                                            <i class="fas fa-trash mr-2"></i>Hapus
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="inv-item-sub">
                        <span class="mono">{{ $invoice->invoice_no }}</span>
                        <span>{{ optional($invoice->accounting_date)->format('d/m/Y') ?? '-' }}</span>
                    </div>

                    <div class="inv-item-tags">
                        @if($invoice->entry_type === 'INCOME')
                            <span class="badge-type badge-income">
                                <i class="fas fa-arrow-up" style="font-size:.55rem;"></i> Pemasukan
                            </span>
                        @else
                            <span class="badge-type badge-expense">
                                <i class="fas fa-arrow-down" style="font-size:.55rem;"></i> Pengeluaran
                            </span>
                        @endif
                        <span class="badge-status {{ $statusClass }}">
                            <i class="fas {{ $statusIcon }}" style="font-size:.55rem;"></i>
                            {{ $status }}
                        </span>
                    </div>

                    <div class="inv-item-amounts">
                        <div class="inv-item-amount debit">
                            <span>Debit</span>
                            <strong>Rp {{ number_format((float) $invoice->total_debit, 2, ',', '.') }}</strong>
                        </div>
                        <div class="inv-item-amount credit">
                            <span>Kredit</span>
                            <strong>Rp {{ number_format((float) $invoice->total_credit, 2, ',', '.') }}</strong>
                        </div>
                    </div>

                    <div class="inv-item-footer">
                        <a href="{{ route('finance.invoice.show', $invoice->id) }}" class="inv-item-main-btn">
                            <i class="fas fa-folder-open"></i> Lihat Entri
                        </a>
                        <div class="creator-cell">
                            <div class="creator-avatar">{{ $creatorInitial }}</div>
                            <span class="creator-name">{{ $creatorName }}</span>
                        </div>
                    </div>
                </div>
            @empty
                <div class="inv-empty-state">
                    <div class="inv-empty-icon"><i class="fas fa-file-invoice"></i></div>
                    <div class="inv-empty-text">Belum ada data faktur / jurnal.</div>
                </div>
            @endforelse
        </div>
    </div>
    @if($invoices->hasPages())
        <div class="inv-table-footer">
            {{ $invoices->links() }}
        </div>
    @endif
</div>
@endsection
