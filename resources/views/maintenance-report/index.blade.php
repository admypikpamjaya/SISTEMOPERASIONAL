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
@endphp

@extends('layouts.app')

@section('content')
@include('shared.modal')
<form class="card">
    <div class="card-header">
        <div class="row justify-content-between align-items-center">
            <div class="col-md-6">
                <span class="card-title">Laporan Pemeliharaan</span>
            </div>
            <div class="col-md-6">
                <div class="row align-items-center">
                    <div class="col">
                        <div class="input-group input-group-sm">
                            <select name="category" id="filter-category-select" class="form-control">
                                <option value="">Semua Status</option>
                                @foreach (AssetMaintenanceReportStatus::cases() as $status)
                                    <option value="{{ $status->value }}" {{ request('status') == $status->value ? 'selected' : '' }}>{{ $status->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col">
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
                </div>
            </div>
        </div> 
    </div>
    <div class="card-body p-0">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th scope="col">#</th>
                    <th scope="col">KODE ASET</th>
                    <th scope="col">STATUS</th>
                </tr>
            </thead>
            <tbody>
                @forelse($reports as $report)
                    <tr>
                        <th scope="row">{{ $loop->iteration }}</th>
                        <td class="text-left">
                            <a href="{{ route('assets.detail', $report->asset->id) }}">{{ $report->asset->account_code }}</a>
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

    function constructMaintenanceReportForm(data) 
    {
        return `
            <form id="maintenance-report">
                <div class="form-group">
                    <label for="name">Kode Akun</label>
                    <input type="text" class="form-control" value="${data.asset.accountCode}" readonly>
                </div>
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
            </form>
        `;
    }

    $(function() {
        console.log(isUserCanUpdate);
        $('#toggle-maintenance-report-detail-button').on('click', async function() {
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