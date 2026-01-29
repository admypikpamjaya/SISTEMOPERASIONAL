@extends('layouts.app')

@section('content')
<div class="container-fluid recipient-wrapper">

    {{-- Page Title --}}
    <div class="page-title">
        <h3>Kelola Data Penerima WhatsApp & Email</h3>
        <p>Manajemen data penerima untuk kebutuhan blasting</p>
    </div>

    {{-- Action Bar --}}
    <div class="action-bar">
        <a href="{{ route('admin.blast.recipients.create') }}" class="btn btn-primary">
            + Tambah Penerima
        </a>

        <form action="{{ route('admin.blast.recipients.import') }}"
              method="POST"
              enctype="multipart/form-data"
              class="import-form">
            @csrf
            <input type="file" name="file" required>
            <button class="btn btn-success">Import Excel</button>
        </form>
    </div>

    {{-- Data Table --}}
    <div class="card data-card">
        <div class="table-responsive">
            <table class="table recipient-table">
                <thead>
                <tr>
                    <th>Nama Siswa</th>
                    <th>Kelas</th>
                    <th>Nama Wali</th>
                    <th>Email</th>
                    <th>WhatsApp</th>
                    <th>Catatan</th>
                    <th>Status</th>
                    <th width="120">Aksi</th>
                </tr>
                </thead>
                <tbody>
                @forelse($recipients as $r)
                    <tr>
                        <td class="fw-semibold">{{ $r->nama_siswa }}</td>
                        <td>{{ $r->kelas }}</td>
                        <td>{{ $r->nama_wali }}</td>
                        <td>{{ $r->email_wali }}</td>
                        <td>{{ $r->wa_wali }}</td>
                        <td>{{ $r->catatan ?? '-' }}</td>
                        <td>
                            @if($r->is_valid)
                                <span class="badge badge-valid">VALID</span>
                            @else
                                <span class="badge badge-invalid">INVALID</span>
                            @endif
                        </td>
                        <td>
                            <div class="aksi">
                                <a href="{{ route('admin.blast.recipients.edit', $r->id) }}"
                                   class="btn btn-sm btn-warning">
                                    Edit
                                </a>

                                <form method="POST"
                                      action="{{ route('admin.blast.recipients.destroy', $r->id) }}"
                                      onsubmit="return confirm('Hapus data ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-danger">✕</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted py-4">
                            Belum ada data penerima
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Pagination --}}
    <div class="mt-3">
        {{ $recipients->links() }}
    </div>

    {{-- Activity Log --}}
    <div class="card activity-card">
        <div class="card-header">
            <strong>Activity Log</strong>
            <small class="text-muted d-block">
                Riwayat aktivitas pengelolaan data penerima
            </small>
        </div>
        <div class="card-body">
            <ul class="activity-list">
                <li>Import data penerima via Excel</li>
                <li>Tambah penerima baru</li>
                <li class="text-muted fst-italic">
                    Log akan tampil otomatis setelah audit trail aktif
                </li>
            </ul>
        </div>
    </div>

</div>

<style>
/* ===== LAYOUT ===== */
.recipient-wrapper {
    padding: 20px;
}

/* ===== TITLE ===== */
.page-title h3 {
    margin-bottom: 4px;
    font-weight: 600;
}
.page-title p {
    color: #6c757d;
    font-size: 14px;
}

/* ===== ACTION BAR ===== */
.action-bar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin: 15px 0 20px;
    flex-wrap: wrap;
    gap: 10px;
}

.import-form {
    display: flex;
    gap: 8px;
    align-items: center;
}

/* ===== TABLE ===== */
.data-card {
    border-radius: 6px;
}

.recipient-table thead th {
    background: #f8f9fa;
    font-size: 13px;
    font-weight: 600;
    color: #495057;
}

.recipient-table td {
    font-size: 14px;
    vertical-align: middle;
}

/* ===== BADGE ===== */
.badge-valid {
    background: #d1e7dd;
    color: #0f5132;
    padding: 5px 10px;
    border-radius: 4px;
    font-size: 12px;
    font-weight: 600;
}

.badge-invalid {
    background: #f8d7da;
    color: #842029;
    padding: 5px 10px;
    border-radius: 4px;
    font-size: 12px;
    font-weight: 600;
}

/* ===== AKSI ===== */
.aksi {
    display: flex;
    gap: 6px;
}

/* ===== ACTIVITY ===== */
.activity-card {
    margin-top: 25px;
}

.activity-list {
    padding-left: 18px;
    margin: 0;
}

.activity-list li {
    font-size: 14px;
    margin-bottom: 6px;
}
</style>
@endsection
