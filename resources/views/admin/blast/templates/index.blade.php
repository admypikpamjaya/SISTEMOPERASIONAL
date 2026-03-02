@extends('layouts.app')

@section('title', 'Template Blast')

@section('content')
<style>
@import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');

:root {
    --tpl-blue-900: #102a66;
    --tpl-blue-800: #1a56db;
    --tpl-blue-700: #2563eb;
    --tpl-blue-600: #3b82f6;
    --tpl-blue-100: #dbeafe;
    --tpl-blue-50: #eff6ff;
    --tpl-text-900: #0f172a;
    --tpl-text-700: #334155;
    --tpl-text-500: #64748b;
    --tpl-border: #dbe4f0;
    --tpl-bg: #f3f7ff;
}

.tpl-page {
    font-family: 'Plus Jakarta Sans', sans-serif;
    padding: 4px 2px 14px;
    color: var(--tpl-text-900);
}

.tpl-head {
    border-radius: 14px;
    padding: 20px 22px;
    margin-bottom: 16px;
    background: linear-gradient(135deg, var(--tpl-blue-900) 0%, var(--tpl-blue-800) 60%, var(--tpl-blue-700) 100%);
    box-shadow: 0 12px 24px rgba(26,86,219,.22);
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 12px;
    flex-wrap: wrap;
}

.tpl-head-title {
    font-size: 20px;
    font-weight: 800;
    color: #fff;
    line-height: 1.25;
    margin-bottom: 4px;
}

.tpl-head-sub {
    font-size: 12px;
    color: rgba(255,255,255,.86);
}

.tpl-head-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
}

.tpl-btn {
    border: 1px solid transparent;
    border-radius: 8px;
    font-size: 12px;
    font-weight: 700;
    padding: 8px 11px;
    line-height: 1.2;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    transition: .15s;
}

.tpl-btn:hover {
    transform: translateY(-1px);
}

