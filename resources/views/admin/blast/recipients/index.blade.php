@extends('layouts.app')

@section('section_name', 'Data Penerima Blasting')

@section('content')

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

<div class="mb-3 d-flex gap-2">
    <a href="{{ route('admin.blast.recipients.create') }}" class="btn btn-primary">
        + Tambah Penerima
    </a>

    <form action="{{ route('admin.blast.recipients.import') }}"
          method="POST"
          enctype="multipart/form-data"
          class="d-flex gap-2">
        @csrf
        <input type="file" name="file" required class="form-control">
        <button class="btn btn-success">Import Excel</button>
    </form>
</div>

<div class="card">
    <div class="card-body table-responsive">
        <table class="table table-bordered table-hover">
            <thead>
                <tr>
                    <th>Nama Siswa</th>
                    <th>Kelas</th>
                    <th>Nama Wali</th>
                    <th>Email</th>
                    <th>WhatsApp</th>
                    <th>Status</th>
                    <th width="150">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recipients as $row)
                    <tr>
                        <td>{{ $row->nama_siswa }}</td>
                        <td>{{ $row->kelas }}</td>
                        <td>{{ $row->nama_wali }}</td>
                        <td>{{ $row->email_wali ?? '-' }}</td>
                        <td>{{ $row->wa_wali ?? '-' }}</td>
                        <td>
                            @if($row->is_valid)
                                <span class="badge bg-success">VALID</span>
                            @else
                                <span class="badge bg-danger">INVALID</span>
                                <div class="text-danger small">
                                    {{ $row->validation_error }}
                                </div>
                            @endif
                        </td>
                        <td class="d-flex gap-1">
                            <a href="{{ route('admin.blast.recipients.edit', $row->id) }}"
                               class="btn btn-sm btn-warning">
                                Edit
                            </a>

                            <form action="{{ route('admin.blast.recipients.destroy', $row->id) }}"
                                  method="POST"
                                  onsubmit="return confirm('Yakin hapus data ini?')">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-danger">
                                    Hapus
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center">
                            Belum ada data penerima
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        {{ $recipients->links() }}
    </div>
</div>

@endsection
