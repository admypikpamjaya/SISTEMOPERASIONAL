@php 
use Carbon\Carbon;
use App\Enums\Portal\PortalPermission;
use App\Services\AccessControl\PermissionService;
use App\Enums\Report\Maintenance\AssetMaintenanceReportStatus;

$badgeMap = [
    AssetMaintenanceReportStatus::PENDING->value  => 'warning',
    AssetMaintenanceReportStatus::APPROVED->value => 'success',
    AssetMaintenanceReportStatus::REJECTED->value => 'danger',
];

$isUserCanUpdate = app(PermissionService::class)->checkAccess(auth()->user(), PortalPermission::MAINTENANCE_REPORT_UPDATE->value);
$maintenanceNotificationConfig = $notificationRecipients ?? [
    'master' => \App\Services\Report\MaintenanceNotificationService::MASTER_RECIPIENT,
    'stored' => [],
    'all' => [\App\Services\Report\MaintenanceNotificationService::MASTER_RECIPIENT],
    'allDisplay' => \App\Services\Report\MaintenanceNotificationService::MASTER_RECIPIENT,
    'additionalCount' => 0,
    'totalCount' => 1,
];
$maintenanceNotificationMasterRecipient = (string) data_get(
    $maintenanceNotificationConfig,
    'master',
    \App\Services\Report\MaintenanceNotificationService::MASTER_RECIPIENT
);
$maintenanceNotificationAdditionalCount = (int) data_get($maintenanceNotificationConfig, 'additionalCount', 0);
@endphp

@extends('layouts.app')

@section('content')
@include('shared.modal')
<style>
    .maintenance-recipient-selection-toolbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: .75rem;
        flex-wrap: wrap;
        margin-bottom: .5rem;
    }
    .maintenance-recipient-selection-actions {
        display: inline-flex;
        align-items: center;
        gap: .5rem;
        font-size: .82rem;
    }
    .maintenance-recipient-selection-actions .btn {
        font-size: .78rem;
        font-weight: 600;
        text-decoration: none;
    }
    .maintenance-recipient-option-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: .65rem;
    }
    .maintenance-recipient-option {
        display: flex;
        align-items: flex-start;
        gap: .7rem;
        padding: .85rem .9rem;
        border: 1px solid rgba(96, 165, 250, 0.22);
        border-radius: 12px;
        background: rgba(37, 99, 235, 0.08);
        cursor: pointer;
        transition: border-color .2s ease, background .2s ease, transform .2s ease;
        margin: 0;
    }
    .maintenance-recipient-option:hover {
        border-color: rgba(96, 165, 250, 0.38);
        background: rgba(37, 99, 235, 0.14);
        transform: translateY(-1px);
    }
    .maintenance-recipient-option input {
        margin-top: .2rem;
        transform: scale(1.06);
        accent-color: #2563eb;
    }
    .maintenance-recipient-option-copy {
        display: flex;
        flex-direction: column;
        gap: .15rem;
        min-width: 0;
    }
    .maintenance-recipient-option-name {
        font-size: .84rem;
        font-weight: 700;
        line-height: 1.45;
        color: #e2e8f0;
        word-break: break-word;
    }
    .maintenance-recipient-option-email {
        font-size: .76rem;
        color: #93c5fd;
        line-height: 1.5;
        word-break: break-word;
    }
    .maintenance-recipient-preview-box {
        min-height: 52px;
    }
    .maintenance-recipient-preview-meta {
        font-size: .78rem;
        color: #94a3b8;
        line-height: 1.6;
    }
