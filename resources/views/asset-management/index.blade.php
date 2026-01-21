@extends('layouts.app')

@php 
use App\Enums\Asset\AssetCategory;
@endphp

@section('content')
@include('shared.modal')
<div class="row">
<div class="col">
    <div class="callout callout-info">
        <h5 class="font-weight-bolder">
            📢
            Informasi Pengelolaan Aset
        </h5>
        <p>Penambahan aset dapat dilakukan dengan 2 metode, yakni pengisian formulir atau import data aset (file .csv). Apabila metode yang dipilih adalah import data, maka file harus mengikuti format yang telah ditentukan. Silakan untuk download template sesuai kategori aset <a href="https://drive.google.com/drive/folders/1_dySG9XdJB3GPiVATUzAvzjSvoDNvIBC?usp=drive_link" target="_blank" class="text-primary">di sini</a></p>
    </div>
</div>
</div>

<div class="row">
<div class="col">
<form class="card">
    <div class="card-header">
        <div class="row justify-content-between align-items-center">
            <div class="col-md-6">
                <span class="card-title">Kelola Aset</span>
            </div>
            <div class="col-md-6">
                <div class="row align-items-center">
                    <div class="col">
                        <div class="input-group input-group-sm">
                            <select name="category" id="filter-category-select" class="form-control">
                                <option value="">Semua Kategori</option>
                                @foreach (AssetCategory::cases() as $category)
                                    <option value="{{ $category->value }}" {{ request('category') == $category->value ? 'selected' : '' }}>{{ $category->name }}</option>
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
                                placeholder="Cari aset..."
                            />

                            <div class="input-group-append">
                            <button type="submit" class="btn btn-default">
                                <i class="fas fa-search"></i>
                            </button>
                            </div>
                        </div>
                    </div>
                    <div class="col-3">
                        <div class="d-flex justify-content-around">
                            <button id="toggle-asset-registration-via-file-button" type="button" class="btn btn-sm btn-primary" title="Upload File Aset">
                                <i class="fas fa-upload"></i>
                            </button>
                            <a href="{{ route('asset-management.register-form') }}" class="btn btn-sm btn-primary" title="Tambah Aset Baru">
                                <i class="fas fa-plus"></i>
                            </a>
                            <a id="download-qr-anchor" href="#" class="d-none"></a>
                            <button id="download-qr-code-button" type="button" class="btn btn-sm btn-primary" title="Download Semua QR Aset">
                                <i class="fas fa-qrcode"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div> 
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th scope="col">
                        <input id="root-checkbox" type="checkbox">
                    </th>
                    <th scope="col">KATEGORI</th>
                    <th scope="col">KODE AKUN</th>
                    <th scope="col">LOKASI</th>
                    <th scope="col">TANGGAL DIDAFTARKAN</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($assets as $asset)
                    <tr>
                        <td><input class="child-checkbox" type="checkbox" value="{{ $asset->id }}"></td>
                        <td>{{ $asset->category }}</td>
                        <td>{{ $asset->account_code }}</td>
                        <td>{{ $asset->location }}</td>
                        <td>{{ $asset->created_at->format('d M Y') }}</td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('assets.detail', $asset->id) }}" target="_blank" class="btn btn-outline-info">
                                    <div class="fas fa-eye"></div>
                                </a>
                                <a href="{{ route('asset-management.edit-form', $asset->id) }}" class="btn btn-outline-warning" >
                                    <i class="fas fa-edit"></i>
                                </a>
                                <button id="delete-asset-button" type="button" class="btn btn-outline-danger" data-url="{{ route('asset-management.delete', $asset->id) }}">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                                <a href="{{ route('asset-management.download-qr-code', ['ids' => [$asset->id]]) }}" class="btn btn-outline-info">
                                    <i class="fas fa-qrcode"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center">Tidak ada data aset</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        </div>
    </div>
    <div class="card-footer">
        {{ $assets->links() }}
    </div>
</div>
</div>
</div>
@stop

@section('js')
@if(session()->has('error'))
<script>
    Notification.error("{{ session()->get('error') }}");
</script>
@endif

<script>
    function resetState()
    {
        $('#root-checkbox').prop('checked', false);
        $('.child-checkbox').prop('checked', false);
    }

    function constructAssetRegistrationViaFileForm()
    {
        return `
            <form id="asset-registration-via-file-form">
                <div class="form-group">
                    <label for="category">Kategori Aset  <span class="text-red">*</span></label>
                    <select name="category" id="category" class="form-control">
                        <option value="" disabled selected>-- Pilih Kategori Aset --</option>
                        @foreach(AssetCategory::cases() as $category)
                            <option value="{{ $category->value }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="input-group">
                    <div class="custom-file">
                        <input type="file" id="asset-file-input" class="custom-file-input" name="file" accept=".csv">
                        <label class="custom-file-label" for="inputGroupFile01">Pilih file</label>
                    </div>
                </div>
            </form>
        `;
    }

    $(function() {
        resetState();

        $('#filter-category-select').on('change', function() {
            $(this).closest('form').submit();
        })

        $('#toggle-asset-registration-via-file-button').on('click', function() {
            const form = constructAssetRegistrationViaFileForm();
            const buttons = `
                <button id="register-asset-via-file-button" class="btn btn-sm btn-primary">Simpan</button>
            `;

            modal.show('Form Registrasi Aset Via File', form, buttons);
        });

        $('#root-checkbox').on('click', function() {
            const checkboxes = $('.child-checkbox');
            checkboxes.prop('checked', this.checked);
        });

        $(document).on('click', '#register-asset-via-file-button', async function() {
            Loading.show();
            $(this).prop('disabled', true);
            try 
            {
                const formData = new FormData(document.getElementById('asset-registration-via-file-form'));

                await Http.post("{{ route('asset-management.store-with-file') }}", formData);
                refreshUI();
            }
            catch(error)
            {
                Notification.error(error);
            }
            finally
            {
                $(this).prop('disabled', false);
                Loading.hide();
            }
        });

        $(document).on('change', '#asset-file-input', function(e) {
            const fileName = e.target.files[0]?.name ?? 'Pilih File';
            $(this).next('.custom-file-label').html(fileName);
        });

        $(document).on('click', '#delete-asset-button', async function() {
            const confirmation = await Notification.confirmation('Anda yakin ingin menghapus aset ini?');
            if(!confirmation.isConfirmed)
                return;

            Loading.show();
            try 
            {
                await Http.delete($(this).data('url'));
                refreshUI();
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

        $(document).on('click', '#download-qr-code-button', async function() {
            const ids = $('.child-checkbox:checked')
                .map((_, el) => el.value)
                .toArray();

            const baseUrl = "{{ route('asset-management.download-qr-code') }}";
            const params = new URLSearchParams();

            ids.forEach(id => params.append('ids[]', id));

            const url = params.toString()
                ? `${baseUrl}?${params.toString()}`
                : baseUrl;

            $('#download-qr-anchor')
                .attr('href', url)[0]
                .click();
        });
    });
</script>
@stop