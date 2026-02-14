@extends('layouts.app')

@section('section_name', 'Announcement')

@section('content')
@php
    $isEdit = (bool) $editingAnnouncement;
    $formAction = $isEdit
        ? route('admin.announcements.update', $editingAnnouncement->id)
        : route('admin.announcements.store');
    $defaultChannels = $isEdit ? [] : ['email', 'whatsapp'];
    $selectedChannels = old('channels', $defaultChannels);
@endphp

@if ($errors->any())
    <div class="alert alert-danger">
        <strong>Terjadi kesalahan validasi:</strong>
        <ul class="mb-0 mt-2 pl-3">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="row">
    <div class="col-lg-4">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">{{ $isEdit ? 'Edit Announcement' : 'Buat Announcement' }}</h3>
            </div>

            <form action="{{ $formAction }}" method="POST" enctype="multipart/form-data">
                @csrf
                @if ($isEdit)
                    @method('PUT')
                @endif

                <div class="card-body">
                    <div class="form-group">
                        <label for="title">Judul</label>
                        <input
                            type="text"
                            class="form-control @error('title') is-invalid @enderror"
                            id="title"
                            name="title"
                            value="{{ old('title', $editingAnnouncement->title ?? '') }}"
                            required
                        >
                    </div>

                    <div class="form-group">
                        <label for="message">Isi Pengumuman</label>
                        <textarea
                            class="form-control @error('message') is-invalid @enderror"
                            id="message"
                            name="message"
                            rows="6"
                            required
                        >{{ old('message', $editingAnnouncement->message ?? '') }}</textarea>
                    </div>

                    <div class="form-group">
                        <label for="attachment">Attachment (opsional)</label>
                        <input
                            type="file"
                            class="form-control-file @error('attachment') is-invalid @enderror"
                            id="attachment"
                            name="attachment"
                        >
                        @if ($isEdit && !empty($editingAnnouncement->attachment_path))
                            <small class="form-text text-muted">
                                File saat ini:
                                <a href="{{ asset('storage/' . $editingAnnouncement->attachment_path) }}" target="_blank">
                                    Lihat attachment
                                </a>
                            </small>
                        @endif
                    </div>

                    <div class="form-group mb-0">
                        <label>Kirim ke Blasting</label>
                        <div class="custom-control custom-checkbox">
                            <input
                                type="checkbox"
                                class="custom-control-input"
                                id="channel_email"
                                name="channels[]"
                                value="email"
                                @checked(in_array('email', (array) $selectedChannels, true))
                            >
                            <label class="custom-control-label" for="channel_email">
                                Email ({{ $emailRecipientCount }} recipient valid)
                            </label>
                        </div>
                        <div class="custom-control custom-checkbox">
                            <input
                                type="checkbox"
                                class="custom-control-input"
                                id="channel_whatsapp"
                                name="channels[]"
                                value="whatsapp"
                                @checked(in_array('whatsapp', (array) $selectedChannels, true))
                            >
                            <label class="custom-control-label" for="channel_whatsapp">
                                WhatsApp ({{ $whatsappRecipientCount }} recipient valid)
                            </label>
                        </div>
                        <small class="text-muted d-block mt-2">
                            {{ $isEdit ? 'Kosongkan channel jika hanya ingin update data announcement tanpa kirim ulang.' : 'Pilih channel untuk menghubungkan announcement ke modul blasting.' }}
                        </small>
                    </div>
                </div>

                <div class="card-footer d-flex justify-content-between">
                    <button type="submit" class="btn btn-primary">
                        {{ $isEdit ? 'Update Announcement' : 'Publikasikan Announcement' }}
                    </button>

                    @if ($isEdit)
                        <a href="{{ route('admin.announcements.index') }}" class="btn btn-default">
                            Batal Edit
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Data Announcement Yang Sudah Dibuat</h3>
                <div class="card-tools" style="width: 360px;">
                    <form method="GET" action="{{ route('admin.announcements.index') }}">
                        <div class="input-group input-group-sm">
                            <input
                                type="text"
                                name="search"
                                class="form-control float-right"
                                placeholder="Cari judul, isi, pembuat, target, status..."
                                value="{{ $search ?? '' }}"
                            >
                            <div class="input-group-append">
                                <button type="submit" class="btn btn-primary">
                                    Search
                                </button>
                                @if (!empty($search))
                                    <a href="{{ route('admin.announcements.index') }}" class="btn btn-default">
                                        Reset
                                    </a>
                                @endif
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped table-bordered mb-0">
                        <thead>
                            <tr>
                                <th style="width: 60px;">ID</th>
                                <th>Judul / Pesan</th>
                                <th style="width: 170px;">Dibuat Oleh</th>
                                <th style="width: 180px;">Log Blasting</th>
                                <th style="width: 170px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($announcements as $announcement)
                                <tr>
                                    <td>{{ $announcement->id }}</td>
                                    <td>
                                        <div><strong>{{ $announcement->title }}</strong></div>
                                        <div class="text-muted">
                                            {{ \Illuminate\Support\Str::limit($announcement->message, 120) }}
                                        </div>
                                        <small class="text-secondary">
                                            {{ $announcement->created_at?->format('d/m/Y H:i') ?? '-' }}
                                        </small>
                                    </td>
                                    <td>
                                        <div>{{ $announcement->creator?->name ?? '-' }}</div>
                                        @if (!empty($announcement->attachment_path))
                                            <small>
                                                <a href="{{ asset('storage/' . $announcement->attachment_path) }}" target="_blank">
                                                    Lihat attachment
                                                </a>
                                            </small>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge badge-info">Total: {{ $announcement->logs_total_count }}</span>
                                        <span class="badge badge-success">SENT: {{ $announcement->logs_sent_count }}</span>
                                        <span class="badge badge-danger">FAILED: {{ $announcement->logs_failed_count }}</span>
                                        <span class="badge badge-warning">PENDING: {{ $announcement->logs_pending_count }}</span>
                                    </td>
                                    <td>
                                        <a
                                            href="{{ route('admin.announcements.edit', $announcement->id) }}"
                                            class="btn btn-sm btn-warning mb-1"
                                        >
                                            Edit
                                        </a>

                                        <form
                                            action="{{ route('admin.announcements.destroy', $announcement->id) }}"
                                            method="POST"
                                            onsubmit="return confirm('Hapus announcement ini?')"
                                        >
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger">
                                                Hapus
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted">Belum ada data announcement.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if ($announcements->hasPages())
                <div class="card-footer">
                    {{ $announcements->links() }}
                </div>
            @endif
        </div>

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Log Announcement</h3>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered table-sm mb-0">
                        <thead>
                            <tr>
                                <th style="width: 60px;">ID</th>
                                <th>Announcement</th>
                                <th style="width: 110px;">Channel</th>
                                <th>Target</th>
                                <th style="width: 100px;">Status</th>
                                <th>Response</th>
                                <th style="width: 150px;">Sent At</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($announcementLogs as $log)
                                <tr>
                                    <td>{{ $log->id }}</td>
                                    <td>{{ $log->announcement?->title ?? '-' }}</td>
                                    <td>{{ strtoupper($log->channel) }}</td>
                                    <td>{{ $log->target }}</td>
                                    <td>
                                        @php
                                            $status = strtoupper((string) $log->status);
                                            $badgeClass = $status === 'SENT'
                                                ? 'badge-success'
                                                : ($status === 'FAILED' ? 'badge-danger' : 'badge-warning');
                                        @endphp
                                        <span class="badge {{ $badgeClass }}">{{ $status }}</span>
                                    </td>
                                    <td>{{ \Illuminate\Support\Str::limit((string) $log->response, 60) ?: '-' }}</td>
                                    <td>{{ $log->sent_at?->format('d/m/Y H:i:s') ?? '-' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted">Belum ada log announcement.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if ($announcementLogs->hasPages())
                <div class="card-footer">
                    {{ $announcementLogs->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
