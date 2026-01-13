@php 
    use Carbon\Carbon;
    use App\Enums\Asset\AssetCategory;
    use App\Enums\Report\Maintenance\AssetMaintenanceReportStatus;

    $basicAssetInfoFields = [
        [
            'label' => 'Kode Akun',
            'key' => 'accountCode',
        ],
        [
            'label' => 'Nomor Serial',
            'key' => 'serialNumber'
        ],
        [
            'label' => 'Lokasi',
            'key' => 'location'    
        ],
        [
            'label' => 'Tahun Pembelian',
            'key' => 'purchaseYear'    
        ]
    ];

    $assetDetailFields = [
        AssetCategory::AC->value => [
            [
                'label' => 'Brand',
                'key' => 'brand'    
            ],
            [
                'label' => 'Dimensi',
                'key' => 'dimension'
            ],
            [
                'label' => 'Voltase',
                'key' => 'power_rating'    
            ]
        ]
    ];
    $currentAssetDetail = $assetDetailFields[$asset->category->value];

    $chunkedBasicAssetInfoFields = array_chunk($basicAssetInfoFields, 2);
    $chunkedAssetDetailFields = array_chunk($currentAssetDetail, 2);
@endphp

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>{{ env('APP_NAME') }}</title>

  <!-- Google Font: Source Sans Pro -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="{{ asset('vendor/adminlte/plugins/fontawesome-free/css/all.min.css') }}">
  <!-- icheck bootstrap -->
  <link rel="stylesheet" href="{{ asset('vendor/adminlte/plugins/icheck-bootstrap/icheck-bootstrap.min.css') }}">
  <!-- SweetAlert2 -->
  <link rel="stylesheet" href="{{ asset('vendor/adminlte/plugins/sweetalert2-theme-bootstrap-4/bootstrap-4.min.css') }}">
  <!-- Theme style -->
  <link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/adminlte.min.css') }}">
  <!-- Extra CSS -->
  <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body style="background-color: #e9ecef;" class="p-3">
@include('shared.modal')
<div id="loading-overlay">
    <i class="fas fa-2x fa-spinner fa-spin"></i>
</div>
<div class="container mx-auto">
<div class="card card-outline card-primary">
    <div class="card-header d-flex flex-column justify-content-center align-items-center">
        <img class="mb-2" src="{{ asset('images/logo_ypik.webp') }}" alt="logo_ypik" height="100" />
        <h4 class="font-weight-bold">ASET YPIK PAM JAYA</h4>
    </div>
    <!-- /.card-header -->
    <div class="card-body">
        <div class="callout callout-info">
            <h5 class="font-weight-bolder">❗ Informasi Penting</h5>
            <p>Jika Anda merupakan teknisi yang memperbaiki aset, silakan klik <a id="toggle-maintenance-report-form-anchor" href="#" class="text-primary">di sini</a> untuk mengisi formulir.</p>
        </div>

        <div class="card">
            <div class="card-header">
                <span class="card-title font-weight-bolder">I. Informasi Dasar Aset</span>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col">
                        <div class="form-group">
                            <label>Kategori</label>
                            <input type="text" class="form-control" value="{{ $asset->category }}" readonly>
                        </div>
                    </div>
                </div>
                @foreach($chunkedBasicAssetInfoFields as $chunk) 
                    <div class="row">
                        @foreach($chunk as $field)
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>{{ $field['label'] }}</label>
                                    <input type="text" class="form-control" value="{{ data_get($asset, $field['key']) }}" readonly>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endforeach
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <span class="card-title font-weight-bolder">II. Informasi Detail Aset</span>
            </div>
            <div class="card-body">
                @foreach($chunkedAssetDetailFields as $chunk) 
                    <div class="row">
                        @foreach($chunk as $field)
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>{{ $field['label'] }}</label>
                                    <input type="text" class="form-control" value="{{ data_get($asset->detail, $field['key']) }}" readonly>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endforeach
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <span class="card-title font-weight-bolder">III. Riwayat Pemeliharaan</span>
            </div>
            <div class="card-body p-0">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Nama Pekerja</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $approvedMaintenances = collect($asset->maintenanceLogs)
                                ->where('status', AssetMaintenanceReportStatus::APPROVED->value)
                                ->values(); // reset index
                        @endphp

                        @forelse($approvedMaintenances as $index => $maintenance)
                            @php
                                $collapseId = 'maintenance-detail-' . $asset->id . '-' . $index;
                            @endphp

                            {{-- ROW UTAMA --}}
                            <tr>
                                <td>{{ Carbon::parse($maintenance['date'])->format('d M Y') }}</td>
                                <td>{{ $maintenance['worker_name'] }}</td>
                                <td class="text-center">
                                    <button
                                        type="button"
                                        class="btn btn-xs btn-outline-info"
                                        data-toggle="collapse"
                                        data-target="#{{ $collapseId }}"
                                        aria-expanded="false"
                                    >
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </td>
                            </tr>

                            {{-- ROW DETAIL (COLLAPSE) --}}
                            <tr class="collapse" id="{{ $collapseId }}">
                                <td colspan="3">
                                    <div class="p-3 border rounded bg-light">
                                        <strong>Permasalahan:</strong>
                                        <p class="mb-2">{{ $maintenance['issue_description'] }}</p>

                                        <strong>Deskripsi Pekerjaan:</strong>
                                        <p class="mb-0">{{ $maintenance['working_description'] }}</p>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td class="text-center" colspan="3">
                                    Belum ada riwayat pemeliharaan
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<!-- /.card -->
</div>

