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
    $focusedReminderId = (int) ($focusedReminderId ?? 0);
    $focusedAnnouncementId = (int) ($focusedAnnouncementId ?? 0);
    $focusedReminder = $focusedReminder ?? null;
    $pendingAnnouncementReminders = $pendingAnnouncementReminders ?? collect();
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

                @if (! $isEdit && $focusedReminder)
                    <input type="hidden" name="reminder_id" value="{{ $focusedReminder->id }}">
                @endif

                <div class="card-body">
                    @if (! $isEdit && $focusedReminder)
                        <div class="alert alert-warning">
                            <strong>Reminder Sumber:</strong>
                            #{{ $focusedReminder->id }} - {{ $focusedReminder->title }}<br>
                            <small>
                                Jadwal reminder: {{ $focusedReminder->remind_at?->format('d/m/Y H:i') ?? '-' }} |
                                Dibuat oleh: {{ $focusedReminder->creator?->name ?? '-' }}
                            </small><br>
                            <small>
                                Saat publish announcement, reminder ini akan otomatis ditautkan.
                            </small>
                            <div class="mt-2">
                                <a href="{{ route('admin.announcements.index') }}" class="btn btn-sm btn-default">
                                    Lepas Fokus Reminder
                                </a>
                            </div>
                        </div>
                    @endif

                    <div class="form-group">
                        <label for="title">Judul</label>
                        <input
                            type="text"
                            class="form-control @error('title') is-invalid @enderror"
                            id="title"
                            name="title"
                            value="{{ old('title', $editingAnnouncement->title ?? ($focusedReminder?->title ?? '')) }}"
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
                        >{{ old('message', $editingAnnouncement->message ?? ($focusedReminder?->description ?? '')) }}</textarea>
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

        @if ($isEdit)
            @php
                $editingAnnouncementReminders = $editingAnnouncement->reminders ?? collect();
            @endphp
            <div class="card mt-3">
                <div class="card-header">
                    <h3 class="card-title">Reminder Terkait Announcement Ini</h3>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered mb-0">
                            <thead>
                                <tr>
                                    <th style="width: 60px;">ID</th>
                                    <th>Reminder</th>
                                    <th style="width: 130px;">Status</th>
                                    <th style="width: 150px;">Jadwal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($editingAnnouncementReminders as $reminder)
                                    <tr class="{{ $focusedReminderId === (int) $reminder->id ? 'table-warning' : '' }}">
                                        <td>{{ $reminder->id }}</td>
                                        <td>
                                            <div><strong>{{ $reminder->title }}</strong></div>
                                            <small class="text-muted">{{ \Illuminate\Support\Str::limit((string) $reminder->description, 80) ?: '-' }}</small>
                                        </td>
                                        <td>
                                            @if ($reminder->is_active)
                                                <span class="badge badge-success">Aktif</span>
                                            @else
                                                <span class="badge badge-secondary">Nonaktif</span>
                                            @endif
                                        </td>
                                        <td>{{ $reminder->remind_at?->format('d/m/Y H:i') ?? '-' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted">
                                            Belum ada reminder yang terhubung.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif
    </div>

    <div class="col-lg-8">
        @if ($pendingAnnouncementReminders->isNotEmpty())
            <div class="card card-warning">
                <div class="card-header">
                    <h3 class="card-title">Reminder Announcement Belum Ditautkan</h3>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered mb-0">
                            <thead>
                                <tr>
                                    <th style="width: 60px;">ID</th>
                                    <th>Reminder</th>
                                    <th style="width: 150px;">Jadwal</th>
                                    <th style="width: 160px;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($pendingAnnouncementReminders as $pendingReminder)
                                    <tr class="{{ $focusedReminderId === (int) $pendingReminder->id ? 'table-warning' : '' }}">
                                        <td>{{ $pendingReminder->id }}</td>
                                        <td>
                                            <div><strong>{{ $pendingReminder->title }}</strong></div>
                                            <small class="text-muted">
                                                {{ \Illuminate\Support\Str::limit((string) $pendingReminder->description, 80) ?: '-' }}
                                            </small>
                                        </td>
                                        <td>{{ $pendingReminder->remind_at?->format('d/m/Y H:i') ?? '-' }}</td>
                                        <td>
                                            <a
                                                href="{{ route('admin.announcements.index', ['focus_reminder' => $pendingReminder->id]) }}"
                                                class="btn btn-sm btn-primary"
                                            >
                                                Gunakan Reminder Ini
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif

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
                                <th style="width: 220px;">Reminder Terkait</th>
                                <th style="width: 170px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($announcements as $announcement)
                                @php
                                    $announcementReminders = $announcement->reminders ?? collect();
                                    $nextActiveReminder = $announcementReminders
                                        ->where('is_active', true)
                                        ->sortBy('remind_at')
                                        ->first();
                                @endphp
                                <tr class="{{ $focusedAnnouncementId === (int) $announcement->id ? 'table-warning' : '' }}">
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
                                        <span class="badge badge-info">Total: {{ $announcement->reminders_total_count ?? 0 }}</span>
                                        <span class="badge badge-success">Aktif: {{ $announcement->reminders_active_count ?? 0 }}</span>
                                        <div class="mt-1">
                                            @if ($nextActiveReminder)
                                                <small class="text-muted">
                                                    Reminder aktif terdekat:
                                                    {{ $nextActiveReminder->remind_at?->format('d/m/Y H:i') ?? '-' }}
                                                </small>
                                            @else
                                                <small class="text-muted">Belum ada reminder aktif.</small>
                                            @endif
                                        </div>
                                        @if ($announcementReminders->isNotEmpty())
                                            <div class="mt-1">
                                                @foreach ($announcementReminders->take(2) as $reminder)
                                                    <div class="{{ $focusedReminderId === (int) $reminder->id ? 'bg-warning p-1 rounded mb-1' : '' }}">
                                                        <small>
                                                            #{{ $reminder->id }} {{ \Illuminate\Support\Str::limit($reminder->title, 35) }}
                                                            ({{ $reminder->remind_at?->format('d/m H:i') ?? '-' }})
                                                        </small>
                                                    </div>
                                                @endforeach
                                                @if ($announcementReminders->count() > 2)
                                                    <small class="text-muted">+{{ $announcementReminders->count() - 2 }} reminder lainnya</small>
                                                @endif
                                            </div>
                                        @endif
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
                                    <td colspan="6" class="text-center text-muted">Belum ada data announcement.</td>
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
