@extends('layouts.app')

@section('title', 'Recipient Karyawan Koperasi Tirta Jatik Utama')

@section('content')
<style>
@import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');

:root {
    --emp-blue-900: #112a62;
    --emp-blue-800: #1a56db;
    --emp-blue-700: #2563eb;
    --emp-blue-100: #dbeafe;
    --emp-blue-50: #eff6ff;
    --emp-text-900: #0f172a;
    --emp-text-700: #334155;
    --emp-text-500: #64748b;
    --emp-border: #dbe4f0;
    --emp-bg: #f2f7ff;
}

.emp-page {
    font-family: 'Plus Jakarta Sans', sans-serif;
    color: var(--emp-text-900);
    padding: 4px 2px 16px;
}

.emp-head {
    border-radius: 14px;
    padding: 20px 22px;
    margin-bottom: 14px;
    background: linear-gradient(135deg, var(--emp-blue-900) 0%, var(--emp-blue-800) 60%, var(--emp-blue-700) 100%);
    box-shadow: 0 12px 24px rgba(26,86,219,.22);
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    flex-wrap: wrap;
    gap: 12px;
}

.emp-head-title {
    font-size: 20px;
    font-weight: 800;
    color: #fff;
    margin-bottom: 4px;
}

.emp-head-sub {
    font-size: 12px;
    color: rgba(255,255,255,.86);
}

.emp-head-actions {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
}

.emp-btn {
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

.emp-btn:hover {
    transform: translateY(-1px);
}

.emp-btn.ghost {
    color: #fff;
    border-color: rgba(255,255,255,.38);
    background: rgba(255,255,255,.1);
}

.emp-panel {
    border: 1px solid var(--emp-border);
    border-radius: 14px;
    background: #fff;
    box-shadow: 0 8px 18px rgba(15,23,42,.06);
    overflow: hidden;
}

.emp-panel-body {
    padding: 16px;
}

.emp-alert {
    border-radius: 10px;
    padding: 10px 12px;
    font-size: 12.5px;
    font-weight: 600;
    margin-bottom: 12px;
}

.emp-alert.success {
    border: 1px solid #86efac;
    background: #f0fdf4;
    color: #166534;
}

.emp-alert.error {
    border: 1px solid #fecaca;
    background: #fef2f2;
    color: #991b1b;
}

.emp-stats {
    display: grid;
    grid-template-columns: repeat(3, minmax(0,1fr));
    gap: 10px;
    margin-bottom: 12px;
}

.emp-stat {
    border: 1px solid var(--emp-border);
    border-radius: 12px;
    background: var(--emp-bg);
    padding: 12px;
}

.emp-stat-label {
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .04em;
    color: var(--emp-text-500);
    margin-bottom: 4px;
}

.emp-stat-value {
    font-size: 26px;
    font-weight: 800;
    color: var(--emp-blue-800);
    line-height: 1;
}

.emp-toolbar {
    border: 1px solid var(--emp-border);
    border-radius: 12px;
    background: var(--emp-bg);
    padding: 12px;
    margin-bottom: 12px;
    display: flex;
    flex-wrap: wrap;
    align-items: flex-end;
    justify-content: space-between;
    gap: 10px;
}

.emp-filter {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    align-items: flex-end;
}

.emp-field {
    min-width: 170px;
}

.emp-label {
    display: block;
    margin-bottom: 5px;
    font-size: 12px;
    font-weight: 700;
    color: var(--emp-text-700);
}

.emp-input,
.emp-select {
    width: 100%;
    border: 1px solid var(--emp-border);
    border-radius: 8px;
    background: #fff;
    color: var(--emp-text-900);
    font-size: 12.5px;
    font-family: inherit;
    height: 36px;
    padding: 0 10px;
}

.emp-input:focus,
.emp-select:focus {
    outline: none;
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(37,99,235,.14);
}

.emp-import-wrap {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
}

.emp-file-btn {
    cursor: pointer;
}

.emp-table-wrap {
    border: 1px solid var(--emp-border);
    border-radius: 12px;
    overflow: auto;
}

.emp-table {
    width: 100%;
    border-collapse: collapse;
    min-width: 980px;
}

.emp-table th {
    background: #f8fbff;
    color: var(--emp-text-500);
    text-transform: uppercase;
    letter-spacing: .04em;
    font-size: 10.5px;
    font-weight: 800;
    padding: 10px 12px;
    border-bottom: 1px solid var(--emp-border);
    white-space: nowrap;
}

.emp-table td {
    padding: 11px 12px;
    border-bottom: 1px solid #eef3fb;
    font-size: 12.5px;
    color: var(--emp-text-700);
    vertical-align: top;
}

.emp-table tr:last-child td {
    border-bottom: none;
}

.emp-table tr:hover td {
    background: #f8fbff;
}

.emp-checkbox {
    width: 16px;
    height: 16px;
    accent-color: var(--emp-blue-800);
    cursor: pointer;
}

.emp-btn.outline-danger {
    background: #ffffff;
    border-color: #fecaca;
    color: #b91c1c;
}

.emp-btn.outline-danger:hover {
    background: #fee2e2;
}

.emp-btn.danger {
    background: #fee2e2;
    border-color: #fecaca;
    color: #b91c1c;
}

.emp-btn.danger:hover {
    background: #b91c1c;
    color: #ffffff;
    border-color: #b91c1c;
}

.emp-btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
    transform: none;
}

