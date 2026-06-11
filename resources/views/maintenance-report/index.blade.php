@php 
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
        color: var(--app-text);
        word-break: break-word;
    }
    .maintenance-recipient-option-email {
        font-size: .76rem;
        color: var(--app-accent);
        line-height: 1.5;
        word-break: break-word;
    }
    .maintenance-recipient-preview-box {
        min-height: 52px;
    }
    .maintenance-recipient-preview-meta {
        font-size: .78rem;
        color: var(--app-text-muted);
        line-height: 1.6;
    }
</style>
@include('maintenance-report.partials.index-content')
@stop

@section('js')
<script>
    const isUserCanUpdate = "{{ $isUserCanUpdate }}";
    let maintenanceNotificationConfig = @json($maintenanceNotificationConfig);
    const maintenanceExportRoutes = {
        excel: @json(route('maintenance-report.export-excel')),
        pdf: @json(route('maintenance-report.export-pdf')),
    };
    const maintenanceText = {
        noAdditionalEmail: @json(__('app.maintenance.no_additional_email')),
        noDashboardEmail: @json(__('app.maintenance.no_dashboard_email')),
        dashboardEmail: @json(__('app.maintenance.dashboard_email')),
        emailCount: @json(__('app.maintenance.email_count')),
        selectedCount: @json(__('app.maintenance.selected_count')),
        accountCode: @json(__('app.maintenance.account_code')),
        category: @json(__('app.maintenance.category')),
        location: @json(__('app.maintenance.location')),
        dimension: @json(__('app.maintenance.dimension')),
        workerName: @json(__('app.maintenance.worker_name')),
        workerNamePlaceholder: @json(__('app.maintenance.worker_name_placeholder')),
        workingDate: @json(__('app.maintenance.working_date')),
        datePlaceholder: @json(__('app.maintenance.date_placeholder')),
        assetIssue: @json(__('app.maintenance.asset_issue')),
        workDescription: @json(__('app.maintenance.work_description')),
        picName: @json(__('app.maintenance.pic_name')),
        picPlaceholder: @json(__('app.maintenance.pic_placeholder')),
        cost: @json(__('app.maintenance.cost')),
        costPlaceholder: @json(__('app.maintenance.cost_placeholder')),
        evidencePhotos: @json(__('app.maintenance.evidence_photos')),
        masterEmail: @json(__('app.maintenance.master_email')),
        superadminDashboardEmail: @json(__('app.maintenance.superadmin_dashboard_email')),
        selectAll: @json(__('app.maintenance.select_all')),
        clear: @json(__('app.maintenance.clear')),
        masterEmailRequiredNote: @json(__('app.maintenance.master_email_required_note')),
        deliveryRecipients: @json(__('app.maintenance.delivery_recipients')),
        recipientPreviewNote: @json(__('app.maintenance.recipient_preview_note')),
        manualAdditionalEmail: @json(__('app.maintenance.manual_additional_email')),
        manualEmailPlaceholder: @json(__('app.maintenance.manual_email_placeholder')),
        manualEmailHelp: @json(__('app.maintenance.manual_email_help')),
        sendNotification: @json(__('app.maintenance.send_notification')),
        approve: @json(__('app.maintenance.approve')),
        reject: @json(__('app.maintenance.reject')),
        delete: @json(__('app.maintenance.delete')),
        save: @json(__('app.maintenance.save')),
        maintenanceDetailFormTitle: @json(__('app.maintenance.maintenance_detail_form_title')),
        notSelectedReportError: @json(__('app.maintenance.not_selected_report_error')),
        filteredExportNote: @json(__('app.maintenance.filtered_export_note')),
        selectedReportCount: @json(__('app.maintenance.selected_report_count')),
        updateStatusConfirm: @json(__('app.maintenance.update_status_confirm')),
        saveChangesConfirm: @json(__('app.maintenance.save_changes_confirm')),
        deleteReportConfirm: @json(__('app.maintenance.delete_report_confirm')),
        notifyMasterOnlyConfirm: @json(__('app.maintenance.notify_master_only_confirm')),
        notifyWithDashboardManualConfirm: @json(__('app.maintenance.notify_with_dashboard_manual_confirm')),
        notifyWithDashboardConfirm: @json(__('app.maintenance.notify_with_dashboard_confirm')),
        notifyWithManualConfirm: @json(__('app.maintenance.notify_with_manual_confirm')),
        statuses: {
            Pending: @json(__('app.maintenance.statuses.pending')),
            Approved: @json(__('app.maintenance.statuses.approved')),
            Rejected: @json(__('app.maintenance.statuses.rejected')),
        },
    };

    function formatText(template, replacements = {}) {
        return Object.entries(replacements).reduce(
            (text, [key, value]) => text.split(`:${key}`).join(value),
            String(template ?? '')
        );
    }

    function buildMaintenanceExportUrl(baseUrl) {
        const ids = $('.child-checkbox:checked')
            .map((_, el) => el.value)
            .toArray();
        const params = new URLSearchParams();

        if (ids.length > 0) {
            ids.forEach(id => params.append('ids[]', id));
        } else {
            const filterForm = document.getElementById('maintenance-report-filter-form');
            ['date_from', 'date_to', 'status', 'keyword'].forEach((field) => {
                const input = filterForm?.querySelector(`[name="${field}"]`);
                const value = String(input?.value ?? '').trim();

                if (value !== '') {
                    params.append(field, value);
                }
            });
        }

        return params.toString()
            ? `${baseUrl}?${params.toString()}`
            : baseUrl;
    }

    function triggerMaintenanceDownload(url) {
        $('#download-bulk-report-anchor')
            .attr('href', url)[0]
            .click();
    }

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
            return `<span class="badge badge-secondary mr-1 mb-1">${escapeHtml(maintenanceText.noAdditionalEmail)}</span>`;
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
            return `<div class="text-muted small">${escapeHtml(maintenanceText.noDashboardEmail)}</div>`;
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
                            <span class="maintenance-recipient-option-name">${escapeHtml(recipient?.name || maintenanceText.dashboardEmail)}</span>
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
            previewCountBadge.textContent = formatText(maintenanceText.emailCount, { count: allSelectedRecipients.length });
        }

        if (dashboardCountBadge) {
            dashboardCountBadge.textContent = formatText(maintenanceText.selectedCount, { count: selectedDashboardRecipients.length });
        }
    }

    function resetState()
    {
        $('#root-checkbox').prop('checked', false).prop('indeterminate', false);
        $('.child-checkbox').prop('checked', false);
        updateMaintenanceSelectionState();
    }

    function updateMaintenanceSelectionState()
    {
        const totalCheckboxes = $('.child-checkbox').length;
        const selectedCount = $('.child-checkbox:checked').length;
        const rootCheckbox = $('#root-checkbox');

        rootCheckbox
            .prop('checked', totalCheckboxes > 0 && selectedCount === totalCheckboxes)
            .prop('indeterminate', selectedCount > 0 && selectedCount < totalCheckboxes);

        $('#maintenance-selected-count-value').text(selectedCount);
        $('#maintenance-selected-export-note').text(
            selectedCount > 0
                ? formatText(maintenanceText.selectedReportCount, { count: selectedCount })
                : maintenanceText.filteredExportNote
        );
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
                    <label>${maintenanceText.accountCode}</label>
                    <input type="text" class="form-control" value="${data.asset.accountCode}" readonly>
                </div>
                <div class="form-group">
                    <label>${maintenanceText.category}</label>
                    <input type="text" class="form-control" value="${data.asset.category}" readonly>
                </div>
                <div class="form-group">
                    <label>${maintenanceText.location}</label>
                    <input type="text" class="form-control" value="${data.asset.location}" readonly>
                </div>
                ${data.asset.category === 'AC' && `
                    <div class="form-group">
                        <label>${maintenanceText.dimension}</label>
                        <input type="text" class="form-control" value="${data.asset.detail.dimension ?? '-'}" readonly>
                    </div>
                `}
                <div class="form-group">
                    <label for="name">${maintenanceText.workerName}</label>
                    <input type="text" name="worker_name" class="form-control" placeholder="${maintenanceText.workerNamePlaceholder}" value="${data.workerName}" ${!isUserCanUpdate ? 'readonly' : ''} required>
                </div>
                <div class="form-group">
                    <label for="name">${maintenanceText.workingDate}</label>
                    <input type="date" name="working_date" class="form-control" placeholder="${maintenanceText.datePlaceholder}" value="${formatDateForInput(data.workingDate)}" ${!isUserCanUpdate ? 'readonly' : ''} required>
                </div>
                <div class="form-group">
                    <label for="name">${maintenanceText.assetIssue}</label>
                    <textarea name="issue_description" class="form-control" rows='3' ${!isUserCanUpdate ? 'readonly' : ''} required>${data.issueDescription}</textarea>
                </div>
                <div class="form-group">
                    <label for="name">${maintenanceText.workDescription}</label>
                    <textarea name="working_description" class="form-control" rows='3' ${!isUserCanUpdate ? 'readonly' : ''} required>${data.workingDescription}</textarea>
                </div>
                <div class="form-group">
                    <label for="pic">${maintenanceText.picName}</label>
                    <input type="text" name="pic" class="form-control" placeholder="${maintenanceText.picPlaceholder}" value="${data.pic}" ${!isUserCanUpdate ? 'readonly' : ''} required>
                </div>
                <div class="form-group">
                    <label for="cost">${maintenanceText.cost}</label>
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
                                placeholder="${maintenanceText.costPlaceholder}"
                                value="${data.cost}"
                                required
                            >
                        `
                    }
                </div>
                <div class="form-group">
                    <details>
                        <summary class="font-weight-bold">${maintenanceText.evidencePhotos}</summary>
                        ${constructEvidencePhoto()}
                    </details>
                </div>
                <div class="form-group">
                    <label>${maintenanceText.masterEmail}</label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fas fa-lock"></i></span>
                        </div>
                        <input type="text" class="form-control" value="${escapeHtml(masterRecipient)}" readonly>
                    </div>
                </div>
                <div class="form-group">
                    <div class="maintenance-recipient-selection-toolbar">
                        <label class="mb-0">${maintenanceText.superadminDashboardEmail}</label>
                        ${isUserCanUpdate && storedRecipients.length > 0 ? `
                            <div class="maintenance-recipient-selection-actions">
                                <button type="button" class="btn btn-link btn-sm p-0 maintenance-select-all-dashboard-recipients">${maintenanceText.selectAll}</button>
                                <span class="text-muted">•</span>
                                <button type="button" class="btn btn-link btn-sm p-0 maintenance-clear-dashboard-recipients">${maintenanceText.clear}</button>
                                <span class="badge badge-info" id="maintenance-selected-dashboard-count">${formatText(maintenanceText.selectedCount, { count: selectedStoredRecipients.length })}</span>
                            </div>
                        ` : ''}
                    </div>
                    <div class="border rounded p-2" style="min-height:44px;">
                        ${storedRecipientsHtml}
                    </div>
                    <small class="form-text text-muted">
                        ${maintenanceText.masterEmailRequiredNote}
                    </small>
                </div>
                <div class="form-group">
                    <div class="d-flex align-items-center justify-content-between flex-wrap" style="gap:.5rem;">
                        <label class="mb-0">${maintenanceText.deliveryRecipients}</label>
                        <span class="badge badge-primary" id="maintenance-selected-recipient-count">${formatText(maintenanceText.emailCount, { count: 1 + selectedStoredRecipients.length })}</span>
                    </div>
                    <div id="maintenance-selected-recipient-preview" class="border rounded p-2 maintenance-recipient-preview-box" style="min-height:44px;">
                        ${selectedRecipientsHtml}
                    </div>
                    <small class="form-text maintenance-recipient-preview-meta">
                        ${maintenanceText.recipientPreviewNote}
                    </small>
                </div>
                ${isUserCanUpdate ? `
                    <div class="form-group mb-0">
                        <label>${maintenanceText.manualAdditionalEmail}</label>
                        <textarea name="manual_recipients" class="form-control" rows="2" placeholder="${maintenanceText.manualEmailPlaceholder}"></textarea>
                        <small class="form-text text-muted">
                            ${formatText(maintenanceText.manualEmailHelp, { button: `<strong>${maintenanceText.sendNotification}</strong>` })}
                        </small>
                    </div>
                ` : ''}
            </form>
        `;
    }

    $(function() {
        resetState();

        $('#root-checkbox').on('click', function() {
            const checkboxes = $('.child-checkbox');
            checkboxes.prop('checked', this.checked);
            updateMaintenanceSelectionState();
        });

        $(document).on('change', '.child-checkbox', function() {
            updateMaintenanceSelectionState();
        });

        $(document).on('click', '#download-bulk-report-excel-button', async function() {
            triggerMaintenanceDownload(buildMaintenanceExportUrl(maintenanceExportRoutes.excel));
        });

        $(document).on('click', '#download-bulk-report-pdf-button', async function() {
            triggerMaintenanceDownload(buildMaintenanceExportUrl(maintenanceExportRoutes.pdf));
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
                            ${(isStatusPendingOrRejected) ? maintenanceText.approve : maintenanceText.reject}
                        </button>
                    @endpermission
                    @permission('maintenance_report.delete')
                        <button id="delete-maintenance-report-button" type="button" class="btn btn-sm btn-danger" data-id="${data.id}">
                            <i class="fas fa-trash-alt"></i>
                            ${maintenanceText.delete}
                        </button>
                    @endpermission
                    @permission('maintenance_report.update')
                        <button id="send-maintenance-report-notification-button" type="button" class="btn btn-sm btn-warning" data-id="${data.id}">
                            <i class="fas fa-envelope"></i>
                            ${maintenanceText.sendNotification}
                        </button>
                    @endpermission
                    @permission('maintenance_report.update')
                        <button id="update-maintenance-report-button" type="button" class="btn btn-sm btn-primary" data-id="${data.id}">
                            <i class="fas fa-save"></i>
                            ${maintenanceText.save}
                        </button>
                    @endpermission
                `;

                modal.show(maintenanceText.maintenanceDetailFormTitle, form, buttons);
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

                const confirmation = await Notification.confirmation(
                    formatText(maintenanceText.updateStatusConfirm, { status: maintenanceText.statuses[status] ?? status })
                );
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

                const confirmation = await Notification.confirmation(maintenanceText.saveChangesConfirm);
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
                let confirmationMessage = maintenanceText.notifyMasterOnlyConfirm;

                if (selectedDashboardRecipientCount > 0 && manualRecipientList.length > 0) {
                    confirmationMessage = formatText(maintenanceText.notifyWithDashboardManualConfirm, { count: selectedDashboardRecipientCount });
                } else if (selectedDashboardRecipientCount > 0) {
                    confirmationMessage = formatText(maintenanceText.notifyWithDashboardConfirm, { count: selectedDashboardRecipientCount });
                } else if (manualRecipientList.length > 0) {
                    confirmationMessage = maintenanceText.notifyWithManualConfirm;
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
                const confirmation = await Notification.confirmation(maintenanceText.deleteReportConfirm);
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
