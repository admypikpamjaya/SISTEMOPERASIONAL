@extends('layouts.app')

@php
use App\Enums\Asset\AssetCategory;
use App\Enums\Asset\AssetUnit;
use App\Enums\Asset\ComputerComponent;

$groupedComponents = collect($asset->detail)
    ->keyBy('component_type');

$assetCategoryLabel = $asset->category?->label() ?? $asset->category->value;
$assetPriceLabel = $asset->purchasePrice !== null
    ? 'Rp ' . number_format((float) $asset->purchasePrice, 2, ',', '.')
    : '-';
$vehicleDetailFields = [
    ['label' => __('app.asset.vehicle_fields.vehicle_type'), 'name' => 'vehicle_type', 'type' => 'text'],
    ['label' => __('app.asset.vehicle_fields.vehicle_name'), 'name' => 'vehicle_name', 'type' => 'text'],
    ['label' => __('app.asset.vehicle_fields.brand'), 'name' => 'brand', 'type' => 'text'],
    ['label' => __('app.asset.vehicle_fields.model_type'), 'name' => 'model_type', 'type' => 'text'],
    ['label' => __('app.asset.vehicle_fields.vehicle_year'), 'name' => 'vehicle_year', 'type' => 'text'],
    ['label' => __('app.asset.vehicle_fields.color'), 'name' => 'color', 'type' => 'text'],
    ['label' => __('app.asset.vehicle_fields.license_plate'), 'name' => 'license_plate', 'type' => 'text'],
    ['label' => __('app.asset.vehicle_fields.chassis_number'), 'name' => 'chassis_number', 'type' => 'text'],
    ['label' => __('app.asset.vehicle_fields.engine_number'), 'name' => 'engine_number', 'type' => 'text'],
    ['label' => __('app.asset.vehicle_fields.bpkb_name'), 'name' => 'bpkb_name', 'type' => 'text'],
    ['label' => __('app.asset.vehicle_fields.stnk_valid_until'), 'name' => 'stnk_valid_until', 'type' => 'date'],
    ['label' => __('app.asset.vehicle_fields.tax_valid_until'), 'name' => 'tax_valid_until', 'type' => 'date'],
    ['label' => __('app.asset.vehicle_fields.kilometer'), 'name' => 'kilometer', 'type' => 'number', 'min' => '0', 'step' => '1'],
    ['label' => __('app.asset.vehicle_fields.acquisition_date'), 'name' => 'acquisition_date', 'type' => 'date'],
    ['label' => __('app.asset.vehicle_fields.asset_account_code'), 'name' => 'asset_account_code', 'type' => 'text'],
    ['label' => __('app.asset.vehicle_fields.useful_life_years'), 'name' => 'useful_life_years', 'type' => 'number', 'min' => '0', 'step' => '1'],
    ['label' => __('app.asset.vehicle_fields.accumulated_depreciation'), 'name' => 'accumulated_depreciation', 'type' => 'number', 'min' => '0', 'step' => '0.01'],
    ['label' => __('app.asset.vehicle_fields.book_value'), 'name' => 'book_value', 'type' => 'number', 'min' => '0', 'step' => '0.01'],
    ['label' => __('app.asset.vehicle_fields.pic'), 'name' => 'pic', 'type' => 'text'],
    ['label' => __('app.asset.vehicle_fields.condition'), 'name' => 'condition', 'type' => 'text'],
    ['label' => __('app.asset.vehicle_fields.status'), 'name' => 'status', 'type' => 'text'],
    ['label' => __('app.asset.vehicle_fields.notes'), 'name' => 'notes', 'type' => 'text'],
    ['label' => __('app.asset.vehicle_fields.source_data'), 'name' => 'source_data', 'type' => 'text'],
];
$electronicDetailFields = [
    ['label' => __('app.asset.electronic_fields.asset_code'), 'name' => 'asset_code', 'type' => 'text'],
    ['label' => __('app.asset.electronic_fields.electronic_type'), 'name' => 'electronic_type', 'type' => 'text'],
    ['label' => __('app.asset.electronic_fields.asset_name'), 'name' => 'asset_name', 'type' => 'text'],
    ['label' => __('app.asset.electronic_fields.brand'), 'name' => 'brand', 'type' => 'text'],
    ['label' => __('app.asset.electronic_fields.model_type'), 'name' => 'model_type', 'type' => 'text'],
    ['label' => __('app.asset.electronic_fields.specification'), 'name' => 'specification', 'type' => 'text'],
    ['label' => __('app.asset.electronic_fields.serial_number'), 'name' => 'serial_number', 'type' => 'text'],
    ['label' => __('app.asset.electronic_fields.acquisition_date'), 'name' => 'acquisition_date', 'type' => 'date'],
    ['label' => __('app.asset.electronic_fields.asset_account_code'), 'name' => 'asset_account_code', 'type' => 'text'],
    ['label' => __('app.asset.electronic_fields.useful_life_years'), 'name' => 'useful_life_years', 'type' => 'number', 'min' => '0', 'step' => '1'],
    ['label' => __('app.asset.electronic_fields.accumulated_depreciation'), 'name' => 'accumulated_depreciation', 'type' => 'number', 'min' => '0', 'step' => '0.01'],
    ['label' => __('app.asset.electronic_fields.book_value'), 'name' => 'book_value', 'type' => 'number', 'min' => '0', 'step' => '0.01'],
    ['label' => __('app.asset.electronic_fields.condition'), 'name' => 'condition', 'type' => 'text'],
    ['label' => __('app.asset.electronic_fields.status'), 'name' => 'status', 'type' => 'text'],
    ['label' => __('app.asset.electronic_fields.pic'), 'name' => 'pic', 'type' => 'text'],
    ['label' => __('app.asset.electronic_fields.notes'), 'name' => 'notes', 'type' => 'text'],
    ['label' => __('app.asset.electronic_fields.source_data'), 'name' => 'source_data', 'type' => 'text'],
];
$roomInventoryDetailFields = [
    ['label' => __('app.asset.room_inventory_fields.asset_code'), 'name' => 'asset_code', 'type' => 'text'],
    ['label' => __('app.asset.room_inventory_fields.item_type'), 'name' => 'item_type', 'type' => 'text'],
    ['label' => __('app.asset.room_inventory_fields.item_name'), 'name' => 'item_name', 'type' => 'text'],
    ['label' => __('app.asset.room_inventory_fields.material'), 'name' => 'material', 'type' => 'text'],
    ['label' => __('app.asset.room_inventory_fields.size'), 'name' => 'size', 'type' => 'text'],
    ['label' => __('app.asset.room_inventory_fields.quantity'), 'name' => 'quantity', 'type' => 'text'],
    ['label' => __('app.asset.room_inventory_fields.acquisition_date'), 'name' => 'acquisition_date', 'type' => 'date'],
    ['label' => __('app.asset.room_inventory_fields.unit_price'), 'name' => 'unit_price', 'type' => 'number', 'min' => '0', 'step' => '0.01'],
    ['label' => __('app.asset.room_inventory_fields.asset_account_code'), 'name' => 'asset_account_code', 'type' => 'text'],
    ['label' => __('app.asset.room_inventory_fields.useful_life_years'), 'name' => 'useful_life_years', 'type' => 'number', 'min' => '0', 'step' => '1'],
    ['label' => __('app.asset.room_inventory_fields.accumulated_depreciation'), 'name' => 'accumulated_depreciation', 'type' => 'number', 'min' => '0', 'step' => '0.01'],
    ['label' => __('app.asset.room_inventory_fields.book_value'), 'name' => 'book_value', 'type' => 'number', 'min' => '0', 'step' => '0.01'],
    ['label' => __('app.asset.room_inventory_fields.condition'), 'name' => 'condition', 'type' => 'text'],
    ['label' => __('app.asset.room_inventory_fields.status'), 'name' => 'status', 'type' => 'text'],
    ['label' => __('app.asset.room_inventory_fields.notes'), 'name' => 'notes', 'type' => 'text'],
    ['label' => __('app.asset.room_inventory_fields.source_data'), 'name' => 'source_data', 'type' => 'text'],
];
$buildingInfrastructureDetailFields = [
    ['label' => __('app.asset.building_infrastructure_fields.asset_code'), 'name' => 'asset_code', 'type' => 'text'],
    ['label' => __('app.asset.building_infrastructure_fields.asset_name'), 'name' => 'asset_name', 'type' => 'text'],
    ['label' => __('app.asset.building_infrastructure_fields.asset_type'), 'name' => 'asset_type', 'type' => 'text'],
    ['label' => __('app.asset.building_infrastructure_fields.land_area'), 'name' => 'land_area', 'type' => 'text'],
    ['label' => __('app.asset.building_infrastructure_fields.building_area'), 'name' => 'building_area', 'type' => 'text'],
    ['label' => __('app.asset.building_infrastructure_fields.volume_size'), 'name' => 'volume_size', 'type' => 'text'],
    ['label' => __('app.asset.building_infrastructure_fields.document_number'), 'name' => 'document_number', 'type' => 'text'],
    ['label' => __('app.asset.building_infrastructure_fields.acquisition_date'), 'name' => 'acquisition_date', 'type' => 'date'],
    ['label' => __('app.asset.building_infrastructure_fields.asset_account_code'), 'name' => 'asset_account_code', 'type' => 'text'],
    ['label' => __('app.asset.building_infrastructure_fields.useful_life_years'), 'name' => 'useful_life_years', 'type' => 'number', 'min' => '0', 'step' => '1'],
    ['label' => __('app.asset.building_infrastructure_fields.initial_accumulated_depreciation'), 'name' => 'initial_accumulated_depreciation', 'type' => 'number', 'min' => '0', 'step' => '0.01'],
    ['label' => __('app.asset.building_infrastructure_fields.current_year_depreciation'), 'name' => 'current_year_depreciation', 'type' => 'number', 'min' => '0', 'step' => '0.01'],
    ['label' => __('app.asset.building_infrastructure_fields.accumulated_depreciation'), 'name' => 'accumulated_depreciation', 'type' => 'number', 'min' => '0', 'step' => '0.01'],
    ['label' => __('app.asset.building_infrastructure_fields.book_value'), 'name' => 'book_value', 'type' => 'number', 'min' => '0', 'step' => '0.01'],
    ['label' => __('app.asset.building_infrastructure_fields.condition'), 'name' => 'condition', 'type' => 'text'],
    ['label' => __('app.asset.building_infrastructure_fields.status'), 'name' => 'status', 'type' => 'text'],
    ['label' => __('app.asset.building_infrastructure_fields.responsible_person'), 'name' => 'responsible_person', 'type' => 'text'],
    ['label' => __('app.asset.building_infrastructure_fields.notes'), 'name' => 'notes', 'type' => 'text'],
    ['label' => __('app.asset.building_infrastructure_fields.source_data'), 'name' => 'source_data', 'type' => 'text'],
];
@endphp

