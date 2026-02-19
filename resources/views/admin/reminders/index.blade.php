@extends('layouts.app')

@section('content')
@php
    $isEdit = (bool) ($editingReminder ?? null);
    $formAction = $isEdit
        ? route('admin.reminders.update', $editingReminder->id)
        : route('admin.reminders.store');
    $defaultRemindAt = $isEdit && $editingReminder->remind_at
        ? $editingReminder->remind_at->timezone('Asia/Jakarta')->format('Y-m-d\TH:i')
        : now('Asia/Jakarta')->format('Y-m-d\TH:i');
    $selectedType = old('type', $editingReminder->type ?? 'GENERAL');
    $selectedAnnouncementId = old('announcement_id', $editingReminder->announcement_id ?? '');
@endphp

<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

<style>
    /* ── Design Tokens — Blue palette (matches Finance Dashboard & sidebar) ── */
    :root {
        --p1:        #3B82F6;
        --p2:        #2563EB;
        --p3:        #1D4ED8;
        --grad:      linear-gradient(135deg, #3B82F6 0%, #2563EB 55%, #1D4ED8 100%);
        --surface:   #FFFFFF;
        --surface-alt: #F0F6FF;
        --border:    #DBEAFE;
        --text:      #1E293B;
        --muted:     #64748B;
        --success:   #22C55E;
        --s-bg:      #F0FDF4;
        --s-b:       #BBF7D0;
        --warn:      #F59E0B;
        --w-bg:      #FFFBEB;
        --w-b:       #FDE68A;
        --danger:    #EF4444;
        --d-bg:      #FFF1F2;
        --d-b:       #FECDD3;
        --shadow:    0 4px 24px rgba(37,99,235,.09);
        --shadow-lg: 0 8px 32px rgba(37,99,235,.16);
        --radius:    18px;
        --radius-sm: 11px;
        --font:      'Plus Jakarta Sans', 'Nunito', 'Segoe UI', sans-serif;
    }

    .rp, .rp * {
        font-family: var(--font) !important;
        box-sizing: border-box;
    }

    /* Font Awesome protection */
    .rp .fas, .rp .far, .rp .fab {
        font-family: 'Font Awesome 5 Free' !important;
        font-style: normal !important;
        -webkit-font-smoothing: antialiased;
        display: inline-block;
        line-height: 1;
        vertical-align: middle;
    }

    /* ── Alert ── */
    .rp .rp-alert {
        background: var(--d-bg);
        border: 1.5px solid var(--d-b);
        border-radius: var(--radius-sm);
        color: #BE123C;
        padding: 14px 18px;
        margin-bottom: 20px;
        font-size: .93rem;
    }

    /* ── Card ── */
    .rp-card {
        background: var(--surface);
        border-radius: var(--radius);
        box-shadow: var(--shadow);
        border: 1.5px solid var(--border);
        overflow: hidden;
        margin-bottom: 24px;
        transition: box-shadow .2s, transform .2s;
    }
    .rp-card:hover { box-shadow: var(--shadow-lg); transform: translateY(-2px); }

    /* ── Card header ── */
    .rp-card-header {
        background: var(--grad);
        padding: 15px 22px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
    }
    .rp-card-header-left {
        display: flex; align-items: center; gap: 12px;
    }
    .rp-card-header .hicon {
        width: 30px; height: 30px;
        background: rgba(255,255,255,.18);
        border-radius: 8px;
        display: flex; align-items: center; justify-content: center;
        color: #fff; flex-shrink: 0;
    }
    .rp-card-header .hicon .fas { font-size: .88rem !important; color: #fff !important; }
    .rp-card-header h3 {
        margin: 0; font-size: 1rem; font-weight: 700; color: #fff;
    }

    /* ── Card body ── */
    .rp-card-body { padding: 22px; }

    /* ── Card footer ── */
    .rp-card-footer {
        padding: 14px 22px;
        background: #F8FAFF;
        border-top: 1.5px solid var(--border);
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
    }
    .rp-card-footer-pagination {
        padding: 14px 22px;
        background: #F8FAFF;
        border-top: 1.5px solid var(--border);
    }
    .rp-card-footer .pagination,
    .rp-card-footer-pagination .pagination { margin: 0; }

    /* ── Form groups ── */
    .rp-form-group { margin-bottom: 18px; }
    .rp-form-group label {
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: .74rem;
        font-weight: 700;
        color: var(--muted);
        margin-bottom: 6px;
        letter-spacing: .04em;
        text-transform: uppercase;
    }
    .rp-form-group label .fas {
        font-size: .78rem !important;
        color: var(--p1) !important;
        width: 14px;
        text-align: center;
    }
    .rp-form-group label .opt-label {
        font-weight: 400;
        text-transform: none;
        color: var(--muted);
        font-size: .74rem;
        letter-spacing: 0;
    }

    .rp-input, .rp-select, .rp-textarea {
        width: 100%;
        background: #F8FAFF;
        border: 1.5px solid var(--border);
        border-radius: var(--radius-sm);
        padding: 10px 14px;
        font-size: .9rem;
        color: var(--text);
        font-family: var(--font) !important;
        outline: none;
        transition: border-color .2s, box-shadow .2s;
        height: auto;
        appearance: auto;
    }
    .rp-input:focus, .rp-select:focus, .rp-textarea:focus {
        border-color: var(--p1);
        box-shadow: 0 0 0 3px rgba(59,130,246,.14);
        background: #fff;
    }
    .rp-input.is-invalid, .rp-select.is-invalid, .rp-textarea.is-invalid {
        border-color: var(--danger);
    }
    .rp-hint {
        font-size: .77rem;
        color: var(--muted);
        margin-top: 5px;
        display: block;
    }
    .rp-textarea { resize: vertical; min-height: 80px; }

    /* ── Buttons ── */
    .rp-btn {
        display: inline-flex; align-items: center; gap: 7px;
        padding: 10px 20px;
        border-radius: var(--radius-sm);
        font-size: .88rem; font-weight: 700;
        cursor: pointer; border: none;
        transition: transform .15s, box-shadow .15s;
        text-decoration: none;
        font-family: var(--font) !important;
        white-space: nowrap; line-height: 1;
    }
    .rp-btn:hover { transform: translateY(-1px); box-shadow: 0 4px 14px rgba(37,99,235,.22); text-decoration: none; }
    .rp-btn .fas { font-size: .88rem !important; }

    .rp-btn-primary  { background: var(--grad); color: #fff !important; }
    .rp-btn-primary .fas { color: #fff !important; }

    .rp-btn-secondary {
        background: var(--surface); color: var(--p1) !important;
        border: 1.5px solid var(--border);
    }
    .rp-btn-secondary .fas { color: var(--p1) !important; }

    /* Aksi tabel — ukuran kecil */
    .rp-btn-sm { padding: 6px 13px; font-size: .8rem; border-radius: 9px; }
    .rp-btn-sm .fas { font-size: .78rem !important; }

    .rp-btn-edit    { background: #EFF6FF; color: var(--p1) !important; border: 1.5px solid #BFDBFE; }
    .rp-btn-edit .fas { color: var(--p1) !important; }

    .rp-btn-danger  { background: var(--d-bg); color: var(--danger) !important; border: 1.5px solid var(--d-b); }
    .rp-btn-danger .fas { color: var(--danger) !important; }

    .rp-btn-success { background: var(--s-bg); color: #16A34A !important; border: 1.5px solid var(--s-b); }
    .rp-btn-success .fas { color: #16A34A !important; }

    .rp-btn-outline {
        background: transparent; color: var(--p1) !important;
        border: 1.5px solid var(--p1);
        font-size: .78rem; padding: 4px 11px; border-radius: 8px;
        display: inline-flex; align-items: center; gap: 5px;
        font-weight: 600;
        transition: background .15s, color .15s;
    }
    .rp-btn-outline .fas { font-size: .7rem !important; color: var(--p1) !important; }
    .rp-btn-outline:hover { background: var(--p1); color: #fff !important; text-decoration: none; }
    .rp-btn-outline:hover .fas { color: #fff !important; }

    /* ── Table ── */
    .rp-table-wrap { overflow-x: auto; }
    .rp-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        font-size: .88rem;
        color: var(--text);
    }
    .rp-table thead th {
        padding: 12px 14px;
        font-size: .71rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .05em;
        color: var(--muted);
        background: #F8FAFF;
        border-bottom: 2px solid var(--border);
        white-space: nowrap;
    }
    .rp-table thead th .fas, .rp-table thead th .far {
        font-size: .72rem !important;
        color: var(--p1) !important;
        margin-right: 4px;
        opacity: .85;
    }
    .rp-table tbody tr { transition: background .15s; }
    .rp-table tbody tr:hover { background: #F0F6FF; }
    .rp-table tbody td {
        padding: 13px 14px;
        border-bottom: 1px solid var(--border);
        vertical-align: top;
    }
    .rp-table tbody tr:last-child td { border-bottom: none; }

    /* ── Table cell helpers ── */
    .cell-id    { font-weight: 700; color: var(--p1); font-size: .92rem; }
    .cell-title { font-weight: 700; color: var(--text); margin-bottom: 2px; }
    .cell-desc  { color: var(--muted); font-size: .82rem; }
    .cell-meta  { color: #94A3B8; font-size: .76rem; margin-top: 3px; line-height: 1.5; }
    .cell-meta .fas { font-size: .68rem !important; color: #94A3B8 !important; }

    .sched-main { font-weight: 600; font-size: .87rem; color: var(--text); }
    .sched-sub  { font-size: .76rem; color: var(--muted); margin-top: 2px; }

    .ann-id    { font-size: .76rem; color: var(--muted); }
    .ann-title { font-size: .84rem; font-weight: 600; margin-bottom: 5px; }

    /* ── Badges ── */
    .rp-badge {
        display: inline-flex; align-items: center; gap: 5px;
        padding: 3px 10px; border-radius: 20px;
        font-size: .73rem; font-weight: 700;
        white-space: nowrap; margin-bottom: 3px;
    }
    .bdot {
        width: 7px; height: 7px; border-radius: 50%;
        display: inline-block; flex-shrink: 0;
    }
    .b-aktif    { background: var(--s-bg);  color: #16A34A; border: 1px solid var(--s-b); }
    .b-aktif    .bdot { background: #16A34A; }
    .b-nonaktif { background: #F1F5F9; color: #94A3B8; border: 1px solid #CBD5E1; }
    .b-nonaktif .bdot { background: #94A3B8; }
    .b-due      { background: var(--d-bg);  color: #BE123C; border: 1px solid var(--d-b); }
    .b-due      .bdot { background: #BE123C; }
    .b-upcoming { background: var(--w-bg);  color: #B45309; border: 1px solid var(--w-b); }
    .b-upcoming .bdot { background: #B45309; }
    .b-belum    { background: #EFF6FF; color: var(--p1);   border: 1px solid #BFDBFE; }
    .b-belum    .bdot { background: var(--p1); }

    .deact-text { font-size: .73rem; color: var(--muted); margin-top: 4px; }

    /* ── Empty state ── */
    .rp-empty { text-align: center; padding: 48px 24px; color: var(--muted); }
    .rp-empty .ei { font-size: 2.6rem; color: #BFDBFE; margin-bottom: 10px; display: block; }
    .rp-empty .ei .fas { font-size: 2.6rem !important; color: #BFDBFE !important; }
    .rp-empty p { margin: 0; font-size: .92rem; }

    /* ── Pagination ── */
    .pagination .page-link {
        border: none; margin: 0 2px;
        border-radius: 9px !important;
        color: var(--p1); padding: 6px 11px;
        font-size: .82rem; font-weight: 600;
        transition: background .15s, color .15s;
    }
    .pagination .page-item.active .page-link {
        background: var(--grad); color: #fff;
        box-shadow: 0 2px 10px rgba(37,99,235,.28);
    }
    .pagination .page-link:hover { background: #EFF6FF; color: var(--p1); }

    /* ── Announcement hidden ── */
    #announcement-link-group { display: none; }

    @media (max-width: 991px) {
        .rp-card-body { padding: 16px; }
        .rp-card-header { flex-direction: column; align-items: flex-start; }
    }
</style>

<div class="rp">

    {{-- ── Brand Header ── --}}
    <div style="display:flex; align-items:center; gap:14px; margin-bottom:24px;">
        <div style="
            width:52px; height:52px;
            background: var(--grad);
            border-radius:14px;
            display:flex; align-items:center; justify-content:center;
            box-shadow: 0 4px 16px rgba(37,99,235,.28);
            flex-shrink:0;
        ">
            <i class="fas fa-bell" style="font-size:1.35rem; color:#fff;"></i>
        </div>
        <div>
            <h1 style="font-size:1.3rem; font-weight:800; color:var(--text); margin:0 0 2px; line-height:1.2;">
                Reminder Plan
            </h1>
            <p style="font-size:.8rem; color:var(--muted); font-weight:500; margin:0;">
                Kelola & pantau jadwal reminder pengumuman
            </p>
        </div>
    </div>

    {{-- ── Validation errors ── --}}
    @if ($errors->any())
        <div class="rp-alert">
            <strong>⚠ Terjadi kesalahan validasi:</strong>
            <ul style="margin: 8px 0 0; padding-left: 18px;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="row">

        {{-- ══════════════════════════
             LEFT – Form
        ══════════════════════════ --}}
        <div class="col-lg-4">
            <div class="rp-card">
                <div class="rp-card-header">
                    <div class="rp-card-header-left">
                        <span class="hicon">
                            <i class="fas {{ $isEdit ? 'fa-edit' : 'fa-bell' }}"></i>
                        </span>
                        <h3>{{ $isEdit ? 'Edit Reminder' : 'Buat Reminder Baru' }}</h3>
                    </div>
                </div>

                <form method="POST" action="{{ $formAction }}">
                    @csrf
                    @if ($isEdit) @method('PUT') @endif

                    <div class="rp-card-body">

                        {{-- Judul --}}
                        <div class="rp-form-group">
                            <label for="title">
                                <i class="fas fa-tag"></i>
                                Judul Reminder
                            </label>
                            <input
                                type="text" id="title" name="title"
                                class="rp-input @error('title') is-invalid @enderror"
                                value="{{ old('title', $editingReminder->title ?? '') }}"
                                placeholder="Contoh: Reminder Pengumuman Ujian"
                                required
                            >
                        </div>

                        {{-- Deskripsi --}}
                        <div class="rp-form-group">
                            <label for="description">
                                <i class="fas fa-align-left"></i>
                                Deskripsi
                                <span class="opt-label">(opsional)</span>
                            </label>
                            <textarea
                                id="description" name="description"
                                class="rp-textarea @error('description') is-invalid @enderror"
                                placeholder="Detail reminder atau catatan tambahan..."
                            >{{ old('description', $editingReminder->description ?? '') }}</textarea>
                        </div>

                        {{-- Tanggal & Jam --}}
                        <div class="rp-form-group">
                            <label for="remind_at">
                                <i class="fas fa-calendar-alt"></i>
                                Tanggal &amp; Jam Reminder
                            </label>
                            <input
                                type="datetime-local" id="remind_at" name="remind_at"
                                class="rp-input @error('remind_at') is-invalid @enderror"
                                value="{{ old('remind_at', $defaultRemindAt) }}"
                                required
                            >
                            <span class="rp-hint">Bisa untuk hari ini juga, termasuk jam dan menit.</span>
                        </div>

                        {{-- Alert menit --}}
                        <div class="rp-form-group">
                            <label for="alert_before_minutes">
                                <i class="fas fa-clock"></i>
                                Alert Menjelang (menit)
                            </label>
                            <input
                                type="number" min="1" max="10080"
                                id="alert_before_minutes" name="alert_before_minutes"
                                class="rp-input @error('alert_before_minutes') is-invalid @enderror"
                                value="{{ old('alert_before_minutes', $editingReminder->alert_before_minutes ?? 30) }}"
                                required
                            >
                        </div>

                        {{-- Tipe --}}
                        <div class="rp-form-group">
                            <label for="type">
                                <i class="fas fa-layer-group"></i>
                                Tipe Reminder
                            </label>
                            <select
                                id="type" name="type"
                                class="rp-select @error('type') is-invalid @enderror"
                            >
                                <option value="GENERAL"      @selected($selectedType === 'GENERAL')>Umum</option>
                                <option value="ANNOUNCEMENT" @selected($selectedType === 'ANNOUNCEMENT')>Announcement</option>
                            </select>
                        </div>

                        {{-- Announcement link --}}
                        <div class="rp-form-group" id="announcement-link-group">
                            <label for="announcement_id">
                                <i class="fas fa-bullhorn"></i>
                                Kaitkan ke Announcement
                                <span class="opt-label">(opsional)</span>
                            </label>
                            <select
                                id="announcement_id" name="announcement_id"
                                class="rp-select @error('announcement_id') is-invalid @enderror"
                            >
                                <option value="">Tanpa pilih announcement (ingatkan buat pengumuman baru)</option>
                                @foreach ($announcements as $announcement)
                                    <option
                                        value="{{ $announcement->id }}"
                                        @selected((string) $selectedAnnouncementId === (string) $announcement->id)
                                    >
                                        #{{ $announcement->id }} – {{ \Illuminate\Support\Str::limit($announcement->title, 70) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                    </div>{{-- /card-body --}}

                    <div class="rp-card-footer">
                        <button type="submit" class="rp-btn rp-btn-primary">
                            <i class="fas {{ $isEdit ? 'fa-save' : 'fa-plus-circle' }}"></i>
                            <span>{{ $isEdit ? 'Update Reminder' : 'Simpan Reminder' }}</span>
                        </button>
                        @if ($isEdit)
                            <a href="{{ route('admin.reminders.index') }}" class="rp-btn rp-btn-secondary">
                                <i class="fas fa-times"></i>
                                <span>Batal Edit</span>
                            </a>
                        @endif
                    </div>
                </form>
            </div>
        </div>

        {{-- ══════════════════════════
             RIGHT – Table
        ══════════════════════════ --}}
        <div class="col-lg-8">
            <div class="rp-card">
                <div class="rp-card-header">
                    <div class="rp-card-header-left">
                        <span class="hicon">
                            <i class="fas fa-list-ul"></i>
                        </span>
                        <h3>Daftar Reminder</h3>
                    </div>
                </div>

                <div class="rp-table-wrap">
                    <table class="rp-table">
                        <thead>
                            <tr>
                                <th style="width:50px;"><i class="fas fa-hashtag"></i>ID</th>
                                <th><i class="fas fa-bell"></i>Reminder</th>
                                <th style="width:148px;"><i class="far fa-calendar"></i>Jadwal</th>
                                <th style="width:148px;"><i class="fas fa-circle"></i>Status</th>
                                <th style="width:175px;"><i class="fas fa-bullhorn"></i>Announcement</th>
                                <th style="width:132px;"><i class="fas fa-cog"></i>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $now = now('Asia/Jakarta'); @endphp
                            @forelse ($reminders as $reminder)
                                @php
                                    $alertState = $reminder->alertState($now);
                                    $typeLabel  = $reminder->isAnnouncementType() ? 'Announcement' : 'Umum';
                                @endphp
                                <tr>
                                    {{-- ID --}}
                                    <td>
                                        <span class="cell-id">#{{ $reminder->id }}</span>
                                    </td>

                                    {{-- Reminder info --}}
                                    <td>
                                        <div class="cell-title">{{ $reminder->title }}</div>
                                        @if($reminder->description)
                                            <div class="cell-desc">{{ $reminder->description }}</div>
                                        @endif
                                        <div class="cell-meta">
                                            <i class="fas fa-tag"></i> {{ $typeLabel }}
                                            &nbsp;·&nbsp;
                                            <i class="fas fa-clock"></i> {{ $reminder->alert_before_minutes }} mnt
                                            <br>
                                            <i class="fas fa-user"></i> {{ $reminder->creator?->name ?? '-' }}
                                        </div>
                                    </td>

                                    {{-- Jadwal --}}
                                    <td>
                                        <div class="sched-main">
                                            {{ $reminder->remind_at?->timezone('Asia/Jakarta')->format('d/m/Y H:i') ?? '-' }}
                                        </div>
                                        <div class="sched-sub">
                                            Dibuat: {{ $reminder->created_at?->timezone('Asia/Jakarta')->format('d/m/Y H:i') ?? '-' }}
                                        </div>
                                    </td>

                                    {{-- Status --}}
                                    <td>
                                        @if ($reminder->is_active)
                                            <span class="rp-badge b-aktif"><span class="bdot"></span>Aktif</span>
                                        @else
                                            <span class="rp-badge b-nonaktif"><span class="bdot"></span>Nonaktif</span>
                                        @endif
                                        <br>
                                        @if ($alertState === 'due')
                                            <span class="rp-badge b-due"><span class="bdot"></span>Hari-H / Due</span>
                                        @elseif ($alertState === 'upcoming')
                                            <span class="rp-badge b-upcoming"><span class="bdot"></span>Mendekati</span>
                                        @else
                                            <span class="rp-badge b-belum"><span class="bdot"></span>Belum Alert</span>
                                        @endif

                                        @if (! $reminder->is_active && $reminder->deactivated_at)
                                            <div class="deact-text">
                                                Nonaktif: {{ $reminder->deactivated_at->timezone('Asia/Jakarta')->format('d/m/Y H:i') }}
                                            </div>
                                        @endif
                                    </td>

                                    {{-- Announcement --}}
                                    <td>
                                        @if ($reminder->isAnnouncementType())
                                            @if ($reminder->announcement)
                                                <div class="ann-id">#{{ $reminder->announcement->id }}</div>
                                                <div class="ann-title">{{ \Illuminate\Support\Str::limit($reminder->announcement->title, 55) }}</div>
                                                <a
                                                    href="{{ route('admin.announcements.edit', ['id' => $reminder->announcement->id, 'focus_reminder' => $reminder->id, 'focus_announcement' => $reminder->announcement->id]) }}"
                                                    class="rp-btn-outline"
                                                >
                                                    <i class="fas fa-external-link-alt"></i> Buka
                                                </a>
                                            @else
                                                <div class="cell-desc" style="margin-bottom:6px;">Buat pengumuman baru.</div>
                                                <a
                                                    href="{{ route('admin.announcements.index', ['focus_reminder' => $reminder->id]) }}"
                                                    class="rp-btn-outline"
                                                >
                                                    <i class="fas fa-plus"></i> Buat
                                                </a>
                                            @endif
                                        @else
                                            <span style="color:var(--muted);font-size:.83rem;">—</span>
                                        @endif
                                    </td>

                                    {{-- Aksi --}}
                                    <td>
                                        <a
                                            href="{{ route('admin.reminders.edit', $reminder->id) }}"
                                            class="rp-btn rp-btn-sm rp-btn-edit"
                                            style="margin-bottom:5px; display:inline-flex;"
                                        >
                                            <i class="fas fa-pencil-alt"></i>
                                            <span>Edit</span>
                                        </a>
                                        <form method="POST" action="{{ route('admin.reminders.toggle', $reminder->id) }}">
                                            @csrf
                                            <input type="hidden" name="is_active" value="{{ $reminder->is_active ? 0 : 1 }}">
                                            <button
                                                type="submit"
                                                class="rp-btn rp-btn-sm {{ $reminder->is_active ? 'rp-btn-danger' : 'rp-btn-success' }}"
                                            >
                                                @if ($reminder->is_active)
                                                    <i class="fas fa-pause-circle"></i>
                                                    <span>Nonaktifkan</span>
                                                @else
                                                    <i class="fas fa-play-circle"></i>
                                                    <span>Aktifkan</span>
                                                @endif
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6">
                                        <div class="rp-empty">
                                            <span class="ei"><i class="fas fa-bell-slash"></i></span>
                                            <p>Belum ada reminder plan.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($reminders->hasPages())
                    <div class="rp-card-footer-pagination">
                        {{ $reminders->links() }}
                    </div>
                @endif
            </div>
        </div>

    </div>
</div>{{-- /rp --}}
@endsection

@section('js')
<script>
    (function () {
        const typeInput        = document.getElementById('type');
        const announcementGroup  = document.getElementById('announcement-link-group');
        const announcementSelect = document.getElementById('announcement_id');

        if (!typeInput || !announcementGroup || !announcementSelect) return;

        function syncAnnouncementField() {
            const isAnnouncementType = typeInput.value === 'ANNOUNCEMENT';
            announcementGroup.style.display = isAnnouncementType ? 'block' : 'none';
            if (!isAnnouncementType) announcementSelect.value = '';
        }

        typeInput.addEventListener('change', syncAnnouncementField);
        syncAnnouncementField();
    })();
</script>
@endsection