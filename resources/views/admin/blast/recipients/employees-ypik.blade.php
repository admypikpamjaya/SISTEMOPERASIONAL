@extends('layouts.app')

@section('title', 'Recipient Karyawan YPIK')

@section('content')
<style>
@import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');

:root {
    --ypk-blue-900: #0f2d57;
    --ypk-blue-800: #0d9488;
    --ypk-blue-700: #0f766e;
    --ypk-blue-100: #ccfbf1;
    --ypk-blue-50: #f0fdfa;
    --ypk-text-900: #0f172a;
    --ypk-text-700: #334155;
    --ypk-text-500: #64748b;
    --ypk-border: #dbe4f0;
    --ypk-bg: #f3fbfb;
}

.ypk-page {
    font-family: 'Plus Jakarta Sans', sans-serif;
    color: var(--ypk-text-900);
    padding: 4px 2px 16px;
}

.ypk-head {
    border-radius: 14px;
    padding: 20px 22px;
    margin-bottom: 14px;
    background: linear-gradient(135deg, var(--ypk-blue-900) 0%, var(--ypk-blue-800) 60%, var(--ypk-blue-700) 100%);
    box-shadow: 0 12px 24px rgba(13, 148, 136, .24);
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    flex-wrap: wrap;
    gap: 12px;
}

.ypk-head-title {
    font-size: 20px;
    font-weight: 800;
    color: #fff;
    margin-bottom: 4px;
}

.ypk-head-sub {
    font-size: 12px;
    color: rgba(255, 255, 255, .88);
}

.ypk-head-actions {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
}

.ypk-btn {
    border-radius: 8px;
    border: 1px solid transparent;
    font-size: 12px;
    font-weight: 700;
    line-height: 1.2;
    padding: 8px 11px;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    transition: .15s;
}

.ypk-btn:hover {
    transform: translateY(-1px);
}

.ypk-btn.ghost {
    color: #fff;
    border-color: rgba(255, 255, 255, .38);
    background: rgba(255, 255, 255, .1);
}

.ypk-panel {
    border: 1px solid var(--ypk-border);
    border-radius: 14px;
    background: #fff;
    box-shadow: 0 8px 18px rgba(15, 23, 42, .06);
    overflow: hidden;
}

.ypk-panel-body {
    padding: 16px;
}

.ypk-alert {
    border-radius: 10px;
    padding: 10px 12px;
    font-size: 12.5px;
    font-weight: 600;
    margin-bottom: 12px;
}

.ypk-alert.success {
    border: 1px solid #86efac;
    background: #f0fdf4;
    color: #166534;
}

.ypk-alert.error {
    border: 1px solid #fecaca;
    background: #fef2f2;
    color: #991b1b;
}

.ypk-stats {
    display: grid;
    grid-template-columns: repeat(3, minmax(0,1fr));
    gap: 10px;
    margin-bottom: 12px;
}

.ypk-stat {
    border: 1px solid var(--ypk-border);
    border-radius: 12px;
    background: var(--ypk-bg);
    padding: 12px;
}

.ypk-stat-label {
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .04em;
    color: var(--ypk-text-500);
    margin-bottom: 4px;
}

.ypk-stat-value {
    font-size: 26px;
    font-weight: 800;
    color: #0f766e;
    line-height: 1;
}

.ypk-toolbar {
    border: 1px solid var(--ypk-border);
    border-radius: 12px;
    background: var(--ypk-bg);
    padding: 12px;
    margin-bottom: 12px;
    display: flex;
    flex-wrap: wrap;
    align-items: flex-end;
    justify-content: space-between;
    gap: 10px;
}

.ypk-filter {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    align-items: flex-end;
}

.ypk-field {
    min-width: 160px;
}

.ypk-label {
    display: block;
    margin-bottom: 5px;
    font-size: 12px;
    font-weight: 700;
    color: var(--ypk-text-700);
}

.ypk-input,
.ypk-select {
    width: 100%;
    border: 1px solid var(--ypk-border);
    border-radius: 8px;
    background: #fff;
    color: var(--ypk-text-900);
    font-size: 12.5px;
    font-family: inherit;
    height: 36px;
    padding: 0 10px;
}

.ypk-input:focus,
.ypk-select:focus {
    outline: none;
    border-color: #14b8a6;
    box-shadow: 0 0 0 3px rgba(20, 184, 166, .14);
}

