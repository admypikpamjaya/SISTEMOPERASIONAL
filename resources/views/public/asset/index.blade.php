@php
    use App\Enums\Asset\AssetCategory;
    use App\Enums\Asset\ComputerComponent;
    use App\Enums\Report\Maintenance\AssetMaintenanceReportStatus;

    $basicAssetInfoFields = [
        [
            'label' => __('app.asset.account_code'),
            'value' => $asset->accountCode,
        ],
        [
            'label' => __('app.asset.serial_number'),
            'value' => $asset->serialNumber ?: '-',
        ],
        [
            'label' => __('app.asset.unit'),
            'value' => $asset->unit?->name ?? '-',
        ],
        [
            'label' => __('app.maintenance.location'),
            'value' => $asset->location,
        ],
        [
            'label' => __('app.asset.purchase_year'),
            'value' => $asset->purchaseYear ?: '-',
        ],
        [
            'label' => __('app.asset.purchase_price'),
            'value' => $asset->purchasePrice !== null
                ? 'Rp ' . number_format((float) $asset->purchasePrice, 2, ',', '.')
                : '-',
        ],
    ];

    $assetDetailFields = [
        AssetCategory::AC->value => [
            [
                'label' => __('app.asset.ac_fields.brand'),
                'value' => data_get($asset->detail, 'brand') ?: '-',
            ],
            [
                'label' => __('app.asset.ac_fields.dimension'),
                'value' => data_get($asset->detail, 'dimension') ?: '-',
            ],
            [
                'label' => __('app.asset.ac_fields.power_rating'),
                'value' => data_get($asset->detail, 'power_rating') ?: '-',
            ],
        ],
        AssetCategory::OTHER->value => [
            [
                'label' => __('app.asset.ac_fields.brand'),
                'value' => data_get($asset->detail, 'brand') ?: '-',
            ],
            [
                'label' => __('app.asset.ac_fields.dimension'),
                'value' => data_get($asset->detail, 'dimension') ?: '-',
            ],
            [
                'label' => __('app.asset.ac_fields.power_rating'),
                'value' => data_get($asset->detail, 'power_rating') ?: '-',
            ],
        ],
        AssetCategory::VEHICLE->value => collect([
            'vehicle_type',
            'vehicle_name',
            'brand',
            'model_type',
            'vehicle_year',
            'color',
            'license_plate',
            'chassis_number',
            'engine_number',
            'bpkb_name',
            'stnk_valid_until',
            'tax_valid_until',
            'kilometer',
            'acquisition_date',
            'asset_account_code',
            'useful_life_years',
            'accumulated_depreciation',
            'book_value',
            'pic',
            'condition',
            'status',
            'notes',
            'source_data',
        ])->map(fn (string $field): array => [
            'label' => __('app.asset.vehicle_fields.' . $field),
            'value' => data_get($asset->detail, $field) ?: '-',
        ])->all(),
        AssetCategory::ELECTRONIC->value => collect([
            'asset_code',
            'electronic_type',
            'asset_name',
            'brand',
            'model_type',
            'specification',
            'serial_number',
            'acquisition_date',
            'asset_account_code',
            'useful_life_years',
            'accumulated_depreciation',
            'book_value',
            'condition',
            'status',
            'pic',
            'notes',
            'source_data',
        ])->map(fn (string $field): array => [
            'label' => __('app.asset.electronic_fields.' . $field),
            'value' => data_get($asset->detail, $field) ?: '-',
        ])->all(),
        AssetCategory::ROOM_INVENTORY->value => collect([
            'asset_code',
            'item_type',
            'item_name',
            'material',
            'size',
            'quantity',
            'acquisition_date',
            'unit_price',
            'asset_account_code',
            'useful_life_years',
            'accumulated_depreciation',
            'book_value',
            'condition',
            'status',
            'notes',
            'source_data',
        ])->map(fn (string $field): array => [
            'label' => __('app.asset.room_inventory_fields.' . $field),
            'value' => data_get($asset->detail, $field) ?: '-',
        ])->all(),
        AssetCategory::BUILDING_INFRASTRUCTURE->value => collect([
            'asset_code',
            'asset_name',
            'asset_type',
            'land_area',
            'building_area',
            'volume_size',
            'document_number',
            'acquisition_date',
            'asset_account_code',
            'useful_life_years',
            'initial_accumulated_depreciation',
            'current_year_depreciation',
            'accumulated_depreciation',
            'book_value',
            'condition',
            'status',
            'responsible_person',
            'notes',
            'source_data',
        ])->map(fn (string $field): array => [
            'label' => __('app.asset.building_infrastructure_fields.' . $field),
            'value' => data_get($asset->detail, $field) ?: '-',
        ])->all(),
    ];

    $currentAssetDetail = $assetDetailFields[$asset->category->value] ?? [];
    $groupedComponents = collect($asset->detail ?? [])
        ->keyBy('component_type');
    $approvedMaintenances = collect($asset->maintenanceLogs)
        ->where('status', AssetMaintenanceReportStatus::APPROVED)
        ->values();
    $appCssVersion = file_exists(public_path('css/app.css')) ? filemtime(public_path('css/app.css')) : time();
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>{{ env('APP_NAME') }}</title>

  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap">
  <link rel="stylesheet" href="{{ asset('vendor/adminlte/plugins/fontawesome-free/css/all.min.css') }}">
  <link rel="stylesheet" href="{{ asset('vendor/adminlte/plugins/icheck-bootstrap/icheck-bootstrap.min.css') }}">
  <link rel="stylesheet" href="{{ asset('vendor/adminlte/plugins/sweetalert2-theme-bootstrap-4/bootstrap-4.min.css') }}">
  <link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/adminlte.min.css') }}">
  <link rel="stylesheet" href="{{ asset('css/app.css') }}?v={{ $appCssVersion }}">
