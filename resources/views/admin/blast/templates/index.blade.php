@extends('layouts.app')

@section('title', 'Template Blast')

@section('content')
<div class="card shadow-sm border-0">
    <div class="card-header bg-white d-flex flex-wrap justify-content-between align-items-center" style="gap:10px;">
        <div>
            <h3 class="card-title mb-1" style="float:none;">Template Blast</h3>
            <div class="text-muted" style="font-size:12px;">
                Kelola template database untuk WhatsApp dan Email blast.
            </div>
        </div>
        <div class="d-flex flex-wrap" style="gap:8px;">
            <a href="{{ route('admin.blast.whatsapp') }}" class="btn btn-sm btn-outline-success">
                <i class="fab fa-whatsapp mr-1"></i> WhatsApp Blast
            </a>
            <a href="{{ route('admin.blast.email') }}" class="btn btn-sm btn-outline-primary">
                <i class="fas fa-envelope mr-1"></i> Email Blast
            </a>
            <a href="{{ route('admin.blast.templates.create', ['channel' => $channel ?: 'whatsapp']) }}" class="btn btn-sm btn-primary">
                <i class="fas fa-plus mr-1"></i> Tambah Template
            </a>
        </div>
    </div>

    <div class="card-body">
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger mb-3">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="GET" action="{{ route('admin.blast.templates.index') }}" class="row mb-3 align-items-end">
            <div class="col-md-3 col-sm-6">
                <label for="channelFilter" class="mb-1">Channel</label>
                <select id="channelFilter" name="channel" class="form-control">
                    <option value="">Semua Channel</option>
                    <option value="whatsapp" {{ $channel === 'whatsapp' ? 'selected' : '' }}>WhatsApp</option>
                    <option value="email" {{ $channel === 'email' ? 'selected' : '' }}>Email</option>
                </select>
            </div>
            <div class="col-md-3 col-sm-6 mt-2 mt-sm-0">
                <button type="submit" class="btn btn-outline-primary btn-block">
                    <i class="fas fa-filter mr-1"></i> Filter
                </button>
            </div>
            <div class="col-md-3 col-sm-6 mt-2 mt-md-0">
                <a href="{{ route('admin.blast.templates.index') }}" class="btn btn-outline-secondary btn-block">
                    Reset
                </a>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle">
                <thead class="thead-light">
                    <tr>
                        <th style="width:50px;">No</th>
                        <th style="width:180px;">Nama Template</th>
                        <th style="width:120px;">Channel</th>
                        <th style="width:110px;">Status</th>
                        <th>Isi Template</th>
                        <th style="width:170px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($templates as $template)
                        <tr>
                            <td>{{ ($templates->currentPage() - 1) * $templates->perPage() + $loop->iteration }}</td>
                            <td>{{ $template->name }}</td>
                            <td>
                                @if(strtolower((string) $template->channel) === 'whatsapp')
                                    <span class="badge badge-success">WhatsApp</span>
                                @else
                                    <span class="badge badge-primary">Email</span>
                                @endif
                            </td>
                            <td>
                                @if($template->is_active)
                                    <span class="badge badge-success">Aktif</span>
                                @else
                                    <span class="badge badge-secondary">Nonaktif</span>
                                @endif
                            </td>
                            <td class="text-wrap">
                                {{ \Illuminate\Support\Str::limit($template->content, 180) }}
                            </td>
                            <td>
                                <div class="d-flex" style="gap:6px;">
                                    <a href="{{ route('admin.blast.templates.edit', ['id' => $template->id]) }}" class="btn btn-sm btn-warning">
                                        <i class="fas fa-pen"></i>
                                    </a>
                                    <form method="POST" action="{{ route('admin.blast.templates.destroy', ['id' => $template->id]) }}" onsubmit="return confirm('Hapus template ini?')" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">
                                Belum ada template pada channel ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">
            {{ $templates->links() }}
        </div>
    </div>
</div>
@endsection