.ypk-import-wrap {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
}

.ypk-file-btn {
    cursor: pointer;
}

.ypk-table-wrap {
    border: 1px solid var(--ypk-border);
    border-radius: 12px;
    overflow: auto;
}

.ypk-table {
    width: 100%;
    border-collapse: collapse;
    min-width: 980px;
}

.ypk-table th {
    background: #f8fbff;
    color: var(--ypk-text-500);
    text-transform: uppercase;
    letter-spacing: .04em;
    font-size: 10.5px;
    font-weight: 800;
    padding: 10px 12px;
    border-bottom: 1px solid var(--ypk-border);
    white-space: nowrap;
}

.ypk-table td {
    padding: 11px 12px;
    border-bottom: 1px solid #eef3fb;
    font-size: 12.5px;
    color: var(--ypk-text-700);
    vertical-align: top;
}

.ypk-table tr:last-child td {
    border-bottom: none;
}

.ypk-table tr:hover td {
    background: #f8fbff;
}

.ypk-name {
    font-weight: 700;
    color: var(--ypk-text-900);
}

.ypk-badge {
    display: inline-flex;
    align-items: center;
    border-radius: 999px;
    padding: 3px 8px;
    font-size: 10px;
    font-weight: 800;
    letter-spacing: .02em;
}

.ypk-badge.valid {
    background: #dcfce7;
    color: #166534;
}

.ypk-badge.invalid {
    background: #fee2e2;
    color: #991b1b;
}

.ypk-error-detail {
    margin-top: 3px;
    font-size: 11px;
    color: var(--ypk-text-500);
}

.ypk-empty {
    text-align: center;
    color: var(--ypk-text-500);
    padding: 22px 12px;
}

@media (max-width: 900px) {
    .ypk-stats {
        grid-template-columns: 1fr;
    }

    .ypk-field {
        min-width: 100%;
    }
}
</style>

