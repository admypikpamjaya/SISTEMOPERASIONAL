@extends('layouts.app')

@section('title', __('app.blast.general_recipient_title'))

@section('content')
<style>
@import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');

:root {
    --gen-teal-900: #134e4a;
    --gen-teal-800: #0f766e;
    --gen-teal-700: #14b8a6;
    --gen-teal-100: #ccfbf1;
    --gen-teal-50: #f0fdfa;
    --gen-text-900: #0f172a;
    --gen-text-700: #334155;
    --gen-text-500: #64748b;
    --gen-border: #dbe4f0;
}

.general-page {
    font-family: 'Plus Jakarta Sans', sans-serif;
    color: var(--gen-text-900);
    padding: 4px 2px 16px;
}

.general-head {
    border-radius: 14px;
    padding: 20px 22px;
    margin-bottom: 14px;
    background: linear-gradient(135deg, var(--gen-teal-900) 0%, var(--gen-teal-800) 62%, var(--gen-teal-700) 100%);
    box-shadow: 0 12px 24px rgba(20, 184, 166, .22);
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    flex-wrap: wrap;
    gap: 12px;
}

.general-head-title {
    font-size: 20px;
    font-weight: 800;
    color: #fff;
    margin-bottom: 4px;
}

.general-head-sub {
    font-size: 12px;
    color: rgba(255, 255, 255, .88);
}

.general-head-actions,
.general-import-wrap,
.general-filter {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
}

.general-btn {
    border-radius: 8px;
    border: 1px solid transparent;
    font-size: 12px;
    font-weight: 700;
    line-height: 1.2;
    padding: 8px 11px;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    transition: .15s;
}

.general-btn:hover {
    transform: translateY(-1px);
}

.general-btn.ghost {
    color: #fff;
    border-color: rgba(255, 255, 255, .38);
    background: rgba(255, 255, 255, .1);
}

