@extends('layouts.app')

@php
use App\Enums\Asset\AssetUnit;
use App\Enums\Asset\ComputerComponent;

$groupedComponents = collect($asset->detail)
    ->keyBy('component_type');
@endphp

@section('content')
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap">
<div class="asset-shell">
    <div class="card">
        <div class="card-header">
            <h3 class="text-center mb-0">{{ __('app.asset.edit_title') }}</h3>
        </div>

        <div class="card-body p-0">
            <div class="row mb-3">
                <div class="col">
                    <form id="asset-basic-information-form" class="card mb-0 border-0 shadow-none">
                        <div class="card-body">
                            <div class="row">
                                <div class="col">
                                    <div class="form-group">
                                        <label for="account_code">{{ __('app.asset.account_code') }} <span class="text-red">*</span></label>
                                        <input type="text" name="account_code" class="form-control" id="account_code" value="{{ $asset->accountCode }}" placeholder="{{ __('app.asset.account_code_placeholder') }}">
                                    </div>
                                </div>
                                <div class="col">
                                    <div class="form-group">
                                        <label for="asset_serial_number">{{ __('app.asset.serial_number') }}</label>
                                        <input type="text" name="asset_serial_number" class="form-control" id="asset_serial_number" value="{{ $asset->serialNumber }}" placeholder="{{ __('app.asset.serial_number_placeholder') }}">
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col">
                                    <div class="form-group">
                                        <label for="location">{{ __('app.asset.location') }} <span class="text-red">*</span></label>
                                        <input type="text" name="location" class="form-control" id="location" value="{{ $asset->location }}" placeholder="{{ __('app.asset.location_placeholder') }}">
                                    </div>
                                </div>
                                <div class="col">
                                    <div class="form-group">
                                        <label for="purchase_year">{{ __('app.asset.purchase_year') }}</label>
                                        <input type="text" name="purchase_year" class="form-control" id="purchase_year" value="{{ $asset->purchaseYear }}" placeholder="{{ __('app.asset.purchase_year_placeholder') }}">
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col">
                                    <div class="form-group mb-0">
                                        <label for="unit">{{ __('app.asset.unit') }} <span class="text-red">*</span></label>
                                        <select name="unit" id="unit" class="form-control">
                                            <option value="" disabled {{ empty($asset->unit) ? 'selected' : '' }}>
                                                {{ __('app.asset.choose_unit') }}
                                            </option>

                                            @foreach(AssetUnit::cases() as $unit)
                                                <option value="{{ $unit->value }}"
                                                    {{ $asset->unit === $unit ? 'selected' : '' }}>
                                                    {{ $unit->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div id="asset-detail-form-container" class="row">
                <div class="col">
                    <form id="asset-detail-form" class="card mb-0 border-0 shadow-none">
                        <div class="card-body">
                            @if(in_array($asset->category->value, ['AC', 'OTHER'], true))
                                <div class="row">
                                    <div class="col">
                                        <div class="form-group">
                                            <label for="brand">Brand <span class="text-red">*</span></label>
                                            <input
                                                type="text"
                                                name="brand"
                                                class="form-control"
                                                id="brand"
                                                value="{{ data_get($asset->detail, 'brand') }}"
                                                placeholder="Masukkan brand"
                                            >
                                        </div>
                                    </div>
                                    <div class="col">
                                        <div class="form-group">
                                            <label for="dimension">Dimensi <span class="text-red">*</span></label>
                                            <input
                                                type="text"
                                                name="dimension"
                                                class="form-control"
                                                id="dimension"
                                                value="{{ data_get($asset->detail, 'dimension') }}"
                                                placeholder="Masukkan dimensi"
                                            >
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col">
                                        <div class="form-group mb-0">
                                            <label for="power_rating">Voltase <span class="text-red">*</span></label>
                                            <input
                                                type="number"
                                                name="power_rating"
                                                class="form-control"
                                                id="power_rating"
                                                value="{{ data_get($asset->detail, 'power_rating') }}"
                                                placeholder="Masukkan voltase"
                                            >
                                        </div>
                                    </div>
                                </div>
                            @elseif($asset->category->value === 'COMPUTER')
                                @foreach(ComputerComponent::cases() as $index => $componentEnum)
                                    @php
                                        $component = $groupedComponents->get($componentEnum->value);
                                    @endphp

                                    <div class="card mb-3 p-3">
                                        <h5>{{ $componentEnum->value }}</h5>

                                        <div class="row">
                                            <div class="col">
                                                <div class="form-group">
                                                    <label>Brand</label>
                                                    <input
                                                        type="text"
                                                        class="form-control"
                                                        name="components[{{ $index }}][brand]"
                                                        value="{{ $component['brand'] ?? '' }}"
                                                    >
                                                </div>
                                            </div>

                                            <div class="col">
                                                <div class="form-group">
                                                    <label>Spesifikasi</label>
                                                    <input
                                                        type="text"
                                                        class="form-control"
                                                        name="components[{{ $index }}][specification]"
                                                        value="{{ $component['specification'] ?? '' }}"
                                                    >
                                                </div>
                                            </div>

                                            <div class="col">
                                                <div class="form-group">
                                                    <label>Serial Number</label>
                                                    <input
                                                        type="text"
                                                        class="form-control"
                                                        name="components[{{ $index }}][serial_number]"
                                                        value="{{ $component['serial_number'] ?? '' }}"
                                                    >
                                                </div>
                                            </div>
                                        </div>

                                        <input
                                            type="hidden"
                                            name="components[{{ $index }}][component_type]"
                                            value="{{ $componentEnum->value }}"
                                        >
                                    </div>
                                @endforeach
                            @endif
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="card-footer">
            <button id="update-asset-button" class="float-right btn btn-primary" data-category="{{ $asset->category->value }}" data-asset-id="{{ $asset->id }}">
                <i class="fas fa-save"></i>
                {{ __('app.asset.save') }}
            </button>
        </div>
    </div>
</div>
@stop

@section('js')
<script>
    $(function() {
        $('#update-asset-button').on('click', async function() {
            Loading.show();
            $(this).prop('disabled', true);
            try
            {
                const category = $(this).data('category');
                const assetId = $(this).data('asset-id');

                const basicFormData = new FormData(document.getElementById('asset-basic-information-form'));
                const detailFormData = new FormData(document.getElementById('asset-detail-form'));

                const formData = new FormData();

                for (const [key, value] of basicFormData.entries())
                    formData.append(key, value);

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
                $(this).prop('disabled', false);
            }
        });
    });
</script>
@stop
