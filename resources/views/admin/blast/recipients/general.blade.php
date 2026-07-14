@extends('layouts.app')

@section('title', __('app.blast.general_recipient_title'))

@section('content')
<style>
@import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');

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
    font-family: 'Plus Jakarta Sans', sans-serif;
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
    grid-template-columns: repeat(3, minmax(0, 1fr));
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
    text-transform: uppercase;
    letter-spacing: .03em;
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
    display: grid;
    grid-template-columns: minmax(220px, 1.3fr) minmax(140px, .6fr) minmax(112px, .45fr) auto auto;
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
    min-width: 1120px;
}

.general-table th {
    background: var(--gen-surface-soft);
    color: var(--gen-text-muted);
    text-transform: uppercase;
    letter-spacing: .03em;
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

.general-badge {
    display: inline-flex;
    align-items: center;
    border-radius: 999px;
    padding: 3px 8px;
    font-size: 10px;
    font-weight: 800;
    letter-spacing: .02em;
}

.general-badge.valid {
    background: var(--green-bg);
    color: var(--success-color);
    border: 1px solid var(--green-border);
}

.general-badge.invalid {
    background: var(--red-bg);
    color: var(--gen-danger);
    border: 1px solid var(--red-border);
}

.general-error-detail {
    margin-top: 3px;
    font-size: 11px;
    color: var(--gen-text-muted);
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
    .general-stats {
        grid-template-columns: 1fr;
    }

    .general-toolbar {
        grid-template-columns: 1fr;
    }

    .general-filter {
        grid-template-columns: 1fr;
    }

    .general-field {
        min-width: 100%;
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
                <i class="fas fa-address-book"></i>
            </div>
            <div>
                <div class="general-head-title">{{ __('app.blast.general_recipient_title') }}</div>
                <div class="general-head-sub">{{ __('app.blast.general_recipient_subtitle') }}</div>
            </div>
        </div>
        <div class="general-head-actions">
            <a href="{{ route('admin.blast.recipients.index') }}" class="general-btn ghost">
                <i class="fas fa-user-graduate"></i> {{ __('app.blast.data_students') }}
            </a>
            <a href="{{ route('admin.blast.recipients.employees.index') }}" class="general-btn ghost">
                <i class="fas fa-building"></i> {{ __('app.blast.data_cooperative') }}
            </a>
            <a href="{{ route('admin.blast.recipients.employees-ypik.index') }}" class="general-btn ghost">
                <i class="fas fa-id-card"></i> {{ __('app.blast.employee_ypik_short') }}
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
                    <div class="general-stat-icon"><i class="fas fa-address-book"></i></div>
                    <div>
                        <div class="general-stat-label">{{ __('app.blast.total_general_recipients') }}</div>
                        <div class="general-stat-value">{{ $totalRecipients ?? $recipients->total() }}</div>
                    </div>
                </div>
                <div class="general-stat">
                    <div class="general-stat-icon"><i class="fas fa-check-circle"></i></div>
                    <div>
                        <div class="general-stat-label">{{ __('app.blast.valid_data') }}</div>
                        <div class="general-stat-value">{{ $validCount ?? 0 }}</div>
                    </div>
                </div>
                <div class="general-stat">
                    <div class="general-stat-icon"><i class="fas fa-exclamation-circle"></i></div>
                    <div>
                        <div class="general-stat-label">{{ __('app.blast.invalid_data') }}</div>
                        <div class="general-stat-value">{{ $invalidCount ?? 0 }}</div>
                    </div>
                </div>
            </div>

            <div class="general-toolbar">
                <form method="GET" action="{{ route('admin.blast.recipients.general.index') }}" class="general-filter">
                    <div class="general-field">
                        <label class="general-label">{{ __('app.blast.search') }}</label>
                        <input type="text" name="q" value="{{ $search ?? '' }}" class="general-input" placeholder="{{ __('app.blast.search_general_placeholder') }}">
                    </div>
                    <div class="general-field">
                        <label class="general-label">{{ __('app.blast.table_status') }}</label>
                        <select name="status" class="general-select">
                            <option value="all" @selected(($selectedStatus ?? 'all') === 'all')>{{ __('app.blast.all_statuses') }}</option>
                            <option value="valid" @selected(($selectedStatus ?? 'all') === 'valid')>{{ __('app.blast.valid_upper') }}</option>
                            <option value="invalid" @selected(($selectedStatus ?? 'all') === 'invalid')>{{ __('app.blast.invalid_upper') }}</option>
                        </select>
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
                    <a href="{{ route('admin.blast.recipients.general.index') }}" class="general-btn neutral">
                        {{ __('app.blast.reset') }}
                    </a>
                </form>

                <form id="bulk-delete-general-form" method="POST" action="{{ route('admin.blast.recipients.general.bulk-delete') }}">
                    @csrf
                    @method('DELETE')
                </form>

                <div class="general-import-wrap">
                    <a href="{{ route('admin.blast.recipients.general.create') }}" class="general-btn light">
                        <i class="fas fa-plus"></i> {{ __('app.blast.manual_input') }}
                    </a>
                    <a href="{{ route('admin.blast.recipients.templates.download', 'umum') }}" class="general-btn light">
                        <i class="fas fa-file-download"></i> {{ __('app.blast.download_general_recipient_template') }}
                    </a>
                    <form action="{{ route('admin.blast.recipients.general.import') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <label class="general-btn light general-file-btn">
                            <i class="fas fa-file-import"></i> {{ __('app.blast.import_general_excel') }}
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
                    <button type="submit" class="general-btn danger" id="bulkDeleteGeneralBtn" form="bulk-delete-general-form" disabled>
                        <i class="fas fa-trash-alt"></i> <span class="general-btn-label">{{ __('app.blast.delete_selected') }}</span>
                    </button>
                    <form method="POST" action="{{ route('admin.blast.recipients.general.destroy-all') }}" data-confirm-message="{{ __('app.blast.delete_all_general_confirm') }}" onsubmit="return confirm(this.dataset.confirmMessage)">
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
                                <input type="checkbox" class="general-checkbox" id="selectAllGeneral">
                            </th>
                            <th style="width:56px;">{{ __('app.blast.no') }}</th>
                            <th>{{ __('app.blast.general_name') }}</th>
                            <th>{{ __('app.blast.whatsapp') }}</th>
                            <th>{{ __('app.blast.institution') }}</th>
                            <th>{{ __('app.blast.email') }}</th>
                            <th>{{ __('app.blast.certificate_link') }}</th>
                            <th>{{ __('app.blast.notes') }}</th>
                            <th style="width:150px;">{{ __('app.blast.table_status') }}</th>
                            <th style="width:140px;">{{ __('app.blast.action') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recipients as $recipient)
                            <tr>
                                <td>
                                    <input
                                        type="checkbox"
                                        class="general-checkbox general-recipient-checkbox"
                                        name="selected_ids[]"
                                        value="{{ $recipient->id }}"
                                        form="bulk-delete-general-form"
                                    >
                                </td>
                                <td>{{ ($recipients->currentPage() - 1) * $recipients->perPage() + $loop->iteration }}</td>
                                <td><div class="general-name">{{ $recipient->nama }}</div></td>
                                <td>{{ $recipient->whatsapp ?? '-' }}</td>
                                <td>{{ $recipient->instansi ?? '-' }}</td>
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
                                <td>{!! $recipient->catatan ? nl2br(e($recipient->catatan)) : '<span class="general-muted">-</span>' !!}</td>
                                <td>
                                    @if($recipient->is_valid)
                                        <span class="general-badge valid">{{ __('app.blast.valid_upper') }}</span>
                                    @else
                                        <span class="general-badge invalid">{{ __('app.blast.invalid_upper') }}</span>
                                        @if($recipient->validation_error)
                                            <div class="general-error-detail">{{ $recipient->validation_error }}</div>
                                        @endif
                                    @endif
                                </td>
                                <td>
                                    <div class="general-row-actions">
                                        <a href="{{ route('admin.blast.recipients.general.edit', $recipient->id) }}" class="general-btn light general-icon-btn" title="{{ __('app.blast.edit_recipient') }}" aria-label="{{ __('app.blast.edit_recipient') }}">
                                            <i class="fas fa-pen"></i>
                                        </a>
                                        <form method="POST" action="{{ route('admin.blast.recipients.general.destroy', $recipient->id) }}" data-confirm-message="{{ __('app.blast.delete_general_confirm') }}" onsubmit="return confirm(this.dataset.confirmMessage)">
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
                                <td colspan="10" class="general-empty">
                                    {{ __('app.blast.no_general_recipient_data') }}
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
    const generalText = {
        deleteSelected: @json(__('app.blast.delete_selected')),
        chooseMinOneDelete: @json(__('app.blast.choose_min_one_general_delete')),
        deleteSelectedConfirm: @json(__('app.blast.delete_selected_general_confirm', ['count' => '__COUNT__'])),
    };
    const form = document.getElementById('bulk-delete-general-form');
    const selectAll = document.getElementById('selectAllGeneral');
    const checkboxes = Array.from(document.querySelectorAll('.general-recipient-checkbox'));
    const deleteBtn = document.getElementById('bulkDeleteGeneralBtn');

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
            ? `${generalText.deleteSelected} (${selectedCount})`
            : generalText.deleteSelected;
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

    checkboxes.forEach(cb => cb.addEventListener('change', updateState));

    form.addEventListener('submit', (event) => {
        const selected = checkboxes.filter(cb => cb.checked);
        if (selected.length === 0) {
            event.preventDefault();
            alert(generalText.chooseMinOneDelete);
            return;
        }

        const confirmText = generalText.deleteSelectedConfirm.replace('__COUNT__', selected.length);
        if (!confirm(confirmText)) {
            event.preventDefault();
        }
    });

    updateState();
});
</script>
@endsection