.general-btn.primary {
    background: linear-gradient(135deg, #0f766e, #14b8a6);
    color: #fff;
}

.general-btn.light {
    background: #fff;
    border-color: #99f6e4;
    color: #0f766e;
}

.general-btn.danger {
    background: #fee2e2;
    border-color: #fecaca;
    color: #b91c1c;
}

.general-btn.danger:hover {
    background: #b91c1c;
    color: #fff;
    border-color: #b91c1c;
}

.general-btn:disabled {
    opacity: .6;
    cursor: not-allowed;
    transform: none;
}

.general-panel {
    border: 1px solid var(--gen-border);
    border-radius: 14px;
    background: #fff;
    box-shadow: 0 8px 18px rgba(15, 23, 42, .06);
    overflow: hidden;
}

.general-panel-body {
    padding: 16px;
}

.general-alert {
    border-radius: 10px;
    padding: 10px 12px;
    font-size: 12.5px;
    font-weight: 600;
    margin-bottom: 12px;
}

.general-alert.success {
    border: 1px solid #86efac;
    background: #f0fdf4;
    color: #166534;
}

.general-alert.error {
    border: 1px solid #fecaca;
    background: #fef2f2;
    color: #991b1b;
}

.general-stats {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 10px;
    margin-bottom: 12px;
}

.general-stat {
    border: 1px solid var(--gen-border);
    border-radius: 12px;
    background: var(--gen-teal-50);
    padding: 12px;
}

.general-stat-label {
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .04em;
    color: var(--gen-text-500);
    margin-bottom: 4px;
}

.general-stat-value {
    font-size: 26px;
    font-weight: 800;
    color: var(--gen-teal-800);
    line-height: 1;
}

.general-toolbar {
    border: 1px solid var(--gen-border);
    border-radius: 12px;
    background: var(--gen-teal-50);
    padding: 12px;
    margin-bottom: 12px;
    display: flex;
    flex-wrap: wrap;
    align-items: flex-end;
    justify-content: space-between;
    gap: 10px;
}

.general-field {
    min-width: 170px;
}

.general-label {
    display: block;
    margin-bottom: 5px;
    font-size: 12px;
    font-weight: 700;
    color: var(--gen-text-700);
}

.general-input,
.general-select {
    width: 100%;
    border: 1px solid var(--gen-border);
    border-radius: 8px;
    background: #fff;
    color: var(--gen-text-900);
    font-size: 12.5px;
    font-family: inherit;
    height: 36px;
    padding: 0 10px;
}

.general-input:focus,
.general-select:focus {
    outline: none;
    border-color: #14b8a6;
    box-shadow: 0 0 0 3px rgba(20, 184, 166, .14);
}

.general-file-btn {
    cursor: pointer;
}

.general-table-wrap {
    border: 1px solid var(--gen-border);
    border-radius: 12px;
    overflow: auto;
}

.general-table {
    width: 100%;
    border-collapse: collapse;
    min-width: 820px;
}

.general-table th {
    background: #f8fbff;
    color: var(--gen-text-500);
    text-transform: uppercase;
    letter-spacing: .04em;
    font-size: 10.5px;
    font-weight: 800;
    padding: 10px 12px;
    border-bottom: 1px solid var(--gen-border);
    white-space: nowrap;
}

.general-table td {
    padding: 11px 12px;
    border-bottom: 1px solid #eef3fb;
    font-size: 12.5px;
    color: var(--gen-text-700);
    vertical-align: top;
}

.general-table tr:last-child td {
    border-bottom: none;
}

.general-table tr:hover td {
    background: #f8fbff;
}

.general-checkbox {
    width: 16px;
    height: 16px;
    accent-color: var(--gen-teal-800);
    cursor: pointer;
}

.general-name {
    font-weight: 700;
    color: var(--gen-text-900);
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
    background: #dcfce7;
    color: #166534;
}

.general-badge.invalid {
    background: #fee2e2;
    color: #991b1b;
}

.general-error-detail {
    margin-top: 3px;
    font-size: 11px;
    color: var(--gen-text-500);
}

.general-empty {
    text-align: center;
    color: var(--gen-text-500);
    padding: 22px 12px;
}

@media (max-width: 900px) {
    .general-stats {
        grid-template-columns: 1fr;
    }

    .general-field {
        min-width: 100%;
    }
}
</style>

<div class="general-page">
    <div class="general-head">
        <div>
            <div class="general-head-title">{{ __('app.blast.general_recipient_title') }}</div>
            <div class="general-head-sub">{{ __('app.blast.general_recipient_subtitle') }}</div>
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
                    <div class="general-stat-label">{{ __('app.blast.total_general_recipients') }}</div>
                    <div class="general-stat-value">{{ $totalRecipients ?? $recipients->total() }}</div>
                </div>
                <div class="general-stat">
                    <div class="general-stat-label">{{ __('app.blast.valid_data') }}</div>
                    <div class="general-stat-value">{{ $validCount ?? 0 }}</div>
                </div>
                <div class="general-stat">
                    <div class="general-stat-label">{{ __('app.blast.invalid_data') }}</div>
                    <div class="general-stat-value">{{ $invalidCount ?? 0 }}</div>
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
                    <div style="min-width:120px;">
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
                    <a href="{{ route('admin.blast.recipients.general.index') }}" class="general-btn light">
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
                        <i class="fas fa-trash-alt"></i> {{ __('app.blast.delete_selected') }}
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
                                <td>{{ $recipient->catatan ?? '-' }}</td>
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
                                    <div style="display:flex;gap:6px;">
                                        <a href="{{ route('admin.blast.recipients.general.edit', $recipient->id) }}" class="general-btn light" style="padding:6px 9px;" title="{{ __('app.blast.edit_recipient') }}" aria-label="{{ __('app.blast.edit_recipient') }}">
                                            <i class="fas fa-pen"></i>
                                        </a>
                                        <form method="POST" action="{{ route('admin.blast.recipients.general.destroy', $recipient->id) }}" data-confirm-message="{{ __('app.blast.delete_general_confirm') }}" onsubmit="return confirm(this.dataset.confirmMessage)">
                                            @csrf
                                            @method('DELETE')
                                            <button class="general-btn danger" type="submit" style="padding:6px 9px;" title="{{ __('app.blast.delete_recipient') }}" aria-label="{{ __('app.blast.delete_recipient') }}">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="general-empty">
                                    {{ __('app.blast.no_general_recipient_data') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
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

        deleteBtn.disabled = selectedCount === 0;
        deleteBtn.textContent = selectedCount > 0
            ? `${generalText.deleteSelected} (${selectedCount})`
            : generalText.deleteSelected;

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
