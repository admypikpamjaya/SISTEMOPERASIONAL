@extends('layouts.app')

@section('title', 'Finance Tunggakan')

@section('content')
@php
    $filters = $filters ?? [];
    $stats = $stats ?? [];
    $editRecord = $editRecord ?? null;
    $whatsappTemplates = $whatsappTemplates ?? collect();
    $defaultSyncMonth = $defaultSyncMonth ?? now()->format('F Y');
    $formatRupiah = static fn ($value) => 'Rp ' . number_format((float) $value, 0, ',', '.');

    $sourceLabels = [
        'excel' => 'Excel',
        'manual' => 'Manual',
        'database' => 'DB Siswa',
    ];

    $matchLabels = [
        'matched' => 'Matched',
        'unmatched' => 'Unmatched',
        'multiple' => 'Multiple',
        'manual' => 'Manual',
    ];

    $blastLabels = [
        'draft' => 'Draft',
        'queued' => 'Queued',
        'sent' => 'Sent',
        'failed' => 'Failed',
    ];
@endphp

<style>
@import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');

:root {
    --tg-bg: #ecf2f9;
    --tg-card: #ffffff;
    --tg-border: #dce6f4;
    --tg-text: #0f172a;
    --tg-muted: #64748b;
    --tg-primary: #1e40af;
    --tg-primary-soft: #3b82f6;
    --tg-success: #16a34a;
    --tg-warning: #d97706;
    --tg-danger: #dc2626;
    --tg-shadow: 0 10px 28px rgba(15, 23, 42, .08);
}

.tg-shell {
    font-family: 'Plus Jakarta Sans', sans-serif;
    color: var(--tg-text);
    padding: 24px;
    min-height: calc(100vh - 60px);
    background:
        radial-gradient(circle at 92% -8%, rgba(59, 130, 246, .18) 0%, transparent 33%),
        radial-gradient(circle at -5% 25%, rgba(37, 99, 235, .10) 0%, transparent 40%),
        var(--tg-bg);
}

