@extends('layouts.app')

{{-- Developer note: this page creates the asset master only. Finance policy
     fields for automated depreciation are not part of this form yet. See
     docs/finance-asset-depreciation.md before extending the asset schema. --}}
@php
use App\Enums\Asset\AssetCategory;
use App\Enums\Asset\AssetUnit;

$presetCategory = request('category') ? AssetCategory::tryFrom((string) request('category')) : null;
@endphp

@section('section_name', __('app.asset.register_title'))

@section('content')
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap">

<div class="asset-shell asset-page">
    <div class="asset-page-head">
        <div class="asset-page-title-group">
            <span class="asset-page-icon">
                <i class="fas fa-plus-circle"></i>
            </span>
            <div>
                <h2 class="asset-page-title">{{ __('app.asset.register_title') }}</h2>
                <p class="asset-page-subtitle">{{ __('app.asset.register_subtitle') }}</p>
            </div>
        </div>

        <div class="asset-page-actions">
            <a href="{{ route('asset-management.index') }}" class="asset-action-btn">
                <i class="fas fa-arrow-left"></i>
                <span>{{ __('app.asset.back_to_assets') }}</span>
            </a>
            <button id="register-asset-button" type="button" class="asset-action-btn is-primary">
                <i class="fas fa-save"></i>
                <span>{{ __('app.asset.save_asset') }}</span>
            </button>
        </div>
    </div>

    <div class="asset-mini-stats">
        <div class="asset-mini-stat">
            <span class="asset-mini-label">{{ __('app.asset.form_summary') }}</span>
            <span class="asset-mini-value">{{ __('app.asset.master_data') }}</span>
        </div>
        <div class="asset-mini-stat">
            <span class="asset-mini-label">{{ __('app.asset.selected_category') }}</span>
            <span class="asset-mini-value" id="asset-selected-category-label">{{ __('app.asset.waiting_detail_title') }}</span>
        </div>
        <div class="asset-mini-stat">
            <span class="asset-mini-label">{{ __('app.asset.finance_snapshot') }}</span>
            <span class="asset-mini-value">{{ __('app.asset.purchase_price') }}</span>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
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
                        <div class="asset-field is-full">
                            <label for="category">
                                {{ __('app.asset.category') }}
                                <span class="asset-required">*</span>
                            </label>
                            @if($presetCategory)
                                <input type="hidden" name="category" value="{{ $presetCategory->value }}">
                            @endif
                            <select name="category" id="category" class="form-control asset-control" required @disabled($presetCategory)>
                                <option value="" disabled @selected(!$presetCategory)>{{ __('app.asset.choose_category') }}</option>
                                @foreach(AssetCategory::cases() as $category)
                                    <option value="{{ $category->value }}" @selected($presetCategory?->value === $category->value)>{{ $category->label() }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="asset-field">
                            <label for="account_code">
                                {{ __('app.asset.account_code') }}
                                <span class="asset-required">*</span>
                            </label>
                            <input type="text" name="account_code" class="form-control asset-control" id="account_code" placeholder="{{ __('app.asset.account_code_placeholder') }}">
                        </div>

                        <div class="asset-field">
                            <label for="asset_serial_number">{{ __('app.asset.serial_number') }}</label>
                            <input type="text" name="asset_serial_number" class="form-control asset-control" id="asset_serial_number" placeholder="{{ __('app.asset.serial_number_placeholder') }}">
                        </div>

                        <div class="asset-field">
                            <label for="unit">
                                {{ __('app.asset.unit') }}
                                <span class="asset-required">*</span>
                            </label>
                            <select name="unit" id="unit" class="form-control asset-control">
                                <option value="" disabled selected>{{ __('app.asset.choose_unit') }}</option>
                                @foreach(AssetUnit::cases() as $unit)
                                    <option value="{{ $unit->value }}">{{ $unit->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="asset-field">
                            <label for="location">
                                {{ __('app.asset.location') }}
                                <span class="asset-required">*</span>
                            </label>
                            <input type="text" name="location" class="form-control asset-control" id="location" placeholder="{{ __('app.asset.location_placeholder') }}">
                        </div>

                        <div class="asset-field">
                            <label for="purchase_year">{{ __('app.asset.purchase_year') }}</label>
                            <input type="text" name="purchase_year" class="form-control asset-control" id="purchase_year" placeholder="{{ __('app.asset.purchase_year_placeholder') }}">
                        </div>

                        <div class="asset-field">
                            <label for="purchase_price">{{ __('app.asset.purchase_price') }}</label>
                            <input type="number" name="purchase_price" min="0" step="0.01" class="form-control asset-control" id="purchase_price" placeholder="{{ __('app.asset.purchase_price_placeholder') }}">
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <div class="col-lg-4 mt-3 mt-lg-0">
            <section class="asset-panel h-100">
                <div class="asset-panel-head">
                    <h3 class="asset-panel-title">
                        <i class="fas fa-clipboard-check"></i>
                        <span>{{ __('app.asset.operational_detail') }}</span>
                    </h3>
                </div>
                <div class="asset-panel-body">
                    <div class="asset-detail-placeholder">
                        <i class="fas fa-layer-group"></i>
                        <div>
                            <strong>{{ __('app.asset.waiting_detail_title') }}</strong>
                            <span>{{ __('app.asset.waiting_detail_text') }}</span>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>

    <form id="asset-detail-form" class="asset-panel">
        <div class="asset-panel-head">
            <h3 class="asset-panel-title">
                <i class="fas fa-tools"></i>
                <span>{{ __('app.asset.operational_detail') }}</span>
            </h3>
            <span class="asset-panel-note" id="asset-detail-panel-note">{{ __('app.asset.waiting_detail_title') }}</span>
        </div>

        <div class="asset-panel-body" id="asset-detail-form-body">
            <div class="asset-detail-placeholder">
                <i class="fas fa-info-circle"></i>
                <div>
                    <strong>{{ __('app.asset.waiting_detail_title') }}</strong>
                    <span>{{ __('app.asset.waiting_detail_text') }}</span>
                </div>
            </div>
        </div>
    </form>
</div>
@stop

@section('js')
<script>
    const assetText = {
        brand: @json(__('app.asset.brand')),
        brandPlaceholder: @json(__('app.asset.brand_placeholder')),
        specification: @json(__('app.asset.specification')),
        serialNumber: @json(__('app.asset.serial_number')),
        serialNumberPlaceholder: @json(__('app.asset.serial_number_placeholder')),
        dimension: @json(__('app.asset.dimension')),
        dimensionPlaceholder: @json(__('app.asset.dimension_placeholder')),
        powerRating: @json(__('app.asset.power_rating')),
        powerRatingPlaceholder: @json(__('app.asset.power_rating_placeholder')),
        waitingDetailTitle: @json(__('app.asset.waiting_detail_title')),
        waitingDetailText: @json(__('app.asset.waiting_detail_text')),
        detailReady: @json(__('app.asset.detail_ready')),
        noDetailTitle: @json(__('app.asset.no_detail_title')),
        noDetailText: @json(__('app.asset.no_detail_text')),
        computerComponentsNote: @json(__('app.asset.computer_components_note')),
        vehicleFields: @json(__('app.asset.vehicle_fields')),
        electronicFields: @json(__('app.asset.electronic_fields')),
        roomInventoryFields: @json(__('app.asset.room_inventory_fields')),
        buildingInfrastructureFields: @json(__('app.asset.building_infrastructure_fields')),
        categories: {
            AC: @json(__('app.asset.categories.ac')),
            BUILDING_INFRASTRUCTURE: @json(__('app.asset.categories.building_infrastructure')),
            ELECTRONIC: @json(__('app.asset.categories.electronic')),
            ROOM_INVENTORY: @json(__('app.asset.categories.room_inventory')),
            VEHICLE: @json(__('app.asset.categories.vehicle')),
            COMPUTER: @json(__('app.asset.categories.computer')),
            OTHER: @json(__('app.asset.categories.other')),
        },
        components: {
            monitor: @json(__('app.asset.computer_components.monitor')),
            motherboard: @json(__('app.asset.computer_components.motherboard')),
            processor: @json(__('app.asset.computer_components.processor')),
            ram: @json(__('app.asset.computer_components.ram')),
            storage: @json(__('app.asset.computer_components.storage')),
            gpu: @json(__('app.asset.computer_components.gpu')),
            keyboardMouse: @json(__('app.asset.computer_components.keyboard_mouse')),
        },
    };

    const assetDetailForm = {
        AC: [
            { label: assetText.brand, name: 'brand', type: 'text', placeholder: assetText.brandPlaceholder, required: true },
            { label: assetText.dimension, name: 'dimension', type: 'text', placeholder: assetText.dimensionPlaceholder, required: true },
            { label: assetText.powerRating, name: 'power_rating', type: 'text', placeholder: assetText.powerRatingPlaceholder, required: true },
        ],
        OTHER: [
            { label: assetText.brand, name: 'brand', type: 'text', placeholder: assetText.brandPlaceholder, required: true },
            { label: assetText.dimension, name: 'dimension', type: 'text', placeholder: assetText.dimensionPlaceholder, required: true },
            { label: assetText.powerRating, name: 'power_rating', type: 'text', placeholder: assetText.powerRatingPlaceholder, required: true },
        ],
        BUILDING_INFRASTRUCTURE: [
            { label: assetText.buildingInfrastructureFields.asset_code, name: 'asset_code', type: 'text' },
            { label: assetText.buildingInfrastructureFields.asset_name, name: 'asset_name', type: 'text' },
            { label: assetText.buildingInfrastructureFields.asset_type, name: 'asset_type', type: 'text' },
            { label: assetText.buildingInfrastructureFields.land_area, name: 'land_area', type: 'text' },
            { label: assetText.buildingInfrastructureFields.building_area, name: 'building_area', type: 'text' },
            { label: assetText.buildingInfrastructureFields.volume_size, name: 'volume_size', type: 'text' },
            { label: assetText.buildingInfrastructureFields.document_number, name: 'document_number', type: 'text' },
            { label: assetText.buildingInfrastructureFields.acquisition_date, name: 'acquisition_date', type: 'date' },
            { label: assetText.buildingInfrastructureFields.asset_account_code, name: 'asset_account_code', type: 'text' },
            { label: assetText.buildingInfrastructureFields.useful_life_years, name: 'useful_life_years', type: 'number', min: '0', step: '1' },
            { label: assetText.buildingInfrastructureFields.initial_accumulated_depreciation, name: 'initial_accumulated_depreciation', type: 'number', min: '0', step: '0.01' },
            { label: assetText.buildingInfrastructureFields.current_year_depreciation, name: 'current_year_depreciation', type: 'number', min: '0', step: '0.01' },
            { label: assetText.buildingInfrastructureFields.accumulated_depreciation, name: 'accumulated_depreciation', type: 'number', min: '0', step: '0.01' },
            { label: assetText.buildingInfrastructureFields.book_value, name: 'book_value', type: 'number', min: '0', step: '0.01' },
            { label: assetText.buildingInfrastructureFields.condition, name: 'condition', type: 'text' },
            { label: assetText.buildingInfrastructureFields.status, name: 'status', type: 'text' },
            { label: assetText.buildingInfrastructureFields.responsible_person, name: 'responsible_person', type: 'text' },
            { label: assetText.buildingInfrastructureFields.notes, name: 'notes', type: 'text' },
            { label: assetText.buildingInfrastructureFields.source_data, name: 'source_data', type: 'text' },
        ],
        COMPUTER: [
            { type: 'component', component: 'Monitor', label: assetText.components.monitor },
            { type: 'component', component: 'Motherboard', label: assetText.components.motherboard },
            { type: 'component', component: 'Processor', label: assetText.components.processor },
            { type: 'component', component: 'RAM', label: assetText.components.ram },
            { type: 'component', component: 'Storage', label: assetText.components.storage },
            { type: 'component', component: 'GPU', label: assetText.components.gpu },
            { type: 'component', component: 'Keyboard / Mouse', label: assetText.components.keyboardMouse },
        ],
        VEHICLE: [
            { label: assetText.vehicleFields.vehicle_type, name: 'vehicle_type', type: 'text' },
            { label: assetText.vehicleFields.vehicle_name, name: 'vehicle_name', type: 'text' },
            { label: assetText.vehicleFields.brand, name: 'brand', type: 'text' },
            { label: assetText.vehicleFields.model_type, name: 'model_type', type: 'text' },
            { label: assetText.vehicleFields.vehicle_year, name: 'vehicle_year', type: 'text' },
            { label: assetText.vehicleFields.color, name: 'color', type: 'text' },
            { label: assetText.vehicleFields.license_plate, name: 'license_plate', type: 'text' },
            { label: assetText.vehicleFields.chassis_number, name: 'chassis_number', type: 'text' },
            { label: assetText.vehicleFields.engine_number, name: 'engine_number', type: 'text' },
            { label: assetText.vehicleFields.bpkb_name, name: 'bpkb_name', type: 'text' },
            { label: assetText.vehicleFields.stnk_valid_until, name: 'stnk_valid_until', type: 'date' },
            { label: assetText.vehicleFields.tax_valid_until, name: 'tax_valid_until', type: 'date' },
            { label: assetText.vehicleFields.kilometer, name: 'kilometer', type: 'number', min: '0', step: '1' },
            { label: assetText.vehicleFields.acquisition_date, name: 'acquisition_date', type: 'date' },
            { label: assetText.vehicleFields.asset_account_code, name: 'asset_account_code', type: 'text' },
            { label: assetText.vehicleFields.useful_life_years, name: 'useful_life_years', type: 'number', min: '0', step: '1' },
            { label: assetText.vehicleFields.accumulated_depreciation, name: 'accumulated_depreciation', type: 'number', min: '0', step: '0.01' },
            { label: assetText.vehicleFields.book_value, name: 'book_value', type: 'number', min: '0', step: '0.01' },
            { label: assetText.vehicleFields.pic, name: 'pic', type: 'text' },
            { label: assetText.vehicleFields.condition, name: 'condition', type: 'text' },
            { label: assetText.vehicleFields.status, name: 'status', type: 'text' },
            { label: assetText.vehicleFields.notes, name: 'notes', type: 'text' },
            { label: assetText.vehicleFields.source_data, name: 'source_data', type: 'text' },
        ],
        ELECTRONIC: [
            { label: assetText.electronicFields.asset_code, name: 'asset_code', type: 'text' },
            { label: assetText.electronicFields.electronic_type, name: 'electronic_type', type: 'text' },
            { label: assetText.electronicFields.asset_name, name: 'asset_name', type: 'text' },
            { label: assetText.electronicFields.brand, name: 'brand', type: 'text' },
            { label: assetText.electronicFields.model_type, name: 'model_type', type: 'text' },
            { label: assetText.electronicFields.specification, name: 'specification', type: 'text' },
            { label: assetText.electronicFields.serial_number, name: 'serial_number', type: 'text' },
            { label: assetText.electronicFields.acquisition_date, name: 'acquisition_date', type: 'date' },
            { label: assetText.electronicFields.asset_account_code, name: 'asset_account_code', type: 'text' },
            { label: assetText.electronicFields.useful_life_years, name: 'useful_life_years', type: 'number', min: '0', step: '1' },
            { label: assetText.electronicFields.accumulated_depreciation, name: 'accumulated_depreciation', type: 'number', min: '0', step: '0.01' },
            { label: assetText.electronicFields.book_value, name: 'book_value', type: 'number', min: '0', step: '0.01' },
            { label: assetText.electronicFields.condition, name: 'condition', type: 'text' },
            { label: assetText.electronicFields.status, name: 'status', type: 'text' },
            { label: assetText.electronicFields.pic, name: 'pic', type: 'text' },
            { label: assetText.electronicFields.notes, name: 'notes', type: 'text' },
            { label: assetText.electronicFields.source_data, name: 'source_data', type: 'text' },
        ],
        ROOM_INVENTORY: [
            { label: assetText.roomInventoryFields.asset_code, name: 'asset_code', type: 'text' },
            { label: assetText.roomInventoryFields.item_type, name: 'item_type', type: 'text' },
            { label: assetText.roomInventoryFields.item_name, name: 'item_name', type: 'text' },
            { label: assetText.roomInventoryFields.material, name: 'material', type: 'text' },
            { label: assetText.roomInventoryFields.size, name: 'size', type: 'text' },
            { label: assetText.roomInventoryFields.quantity, name: 'quantity', type: 'text' },
            { label: assetText.roomInventoryFields.acquisition_date, name: 'acquisition_date', type: 'date' },
            { label: assetText.roomInventoryFields.unit_price, name: 'unit_price', type: 'number', min: '0', step: '0.01' },
            { label: assetText.roomInventoryFields.asset_account_code, name: 'asset_account_code', type: 'text' },
            { label: assetText.roomInventoryFields.useful_life_years, name: 'useful_life_years', type: 'number', min: '0', step: '1' },
            { label: assetText.roomInventoryFields.accumulated_depreciation, name: 'accumulated_depreciation', type: 'number', min: '0', step: '0.01' },
            { label: assetText.roomInventoryFields.book_value, name: 'book_value', type: 'number', min: '0', step: '0.01' },
            { label: assetText.roomInventoryFields.condition, name: 'condition', type: 'text' },
            { label: assetText.roomInventoryFields.status, name: 'status', type: 'text' },
            { label: assetText.roomInventoryFields.notes, name: 'notes', type: 'text' },
            { label: assetText.roomInventoryFields.source_data, name: 'source_data', type: 'text' },
        ],
    };
    const presetAssetCategory = @json($presetCategory?->value);

    function waitingDetailHtml()
    {
        return `
            <div class="asset-detail-placeholder">
                <i class="fas fa-info-circle"></i>
                <div>
                    <strong>${assetText.waitingDetailTitle}</strong>
                    <span>${assetText.waitingDetailText}</span>
                </div>
            </div>
        `;
    }

    function resetAssetForm()
    {
        document.getElementById('asset-basic-information-form').reset();
        document.getElementById('asset-detail-form').reset();
        $('#asset-detail-form-body').html(waitingDetailHtml());
        $('#asset-selected-category-label').text(assetText.waitingDetailTitle);
        $('#asset-detail-panel-note').text(assetText.waitingDetailTitle);
        if (presetAssetCategory) {
            $('#category').val(presetAssetCategory).trigger('change');
        } else {
            $('#category').prop('selectedIndex', 0);
        }
        $('#unit').prop('selectedIndex', 0);
    }

    function constructAssetDetailForm(category)
    {
        const fields = assetDetailForm[category] || [];
        let html = '';

        if (fields.length === 0) {
            $('#asset-detail-panel-note').text(assetText.noDetailTitle);
            $('#asset-detail-form-body').html(`
                <div class="asset-detail-placeholder">
                    <i class="fas fa-info-circle"></i>
                    <div>
                        <strong>${assetText.noDetailTitle}</strong>
                        <span>${assetText.noDetailText}</span>
                    </div>
                </div>
            `);
            return;
        }

        if (category === 'COMPUTER') {
            html += `
                <div class="asset-detail-placeholder mb-3">
                    <i class="fas fa-desktop"></i>
                    <div>
                        <strong>${assetText.detailReady}</strong>
                        <span>${assetText.computerComponentsNote}</span>
                    </div>
                </div>
                <div class="asset-component-grid">
            `;

            fields.forEach((item, index) => {
                html += `
                    <section class="asset-component-card">
                        <h4 class="asset-component-title">
                            <i class="fas fa-microchip"></i>
                            <span>${item.label}</span>
                        </h4>
                        <div class="asset-form-grid">
                            <div class="asset-field">
                                <label>${assetText.brand}</label>
                                <input type="text" class="form-control asset-control" name="components[${index}][brand]" placeholder="${assetText.brandPlaceholder}">
                            </div>
                            <div class="asset-field">
                                <label>${assetText.specification}</label>
                                <input type="text" class="form-control asset-control" name="components[${index}][specification]">
                            </div>
                            <div class="asset-field">
                                <label>${assetText.serialNumber}</label>
                                <input type="text" class="form-control asset-control" name="components[${index}][serial_number]" placeholder="${assetText.serialNumberPlaceholder}">
                            </div>
                        </div>
                        <input type="hidden" name="components[${index}][component_type]" value="${item.component}">
                    </section>
                `;
            });

            html += '</div>';
        } else {
            html = '<div class="asset-form-grid">';
            fields.forEach(field => {
                html += `
                    <div class="asset-field">
                        <label>
                            ${field.label}
                            ${field.required ? '<span class="asset-required">*</span>' : ''}
                        </label>
                        <input
                            type="${field.type}"
                            name="${field.name}"
                            class="form-control asset-control"
                            placeholder="${field.placeholder ?? ''}"
                            ${field.min ? `min="${field.min}"` : ''}
                            ${field.step ? `step="${field.step}"` : ''}
                            ${field.required ? 'required' : ''}
                        >
                    </div>
                `;
            });
            html += '</div>';
        }

        $('#asset-detail-form-body').html(html);
    }

    $(function() {
        $('#category').on('change', function() {
            const category = $(this).val();
            const categoryLabel = assetText.categories[category] ?? category;

            $('#asset-selected-category-label').text(categoryLabel);
            $('#asset-detail-panel-note').text(assetText.detailReady);
            constructAssetDetailForm(category);
        });

        resetAssetForm();

        $('#register-asset-button').on('click', async function() {
            Loading.show();
            $(this).prop('disabled', true);

            try
            {
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

                await Http.post("{{ route('asset-management.store') }}", formData);
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
