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
        --accent-green: #10b981;
        --accent-red: #ef4444;
        --accent-amber: #f59e0b;
        --accent-purple: #8b5cf6;
        --surface-bg: #f0f4fd;
        --surface-card: #ffffff;
        --text-primary: #0f172a;
        --text-secondary: #475569;
        --text-muted: #94a3b8;
        --border-light: rgba(37,99,235,0.10);
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
    .ivd-page-header {
        display: flex; align-items: center; justify-content: space-between;
        margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;
        animation: fadeDown 0.4s ease both;
    }
    .ivd-header-left { display: flex; align-items: center; gap: 0.9rem; }
    .ivd-header-icon {
        width: 48px; height: 48px; border-radius: var(--radius-sm);
        background: linear-gradient(135deg, var(--blue-primary), var(--blue-light));
        display: flex; align-items: center; justify-content: center;
        color: white; font-size: 1.2rem; box-shadow: var(--shadow-md); flex-shrink: 0;
    }
    .ivd-header-title { font-size: 1.3rem; font-weight: 700; color: var(--text-primary); margin: 0; letter-spacing: -0.01em; line-height: 1.2; font-family: 'Plus Jakarta Sans', sans-serif; }
    .ivd-header-sub { font-size: 0.8rem; color: var(--text-muted); margin: 0.1rem 0 0; font-weight: 500; font-family: 'Plus Jakarta Sans', sans-serif; }
    .ivd-header-actions { display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap; }

    /* ── Action Buttons ───────────────────────── */
    .btn-ivd {
        display: inline-flex; align-items: center; gap: 0.38rem;
        border-radius: var(--radius-sm); font-size: 0.82rem; font-weight: 700;
        padding: 0.52rem 1rem; text-decoration: none; transition: all 0.2s;
        border: 1.5px solid transparent; cursor: pointer; white-space: nowrap;
        font-family: 'Plus Jakarta Sans', sans-serif;
    }
    .btn-ivd i { font-size: 0.68rem; }
    .btn-ivd-back   { background: white; border-color: var(--border-table); color: var(--text-secondary); box-shadow: var(--shadow-sm); }
    .btn-ivd-back:hover { border-color: var(--blue-light); color: var(--text-primary); text-decoration: none; }
    .btn-ivd-edit   { background: rgba(245,158,11,0.09); border-color: rgba(245,158,11,0.28); color: #92400e; }
    .btn-ivd-edit:hover { background: var(--accent-amber); color: white; border-color: var(--accent-amber); text-decoration: none; }
    .btn-ivd-post   { background: linear-gradient(135deg, var(--blue-primary), var(--blue-mid)); color: white; border-color: transparent; box-shadow: 0 3px 10px rgba(37,99,235,0.3); }
    .btn-ivd-post:hover { transform: translateY(-1px); box-shadow: 0 6px 18px rgba(37,99,235,0.4); }
    .btn-ivd-delete { background: rgba(239,68,68,0.08); border-color: rgba(239,68,68,0.2); color: #991b1b; }
    .btn-ivd-delete:hover { background: var(--accent-red); color: white; border-color: var(--accent-red); }
    .btn-ivd-reset  { background: rgba(245,158,11,0.09); border-color: rgba(245,158,11,0.28); color: #92400e; }
    .btn-ivd-reset:hover { background: var(--accent-amber); color: white; border-color: var(--accent-amber); }

    /* ── Alert ────────────────────────────────── */
    .ivd-alert {
        display: flex; align-items: flex-start; gap: 0.75rem;
        border-radius: var(--radius-md); padding: 0.9rem 1.1rem;
        margin-bottom: 1.25rem; font-size: 0.83rem; font-weight: 500;
        border: 1px solid transparent; animation: fadeUp 0.4s ease both;
    }
    .ivd-alert.danger { background: rgba(239,68,68,0.07); border-color: rgba(239,68,68,0.2); color: #991b1b; }
    .ivd-alert .al-icon {
        width: 28px; height: 28px; border-radius: 8px; flex-shrink: 0;
        display: flex; align-items: center; justify-content: center; font-size: 0.78rem;
        background: rgba(239,68,68,0.12); color: var(--accent-red);
    }
    .ivd-alert ul { margin: 0.4rem 0 0; padding-left: 1.2rem; }
    .ivd-alert li  { margin-bottom: 0.15rem; }

    /* ── Main Card ────────────────────────────── */
    .ivd-main-card {
        background: white; border-radius: var(--radius-lg);
        box-shadow: var(--shadow-md); border: 1px solid var(--border-light);
        overflow: hidden; animation: fadeUp 0.5s ease both;
    }

    /* ── Summary Strip ────────────────────────── */
    .ivd-summary-strip {
        display: grid; grid-template-columns: repeat(4, 1fr);
        border-bottom: 1px solid var(--border-light);
    }
    @media(max-width:768px) { .ivd-summary-strip { grid-template-columns: repeat(2,1fr); } }
    .ivd-sum-item {
        padding: 1rem 1.25rem; border-right: 1px solid var(--border-light);
        position: relative; overflow: hidden;
    }
    .ivd-sum-item:last-child { border-right: none; }
    .ivd-sum-item::before {
        content: ''; position: absolute; top: 0; left: 0; right: 0;
        height: 3px; border-radius: 0;
    }
    .ivd-sum-item.si-debit::before   { background: linear-gradient(90deg, var(--blue-primary), var(--blue-light)); }
    .ivd-sum-item.si-credit::before  { background: linear-gradient(90deg, var(--accent-red), #f87171); }
    .ivd-sum-item.si-creator::before { background: linear-gradient(90deg, var(--accent-green), #34d399); }
    .ivd-sum-item.si-posted::before  { background: linear-gradient(90deg, var(--accent-purple), #a78bfa); }
    .ivd-sum-label {
        font-size: 0.67rem; font-weight: 700; text-transform: uppercase;
        letter-spacing: 0.08em; color: var(--text-muted); margin-bottom: 0.3rem;
        display: flex; align-items: center; gap: 0.3rem;
    }
    .ivd-sum-value {
        font-size: 0.95rem; font-weight: 500; color: var(--text-primary);
        font-family: 'Plus Jakarta Sans', sans-serif; line-height: 1.2;
    }
    .ivd-sum-value.blue   { color: var(--blue-primary); }
    .ivd-sum-value.red    { color: var(--accent-red); }
    .ivd-sum-sub { font-size: 0.71rem; color: var(--text-muted); margin-top: 0.15rem; }

    /* ── Meta Grid ────────────────────────────── */
    .ivd-meta-grid {
        display: grid; grid-template-columns: 1fr 1fr;
        gap: 0; border-bottom: 1px solid var(--border-light);
    }
    @media(max-width: 640px) { .ivd-meta-grid { grid-template-columns: 1fr; } }
    .ivd-meta-col { padding: 1.25rem 1.4rem; }
    .ivd-meta-col:first-child { border-right: 1px solid var(--border-light); }
    .ivd-meta-row {
        display: flex; align-items: flex-start; gap: 0.5rem;
        padding: 0.45rem 0; border-bottom: 1px solid rgba(0,0,0,0.04);
    }
    .ivd-meta-row:last-child { border-bottom: none; }
    .ivd-meta-key {
        font-size: 0.72rem; font-weight: 700; color: var(--text-muted);
        min-width: 145px; flex-shrink: 0; display: flex; align-items: center;
        gap: 0.3rem; padding-top: 0.05rem;
    }
    .ivd-meta-key i { font-size: 0.6rem; color: var(--blue-primary); }
    .ivd-meta-val { font-size: 0.83rem; font-weight: 600; color: var(--text-primary); }
    .ivd-meta-val.mono { font-family: 'Plus Jakarta Sans', sans-serif; font-size: 0.83rem; font-weight: 500; }

    /* Badges */
    .badge-ivd {
        display: inline-flex; align-items: center; gap: 0.3rem;
        border-radius: 999px; padding: 0.22rem 0.65rem;
        font-size: 0.7rem; font-weight: 800; letter-spacing: 0.05em;
    }
    .badge-posted-ivd    { background: rgba(16,185,129,0.1);  color: #065f46; }
    .badge-draft-ivd     { background: rgba(245,158,11,0.1);  color: #92400e; }
    .badge-cancelled-ivd { background: rgba(100,116,139,0.1); color: #475569; }
    .badge-income-ivd    { background: rgba(16,185,129,0.1);  color: #065f46; }
    .badge-expense-ivd   { background: rgba(239,68,68,0.1);   color: #991b1b; }

    /* ── Tabs ─────────────────────────────────── */
    .ivd-tabs-wrap { padding: 1.1rem 1.4rem 0; border-bottom: 2px solid var(--border-table); }
    .ivd-tabs { display: flex; gap: 0; list-style: none; margin: 0; padding: 0; }
    .ivd-tab-link {
        display: inline-flex; align-items: center; gap: 0.4rem;
        padding: 0.6rem 1.1rem; font-size: 0.83rem; font-weight: 700;
        color: var(--text-muted); text-decoration: none; border-bottom: 2.5px solid transparent;
        margin-bottom: -2px; transition: all 0.2s; cursor: pointer; white-space: nowrap;
        border-radius: var(--radius-sm) var(--radius-sm) 0 0;
    }
    .ivd-tab-link:hover { color: var(--blue-primary); text-decoration: none; background: rgba(37,99,235,0.04); }
    .ivd-tab-link.active { color: var(--blue-primary); border-bottom-color: var(--blue-primary); background: rgba(37,99,235,0.05); }
    .ivd-tab-link .tab-badge {
        background: rgba(37,99,235,0.12); color: var(--blue-primary);
        font-size: 0.65rem; font-weight: 800; padding: 0.1rem 0.45rem;
        border-radius: 999px; min-width: 20px; text-align: center;
    }
    .ivd-tab-link.active .tab-badge { background: var(--blue-primary); color: white; }

    /* ── Tab Content ──────────────────────────── */
    .ivd-tab-content { padding: 1.25rem 1.4rem; }
    .ivd-tab-pane { display: none; }
    .ivd-tab-pane.active { display: block; animation: fadeUp 0.3s ease both; }

    /* ── Items Table ──────────────────────────── */
    .ivd-items-wrap {
        border: 1.5px solid var(--border-table); border-radius: var(--radius-md); overflow: hidden;
    }
    .ivd-items-table { width: 100%; border-collapse: collapse; font-size: 0.81rem; }
    .ivd-items-table th {
        background: #f8fafc; color: var(--text-muted); font-size: 0.67rem;
        font-weight: 700; text-transform: uppercase; letter-spacing: 0.07em;
        padding: 0.65rem 0.9rem; border-bottom: 1.5px solid var(--border-table); white-space: nowrap;
    }
    .ivd-items-table td {
        padding: 0.6rem 0.9rem; border-bottom: 1px solid var(--border-table);
        color: var(--text-secondary); vertical-align: middle;
    }
    .ivd-items-table tbody tr:last-child td { border-bottom: none; }
    .ivd-items-table tbody tr:hover td { background: rgba(37,99,235,0.025); }
    .ivd-items-table tfoot th {
        background: #f0f4fd; border-top: 2px solid var(--border-table);
        font-size: 0.78rem; padding: 0.65rem 0.9rem; color: var(--text-primary);
    }
    .cell-no-sm  { font-size: 0.72rem; color: var(--text-muted); font-family: 'Plus Jakarta Sans', sans-serif; text-align: center; }
    .cell-code   { font-family: 'Plus Jakarta Sans', sans-serif; font-size: 0.78rem; color: var(--text-primary); font-weight: 500; }
    .cell-muted  { color: var(--text-muted); font-size: 0.78rem; }
    .cell-amount-th { text-align: right; font-family: 'Plus Jakarta Sans', sans-serif; font-size: 0.82rem; font-weight: 700; }
    .cell-amount-th.blue { color: var(--blue-primary); }
    .cell-amount-th.red  { color: var(--accent-red); }
    .cell-amount-td { text-align: right; font-family: 'Plus Jakarta Sans', sans-serif; font-size: 0.82rem; font-weight: 400; color: var(--text-primary); white-space: nowrap; }
    .empty-row td { text-align: center; color: var(--text-muted); font-size: 0.82rem; font-style: italic; padding: 2rem; }

    /* ── Notes ────────────────────────────────── */
    .ivd-note-form-card {
        background: rgba(37,99,235,0.03); border: 1.5px solid var(--border-light);
        border-radius: var(--radius-md); padding: 1.1rem; margin-bottom: 1.25rem;
    }
    .ivd-note-label {
        font-size: 0.7rem; font-weight: 700; text-transform: uppercase;
        letter-spacing: 0.07em; color: var(--text-muted); margin-bottom: 0.45rem;
        display: flex; align-items: center; gap: 0.3rem;
    }
    .ivd-note-label i { font-size: 0.62rem; color: var(--blue-primary); }
    .ivd-note-textarea {
        width: 100%; border: 1.5px solid var(--border-table); border-radius: var(--radius-sm);
        padding: 0.6rem 0.85rem; font-size: 0.83rem; font-family: 'Plus Jakarta Sans', sans-serif;
        color: var(--text-primary); background: white; transition: all 0.2s;
        resize: vertical; min-height: 80px; margin-bottom: 0.75rem;
    }
    .ivd-note-textarea:focus { outline: none; border-color: var(--blue-primary); box-shadow: 0 0 0 3px rgba(37,99,235,0.12); }
    .btn-ivd-note {
        display: inline-flex; align-items: center; gap: 0.4rem;
        background: linear-gradient(135deg, var(--blue-primary), var(--blue-mid));
        color: white; font-size: 0.82rem; font-weight: 700;
        padding: 0.52rem 1.1rem; border-radius: var(--radius-sm);
        border: none; cursor: pointer; font-family: inherit;
        transition: all 0.2s; box-shadow: 0 3px 10px rgba(37,99,235,0.3);
    }
    .btn-ivd-note:hover { transform: translateY(-1px); box-shadow: 0 5px 18px rgba(37,99,235,0.4); }

    /* Note list */
    .ivd-notes-list { display: flex; flex-direction: column; gap: 0.75rem; }
    .ivd-note-item {
        background: white; border: 1.5px solid var(--border-light);
        border-radius: var(--radius-md); padding: 0.9rem 1.1rem;
        transition: box-shadow 0.2s;
    }
    .ivd-note-item:hover { box-shadow: var(--shadow-sm); }
    .ivd-note-header {
        display: flex; align-items: center; justify-content: space-between;
        margin-bottom: 0.55rem; flex-wrap: wrap; gap: 0.4rem;
    }
    .ivd-note-user { display: flex; align-items: center; gap: 0.5rem; }
    .ivd-note-avatar {
        width: 28px; height: 28px; border-radius: 50%; flex-shrink: 0;
        background: linear-gradient(135deg, var(--blue-primary), var(--blue-light));
        display: flex; align-items: center; justify-content: center;
        color: white; font-size: 0.65rem; font-weight: 800;
    }
    .ivd-note-name { font-size: 0.82rem; font-weight: 700; color: var(--text-primary); }
    .ivd-note-role {
        font-size: 0.7rem; font-weight: 600; color: var(--text-muted);
        background: rgba(37,99,235,0.07); padding: 0.1rem 0.45rem; border-radius: 999px;
    }
    .ivd-note-time { font-size: 0.72rem; color: var(--text-muted); font-family: 'Plus Jakarta Sans', sans-serif; }
    .ivd-note-body { font-size: 0.83rem; color: var(--text-secondary); line-height: 1.55; }
    .ivd-notes-empty {
        text-align: center; padding: 2rem 1rem; color: var(--text-muted);
        font-size: 0.83rem; font-style: italic;
    }
    .ivd-notes-empty i { font-size: 1.4rem; display: block; margin-bottom: 0.5rem; opacity: 0.4; }

    /* ── Animations ───────────────────────────── */
    @keyframes fadeUp   { from { opacity:0; transform:translateY(14px); } to { opacity:1; transform:translateY(0); } }
    @keyframes fadeDown { from { opacity:0; transform:translateY(-10px); } to { opacity:1; transform:translateY(0); } }
</style>

@php
    $activeTab = $errors->has('note') ? 'notes' : 'items';
    $status = strtoupper((string) $invoice->status);
    $statusBadge = match($status) {
        'POSTED'    => 'badge-posted-ivd',
        'CANCELLED' => 'badge-cancelled-ivd',
        default     => 'badge-draft-ivd',
    };
    $statusIcon = match($status) {
        'POSTED'    => 'fa-check-circle',
        'CANCELLED' => 'fa-times-circle',
        default     => 'fa-clock',
    };
@endphp

{{-- ── Validation Errors ────────────────────────────── --}}
@if($errors->any())
    <div class="ivd-alert danger">
        <div class="al-icon"><i class="fas fa-exclamation-triangle"></i></div>
        <div>
            <strong>Validasi gagal:</strong>
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    </div>
@endif

{{-- ── Page Header ──────────────────────────────────── --}}
<div class="ivd-page-header">
    <div class="ivd-header-left">
        <div class="ivd-header-icon"><i class="fas fa-file-invoice-dollar"></i></div>
        <div>
            <h1 class="ivd-header-title">{{ $invoice->invoice_no }}</h1>
            <p class="ivd-header-sub">Detail Faktur &amp; Entri Jurnal</p>
        </div>
    </div>
    <div class="ivd-header-actions">
        <a href="{{ route('finance.invoice.index') }}" class="btn-ivd btn-ivd-back">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
        <a href="{{ route('finance.invoice.edit', $invoice->id) }}" class="btn-ivd btn-ivd-edit">
            <i class="fas fa-pen"></i> Edit
        </a>
        @if($invoice->status === 'DRAFT')
            <form action="{{ route('finance.invoice.post', $invoice->id) }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn-ivd btn-ivd-post">
                    <i class="fas fa-check-circle"></i> Rekam
                </button>
            </form>
            <form action="{{ route('finance.invoice.destroy', $invoice->id) }}" method="POST" class="d-inline"
                onsubmit="return confirm('Hapus faktur ini?');">
                @csrf @method('DELETE')
                <button type="submit" class="btn-ivd btn-ivd-delete">
                    <i class="fas fa-trash"></i> Hapus
                </button>
            </form>
        @elseif($invoice->status === 'POSTED')
            <form action="{{ route('finance.invoice.set-draft', $invoice->id) }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn-ivd btn-ivd-reset">
                    <i class="fas fa-undo"></i> Reset ke Rancangan
                </button>
            </form>
        @endif
    </div>
</div>

{{-- ── Main Card ────────────────────────────────────── --}}
<div class="ivd-main-card">

    {{-- ── Summary Strip ── --}}
    <div class="ivd-summary-strip">
        <div class="ivd-sum-item si-debit">
            <div class="ivd-sum-label"><i class="fas fa-arrow-up" style="font-size:.6rem;color:var(--blue-primary);"></i> Total Debit</div>
            <div class="ivd-sum-value blue">Rp {{ number_format((float) $invoice->total_debit, 2, ',', '.') }}</div>
        </div>
        <div class="ivd-sum-item si-credit">
            <div class="ivd-sum-label"><i class="fas fa-arrow-down" style="font-size:.6rem;color:var(--accent-red);"></i> Total Kredit</div>
            <div class="ivd-sum-value red">Rp {{ number_format((float) $invoice->total_credit, 2, ',', '.') }}</div>
        </div>
        <div class="ivd-sum-item si-creator">
            <div class="ivd-sum-label"><i class="fas fa-user" style="font-size:.6rem;color:var(--accent-green);"></i> Dibuat Oleh</div>
            <div class="ivd-sum-value" style="font-size:.85rem;">{{ $invoice->creator?->name ?? '-' }}</div>
        </div>
        <div class="ivd-sum-item si-posted">
            <div class="ivd-sum-label"><i class="fas fa-stamp" style="font-size:.6rem;color:var(--accent-purple);"></i> Terekam Oleh</div>
            <div class="ivd-sum-value" style="font-size:.85rem;">{{ $invoice->poster?->name ?? '—' }}</div>
            @if($invoice->posted_at)
                <div class="ivd-sum-sub" style="font-family:'Plus Jakarta Sans',sans-serif;">{{ $invoice->posted_at->format('d/m/Y H:i') }}</div>
            @endif
        </div>
    </div>

    {{-- ── Meta Grid ── --}}
    <div class="ivd-meta-grid">
        {{-- Kiri --}}
        <div class="ivd-meta-col">
            <div class="ivd-meta-row">
                <div class="ivd-meta-key"><i class="fas fa-hashtag"></i> Nomor Faktur</div>
                <div class="ivd-meta-val mono">{{ $invoice->invoice_no }}</div>
            </div>
            <div class="ivd-meta-row">
                <div class="ivd-meta-key"><i class="fas fa-calendar-alt"></i> Tanggal Akuntansi</div>
                <div class="ivd-meta-val mono">{{ optional($invoice->accounting_date)->format('d/m/Y') ?? '-' }}</div>
            </div>
            <div class="ivd-meta-row">
                <div class="ivd-meta-key"><i class="fas fa-tags"></i> Jenis</div>
                <div class="ivd-meta-val">
                    @if($invoice->entry_type === 'INCOME')
                        <span class="badge-ivd badge-income-ivd"><i class="fas fa-arrow-up" style="font-size:.55rem;"></i> Pemasukan</span>
                    @else
                        <span class="badge-ivd badge-expense-ivd"><i class="fas fa-arrow-down" style="font-size:.55rem;"></i> Pengeluaran</span>
                    @endif
                </div>
            </div>
            <div class="ivd-meta-row">
                <div class="ivd-meta-key"><i class="fas fa-book"></i> Jurnal</div>
                <div class="ivd-meta-val">{{ $invoice->journal_name }}</div>
            </div>
            <div class="ivd-meta-row">
                <div class="ivd-meta-key"><i class="fas fa-link"></i> Referensi</div>
                <div class="ivd-meta-val" style="{{ !$invoice->reference ? 'color:var(--text-muted);' : '' }}">
                    {{ $invoice->reference ?: '—' }}
                </div>
            </div>
        </div>
        {{-- Kanan --}}
        <div class="ivd-meta-col">
            <div class="ivd-meta-row">
                <div class="ivd-meta-key"><i class="fas fa-toggle-on"></i> Status</div>
                <div class="ivd-meta-val">
                    <span class="badge-ivd {{ $statusBadge }}">
                        <i class="fas {{ $statusIcon }}" style="font-size:.55rem;"></i>
                        {{ $status }}
                    </span>
                </div>
            </div>
            <div class="ivd-meta-row">
                <div class="ivd-meta-key"><i class="fas fa-wallet"></i> Total Debit</div>
                <div class="ivd-meta-val mono" style="color:var(--blue-primary);">Rp {{ number_format((float) $invoice->total_debit, 2, ',', '.') }}</div>
            </div>
            <div class="ivd-meta-row">
                <div class="ivd-meta-key"><i class="fas fa-wallet"></i> Total Kredit</div>
                <div class="ivd-meta-val mono" style="color:var(--accent-red);">Rp {{ number_format((float) $invoice->total_credit, 2, ',', '.') }}</div>
            </div>
            <div class="ivd-meta-row">
                <div class="ivd-meta-key"><i class="fas fa-user-edit"></i> Dibuat Oleh</div>
                <div class="ivd-meta-val">{{ $invoice->creator?->name ?? '-' }}</div>
            </div>
            <div class="ivd-meta-row">
                <div class="ivd-meta-key"><i class="fas fa-stamp"></i> Terekam Oleh</div>
                <div class="ivd-meta-val">
                    {{ $invoice->poster?->name ?? '—' }}
                    @if($invoice->posted_at)
                        <span class="ivd-sum-sub" style="font-family:'Plus Jakarta Sans',sans-serif;font-size:.72rem;color:var(--text-muted);display:block;">{{ $invoice->posted_at->format('d/m/Y H:i:s') }}</span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- ── Tabs ── --}}
    <div class="ivd-tabs-wrap">
        <ul class="ivd-tabs" id="ivd-tab-list">
            <li>
                <a class="ivd-tab-link {{ $activeTab === 'items' ? 'active' : '' }}"
                   data-target="ivd-pane-items" href="#" onclick="switchTab(event,'ivd-pane-items')">
                    <i class="fas fa-table" style="font-size:.7rem;"></i>
                    Item Jurnal
                    <span class="tab-badge">{{ $invoice->items->count() }}</span>
                </a>
            </li>
            <li>
                <a class="ivd-tab-link {{ $activeTab === 'notes' ? 'active' : '' }}"
                   data-target="ivd-pane-notes" href="#" onclick="switchTab(event,'ivd-pane-notes')">
                    <i class="fas fa-comment-dots" style="font-size:.7rem;"></i>
                    Log Catatan
                    <span class="tab-badge">{{ $invoice->notes->count() }}</span>
                </a>
            </li>
        </ul>
    </div>

    {{-- ── Tab Panes ── --}}
    <div class="ivd-tab-content">

        {{-- Items Pane --}}
        <div id="ivd-pane-items" class="ivd-tab-pane {{ $activeTab === 'items' ? 'active' : '' }}">
            <div class="ivd-items-wrap">
                <table class="ivd-items-table">
                    <thead>
                        <tr>
                            <th style="width:44px;text-align:center;">#</th>
                            <th style="width:130px;">Asset Category</th>
                            <th style="width:115px;">Akun</th>
                            <th style="width:140px;">Rekanan</th>
                            <th>Label</th>
                            <th style="width:180px;">Analisa Distribusi</th>
                            <th style="width:148px;text-align:right;">Debit</th>
                            <th style="width:148px;text-align:right;">Kredit</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($invoice->items as $item)
                            <tr>
                                <td class="cell-no-sm">{{ $loop->iteration }}</td>
                                <td class="cell-code">{{ $item->asset_category ?: '—' }}</td>
                                <td class="cell-code">{{ $item->account_code }}</td>
                                <td>{{ $item->partner_name ?: '—' }}</td>
                                <td style="font-weight:600;color:var(--text-primary);">{{ $item->label }}</td>
                                <td class="cell-muted">{{ $item->analytic_distribution ?: '—' }}</td>
                                <td class="cell-amount-td">Rp {{ number_format((float) $item->debit, 2, ',', '.') }}</td>
                                <td class="cell-amount-td">Rp {{ number_format((float) $item->credit, 2, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr class="empty-row"><td colspan="8">Belum ada item jurnal.</td></tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="6" style="text-align:right;color:var(--text-muted);">Total</th>
                            <th class="cell-amount-th blue">Rp {{ number_format((float) $invoice->total_debit, 2, ',', '.') }}</th>
                            <th class="cell-amount-th red">Rp {{ number_format((float) $invoice->total_credit, 2, ',', '.') }}</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        {{-- Notes Pane --}}
        <div id="ivd-pane-notes" class="ivd-tab-pane {{ $activeTab === 'notes' ? 'active' : '' }}">
            {{-- Add note form --}}
            <div class="ivd-note-form-card">
                <form method="POST" action="{{ route('finance.invoice.notes.store', $invoice->id) }}">
                    @csrf
                    <div class="ivd-note-label"><i class="fas fa-plus-circle"></i> Tambahkan Catatan</div>
                    <textarea name="note" id="note" class="ivd-note-textarea"
                        placeholder="Finance atau IT Support bisa menuliskan catatan tindak lanjut di sini."
                        required>{{ old('note') }}</textarea>
                    <button type="submit" class="btn-ivd-note">
                        <i class="fas fa-paper-plane"></i> Simpan Catatan
                    </button>
                </form>
            </div>

            {{-- Notes list --}}
            @if($invoice->notes->count())
                <div class="ivd-notes-list">
                    @foreach($invoice->notes as $note)
                        @php
                            $noteName = $note->user?->name ?? 'System';
                            $noteInitial = strtoupper(substr($noteName, 0, 1));
                        @endphp
                        <div class="ivd-note-item">
                            <div class="ivd-note-header">
                                <div class="ivd-note-user">
                                    <div class="ivd-note-avatar">{{ $noteInitial }}</div>
                                    <div>
                                        <span class="ivd-note-name">{{ $noteName }}</span>
                                        @if($note->user?->role)
                                            <span class="ivd-note-role">{{ $note->user->role }}</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="ivd-note-time">{{ optional($note->created_at)->format('d/m/Y H:i:s') }}</div>
                            </div>
                            <div class="ivd-note-body">{{ $note->note }}</div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="ivd-notes-empty">
                    <i class="fas fa-comment-slash"></i>
                    Belum ada catatan pada faktur ini.
                </div>
            @endif
        </div>

    </div>{{-- /ivd-tab-content --}}
</div>{{-- /ivd-main-card --}}

<script>
    function switchTab(event, targetId) {
        event.preventDefault();
        document.querySelectorAll('.ivd-tab-link').forEach(function(l) { l.classList.remove('active'); });
        document.querySelectorAll('.ivd-tab-pane').forEach(function(p) { p.classList.remove('active'); });
        event.currentTarget.classList.add('active');
        var pane = document.getElementById(targetId);
        if (pane) { pane.classList.add('active'); }
    }
</script>
@endsection