@extends('layouts.app')

@section('content')
<div class="container-fluid">

    <h4 class="mb-3">Data Penerima Blasting</h4>

    <div class="d-flex gap-2 mb-3">
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

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Nama Siswa</th>
                <th>Kelas</th>
                <th>Nama Wali</th>
                <th>Email</th>
                <th>WhatsApp</th>
                <th>Catatan</th>
                <th>Status</th>
                <th width="80">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($recipients as $r)
                <tr>
                    <td>{{ $r->nama_siswa }}</td>
                    <td>{{ $r->kelas }}</td>
                    <td>{{ $r->nama_wali }}</td>
                    <td>{{ $r->email_wali }}</td>
                    <td>{{ $r->wa_wali }}</td>
                    <td>{{ $r->catatan }}</td>
                    <td>
                        @if($r->is_valid)
                            <span class="badge bg-success">VALID</span>
                        @else
                            <span class="badge bg-danger">INVALID</span>
                        @endif
                    </td>
                    <td>
                        <form method="POST"
                              action="{{ route('admin.blast.recipients.destroy', $r->id) }}"
                              onsubmit="return confirm('Hapus data ini?')">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-sm btn-danger">✕</button>
                        </form>
                    </td>
                    <td>
    <a href="{{ route('admin.blast.recipients.edit', $r->id) }}"
       class="btn btn-sm btn-warning">Edit</a>

    <form method="POST"
          action="{{ route('admin.blast.recipients.destroy', $r->id) }}"
          class="d-inline"
          onsubmit="return confirm('Hapus data ini?')">
        @csrf
        @method('DELETE')
        <button class="btn btn-sm btn-danger">✕</button>
    </form>
</td>

                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center">Belum ada data</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{ $recipients->links() }}
</div>
@endsection
