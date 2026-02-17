@extends('layouts.app')

@section('section_name', 'Reminder Plan')

@section('content')
@php
    $isEdit = (bool) ($editingReminder ?? null);
    $formAction = $isEdit
        ? route('admin.reminders.update', $editingReminder->id)
        : route('admin.reminders.store');
    $defaultRemindAt = $isEdit && $editingReminder->remind_at
        ? $editingReminder->remind_at->timezone('Asia/Jakarta')->format('Y-m-d\TH:i')
        : now('Asia/Jakarta')->format('Y-m-d\TH:i');
    $selectedType = old('type', $editingReminder->type ?? 'GENERAL');
    $selectedAnnouncementId = old('announcement_id', $editingReminder->announcement_id ?? '');
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
        <div class="card card-warning">
            <div class="card-header">
                <h3 class="card-title">{{ $isEdit ? 'Edit Reminder' : 'Buat Reminder Baru' }}</h3>
            </div>
            <form method="POST" action="{{ $formAction }}">
                @csrf
                @if ($isEdit)
                    @method('PUT')
                @endif

                <div class="card-body">
                    <div class="form-group">
                        <label for="title">Judul Reminder</label>
                        <input
                            type="text"
                            id="title"
                            name="title"
                            class="form-control @error('title') is-invalid @enderror"
                            value="{{ old('title', $editingReminder->title ?? '') }}"
                            placeholder="Contoh: Reminder Pengumuman Ujian"
                            required
                        >
                    </div>

                    <div class="form-group">
                        <label for="description">Deskripsi (opsional)</label>
                        <textarea
                            id="description"
                            name="description"
                            rows="3"
                            class="form-control @error('description') is-invalid @enderror"
                            placeholder="Detail reminder atau catatan tambahan..."
                        >{{ old('description', $editingReminder->description ?? '') }}</textarea>
                    </div>

                    <div class="form-group">
                        <label for="remind_at">Tanggal & Jam Reminder</label>
                        <input
                            type="datetime-local"
                            id="remind_at"
                            name="remind_at"
                            class="form-control @error('remind_at') is-invalid @enderror"
                            value="{{ old('remind_at', $defaultRemindAt) }}"
                            required
                        >
                        <small class="text-muted">
                            Bisa untuk hari ini juga, termasuk jam dan menit.
                        </small>
                    </div>

                    <div class="form-group">
                        <label for="alert_before_minutes">Alert Menjelang (menit)</label>
                        <input
                            type="number"
                            min="1"
                            max="10080"
                            id="alert_before_minutes"
                            name="alert_before_minutes"
                            class="form-control @error('alert_before_minutes') is-invalid @enderror"
                            value="{{ old('alert_before_minutes', $editingReminder->alert_before_minutes ?? 30) }}"
                            required
                        >
                    </div>

                    <div class="form-group">
                        <label for="type">Tipe Reminder</label>
                        <select
                            id="type"
                            name="type"
                            class="form-control @error('type') is-invalid @enderror"
                        >
                            <option value="GENERAL" @selected($selectedType === 'GENERAL')>Umum</option>
                            <option value="ANNOUNCEMENT" @selected($selectedType === 'ANNOUNCEMENT')>Announcement</option>
                        </select>
                    </div>

                    <div class="form-group" id="announcement-link-group">
                        <label for="announcement_id">Kaitkan ke Announcement (opsional)</label>
                        <select
                            id="announcement_id"
                            name="announcement_id"
                            class="form-control @error('announcement_id') is-invalid @enderror"
                        >
                            <option value="">Tanpa pilih announcement (ingatkan buat pengumuman baru)</option>
                            @foreach ($announcements as $announcement)
                                <option
                                    value="{{ $announcement->id }}"
                                    @selected((string) $selectedAnnouncementId === (string) $announcement->id)
                                >
                                    #{{ $announcement->id }} - {{ \Illuminate\Support\Str::limit($announcement->title, 70) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="card-footer d-flex justify-content-between">
                    <button type="submit" class="btn btn-warning">
                        {{ $isEdit ? 'Update Reminder' : 'Simpan Reminder' }}
                    </button>
                    @if ($isEdit)
                        <a href="{{ route('admin.reminders.index') }}" class="btn btn-default">Batal Edit</a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Daftar Reminder</h3>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped table-bordered mb-0">
                        <thead>
                            <tr>
                                <th style="width: 60px;">ID</th>
                                <th>Reminder</th>
                                <th style="width: 180px;">Jadwal</th>
                                <th style="width: 150px;">Status</th>
                                <th style="width: 190px;">Announcement</th>
                                <th style="width: 160px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $now = now('Asia/Jakarta'); @endphp
                            @forelse ($reminders as $reminder)
                                @php
                                    $alertState = $reminder->alertState($now);
                                    $typeLabel = $reminder->isAnnouncementType() ? 'Announcement' : 'Umum';
                                @endphp
                                <tr>
                                    <td>{{ $reminder->id }}</td>
                                    <td>
                                        <div><strong>{{ $reminder->title }}</strong></div>
                                        <div class="text-muted">{{ $reminder->description ?: '-' }}</div>
                                        <small class="text-secondary">
                                            Tipe: {{ $typeLabel }} | Alert: {{ $reminder->alert_before_minutes }} menit sebelumnya
                                        </small>
                                        <br>
                                        <small class="text-secondary">
                                            Dibuat oleh: {{ $reminder->creator?->name ?? '-' }}
                                        </small>
                                    </td>
                                    <td>
                                        <div>{{ $reminder->remind_at?->timezone('Asia/Jakarta')->format('d/m/Y H:i') ?? '-' }}</div>
                                        <small class="text-muted">{{ $reminder->created_at?->timezone('Asia/Jakarta')->format('d/m/Y H:i') ?? '-' }}</small>
                                    </td>
                                    <td>
                                        @if ($reminder->is_active)
                                            <span class="badge badge-success">Aktif</span>
                                        @else
                                            <span class="badge badge-secondary">Nonaktif</span>
                                        @endif

                                        @if ($alertState === 'due')
                                            <span class="badge badge-danger">Hari-H / Due</span>
                                        @elseif ($alertState === 'upcoming')
                                            <span class="badge badge-warning">Mendekati</span>
                                        @else
                                            <span class="badge badge-light">Belum Waktu Alert</span>
                                        @endif

                                        @if (! $reminder->is_active && $reminder->deactivated_at)
                                            <div class="text-muted mt-1" style="font-size: 12px;">
                                                Nonaktif: {{ $reminder->deactivated_at->timezone('Asia/Jakarta')->format('d/m/Y H:i') }}
                                            </div>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($reminder->isAnnouncementType())
                                            @if ($reminder->announcement)
                                                <div>#{{ $reminder->announcement->id }}</div>
                                                <div>{{ \Illuminate\Support\Str::limit($reminder->announcement->title, 60) }}</div>
                                                <a
                                                    href="{{ route('admin.announcements.edit', ['id' => $reminder->announcement->id, 'focus_reminder' => $reminder->id, 'focus_announcement' => $reminder->announcement->id]) }}"
                                                    class="btn btn-xs btn-outline-primary mt-1"
                                                >
                                                    Buka Announcement
                                                </a>
                                            @else
                                                <span class="text-muted">Arahkan ke pembuatan announcement baru.</span>
                                                <a
                                                    href="{{ route('admin.announcements.index', ['focus_reminder' => $reminder->id]) }}"
                                                    class="btn btn-xs btn-outline-primary mt-1"
                                                >
                                                    Buat Announcement
                                                </a>
                                            @endif
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        <a
                                            href="{{ route('admin.reminders.edit', $reminder->id) }}"
                                            class="btn btn-sm btn-warning mb-1"
                                        >
                                            Edit
                                        </a>
                                        <form method="POST" action="{{ route('admin.reminders.toggle', $reminder->id) }}">
                                            @csrf
                                            <input
                                                type="hidden"
                                                name="is_active"
                                                value="{{ $reminder->is_active ? 0 : 1 }}"
                                            >
                                            <button
                                                type="submit"
                                                class="btn btn-sm {{ $reminder->is_active ? 'btn-danger' : 'btn-success' }}"
                                            >
                                                {{ $reminder->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted">
                                        Belum ada reminder plan.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if ($reminders->hasPages())
                <div class="card-footer">
                    {{ $reminders->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@section('js')
<script>
    (function () {
        const typeInput = document.getElementById('type');
        const announcementGroup = document.getElementById('announcement-link-group');
        const announcementSelect = document.getElementById('announcement_id');

        if (!typeInput || !announcementGroup || !announcementSelect) {
            return;
        }

        function syncAnnouncementField() {
            const isAnnouncementType = typeInput.value === 'ANNOUNCEMENT';
            announcementGroup.style.display = isAnnouncementType ? 'block' : 'none';

            if (!isAnnouncementType) {
                announcementSelect.value = '';
            }
        }

        typeInput.addEventListener('change', syncAnnouncementField);
        syncAnnouncementField();
    })();
</script>
@endsection
