@extends('layouts.app')

@section('content')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');

    :root {
        --fa-blue: #2563eb;
        --fa-blue-dark: #1e3a8a;
        --fa-bg: #f1f5ff;
        --fa-card: #ffffff;
        --fa-border: #dbe5f6;
        --fa-text: #0f172a;
        --fa-muted: #64748b;
        --fa-green: #10b981;
        --fa-red: #ef4444;
        --fa-shadow: 0 10px 28px rgba(15, 23, 42, 0.09);
        --fa-radius: 14px;
    }

    body, .content-wrapper { background: var(--fa-bg) !important; font-family: 'Plus Jakarta Sans', sans-serif !important; }

    .coa-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: .8rem;
        margin-bottom: 1rem;
        flex-wrap: wrap;
    }
    .coa-title-wrap { display: flex; align-items: center; gap: .7rem; }
    .coa-title-icon {
        width: 42px; height: 42px; border-radius: 10px;
        display: inline-flex; align-items: center; justify-content: center;
        color: #fff; background: linear-gradient(135deg, var(--fa-blue), var(--fa-blue-dark));
        box-shadow: 0 6px 20px rgba(37, 99, 235, .35);
    }
    .coa-title { margin: 0; font-size: 1.2rem; font-weight: 800; color: var(--fa-text); }
    .coa-subtitle { margin: 0; font-size: .78rem; color: var(--fa-muted); font-weight: 500; }

    .coa-layout {
        display: grid;
        grid-template-columns: 280px minmax(0, 1fr);
        gap: 1rem;
    }
    @media (max-width: 992px) {
        .coa-layout {
            grid-template-columns: 1fr;
        }
    }

    .coa-card {
        background: var(--fa-card);
        border: 1px solid var(--fa-border);
        border-radius: var(--fa-radius);
        box-shadow: var(--fa-shadow);
        overflow: hidden;
    }

    .coa-card-head {
        padding: .9rem 1rem;
        border-bottom: 1px solid var(--fa-border);
        background: linear-gradient(135deg, rgba(37,99,235,.08), rgba(30,58,138,.04));
    }
    .coa-card-head h3 {
        margin: 0;
        font-size: .88rem;
        font-weight: 700;
        color: var(--fa-text);
    }

    .coa-side-list {
        list-style: none;
        margin: 0;
        padding: .7rem;
        display: flex;
        flex-direction: column;
        gap: .4rem;
    }
    .coa-side-row {
        display: flex;
        align-items: center;
        gap: .35rem;
    }
    .coa-side-item {
        flex: 1;
        display: flex;
        align-items: center;
        gap: .6rem;
        padding: .55rem .65rem;
        border-radius: 10px;
        color: var(--fa-text);
        text-decoration: none;
        border: 1px solid transparent;
    }
    .coa-side-item:hover {
        border-color: var(--fa-border);
        background: rgba(37,99,235,.04);
        text-decoration: none;
        color: var(--fa-text);
    }
    .coa-side-item.active {
        background: rgba(37,99,235,.1);
        border-color: rgba(37,99,235,.22);
    }
    .coa-side-delete {
        width: 30px;
        height: 30px;
        border-radius: 8px;
        border: 1px solid rgba(239,68,68,.22);
        background: rgba(239,68,68,.08);
        color: #b91c1c;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all .15s ease;
    }
    .coa-side-delete:hover {
        background: #ef4444;
        border-color: #ef4444;
        color: #fff;
    }
    .coa-side-num {
        width: 28px; height: 28px;
        border-radius: 8px;
        display: inline-flex; align-items: center; justify-content: center;
        background: #eef2ff;
        color: var(--fa-blue-dark);
        font-weight: 800;
        font-size: .78rem;
    }
    .coa-side-label {
        flex: 1;
        font-size: .78rem;
        font-weight: 600;
        color: #334155;
        line-height: 1.3;
    }
    .coa-side-count {
        font-size: .72rem;
        color: var(--fa-muted);
        font-weight: 700;
    }

    .coa-main {
        display: grid;
        gap: 1rem;
    }

    .coa-form {
        padding: 1rem;
    }
    .coa-form-grid {
        display: grid;
        gap: .8rem;
        grid-template-columns: repeat(4, minmax(0, 1fr));
    }
    @media (max-width: 1200px) {
        .coa-form-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }
    @media (max-width: 640px) {
        .coa-form-grid {
            grid-template-columns: 1fr;
        }
    }

    .coa-field label {
        display: block;
        margin-bottom: .35rem;
        font-size: .68rem;
        color: var(--fa-muted);
        font-weight: 700;
        letter-spacing: .06em;
        text-transform: uppercase;
    }
    .coa-input, .coa-select {
        width: 100%;
        border: 1px solid var(--fa-border);
        border-radius: 10px;
        padding: .52rem .68rem;
        font-size: .83rem;
        color: var(--fa-text);
        background: #fff;
    }
    .coa-input:focus, .coa-select:focus {
        outline: none;
        border-color: var(--fa-blue);
        box-shadow: 0 0 0 3px rgba(37,99,235,.12);
    }

    .coa-inline {
        display: flex;
        align-items: center;
        gap: .65rem;
        margin-top: .85rem;
        flex-wrap: wrap;
    }

    .coa-btn {
        border: none;
        border-radius: 10px;
        padding: .5rem .95rem;
        font-size: .8rem;
        font-weight: 700;
        cursor: pointer;
    }
    .coa-btn-primary {
        background: linear-gradient(135deg, var(--fa-blue), var(--fa-blue-dark));
        color: #fff;
    }
    .coa-btn-muted {
        background: #fff;
        color: #475569;
        border: 1px solid var(--fa-border);
        text-decoration: none;
    }
    .coa-btn-muted:hover {
        text-decoration: none;
        color: #334155;
    }

    .coa-table-wrap {
        overflow-x: auto;
        padding: .9rem 1rem 1rem;
    }
    .coa-table {
        width: 100%;
        border-collapse: collapse;
        min-width: 760px;
    }
    .coa-table th {
        font-size: .68rem;
        text-transform: uppercase;
        letter-spacing: .06em;
        color: var(--fa-muted);
        font-weight: 700;
        text-align: left;
        padding: .62rem .5rem;
        border-bottom: 1.5px solid var(--fa-border);
        background: #f8faff;
    }
    .coa-table td {
        font-size: .81rem;
        color: #334155;
        padding: .58rem .5rem;
        border-bottom: 1px solid var(--fa-border);
        vertical-align: middle;
    }
    .coa-table tbody tr:hover td {
        background: rgba(37,99,235,.03);
    }
    .coa-type-badge {
        display: inline-flex;
        align-items: center;
        padding: .18rem .52rem;
        border-radius: 999px;
        font-size: .72rem;
        font-weight: 700;
        background: rgba(37,99,235,.1);
        color: var(--fa-blue-dark);
    }
    .coa-status {
        display: inline-flex;
        align-items: center;
        gap: .32rem;
        font-size: .72rem;
        font-weight: 700;
    }
    .coa-status.active { color: #065f46; }
    .coa-status.inactive { color: #991b1b; }
    .coa-actions {
        display: flex;
        align-items: center;
        gap: .55rem;
        flex-wrap: wrap;
    }
    .coa-link {
        font-size: .75rem;
        font-weight: 700;
        color: var(--fa-blue);
        text-decoration: none;
    }
    .coa-link:hover {
        color: var(--fa-blue-dark);
        text-decoration: underline;
    }
    .coa-row-selected td {
        background: rgba(37,99,235,.08);
    }

    .coa-empty {
        text-align: center;
        padding: 1.4rem .8rem;
        color: var(--fa-muted);
        font-size: .82rem;
    }

    .coa-pagination {
        padding: 0 .95rem .95rem;
    }
    .coa-pagination .pagination {
        margin-bottom: 0;
    }

    .coa-alert {
        margin-bottom: 1rem;
        border-radius: 12px;
    }

    .coa-log-wrap {
        overflow-x: auto;
        padding: .9rem 1rem 1rem;
    }
    .coa-log-table {
        width: 100%;
        border-collapse: collapse;
        min-width: 860px;
    }
    .coa-log-table th {
        font-size: .68rem;
        text-transform: uppercase;
        letter-spacing: .06em;
        color: var(--fa-muted);
        font-weight: 700;
        text-align: left;
        padding: .62rem .5rem;
        border-bottom: 1.5px solid var(--fa-border);
        background: #f8faff;
    }
    .coa-log-table td {
        font-size: .8rem;
        color: #334155;
        padding: .58rem .5rem;
        border-bottom: 1px solid var(--fa-border);
        vertical-align: top;
    }
    .coa-log-table tbody tr:hover td {
        background: rgba(37,99,235,.03);
    }
    .coa-log-badge {
        display: inline-flex;
        align-items: center;
        padding: .2rem .54rem;
        border-radius: 999px;
        font-size: .68rem;
        font-weight: 800;
        letter-spacing: .02em;
    }
    .coa-log-badge.create {
        background: rgba(16,185,129,.12);
        color: #047857;
    }
    .coa-log-badge.update {
        background: rgba(37,99,235,.12);
        color: #1e40af;
    }
    .coa-log-badge.delete {
        background: rgba(239,68,68,.12);
        color: #b91c1c;
    }
    .coa-log-meta {
        display: inline-flex;
        align-items: center;
        gap: .35rem;
        color: #64748b;
        font-size: .72rem;
        font-weight: 600;
    }
    .coa-log-mini {
        margin-top: .15rem;
        font-size: .72rem;
        color: #64748b;
    }
    .coa-log-empty {
        text-align: center;
        padding: 1rem .7rem;
        color: var(--fa-muted);
        font-size: .8rem;
    }

    .coa-detail-body {
        padding: .95rem 1rem 1rem;
    }
    .coa-detail-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: .7rem .95rem;
    }
    @media (max-width: 1100px) {
        .coa-detail-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }
    @media (max-width: 640px) {
        .coa-detail-grid {
            grid-template-columns: 1fr;
        }
    }
    .coa-detail-item label {
        display: block;
        margin-bottom: .2rem;
        font-size: .66rem;
        font-weight: 700;
        letter-spacing: .06em;
        text-transform: uppercase;
        color: var(--fa-muted);
    }
    .coa-detail-value {
        font-size: .8rem;
        color: #0f172a;
        font-weight: 600;
        line-height: 1.35;
    }
    .coa-detail-actions {
        display: flex;
        gap: .6rem;
        margin-top: .95rem;
        flex-wrap: wrap;
    }
    .coa-detail-subtitle {
        margin-top: 1rem;
        margin-bottom: .45rem;
        font-size: .76rem;
        font-weight: 800;
        color: #1e3a8a;
        text-transform: uppercase;
        letter-spacing: .05em;
    }
    .coa-detail-log-list {
        list-style: none;
        margin: 0;
        padding: 0;
        display: grid;
        gap: .42rem;
    }
    .coa-detail-log-item {
        border: 1px solid var(--fa-border);
        border-radius: 10px;
        padding: .52rem .6rem;
        font-size: .76rem;
        color: #334155;
        background: #f8fbff;
    }
</style>

@php
    $selectedGroup = isset($selectedGroup) ? (int) $selectedGroup : null;
    $groupOrder = $groupOrder ?? [];
    $classNoSuggestions = $classNoSuggestions ?? $groupOrder;
    $coreClassifications = $coreClassifications ?? [1, 2, 3, 4, 5, 8, 9];
    $groupLabels = $groupLabels ?? [];
    $typeLabels = $typeLabels ?? [];
    $typeSuggestions = $typeSuggestions ?? [];
    $typeClassMap = $typeClassMap ?? [];
    $accountCounts = $accountCounts ?? collect();
    $accountLogs = $accountLogs ?? collect();
    $detailAccount = $detailAccount ?? null;
    $isDetailActive = $detailAccount !== null;
    $detailAccountLogs = ($isDetailActive && $detailAccount->relationLoaded('logs'))
        ? collect($detailAccount->logs)->take(10)
        : collect();
    $isEditing = isset($editAccount) && $editAccount;

    $currentType = old('type', $isEditing ? $editAccount->type : '');
    $currentClassNo = old(
        'class_no',
        $isEditing
            ? (int) $editAccount->class_no
            : ($selectedGroup ?? ($typeClassMap[$currentType] ?? ''))
    );
    $formAction = $isEditing
        ? route('finance.accounts.update', $editAccount->id)
        : route('finance.accounts.store');
    $cancelEditParams = array_filter([
        'group' => $selectedGroup ?: ($isEditing ? $editAccount->class_no : null),
        'detail' => $isDetailActive ? $detailAccount->id : null,
    ]);
@endphp

<div class="coa-header">
    <div class="coa-title-wrap">
        <div class="coa-title-icon"><i class="fas fa-sitemap"></i></div>
        <div>
            <h1 class="coa-title">Bagan Akun Finance</h1>
            <p class="coa-subtitle">Kelola kode akun, jenis akun, dan urutan klasifikasi kiri.</p>
        </div>
    </div>
</div>

@if($errors->any())
    <div class="alert alert-danger coa-alert">
        <strong>Validasi gagal:</strong>
        <ul class="mb-0 mt-2 pl-3">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="coa-layout">
    <aside class="coa-card">
        <div class="coa-card-head">
            <h3>Urutan Klasifikasi Kiri</h3>
        </div>
        <ul class="coa-side-list">
            @forelse($groupOrder as $groupNo)
                @php
                    $isActiveGroup = $selectedGroup === (int) $groupNo;
                    $groupCount = (int) ($accountCounts[$groupNo] ?? 0);
                    $groupLabel = $groupLabels[$groupNo] ?? ('Klasifikasi ' . $groupNo);
                @endphp
                <li>
                    <div class="coa-side-row">
                        <a
                            href="{{ route('finance.accounts.index', ['group' => $groupNo]) }}"
                            class="coa-side-item {{ $isActiveGroup ? 'active' : '' }}">
                            <span class="coa-side-num">{{ $groupNo }}</span>
                            <span class="coa-side-label">{{ $groupLabel }}</span>
                            <span class="coa-side-count">{{ $groupCount }}</span>
                        </a>
                        @if($groupCount > 0 && !in_array((int) $groupNo, $coreClassifications, true))
                            <form method="POST" action="{{ route('finance.accounts.classifications.destroy', $groupNo) }}" onsubmit="return confirm('Hapus klasifikasi {{ $groupNo }} beserta semua akun di dalamnya?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="coa-side-delete" title="Hapus klasifikasi">
                                    <i class="fas fa-trash-alt" style="font-size:.72rem;"></i>
                                </button>
                            </form>
                        @endif
                    </div>
                </li>
            @empty
                <li class="coa-empty">Belum ada klasifikasi.</li>
            @endforelse
        </ul>
    </aside>

    <section class="coa-main">
        <div class="coa-card">
            <div class="coa-card-head">
                <h3>{{ $isEditing ? 'Ubah Akun' : 'Tambah Akun Baru' }}</h3>
            </div>
            <div class="coa-form">
                <form method="POST" action="{{ $formAction }}">
                    @csrf
                    @if($isEditing)
                        @method('PUT')
                    @endif

                    <div class="coa-form-grid">
                        <div class="coa-field">
                            <label for="code">Kode Akun</label>
                            <input
                                type="text"
                                id="code"
                                name="code"
                                class="coa-input"
                                maxlength="64"
                                value="{{ old('code', $isEditing ? $editAccount->code : '') }}"
                                required>
                        </div>
                        <div class="coa-field">
                            <label for="name">Nama Akun</label>
                            <input
                                type="text"
                                id="name"
                                name="name"
                                class="coa-input"
                                maxlength="255"
                                value="{{ old('name', $isEditing ? $editAccount->name : '') }}"
                                required>
                        </div>
                        <div class="coa-field">
                            <label for="type">Jenis</label>
                            <input
                                type="text"
                                id="type"
                                name="type"
                                class="coa-input"
                                maxlength="64"
                                list="type_suggestions"
                                value="{{ $currentType }}"
                                placeholder="Contoh: PIUTANG / KREDIT"
                                required>
                        </div>
                        <div class="coa-field">
                            <label for="class_no">No Klasifikasi Kiri</label>
                            <input
                                type="number"
                                id="class_no"
                                name="class_no"
                                class="coa-input"
                                min="1"
                                max="255"
                                list="class_no_suggestions"
                                value="{{ $currentClassNo }}"
                                placeholder="Contoh: 6"
                                required>
                        </div>
                    </div>
                    <datalist id="type_suggestions">
                        @foreach($typeSuggestions as $typeSuggestion)
                            <option value="{{ $typeSuggestion }}">
                                {{ $typeLabels[$typeSuggestion] ?? $typeSuggestion }}
                            </option>
                        @endforeach
                    </datalist>
                    <datalist id="class_no_suggestions">
                        @foreach($classNoSuggestions as $groupNo)
                            <option value="{{ $groupNo }}">{{ $groupLabels[$groupNo] ?? ('Klasifikasi ' . $groupNo) }}</option>
                        @endforeach
                    </datalist>

                    <div class="coa-inline">
                        <input type="hidden" name="is_active" value="0">
                        <label class="mb-0" style="font-size:.78rem;color:#334155;display:inline-flex;align-items:center;gap:.4rem;">
                            <input
                                type="checkbox"
                                name="is_active"
                                value="1"
                                {{ old('is_active', $isEditing ? (int) $editAccount->is_active : 1) ? 'checked' : '' }}>
                            Aktifkan akun
                        </label>

                        <button type="submit" class="coa-btn coa-btn-primary">
                            <i class="fas fa-save"></i> {{ $isEditing ? 'Simpan Perubahan' : 'Simpan Akun' }}
                        </button>

                        @if($isEditing)
                            <a href="{{ route('finance.accounts.index', $cancelEditParams) }}" class="coa-btn coa-btn-muted">
                                Batal Ubah
                            </a>
                        @endif
                    </div>
                </form>
            </div>
        </div>

        @if($isDetailActive)
            <div class="coa-card">
                <div class="coa-card-head">
                    <h3>Detail Bagan Akun</h3>
                </div>
                <div class="coa-detail-body">
                    <div class="coa-detail-grid">
                        <div class="coa-detail-item">
                            <label>Kode Akun</label>
                            <div class="coa-detail-value">{{ $detailAccount->code }}</div>
                        </div>
                        <div class="coa-detail-item">
                            <label>Nama Akun</label>
                            <div class="coa-detail-value">{{ $detailAccount->name }}</div>
                        </div>
                        <div class="coa-detail-item">
                            <label>Jenis Akun</label>
                            <div class="coa-detail-value">
                                <span class="coa-type-badge">{{ $detailAccount->type_label }}</span>
                            </div>
                        </div>
                        <div class="coa-detail-item">
                            <label>Klasifikasi Kiri</label>
                            <div class="coa-detail-value">
                                {{ $detailAccount->class_no }} - {{ $groupLabels[$detailAccount->class_no] ?? ('Klasifikasi ' . $detailAccount->class_no) }}
                            </div>
                        </div>
                        <div class="coa-detail-item">
                            <label>Status</label>
                            <div class="coa-detail-value">
                                <span class="coa-status {{ $detailAccount->is_active ? 'active' : 'inactive' }}">
                                    <i class="fas fa-circle" style="font-size:.5rem;"></i>
                                    {{ $detailAccount->is_active ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </div>
                        </div>
                        <div class="coa-detail-item">
                            <label>Dibuat Oleh</label>
                            <div class="coa-detail-value">{{ $detailAccount->creator?->name ?? '-' }}</div>
                        </div>
                        <div class="coa-detail-item">
                            <label>Tanggal Dibuat</label>
                            <div class="coa-detail-value">
                                {{ $detailAccount->created_at?->timezone(config('app.timezone'))->format('d/m/Y H:i:s') ?? '-' }} WIB
                            </div>
                        </div>
                        <div class="coa-detail-item">
                            <label>Diubah Oleh</label>
                            <div class="coa-detail-value">{{ $detailAccount->updater?->name ?? '-' }}</div>
                        </div>
                        <div class="coa-detail-item">
                            <label>Terakhir Diubah</label>
                            <div class="coa-detail-value">
                                {{ $detailAccount->updated_at?->timezone(config('app.timezone'))->format('d/m/Y H:i:s') ?? '-' }} WIB
                            </div>
                        </div>
                    </div>

                    <div class="coa-detail-actions">
                        <a
                            href="{{ route('finance.accounts.index', array_filter(['group' => $selectedGroup ?: $detailAccount->class_no, 'edit' => $detailAccount->id, 'detail' => $detailAccount->id])) }}"
                            class="coa-btn coa-btn-primary">
                            Ubah Akun Ini
                        </a>
                        <a
                            href="{{ route('finance.accounts.index', array_filter(['group' => $selectedGroup ?: $detailAccount->class_no])) }}"
                            class="coa-btn coa-btn-muted">
                            Tutup Detail
                        </a>
                    </div>

                    @if($detailAccountLogs->isNotEmpty())
                        <div class="coa-detail-subtitle">Riwayat Akun Ini</div>
                        <ul class="coa-detail-log-list">
                            @foreach($detailAccountLogs as $detailLog)
                                @php
                                    $detailActionName = strtoupper((string) $detailLog->action);
                                @endphp
                                <li class="coa-detail-log-item">
                                    <strong>{{ in_array($detailActionName, ['CREATED', 'UPDATED', 'DELETED'], true) ? $detailActionName : 'UPDATED' }}</strong>
                                    <span style="color:#64748b;">
                                        oleh {{ $detailLog->actor?->name ?? '-' }}
                                        pada {{ $detailLog->created_at?->timezone(config('app.timezone'))->format('d/m/Y H:i:s') ?? '-' }} WIB
                                    </span>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>
        @endif

        <div class="coa-card">
            <div class="coa-card-head">
                <h3>
                    Daftar Akun
                    {{ $selectedGroup
                        ? '(' . ($groupLabels[$selectedGroup] ?? ('Klasifikasi ' . $selectedGroup)) . ' - ' . $selectedGroup . ')'
                        : '' }}
                </h3>
            </div>

            <div class="coa-table-wrap">
                <table class="coa-table">
                    <thead>
                        <tr>
                            <th style="width:52px;">#</th>
                            <th style="width:140px;">Kode</th>
                            <th>Nama Akun</th>
                            <th style="width:200px;">Jenis</th>
                            <th style="width:92px;">Urutan</th>
                            <th style="width:110px;">Status</th>
                            <th style="width:150px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($accounts as $account)
                            @php
                                $isDetailRow = $isDetailActive && (string) $detailAccount->id === (string) $account->id;
                            @endphp
                            <tr class="{{ $isDetailRow ? 'coa-row-selected' : '' }}">
                                <td>{{ $accounts->firstItem() + $loop->index }}</td>
                                <td style="font-weight:700;color:#1e3a8a;">{{ $account->code }}</td>
                                <td>{{ $account->name }}</td>
                                <td>
                                    <span class="coa-type-badge">{{ $account->type_label }}</span>
                                </td>
                                <td style="font-weight:700;">{{ $account->class_no }}</td>
                                <td>
                                    <span class="coa-status {{ $account->is_active ? 'active' : 'inactive' }}">
                                        <i class="fas fa-circle" style="font-size:.5rem;"></i>
                                        {{ $account->is_active ? 'Aktif' : 'Nonaktif' }}
                                    </span>
                                </td>
                                <td>
                                    <div class="coa-actions">
                                        <a
                                            href="{{ route('finance.accounts.index', array_filter(['group' => $selectedGroup ?: $account->class_no, 'detail' => $account->id])) }}"
                                            class="coa-link">
                                            Detail
                                        </a>
                                        <a
                                            href="{{ route('finance.accounts.index', array_filter(['group' => $selectedGroup ?: $account->class_no, 'edit' => $account->id, 'detail' => $account->id])) }}"
                                            class="coa-link">
                                            Ubah
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="coa-empty">Belum ada akun pada klasifikasi ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($accounts->hasPages())
                <div class="coa-pagination">
                    {{ $accounts->links() }}
                </div>
            @endif
        </div>

        <div class="coa-card">
            <div class="coa-card-head">
                <h3>Log Aktivitas Bagan Akun</h3>
            </div>

            <div class="coa-log-wrap">
                <table class="coa-log-table">
                    <thead>
                        <tr>
                            <th style="width:56px;">#</th>
                            <th style="width:180px;">Waktu</th>
                            <th style="width:140px;">Aksi</th>
                            <th style="width:220px;">Akun</th>
                            <th style="width:170px;">Pelaku</th>
                            <th>Ringkasan Perubahan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($accountLogs as $index => $log)
                            @php
                                $after = is_array($log->after_data) ? $log->after_data : [];
                                $before = is_array($log->before_data) ? $log->before_data : [];
                                $codeAfter = $after['code'] ?? null;
                                $nameAfter = $after['name'] ?? null;
                                $typeAfter = $after['type_label'] ?? ($after['type'] ?? null);
                                $codeBefore = $before['code'] ?? null;
                                $nameBefore = $before['name'] ?? null;
                                $typeBefore = $before['type_label'] ?? ($before['type'] ?? null);
                            @endphp
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>
                                    <div style="font-weight:700;color:#1e3a8a;">
                                        {{ $log->created_at?->timezone(config('app.timezone'))->format('d/m/Y') ?? '-' }}
                                    </div>
                                    <div class="coa-log-mini">
                                        {{ $log->created_at?->timezone(config('app.timezone'))->format('H:i:s') ?? '-' }} WIB
                                    </div>
                                </td>
                                <td>
                                    @php
                                        $actionName = strtoupper((string) $log->action);
                                        $actionClass = match ($actionName) {
                                            'CREATED' => 'create',
                                            'DELETED' => 'delete',
                                            default => 'update',
                                        };
                                    @endphp
                                    <span class="coa-log-badge {{ $actionClass }}">
                                        {{ in_array($actionName, ['CREATED', 'UPDATED', 'DELETED'], true) ? $actionName : 'UPDATED' }}
                                    </span>
                                </td>
                                <td>
                                    <div style="font-weight:700;color:#0f172a;">
                                        {{ $log->account?->code ?? ($codeAfter ?? $codeBefore ?? '-') }}
                                    </div>
                                    <div class="coa-log-mini">
                                        {{ $log->account?->name ?? ($nameAfter ?? $nameBefore ?? '-') }}
                                    </div>
                                </td>
                                <td>
                                    <span class="coa-log-meta">
                                        <i class="fas fa-user"></i>
                                        {{ $log->actor?->name ?? '-' }}
                                    </span>
                                </td>
                                <td>
                                    @if($actionName === 'CREATED')
                                        <span>Kode <strong>{{ $codeAfter ?? '-' }}</strong> ditambahkan sebagai <strong>{{ $typeAfter ?? '-' }}</strong>.</span>
                                    @elseif($actionName === 'DELETED')
                                        <span>Kode <strong>{{ $codeBefore ?? '-' }}</strong> pada jenis <strong>{{ $typeBefore ?? '-' }}</strong> dihapus dari klasifikasi kiri.</span>
                                    @else
                                        <div>Kode: <strong>{{ $codeBefore ?? '-' }}</strong> -> <strong>{{ $codeAfter ?? '-' }}</strong></div>
                                        <div>Nama: <strong>{{ $nameBefore ?? '-' }}</strong> -> <strong>{{ $nameAfter ?? '-' }}</strong></div>
                                        <div>Jenis: <strong>{{ $typeBefore ?? '-' }}</strong> -> <strong>{{ $typeAfter ?? '-' }}</strong></div>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="coa-log-empty">Belum ada log aktivitas bagan akun.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </section>
</div>
@endsection

@section('js')
<script>
    (function () {
        const typeInput = document.getElementById('type');
        const classInput = document.getElementById('class_no');
        const typeClassMap = @json($typeClassMap);
        let classOverridden = false;

        if (!typeInput || !classInput) {
            return;
        }

        function normalizeType(value) {
            return String(value || '').trim().toUpperCase();
        }

        function syncClassByType(forceUpdate) {
            if (classOverridden && !forceUpdate) {
                return;
            }

            if (forceUpdate && classInput.value !== '') {
                return;
            }

            const typeKey = normalizeType(typeInput.value);
            const classNo = typeClassMap[typeKey];
            if (typeof classNo !== 'undefined' && String(classNo).trim() !== '') {
                classInput.value = classNo;
            }
        }

        classInput.addEventListener('input', function () {
            classOverridden = String(classInput.value).trim() !== '';
        });

        typeInput.addEventListener('change', function () {
            typeInput.value = normalizeType(typeInput.value);
            syncClassByType(false);
        });

        typeInput.addEventListener('blur', function () {
            typeInput.value = normalizeType(typeInput.value);
        });

        syncClassByType(true);
    })();
</script>
@endsection