</head>
<body class="asset-public-body">
@include('shared.modal')
<div id="loading-overlay">
    <i class="fas fa-2x fa-spinner fa-spin"></i>
</div>

<main class="asset-public-wrap">
    <section class="asset-public-hero">
        <div class="asset-public-main">
            <div class="asset-public-brand">
                <img src="{{ asset('images/logo_ypik.webp') }}" alt="logo_ypik">
                <div>
                    <h1 class="asset-public-title">{{ __('app.maintenance.public_asset_title') }}</h1>
                    <p class="asset-public-subtitle">{{ __('app.asset.public_asset_subtitle') }}</p>
                </div>
            </div>

            <div class="asset-mini-stats">
                <div class="asset-mini-stat">
                    <span class="asset-mini-label">{{ __('app.asset.category') }}</span>
                    <span class="asset-mini-value">{{ $asset->category->label() }}</span>
                </div>
                <div class="asset-mini-stat">
                    <span class="asset-mini-label">{{ __('app.asset.account_code') }}</span>
                    <span class="asset-mini-value">{{ $asset->accountCode }}</span>
                </div>
                <div class="asset-mini-stat">
                    <span class="asset-mini-label">{{ __('app.asset.unit') }}</span>
                    <span class="asset-mini-value">{{ $asset->unit?->name ?? '-' }}</span>
                </div>
            </div>
        </div>

        <aside class="asset-public-action">
            <div>
                <span class="asset-public-action-icon">
                    <i class="fas fa-tools"></i>
                </span>
                <h2 class="asset-public-action-title">{{ __('app.asset.submit_maintenance_report') }}</h2>
                <p class="asset-public-action-text">{{ __('app.asset.submit_maintenance_desc') }}</p>
            </div>

            <button id="toggle-maintenance-report-form-anchor" type="button" class="asset-action-btn is-primary">
                <i class="fas fa-paper-plane"></i>
                <span>{{ __('app.asset.submit_maintenance_report') }}</span>
            </button>
        </aside>
    </section>

    <section class="asset-panel mb-3">
        <div class="asset-panel-head">
            <h3 class="asset-panel-title">
                <i class="fas fa-id-card"></i>
                <span>{{ __('app.maintenance.basic_asset_info') }}</span>
            </h3>
            <span class="asset-panel-note">{{ __('app.asset.master_data') }}</span>
        </div>
        <div class="asset-panel-body">
            <div class="asset-readonly-grid">
                @foreach($basicAssetInfoFields as $field)
                    <div class="asset-readonly-field">
                        <span class="asset-readonly-label">{{ $field['label'] }}</span>
                        <span class="asset-readonly-value">{{ $field['value'] }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <section class="asset-panel mb-3">
        <div class="asset-panel-head">
            <h3 class="asset-panel-title">
                <i class="fas fa-microchip"></i>
                <span>{{ __('app.maintenance.asset_detail_info') }}</span>
            </h3>
            <span class="asset-panel-note">{{ $asset->category->label() }}</span>
        </div>
        <div class="asset-panel-body">
            @if($asset->category->value === AssetCategory::COMPUTER->value)
                <div class="asset-component-grid">
                    @foreach(ComputerComponent::cases() as $componentEnum)
                        @php
                            $component = $groupedComponents->get($componentEnum->value);
                            $hasComponentData = $component && (
                                !empty($component['brand']) ||
                                !empty($component['specification']) ||
                                !empty($component['serial_number'])
                            );
                        @endphp

                        <section class="asset-component-card">
                            <h4 class="asset-component-title">
                                <i class="fas fa-microchip"></i>
                                <span>{{ $componentEnum->label() }}</span>
                            </h4>

                            @if(!$hasComponentData)
                                <div class="text-muted">{{ __('app.maintenance.no_data') }}</div>
                            @else
                                <div class="asset-readonly-grid">
                                    <div class="asset-readonly-field">
                                        <span class="asset-readonly-label">{{ __('app.asset.brand') }}</span>
                                        <span class="asset-readonly-value">{{ $component['brand'] ?: '-' }}</span>
                                    </div>
                                    <div class="asset-readonly-field">
                                        <span class="asset-readonly-label">{{ __('app.asset.specification') }}</span>
                                        <span class="asset-readonly-value">{{ $component['specification'] ?: '-' }}</span>
                                    </div>
                                    <div class="asset-readonly-field">
                                        <span class="asset-readonly-label">{{ __('app.asset.serial_number') }}</span>
                                        <span class="asset-readonly-value">{{ $component['serial_number'] ?: '-' }}</span>
                                    </div>
                                </div>
                            @endif
                        </section>
                    @endforeach
                </div>
            @elseif(empty($currentAssetDetail))
                <div class="asset-detail-placeholder">
                    <i class="fas fa-info-circle"></i>
                    <div>
                        <strong>{{ __('app.asset.no_detail_title') }}</strong>
                        <span>{{ __('app.asset.no_detail_text') }}</span>
                    </div>
                </div>
            @else
                <div class="asset-readonly-grid">
                    @foreach($currentAssetDetail as $field)
                        <div class="asset-readonly-field">
                            <span class="asset-readonly-label">{{ $field['label'] }}</span>
                            <span class="asset-readonly-value">{{ $field['value'] }}</span>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </section>

    <section class="asset-panel">
        <div class="asset-panel-head">
            <h3 class="asset-panel-title">
                <i class="fas fa-clock-rotate-left"></i>
                <span>{{ __('app.asset.approved_history') }}</span>
            </h3>
            <span class="asset-panel-note">{{ __('app.asset.history_count', ['count' => $approvedMaintenances->count()]) }}</span>
        </div>
        <div class="asset-panel-body p-0">
            <div class="table-responsive">
                <table class="table table-hover app-table-compact mb-0">
                    <thead>
                        <tr>
                            <th>{{ __('app.maintenance.date') }}</th>
                            <th>{{ __('app.maintenance.worker_name') }}</th>
                            <th>{{ __('app.maintenance.pic_upper') }}</th>
                            <th class="text-center">{{ __('app.asset.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($approvedMaintenances as $index => $maintenance)
                            @php
                                $collapseId = 'maintenance-detail-' . $asset->id . '-' . $index;
                            @endphp

                            <tr>
                                <td>{{ $maintenance->workingDate->format('d M Y') }}</td>
                                <td>{{ $maintenance->workerName }}</td>
                                <td>{{ $maintenance->pic }}</td>
                                <td class="text-center">
                                    <button
                                        type="button"
                                        class="app-icon-btn is-info"
                                        data-toggle="collapse"
                                        data-target="#{{ $collapseId }}"
                                        aria-expanded="false"
                                        title="{{ __('app.asset.history_detail') }}"
                                        aria-label="{{ __('app.asset.history_detail') }}"
                                    >
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </td>
                            </tr>

                            <tr class="collapse" id="{{ $collapseId }}">
                                <td colspan="4">
                                    <div class="asset-history-detail">
                                        <div class="asset-readonly-grid">
                                            <div class="asset-readonly-field">
                                                <span class="asset-readonly-label">{{ __('app.maintenance.issue') }}</span>
                                                <span class="asset-readonly-value">{{ $maintenance->issueDescription }}</span>
                                            </div>
                                            <div class="asset-readonly-field">
                                                <span class="asset-readonly-label">{{ __('app.maintenance.work_description') }}</span>
                                                <span class="asset-readonly-value">{{ $maintenance->workingDescription }}</span>
                                            </div>
                                            <div class="asset-readonly-field">
                                                <span class="asset-readonly-label">{{ __('app.maintenance.cost') }}</span>
                                                <span class="asset-readonly-value">{{ $maintenance->costFormatted }}</span>
                                            </div>
                                        </div>

                                        @if(!empty($maintenance->evidencePhotos))
                                            <details class="mt-3">
                                                <summary class="font-weight-bold">{{ __('app.asset.evidence') }}</summary>
                                                <div class="asset-evidence-grid">
                                                    @foreach($maintenance->evidencePhotos as $photo)
                                                        <img src="{{ $photo }}" alt="dokumentasi pengerjaan" loading="lazy">
                                                    @endforeach
                                                </div>
                                            </details>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td class="text-center" colspan="4">
                                    <div class="asset-empty-state">
                                        <i class="fas fa-inbox"></i>
                                        <h4>{{ __('app.maintenance.no_history') }}</h4>
                                        <p>{{ __('app.asset.no_history_note') }}</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </section>
</main>

<script src="{{ asset('vendor/adminlte/plugins/jquery/jquery.min.js') }}"></script>
<script src="{{ asset('vendor/adminlte/plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('vendor/adminlte/dist/js/adminlte.min.js') }}"></script>
<script src="{{ asset('vendor/adminlte/plugins/sweetalert2/sweetalert2.min.js') }}"></script>
<script src="{{ asset('js/helper.js') }}"></script>
@if(session()->has('success'))
<script>
    Notification.success("{{ session()->get('success') }}");
</script>
@endif
@stack('component_js')
<script>
    const maintenanceText = {
        accountCode: @json(__('app.maintenance.account_code')),
        workerName: @json(__('app.maintenance.worker_name')),
        yourNamePlaceholder: @json(__('app.maintenance.your_name_placeholder')),
        workingDate: @json(__('app.maintenance.working_date')),
        datePlaceholder: @json(__('app.maintenance.date_placeholder')),
        assetIssue: @json(__('app.maintenance.asset_issue')),
        workDescription: @json(__('app.maintenance.work_description')),
        picName: @json(__('app.maintenance.pic_name')),
        picPlaceholder: @json(__('app.maintenance.pic_placeholder')),
        cost: @json(__('app.maintenance.cost')),
        optionalCostPlaceholder: @json(__('app.maintenance.optional_cost_placeholder')),
        optionalCostHelp: @json(__('app.maintenance.optional_cost_help')),
        evidencePhotos: @json(__('app.maintenance.evidence_photos')),
        chooseImage: @json(__('app.maintenance.choose_image')),
        chooseImageLabel: @json(__('app.maintenance.choose_image_label')),
        uploadPhotoHelp: @json(__('app.maintenance.upload_photo_help')),
        send: @json(__('app.maintenance.send')),
        maintenanceFormTitle: @json(__('app.maintenance.maintenance_form_title')),
        submitConfirm: @json(__('app.maintenance.submit_confirm')),
    };

    function constructMaintenanceReportForm()
    {
        return `
            <form id='maintenance-form'>
                <div class="form-group">
                    <label for="name">${maintenanceText.accountCode}</label>
                    <input type="text" class="form-control" value="{{ $asset->accountCode }}" readonly>
                </div>
                <div class="form-group">
                    <label for="name">${maintenanceText.workerName}</label>
                    <input type="text" name="worker_name" class="form-control" placeholder="${maintenanceText.yourNamePlaceholder}" required>
                </div>
                <div class="form-group">
                    <label for="name">${maintenanceText.workingDate}</label>
                    <input type="date" name="working_date" class="form-control" placeholder="${maintenanceText.datePlaceholder}" required>
                </div>
                <div class="form-group">
                    <label for="name">${maintenanceText.assetIssue}</label>
                    <textarea name="issue_description" class="form-control" rows='3' required></textarea>
                </div>
                <div class="form-group">
                    <label for="name">${maintenanceText.workDescription}</label>
                    <textarea name="working_description" class="form-control" rows='3' required></textarea>
                </div>
                <div class="form-group">
                    <label for="pic">${maintenanceText.picName}</label>
                    <input type="text" name="pic" class="form-control" placeholder="${maintenanceText.picPlaceholder}" required>
                </div>
                <div class="form-group">
                    <label for="cost">${maintenanceText.cost}</label>
                    <input type="number" name="cost" min="0" step="0.01" class="form-control" placeholder="${maintenanceText.optionalCostPlaceholder}">
                    <small class="form-text text-muted">
                        ${maintenanceText.optionalCostHelp}
                    </small>
                </div>
                <div class="form-group">
                    <label for="evidence-photo-input" class="font-weight-bold">
                        ${maintenanceText.evidencePhotos}
                    </label>

                    <div class="custom-file">
                        <input
                            type="file"
                            id="evidence-photo-input"
                            class="custom-file-input"
                            name="evidence_photo"
                            accept=".jpg,.jpeg,.png,.webp"
                            required
                        >
                        <label class="custom-file-label" for="evidence-photo-input">
                            ${maintenanceText.chooseImageLabel}
                        </label>
                    </div>

                    <small class="form-text text-muted">
                        ${maintenanceText.uploadPhotoHelp}
                    </small>
                </div>
            </form>
        `;
    }

    $(function() {
        $('#toggle-maintenance-report-form-anchor').on('click', function() {
            const buttons = `
                <button id="submit-maintenance-report-form-button" type="button" class="btn btn-sm btn-primary" data-asset-id="{{ $asset->id }}">
                    <i class="fas fa-paper-plane"></i> ${maintenanceText.send}
                </button>
            `;
            modal.show(maintenanceText.maintenanceFormTitle, constructMaintenanceReportForm(), buttons);
        });

        $(document).on('change', '#evidence-photo-input', function(e) {
            const fileName = e.target.files[0]?.name || maintenanceText.chooseImage;
            e.target.nextElementSibling.innerText = fileName;
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

                const confirmation = await Notification.confirmation(maintenanceText.submitConfirm);
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
