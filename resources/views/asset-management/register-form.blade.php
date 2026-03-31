@extends('layouts.app')

@php 
use App\Enums\Asset\AssetCategory;
use App\Enums\Asset\AssetUnit;
@endphp 

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="text-center">Form Pendaftaran Aset</h3>
    </div>

    <div class="card-body p-0">
        <div class="row mb-3">
            <div class="col">
                <form id="asset-basic-information-form" class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col">
                                <div class="form-group">
                                    <label for="category">Kategori Aset  <span class="text-red">*</span></label>
                                    <select name="category" id="category" class="form-control">
                                        <option value="" disabled selected>-- Pilih Kategori Aset --</option>
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
                                    <label for="account_code">Kode Akun <span class="text-red">*</span></label>
                                    <input type="text" name="account_code" class="form-control" id="account_code" placeholder="Masukkan kode akun">
                                </div>
                            </div>
                            <div class="col">
                                <div class="form-group">
                                    <label for="asset_serial_number">Nomor Serial</label>
                                    <input type="text" name="asset_serial_number" class="form-control" id="asset_serial_number" placeholder="Masukkan nomor serial">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col">
                                <div class="form-group">
                                    <label for="unit">Unit  <span class="text-red">*</span></label>
                                    <select name="unit" id="unit" class="form-control">
                                        <option value="" disabled selected>-- Pilih Unit --</option>
                                        @foreach(AssetUnit::cases() as $unit)
                                            <option value="{{ $unit->value }}">{{ $unit->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col">
                                <div class="form-group">
                                    <label for="location">Lokasi <span class="text-red">*</span></label>
                                    <input type="text" name="location" class="form-control" id="location" placeholder="Masukkan lokasi">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col">
                                <div class="form-group">
                                    <label for="location">Tahun Pembelian</label>
                                    <input type="text" name="purchase_year" class="form-control" id="purchase_year" placeholder="Masukkan tahun pembelian">
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div id="asset-detail-form-container" class="d-none row">
            <div class="col">
                <form id="asset-detail-form" class="card">
                    <div class="card-body">
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="card-footer">
        <button id="register-asset-button" class="float-right btn btn-primary">
            <i class="fas fa-save"></i> 
            Simpan
        </button>
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
        // 'OTHER': [
        //     { label: 'Brand <span class="text-red">*</span>', name: 'brand', type: 'text' },
        //     { label: 'Dimensi <span class="text-red">*</span>', name: 'dimension', type: 'text' },
        //     { label: 'Voltase <span class="text-red">*</span>', name: 'power_rating', type: 'number', min: 1 },            
        // ]
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
        // const fields = assetDetailForm[category] || assetDetailForm.AC;

        // const chunkedFields = chunkArray(fields, 2);

        // let html = ``;
        // if(fields.length > 0)
        // {
        //     chunkedFields.forEach(chunk => {
        //         chunk.forEach(field => {
        //             html += `
        //                 <div class="col">
        //                     <div class="form-group">
        //                         <label for="${field.name}">${field.label}</label>
        //                         <input 
        //                             type="${field.type}" 
        //                             name="${field.name}" 
        //                             class="form-control" 
        //                             id="${field.name}" 
        //                             placeholder="Masukkan ${field.label.split('<')[0]}"
        //                             min="${(field.type === 'number' && field.min) ? field.min : ''}"
        //                             max="${(field.type === 'number' && field.max) ? field.max : ''}"
        //                         >
        //                     </div>
        //                 </div>
        //             `
        //         });
        //     });
        // }

        // $('#asset-detail-form').find('.card-body').html(html);

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
        } 
        else 
        {
            // existing AC logic
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
            // Loading.show();
            // $(this).prop('disabled', true);
            // try 
            // {
            //     const basicFormData = new FormData(document.getElementById('asset-basic-information-form'));
            //     const detailFormData = new FormData(document.getElementById('asset-detail-form'));

            //     const formData = new FormData();

            //     for (const [key, value] of basicFormData.entries())
            //         formData.append(key, value);

            //     for (const [key, value] of detailFormData.entries())
            //         formData.append(`detail[${key}]`, value);

            //     await Http.post("{{ route('asset-management.store') }}", formData);
            //     refreshUI();
            // }
            // catch(error)
            // {
            //     Notification.error(error);
            // }
            // finally
            // {
            //     Loading.hide();
            //     $(this).prop('disabled', false);
            // }

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

                const category = basicFormData.get('category');

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