</style>
<form class="card">
    <div class="card-header">
        <div class="row justify-content-between align-items-center">
            <div class="col-md-6">
                <span class="card-title">Laporan Pemeliharaan</span>
                <div class="small text-muted mt-2">
                    Notifikasi email maintenance baru dikirim otomatis ke email master <strong>{{ $maintenanceNotificationMasterRecipient }}</strong>
                    @if($maintenanceNotificationAdditionalCount > 0)
                        dan <strong>{{ $maintenanceNotificationAdditionalCount }}</strong> email tambahan dari dashboard superadmin.
                    @else
                        dan saat ini belum ada email tambahan dari dashboard superadmin.
                    @endif
                    Jika perlu kirim ulang, buka detail laporan lalu klik <strong>Kirim Notifikasi</strong>; dari sana Anda bisa memilih email dashboard yang ikut dikirim dan tetap bisa menambahkan email manual.
                </div>
            </div>
            <div class="col-md-6">
                <div class="row align-items-center">
                    <div class="col">
                        <div class="d-flex align-items-center input-group input-group-sm">
                            <input 
                                type="date" 
                                name="date_from" 
                                value="{{ request('date_from') ?? Carbon::now()->toDateString() }}" 
                                class="form-control mr-2"
                            />
                            <span class="mr-2">s/d</span>
                            <input 
                                type="date" 
                                name="date_to" 
                                value="{{ request('date_to') ?? Carbon::now()->toDateString() }}" 
                                class="form-control"
                            />
                        </div>
                    </div>
                    <div class="col-2">
                        <div class="input-group input-group-sm">
                            <select name="status" id="filter-status-select" class="form-control">
                                <option value="">Semua Status</option>
                                @foreach (AssetMaintenanceReportStatus::cases() as $status)
                                    <option value="{{ $status->value }}" {{ request('status') == $status->value ? 'selected' : '' }}>{{ $status->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-3">
                        <div class="input-group input-group-sm">
                            <input 
                                type="text" 
                                name="keyword" 
                                value="{{ request('keyword') }}" 
                                class="form-control float-right" 
                                placeholder="Cari laporan..."
                            />

                            <div class="input-group-append">
                            <button type="submit" class="btn btn-default">
                                <i class="fas fa-search"></i>
                            </button>
                            </div>
                        </div>
                    </div>
                    <div class="col-1">
                        <div class="d-flex justify-content-around">
                            <a id="download-bulk-report-anchor" href="#" class="d-none"></a>
                            <button id="download-bulk-report-button" type="button" class="btn btn-sm btn-primary" title="Download Laporan Pemeliharaan">
                                <i class="fas fa-file-excel"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div> 
    </div>
    <div class="card-body p-0">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th scope="col">
                        <input id="root-checkbox" type="checkbox">
                    </th>
                    <th scope="col">#</th>
                    <th scope="col">KODE ASET</th>
                    <th scope="col">PIC</th>
                    <th scope="col">STATUS</th>
                </tr>
            </thead>
            <tbody>
                @forelse($reports as $report)
                    <tr>
                        <td><input class="child-checkbox" type="checkbox" value="{{ $report->id }}"></td>
                        <th scope="row">{{ $loop->iteration }}</th>
                        <td class="text-left">
                            <a href="{{ \App\Support\AssetPublicUrl::detailUrl((string) $report->asset->id) }}" target="_blank">{{ $report->asset->account_code }}</a>
                        </td>
                        <td>
                            <span>
                                {{ $report->pic }}
                            </span>
                        </td>
                        <td>
                            <span class="badge badge-{{ $badgeMap[$report->status->value] ?? 'secondary' }}">
                                {{ $report->status }}
                            </span>
                        </td>
                        <td>
                            <button id="toggle-maintenance-report-detail-button" type="button" class="btn btn-sm btn-outline-info" data-url="{{ route('maintenance-report.detail', $report->id) }}">
                                <div class="fas fa-eye"></div>
                            </button>
                            <a href="{{ route('maintenance-report.export-excel', ['ids' => [$report->id]]) }}" class="btn btn-sm btn-outline-success">
                                <i class="fas fa-file-excel"></i>
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center">Tidak ada data laporan</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer">
        {{ $reports->links() }}
    </div>
</form>
@stop

@section('js')
<script>
    const isUserCanUpdate = "{{ $isUserCanUpdate }}";
    let maintenanceNotificationConfig = @json($maintenanceNotificationConfig);

    function escapeHtml(value) {
        return String(value ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function renderMaintenanceRecipientBadges(recipients, formatter = (recipient) => recipient) {
        if (!Array.isArray(recipients) || recipients.length === 0) {
            return '<span class="badge badge-secondary mr-1 mb-1">Belum ada email tambahan</span>';
        }

        return recipients
            .map((recipient) => `
                <span class="badge badge-info mr-1 mb-1" style="font-size:.78rem;">
                    <i class="fas fa-envelope mr-1"></i>${escapeHtml(formatter(recipient))}
                </span>
            `)
            .join('');
    }

    function isValidMaintenanceEmail(value) {
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(String(value ?? '').trim());
    }

    function normalizeUniqueMaintenanceRecipients(recipients) {
        const uniqueRecipients = new Map();

        (Array.isArray(recipients) ? recipients : []).forEach((recipient) => {
            const value = String(recipient ?? '').trim();
            if (value === '' || !isValidMaintenanceEmail(value)) {
                return;
            }

            const key = value.toLowerCase();
            if (!uniqueRecipients.has(key)) {
                uniqueRecipients.set(key, value);
            }
        });

        return Array.from(uniqueRecipients.values());
    }

    function parseMaintenanceManualRecipients(rawValue) {
        const value = String(rawValue ?? '').trim();
        if (value === '') {
            return [];
        }

        return normalizeUniqueMaintenanceRecipients(
            value.split(/[\s,;\r\n]+/)
        );
    }

    function renderMaintenanceDashboardRecipientOptions(recipients, selectedIds, selectable = true) {
        if (!Array.isArray(recipients) || recipients.length === 0) {
            return '<div class="text-muted small">Belum ada email dashboard tambahan yang aktif.</div>';
        }

        const selectedRecipientIds = new Set(
            Array.isArray(selectedIds) ? selectedIds.map((recipientId) => String(recipientId)) : []
        );

        return `
            <div class="maintenance-recipient-option-grid">
                ${recipients.map((recipient) => `
                    <label class="maintenance-recipient-option">
                        <input
                            type="checkbox"
                            name="selected_dashboard_recipient_ids[]"
                            value="${escapeHtml(recipient?.id ?? '')}"
                            class="maintenance-dashboard-recipient-checkbox"
                            ${selectedRecipientIds.has(String(recipient?.id ?? '')) ? 'checked' : ''}
                            ${selectable ? '' : 'disabled'}
                        >
                        <span class="maintenance-recipient-option-copy">
                            <span class="maintenance-recipient-option-name">${escapeHtml(recipient?.name || 'Email Dashboard')}</span>
                            <span class="maintenance-recipient-option-email">${escapeHtml(recipient?.email || '-')}</span>
                        </span>
                    </label>
                `).join('')}
            </div>
        `;
    }

    function getSelectedDashboardRecipients(reportForm, recipientConfig) {
        if (!reportForm) {
            return [];
        }

        const notificationConfig = recipientConfig && typeof recipientConfig === 'object'
            ? recipientConfig
            : maintenanceNotificationConfig;
        const storedRecipients = Array.isArray(notificationConfig?.stored)
            ? notificationConfig.stored
            : [];
        const selectedRecipientIds = new Set(
            Array.from(reportForm.querySelectorAll('.maintenance-dashboard-recipient-checkbox:checked'))
                .map((checkbox) => String(checkbox.value))
        );

        return storedRecipients.filter((recipient) => selectedRecipientIds.has(String(recipient?.id ?? '')));
    }

    function updateMaintenanceRecipientSelectionPreview(reportForm, recipientConfig) {
        if (!reportForm) {
            return;
        }

        const notificationConfig = recipientConfig && typeof recipientConfig === 'object'
            ? recipientConfig
            : maintenanceNotificationConfig;
        const masterRecipient = String(notificationConfig?.master ?? '').trim();
        const selectedDashboardRecipients = getSelectedDashboardRecipients(reportForm, notificationConfig);
        const manualRecipients = parseMaintenanceManualRecipients(
            new FormData(reportForm).get('manual_recipients') ?? ''
        );
        const selectedDashboardEmails = selectedDashboardRecipients
            .map((recipient) => String(recipient?.email ?? '').trim())
            .filter((recipient) => recipient !== '');
        const allSelectedRecipients = normalizeUniqueMaintenanceRecipients([
            masterRecipient,
            ...selectedDashboardEmails,
            ...manualRecipients,
        ]);

        const previewContainer = reportForm.querySelector('#maintenance-selected-recipient-preview');
        const previewCountBadge = reportForm.querySelector('#maintenance-selected-recipient-count');
        const dashboardCountBadge = reportForm.querySelector('#maintenance-selected-dashboard-count');

        if (previewContainer) {
            previewContainer.innerHTML = renderMaintenanceRecipientBadges(allSelectedRecipients);
        }

        if (previewCountBadge) {
            previewCountBadge.textContent = `${allSelectedRecipients.length} email`;
        }

        if (dashboardCountBadge) {
            dashboardCountBadge.textContent = `${selectedDashboardRecipients.length} dipilih`;
        }
    }

    function resetState()
    {
        $('#root-checkbox').prop('checked', false);
        $('.child-checkbox').prop('checked', false);
    }

    function constructMaintenanceReportForm(data, recipientConfig) 
    {
        const notificationConfig = recipientConfig && typeof recipientConfig === 'object'
            ? recipientConfig
            : maintenanceNotificationConfig;
        const masterRecipient = notificationConfig?.master ?? '';
        const storedRecipients = Array.isArray(notificationConfig?.stored)
            ? notificationConfig.stored
            : [];
        const selectedStoredRecipientIds = Array.isArray(notificationConfig?.selectedStoredIds)
            ? notificationConfig.selectedStoredIds
            : storedRecipients.map((recipient) => recipient?.id).filter((recipientId) => !!recipientId);
        const selectedStoredRecipients = storedRecipients.filter(
            (recipient) => selectedStoredRecipientIds.includes(recipient?.id)
        );
        const storedRecipientsHtml = renderMaintenanceDashboardRecipientOptions(
            storedRecipients,
            selectedStoredRecipientIds,
            Boolean(isUserCanUpdate)
        );
        const selectedRecipientsHtml = renderMaintenanceRecipientBadges([
            masterRecipient,
            ...selectedStoredRecipients.map((recipient) => recipient?.email ?? '')
        ]);

        const constructEvidencePhoto = () => {
            let html = '';
            data.evidencePhotos.forEach((photo, index) => {
                html += `
                    <img src="${photo}" alt="evidence-${index}" class="img-fluid">
                `
            });

            return html;
        }

        return `
            <form id="maintenance-report">
                <div class="form-group">
                    <label>Kode Akun</label>
                    <input type="text" class="form-control" value="${data.asset.accountCode}" readonly>
                </div>
                <div class="form-group">
                    <label>Kategori</label>
                    <input type="text" class="form-control" value="${data.asset.category}" readonly>
                </div>
                <div class="form-group">
                    <label>Lokasi</label>
                    <input type="text" class="form-control" value="${data.asset.location}" readonly>
                </div>
                ${data.asset.category === 'AC' && `
                    <div class="form-group">
                        <label>Ukuran / Dimensi</label>
                        <input type="text" class="form-control" value="${data.asset.detail.dimension ?? '-'}" readonly>
                    </div>
                `}
                <div class="form-group">
                    <label for="name">Nama Pekerja</label>
                    <input type="text" name="worker_name" class="form-control" placeholder="Masukkan nama pekerja" value="${data.workerName}" ${!isUserCanUpdate ? 'readonly' : ''} required>
                </div>
                <div class="form-group">
                    <label for="name">Tanggal Pengerjaan</label>
                    <input type="date" name="working_date" class="form-control" placeholder="Pilih tanggal" value="${formatDateForInput(data.workingDate)}" ${!isUserCanUpdate ? 'readonly' : ''} required>
                </div>
                <div class="form-group">
                    <label for="name">Kondisi / Masalah Aset</label>
                    <textarea name="issue_description" class="form-control" rows='3' ${!isUserCanUpdate ? 'readonly' : ''} required>${data.issueDescription}</textarea>
                </div>
                <div class="form-group">
                    <label for="name">Deskripsi Pengerjaan</label>
                    <textarea name="working_description" class="form-control" rows='3' ${!isUserCanUpdate ? 'readonly' : ''} required>${data.workingDescription}</textarea>
                </div>
                <div class="form-group">
                    <label for="pic">Nama PIC (Pemanggil Pekerja)</label>
                    <input type="text" name="pic" class="form-control" placeholder="Masukkan nama PIC / pemanggil pekerja" value="${data.pic}" ${!isUserCanUpdate ? 'readonly' : ''} required>
                </div>
                <div class="form-group">
                    <label for="cost">Biaya</label>
                    ${!isUserCanUpdate 
                        ? `
                            <input
                                type="text"
                                class="form-control"
                                value="${data.costFormatted}"
                                readonly
                            >
                        `
                        : `
                            <input
                                type="number"
                                name="cost"
                                min="0"
                                step="0.01"
                                class="form-control"
                                placeholder="Masukkan biaya"
                                value="${data.cost}"
                                required
                            >
                        `
                    }
                </div>
                <div class="form-group">
                    <details>
                        <summary class="font-weight-bold">Gambar Dokumentasi Pengerjaan</summary>
                        ${constructEvidencePhoto()}
                    </details>
                </div>
                <div class="form-group">
                    <label>Email Master</label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fas fa-lock"></i></span>
                        </div>
                        <input type="text" class="form-control" value="${escapeHtml(masterRecipient)}" readonly>
                    </div>
                </div>
                <div class="form-group">
                    <div class="maintenance-recipient-selection-toolbar">
                        <label class="mb-0">Email Dashboard Superadmin</label>
                        ${isUserCanUpdate && storedRecipients.length > 0 ? `
                            <div class="maintenance-recipient-selection-actions">
                                <button type="button" class="btn btn-link btn-sm p-0 maintenance-select-all-dashboard-recipients">Pilih semua</button>
                                <span class="text-muted">•</span>
                                <button type="button" class="btn btn-link btn-sm p-0 maintenance-clear-dashboard-recipients">Kosongkan</button>
                                <span class="badge badge-info" id="maintenance-selected-dashboard-count">${selectedStoredRecipients.length} dipilih</span>
                            </div>
                        ` : ''}
                    </div>
                    <div class="border rounded p-2" style="min-height:44px;">
                        ${storedRecipientsHtml}
                    </div>
                    <small class="form-text text-muted">
                        Email master tetap wajib terkirim. Dari daftar dashboard di atas, Anda bisa memilih siapa saja yang ikut dikirim untuk pengiriman kali ini.
                    </small>
                </div>
                <div class="form-group">
                    <div class="d-flex align-items-center justify-content-between flex-wrap" style="gap:.5rem;">
                        <label class="mb-0">Tujuan Yang Akan Dikirim</label>
                        <span class="badge badge-primary" id="maintenance-selected-recipient-count">${1 + selectedStoredRecipients.length} email</span>
                    </div>
                    <div id="maintenance-selected-recipient-preview" class="border rounded p-2 maintenance-recipient-preview-box" style="min-height:44px;">
                        ${selectedRecipientsHtml}
                    </div>
                    <small class="form-text maintenance-recipient-preview-meta">
                        Preview ini akan berubah saat Anda memilih email dashboard atau menambahkan email manual tambahan.
                    </small>
                </div>
                ${isUserCanUpdate ? `
                    <div class="form-group mb-0">
                        <label>Email Manual Tambahan</label>
                        <textarea name="manual_recipients" class="form-control" rows="2" placeholder="Pisahkan dengan koma, enter, atau titik koma"></textarea>
                        <small class="form-text text-muted">
                            Kolom ini hanya dipakai sekali saat klik <strong>Kirim Notifikasi</strong>, tidak disimpan ke dashboard, dan email duplikat akan diabaikan otomatis.
                        </small>
                    </div>
                ` : ''}
            </form>
        `;
    }

    $(function() {
        resetState();

        $('#filter-status-select').on('change', function() {
            $(this).closest('form').submit(); 
        });

        $('input[name="date_from"], input[name="date_to"]').on('change', function() {
            $(this).closest('form').submit();
        });

        $('#root-checkbox').on('click', function() {
            const checkboxes = $('.child-checkbox');
            checkboxes.prop('checked', this.checked);
        });

        $(document).on('click', '#download-bulk-report-button', async function() {
            const ids = $('.child-checkbox:checked')
                .map((_, el) => el.value)
                .toArray();

            if(ids.length === 0)
                return Notification.error('Anda belum memilih laporan');

            const baseUrl = "{{ route('maintenance-report.export-excel') }}";
            const params = new URLSearchParams();

            ids.forEach(id => params.append('ids[]', id));

            const url = params.toString()
                ? `${baseUrl}?${params.toString()}`
                : baseUrl;

            $('#download-bulk-report-anchor')
                .attr('href', url)[0]
                .click();
        });

        $(document).on('click', '#toggle-maintenance-report-detail-button', async function() {
            Loading.show();
            try 
            {
                const response = await Http.get($(this).data('url'));
                const data = response.data;
                maintenanceNotificationConfig = response.notificationRecipients || maintenanceNotificationConfig;

                const form = constructMaintenanceReportForm(data, maintenanceNotificationConfig);
                const isStatusPendingOrRejected = (data.status === 'Pending' || data.status === 'Rejected');
                const buttons = `
                    @permission('maintenance_report.update_status')
                        <button id="update-maintenance-report-status-button" type="button" class="btn btn-sm ${(isStatusPendingOrRejected) ? 'btn-success' : 'btn-danger'}" data-status="${(isStatusPendingOrRejected) ? 'Approved' : 'Rejected'}" data-id="${data.id}">
                            <i class="fas fa-check-circle"></i>
                            ${(isStatusPendingOrRejected) ? 'Approve' : 'Reject'}
                        </button>
                    @endpermission
                    @permission('maintenance_report.delete')
                        <button id="delete-maintenance-report-button" type="button" class="btn btn-sm btn-danger" data-id="${data.id}">
                            <i class="fas fa-trash-alt"></i>
                            Hapus
                        </button>
                    @endpermission
                    @permission('maintenance_report.update')
                        <button id="send-maintenance-report-notification-button" type="button" class="btn btn-sm btn-warning" data-id="${data.id}">
                            <i class="fas fa-envelope"></i>
                            Kirim Notifikasi
                        </button>
                    @endpermission
                    @permission('maintenance_report.update')
                        <button id="update-maintenance-report-button" type="button" class="btn btn-sm btn-primary" data-id="${data.id}">
                            <i class="fas fa-save"></i>
                            Simpan
                        </button>
                    @endpermission
                `;

                modal.show('Form Detail Laporan Pemeliharaan', form, buttons);
                updateMaintenanceRecipientSelectionPreview(
                    document.getElementById('maintenance-report'),
                    maintenanceNotificationConfig
                );
            }
            catch(error)
            {
                Notification.error(error);
            }
            finally
            {
                Loading.hide();
            }
        });

        $(document).on('click', '#update-maintenance-report-status-button', async function() {
            $(this).prop('disabled', true);
            try 
            {
                const id = $(this).data('id');
                const status = $(this).data('status');

                const confirmation = await Notification.confirmation('Anda yakin ingin mengubah status laporan menjadi ' + status + '?');
                if(!confirmation.isConfirmed)
                    return;

                Loading.show();

                const formData = new FormData();
                formData.append('id', id);
                formData.append('status', status);
                formData.append('_method', 'PUT');

                await Http.post("{{ route('maintenance-report.update-status') }}", formData);
                refreshUI();
            }
            catch(error)
            {
                Notification.error(error);
            }
            finally
            {
                Loading.hide();
                $(this).prop('disabled', false);
            }
        });

        $(document).on('click', '#update-maintenance-report-button', async function() {
            $(this).prop('disabled', true);
            try 
            {
                const form = document.getElementById('maintenance-report');
                if(!form.checkValidity())
                {
                    form.reportValidity();
                    return;
                }

                const confirmation = await Notification.confirmation('Anda yakin ingin menyimpan perubahan?');
                if(!confirmation.isConfirmed)
                    return;

                Loading.show();

                const formData = new FormData(form);
                formData.append('id', $(this).data('id'));
                formData.append('_method', 'PUT');

                await Http.post("{{ route('maintenance-report.update') }}", formData);
                refreshUI();
            }
            catch(error)
            {
                Notification.error(error);
            }
            finally
            {
                Loading.hide();
                $(this).prop('disabled', false);
            }
        });

        $(document).on('change', '.maintenance-dashboard-recipient-checkbox', function() {
            updateMaintenanceRecipientSelectionPreview(
                document.getElementById('maintenance-report'),
                maintenanceNotificationConfig
            );
        });

        $(document).on('input', '#maintenance-report textarea[name="manual_recipients"]', function() {
            updateMaintenanceRecipientSelectionPreview(
                document.getElementById('maintenance-report'),
                maintenanceNotificationConfig
            );
        });

        $(document).on('click', '.maintenance-select-all-dashboard-recipients', function() {
            const reportForm = document.getElementById('maintenance-report');
            if (!reportForm) {
                return;
            }

            $(reportForm)
                .find('.maintenance-dashboard-recipient-checkbox')
                .prop('checked', true);

            updateMaintenanceRecipientSelectionPreview(reportForm, maintenanceNotificationConfig);
        });

        $(document).on('click', '.maintenance-clear-dashboard-recipients', function() {
            const reportForm = document.getElementById('maintenance-report');
            if (!reportForm) {
                return;
            }

            $(reportForm)
                .find('.maintenance-dashboard-recipient-checkbox')
                .prop('checked', false);

            updateMaintenanceRecipientSelectionPreview(reportForm, maintenanceNotificationConfig);
        });

        $(document).on('click', '#send-maintenance-report-notification-button', async function() {
            $(this).prop('disabled', true);
            try
            {
                const reportForm = document.getElementById('maintenance-report');
                const selectedDashboardRecipientIds = reportForm
                    ? Array.from(reportForm.querySelectorAll('.maintenance-dashboard-recipient-checkbox:checked'))
                        .map((checkbox) => String(checkbox.value))
                    : [];
                const manualRecipients = reportForm
                    ? String(new FormData(reportForm).get('manual_recipients') ?? '').trim()
                    : '';
                const manualRecipientList = parseMaintenanceManualRecipients(manualRecipients);
                const selectedDashboardRecipientCount = selectedDashboardRecipientIds.length;
                let confirmationMessage = 'Kirim ulang notifikasi maintenance ke email master saja?';

                if (selectedDashboardRecipientCount > 0 && manualRecipientList.length > 0) {
                    confirmationMessage = `Kirim ulang notifikasi maintenance ke email master, ${selectedDashboardRecipientCount} email dashboard terpilih, dan email manual tambahan?`;
                } else if (selectedDashboardRecipientCount > 0) {
                    confirmationMessage = `Kirim ulang notifikasi maintenance ke email master dan ${selectedDashboardRecipientCount} email dashboard terpilih?`;
                } else if (manualRecipientList.length > 0) {
                    confirmationMessage = 'Kirim ulang notifikasi maintenance ke email master dan email manual tambahan?';
                }

                const confirmation = await Notification.confirmation(confirmationMessage);
                if(!confirmation.isConfirmed)
                    return;

                Loading.show();

                const formData = new FormData();
                selectedDashboardRecipientIds.forEach((recipientId) => {
                    formData.append('selected_dashboard_recipient_ids[]', recipientId);
                });

                if (manualRecipients !== '') {
                    formData.append('manual_recipients', manualRecipients);
                }

                const { message } = await Http.post(
                    "{{ route('maintenance-report.notify', ':id') }}".replace(':id', $(this).data('id')),
                    formData
                );

                Notification.success(message);
            }
            catch(error)
            {
                Notification.error(error);
            }
            finally
            {
                Loading.hide();
                $(this).prop('disabled', false);
            }
        });

        $(document).on('click', '#delete-maintenance-report-button', async function() {
            $(this).prop('disabled', true);
            try 
            {
                const confirmation = await Notification.confirmation('Anda yakin ingin menghapus laporan ini?');
                if(!confirmation.isConfirmed)
                    return;

                Loading.show();

                await Http.delete("{{ route('maintenance-report.delete', ':id') }}".replace(':id', $(this).data('id')));
                refreshUI();
            }
            catch(error)
            {
                Notification.error(error);
            }
            finally
            {
                Loading.hide();
                $(this).prop('disabled', false);
            }
        });
    });
</script>
@stop
