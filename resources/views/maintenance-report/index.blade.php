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
$maintenanceNotificationRecipient = config('services.maintenance_notification.recipient', 'Ridodwikurniawan@gmail.com');
@endphp

@extends('layouts.app')

@section('content')
@include('shared.modal')
<form class="card">
    <div class="card-header">
        <div class="row justify-content-between align-items-center">
            <div class="col-md-6">
                <span class="card-title">Laporan Pemeliharaan</span>
                <div class="small text-muted mt-2">
                    Notifikasi email maintenance baru dikirim otomatis ke <strong>{{ $maintenanceNotificationRecipient }}</strong>.
                    Jika perlu kirim ulang, buka detail laporan lalu klik <strong>Kirim Notifikasi</strong>.
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
                            <a href="{{ route('assets.detail', $report->asset->id) }}" target="_blank">{{ $report->asset->account_code }}</a>
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
    const maintenanceNotificationRecipient = @json($maintenanceNotificationRecipient);

    function resetState()
    {
        $('#root-checkbox').prop('checked', false);
        $('.child-checkbox').prop('checked', false);
    }

    function constructMaintenanceReportForm(data) 
    {
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
                        <label>PK</label>
                        <input type="text" class="form-control" value="${data.asset.detail.dimension} PK" readonly>
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
                <div class="form-group mb-0">
                    <label>Email Notifikasi</label>
                    <input type="text" class="form-control" value="${maintenanceNotificationRecipient}" readonly>
                    <small class="form-text text-muted">
                        Sistem akan mengirim otomatis saat laporan dibuat, dan bisa dikirim ulang manual dari tombol aksi.
                    </small>
                </div>
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
                const { data } = await Http.get($(this).data('url'));

                const form = constructMaintenanceReportForm(data);
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

        $(document).on('click', '#send-maintenance-report-notification-button', async function() {
            $(this).prop('disabled', true);
            try
            {
                const confirmation = await Notification.confirmation(
                    'Kirim ulang notifikasi maintenance ke ' + maintenanceNotificationRecipient + '?'
                );
                if(!confirmation.isConfirmed)
                    return;

                Loading.show();

                const { message } = await Http.post(
                    "{{ route('maintenance-report.notify', ':id') }}".replace(':id', $(this).data('id'))
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