<div class="ypk-page">
    <div class="ypk-head">
        <div>
            <div class="ypk-head-title">Recipient Karyawan YPIK</div>
            <div class="ypk-head-sub">Fitur baru terpisah dari data koperasi. Mendukung import semua sheet dari file Excel karyawan YPIK.</div>
        </div>
        <div class="ypk-head-actions">
            <a href="{{ route('admin.blast.recipients.index') }}" class="ypk-btn ghost">
                <i class="fas fa-user-graduate"></i> Data Siswa
            </a>
            <a href="{{ route('admin.blast.recipients.employees.index') }}" class="ypk-btn ghost">
                <i class="fas fa-building"></i> Data Koperasi
            </a>
        </div>
    </div>

    <div class="ypk-panel">
        <div class="ypk-panel-body">
            @if(session('success'))
                <div class="ypk-alert success">{{ session('success') }}</div>
            @endif

            @if(session('error'))
                <div class="ypk-alert error">{{ session('error') }}</div>
            @endif

            @if($errors->any())
                <div class="ypk-alert error">{{ $errors->first() }}</div>
            @endif

            <div class="ypk-stats">
                <div class="ypk-stat">
                    <div class="ypk-stat-label">Total Karyawan</div>
                    <div class="ypk-stat-value">{{ $totalEmployees ?? $employees->total() }}</div>
                </div>
                <div class="ypk-stat">
                    <div class="ypk-stat-label">Data Valid</div>
                    <div class="ypk-stat-value">{{ $validCount ?? 0 }}</div>
                </div>
                <div class="ypk-stat">
                    <div class="ypk-stat-label">Kontak Belum Lengkap</div>
                    <div class="ypk-stat-value">{{ $incompleteCount ?? 0 }}</div>
                </div>
            </div>

            <div class="ypk-toolbar">
                <form method="GET" action="{{ route('admin.blast.recipients.employees-ypik.index') }}" class="ypk-filter">
                    <div class="ypk-field">
                        <label class="ypk-label">Cari</label>
                        <input type="text" name="q" value="{{ $search ?? '' }}" class="ypk-input" placeholder="Nama / WA / Email">
                    </div>
                    <div class="ypk-field">
                        <label class="ypk-label">Instansi</label>
                        <select name="instansi" class="ypk-select">
                            <option value="">Semua Instansi</option>
                            @foreach(($instansiOptions ?? collect()) as $instansiOption)
                                <option value="{{ $instansiOption }}" @selected(($selectedInstansi ?? '') === $instansiOption)>{{ $instansiOption }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="ypk-field">
                        <label class="ypk-label">Status</label>
                        <select name="status" class="ypk-select">
                            <option value="all" @selected(($selectedStatus ?? 'all') === 'all')>Semua</option>
                            <option value="valid" @selected(($selectedStatus ?? 'all') === 'valid')>Valid</option>
                            <option value="invalid" @selected(($selectedStatus ?? 'all') === 'invalid')>Invalid</option>
                        </select>
                    </div>
                    <div style="min-width:120px;">
                        <label class="ypk-label">Per Halaman</label>
                        <select name="per_page" class="ypk-select">
                            @foreach(($allowedPerPage ?? [20, 50, 100, 200]) as $size)
                                <option value="{{ $size }}" @selected((int) ($perPage ?? 50) === (int) $size)>{{ $size }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="ypk-btn" style="background:linear-gradient(135deg,#0d9488,#0f766e);color:#fff;">
                        <i class="fas fa-filter"></i> Filter
                    </button>
                    <a href="{{ route('admin.blast.recipients.employees-ypik.index') }}" class="ypk-btn" style="background:#fff;border-color:var(--ypk-border);color:var(--ypk-text-700);">
                        Reset
                    </a>
                </form>

                <div class="ypk-import-wrap">
                    <a href="{{ route('admin.blast.recipients.employees-ypik.create') }}" class="ypk-btn" style="background:#ccfbf1;border-color:#99f6e4;color:#0f766e;">
                        <i class="fas fa-plus"></i> Input Manual
                    </a>
                    <form action="{{ route('admin.blast.recipients.employees-ypik.import') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <label class="ypk-btn ypk-file-btn" style="background:#fff;border-color:#99f6e4;color:#0f766e;">
                            <i class="fas fa-file-import"></i> Import Excel Karyawan YPIK
                            <input
                                type="file"
                                name="file"
                                accept=".xlsx,.xls,.csv"
                                style="display:none;"
                                onchange="if(this.files.length){ this.form.submit(); }"
                                required
                            >
                        </label>
                    </form>
                </div>
            </div>

            <div class="ypk-table-wrap">
                <table class="ypk-table">
                    <thead>
                        <tr>
                            <th style="width:56px;">No</th>
                            <th>Nama Karyawan</th>
                            <th>Instansi</th>
                            <th>Nama Wali</th>
                            <th>WhatsApp</th>
                            <th>Email</th>
                            <th>Catatan</th>
                            <th style="width:150px;">Status</th>
                            <th style="width:140px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($employees as $employee)
                            <tr>
                                <td>{{ ($employees->currentPage() - 1) * $employees->perPage() + $loop->iteration }}</td>
                                <td><div class="ypk-name">{{ $employee->nama_karyawan }}</div></td>
                                <td>{{ $employee->instansi ?? '-' }}</td>
                                <td>{{ $employee->nama_wali ?? '-' }}</td>
                                <td>{{ $employee->wa_karyawan ?? '-' }}</td>
                                <td>{{ $employee->email_karyawan ?? '-' }}</td>
                                <td>{{ $employee->catatan ?? '-' }}</td>
                                <td>
                                    @if($employee->is_valid)
                                        <span class="ypk-badge valid">VALID</span>
                                    @else
                                        <span class="ypk-badge invalid">INVALID</span>
                                        @if($employee->validation_error)
                                            <div class="ypk-error-detail">{{ $employee->validation_error }}</div>
                                        @endif
                                    @endif
                                </td>
                                <td>
                                    <div style="display:flex;gap:6px;">
                                        <a href="{{ route('admin.blast.recipients.employees-ypik.edit', $employee->id) }}" class="ypk-btn" style="padding:6px 9px;background:#f0fdfa;border-color:#99f6e4;color:#0f766e;">
                                            <i class="fas fa-pen"></i>
                                        </a>
                                        <form method="POST" action="{{ route('admin.blast.recipients.employees-ypik.destroy', $employee->id) }}" onsubmit="return confirm('Hapus data karyawan YPIK ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button class="ypk-btn" type="submit" style="padding:6px 9px;background:#fff1f2;border-color:#fecaca;color:#b91c1c;">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="ypk-empty">
                                    Belum ada data karyawan YPIK. Silakan import file <b>DATAKARYAWANYPIK.xlsx</b>.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $employees->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
