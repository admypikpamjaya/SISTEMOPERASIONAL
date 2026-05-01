@extends('layouts.app')

@php
use App\Enums\Asset\AssetCategory;
use App\Enums\Asset\AssetUnit;
use App\Enums\Portal\PortalPermission;
use App\Services\AccessControl\PermissionService;

$permissionService = app(PermissionService::class);
$canAssetCreate = $permissionService->checkAccess(auth()->user(), PortalPermission::ASSET_MANAGEMENT_CREATE->value);
$canAssetUpdate = $permissionService->checkAccess(auth()->user(), PortalPermission::ASSET_MANAGEMENT_UPDATE->value);
$canAssetDelete = $permissionService->checkAccess(auth()->user(), PortalPermission::ASSET_MANAGEMENT_DELETE->value);
@endphp

@section('content')
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap">
<div class="asset-shell">
@include('shared.modal')

<div class="row">
    <div class="col">
        <div class="callout callout-info">
            <h5 class="font-weight-bolder">
                <i class="fas fa-bullhorn mr-1"></i>
                {{ __('app.asset.info_title') }}
            </h5>
            <p class="mb-0">
                {{ __('app.asset.info_body') }}
                <a href="https://drive.google.com/drive/folders/1_dySG9XdJB3GPiVATUzAvzjSvoDNvIBC?usp=drive_link" target="_blank" class="text-primary">
                    {{ __('app.asset.info_link_label') }}
                </a>
            </p>
        </div>
    </div>
</div>

