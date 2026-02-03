@extends('layouts.app')

@php 
use App\Enums\Asset\AssetUnit;
@endphp 

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="text-center">Form Edit Aset</h3>
    </div>

    <div class="card-body p-0">
        <div class="row mb-3">
            <div class="col">
                <form id="asset-basic-information-form" class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col">
                                <div class="form-group">
                                    <label for="account_code">Kode Akun <span class="text-red">*</span></label>
                                    <input type="text" name="account_code" class="form-control" id="account_code" value="{{ $asset->accountCode }}" placeholder="Masukkan kode akun">
                                </div>
                            </div>
                            <div class="col">
                                <div class="form-group">
                                    <label for="asset_serial_number">Nomor Serial</label>
                                    <input type="text" name="asset_serial_number" class="form-control" id="asset_serial_number" value="{{ $asset->serialNumber }}" placeholder="Masukkan nomor serial">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col">
                                <div class="form-group">
                                    <label for="location">Lokasi <span class="text-red">*</span></label>
                                    <input type="text" name="location" class="form-control" id="location" value="{{ $asset->location }}" placeholder="Masukkan lokasi">
                                </div>
                            </div>
                            <div class="col">
                                <div class="form-group">
                                    <label for="location">Tahun Pembelian</label>
                                    <input type="text" name="purchase_year" class="form-control" id="purchase_year" value="{{ $asset->purchaseYear }}" placeholder="Masukkan tahun pembelian">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col">
                                <div class="form-group">
                                    <label for="unit">Unit  <span class="text-red">*</span></label>
                                    <select name="unit" id="unit" class="form-control">
                                        <option value="" disabled {{ empty($asset->unit) ? 'selected' : '' }}>
                                            -- Pilih Unit --
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
                <form id="asset-detail-form" class="card">
                    <div class="card-body">
                        @if(!empty($asset->detail))
                            @if($asset->category->value == 'AC')
                                <div class="row">
                                    <div class="col">
                                        <div class="form-group">
                                            <label for="brand">Brand <span class="text-red">*</span></label>
                                            <input type="text" name="brand" class="form-control" id="brand" value="{{ $asset->detail['brand'] }}" placeholder="Masukkan brand">
                                        </div>
                                    </div>
                                    <div class="col">
                                        <div class="form-group">
                                            <label for="dimension">Dimensi <span class="text-red">*</span></label>
                                            <input type="text" name="dimension" class="form-control" id="dimension" value="{{ $asset->detail['dimension'] }}" placeholder="Masukkan dimensi">
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col">
                                        <div class="form-group">
                                            <label for="power_rating">Voltase <span class="text-red">*</span></label>
                                            <input type="number" name="power_rating" class="form-control" id="power_rating" value="{{ $asset->detail['power_rating'] }}" placeholder="Masukkan voltase">
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @endif
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="card-footer">
        <button id="update-asset-button" class="float-right btn btn-primary" data-category="{{ $asset->category->value }}" data-asset-id="{{ $asset->id }}">
            <i class="fas fa-save"></i> 
            Simpan
        </button>
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

                for (const [key, value] of detailFormData.entries())
                    formData.append(`detail[${key}]`, value);

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