.tg-hero {
    border-radius: 22px;
    padding: 28px 30px;
    margin-bottom: 18px;
    background: linear-gradient(135deg, #0f1a3d 0%, #1e3a8a 52%, #2563eb 100%);
    color: #fff;
    box-shadow: 0 16px 34px rgba(15, 26, 61, .28);
    position: relative;
    overflow: hidden;
}

.tg-hero::before {
    content: '';
    position: absolute;
    right: -80px;
    top: -80px;
    width: 240px;
    height: 240px;
    border-radius: 50%;
    background: radial-gradient(circle, rgba(255, 255, 255, .18) 0%, transparent 66%);
}

.tg-hero h1 {
    margin: 0 0 8px;
    font-size: 30px;
    font-weight: 800;
    letter-spacing: -.02em;
}

.tg-hero p {
    margin: 0;
    max-width: 760px;
    font-size: 14px;
    line-height: 1.65;
    opacity: .92;
}

.tg-alert {
    padding: 12px 14px;
    border-radius: 12px;
    font-size: 12.5px;
    font-weight: 700;
    margin-bottom: 12px;
}

.tg-alert.success {
    border: 1px solid #86efac;
    background: #f0fdf4;
    color: #166534;
}

.tg-alert.error {
    border: 1px solid #fecaca;
    background: #fef2f2;
    color: #991b1b;
}

.tg-alert.warn {
    border: 1px solid #fde68a;
    background: #fffbeb;
    color: #92400e;
}

.tg-metrics {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 12px;
    margin-bottom: 16px;
}

.tg-metric {
    border: 1px solid var(--tg-border);
    border-radius: 16px;
    background: var(--tg-card);
    padding: 16px;
    box-shadow: var(--tg-shadow);
}

.tg-metric .label {
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: .04em;
    font-weight: 800;
    color: var(--tg-muted);
    margin-bottom: 8px;
}

.tg-metric .value {
    font-size: 26px;
    font-weight: 800;
    color: #0b2d7a;
    line-height: 1.05;
}

.tg-panel {
    border: 1px solid var(--tg-border);
    border-radius: 18px;
    background: var(--tg-card);
    box-shadow: var(--tg-shadow);
    margin-bottom: 16px;
    overflow: hidden;
}

.tg-panel-head {
    padding: 14px 18px;
    border-bottom: 1px solid var(--tg-border);
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    background: linear-gradient(180deg, #f8fbff, #f3f8ff);
}

.tg-panel-title {
    margin: 0;
    font-size: 14px;
    font-weight: 800;
    color: #1e3a8a;
}

.tg-panel-note {
    margin: 0;
    font-size: 12px;
    color: var(--tg-muted);
}

.tg-panel-body {
    padding: 16px 18px 18px;
}

.tg-actions-grid {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 12px;
}

.tg-action-card {
    border: 1px solid var(--tg-border);
    border-radius: 14px;
    padding: 14px;
    background: #fbfdff;
}

.tg-action-label {
    font-size: 12px;
    font-weight: 800;
    margin-bottom: 8px;
    color: #1e3a8a;
}

.tg-row {
    display: flex;
    gap: 8px;
    align-items: center;
    flex-wrap: wrap;
}

.tg-form-grid {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 12px;
}

.tg-field {
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.tg-label {
    font-size: 12px;
    font-weight: 700;
    color: #334155;
}

.tg-input,
.tg-select {
    width: 100%;
    border: 1px solid var(--tg-border);
    border-radius: 10px;
    padding: 9px 11px;
    font-size: 12.5px;
    font-family: inherit;
    color: var(--tg-text);
    background: #fff;
}

.tg-input:focus,
.tg-select:focus {
    outline: none;
    border-color: #93c5fd;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, .16);
}

.tg-actions {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
}

.tg-btn {
    border: 1px solid transparent;
    border-radius: 10px;
    padding: 9px 13px;
    font-size: 12px;
    font-weight: 800;
    font-family: inherit;
    cursor: pointer;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    transition: .15s ease;
}

.tg-btn:hover {
    transform: translateY(-1px);
}

.tg-btn.primary {
    color: #fff;
    background: linear-gradient(135deg, #1d4ed8, #2563eb);
}

.tg-btn.ghost {
    color: #1d4ed8;
    border-color: #bfdbfe;
    background: #eff6ff;
}

.tg-btn.warn {
    color: #fff;
    background: linear-gradient(135deg, #d97706, #b45309);
}

.tg-btn.danger {
    color: #fff;
    background: linear-gradient(135deg, #dc2626, #b91c1c);
}

.tg-file-wrap {
    position: relative;
    overflow: hidden;
}

.tg-file-wrap input[type=file] {
    position: absolute;
    inset: 0;
    opacity: 0;
    cursor: pointer;
}

.tg-hint {
    margin-top: 10px;
    font-size: 12px;
    color: var(--tg-muted);
    line-height: 1.6;
}

.tg-table-wrap {
    border: 1px solid var(--tg-border);
    border-radius: 14px;
    overflow: auto;
}

.tg-table {
    width: 100%;
    border-collapse: collapse;
    min-width: 1120px;
}

.tg-table th {
    position: sticky;
    top: 0;
    z-index: 1;
    background: #f4f8ff;
    color: #5b6b85;
    text-transform: uppercase;
    letter-spacing: .05em;
    font-size: 10.5px;
    font-weight: 800;
    padding: 10px 12px;
    border-bottom: 1px solid var(--tg-border);
    white-space: nowrap;
}

.tg-table td {
    font-size: 12.5px;
    padding: 11px 12px;
    border-bottom: 1px solid #edf2f9;
    vertical-align: top;
}

.tg-table tr:hover td {
    background: #f8fbff;
}

.tg-name {
    font-weight: 800;
    color: #0f172a;
}

.tg-meta {
    margin-top: 3px;
    color: var(--tg-muted);
    font-size: 11px;
}

.tg-badge {
    border-radius: 999px;
    padding: 4px 9px;
    font-size: 10.5px;
    font-weight: 800;
    display: inline-flex;
}

.tg-badge.match-matched { background: #dcfce7; color: #166534; }
.tg-badge.match-unmatched { background: #fee2e2; color: #991b1b; }
.tg-badge.match-multiple { background: #fef3c7; color: #92400e; }
.tg-badge.match-manual { background: #dbeafe; color: #1d4ed8; }

.tg-badge.blast-draft { background: #e2e8f0; color: #334155; }
.tg-badge.blast-queued { background: #dbeafe; color: #1d4ed8; }
.tg-badge.blast-sent { background: #dcfce7; color: #166534; }
.tg-badge.blast-failed { background: #fee2e2; color: #991b1b; }

.tg-inline-actions {
    display: flex;
    gap: 6px;
    flex-wrap: wrap;
}

.tg-empty {
    padding: 22px;
    text-align: center;
    color: var(--tg-muted);
    font-size: 13px;
}

@media (max-width: 1200px) {
    .tg-actions-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
}

@media (max-width: 1100px) {
    .tg-metrics {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .tg-form-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
}

@media (max-width: 760px) {
    .tg-shell {
        padding: 12px;
    }

    .tg-hero {
        padding: 20px 18px;
        border-radius: 16px;
    }

    .tg-hero h1 {
        font-size: 24px;
    }

    .tg-metrics,
    .tg-actions-grid,
    .tg-form-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<div class="tg-shell">
    <div
        id="tunggakanAutoSync"
        data-version="{{ $recordsVersion ?? '0' }}"
        data-url="{{ route('finance.tunggakan.version') }}"
        data-editing="{{ $editRecord ? '1' : '0' }}"
        style="display:none;"
    ></div>

    <section class="tg-hero">
        <h1>Finance Tunggakan</h1>
        <p>Kelola data tunggakan siswa untuk kebutuhan blasting. Data bisa masuk dari import Excel, input manual, atau sinkronisasi dari database recipient siswa.</p>
    </section>

    @if(session('success'))
        <div class="tg-alert success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="tg-alert error">{{ session('error') }}</div>
    @endif
    @if(session('warning'))
        <div class="tg-alert warn">{{ session('warning') }}</div>
    @endif
    @if($errors->any())
        <div class="tg-alert error">{{ $errors->first() }}</div>
    @endif

    <section class="tg-metrics">
        <article class="tg-metric">
            <div class="label">Total Data</div>
            <div class="value">{{ number_format((int) ($stats['total_records'] ?? 0), 0, ',', '.') }}</div>
        </article>
        <article class="tg-metric">
            <div class="label">Total Nominal</div>
            <div class="value" style="font-size:20px;">{{ $formatRupiah($stats['total_nilai'] ?? 0) }}</div>
        </article>
        <article class="tg-metric">
            <div class="label">Matched Recipient</div>
            <div class="value">{{ number_format((int) ($stats['matched_records'] ?? 0), 0, ',', '.') }}</div>
        </article>
        <article class="tg-metric">
            <div class="label">Blast Sent</div>
            <div class="value">{{ number_format((int) ($stats['blast_sent_records'] ?? 0), 0, ',', '.') }}</div>
        </article>
    </section>

    <section class="tg-panel">
        <div class="tg-panel-head">
            <h2 class="tg-panel-title">Integrasi Data</h2>
            <p class="tg-panel-note">Sinkronisasi DB hanya mengambil recipient siswa</p>
        </div>
        <div class="tg-panel-body">
            <div class="tg-actions-grid">
                <div class="tg-action-card">
                    <div class="tg-action-label">Import Excel</div>
                    <form action="{{ route('finance.tunggakan.import') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <label class="tg-btn primary tg-file-wrap">
                            Pilih File Excel
                            <input type="file" name="file" accept=".xlsx,.xls,.csv" onchange="if(this.files.length){ this.form.submit(); }" required>
                        </label>
                    </form>
                    <div class="tg-hint">Gunakan format: <strong>no | kelas | nama murid | bulan | nilai | no telepon (opsional)</strong>. Kolom nilai bisa langsung angka rupiah (contoh: <strong>3.100.000</strong>) atau format terpisah <strong>Rp | 3.100.000</strong>.</div>
                </div>

                <div class="tg-action-card">
                    <div class="tg-action-label">Sync Recipient Siswa</div>
                    <form action="{{ route('finance.tunggakan.sync-db') }}" method="POST" onsubmit="return confirm('Sinkron data dari recipient siswa sekarang?');">
                        @csrf
                        <div class="tg-row">
                            <input class="tg-input" style="min-width:200px;" type="text" name="bulan_sync" value="{{ old('bulan_sync', $defaultSyncMonth) }}" placeholder="Contoh: Januari 2026">
                            <button type="submit" class="tg-btn warn">Sync DB</button>
                        </div>
                    </form>
                    <div class="tg-hint">Data hasil sync akan otomatis matched ke recipient siswa.</div>
                </div>

                <div class="tg-action-card">
                    <div class="tg-action-label">Blast WhatsApp Tunggakan</div>
                    <form action="{{ route('finance.tunggakan.blast-whatsapp') }}" method="POST" onsubmit="return confirm('Blast WA dari data tunggakan draft/failed sekarang?');">
                        @csrf
                        <div class="tg-field" style="margin-bottom:10px;">
                            <select class="tg-select" name="template_id">
                                <option value="">Template default tunggakan</option>
                                @foreach($whatsappTemplates as $template)
                                    <option value="{{ $template->id }}">{{ $template->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <button type="submit" class="tg-btn primary">Blast WA Sekarang</button>
                    </form>
                    <div class="tg-hint">Diproses: data <strong>matched siswa</strong> atau data dengan <strong>no telepon valid</strong> (status blast draft/failed).</div>
                </div>

                <div class="tg-action-card">
                    <div class="tg-action-label">Template Blasting</div>
                    <form action="{{ route('finance.tunggakan.template-default') }}" method="POST">
                        @csrf
                        <button type="submit" class="tg-btn ghost">Generate Template Default</button>
                    </form>
                    <div class="tg-hint">Placeholder: <strong>{bulan_tunggakan}</strong>, <strong>{nilai_tunggakan_rupiah}</strong>, <strong>{total_tunggakan_rupiah}</strong>, <strong>{tagihan_rupiah}</strong>.</div>
                </div>
            </div>
        </div>
    </section>

    <section class="tg-panel">
        <div class="tg-panel-head">
            <h2 class="tg-panel-title">Danger Zone</h2>
            <p class="tg-panel-note">Aksi ini akan menghapus seluruh data tagihan tunggakan.</p>
        </div>
        <div class="tg-panel-body">
            <form action="{{ route('finance.tunggakan.destroy-all') }}" method="POST" onsubmit="return confirm('Hapus semua data tagihan tunggakan? Tindakan ini tidak bisa dibatalkan.');">
                @csrf
                @method('DELETE')
                <button type="submit" class="tg-btn danger">Delete All Tagihan</button>
            </form>
        </div>
    </section>

    <section class="tg-panel">
        <div class="tg-panel-head">
            <h2 class="tg-panel-title">{{ $editRecord ? 'Edit Data Tunggakan' : 'Input Manual Tunggakan' }}</h2>
            @if($editRecord)
                <a href="{{ route('finance.tunggakan.index') }}" class="tg-btn ghost">Batal Edit</a>
            @endif
        </div>
        <div class="tg-panel-body">
            <form action="{{ $editRecord ? route('finance.tunggakan.update', $editRecord->id) : route('finance.tunggakan.manual.store') }}" method="POST">
                @csrf
                @if($editRecord)
                    @method('PUT')
                @endif

                <div class="tg-form-grid">
                    <div class="tg-field">
                        <label class="tg-label">No (opsional)</label>
                        <input class="tg-input" type="number" name="no_urut" min="1" max="999999" value="{{ old('no_urut', $editRecord?->no_urut) }}">
                    </div>
                    <div class="tg-field">
                        <label class="tg-label">Kelas</label>
                        <input class="tg-input" type="text" name="kelas" maxlength="100" value="{{ old('kelas', $editRecord?->kelas) }}" placeholder="Contoh: Sistem Informasi">
                    </div>
                    <div class="tg-field">
                        <label class="tg-label">Nama Murid</label>
                        <input class="tg-input" type="text" name="nama_murid" maxlength="255" value="{{ old('nama_murid', $editRecord?->nama_murid) }}" required>
                    </div>
                    <div class="tg-field">
                        <label class="tg-label">No Telepon (opsional)</label>
                        <input class="tg-input" type="text" name="no_telepon" maxlength="30" value="{{ old('no_telepon', $editRecord?->no_telepon) }}" placeholder="Contoh: 0812-3456-7890">
                    </div>
                    <div class="tg-field">
                        <label class="tg-label">Bulan / Periode</label>
                        <input class="tg-input" type="text" name="bulan" maxlength="100" value="{{ old('bulan', $editRecord?->bulan) }}" placeholder="Contoh: Januari-Februari" required>
                    </div>
                    <div class="tg-field" style="grid-column:1 / -1;">
                        <label class="tg-label">Nilai (Rupiah)</label>
                        <input class="tg-input" type="text" name="nilai" maxlength="50" value="{{ old('nilai', $editRecord ? $formatRupiah($editRecord->nilai) : null) }}" placeholder="Contoh: 10.000.000" required>
                    </div>
                </div>

                <div class="tg-actions" style="margin-top:12px;">
                    <button type="submit" class="tg-btn primary">{{ $editRecord ? 'Simpan Perubahan' : 'Tambah Data' }}</button>
                </div>
            </form>
        </div>
    </section>

    <section class="tg-panel">
        <div class="tg-panel-head">
            <h2 class="tg-panel-title">Daftar Data Tunggakan</h2>
            <p class="tg-panel-note">Perlu review: {{ number_format((int) ($stats['needs_review_records'] ?? 0), 0, ',', '.') }} data</p>
        </div>
        <div class="tg-panel-body">
            <form method="GET" action="{{ route('finance.tunggakan.index') }}" class="tg-form-grid" style="margin-bottom:14px;">
                <div class="tg-field">
                    <label class="tg-label">Cari</label>
                    <input class="tg-input" type="text" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Nama murid / kelas / bulan">
                </div>
                <div class="tg-field">
                    <label class="tg-label">Sumber</label>
                    <select class="tg-select" name="source_type">
                        <option value="all" @selected(($filters['source_type'] ?? 'all') === 'all')>Semua</option>
                        <option value="excel" @selected(($filters['source_type'] ?? 'all') === 'excel')>Excel</option>
                        <option value="manual" @selected(($filters['source_type'] ?? 'all') === 'manual')>Manual</option>
                        <option value="database" @selected(($filters['source_type'] ?? 'all') === 'database')>DB Siswa</option>
                    </select>
                </div>
                <div class="tg-field">
                    <label class="tg-label">Match Status</label>
                    <select class="tg-select" name="match_status">
                        <option value="all" @selected(($filters['match_status'] ?? 'all') === 'all')>Semua</option>
                        <option value="matched" @selected(($filters['match_status'] ?? 'all') === 'matched')>Matched</option>
                        <option value="unmatched" @selected(($filters['match_status'] ?? 'all') === 'unmatched')>Unmatched</option>
                        <option value="multiple" @selected(($filters['match_status'] ?? 'all') === 'multiple')>Multiple</option>
                        <option value="manual" @selected(($filters['match_status'] ?? 'all') === 'manual')>Manual</option>
                    </select>
                </div>
                <div class="tg-field">
                    <label class="tg-label">Blast Status</label>
                    <select class="tg-select" name="blast_status">
                        <option value="all" @selected(($filters['blast_status'] ?? 'all') === 'all')>Semua</option>
                        <option value="draft" @selected(($filters['blast_status'] ?? 'all') === 'draft')>Draft</option>
                        <option value="queued" @selected(($filters['blast_status'] ?? 'all') === 'queued')>Queued</option>
                        <option value="sent" @selected(($filters['blast_status'] ?? 'all') === 'sent')>Sent</option>
                        <option value="failed" @selected(($filters['blast_status'] ?? 'all') === 'failed')>Failed</option>
                    </select>
                </div>
                <div class="tg-field">
                    <label class="tg-label">Per Halaman</label>
                    <select class="tg-select" name="per_page">
                        @foreach([20, 50, 100, 200] as $size)
                            <option value="{{ $size }}" @selected((int) ($filters['per_page'] ?? 50) === $size)>{{ $size }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="tg-field" style="justify-content:flex-end;">
                    <label class="tg-label">&nbsp;</label>
                    <div class="tg-actions">
                        <button type="submit" class="tg-btn primary">Terapkan</button>
                        <a href="{{ route('finance.tunggakan.index') }}" class="tg-btn ghost">Reset</a>
                    </div>
                </div>
            </form>

            <div class="tg-table-wrap">
                <table class="tg-table">
                    <thead>
                        <tr>
                            <th style="width:52px;">No</th>
                            <th>Nama Murid</th>
                            <th>Kelas</th>
                            <th>Bulan</th>
                            <th>Nilai</th>
                            <th>No Telepon</th>
                            <th>Sumber</th>
                            <th>Match</th>
                            <th>Blast</th>
                            <th>Catatan</th>
                            <th style="width:130px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($records as $record)
                            @php
                                $source = strtolower((string) optional($record->batch)->source_type);
                                $match = strtolower((string) $record->match_status);
                                $blast = strtolower((string) $record->blast_status);
                            @endphp
                            <tr>
                                <td>{{ ($records->currentPage() - 1) * $records->perPage() + $loop->iteration }}</td>
                                <td>
                                    <div class="tg-name">{{ $record->nama_murid }}</div>
                                    @if(!empty($record->recipient_id))
                                        <div class="tg-meta">Recipient: {{ $record->recipient_source }} / {{ $record->recipient_id }}</div>
                                    @endif
                                </td>
                                <td>{{ $record->kelas ?? '-' }}</td>
                                <td>{{ $record->bulan }}</td>
                                <td>{{ $formatRupiah($record->nilai) }}</td>
                                <td>{{ $record->no_telepon ?: '-' }}</td>
                                <td>
                                    <span class="tg-meta">{{ $sourceLabels[$source] ?? ucfirst($source ?: '-') }}</span><br>
                                    <span class="tg-meta">{{ optional($record->batch)->source_reference ?? '-' }}</span>
                                </td>
                                <td>
                                    <span class="tg-badge match-{{ $match }}">{{ $matchLabels[$match] ?? strtoupper($match) }}</span>
                                </td>
                                <td>
                                    <span class="tg-badge blast-{{ $blast }}">{{ $blastLabels[$blast] ?? strtoupper($blast) }}</span>
                                </td>
                                <td><span class="tg-meta">{{ $record->match_notes ?? '-' }}</span></td>
                                <td>
                                    <div class="tg-inline-actions">
                                        <a class="tg-btn ghost" href="{{ route('finance.tunggakan.index', array_merge(request()->query(), ['edit' => $record->id])) }}">Edit</a>
                                        <form method="POST" action="{{ route('finance.tunggakan.destroy', $record->id) }}" onsubmit="return confirm('Hapus data tunggakan ini?');">
                                            @csrf
                                            @method('DELETE')
                                            <button class="tg-btn danger" type="submit">Hapus</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="11" class="tg-empty">Belum ada data tunggakan. Silakan input manual, import Excel, atau sync dari DB siswa.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($records->hasPages())
                <div style="margin-top:12px;">
                    {{ $records->links() }}
                </div>
            @endif
        </div>
    </section>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const syncNode = document.getElementById('tunggakanAutoSync');
    if (!syncNode) {
        return;
    }

    if (syncNode.dataset.editing === '1') {
        return;
    }

    const endpoint = syncNode.dataset.url;
    let currentVersion = String(syncNode.dataset.version || '0');
    let requestInFlight = false;

    const poll = async () => {
        if (requestInFlight || !endpoint) {
            return;
        }

        requestInFlight = true;

        try {
            const response = await fetch(endpoint, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                cache: 'no-store',
            });

            if (!response.ok) {
                return;
            }

            const payload = await response.json();
            const nextVersion = String(payload.version || '0');
            if (nextVersion !== currentVersion) {
                window.location.reload();
                return;
            }

            currentVersion = nextVersion;
        } catch (error) {
            // silent fail, retry di interval berikutnya
        } finally {
            requestInFlight = false;
        }
    };

    setInterval(poll, 10000);
});
</script>
@endsection