<div class="row">
    <div class="col">
        <form class="card">
            <div class="card-header">
                <div class="row justify-content-between align-items-center">
                    <div class="col-md-6">
                        <span class="card-title">{{ __('app.asset.title') }}</span>
                    </div>
                    <div class="col-md-6">
                        <div class="row align-items-center">
                            <div class="col">
                                <div class="input-group input-group-sm">
                                    <select name="unit" id="filter-unit-select" class="form-control">
                                        <option value="">{{ __('app.asset.all_units') }}</option>
                                        @foreach (AssetUnit::cases() as $unit)
                                            <option value="{{ $unit->value }}" {{ request('unit') == $unit->value ? 'selected' : '' }}>{{ $unit->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-3">
                                <div class="input-group input-group-sm">
                                    <select name="category" id="filter-category-select" class="form-control">
                                        <option value="">{{ __('app.asset.all_categories') }}</option>
                                        @foreach (AssetCategory::cases() as $category)
                                            <option value="{{ $category->value }}" {{ request('category') == $category->value ? 'selected' : '' }}>{{ $category->label() }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="input-group input-group-sm">
                                    <input
                                        type="text"
                                        name="keyword"
                                        value="{{ request('keyword') }}"
                                        class="form-control float-right"
                                        placeholder="{{ __('app.asset.search_placeholder') }}"
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
                                    @if($canAssetCreate)
                                        <button id="toggle-asset-registration-via-file-button" type="button" class="btn btn-sm btn-primary" title="{{ __('app.asset.upload_file') }}">
                                            <i class="fas fa-upload"></i>
                                        </button>
                                        <a href="{{ route('asset-management.register-form') }}" class="btn btn-sm btn-primary" title="{{ __('app.asset.add_new') }}">
                                            <i class="fas fa-plus"></i>
                                        </a>
                                    @endif
                                    <a id="download-qr-anchor" href="#" class="d-none"></a>
                                    <button id="download-qr-code-button" type="button" class="btn btn-sm btn-primary" title="{{ __('app.asset.download_all_qr') }}">
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
                    <table class="table table-hover app-table-compact mb-0">
                        <thead>
                            <tr>
                                <th scope="col">
                                    <input id="root-checkbox" type="checkbox">
                                </th>
                                <th scope="col">KATEGORI</th>
                                <th scope="col">KODE AKUN</th>
                                <th scope="col">LOKASI</th>
                                <th scope="col">TANGGAL DIDAFTARKAN</th>
                                <th scope="col" class="text-center">{{ strtoupper(__('app.asset.actions')) }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($assets as $asset)
                                <tr>
                                    <td><input class="child-checkbox" type="checkbox" value="{{ $asset->id }}"></td>
                                    <td>{{ $asset->category?->label() ?? $asset->category }}</td>
                                    <td>{{ $asset->account_code }}</td>
                                    <td>{{ $asset->location }}</td>
                                    <td>{{ $asset->created_at->format('d M Y') }}</td>
                                    <td class="text-center">
                                        <div class="app-table-actions">
                                            <a href="{{ route('assets.detail', $asset->id) }}" target="_blank" class="app-icon-btn is-info" title="{{ __('app.asset.view_detail') }}" aria-label="{{ __('app.asset.view_detail') }}">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            @if($canAssetUpdate)
                                                <a href="{{ route('asset-management.edit-form', $asset->id) }}" class="app-icon-btn is-warning" title="{{ __('app.asset.edit_asset') }}" aria-label="{{ __('app.asset.edit_asset') }}">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                            @endif
                                            @if($canAssetDelete)
                                                <button id="delete-asset-button" type="button" class="app-icon-btn is-danger" data-url="{{ route('asset-management.delete', $asset->id) }}" title="{{ __('app.asset.delete_asset') }}" aria-label="{{ __('app.asset.delete_asset') }}">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            @endif
                                            <a href="{{ route('asset-management.download-qr-code', ['ids' => [$asset->id]]) }}" class="app-icon-btn is-success" title="{{ __('app.asset.download_qr') }}" aria-label="{{ __('app.asset.download_qr') }}">
                                                <i class="fas fa-qrcode"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center">{{ __('app.asset.empty') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    @if($canAssetDelete)
                        <button type="button" id="delete-bulk-button" class="btn btn-sm btn-danger" title="{{ __('app.asset.bulk_delete') }}">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    @else
                        <span></span>
                    @endif

                    <div class="form-group mb-0">
                        <label for="page-size-select">{{ __('app.asset.row_limit') }}</label>
                        <div class="input-group input-group-sm" style="width: 90px;">
                            <select name="page_size"
                                    id="page-size-select"
                                    class="form-control"
                                    onchange="this.form.submit()">

                                @foreach ([10, 25, 50, 100, 250, 500, 1000] as $size)
                                    <option value="{{ $size }}"
                                        {{ request('page_size', 10) == $size ? 'selected' : '' }}>
                                        {{ $size }}
                                    </option>
                                @endforeach

                            </select>
                        </div>
                    </div>
                </div>
                {{ $assets->appends(request()->query())->links() }}
            </div>
        </form>
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
                    <label for="category">{{ __('app.asset.category') }}  <span class="text-red">*</span></label>
                    <select name="category" id="category" class="form-control">
                        <option value="" disabled selected>{{ __('app.asset.choose_category') }}</option>
                        @foreach(AssetCategory::cases() as $category)
                            <option value="{{ $category->value }}">{{ $category->label() }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="input-group">
                    <div class="custom-file">
                        <input type="file" id="asset-file-input" class="custom-file-input" name="file" accept=".csv">
                        <label class="custom-file-label" for="inputGroupFile01">{{ __('app.asset.choose_file') }}</label>
                    </div>
                </div>
            </form>
        `;
    }

    $(function() {
        resetState();

        $('#filter-unit-select').on('change', function() {
            $(this).closest('form').submit();
        })

        $('#filter-category-select').on('change', function() {
            $(this).closest('form').submit();
        })

        $('#toggle-asset-registration-via-file-button').on('click', function() {
            const form = constructAssetRegistrationViaFileForm();
            const buttons = `
                <button id="register-asset-via-file-button" class="btn btn-sm btn-primary">{{ __('app.asset.save') }}</button>
            `;

            modal.show(@json(__('app.asset.upload_form_title')), form, buttons);
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
            const fileName = e.target.files[0]?.name ?? @json(__('app.asset.choose_file'));
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

        $(document).on('click', '#delete-bulk-button', async function() {
            const ids = $('.child-checkbox:checked')
                .map((_, el) => el.value)
                .toArray();

            if(ids.length === 0)
                return Notification.error('Anda belum memilih aset');

            const confirmation = await Notification.confirmation(`Anda yakin ingin menghapus total ${ids.length} aset ini?`);
            if(!confirmation.isConfirmed)
                return;

            Loading.show();
            try
            {
                await Http.delete("{{ route('asset-management.bulk-delete') }}", { ids });
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
    });
</script>
@stop