@section('section_name', __('app.asset.edit_title'))

@section('content')
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap">

<div class="asset-shell asset-page">
    <div class="asset-page-head">
        <div class="asset-page-title-group">
            <span class="asset-page-icon">
                <i class="fas fa-edit"></i>
            </span>
            <div>
                <h2 class="asset-page-title">{{ __('app.asset.edit_title') }}</h2>
                <p class="asset-page-subtitle">{{ __('app.asset.edit_subtitle') }}</p>
            </div>
        </div>

        <div class="asset-page-actions">
            <a href="{{ route('asset-management.index') }}" class="asset-action-btn">
                <i class="fas fa-arrow-left"></i>
                <span>{{ __('app.asset.back_to_assets') }}</span>
            </a>
            <button
                id="update-asset-button"
                type="button"
                class="asset-action-btn is-primary"
                data-category="{{ $asset->category->value }}"
                data-asset-id="{{ $asset->id }}"
            >
                <i class="fas fa-save"></i>
                <span>{{ __('app.asset.update_asset') }}</span>
            </button>
        </div>
    </div>

    <div class="asset-mini-stats">
        <div class="asset-mini-stat">
            <span class="asset-mini-label">{{ __('app.asset.category') }}</span>
            <span class="asset-mini-value">{{ $assetCategoryLabel }}</span>
        </div>
        <div class="asset-mini-stat">
            <span class="asset-mini-label">{{ __('app.asset.account_code') }}</span>
            <span class="asset-mini-value">{{ $asset->accountCode }}</span>
        </div>
        <div class="asset-mini-stat">
            <span class="asset-mini-label">{{ __('app.asset.finance_snapshot') }}</span>
            <span class="asset-mini-value">{{ $assetPriceLabel }}</span>
        </div>
    </div>

    <form id="asset-basic-information-form" class="asset-panel">
        <div class="asset-panel-head">
            <h3 class="asset-panel-title">
                <i class="fas fa-id-card"></i>
                <span>{{ __('app.asset.asset_identity') }}</span>
            </h3>
            <span class="asset-panel-note">{{ __('app.asset.master_data') }}</span>
        </div>

        <div class="asset-panel-body">
            <div class="asset-form-grid">
                <div class="asset-field">
                    <label for="account_code">
                        {{ __('app.asset.account_code') }}
                        <span class="asset-required">*</span>
                    </label>
                    <input type="text" name="account_code" class="form-control asset-control" id="account_code" value="{{ $asset->accountCode }}" placeholder="{{ __('app.asset.account_code_placeholder') }}">
                </div>

                <div class="asset-field">
                    <label for="asset_serial_number">{{ __('app.asset.serial_number') }}</label>
                    <input type="text" name="asset_serial_number" class="form-control asset-control" id="asset_serial_number" value="{{ $asset->serialNumber }}" placeholder="{{ __('app.asset.serial_number_placeholder') }}">
                </div>

                <div class="asset-field">
                    <label for="location">
                        {{ __('app.asset.location') }}
                        <span class="asset-required">*</span>
                    </label>
                    <input type="text" name="location" class="form-control asset-control" id="location" value="{{ $asset->location }}" placeholder="{{ __('app.asset.location_placeholder') }}">
                </div>

                <div class="asset-field">
                    <label for="unit">
                        {{ __('app.asset.unit') }}
                        <span class="asset-required">*</span>
                    </label>
                    <select name="unit" id="unit" class="form-control asset-control">
                        <option value="" disabled {{ empty($asset->unit) ? 'selected' : '' }}>
                            {{ __('app.asset.choose_unit') }}
                        </option>
                        @foreach(AssetUnit::cases() as $unit)
                            <option value="{{ $unit->value }}" {{ $asset->unit === $unit ? 'selected' : '' }}>
                                {{ $unit->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="asset-field">
                    <label for="purchase_year">{{ __('app.asset.purchase_year') }}</label>
                    <input type="text" name="purchase_year" class="form-control asset-control" id="purchase_year" value="{{ $asset->purchaseYear }}" placeholder="{{ __('app.asset.purchase_year_placeholder') }}">
                </div>

                <div class="asset-field">
                    <label for="purchase_price">{{ __('app.asset.purchase_price') }}</label>
                    <input
                        type="number"
                        name="purchase_price"
                        class="form-control asset-control"
                        id="purchase_price"
                        min="0"
                        step="0.01"
                        value="{{ $asset->purchasePrice }}"
                        placeholder="{{ __('app.asset.purchase_price_placeholder') }}"
                    >
                </div>
            </div>
        </div>
    </form>

    <form id="asset-detail-form" class="asset-panel">
        <div class="asset-panel-head">
            <h3 class="asset-panel-title">
                <i class="fas fa-tools"></i>
                <span>{{ __('app.asset.operational_detail') }}</span>
            </h3>
            <span class="asset-panel-note">{{ $assetCategoryLabel }}</span>
        </div>

        <div class="asset-panel-body">
            @if(in_array($asset->category->value, [AssetCategory::AC->value, AssetCategory::OTHER->value], true))
                <div class="asset-form-grid">
                    <div class="asset-field">
                        <label for="brand">
                            {{ __('app.asset.brand') }}
                            <span class="asset-required">*</span>
                        </label>
                        <input
                            type="text"
                            name="brand"
                            class="form-control asset-control"
                            id="brand"
                            value="{{ data_get($asset->detail, 'brand') }}"
                            placeholder="{{ __('app.asset.brand_placeholder') }}"
                        >
                    </div>

                    <div class="asset-field">
                        <label for="dimension">
                            {{ __('app.asset.dimension') }}
                            <span class="asset-required">*</span>
                        </label>
                        <input
                            type="text"
                            name="dimension"
                            class="form-control asset-control"
                            id="dimension"
                            value="{{ data_get($asset->detail, 'dimension') }}"
                            placeholder="{{ __('app.asset.dimension_placeholder') }}"
                        >
                    </div>

                    <div class="asset-field">
                        <label for="power_rating">
                            {{ __('app.asset.power_rating') }}
                            <span class="asset-required">*</span>
                        </label>
                        <input
                            type="text"
                            name="power_rating"
                            class="form-control asset-control"
                            id="power_rating"
                            value="{{ data_get($asset->detail, 'power_rating') }}"
                            placeholder="{{ __('app.asset.power_rating_placeholder') }}"
                        >
                    </div>
                </div>
            @elseif($asset->category->value === AssetCategory::COMPUTER->value)
                <div class="asset-detail-placeholder mb-3">
                    <i class="fas fa-desktop"></i>
                    <div>
                        <strong>{{ __('app.asset.detail_ready') }}</strong>
                        <span>{{ __('app.asset.computer_components_note') }}</span>
                    </div>
                </div>

                <div class="asset-component-grid">
                    @foreach(ComputerComponent::cases() as $index => $componentEnum)
                        @php
                            $component = $groupedComponents->get($componentEnum->value);
                        @endphp

                        <section class="asset-component-card">
                            <h4 class="asset-component-title">
                                <i class="fas fa-microchip"></i>
                                <span>{{ $componentEnum->label() }}</span>
                            </h4>

                            <div class="asset-form-grid">
                                <div class="asset-field">
                                    <label>{{ __('app.asset.brand') }}</label>
                                    <input
                                        type="text"
                                        class="form-control asset-control"
                                        name="components[{{ $index }}][brand]"
                                        value="{{ $component['brand'] ?? '' }}"
                                        placeholder="{{ __('app.asset.brand_placeholder') }}"
                                    >
                                </div>

                                <div class="asset-field">
                                    <label>{{ __('app.asset.specification') }}</label>
                                    <input
                                        type="text"
                                        class="form-control asset-control"
                                        name="components[{{ $index }}][specification]"
                                        value="{{ $component['specification'] ?? '' }}"
                                    >
                                </div>

                                <div class="asset-field">
                                    <label>{{ __('app.asset.serial_number') }}</label>
                                    <input
                                        type="text"
                                        class="form-control asset-control"
                                        name="components[{{ $index }}][serial_number]"
                                        value="{{ $component['serial_number'] ?? '' }}"
                                        placeholder="{{ __('app.asset.serial_number_placeholder') }}"
                                    >
                                </div>
                            </div>

                            <input
                                type="hidden"
                                name="components[{{ $index }}][component_type]"
                                value="{{ $componentEnum->value }}"
                            >
                        </section>
                    @endforeach
                </div>
            @elseif($asset->category->value === AssetCategory::VEHICLE->value)
                <div class="asset-form-grid">
                    @foreach($vehicleDetailFields as $field)
                        <div class="asset-field">
                            <label for="vehicle_{{ $field['name'] }}">{{ $field['label'] }}</label>
                            <input
                                type="{{ $field['type'] }}"
                                name="{{ $field['name'] }}"
                                class="form-control asset-control"
                                id="vehicle_{{ $field['name'] }}"
                                value="{{ data_get($asset->detail, $field['name']) }}"
                                @if(!empty($field['min'])) min="{{ $field['min'] }}" @endif
                                @if(!empty($field['step'])) step="{{ $field['step'] }}" @endif
                            >
                        </div>
                    @endforeach
                </div>
            @elseif($asset->category->value === AssetCategory::ELECTRONIC->value)
                <div class="asset-form-grid">
                    @foreach($electronicDetailFields as $field)
                        <div class="asset-field">
                            <label for="electronic_{{ $field['name'] }}">{{ $field['label'] }}</label>
                            <input
                                type="{{ $field['type'] }}"
                                name="{{ $field['name'] }}"
                                class="form-control asset-control"
                                id="electronic_{{ $field['name'] }}"
                                value="{{ data_get($asset->detail, $field['name']) }}"
                                @if(!empty($field['min'])) min="{{ $field['min'] }}" @endif
                                @if(!empty($field['step'])) step="{{ $field['step'] }}" @endif
                            >
                        </div>
                    @endforeach
                </div>
            @elseif($asset->category->value === AssetCategory::ROOM_INVENTORY->value)
                <div class="asset-form-grid">
                    @foreach($roomInventoryDetailFields as $field)
                        <div class="asset-field">
                            <label for="room_inventory_{{ $field['name'] }}">{{ $field['label'] }}</label>
                            <input
                                type="{{ $field['type'] }}"
                                name="{{ $field['name'] }}"
                                class="form-control asset-control"
                                id="room_inventory_{{ $field['name'] }}"
                                value="{{ data_get($asset->detail, $field['name']) }}"
                                @if(!empty($field['min'])) min="{{ $field['min'] }}" @endif
                                @if(!empty($field['step'])) step="{{ $field['step'] }}" @endif
                            >
                        </div>
                    @endforeach
                </div>
            @elseif($asset->category->value === AssetCategory::BUILDING_INFRASTRUCTURE->value)
                <div class="asset-form-grid">
                    @foreach($buildingInfrastructureDetailFields as $field)
                        <div class="asset-field">
                            <label for="building_infrastructure_{{ $field['name'] }}">{{ $field['label'] }}</label>
                            <input
                                type="{{ $field['type'] }}"
                                name="{{ $field['name'] }}"
                                class="form-control asset-control"
                                id="building_infrastructure_{{ $field['name'] }}"
                                value="{{ data_get($asset->detail, $field['name']) }}"
                                @if(!empty($field['min'])) min="{{ $field['min'] }}" @endif
                                @if(!empty($field['step'])) step="{{ $field['step'] }}" @endif
                            >
                        </div>
                    @endforeach
                </div>
            @else
                <div class="asset-detail-placeholder">
                    <i class="fas fa-info-circle"></i>
                    <div>
                        <strong>{{ __('app.asset.no_detail_title') }}</strong>
                        <span>{{ __('app.asset.no_detail_text') }}</span>
                    </div>
                </div>
            @endif
        </div>

        <div class="asset-panel-footer">
            <span class="asset-panel-note">{{ __('app.asset.category') }}: {{ $assetCategoryLabel }}</span>
            <button
                id="update-asset-button-footer"
                type="button"
                class="asset-action-btn is-primary"
                data-category="{{ $asset->category->value }}"
                data-asset-id="{{ $asset->id }}"
            >
                <i class="fas fa-save"></i>
                <span>{{ __('app.asset.update_asset') }}</span>
            </button>
        </div>
    </form>
</div>
@stop

@section('js')
<script>
    async function updateAsset(button)
    {
        Loading.show();
        $(button).prop('disabled', true);
        try
        {
            const category = $(button).data('category');
            const assetId = $(button).data('asset-id');

            const basicForm = document.getElementById('asset-basic-information-form');
            const detailForm = document.getElementById('asset-detail-form');

            if (!basicForm.checkValidity() || !detailForm.checkValidity()) {
                basicForm.reportValidity();
                detailForm.reportValidity();
                return;
            }

            const basicFormData = new FormData(basicForm);
            const detailFormData = new FormData(detailForm);
            const formData = new FormData();

            for (const [key, value] of basicFormData.entries()) {
                formData.append(key, value);
            }

            for (const [key, value] of detailFormData.entries()) {
                const match = key.match(/^components\[(\d+)\]\[(.+)\]$/);

                if (match) {
                    const index = match[1];
                    const field = match[2];

                    formData.append(`detail[components][${index}][${field}]`, value);
                } else {
                    formData.append(`detail[${key}]`, value);
                }
            }

            formData.append('category', category);
            formData.append('id', assetId);
            formData.set('_method', 'PUT');

            await Http.post("{{ route('asset-management.update') }}", formData);
            refreshUI();
        }
        catch(error)
        {
            Notification.error(error);
        }
        finally
        {
            Loading.hide();
            $(button).prop('disabled', false);
        }
    }

    $(function() {
        $('#update-asset-button, #update-asset-button-footer').on('click', function() {
            updateAsset(this);
        });
    });
</script>
@stop