.tpl-btn.primary {
    color: #fff;
    background: linear-gradient(135deg, #1d4ed8, #2563eb);
}

.tpl-btn.ghost {
    color: #fff;
    border-color: rgba(255,255,255,.4);
    background: rgba(255,255,255,.1);
}

.tpl-panel {
    border: 1px solid var(--tpl-border);
    border-radius: 14px;
    overflow: hidden;
    background: #fff;
    box-shadow: 0 8px 18px rgba(15,23,42,.06);
}

.tpl-panel-body {
    padding: 16px;
}

.tpl-alert {
    border-radius: 10px;
    font-size: 12.5px;
    font-weight: 600;
    padding: 10px 12px;
    margin-bottom: 12px;
}

.tpl-alert.success {
    border: 1px solid #86efac;
    background: #f0fdf4;
    color: #166534;
}

.tpl-alert.error {
    border: 1px solid #fecaca;
    background: #fef2f2;
    color: #991b1b;
}

.tpl-filter {
    border: 1px solid var(--tpl-border);
    border-radius: 12px;
    background: var(--tpl-bg);
    padding: 12px;
    margin-bottom: 12px;
    display: flex;
    flex-wrap: wrap;
    align-items: flex-end;
    gap: 10px;
}

.tpl-field {
    min-width: 180px;
}

.tpl-label {
    display: block;
    margin-bottom: 5px;
    font-size: 12px;
    font-weight: 700;
    color: var(--tpl-text-700);
}

.tpl-input {
    width: 100%;
    border: 1px solid var(--tpl-border);
    border-radius: 8px;
    height: 36px;
    background: #fff;
    color: var(--tpl-text-900);
    font-size: 12.5px;
    padding: 0 10px;
    font-family: inherit;
}

.tpl-input:focus {
    outline: none;
    border-color: var(--tpl-blue-600);
    box-shadow: 0 0 0 3px rgba(37,99,235,.14);
}

.tpl-table-wrap {
    border: 1px solid var(--tpl-border);
    border-radius: 12px;
    overflow: auto;
}

.tpl-table {
    width: 100%;
    border-collapse: collapse;
    min-width: 900px;
}

.tpl-table th {
    background: #f8fbff;
    color: var(--tpl-text-500);
    text-transform: uppercase;
    letter-spacing: .04em;
    font-size: 10.5px;
    font-weight: 800;
    padding: 10px 12px;
    border-bottom: 1px solid var(--tpl-border);
    white-space: nowrap;
}

.tpl-table td {
    padding: 11px 12px;
    font-size: 12.5px;
    color: var(--tpl-text-700);
    border-bottom: 1px solid #eef3fb;
    vertical-align: top;
}

.tpl-table tr:hover td {
    background: #f8fbff;
}

.tpl-table tr:last-child td {
    border-bottom: none;
}

.tpl-name {
    font-weight: 700;
    color: var(--tpl-text-900);
}

.tpl-content {
    color: var(--tpl-text-500);
    line-height: 1.55;
    white-space: pre-wrap;
}

.tpl-badge {
    display: inline-flex;
    align-items: center;
    border-radius: 999px;
    padding: 3px 8px;
    font-size: 10px;
    font-weight: 800;
    letter-spacing: .02em;
}

.tpl-badge.info {
    background: #dbeafe;
    color: #1d4ed8;
}

.tpl-badge.success {
    background: #dcfce7;
    color: #166534;
}

.tpl-badge.muted {
    background: #e2e8f0;
    color: #334155;
}

.tpl-row-actions {
    display: flex;
    gap: 6px;
}

.tpl-icon-btn {
    width: 30px;
    height: 30px;
    border-radius: 8px;
    border: 1px solid var(--tpl-border);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    text-decoration: none;
    background: #fff;
    color: var(--tpl-text-700);
    transition: .15s;
}

.tpl-icon-btn:hover {
    transform: translateY(-1px);
}

.tpl-icon-btn.edit {
    border-color: #bfdbfe;
    color: #1d4ed8;
    background: #eff6ff;
}

.tpl-icon-btn.delete {
    border-color: #fecaca;
    color: #b91c1c;
    background: #fff1f2;
}

.tpl-empty {
    text-align: center;
    color: var(--tpl-text-500);
    padding: 20px 12px;
}

@media (max-width: 768px) {
    .tpl-head {
        padding: 16px;
    }

    .tpl-head-title {
        font-size: 17px;
    }

    .tpl-filter {
        padding: 10px;
    }

    .tpl-field {
        min-width: 100%;
    }
}
</style>

<div class="tpl-page">
    <div class="tpl-head">
        <div>
            <div class="tpl-head-title">Template Blast</div>
            <div class="tpl-head-sub">Kelola template WhatsApp dan Email agar siap dipakai di halaman blasting.</div>
        </div>
        <div class="tpl-head-actions">
            <a href="{{ route('admin.blast.whatsapp') }}" class="tpl-btn ghost">
                <i class="fab fa-whatsapp"></i> WhatsApp Blast
            </a>
            <a href="{{ route('admin.blast.email') }}" class="tpl-btn ghost">
                <i class="fas fa-envelope"></i> Email Blast
            </a>
            <a href="{{ route('admin.blast.templates.create', ['channel' => $channel ?: 'whatsapp']) }}" class="tpl-btn primary">
                <i class="fas fa-plus"></i> Tambah Template
            </a>
        </div>
    </div>

    <div class="tpl-panel">
        <div class="tpl-panel-body">
            @if(session('success'))
                <div class="tpl-alert success">{{ session('success') }}</div>
            @endif

            @if($errors->any())
                <div class="tpl-alert error">{{ $errors->first() }}</div>
            @endif

            <form method="GET" action="{{ route('admin.blast.templates.index') }}" class="tpl-filter">
                <div class="tpl-field">
                    <label for="channelFilter" class="tpl-label">Channel</label>
                    <select id="channelFilter" name="channel" class="tpl-input">
                        <option value="">Semua Channel</option>
                        <option value="whatsapp" {{ $channel === 'whatsapp' ? 'selected' : '' }}>WhatsApp</option>
                        <option value="email" {{ $channel === 'email' ? 'selected' : '' }}>Email</option>
                    </select>
                </div>
                <button type="submit" class="tpl-btn primary">
                    <i class="fas fa-filter"></i> Filter
                </button>
                <a href="{{ route('admin.blast.templates.index') }}" class="tpl-btn" style="background:#fff;border-color:var(--tpl-border);color:var(--tpl-text-700);">
                    Reset
                </a>
            </form>

            <div class="tpl-table-wrap">
                <table class="tpl-table">
                    <thead>
                        <tr>
                            <th style="width:56px;">No</th>
                            <th style="width:210px;">Nama Template</th>
                            <th style="width:120px;">Channel</th>
                            <th style="width:120px;">Status</th>
                            <th>Isi Template</th>
                            <th style="width:120px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($templates as $template)
                            <tr>
                                <td>{{ ($templates->currentPage() - 1) * $templates->perPage() + $loop->iteration }}</td>
                                <td>
                                    <div class="tpl-name">{{ $template->name }}</div>
                                </td>
                                <td>
                                    @if(strtolower((string) $template->channel) === 'whatsapp')
                                        <span class="tpl-badge info">WhatsApp</span>
                                    @else
                                        <span class="tpl-badge info">Email</span>
                                    @endif
                                </td>
                                <td>
                                    @if($template->is_active)
                                        <span class="tpl-badge success">Aktif</span>
                                    @else
                                        <span class="tpl-badge muted">Nonaktif</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="tpl-content">{{ \Illuminate\Support\Str::limit($template->content, 220) }}</div>
                                </td>
                                <td>
                                    <div class="tpl-row-actions">
                                        <a href="{{ route('admin.blast.templates.edit', ['id' => $template->id]) }}" class="tpl-icon-btn edit" title="Edit">
                                            <i class="fas fa-pen"></i>
                                        </a>
                                        <form method="POST" action="{{ route('admin.blast.templates.destroy', ['id' => $template->id]) }}" onsubmit="return confirm('Hapus template ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="tpl-icon-btn delete" title="Hapus">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="tpl-empty">Belum ada template pada channel ini.</td>
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
</div>
@endsection

