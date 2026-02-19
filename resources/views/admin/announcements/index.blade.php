@extends('layouts.app')

@section('content')
@php
    $isEdit = (bool) $editingAnnouncement;
    $formAction = $isEdit
        ? route('admin.announcements.update', $editingAnnouncement->id)
        : route('admin.announcements.store');
    $defaultChannels = $isEdit ? [] : ['email', 'whatsapp'];
    $selectedChannels = old('channels', $defaultChannels);
    $focusedReminderId = (int) ($focusedReminderId ?? 0);
    $focusedAnnouncementId = (int) ($focusedAnnouncementId ?? 0);
    $focusedReminder = $focusedReminder ?? null;
    $pendingAnnouncementReminders = $pendingAnnouncementReminders ?? collect();
@endphp

<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

<style>
    /* ── Design Tokens ── */
    :root {
        --p1:       #3B82F6;
        --p2:       #2563EB;
        --p3:       #1D4ED8;
        --grad:     linear-gradient(135deg, #3B82F6 0%, #2563EB 55%, #1D4ED8 100%);
        --grad-warn:linear-gradient(135deg, #F59E0B 0%, #D97706 100%);
        --surface:  #FFFFFF;
        --surf-alt: #F8FAFF;
        --border:   #DBEAFE;
        --text:     #1E293B;
        --muted:    #64748B;
        --success:  #22C55E;
        --s-bg:     #F0FDF4;
        --s-b:      #BBF7D0;
        --warn:     #F59E0B;
        --w-bg:     #FFFBEB;
        --w-b:      #FDE68A;
        --danger:   #EF4444;
        --d-bg:     #FFF1F2;
        --d-b:      #FECDD3;
        --info-bg:  #EFF6FF;
        --info-b:   #BFDBFE;
        --shadow:   0 4px 24px rgba(37,99,235,.09);
        --shadow-lg:0 8px 32px rgba(37,99,235,.16);
        --radius:   18px;
        --radius-sm:11px;
        --font:     'Plus Jakarta Sans','Nunito','Segoe UI',sans-serif;
    }

    .an, .an * { font-family: var(--font) !important; box-sizing: border-box; }

    /* Font Awesome protection */
    .an .fas,.an .far,.an .fab {
        font-family:'Font Awesome 5 Free' !important;
        font-style: normal !important;
        -webkit-font-smoothing: antialiased;
        display: inline-block; line-height: 1; vertical-align: middle;
    }

    /* ── Card ── */
    .an-card {
        background: var(--surface);
        border-radius: var(--radius);
        box-shadow: var(--shadow);
        border: 1.5px solid var(--border);
        overflow: hidden;
        margin-bottom: 24px;
        transition: box-shadow .2s, transform .2s;
    }
    .an-card:hover { box-shadow: var(--shadow-lg); transform: translateY(-2px); }

    /* Card header variants */
    .an-card-header {
        background: var(--grad);
        padding: 15px 22px;
        display: flex; align-items: center;
        justify-content: space-between; gap: 12px;
    }
    .an-card-header-warn { background: var(--grad-warn); }
    .an-card-header-left { display: flex; align-items: center; gap: 12px; }
    .an-card-header .hicon {
        width: 30px; height: 30px;
        background: rgba(255,255,255,.18);
        border-radius: 8px;
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0;
    }
    .an-card-header .hicon .fas { font-size: .88rem !important; color: #fff !important; }
    .an-card-header h3 { margin: 0; font-size: 1rem; font-weight: 700; color: #fff; }

    /* Card body / footer */
    .an-card-body { padding: 22px; }
    .an-card-body-p0 { padding: 0; }
    .an-card-footer {
        padding: 14px 22px;
        background: var(--surf-alt);
        border-top: 1.5px solid var(--border);
        display: flex; align-items: center;
        justify-content: space-between; gap: 10px;
    }

    /* ── Validation alert ── */
    .an-alert-danger {
        background: var(--d-bg); border: 1.5px solid var(--d-b);
        border-radius: var(--radius-sm); color: #BE123C;
        padding: 14px 18px; margin-bottom: 20px; font-size: .9rem;
    }

    /* ── Reminder source alert (info box) ── */
    .an-info-box {
        background: var(--w-bg); border: 1.5px solid var(--w-b);
        border-radius: var(--radius-sm); padding: 14px 16px;
        margin-bottom: 18px; font-size: .86rem; color: #92400E;
    }
    .an-info-box strong { font-weight: 700; color: #78350F; }
    .an-info-box small  { font-size: .78rem; color: #B45309; display: block; margin-top: 3px; }

    /* ── Form groups ── */
    .an-form-group { margin-bottom: 18px; }
    .an-form-group label {
        display: flex; align-items: center; gap: 6px;
        font-size: .74rem; font-weight: 700;
        color: var(--muted); text-transform: uppercase;
        letter-spacing: .04em; margin-bottom: 6px;
    }
    .an-form-group label .fas {
        font-size: .78rem !important; color: var(--p1) !important;
        width: 14px; text-align: center;
    }
    .an-form-group label .opt-label {
        font-weight: 400; text-transform: none;
        font-size: .74rem; letter-spacing: 0; color: var(--muted);
    }

    .an-input, .an-select, .an-textarea {
        width: 100%;
        background: var(--surf-alt);
        border: 1.5px solid var(--border);
        border-radius: var(--radius-sm);
        padding: 10px 14px;
        font-size: .9rem; color: var(--text);
        font-family: var(--font) !important;
        outline: none; appearance: auto;
        transition: border-color .2s, box-shadow .2s;
        height: auto;
    }
    .an-input:focus,.an-select:focus,.an-textarea:focus {
        border-color: var(--p1);
        box-shadow: 0 0 0 3px rgba(59,130,246,.14);
        background: #fff;
    }
    .an-input.is-invalid,.an-select.is-invalid,.an-textarea.is-invalid { border-color: var(--danger); }
    .an-textarea { resize: vertical; min-height: 130px; }

    /* ── Checkbox custom ── */
    .an-check-group { margin-bottom: 10px; }
    .an-check-row {
        display: flex; align-items: center; gap: 10px;
        padding: 10px 14px;
        background: var(--surf-alt); border: 1.5px solid var(--border);
        border-radius: var(--radius-sm); margin-bottom: 8px;
        cursor: pointer; transition: border-color .2s, background .2s;
    }
    .an-check-row:hover { border-color: var(--p1); background: #EFF6FF; }
    .an-check-row input[type="checkbox"] {
        width: 16px; height: 16px; accent-color: var(--p1);
        flex-shrink: 0; cursor: pointer;
    }
    .an-check-row .ch-label { font-size: .88rem; font-weight: 600; color: var(--text); cursor: pointer; }
    .an-check-row .ch-sub   { font-size: .76rem; color: var(--muted); }
    .an-check-hint { font-size: .76rem; color: var(--muted); margin-top: 6px; display: block; }

    /* ── Attachment hint ── */
    .an-file-hint { font-size: .77rem; color: var(--muted); margin-top: 5px; display: block; }
    .an-file-hint a { color: var(--p1); font-weight: 600; }
    .an-file-hint a:hover { text-decoration: underline; }

    /* File input styling */
    .an-file-input {
        width: 100%;
        background: var(--surf-alt);
        border: 1.5px dashed var(--border);
        border-radius: var(--radius-sm);
        padding: 10px 14px;
        font-size: .88rem; color: var(--muted);
        cursor: pointer;
        transition: border-color .2s;
    }
    .an-file-input:hover { border-color: var(--p1); }

    /* ── Buttons ── */
    .an-btn {
        display: inline-flex; align-items: center; gap: 8px;
        padding: 10px 20px; border-radius: var(--radius-sm);
        font-size: .88rem; font-weight: 700;
        cursor: pointer; border: none;
        transition: transform .15s, box-shadow .15s;
        text-decoration: none; font-family: var(--font) !important;
        white-space: nowrap; line-height: 1;
    }
    .an-btn:hover { transform: translateY(-1px); box-shadow: 0 4px 14px rgba(37,99,235,.22); text-decoration: none; }
    .an-btn .fas { font-size: .86rem !important; }

    .an-btn-primary  { background: var(--grad);    color: #fff !important; }
    .an-btn-primary .fas { color: #fff !important; }

    .an-btn-secondary { background: var(--surface); color: var(--p1) !important; border: 1.5px solid var(--border); }
    .an-btn-secondary .fas { color: var(--p1) !important; }

    .an-btn-sm { padding: 6px 14px; font-size: .8rem; border-radius: 9px; }
    .an-btn-sm .fas { font-size: .78rem !important; }

    .an-btn-edit   { background: #EFF6FF; color: var(--p1) !important; border: 1.5px solid var(--info-b); }
    .an-btn-edit .fas { color: var(--p1) !important; }

    .an-btn-danger { background: var(--d-bg); color: var(--danger) !important; border: 1.5px solid var(--d-b); }
    .an-btn-danger .fas { color: var(--danger) !important; }

    .an-btn-warn   { background: var(--w-bg); color: #92400E !important; border: 1.5px solid var(--w-b); }
    .an-btn-warn .fas { color: #92400E !important; }

    .an-btn-lepas  { background: var(--surf-alt); color: var(--muted) !important; border: 1.5px solid var(--border); font-size: .78rem; padding: 5px 12px; }

    /* ── Search bar ── */
    .an-search { display: flex; gap: 8px; align-items: center; }
    .an-search-input {
        background: rgba(255,255,255,.18);
        border: 1.5px solid rgba(255,255,255,.30);
        border-radius: var(--radius-sm);
        padding: 7px 13px;
        font-size: .85rem; color: #fff;
        font-family: var(--font) !important; outline: none;
        min-width: 260px;
        transition: background .2s, border-color .2s;
    }
    .an-search-input::placeholder { color: rgba(255,255,255,.65); }
    .an-search-input:focus { background: rgba(255,255,255,.28); border-color: rgba(255,255,255,.55); }

    /* ── Table ── */
    .an-table { width: 100%; border-collapse: separate; border-spacing: 0; }
    .an-table thead th {
        font-size: .71rem; font-weight: 800;
        text-transform: uppercase; letter-spacing: .05em;
        color: var(--muted); padding: 11px 14px;
        background: var(--surf-alt);
        border-bottom: 2px solid var(--border);
        white-space: nowrap;
    }
    .an-table thead th .fas,.an-table thead th .far {
        font-size: .72rem !important; color: var(--p1) !important;
        margin-right: 4px; opacity: .85;
    }
    .an-table tbody tr { transition: background .15s; }
    .an-table tbody tr:hover { background: #F0F6FF; }
    .an-table tbody tr.row-focused { background: #FFFBEB; border-left: 3px solid var(--warn); }
    .an-table tbody td {
        padding: 12px 14px; vertical-align: top;
        border-bottom: 1px solid var(--border);
        font-size: .87rem; color: var(--text);
    }
    .an-table tbody tr:last-child td { border-bottom: none; }

    /* Table cell helpers */
    .cell-title  { font-weight: 700; color: var(--text); margin-bottom: 3px; }
    .cell-msg    { color: var(--muted); font-size: .82rem; margin-bottom: 3px; }
    .cell-date   { font-size: .76rem; color: #94A3B8; }
    .cell-creator { font-weight: 600; font-size: .87rem; }
    .cell-attach { font-size: .76rem; }
    .cell-attach a { color: var(--p1); font-weight: 600; }

    /* Reminder mini rows in table */
    .rem-row {
        font-size: .76rem; padding: 3px 6px; border-radius: 6px;
        margin-bottom: 3px; color: var(--text);
    }
    .rem-row.focused-rem { background: #FFFBEB; border: 1px solid var(--w-b); }

    /* ── Badges ── */
    .an-badge {
        display: inline-flex; align-items: center; gap: 4px;
        padding: 3px 10px; border-radius: 20px;
        font-size: .72rem; font-weight: 700; margin-right: 3px; margin-bottom: 3px;
    }
    .ab-info    { background: var(--info-bg); color: var(--p2); border: 1px solid var(--info-b); }
    .ab-success { background: var(--s-bg);    color: #16A34A;   border: 1px solid var(--s-b);   }
    .ab-danger  { background: var(--d-bg);    color: #BE123C;   border: 1px solid var(--d-b);   }
    .ab-warn    { background: var(--w-bg);    color: #B45309;   border: 1px solid var(--w-b);   }
    .ab-gray    { background: #F1F5F9;        color: #64748B;   border: 1px solid #CBD5E1;      }
    .ab-grad    { background: var(--grad);    color: #fff; }

    /* Status badge mapping */
    .ab-sent    { background: var(--s-bg); color: #16A34A; border: 1px solid var(--s-b); }
    .ab-failed  { background: var(--d-bg); color: #BE123C; border: 1px solid var(--d-b); }
    .ab-pending { background: var(--w-bg); color: #B45309; border: 1px solid var(--w-b); }

    /* ── Empty state ── */
    .an-empty { text-align: center; padding: 44px 24px; }
    .an-empty .ei { font-size: 2.6rem; color: #BFDBFE; margin-bottom: 10px; display: block; }
    .an-empty .ei .fas { font-size: 2.6rem !important; color: #BFDBFE !important; }
    .an-empty p { color: var(--muted); font-size: .9rem; margin: 0; }

    /* ── Pagination ── */
    .an-card-footer-pag {
        padding: 13px 22px;
        background: var(--surf-alt);
        border-top: 1.5px solid var(--border);
    }
    .an-card-footer-pag .pagination { margin: 0; }
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

    @media (max-width: 991px) {
        .an-card-body { padding: 16px; }
        .an-card-header { flex-direction: column; align-items: flex-start; }
        .an-search-input { min-width: 160px; }
    }
</style>

<div class="an">

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
            <i class="fas fa-bullhorn" style="font-size:1.3rem; color:#fff;"></i>
        </div>
        <div>
            <h1 style="font-size:1.3rem; font-weight:800; color:var(--text); margin:0 0 2px; line-height:1.2;">
                Announcement
            </h1>
            <p style="font-size:.8rem; color:var(--muted); font-weight:500; margin:0;">
                Kelola & publikasikan pengumuman ke semua channel
            </p>
        </div>
    </div>

    {{-- ── Validation errors ── --}}
    @if ($errors->any())
        <div class="an-alert-danger">
            <strong>⚠ Terjadi kesalahan validasi:</strong>
            <ul style="margin:8px 0 0; padding-left:18px;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="row">

        {{-- ══════════════════════════════
             LEFT col
        ══════════════════════════════ --}}
        <div class="col-lg-4">

            {{-- ── Form Card ── --}}
            <div class="an-card">
                <div class="an-card-header">
                    <div class="an-card-header-left">
                        <span class="hicon">
                            <i class="fas {{ $isEdit ? 'fa-edit' : 'fa-plus-circle' }}"></i>
                        </span>
                        <h3>{{ $isEdit ? 'Edit Announcement' : 'Buat Announcement' }}</h3>
                    </div>
                </div>

                <form action="{{ $formAction }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @if ($isEdit) @method('PUT') @endif
                    @if (! $isEdit && $focusedReminder)
                        <input type="hidden" name="reminder_id" value="{{ $focusedReminder->id }}">
                    @endif

                    <div class="an-card-body">

                        {{-- Focused reminder info box --}}
                        @if (! $isEdit && $focusedReminder)
                            <div class="an-info-box">
                                <strong><i class="fas fa-bell" style="font-size:.8rem;margin-right:5px;color:#D97706;"></i>Reminder Sumber:</strong>
                                #{{ $focusedReminder->id }} — {{ $focusedReminder->title }}
                                <small>Jadwal: {{ $focusedReminder->remind_at?->format('d/m/Y H:i') ?? '-' }} · Oleh: {{ $focusedReminder->creator?->name ?? '-' }}</small>
                                <small>Saat publish, reminder ini akan otomatis ditautkan.</small>
                                <div style="margin-top:10px;">
                                    <a href="{{ route('admin.announcements.index') }}" class="an-btn an-btn-lepas">
                                        <i class="fas fa-unlink" style="font-size:.76rem !important; color:var(--muted) !important;"></i>
                                        <span>Lepas Fokus Reminder</span>
                                    </a>
                                </div>
                            </div>
                        @endif

                        {{-- Judul --}}
                        <div class="an-form-group">
                            <label for="title">
                                <i class="fas fa-heading"></i> Judul
                            </label>
                            <input
                                type="text" id="title" name="title"
                                class="an-input @error('title') is-invalid @enderror"
                                value="{{ old('title', $editingAnnouncement->title ?? ($focusedReminder?->title ?? '')) }}"
                                required
                            >
                        </div>

                        {{-- Isi Pengumuman --}}
                        <div class="an-form-group">
                            <label for="message">
                                <i class="fas fa-align-left"></i> Isi Pengumuman
                            </label>
                            <textarea
                                id="message" name="message"
                                class="an-textarea @error('message') is-invalid @enderror"
                                required
                            >{{ old('message', $editingAnnouncement->message ?? ($focusedReminder?->description ?? '')) }}</textarea>
                        </div>

                        {{-- Attachment --}}
                        <div class="an-form-group">
                            <label for="attachment">
                                <i class="fas fa-paperclip"></i>
                                Attachment <span class="opt-label">(opsional)</span>
                            </label>
                            <input
                                type="file" id="attachment" name="attachment"
                                class="an-file-input @error('attachment') is-invalid @enderror"
                            >
                            @if ($isEdit && !empty($editingAnnouncement->attachment_path))
                                <span class="an-file-hint">
                                    File saat ini:
                                    <a href="{{ asset('storage/' . $editingAnnouncement->attachment_path) }}" target="_blank">
                                        <i class="fas fa-external-link-alt" style="font-size:.7rem !important; color:var(--p1) !important;"></i>
                                        Lihat attachment
                                    </a>
                                </span>
                            @endif
                        </div>

                        {{-- Channel blasting --}}
                        <div class="an-form-group">
                            <label><i class="fas fa-paper-plane"></i> Kirim ke Blasting</label>
                            <div class="an-check-group">
                                <label class="an-check-row" for="channel_email">
                                    <input
                                        type="checkbox" id="channel_email"
                                        name="channels[]" value="email"
                                        @checked(in_array('email', (array) $selectedChannels, true))
                                    >
                                    <div>
                                        <div class="ch-label">Email</div>
                                        <div class="ch-sub">{{ $emailRecipientCount }} recipient valid</div>
                                    </div>
                                </label>
                                <label class="an-check-row" for="channel_whatsapp">
                                    <input
                                        type="checkbox" id="channel_whatsapp"
                                        name="channels[]" value="whatsapp"
                                        @checked(in_array('whatsapp', (array) $selectedChannels, true))
                                    >
                                    <div>
                                        <div class="ch-label">WhatsApp</div>
                                        <div class="ch-sub">{{ $whatsappRecipientCount }} recipient valid</div>
                                    </div>
                                </label>
                            </div>
                            <span class="an-check-hint">
                                {{ $isEdit
                                    ? 'Kosongkan channel jika hanya ingin update data tanpa kirim ulang.'
                                    : 'Pilih channel untuk menghubungkan announcement ke modul blasting.' }}
                            </span>
                        </div>

                    </div>{{-- /card-body --}}

                    <div class="an-card-footer">
                        <button type="submit" class="an-btn an-btn-primary">
                            <i class="fas {{ $isEdit ? 'fa-save' : 'fa-paper-plane' }}"></i>
                            <span>{{ $isEdit ? 'Update Announcement' : 'Publikasikan Announcement' }}</span>
                        </button>
                        @if ($isEdit)
                            <a href="{{ route('admin.announcements.index') }}" class="an-btn an-btn-secondary">
                                <i class="fas fa-times"></i>
                                <span>Batal Edit</span>
                            </a>
                        @endif
                    </div>
                </form>
            </div>{{-- /form card --}}

            {{-- ── Reminder Terkait (edit mode) ── --}}
            @if ($isEdit)
                @php $editingAnnouncementReminders = $editingAnnouncement->reminders ?? collect(); @endphp
                <div class="an-card">
                    <div class="an-card-header">
                        <div class="an-card-header-left">
                            <span class="hicon"><i class="fas fa-bell"></i></span>
                            <h3>Reminder Terkait</h3>
                        </div>
                    </div>
                    <div class="an-card-body-p0" style="overflow-x:auto;">
                        <table class="an-table">
                            <thead>
                                <tr>
                                    <th style="width:46px;"><i class="fas fa-hashtag"></i>ID</th>
                                    <th><i class="fas fa-bell"></i>Reminder</th>
                                    <th style="width:90px;"><i class="fas fa-circle"></i>Status</th>
                                    <th style="width:120px;"><i class="far fa-calendar"></i>Jadwal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($editingAnnouncementReminders as $reminder)
                                    <tr class="{{ $focusedReminderId === (int) $reminder->id ? 'row-focused' : '' }}">
                                        <td style="font-weight:700; color:var(--p1);">#{{ $reminder->id }}</td>
                                        <td>
                                            <div class="cell-title">{{ $reminder->title }}</div>
                                            <div class="cell-msg">{{ \Illuminate\Support\Str::limit((string) $reminder->description, 60) ?: '-' }}</div>
                                        </td>
                                        <td>
                                            @if ($reminder->is_active)
                                                <span class="an-badge ab-success">Aktif</span>
                                            @else
                                                <span class="an-badge ab-gray">Nonaktif</span>
                                            @endif
                                        </td>
                                        <td style="font-size:.8rem;">{{ $reminder->remind_at?->format('d/m/Y H:i') ?? '-' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4">
                                            <div class="an-empty" style="padding:24px;">
                                                <span class="ei"><i class="fas fa-bell-slash"></i></span>
                                                <p>Belum ada reminder terhubung.</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

        </div>{{-- /col-lg-4 --}}

        {{-- ══════════════════════════════
             RIGHT col
        ══════════════════════════════ --}}
        <div class="col-lg-8">

            {{-- ── Pending Reminders card ── --}}
            @if ($pendingAnnouncementReminders->isNotEmpty())
                <div class="an-card">
                    <div class="an-card-header an-card-header-warn">
                        <div class="an-card-header-left">
                            <span class="hicon"><i class="fas fa-exclamation-triangle"></i></span>
                            <h3>Reminder Announcement Belum Ditautkan</h3>
                        </div>
                    </div>
                    <div class="an-card-body-p0" style="overflow-x:auto;">
                        <table class="an-table">
                            <thead>
                                <tr>
                                    <th style="width:46px;"><i class="fas fa-hashtag"></i>ID</th>
                                    <th><i class="fas fa-bell"></i>Reminder</th>
                                    <th style="width:140px;"><i class="far fa-calendar"></i>Jadwal</th>
                                    <th style="width:160px;"><i class="fas fa-cog"></i>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($pendingAnnouncementReminders as $pendingReminder)
                                    <tr class="{{ $focusedReminderId === (int) $pendingReminder->id ? 'row-focused' : '' }}">
                                        <td style="font-weight:700; color:var(--p1);">#{{ $pendingReminder->id }}</td>
                                        <td>
                                            <div class="cell-title">{{ $pendingReminder->title }}</div>
                                            <div class="cell-msg">{{ \Illuminate\Support\Str::limit((string) $pendingReminder->description, 80) ?: '-' }}</div>
                                        </td>
                                        <td style="font-size:.82rem;">{{ $pendingReminder->remind_at?->format('d/m/Y H:i') ?? '-' }}</td>
                                        <td>
                                            <a
                                                href="{{ route('admin.announcements.index', ['focus_reminder' => $pendingReminder->id]) }}"
                                                class="an-btn an-btn-sm an-btn-warn"
                                            >
                                                <i class="fas fa-link"></i>
                                                <span>Gunakan Reminder Ini</span>
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            {{-- ── Data Announcement Card ── --}}
            <div class="an-card">
                <div class="an-card-header">
                    <div class="an-card-header-left">
                        <span class="hicon"><i class="fas fa-list-ul"></i></span>
                        <h3>Data Announcement Yang Sudah Dibuat</h3>
                    </div>
                    {{-- Search bar in header --}}
                    <form method="GET" action="{{ route('admin.announcements.index') }}">
                        <div class="an-search">
                            <input
                                type="text" name="search"
                                class="an-search-input"
                                placeholder="Cari judul, isi, pembuat..."
                                value="{{ $search ?? '' }}"
                            >
                            <button type="submit" class="an-btn an-btn-sm" style="background:rgba(255,255,255,.18);color:#fff;border:1.5px solid rgba(255,255,255,.28);">
                                <i class="fas fa-search" style="color:#fff !important;"></i>
                            </button>
                            @if (!empty($search))
                                <a href="{{ route('admin.announcements.index') }}" class="an-btn an-btn-sm" style="background:rgba(255,255,255,.12);color:#fff;border:1.5px solid rgba(255,255,255,.22);">
                                    <i class="fas fa-times" style="color:#fff !important;"></i>
                                </a>
                            @endif
                        </div>
                    </form>
                </div>

                <div class="an-card-body-p0" style="overflow-x:auto;">
                    <table class="an-table">
                        <thead>
                            <tr>
                                <th style="width:46px;"><i class="fas fa-hashtag"></i>ID</th>
                                <th><i class="fas fa-bullhorn"></i>Judul / Pesan</th>
                                <th style="width:140px;"><i class="far fa-user"></i>Dibuat Oleh</th>
                                <th style="width:165px;"><i class="fas fa-paper-plane"></i>Log Blasting</th>
                                <th style="width:190px;"><i class="fas fa-bell"></i>Reminder</th>
                                <th style="width:130px;"><i class="fas fa-cog"></i>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($announcements as $announcement)
                                @php
                                    $announcementReminders = $announcement->reminders ?? collect();
                                    $nextActiveReminder = $announcementReminders
                                        ->where('is_active', true)
                                        ->sortBy('remind_at')
                                        ->first();
                                @endphp
                                <tr class="{{ $focusedAnnouncementId === (int) $announcement->id ? 'row-focused' : '' }}">

                                    {{-- ID --}}
                                    <td style="font-weight:700; color:var(--p1);">#{{ $announcement->id }}</td>

                                    {{-- Judul / Pesan --}}
                                    <td>
                                        <div class="cell-title">{{ $announcement->title }}</div>
                                        <div class="cell-msg">{{ \Illuminate\Support\Str::limit($announcement->message, 110) }}</div>
                                        <div class="cell-date">{{ $announcement->created_at?->format('d/m/Y H:i') ?? '-' }}</div>
                                    </td>

                                    {{-- Dibuat Oleh --}}
                                    <td>
                                        <div style="display:flex; align-items:center; gap:7px; margin-bottom:5px;">
                                            <span style="width:26px;height:26px;background:#EFF6FF;border-radius:50%;border:1.5px solid var(--info-b);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                                <i class="fas fa-user" style="font-size:.68rem !important; color:var(--p1) !important;"></i>
                                            </span>
                                            <span class="cell-creator">{{ $announcement->creator?->name ?? '-' }}</span>
                                        </div>
                                        @if (!empty($announcement->attachment_path))
                                            <div class="cell-attach">
                                                <a href="{{ asset('storage/' . $announcement->attachment_path) }}" target="_blank">
                                                    <i class="fas fa-paperclip" style="font-size:.7rem !important; color:var(--p1) !important;"></i>
                                                    Lihat attachment
                                                </a>
                                            </div>
                                        @endif
                                    </td>

                                    {{-- Log Blasting --}}
                                    <td>
                                        <span class="an-badge ab-info">Total: {{ $announcement->logs_total_count }}</span>
                                        <span class="an-badge ab-success">SENT: {{ $announcement->logs_sent_count }}</span>
                                        <span class="an-badge ab-danger">FAILED: {{ $announcement->logs_failed_count }}</span>
                                        <span class="an-badge ab-warn">PENDING: {{ $announcement->logs_pending_count }}</span>
                                    </td>

                                    {{-- Reminder Terkait --}}
                                    <td>
                                        <span class="an-badge ab-info">Total: {{ $announcement->reminders_total_count ?? 0 }}</span>
                                        <span class="an-badge ab-success">Aktif: {{ $announcement->reminders_active_count ?? 0 }}</span>
                                        <div style="margin-top:5px; font-size:.76rem; color:var(--muted);">
                                            @if ($nextActiveReminder)
                                                <i class="fas fa-clock" style="font-size:.68rem !important; color:var(--p1) !important;"></i>
                                                {{ $nextActiveReminder->remind_at?->format('d/m/Y H:i') ?? '-' }}
                                            @else
                                                <span style="color:#94A3B8;">Belum ada reminder aktif.</span>
                                            @endif
                                        </div>
                                        @if ($announcementReminders->isNotEmpty())
                                            <div style="margin-top:4px;">
                                                @foreach ($announcementReminders->take(2) as $reminder)
                                                    <div class="rem-row {{ $focusedReminderId === (int) $reminder->id ? 'focused-rem' : '' }}">
                                                        #{{ $reminder->id }} {{ \Illuminate\Support\Str::limit($reminder->title, 30) }}
                                                        <span style="color:#94A3B8;">({{ $reminder->remind_at?->format('d/m H:i') ?? '-' }})</span>
                                                    </div>
                                                @endforeach
                                                @if ($announcementReminders->count() > 2)
                                                    <div style="font-size:.74rem; color:var(--muted); margin-top:2px;">
                                                        +{{ $announcementReminders->count() - 2 }} reminder lainnya
                                                    </div>
                                                @endif
                                            </div>
                                        @endif
                                    </td>

                                    {{-- Aksi --}}
                                    <td>
                                        <a
                                            href="{{ route('admin.announcements.edit', $announcement->id) }}"
                                            class="an-btn an-btn-sm an-btn-edit"
                                            style="margin-bottom:5px; display:inline-flex;"
                                        >
                                            <i class="fas fa-pencil-alt"></i>
                                            <span>Edit</span>
                                        </a>
                                        <form
                                            action="{{ route('admin.announcements.destroy', $announcement->id) }}"
                                            method="POST"
                                            onsubmit="return confirm('Hapus announcement ini?')"
                                        >
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="an-btn an-btn-sm an-btn-danger">
                                                <i class="fas fa-trash-alt"></i>
                                                <span>Hapus</span>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6">
                                        <div class="an-empty">
                                            <span class="ei"><i class="fas fa-bullhorn"></i></span>
                                            <p>Belum ada data announcement.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($announcements->hasPages())
                    <div class="an-card-footer-pag">
                        {{ $announcements->links() }}
                    </div>
                @endif
            </div>

            {{-- ── Log Announcement Card ── --}}
            <div class="an-card">
                <div class="an-card-header">
                    <div class="an-card-header-left">
                        <span class="hicon"><i class="fas fa-history"></i></span>
                        <h3>Log Announcement</h3>
                    </div>
                </div>

                <div class="an-card-body-p0" style="overflow-x:auto;">
                    <table class="an-table">
                        <thead>
                            <tr>
                                <th style="width:46px;"><i class="fas fa-hashtag"></i>ID</th>
                                <th><i class="fas fa-bullhorn"></i>Announcement</th>
                                <th style="width:100px;"><i class="fas fa-satellite-dish"></i>Channel</th>
                                <th><i class="fas fa-user-tag"></i>Target</th>
                                <th style="width:90px; text-align:center;"><i class="fas fa-circle"></i>Status</th>
                                <th><i class="fas fa-reply"></i>Response</th>
                                <th style="width:140px;"><i class="far fa-clock"></i>Sent At</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($announcementLogs as $log)
                                @php
                                    $status = strtoupper((string) $log->status);
                                    $badgeClass = $status === 'SENT'
                                        ? 'ab-sent'
                                        : ($status === 'FAILED' ? 'ab-failed' : 'ab-pending');
                                @endphp
                                <tr>
                                    <td style="font-weight:700; color:var(--p1);">#{{ $log->id }}</td>
                                    <td style="font-weight:600; font-size:.86rem;">{{ $log->announcement?->title ?? '-' }}</td>
                                    <td>
                                        @php $ch = strtolower($log->channel); @endphp
                                        @if($ch === 'email')
                                            <span class="an-badge ab-info">
                                                <i class="fas fa-envelope" style="font-size:.68rem !important; color:var(--p1) !important;"></i>
                                                EMAIL
                                            </span>
                                        @elseif($ch === 'whatsapp')
                                            <span class="an-badge ab-success">
                                                <i class="fab fa-whatsapp" style="font-size:.72rem !important; color:#16A34A !important;"></i>
                                                WA
                                            </span>
                                        @else
                                            <span class="an-badge ab-gray">{{ strtoupper($log->channel) }}</span>
                                        @endif
                                    </td>
                                    <td style="font-size:.83rem;">{{ $log->target }}</td>
                                    <td style="text-align:center;">
                                        <span class="an-badge {{ $badgeClass }}">{{ $status }}</span>
                                    </td>
                                    <td style="font-size:.8rem; color:var(--muted);">{{ \Illuminate\Support\Str::limit((string) $log->response, 60) ?: '-' }}</td>
                                    <td style="font-size:.8rem;">{{ $log->sent_at?->format('d/m/Y H:i:s') ?? '-' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7">
                                        <div class="an-empty">
                                            <span class="ei"><i class="fas fa-history"></i></span>
                                            <p>Belum ada log announcement.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($announcementLogs->hasPages())
                    <div class="an-card-footer-pag">
                        {{ $announcementLogs->links() }}
                    </div>
                @endif
            </div>

        </div>{{-- /col-lg-8 --}}
    </div>{{-- /row --}}
</div>{{-- /an --}}
@endsection