<!-- jQuery -->
<script src="{{ asset('vendor/adminlte/plugins/jquery/jquery.min.js') }}"></script>
<!-- Bootstrap 4 -->
<script src="{{ asset('vendor/adminlte/plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
<!-- AdminLTE App -->
<script src="{{ asset('vendor/adminlte/dist/js/adminlte.min.js') }}"></script>
<!-- SweetAlert2 -->
<script src="{{ asset('vendor/adminlte/plugins/sweetalert2/sweetalert2.min.js') }}"></script>
<!-- Helpers -->
<script src="{{ asset('js/helper.js') }}"></script>
@if(session()->has('success'))
<script>
    Notification.success("{{ session()->get('success') }}");
</script>
@endif
@stack('component_js')
<script>
    function constructMaintenanceReportForm()
    {
        return `
            <form id='maintenance-form'>
                <div class="form-group">
                    <label for="name">Kode Akun</label>
                    <input type="text" class="form-control" value="{{ $asset->accountCode }}" readonly>
                </div>
                <div class="form-group">
                    <label for="name">Nama Pekerja</label>
                    <input type="text" name="worker_name" class="form-control" placeholder="Masukkan nama Anda" required>
                </div>
                <div class="form-group">
                    <label for="name">Tanggal Pengerjaan</label>
                    <input type="date" name="working_date" class="form-control" placeholder="Pilih tanggal" required>
                </div>
                <div class="form-group">
                    <label for="name">Kondisi / Masalah Aset</label>
                    <textarea name="issue_description" class="form-control" rows='3' required></textarea>
                </div>
                <div class="form-group">
                    <label for="name">Deskripsi Pengerjaan</label>
                    <textarea name="working_description" class="form-control" rows='3' required></textarea>
                </div>
            </form>
        `
    }

    $(function() {
        $('#toggle-maintenance-report-form-anchor').on('click', function() {
            const buttons = `
                <button id="submit-maintenance-report-form-button" type="button" class="btn btn-sm btn-primary" data-asset-id="{{ $asset->id }}">
                    <i class="fas fa-paper-plane"></i> Kirim
                </button>
            `;
            modal.show('Form Laporan Maintenance', constructMaintenanceReportForm(), buttons);
        });

        $(document).on('click', '#submit-maintenance-report-form-button', async function() {
            $(this).prop('disabled', true);
            try 
            {
                const form = document.getElementById('maintenance-form');
                if(!form.checkValidity())
                {
                    form.reportValidity();
                    return;
                }

                const confirmation = await Notification.confirmation('Anda yakin ingin mengirim laporan pemeliharaan? Harap pastikan kembali kode akun dan keterangan pemeliharaan Anda.');
                if(!confirmation.isConfirmed)
                    return;

                Loading.show();

                const formData = new FormData(form);
                formData.append('asset_id', $(this).data('asset-id'));

                await Http.post("{{ route('maintenance-report.submit') }}", formData);
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
</body>
</html>