.emp-name {
    font-weight: 700;
    color: var(--emp-text-900);
}

.emp-badge {
    display: inline-flex;
    align-items: center;
    border-radius: 999px;
    padding: 3px 8px;
    font-size: 10px;
    font-weight: 800;
    letter-spacing: .02em;
}

.emp-badge.valid {
    background: #dcfce7;
    color: #166534;
}

.emp-badge.invalid {
    background: #fee2e2;
    color: #991b1b;
}

.emp-error-detail {
    margin-top: 3px;
    font-size: 11px;
    color: var(--emp-text-500);
}

.emp-empty {
    text-align: center;
    color: var(--emp-text-500);
    padding: 22px 12px;
}

@media (max-width: 900px) {
    .emp-stats {
        grid-template-columns: 1fr;
    }

    .emp-field {
        min-width: 100%;
    }
}
body.dark-mode .emp-page {
    color: var(--app-text) !important;
}

body.dark-mode .emp-panel,
body.dark-mode .emp-stat,
body.dark-mode .emp-toolbar,
body.dark-mode .emp-table-wrap {
    background: var(--app-surface) !important;
    border-color: var(--app-border) !important;
    box-shadow: var(--app-shadow) !important;
}

body.dark-mode .emp-toolbar,
body.dark-mode .emp-stat {
    background: var(--app-surface-soft) !important;
}

body.dark-mode .emp-label,
body.dark-mode .emp-stat-label,
body.dark-mode .emp-error-detail,
body.dark-mode .emp-empty {
    color: var(--app-text-muted) !important;
}

body.dark-mode .emp-name,
body.dark-mode .emp-stat-value {
    color: var(--app-text) !important;
}

body.dark-mode .emp-input,
body.dark-mode .emp-select {
    background: var(--app-surface-soft) !important;
    border-color: var(--app-border) !important;
    color: var(--app-text) !important;
}

body.dark-mode .emp-input:focus,
body.dark-mode .emp-select:focus {
    background: var(--app-surface) !important;
    border-color: rgba(96, 165, 250, 0.36) !important;
    box-shadow: 0 0 0 3px rgba(96, 165, 250, 0.14) !important;
}

body.dark-mode .emp-select option {
    background: var(--app-surface) !important;
    color: var(--app-text) !important;
}

body.dark-mode .emp-table th {
    background: var(--app-surface-soft) !important;
    border-color: var(--app-border) !important;
    color: var(--app-text-muted) !important;
}

body.dark-mode .emp-table td {
    background: transparent !important;
    border-color: var(--app-border) !important;
    color: var(--app-text-soft) !important;
}

body.dark-mode .emp-table tr:hover td {
    background: var(--app-row-hover) !important;
}

body.dark-mode .emp-table tbody tr.is-selected td {
    background: var(--app-row-selected) !important;
}

