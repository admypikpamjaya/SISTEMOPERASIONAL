@extends('layouts.app')

@section('title', __('app.blast.pdam_recipient_title'))

@section('content')
<style>
.general-page {
    --gen-primary: var(--app-accent);
    --gen-primary-strong: var(--app-accent-strong);
    --gen-bg: var(--app-bg);
    --gen-surface: var(--app-surface);
    --gen-surface-soft: var(--app-surface-soft);
    --gen-surface-muted: var(--app-surface-muted);
    --gen-border: var(--app-border);
    --gen-text: var(--app-text);
    --gen-text-soft: var(--app-text-soft);
    --gen-text-muted: var(--app-text-muted);
    --gen-row-hover: var(--app-row-hover);
    --gen-shadow: var(--app-shadow);
    --gen-success: var(--success-color, #10b981);
    --gen-danger: var(--danger-color, #ef4444);
    font-family: 'Plus Jakarta Sans', 'Source Sans Pro', Arial, sans-serif;
    color: var(--gen-text);
    padding: 6px 4px 18px;
}

.general-head {
    border-radius: var(--radius-md, 14px);
    padding: 20px 22px;
    margin-bottom: 14px;
    background: var(--grad-hero);
    box-shadow: var(--gen-shadow);
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 12px;
}

.general-head-main {
    display: flex;
    align-items: center;
    gap: 14px;
    min-width: 260px;
}

.general-head-icon {
    width: 42px;
    height: 42px;
    border-radius: var(--radius-sm, 8px);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: rgba(255, 255, 255, .14);
    color: #fff;
    border: 1px solid rgba(255, 255, 255, .22);
    flex: 0 0 auto;
}

.general-head-title {
    font-size: 19px;
    font-weight: 800;
    color: #fff;
    margin-bottom: 4px;
    letter-spacing: 0;
}

.general-head-sub {
    font-size: 12.5px;
    color: rgba(255, 255, 255, .88);
    line-height: 1.45;
}

.general-head-actions,
.general-import-wrap,
.general-filter {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
}

.general-btn {
    min-height: 36px;
    border-radius: var(--radius-sm, 8px);
    border: 1px solid transparent;
    font-size: 12px;
    font-weight: 700;
    line-height: 1.2;
    padding: 8px 11px;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    transition: background .15s ease, border-color .15s ease, box-shadow .15s ease, transform .15s ease;
    white-space: nowrap;
}

.general-btn:hover {
    transform: translateY(-1px);
    text-decoration: none;
}

.general-btn.ghost {
    color: #fff;
    border-color: rgba(255, 255, 255, .38);
    background: rgba(255, 255, 255, .1);
}

.general-btn.primary {
    background: linear-gradient(135deg, var(--gen-primary-strong), var(--gen-primary));
    border-color: transparent;
    color: #fff;
    box-shadow: 0 8px 18px var(--app-row-selected-strong);
}

.general-btn.light {
    background: var(--gen-surface);
    border-color: var(--gen-border);
    color: var(--gen-primary-strong);
}

.general-btn.neutral {
    background: var(--gen-surface-soft);
    border-color: var(--gen-border);
    color: var(--gen-text-soft);
}

.general-btn.danger {
    background: var(--red-bg, rgba(239, 68, 68, .12));
    border-color: var(--red-border, rgba(239, 68, 68, .22));
    color: var(--gen-danger);
}

.general-btn.danger:hover {
    background: var(--gen-danger);
    color: #fff;
    border-color: var(--gen-danger);
}

.general-btn:disabled {
    opacity: .6;
    cursor: not-allowed;
    transform: none;
}

.general-panel {
    border: 1px solid var(--gen-border);
    border-radius: var(--radius-md, 14px);
    background: var(--gen-surface);
    box-shadow: var(--shadow-sm);
    overflow: hidden;
}

.general-panel-body {
    padding: 16px 16px 18px;
}

.general-alert {
    border-radius: var(--radius-sm, 8px);
    padding: 10px 12px;
    font-size: 12.5px;
    font-weight: 600;
    margin-bottom: 12px;
}

.general-alert.success {
    border: 1px solid var(--green-border);
    background: var(--green-bg);
    color: var(--success-color);
}

.general-alert.error {
    border: 1px solid var(--red-border);
    background: var(--red-bg);
    color: var(--gen-danger);
}

.general-stats {
    display: grid;
    grid-template-columns: repeat(1, minmax(0, 1fr));
    gap: 10px;
    margin-bottom: 12px;
}

.general-stat {
    border: 1px solid var(--gen-border);
    border-radius: var(--radius, 12px);
    background: var(--gen-surface-soft);
    padding: 13px;
    display: flex;
    align-items: center;
    gap: 12px;
    min-height: 78px;
}

.general-stat-icon {
    width: 40px;
    height: 40px;
    border-radius: var(--radius-sm, 8px);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: var(--app-row-selected);
    color: var(--gen-primary-strong);
    flex: 0 0 auto;
}

.general-stat-label {
    font-size: 11px;
    font-weight: 700;
    text-transform: none;
    letter-spacing: 0;
    color: var(--gen-text-muted);
    margin-bottom: 4px;
}

.general-stat-value {
    font-size: 25px;
    font-weight: 800;
    color: var(--gen-text);
    line-height: 1;
}

.general-toolbar {
    border: 1px solid var(--gen-border);
    border-radius: var(--radius, 12px);
    background: var(--gen-surface-soft);
    padding: 12px;
    margin-bottom: 12px;
    display: grid;
    grid-template-columns: minmax(0, 1fr) auto;
    align-items: flex-end;
    gap: 12px;
}

.general-filter {
    display: flex;
    align-items: flex-end;
    gap: 8px;
}

.general-field {
    min-width: 170px;
}

.general-field.compact {
    min-width: 112px;
}

.general-label {
    display: block;
    margin-bottom: 5px;
    font-size: 12px;
    font-weight: 700;
    color: var(--gen-text-soft);
}

.general-input,
.general-select {
    width: 100%;
    border: 1px solid var(--gen-border);
    border-radius: var(--radius-sm, 8px);
    background: var(--gen-surface);
    color: var(--gen-text);
    font-size: 12.5px;
    font-family: inherit;
    height: 36px;
    padding: 0 10px;
}

.general-input:focus,
.general-select:focus {
    outline: none;
    border-color: var(--gen-primary);
    box-shadow: 0 0 0 3px var(--app-row-selected);
}

.general-select option {
    background: var(--gen-surface);
    color: var(--gen-text);
}

.general-file-btn {
    cursor: pointer;
}

.general-import-form {
    display: inline-flex;
    align-items: flex-end;
    gap: 8px;
    flex-wrap: wrap;
}

.general-import-wrap {
    align-items: flex-end;
    justify-content: flex-end;
}

.general-table-wrap {
    border: 1px solid var(--gen-border);
    border-radius: var(--radius, 12px);
    overflow: auto;
    background: var(--gen-surface);
}

.general-table {
    width: 100%;
    border-collapse: collapse;
    min-width: 1280px;
}

.general-table th {
    background: var(--gen-surface-soft);
    color: var(--gen-text-muted);
    text-transform: none;
    letter-spacing: 0;
    font-size: 10.5px;
    font-weight: 800;
    padding: 10px 12px;
    border-bottom: 1px solid var(--gen-border);
    white-space: nowrap;
}

.general-table td {
    padding: 11px 12px;
    border-bottom: 1px solid var(--gen-border);
    font-size: 12.5px;
    color: var(--gen-text-soft);
    vertical-align: top;
}

.general-table tr:last-child td {
    border-bottom: none;
}

.general-table tr:hover td {
    background: var(--gen-row-hover);
}

.general-checkbox {
    width: 16px;
    height: 16px;
    accent-color: var(--gen-primary-strong);
    cursor: pointer;
}

.general-name {
    font-weight: 700;
    color: var(--gen-text);
}

.general-muted {
    color: var(--gen-text-muted);
}

.general-link {
    color: var(--gen-primary-strong);
    font-weight: 700;
    text-decoration: none;
}

.general-link:hover {
    text-decoration: underline;
}

.general-empty {
    text-align: center;
    color: var(--gen-text-muted);
    padding: 28px 12px;
}

.general-row-actions {
    display: flex;
    gap: 6px;
    align-items: center;
}

.general-icon-btn {
    width: 32px;
    min-height: 32px;
    padding: 0;
}

.general-table-footer {
    display: flex;
    justify-content: flex-end;
    margin-top: 12px;
}

.general-table-footer .pagination {
    margin: 0;
}

.general-table-footer .page-link {
    background: var(--gen-surface);
    border-color: var(--gen-border);
    color: var(--gen-text-soft);
}

.general-table-footer .page-item.active .page-link {
    background: var(--gen-primary);
    border-color: var(--gen-primary);
    color: #fff;
}

.general-table-footer .page-link:hover {
    background: var(--gen-row-hover);
    color: var(--gen-primary-strong);
}

body.dark-mode .general-head {
    box-shadow: 0 18px 38px rgba(0, 0, 0, .24);
}

body.dark-mode .general-panel,
body.dark-mode .general-stat,
body.dark-mode .general-toolbar,
body.dark-mode .general-table-wrap {
    box-shadow: none;
}

body.dark-mode .general-btn.primary {
    box-shadow: none;
}

body.dark-mode .general-btn.light,
body.dark-mode .general-btn.neutral {
    background: var(--gen-surface-soft);
}

body.dark-mode .general-table td {
    background: transparent;
}

@media (max-width: 900px) {
    .general-toolbar {
        grid-template-columns: 1fr;
    }

    .general-filter {
        flex-direction: column;
        align-items: stretch;
    }

    .general-import-wrap {
        justify-content: flex-start;
    }
}

@media (max-width: 640px) {
    .general-head {
        align-items: flex-start;
    }

    .general-head-main,
    .general-head-actions,
    .general-import-wrap,
    .general-btn {
        width: 100%;
    }
}
</style>

<div class="general-page">
    <div class="general-head">
        <div class="general-head-main">
            <div class="general-head-icon">
                <i class="fas fa-water"></i>
            </div>
            <div>
                <div class="general-head-title">{{ __('app.blast.pdam_recipient_title') }}</div>
                <div class="general-head-sub">{{ __('app.blast.pdam_recipient_subtitle') }}</div>
            </div>
        </div>
        <div class="general-head-actions">
            <a href="{{ route('admin.blast.recipients.index') }}" class="general-btn ghost">
                <i class="fas fa-user-graduate"></i> {{ __('app.blast.data_students') }}
            </a>
            <a href="{{ route('admin.blast.recipients.general.index') }}" class="general-btn ghost">
                <i class="fas fa-address-book"></i> {{ __('app.blast.data_general') }}
            </a>
        </div>
    </div>

    <div class="general-panel">
        <div class="general-panel-body">
            @if(session('success'))
                <div class="general-alert success">{{ session('success') }}</div>
            @endif

            @if(session('error'))
                <div class="general-alert error">{{ session('error') }}</div>
            @endif

            @if($errors->any())
                <div class="general-alert error">{{ $errors->first() }}</div>
            @endif

            <div class="general-stats">
                <div class="general-stat">
                    <div class="general-stat-icon"><i class="fas fa-water"></i></div>
                    <div>
                        <div class="general-stat-label">{{ __('app.blast.total_pdam_recipients') }}</div>
                        <div class="general-stat-value">{{ $totalRecipients ?? $recipients->total() }}</div>
                    </div>
                </div>
            </div>

            <div class="general-toolbar">
                <form method="GET" action="{{ route('admin.blast.recipients.pdam.index') }}" class="general-filter">
                    <div class="general-field">
                        <label class="general-label">{{ __('app.blast.search') }}</label>
                        <input type="text" name="q" value="{{ $search ?? '' }}" class="general-input" placeholder="{{ __('app.blast.search_pdam_placeholder') }}">
                    </div>
                    <div class="general-field compact">
                        <label class="general-label">{{ __('app.blast.per_page') }}</label>
                        <select name="per_page" class="general-select">
                            @foreach(($allowedPerPage ?? [20, 50, 100, 200]) as $size)
                                <option value="{{ $size }}" @selected((int) ($perPage ?? 50) === (int) $size)>{{ $size }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="general-btn primary">
                        <i class="fas fa-filter"></i> {{ __('app.blast.apply_filter') }}
                    </button>
                    <a href="{{ route('admin.blast.recipients.pdam.index') }}" class="general-btn neutral">
                        {{ __('app.blast.reset') }}
                    </a>
                </form>

                <form id="bulk-delete-pdam-form" method="POST" action="{{ route('admin.blast.recipients.pdam.bulk-delete') }}">
                    @csrf
                    @method('DELETE')
                </form>

                <div class="general-import-wrap">
                    <a href="{{ route('admin.blast.recipients.pdam.create') }}" class="general-btn light">
                        <i class="fas fa-plus"></i> {{ __('app.blast.manual_input') }}
                    </a>
                    <form action="{{ route('admin.blast.recipients.pdam.import') }}" method="POST" enctype="multipart/form-data" class="general-import-form">
                        @csrf
                        <label class="general-btn light general-file-btn">
                            <i class="fas fa-file-import"></i> {{ __('app.blast.import_pdam_excel') }}
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
                    <button type="submit" class="general-btn danger" id="bulkDeletePdamBtn" form="bulk-delete-pdam-form" disabled>
                        <i class="fas fa-trash-alt"></i> <span class="general-btn-label">{{ __('app.blast.delete_selected') }}</span>
                    </button>
                    <form method="POST" action="{{ route('admin.blast.recipients.pdam.destroy-all') }}" data-confirm-message="{{ __('app.blast.delete_all_pdam_confirm') }}" onsubmit="return confirm(this.dataset.confirmMessage)">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="general-btn danger">
                            <i class="fas fa-trash"></i> {{ __('app.blast.delete_all') }}
                        </button>
                    </form>
                </div>
            </div>

            <div class="general-table-wrap">
                <table class="general-table">
                    <thead>
                        <tr>
                            <th style="width:44px;">
                                <input type="checkbox" class="general-checkbox" id="selectAllPdam">
                            </th>
                            <th style="width:56px;">{{ __('app.blast.no') }}</th>
                            <th>Timestamp</th>
                            <th>Nama Lengkap</th>
                            <th>Instansi / Pekerjaan</th>
                            <th>Nomor telpon / WA</th>
                            <th>Email</th>
                            <th>Sertifikat</th>
                            <th style="width:140px;">{{ __('app.blast.action') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recipients as $recipient)
                            <tr>
                                <td>
                                    <input
                                        type="checkbox"
                                        class="general-checkbox pdam-recipient-checkbox"
                                        name="ids[]"
                                        value="{{ $recipient->id }}"
                                        form="bulk-delete-pdam-form"
                                    >
                                </td>
                                <td>{{ ($recipients->currentPage() - 1) * $recipients->perPage() + $loop->iteration }}</td>
                                <td>{{ $recipient->timestamp_excel ?? '-' }}</td>
                                <td><div class="general-name">{{ $recipient->nama_lengkap }}</div></td>
                                <td>{{ $recipient->instansi_pekerjaan ?? '-' }}</td>
                                <td>{{ $recipient->nomor_telpon ?? '-' }}</td>
                                <td>{{ $recipient->email ?? '-' }}</td>
                                <td>
                                    @php($certificate = trim((string) ($recipient->sertifikat ?? '')))
                                    @if($certificate !== '')
                                        @if(\Illuminate\Support\Str::startsWith($certificate, ['http://', 'https://']))
                                            <a href="{{ $certificate }}" class="general-link" target="_blank" rel="noopener">
                                                {{ __('app.blast.open_certificate') }}
                                            </a>
                                        @else
                                            {{ \Illuminate\Support\Str::limit($certificate, 42) }}
                                        @endif
                                    @else
                                        <span class="general-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="general-row-actions">
                                        <a href="{{ route('admin.blast.recipients.pdam.edit', $recipient->id) }}" class="general-btn light general-icon-btn" title="{{ __('app.blast.edit_recipient') }}" aria-label="{{ __('app.blast.edit_recipient') }}">
                                            <i class="fas fa-pen"></i>
                                        </a>
                                        <form method="POST" action="{{ route('admin.blast.recipients.pdam.destroy', $recipient->id) }}" data-confirm-message="{{ __('app.blast.delete_pdam_confirm') }}" onsubmit="return confirm(this.dataset.confirmMessage)">
                                            @csrf
                                            @method('DELETE')
                                            <button class="general-btn danger general-icon-btn" type="submit" title="{{ __('app.blast.delete_recipient') }}" aria-label="{{ __('app.blast.delete_recipient') }}">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="general-empty">
                                    {{ __('app.blast.no_pdam_recipient_data') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="general-table-footer">
                {{ $recipients->links() }}
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const pdamText = {
        deleteSelected: @json(__('app.blast.delete_selected')),
        chooseMinOneDelete: @json(__('app.blast.choose_min_one_pdam_delete')),
        deleteSelectedConfirm: @json(__('app.blast.delete_selected_pdam_confirm', ['count' => '__COUNT__'])),
    };
    const form = document.getElementById('bulk-delete-pdam-form');
    const selectAll = document.getElementById('selectAllPdam');
    const checkboxes = Array.from(document.querySelectorAll('.pdam-recipient-checkbox'));
    const deleteBtn = document.getElementById('bulkDeletePdamBtn');

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
        const deleteLabel = deleteBtn.querySelector('.general-btn-label');

        deleteBtn.disabled = selectedCount === 0;
        const labelText = selectedCount > 0
            ? `${pdamText.deleteSelected} (${selectedCount})`
            : pdamText.deleteSelected;
        if (deleteLabel) {
            deleteLabel.textContent = labelText;
        } else {
            deleteBtn.textContent = labelText;
        }

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

    checkboxes.forEach(cb => {
        cb.addEventListener('change', updateState);
    });

    form.addEventListener('submit', (e) => {
        const selectedCount = checkboxes.filter(cb => cb.checked).length;
        if (selectedCount === 0) {
            e.preventDefault();
            alert(pdamText.chooseMinOneDelete);
            return false;
        }
        if (!confirm(pdamText.deleteSelectedConfirm.replace('__COUNT__', selectedCount))) {
            e.preventDefault();
            return false;
        }
    });

    updateState();
});
</script>
@endsection
