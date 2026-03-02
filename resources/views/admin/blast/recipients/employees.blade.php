@extends('layouts.app')

@section('title', 'Recipient Data Koperasi Tirta Jatik Utama')

@section('content')
<div class="card card-primary card-outline">
    <div class="card-header d-flex flex-wrap justify-content-between align-items-center" style="gap:8px;">
        <div>
            <h3 class="card-title mb-1" style="float:none;">Recipient Data Koperasi Tirta Jatik Utama</h3>
            <small class="text-muted">Data sumber: file recipent data koperasi tirta jatik utama</small>
        </div>
        <div class="d-flex flex-wrap" style="gap:8px;">
            <a href="{{ route('admin.blast.recipients.index') }}" class="btn btn-sm btn-outline-primary">
                <i class="fas fa-user-graduate mr-1"></i> Data Siswa
            </a>
        </div>
    </div>

    <div class="card-body">
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger">{{ $errors->first() }}</div>
        @endif

        <div class="row mb-3">
            <div class="col-lg-4 col-md-6 mb-2">
                <div class="small-box bg-info">
                    <div class="inner">
                        <h3>{{ $totalEmployees ?? $employees->total() }}</h3>
                        <p>Total Karyawan</p>
                    </div>
                    <div class="icon"><i class="fas fa-users"></i></div>
                </div>
            </div>
            <div class="col-lg-4 col-md-6 mb-2">
                <div class="small-box bg-success">
                    <div class="inner">
                        <h3>{{ $validCount ?? 0 }}</h3>
                        <p>Data Valid</p>
                    </div>
                    <div class="icon"><i class="fas fa-check-circle"></i></div>
                </div>
            </div>
            <div class="col-lg-4 col-md-6 mb-2">
                <div class="small-box bg-warning">
                    <div class="inner">
                        <h3>{{ $incompleteCount ?? 0 }}</h3>
                        <p>Kontak Belum Lengkap</p>
                    </div>
                    <div class="icon"><i class="fas fa-exclamation-triangle"></i></div>
                </div>
            </div>
        </div>

        <div class="d-flex flex-wrap align-items-end justify-content-between mb-3" style="gap:10px;">
            <form method="GET" action="{{ route('admin.blast.recipients.employees.index') }}" class="d-flex flex-wrap align-items-end" style="gap:8px;">
                <div>
                    <label class="mb-1">Cari</label>
                    <input type="text" name="q" value="{{ $search ?? '' }}" class="form-control form-control-sm" placeholder="Nama/WA/Email">
                </div>
                <div>
                    <label class="mb-1">Instansi</label>
                    <select name="instansi" class="form-control form-control-sm">
                        <option value="">Semua Instansi</option>
                        @foreach(($instansiOptions ?? collect()) as $instansiOption)
                            <option value="{{ $instansiOption }}" @selected(($selectedInstansi ?? '') === $instansiOption)>{{ $instansiOption }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="mb-1">Per Halaman</label>
                    <select name="per_page" class="form-control form-control-sm">
                        @foreach(($allowedPerPage ?? [20, 50, 100, 200]) as $size)
                            <option value="{{ $size }}" @selected((int) ($perPage ?? 50) === (int) $size)>{{ $size }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="btn btn-sm btn-primary">
                    <i class="fas fa-filter mr-1"></i> Filter
                </button>
                <a href="{{ route('admin.blast.recipients.employees.index') }}" class="btn btn-sm btn-secondary">Reset</a>
            </form>

            <form action="{{ route('admin.blast.recipients.import') }}" method="POST" enctype="multipart/form-data" class="d-inline">
                @csrf
                <input type="hidden" name="import_type" value="karyawan">
                <label class="btn btn-sm btn-outline-success mb-0" style="cursor:pointer;">
                    <i class="fas fa-file-import mr-1"></i> Import Excel Karyawan
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

        <div class="table-responsive">
            <table class="table table-bordered table-striped table-sm">
                <thead>
                    <tr>
                        <th style="width:40px;">No</th>
                        <th>Nama Karyawan</th>
                        <th>Instansi</th>
                        <th>Nama Wali</th>
                        <th>WhatsApp</th>
                        <th>Email</th>
                        <th>Catatan</th>
                        <th>Status</th>
                        <th style="width:90px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($employees as $employee)
                        <tr>
                            <td>{{ ($employees->currentPage() - 1) * $employees->perPage() + $loop->iteration }}</td>
                            <td>{{ $employee->nama_karyawan }}</td>
                            <td>{{ $employee->instansi ?? '-' }}</td>
                            <td>{{ $employee->nama_wali ?? '-' }}</td>
                            <td>{{ $employee->wa_karyawan ?? '-' }}</td>
                            <td>{{ $employee->email_karyawan ?? '-' }}</td>
                            <td>{{ $employee->catatan ?? '-' }}</td>
                            <td>
                                @if($employee->is_valid)
                                    <span class="badge badge-success">VALID</span>
                                @else
                                    <span class="badge badge-danger">INVALID</span>
                                    @if($employee->validation_error)
                                        <div class="text-muted" style="font-size:11px;">{{ $employee->validation_error }}</div>
                                    @endif
                                @endif
                            </td>
                            <td>
                                <form method="POST" action="{{ route('admin.blast.recipients.employees.destroy', $employee->id) }}" onsubmit="return confirm('Hapus data karyawan ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-danger" type="submit">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted py-4">
                                Belum ada data karyawan. Silakan import file <b>recipent data koperasi tirta jatik utama</b>.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-2">
            {{ $employees->links() }}
        </div>
    </div>
</div>
@endsection