body.dark-mode .emp-filter > button.emp-btn {
    background: linear-gradient(135deg, #1d4ed8, #3b82f6) !important;
    border-color: transparent !important;
    color: #ffffff !important;
}

body.dark-mode .emp-filter > a.emp-btn,
body.dark-mode .emp-import-wrap > a.emp-btn,
body.dark-mode .emp-import-wrap > form .emp-file-btn {
    background: var(--app-surface-soft) !important;
    border-color: var(--app-border) !important;
    color: #93c5fd !important;
}

body.dark-mode .emp-btn.outline-danger {
    background: rgba(239, 68, 68, 0.1) !important;
    border-color: rgba(239, 68, 68, 0.2) !important;
    color: #fca5a5 !important;
}

body.dark-mode .emp-btn.danger {
    background: rgba(239, 68, 68, 0.14) !important;
    border-color: rgba(239, 68, 68, 0.22) !important;
    color: #fecaca !important;
}

body.dark-mode .emp-table td a.emp-btn {
    background: rgba(96, 165, 250, 0.12) !important;
    border-color: rgba(96, 165, 250, 0.22) !important;
    color: #bfdbfe !important;
}

body.dark-mode .emp-table td form button.emp-btn {
    background: rgba(239, 68, 68, 0.12) !important;
    border-color: rgba(239, 68, 68, 0.22) !important;
    color: #fecaca !important;
}
</style>

<div class="emp-page">
    <div class="emp-head">
        <div>
            <div class="emp-head-title">Recipient Karyawan Koperasi Tirta Jatik Utama</div>
            <div class="emp-head-sub">Fitur lama khusus koperasi. Sumber data: file <b>recipent data koperasi tirta jatik utama</b>.</div>
        </div>
        <div class="emp-head-actions">
            <a href="{{ route('admin.blast.recipients.index') }}" class="emp-btn ghost">
                <i class="fas fa-user-graduate"></i> Data Siswa
            </a>
            <a href="{{ route('admin.blast.recipients.employees-ypik.index') }}" class="emp-btn ghost">
                <i class="fas fa-id-card"></i> Data Karyawan YPIK
            </a>
            <a href="{{ route('admin.blast.recipients.employees-ypik-pamjaya.index') }}" class="emp-btn ghost">
                <i class="fas fa-address-book"></i> YPIK Pam Jaya
            </a>
        </div>
    </div>

    <div class="emp-panel">
        <div class="emp-panel-body">
            @if(session('success'))
                <div class="emp-alert success">{{ session('success') }}</div>
            @endif

            @if(session('error'))
                <div class="emp-alert error">{{ session('error') }}</div>
            @endif

            @if($errors->any())
                <div class="emp-alert error">{{ $errors->first() }}</div>
            @endif

            <div class="emp-stats">
                <div class="emp-stat">
                    <div class="emp-stat-label">Total Karyawan</div>
                    <div class="emp-stat-value">{{ $totalEmployees ?? $employees->total() }}</div>
                </div>
                <div class="emp-stat">
                    <div class="emp-stat-label">Data Valid</div>
                    <div class="emp-stat-value">{{ $validCount ?? 0 }}</div>
                </div>
                <div class="emp-stat">
                    <div class="emp-stat-label">Kontak Belum Lengkap</div>
                    <div class="emp-stat-value">{{ $incompleteCount ?? 0 }}</div>
                </div>
            </div>

            <div class="emp-toolbar">
                <form method="GET" action="{{ route('admin.blast.recipients.employees.index') }}" class="emp-filter">
                    <div class="emp-field">
                        <label class="emp-label">Cari</label>
                        <input type="text" name="q" value="{{ $search ?? '' }}" class="emp-input" placeholder="Nama / WA / Email">
                    </div>
                    <div class="emp-field">
                        <label class="emp-label">Instansi</label>
                        <select name="instansi" class="emp-select">
                            <option value="">Semua Instansi</option>
                            @foreach(($instansiOptions ?? collect()) as $instansiOption)
                                <option value="{{ $instansiOption }}" @selected(($selectedInstansi ?? '') === $instansiOption)>{{ $instansiOption }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div style="min-width:120px;">
                        <label class="emp-label">Per Halaman</label>
                        <select name="per_page" class="emp-select">
                            @foreach(($allowedPerPage ?? [20, 50, 100, 200]) as $size)
                                <option value="{{ $size }}" @selected((int) ($perPage ?? 50) === (int) $size)>{{ $size }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="emp-btn" style="background:linear-gradient(135deg,#1d4ed8,#2563eb);color:#fff;">
                        <i class="fas fa-filter"></i> Filter
                    </button>
                    <a href="{{ route('admin.blast.recipients.employees.index') }}" class="emp-btn" style="background:#fff;border-color:var(--emp-border);color:var(--emp-text-700);">
                        Reset
                    </a>
                </form>

                <form id="bulk-delete-employees-form" method="POST" action="{{ route('admin.blast.recipients.employees.bulk-delete') }}">
                    @csrf
                    @method('DELETE')
                </form>

                <div class="emp-import-wrap">
                    <a href="{{ route('admin.blast.recipients.employees.create') }}" class="emp-btn" style="background:#dbeafe;border-color:#93c5fd;color:#1d4ed8;">
                        <i class="fas fa-plus"></i> Input Manual
                    </a>
                    <form action="{{ route('admin.blast.recipients.import') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="import_type" value="karyawan">
                        <label class="emp-btn emp-file-btn" style="background:#fff;border-color:#93c5fd;color:#1d4ed8;">
                            <i class="fas fa-file-import"></i> Import Excel Karyawan
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
                    <button type="submit" class="emp-btn outline-danger" id="bulkDeleteEmployeesBtn" form="bulk-delete-employees-form" disabled>
                        <i class="fas fa-trash-alt"></i> Delete Selected
                    </button>
                    <form method="POST" action="{{ route('admin.blast.recipients.employees.destroy-all') }}" onsubmit="return confirm('Hapus SEMUA recipient karyawan koperasi? Tindakan ini tidak bisa dibatalkan.')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="emp-btn danger">
                            <i class="fas fa-trash"></i> Delete All
                        </button>
                    </form>
                </div>
            </div>

            <div class="emp-table-wrap">
                <table class="emp-table">
                    <thead>
                        <tr>
                            <th style="width:44px;">
                                <input type="checkbox" class="emp-checkbox" id="selectAllEmployees">
                            </th>
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
                                <td>
                                    <input
                                        type="checkbox"
                                        class="emp-checkbox employee-checkbox"
                                        name="selected_ids[]"
                                        value="{{ $employee->id }}"
                                        form="bulk-delete-employees-form"
                                    >
                                </td>
                                <td>{{ ($employees->currentPage() - 1) * $employees->perPage() + $loop->iteration }}</td>
                                <td><div class="emp-name">{{ $employee->nama_karyawan }}</div></td>
                                <td>{{ $employee->instansi ?? '-' }}</td>
                                <td>{{ $employee->nama_wali ?? '-' }}</td>
                                <td>{{ $employee->wa_karyawan ?? '-' }}</td>
                                <td>{{ $employee->email_karyawan ?? '-' }}</td>
                                <td>{{ $employee->catatan ?? '-' }}</td>
                                <td>
                                    @if($employee->is_valid)
                                        <span class="emp-badge valid">VALID</span>
                                    @else
                                        <span class="emp-badge invalid">INVALID</span>
                                        @if($employee->validation_error)
                                            <div class="emp-error-detail">{{ $employee->validation_error }}</div>
                                        @endif
                                    @endif
                                </td>
                                <td>
                                    <div style="display:flex;gap:6px;">
                                        <a href="{{ route('admin.blast.recipients.employees.edit', $employee->id) }}" class="emp-btn" style="padding:6px 9px;background:#eff6ff;border-color:#bfdbfe;color:#1d4ed8;">
                                            <i class="fas fa-pen"></i>
                                        </a>
                                        <form method="POST" action="{{ route('admin.blast.recipients.employees.destroy', $employee->id) }}" onsubmit="return confirm('Hapus data karyawan ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button class="emp-btn" type="submit" style="padding:6px 9px;background:#fff1f2;border-color:#fecaca;color:#b91c1c;">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="emp-empty">
                                    Belum ada data karyawan. Silakan import file <b>recipent data koperasi tirta jatik utama</b>.
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

<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('bulk-delete-employees-form');
    const selectAll = document.getElementById('selectAllEmployees');
    const checkboxes = Array.from(document.querySelectorAll('.employee-checkbox'));
    const deleteBtn = document.getElementById('bulkDeleteEmployeesBtn');

    if (!form || !deleteBtn || checkboxes.length === 0) {
        if (deleteBtn) {
            deleteBtn.disabled = true;
        }
        return;
    }

    function updateState() {
        const selected = checkboxes.filter(cb => cb.checked);
        const selectedCount = selected.length;
        const totalCount = checkboxes.length;

        deleteBtn.disabled = selectedCount === 0;
        deleteBtn.textContent = selectedCount > 0
            ? `Delete Selected (${selectedCount})`
            : 'Delete Selected';

        if (selectAll) {
            selectAll.checked = selectedCount > 0 && selectedCount === totalCount;
            selectAll.indeterminate = selectedCount > 0 && selectedCount < totalCount;
        }
    }

    if (selectAll) {
        selectAll.addEventListener('change', () => {
            const checked = selectAll.checked;
            checkboxes.forEach(cb => { cb.checked = checked; });
            updateState();
        });
    }

    checkboxes.forEach(cb => cb.addEventListener('change', updateState));

    form.addEventListener('submit', (event) => {
        const selected = checkboxes.filter(cb => cb.checked);
        if (selected.length === 0) {
            event.preventDefault();
            alert('Pilih minimal satu recipient untuk dihapus.');
            return;
        }

        const confirmText = `Hapus ${selected.length} recipient karyawan koperasi terpilih?`;
        if (!confirm(confirmText)) {
            event.preventDefault();
        }
    });

    updateState();
});
</script>
@endsection
