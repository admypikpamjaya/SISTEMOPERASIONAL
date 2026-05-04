@extends('layouts.app')

{{-- Developer note: this page creates the asset master only. Finance policy
     fields for automated depreciation are not part of this form yet. See
     docs/finance-asset-depreciation.md before extending the asset schema. --}}
@php
use App\Enums\Asset\AssetCategory;
use App\Enums\Asset\AssetUnit;
@endphp

@section('content')
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap">
<div class="asset-shell">
    <div class="card">
        <div class="card-header">
            <h3 class="text-center mb-0">{{ __('app.asset.register_title') }}</h3>
        </div>

        <div class="card-body p-0">
            <div class="row mb-3">
                <div class="col">
                    <form id="asset-basic-information-form" class="card mb-0 border-0 shadow-none">
                        <div class="card-body">
                            <div class="row">
                                <div class="col">
                                    <div class="form-group">
                                        <label for="category">{{ __('app.asset.category') }} <span class="text-red">*</span></label>
                                        <select name="category" id="category" class="form-control">
                                            <option value="" disabled selected>{{ __('app.asset.choose_category') }}</option>
                                            @foreach(AssetCategory::cases() as $category)
                                                <option value="{{ $category->value }}">{{ $category->label() }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col">
                                    <div class="form-group">
                                        <label for="account_code">{{ __('app.asset.account_code') }} <span class="text-red">*</span></label>
                                        <input type="text" name="account_code" class="form-control" id="account_code" placeholder="{{ __('app.asset.account_code_placeholder') }}">
                                    </div>
                                </div>
                                <div class="col">
                                    <div class="form-group">
                                        <label for="asset_serial_number">{{ __('app.asset.serial_number') }}</label>
                                        <input type="text" name="asset_serial_number" class="form-control" id="asset_serial_number" placeholder="{{ __('app.asset.serial_number_placeholder') }}">
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col">
                                    <div class="form-group">
                                        <label for="unit">{{ __('app.asset.unit') }} <span class="text-red">*</span></label>
                                        <select name="unit" id="unit" class="form-control">
                                            <option value="" disabled selected>{{ __('app.asset.choose_unit') }}</option>
                                            @foreach(AssetUnit::cases() as $unit)
                                                <option value="{{ $unit->value }}">{{ $unit->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col">
                                    <div class="form-group">
                                        <label for="location">{{ __('app.asset.location') }} <span class="text-red">*</span></label>
                                        <input type="text" name="location" class="form-control" id="location" placeholder="{{ __('app.asset.location_placeholder') }}">
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col">
                                    <div class="form-group mb-0">
                                        <label for="purchase_year">{{ __('app.asset.purchase_year') }}</label>
                                        <input type="text" name="purchase_year" class="form-control" id="purchase_year" placeholder="{{ __('app.asset.purchase_year_placeholder') }}">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div id="asset-detail-form-container" class="d-none row">
                <div class="col">
                    <form id="asset-detail-form" class="card mb-0 border-0 shadow-none">
                        <div class="card-body"></div>
                    </form>
                </div>
            </div>
        </div>

        <div class="card-footer">
            <button id="register-asset-button" class="float-right btn btn-primary">
                <i class="fas fa-save"></i>
                {{ __('app.asset.save') }}
            </button>
        </div>
    </div>
</div>
@stop

@section('js')
<script>
    const assetDetailForm = {
        'AC': [
            { label: 'Brand <span class="text-red">*</span>', name: 'brand', type: 'text' },
            { label: 'Dimensi <span class="text-red">*</span>', name: 'dimension', type: 'text' },
            { label: 'Voltase <span class="text-red">*</span>', name: 'power_rating', type: 'number', min: 1 },
        ],
        'COMPUTER': [
            { type: 'component', component: 'Monitor' },
            { type: 'component', component: 'Motherboard' },
            { type: 'component', component: 'Processor' },
            { type: 'component', component: 'RAM' },
            { type: 'component', component: 'Storage' },
            { type: 'component', component: 'GPU' },
            { type: 'component', component: 'Keyboard / Mouse' },
        ],
    }

    function resetAssetForm()
    {
        document.getElementById('asset-basic-information-form').reset();
        document.getElementById('asset-detail-form').reset();
        $('#asset-detail-form .card-body').html('');

        $('#category').prop('selectedIndex', 0);
        $('#unit').prop('selectedIndex', 0);
        $('#asset-detail-form-container').addClass('d-none');
    }

    function constructAssetDetailForm(category)
    {
        const fields = assetDetailForm[category] || [];

        let html = ``;

        if (category === 'COMPUTER') {
            fields.forEach((item, index) => {
                html += `
                    <div class="card mb-3 p-3">
                        <h5>${item.component}</h5>
                        <div class="row">
                            <div class="col">
                                <div class="form-group">
                                    <label>Brand</label>
                                    <input type="text" class="form-control" name="components[${index}][brand]">
                                </div>
                            </div>
                            <div class="col">
                                <div class="form-group">
                                    <label>Spesifikasi</label>
                                    <input type="text" class="form-control" name="components[${index}][specification]">
                                </div>
                            </div>
                            <div class="col">
                                <div class="form-group">
                                    <label>Serial Number</label>
                                    <input type="text" class="form-control" name="components[${index}][serial_number]">
                                </div>
                            </div>
                        </div>

                        <input type="hidden" name="components[${index}][component_type]" value="${item.component}">
                    </div>
                `;
            });
        } else {
            const chunkedFields = chunkArray(fields, 2);

            chunkedFields.forEach(chunk => {
                html += `<div class="row">`;
                chunk.forEach(field => {
                    html += `
                        <div class="col">
                            <div class="form-group">
                                <label>${field.label}</label>
                                <input type="${field.type}" name="${field.name}" class="form-control">
                            </div>
                        </div>
                    `;
                });
                html += `</div>`;
            });
        }

        $('#asset-detail-form').find('.card-body').html(html);
    }

    $(function() {
        resetAssetForm();

        $('#category').on('change', function() {
            $('#asset-detail-form-container').removeClass('d-none');
            constructAssetDetailForm($(this).val());
        });

        $('#register-asset-button').on('click', async function() {
            Loading.show();
            $(this).prop('disabled', true);

            try
            {
                const basicFormData = new FormData(document.getElementById('asset-basic-information-form'));
                const detailFormData = new FormData(document.getElementById('asset-detail-form'));

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
