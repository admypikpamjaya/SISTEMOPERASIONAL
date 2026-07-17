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

$assetPageMode = $assetPageMode ?? 'master';
$assetPageCategory = $assetPageCategory ?? null;
$assetPageRouteName = $assetPageRouteName ?? 'asset-management.index';
$isAssetMasterPage = $assetPageMode === 'master';
$isAssetCategoryPage = $assetPageMode === 'category';
$selectedCategory = $assetPageCategory instanceof AssetCategory
    ? $assetPageCategory
    : (request('category') ? AssetCategory::tryFrom((string) request('category')) : null);
$isAcCategoryPage = $isAssetCategoryPage && $selectedCategory === AssetCategory::AC;
$isComputerCategoryPage = $isAssetCategoryPage && $selectedCategory === AssetCategory::COMPUTER;
$isVehicleCategoryPage = $isAssetCategoryPage && $selectedCategory === AssetCategory::VEHICLE;
$isElectronicCategoryPage = $isAssetCategoryPage && $selectedCategory === AssetCategory::ELECTRONIC;
$isRoomInventoryCategoryPage = $isAssetCategoryPage && $selectedCategory === AssetCategory::ROOM_INVENTORY;
$isBuildingInfrastructureCategoryPage = $isAssetCategoryPage && $selectedCategory === AssetCategory::BUILDING_INFRASTRUCTURE;
$usesTemplateSheetTable = $isAcCategoryPage
    || $isComputerCategoryPage
    || $isVehicleCategoryPage
    || $isElectronicCategoryPage
    || $isRoomInventoryCategoryPage
    || $isBuildingInfrastructureCategoryPage;
$selectedUnit = request('unit') ? AssetUnit::tryFrom((string) request('unit')) : null;
$assetFilterRoute = route($assetPageRouteName);
$assetPageCategoryLabel = $selectedCategory?->label() ?? __('app.asset.all');
$assetPageTitle = $isAssetMasterPage
    ? __('app.asset.master_data_asset_title')
    : __('app.asset.category_asset_title', ['category' => $assetPageCategoryLabel]);
$assetPageSubtitle = $isAssetMasterPage
    ? __('app.asset.master_data_asset_subtitle')
    : __('app.asset.category_asset_subtitle', ['category' => $assetPageCategoryLabel]);
$assetToolbarSubtitle = $isAssetMasterPage
    ? __('app.asset.master_read_only_note')
    : __('app.asset.category_crud_note');
$assetEyebrowLabel = $isAssetMasterPage
    ? __('app.asset.master_read_only_badge')
    : __('app.asset.quick_actions');
$assetEyebrowIcon = $isAssetMasterPage ? 'fas fa-lock' : 'fas fa-layer-group';
$canAssetCreate = !$isAssetMasterPage && $canAssetCreate;
$canAssetUpdate = !$isAssetMasterPage && $canAssetUpdate;
$canAssetDelete = !$isAssetMasterPage && $canAssetDelete;
$activeFilterCount = collect([
    request('keyword'),
    $isAssetCategoryPage ? null : request('category'),
    request('unit'),
    request('recorded_from'),
    request('recorded_until'),
    request('import_file'),
])->filter(fn ($value) => filled($value))->count();

$templateConfigs = [
    [
        'category' => AssetCategory::AC,
        'title' => __('app.asset.ac_template_title'),
        'body' => __('app.asset.ac_template_body'),
        'import_label' => __('app.asset.import_ac'),
        'download_label' => __('app.asset.download_ac_template'),
        'note' => __('app.asset.ac_import_note'),
        'sheet_note' => __('app.asset.multi_sheet_note'),
        'icon' => 'fas fa-snowflake',
        'download_url' => route('asset-management.download-template', ['category' => AssetCategory::AC->value]),
    ],
    [
        'category' => AssetCategory::COMPUTER,
        'title' => __('app.asset.computer_template_title'),
        'body' => __('app.asset.computer_template_body'),
        'import_label' => __('app.asset.import_computer'),
        'download_label' => __('app.asset.download_computer_template'),
        'note' => __('app.asset.computer_import_note'),
        'sheet_note' => __('app.asset.multi_sheet_note'),
        'icon' => 'fas fa-desktop',
        'download_url' => route('asset-management.download-template', ['category' => AssetCategory::COMPUTER->value]),
    ],
    [
        'category' => AssetCategory::BUILDING_INFRASTRUCTURE,
        'title' => __('app.asset.building_infrastructure_template_title'),
        'body' => __('app.asset.building_infrastructure_template_body'),
        'import_label' => __('app.asset.import_building_infrastructure'),
        'download_label' => __('app.asset.download_building_infrastructure_template'),
        'note' => __('app.asset.building_infrastructure_import_note'),
        'sheet_note' => __('app.asset.building_infrastructure_sheet_note'),
        'icon' => 'fas fa-building',
        'download_url' => route('asset-management.download-template', ['category' => AssetCategory::BUILDING_INFRASTRUCTURE->value]),
    ],
    [
        'category' => AssetCategory::ELECTRONIC,
        'title' => __('app.asset.electronic_template_title'),
        'body' => __('app.asset.electronic_template_body'),
        'import_label' => __('app.asset.import_electronic'),
        'download_label' => __('app.asset.download_electronic_template'),
        'note' => __('app.asset.electronic_import_note'),
        'sheet_note' => __('app.asset.electronic_sheet_note'),
        'icon' => 'fas fa-tv',
        'download_url' => route('asset-management.download-template', ['category' => AssetCategory::ELECTRONIC->value]),
    ],
    [
        'category' => AssetCategory::ROOM_INVENTORY,
        'title' => __('app.asset.room_inventory_template_title'),
        'body' => __('app.asset.room_inventory_template_body'),
        'import_label' => __('app.asset.import_room_inventory'),
        'download_label' => __('app.asset.download_room_inventory_template'),
        'note' => __('app.asset.room_inventory_import_note'),
        'sheet_note' => __('app.asset.room_inventory_sheet_note'),
        'icon' => 'fas fa-chair',
        'download_url' => route('asset-management.download-template', ['category' => AssetCategory::ROOM_INVENTORY->value]),
    ],
    [
        'category' => AssetCategory::VEHICLE,
        'title' => __('app.asset.vehicle_template_title'),
        'body' => __('app.asset.vehicle_template_body'),
        'import_label' => __('app.asset.import_vehicle'),
        'download_label' => __('app.asset.download_vehicle_template'),
        'note' => __('app.asset.vehicle_import_note'),
        'sheet_note' => __('app.asset.vehicle_sheet_note'),
        'icon' => 'fas fa-car',
        'download_url' => route('asset-management.download-template', ['category' => AssetCategory::VEHICLE->value]),
    ],
];

$templateConfigs = array_values(array_filter($templateConfigs, static function (array $config) use ($selectedCategory): bool {
    return !$selectedCategory instanceof AssetCategory || $config['category'] === $selectedCategory;
}));

$templateConfigPayload = array_map(static function (array $config): array {
    return [
        'category' => $config['category']->value,
        'title' => $config['title'],
        'body' => $config['body'],
        'note' => $config['note'],
        'sheet_note' => $config['sheet_note'],
        'import_label' => $config['import_label'],
        'download_label' => $config['download_label'],
        'download_url' => $config['download_url'],
        'icon' => $config['icon'],
    ];
}, $templateConfigs);

$formatSheetValue = static function ($value, ?string $format = null): string {
    if ($value === null || $value === '') {
        return '-';
    }

    if ($format === 'date') {
        try {
            return \Illuminate\Support\Carbon::parse($value)->format('d M Y');
        } catch (\Throwable) {
            return (string) $value;
        }
    }

    return match ($format) {
        'currency' => 'Rp ' . number_format((float) $value, 2, ',', '.'),
        'number' => number_format((float) $value, 0, ',', '.'),
        default => (string) $value,
    };
};

$acTableColumns = [
    ['label' => __('app.asset.ac_fields.no'), 'type' => 'row_number'],
    ['label' => __('app.asset.ac_fields.account_code'), 'type' => 'asset', 'field' => 'account_code'],
    ['label' => __('app.asset.ac_fields.location'), 'type' => 'asset', 'field' => 'location'],
    ['label' => __('app.asset.ac_fields.dimension'), 'field' => 'dimension'],
    ['label' => __('app.asset.ac_fields.power_rating'), 'field' => 'power_rating'],
    ['label' => __('app.asset.ac_fields.brand'), 'field' => 'brand'],
    ['label' => __('app.asset.ac_fields.serial_number'), 'type' => 'asset', 'field' => 'serial_number'],
    ['label' => __('app.asset.ac_fields.purchase_year'), 'type' => 'asset', 'field' => 'purchase_year'],
    ['label' => __('app.asset.ac_fields.purchase_price'), 'type' => 'asset', 'field' => 'purchase_price', 'format' => 'currency'],
];
$formatAcCell = static function ($asset, array $column, int $rowNumber) use ($formatSheetValue) {
    if (($column['type'] ?? null) === 'row_number') {
        return $rowNumber;
    }

    $value = ($column['type'] ?? null) === 'asset'
        ? data_get($asset, $column['field'])
        : data_get($asset->airConditionerDetail, $column['field']);

    return $formatSheetValue($value, $column['format'] ?? null);
};

$computerTableColumns = [
    ['label' => __('app.asset.computer_fields.no'), 'type' => 'row_number'],
    ['label' => __('app.asset.computer_fields.account_code'), 'type' => 'asset', 'field' => 'account_code'],
    ['label' => __('app.asset.computer_fields.location'), 'type' => 'asset', 'field' => 'location'],
    ['label' => __('app.asset.computer_fields.unit'), 'type' => 'component', 'field' => 'component_type'],
    ['label' => __('app.asset.computer_fields.brand'), 'type' => 'component', 'field' => 'brand'],
    ['label' => __('app.asset.computer_fields.power_rating'), 'type' => 'component', 'field' => 'specification'],
    ['label' => __('app.asset.computer_fields.serial_number'), 'type' => 'component', 'field' => 'serial_number'],
    ['label' => __('app.asset.computer_fields.purchase_year'), 'type' => 'asset', 'field' => 'purchase_year'],
    ['label' => __('app.asset.computer_fields.purchase_price'), 'type' => 'asset', 'field' => 'purchase_price', 'format' => 'currency'],
];
$formatComputerCell = static function ($asset, array $column, int $rowNumber) use ($formatSheetValue) {
    if (($column['type'] ?? null) === 'row_number') {
        return $rowNumber;
    }

    if (($column['type'] ?? null) === 'asset') {
        return $formatSheetValue(data_get($asset, $column['field']), $column['format'] ?? null);
    }

    $field = $column['field'] ?? null;
    $components = collect($asset->computerComponents ?? [])
        ->map(function ($component) use ($field) {
            $componentType = trim((string) data_get($component, 'component_type'));
            $value = trim((string) data_get($component, $field));

            if ($field === 'component_type') {
                return $componentType !== '' ? $componentType : null;
            }

            if ($value === '') {
                return null;
            }

            return $componentType !== '' ? "{$componentType}: {$value}" : $value;
        })
        ->filter()
        ->values();

    return $components->isNotEmpty() ? $components->implode('; ') : '-';
};

$vehicleTableColumns = [
    ['label' => __('app.asset.vehicle_fields.no'), 'type' => 'row_number'],
    ['label' => __('app.asset.vehicle_fields.asset_code'), 'type' => 'asset', 'field' => 'account_code'],
    ['label' => __('app.asset.unit'), 'type' => 'unit'],
    ['label' => __('app.asset.vehicle_fields.vehicle_type'), 'field' => 'vehicle_type'],
    ['label' => __('app.asset.vehicle_fields.vehicle_name'), 'field' => 'vehicle_name'],
    ['label' => __('app.asset.vehicle_fields.brand'), 'field' => 'brand'],
    ['label' => __('app.asset.vehicle_fields.model_type'), 'field' => 'model_type'],
    ['label' => __('app.asset.vehicle_fields.vehicle_year'), 'field' => 'vehicle_year'],
    ['label' => __('app.asset.vehicle_fields.color'), 'field' => 'color'],
    ['label' => __('app.asset.vehicle_fields.license_plate'), 'field' => 'license_plate'],
    ['label' => __('app.asset.vehicle_fields.chassis_number'), 'field' => 'chassis_number'],
    ['label' => __('app.asset.vehicle_fields.engine_number'), 'field' => 'engine_number'],
    ['label' => __('app.asset.vehicle_fields.bpkb_name'), 'field' => 'bpkb_name'],
    ['label' => __('app.asset.vehicle_fields.stnk_valid_until'), 'field' => 'stnk_valid_until', 'format' => 'date'],
    ['label' => __('app.asset.vehicle_fields.tax_valid_until'), 'field' => 'tax_valid_until', 'format' => 'date'],
    ['label' => __('app.asset.vehicle_fields.kilometer'), 'field' => 'kilometer', 'format' => 'number'],
    ['label' => __('app.asset.vehicle_fields.acquisition_date'), 'field' => 'acquisition_date', 'format' => 'date'],
    ['label' => __('app.asset.vehicle_fields.purchase_year'), 'type' => 'asset', 'field' => 'purchase_year'],
    ['label' => __('app.asset.vehicle_fields.purchase_price'), 'type' => 'asset', 'field' => 'purchase_price', 'format' => 'currency'],
    ['label' => __('app.asset.vehicle_fields.asset_account_code'), 'field' => 'asset_account_code'],
    ['label' => __('app.asset.vehicle_fields.useful_life_years'), 'field' => 'useful_life_years', 'format' => 'number'],
    ['label' => __('app.asset.vehicle_fields.accumulated_depreciation'), 'field' => 'accumulated_depreciation', 'format' => 'currency'],
    ['label' => __('app.asset.vehicle_fields.book_value'), 'field' => 'book_value', 'format' => 'currency'],
    ['label' => __('app.asset.vehicle_fields.pic'), 'field' => 'pic'],
    ['label' => __('app.asset.vehicle_fields.condition'), 'field' => 'condition'],
    ['label' => __('app.asset.vehicle_fields.status'), 'field' => 'status'],
    ['label' => __('app.asset.vehicle_fields.notes'), 'field' => 'notes'],
    ['label' => __('app.asset.vehicle_fields.source_data'), 'field' => 'source_data'],
];
$formatVehicleCell = static function ($asset, array $column, int $rowNumber) {
    if (($column['type'] ?? null) === 'row_number') {
        return $rowNumber;
    }

    if (($column['type'] ?? null) === 'unit') {
        return $asset->unit?->name ?? '-';
    }

    $value = ($column['type'] ?? null) === 'asset'
        ? data_get($asset, $column['field'])
        : data_get($asset->vehicleDetail, $column['field']);

    if ($value === null || $value === '') {
        return '-';
    }

    if (($column['format'] ?? null) === 'date') {
        try {
            return \Illuminate\Support\Carbon::parse($value)->format('d M Y');
        } catch (\Throwable) {
            return (string) $value;
        }
    }

    return match ($column['format'] ?? null) {
        'currency' => 'Rp ' . number_format((float) $value, 2, ',', '.'),
        'number' => number_format((float) $value, 0, ',', '.'),
        default => (string) $value,
    };
};
$electronicTableColumns = [
    ['label' => __('app.asset.electronic_fields.no'), 'type' => 'row_number'],
    ['label' => __('app.asset.electronic_fields.asset_code'), 'field' => 'asset_code'],
    ['label' => __('app.asset.unit'), 'type' => 'unit'],
    ['label' => __('app.asset.electronic_fields.location'), 'type' => 'asset', 'field' => 'location'],
    ['label' => __('app.asset.electronic_fields.electronic_type'), 'field' => 'electronic_type'],
    ['label' => __('app.asset.electronic_fields.asset_name'), 'field' => 'asset_name'],
    ['label' => __('app.asset.electronic_fields.brand'), 'field' => 'brand'],
    ['label' => __('app.asset.electronic_fields.model_type'), 'field' => 'model_type'],
    ['label' => __('app.asset.electronic_fields.specification'), 'field' => 'specification'],
    ['label' => __('app.asset.electronic_fields.serial_number'), 'field' => 'serial_number'],
    ['label' => __('app.asset.electronic_fields.acquisition_date'), 'field' => 'acquisition_date', 'format' => 'date'],
    ['label' => __('app.asset.electronic_fields.purchase_year'), 'type' => 'asset', 'field' => 'purchase_year'],
    ['label' => __('app.asset.electronic_fields.purchase_price'), 'type' => 'asset', 'field' => 'purchase_price', 'format' => 'currency'],
    ['label' => __('app.asset.electronic_fields.asset_account_code'), 'field' => 'asset_account_code'],
    ['label' => __('app.asset.electronic_fields.useful_life_years'), 'field' => 'useful_life_years', 'format' => 'number'],
    ['label' => __('app.asset.electronic_fields.accumulated_depreciation'), 'field' => 'accumulated_depreciation', 'format' => 'currency'],
    ['label' => __('app.asset.electronic_fields.book_value'), 'field' => 'book_value', 'format' => 'currency'],
    ['label' => __('app.asset.electronic_fields.condition'), 'field' => 'condition'],
    ['label' => __('app.asset.electronic_fields.status'), 'field' => 'status'],
    ['label' => __('app.asset.electronic_fields.pic'), 'field' => 'pic'],
    ['label' => __('app.asset.electronic_fields.notes'), 'field' => 'notes'],
    ['label' => __('app.asset.electronic_fields.source_data'), 'field' => 'source_data'],
];
$formatElectronicCell = static function ($asset, array $column, int $rowNumber) {
    if (($column['type'] ?? null) === 'row_number') {
        return $rowNumber;
    }

    if (($column['type'] ?? null) === 'unit') {
        return $asset->unit?->name ?? '-';
    }

    $value = ($column['type'] ?? null) === 'asset'
        ? data_get($asset, $column['field'])
        : data_get($asset->electronicDetail, $column['field']);

    if (($column['field'] ?? null) === 'asset_code' && ($value === null || $value === '')) {
        $value = data_get($asset, 'account_code');
    }

    if ($value === null || $value === '') {
        return '-';
    }

    if (($column['format'] ?? null) === 'date') {
        try {
            return \Illuminate\Support\Carbon::parse($value)->format('d M Y');
        } catch (\Throwable) {
            return (string) $value;
        }
    }

    return match ($column['format'] ?? null) {
        'currency' => 'Rp ' . number_format((float) $value, 2, ',', '.'),
        'number' => number_format((float) $value, 0, ',', '.'),
        default => (string) $value,
    };
};
$roomInventoryTableColumns = [
    ['label' => __('app.asset.room_inventory_fields.no'), 'type' => 'row_number'],
    ['label' => __('app.asset.room_inventory_fields.asset_code'), 'field' => 'asset_code'],
    ['label' => __('app.asset.unit'), 'type' => 'unit'],
    ['label' => __('app.asset.room_inventory_fields.location'), 'type' => 'asset', 'field' => 'location'],
    ['label' => __('app.asset.room_inventory_fields.item_type'), 'field' => 'item_type'],
    ['label' => __('app.asset.room_inventory_fields.item_name'), 'field' => 'item_name'],
    ['label' => __('app.asset.room_inventory_fields.material'), 'field' => 'material'],
    ['label' => __('app.asset.room_inventory_fields.size'), 'field' => 'size'],
    ['label' => __('app.asset.room_inventory_fields.quantity'), 'field' => 'quantity'],
    ['label' => __('app.asset.room_inventory_fields.acquisition_date'), 'field' => 'acquisition_date', 'format' => 'date'],
    ['label' => __('app.asset.room_inventory_fields.purchase_year'), 'type' => 'asset', 'field' => 'purchase_year'],
    ['label' => __('app.asset.room_inventory_fields.unit_price'), 'field' => 'unit_price', 'format' => 'currency'],
    ['label' => __('app.asset.room_inventory_fields.purchase_price'), 'type' => 'asset', 'field' => 'purchase_price', 'format' => 'currency'],
    ['label' => __('app.asset.room_inventory_fields.asset_account_code'), 'field' => 'asset_account_code'],
    ['label' => __('app.asset.room_inventory_fields.useful_life_years'), 'field' => 'useful_life_years', 'format' => 'number'],
    ['label' => __('app.asset.room_inventory_fields.accumulated_depreciation'), 'field' => 'accumulated_depreciation', 'format' => 'currency'],
    ['label' => __('app.asset.room_inventory_fields.book_value'), 'field' => 'book_value', 'format' => 'currency'],
    ['label' => __('app.asset.room_inventory_fields.condition'), 'field' => 'condition'],
    ['label' => __('app.asset.room_inventory_fields.status'), 'field' => 'status'],
    ['label' => __('app.asset.room_inventory_fields.notes'), 'field' => 'notes'],
    ['label' => __('app.asset.room_inventory_fields.source_data'), 'field' => 'source_data'],
];
$formatRoomInventoryCell = static function ($asset, array $column, int $rowNumber) {
    if (($column['type'] ?? null) === 'row_number') {
        return $rowNumber;
    }

    if (($column['type'] ?? null) === 'unit') {
        return $asset->unit?->name ?? '-';
    }

    $value = ($column['type'] ?? null) === 'asset'
        ? data_get($asset, $column['field'])
        : data_get($asset->roomInventoryDetail, $column['field']);

    if (($column['field'] ?? null) === 'asset_code' && ($value === null || $value === '')) {
        $value = data_get($asset, 'account_code');
    }

    if ($value === null || $value === '') {
        return '-';
    }

    if (($column['format'] ?? null) === 'date') {
        try {
            return \Illuminate\Support\Carbon::parse($value)->format('d M Y');
        } catch (\Throwable) {
            return (string) $value;
        }
    }

    return match ($column['format'] ?? null) {
        'currency' => 'Rp ' . number_format((float) $value, 2, ',', '.'),
        'number' => number_format((float) $value, 0, ',', '.'),
        default => (string) $value,
    };
};
$buildingInfrastructureTableColumns = [
    ['label' => __('app.asset.building_infrastructure_fields.no'), 'type' => 'row_number'],
    ['label' => __('app.asset.building_infrastructure_fields.asset_code'), 'field' => 'asset_code'],
    ['label' => __('app.asset.unit'), 'type' => 'unit'],
    ['label' => __('app.asset.building_infrastructure_fields.location'), 'type' => 'asset', 'field' => 'location'],
    ['label' => __('app.asset.building_infrastructure_fields.asset_name'), 'field' => 'asset_name'],
    ['label' => __('app.asset.building_infrastructure_fields.asset_type'), 'field' => 'asset_type'],
    ['label' => __('app.asset.building_infrastructure_fields.land_area'), 'field' => 'land_area'],
    ['label' => __('app.asset.building_infrastructure_fields.building_area'), 'field' => 'building_area'],
    ['label' => __('app.asset.building_infrastructure_fields.volume_size'), 'field' => 'volume_size'],
    ['label' => __('app.asset.building_infrastructure_fields.document_number'), 'field' => 'document_number'],
    ['label' => __('app.asset.building_infrastructure_fields.acquisition_date'), 'field' => 'acquisition_date', 'format' => 'date'],
    ['label' => __('app.asset.building_infrastructure_fields.purchase_year'), 'type' => 'asset', 'field' => 'purchase_year'],
    ['label' => __('app.asset.building_infrastructure_fields.purchase_price'), 'type' => 'asset', 'field' => 'purchase_price', 'format' => 'currency'],
    ['label' => __('app.asset.building_infrastructure_fields.asset_account_code'), 'field' => 'asset_account_code'],
    ['label' => __('app.asset.building_infrastructure_fields.useful_life_years'), 'field' => 'useful_life_years', 'format' => 'number'],
    ['label' => __('app.asset.building_infrastructure_fields.initial_accumulated_depreciation'), 'field' => 'initial_accumulated_depreciation', 'format' => 'currency'],
    ['label' => __('app.asset.building_infrastructure_fields.current_year_depreciation'), 'field' => 'current_year_depreciation', 'format' => 'currency'],
    ['label' => __('app.asset.building_infrastructure_fields.accumulated_depreciation'), 'field' => 'accumulated_depreciation', 'format' => 'currency'],
    ['label' => __('app.asset.building_infrastructure_fields.book_value'), 'field' => 'book_value', 'format' => 'currency'],
    ['label' => __('app.asset.building_infrastructure_fields.condition'), 'field' => 'condition'],
    ['label' => __('app.asset.building_infrastructure_fields.status'), 'field' => 'status'],
    ['label' => __('app.asset.building_infrastructure_fields.responsible_person'), 'field' => 'responsible_person'],
    ['label' => __('app.asset.building_infrastructure_fields.notes'), 'field' => 'notes'],
    ['label' => __('app.asset.building_infrastructure_fields.source_data'), 'field' => 'source_data'],
];
$formatBuildingInfrastructureCell = static function ($asset, array $column, int $rowNumber) {
    if (($column['type'] ?? null) === 'row_number') {
        return $rowNumber;
    }

    if (($column['type'] ?? null) === 'unit') {
        return $asset->unit?->name ?? '-';
    }

    $value = ($column['type'] ?? null) === 'asset'
        ? data_get($asset, $column['field'])
        : data_get($asset->buildingInfrastructureDetail, $column['field']);

    if (($column['field'] ?? null) === 'asset_code' && ($value === null || $value === '')) {
        $value = data_get($asset, 'account_code');
    }

    if ($value === null || $value === '') {
        return '-';
    }

    if (($column['format'] ?? null) === 'date') {
        try {
            return \Illuminate\Support\Carbon::parse($value)->format('d M Y');
        } catch (\Throwable) {
            return (string) $value;
        }
    }

    return match ($column['format'] ?? null) {
        'currency' => 'Rp ' . number_format((float) $value, 2, ',', '.'),
        'number' => number_format((float) $value, 0, ',', '.'),
        default => (string) $value,
    };
};
$sheetTableColumns = match (true) {
    $isAcCategoryPage => $acTableColumns,
    $isComputerCategoryPage => $computerTableColumns,
    $isBuildingInfrastructureCategoryPage => $buildingInfrastructureTableColumns,
    $isElectronicCategoryPage => $electronicTableColumns,
    $isRoomInventoryCategoryPage => $roomInventoryTableColumns,
    default => $vehicleTableColumns,
};
$formatSheetCell = match (true) {
    $isAcCategoryPage => $formatAcCell,
    $isComputerCategoryPage => $formatComputerCell,
    $isBuildingInfrastructureCategoryPage => $formatBuildingInfrastructureCell,
    $isElectronicCategoryPage => $formatElectronicCell,
    $isRoomInventoryCategoryPage => $formatRoomInventoryCell,
    default => $formatVehicleCell,
};
@endphp

@section('section_name', $assetPageTitle)

@section('content')
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap">
<style>
    .asset-dashboard {
        display: grid;
        gap: 1.25rem;
        min-width: 0;
    }

    .asset-hero-card,
    .asset-info-card,
    .asset-table-card {
        border: 1px solid var(--app-border);
        border-radius: 1.2rem;
        background: var(--app-surface);
        box-shadow: var(--app-shadow);
        overflow: hidden;
        min-width: 0;
    }

    .asset-hero-card {
        position: relative;
        padding: 1.5rem;
        background:
            radial-gradient(circle at top right, rgba(96, 165, 250, 0.18), transparent 28%),
            linear-gradient(135deg, rgba(255, 255, 255, 0.98), rgba(239, 246, 255, 0.96));
    }

    .asset-hero-card::after {
        content: '';
        position: absolute;
        inset: 0;
        background: linear-gradient(120deg, transparent 40%, rgba(255, 255, 255, 0.24), transparent 72%);
        pointer-events: none;
    }

    .asset-hero-inner {
        position: relative;
        z-index: 1;
        display: grid;
        grid-template-columns: minmax(0, 1.7fr) minmax(280px, 1fr);
        gap: 1rem;
        align-items: end;
        min-width: 0;
    }

    .asset-eyebrow {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.42rem 0.8rem;
        border-radius: 999px;
        background: rgba(37, 99, 235, 0.1);
        color: var(--app-accent-strong);
        font-size: 0.76rem;
        font-weight: 700;
        letter-spacing: 0.08em;
        text-transform: uppercase;
    }

    .asset-hero-title {
        margin: 0.9rem 0 0.55rem;
        font-size: 1.85rem;
        line-height: 1.15;
        font-weight: 800;
        color: var(--app-text);
    }

    .asset-hero-subtitle {
        max-width: 760px;
        margin: 0;
        color: var(--app-text-soft);
        font-size: 0.95rem;
        line-height: 1.7;
        overflow-wrap: anywhere;
    }

    .asset-hero-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 0.75rem;
        margin-top: 1.1rem;
        min-width: 0;
    }

    .asset-hero-btn,
    .asset-inline-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.55rem;
        padding: 0.78rem 1rem;
        border-radius: 0.95rem;
        border: 1px solid var(--app-border);
        background: var(--app-surface);
        color: var(--app-text);
        font-size: 0.88rem;
        font-weight: 700;
        text-decoration: none;
        max-width: 100%;
        min-width: 0;
        line-height: 1.3;
        text-align: center;
        white-space: normal;
        transition: transform 0.16s ease, box-shadow 0.16s ease, border-color 0.16s ease;
    }

    .asset-hero-btn span,
    .asset-inline-btn span {
        min-width: 0;
        overflow-wrap: anywhere;
    }

    .asset-hero-btn i,
    .asset-inline-btn i {
        flex: 0 0 auto;
    }

    .asset-hero-btn:hover,
    .asset-inline-btn:hover,
    .asset-hero-btn:focus,
    .asset-inline-btn:focus {
        color: var(--app-text);
        text-decoration: none;
        transform: translateY(-1px);
        box-shadow: 0 14px 26px rgba(37, 99, 235, 0.12);
        border-color: rgba(37, 99, 235, 0.26);
    }

    .asset-hero-btn.is-primary {
        border-color: transparent;
        background: linear-gradient(135deg, #2563eb, #3b82f6);
        color: #fff;
    }

    .asset-hero-btn.is-primary:hover,
    .asset-hero-btn.is-primary:focus {
        color: #fff;
    }

    .asset-hero-stats {
        display: grid;
        gap: 0.75rem;
    }

    .asset-stat-card {
        padding: 1rem 1.1rem;
        border-radius: 1rem;
        border: 1px solid rgba(148, 163, 184, 0.2);
        background: rgba(255, 255, 255, 0.82);
        backdrop-filter: blur(10px);
    }

    .asset-stat-label {
        display: block;
        margin-bottom: 0.28rem;
        color: var(--app-text-muted);
        font-size: 0.76rem;
        font-weight: 700;
        letter-spacing: 0.06em;
        text-transform: uppercase;
    }

    .asset-stat-value {
        display: block;
        color: var(--app-text);
        font-size: 1.35rem;
        font-weight: 800;
        line-height: 1.15;
    }

    .asset-stat-caption {
        display: block;
        margin-top: 0.3rem;
        color: var(--app-text-soft);
        font-size: 0.82rem;
    }

    .asset-info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 1rem;
        min-width: 0;
    }

    .asset-info-card {
        padding: 1.25rem;
        min-width: 0;
    }

    .asset-info-icon {
        width: 2.8rem;
        height: 2.8rem;
        border-radius: 0.95rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, rgba(37, 99, 235, 0.14), rgba(59, 130, 246, 0.22));
        color: var(--app-accent-strong);
        font-size: 1rem;
    }

    .asset-info-card h3 {
        margin: 0.9rem 0 0.42rem;
        font-size: 1.05rem;
        font-weight: 800;
        color: var(--app-text);
    }

    .asset-info-card p {
        margin: 0;
        color: var(--app-text-soft);
        font-size: 0.9rem;
        line-height: 1.7;
        overflow-wrap: anywhere;
    }

    .asset-template-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 0.7rem;
        margin-top: 1rem;
        min-width: 0;
    }

    .asset-template-actions .asset-inline-btn {
        flex: 1 1 220px;
    }

    .asset-chip-list {
        display: flex;
        flex-wrap: wrap;
        gap: 0.6rem;
        margin-top: 1rem;
        min-width: 0;
        max-width: 100%;
    }

    .asset-chip {
        display: inline-flex;
        align-items: flex-start;
        gap: 0.42rem;
        padding: 0.55rem 0.78rem;
        border-radius: 999px;
        background: var(--app-surface-soft);
        color: var(--app-text-soft);
        font-size: 0.8rem;
        font-weight: 700;
        max-width: 100%;
        min-width: 0;
        line-height: 1.35;
        overflow-wrap: anywhere;
        white-space: normal;
    }

    .asset-chip i {
        flex: 0 0 auto;
        margin-top: 0.1rem;
    }

    .asset-chip.is-wide {
        flex: 1 1 100%;
        border-radius: 0.9rem;
    }

    .asset-locked-value {
        min-height: calc(2.25rem + 2px);
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
        padding: 0.55rem 0.75rem;
        border: 1px solid var(--app-border);
        border-radius: 0.65rem;
        background: var(--app-surface-soft);
        color: var(--app-text);
        font-size: 0.88rem;
        font-weight: 700;
    }

    .asset-locked-value small {
        color: var(--app-text-muted);
        font-size: 0.76rem;
        font-weight: 700;
        white-space: nowrap;
    }

    .asset-toolbar {
        display: grid;
        gap: 1rem;
    }

    .asset-toolbar-head {
        display: flex;
        justify-content: space-between;
        gap: 1rem;
        align-items: flex-start;
    }

    .asset-toolbar-title {
        margin: 0;
        font-size: 1.15rem;
        font-weight: 800;
        color: var(--app-text);
    }

    .asset-toolbar-subtitle {
        margin: 0.28rem 0 0;
        color: var(--app-text-muted);
        font-size: 0.85rem;
        overflow-wrap: anywhere;
    }

    .asset-toolbar-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 0.65rem;
        justify-content: flex-end;
        min-width: 0;
    }

    .asset-filter-grid {
        display: grid;
        grid-template-columns: repeat(12, minmax(0, 1fr));
        gap: 0.85rem;
        align-items: end;
    }

    .asset-filter-field {
        grid-column: span 3;
    }

    .asset-filter-field.is-search {
        grid-column: span 4;
    }

    .asset-filter-label {
        display: block;
        margin-bottom: 0.42rem;
        color: var(--app-text-soft);
        font-size: 0.79rem;
        font-weight: 700;
    }

    .asset-summary-row {
        display: flex;
        flex-wrap: wrap;
        justify-content: space-between;
        gap: 0.8rem;
        padding: 0 1.25rem 1rem;
        color: var(--app-text-muted);
        font-size: 0.83rem;
    }

    .asset-summary-row strong {
        color: var(--app-text);
    }

    .asset-empty-state {
        padding: 2.2rem 1rem;
        text-align: center;
    }

    .asset-empty-state i {
        display: inline-flex;
        width: 3.25rem;
        height: 3.25rem;
        align-items: center;
        justify-content: center;
        border-radius: 1rem;
        background: var(--app-surface-soft);
        color: var(--app-accent-strong);
        font-size: 1.2rem;
    }

    .asset-empty-state h4 {
        margin: 1rem 0 0.45rem;
        color: var(--app-text);
        font-size: 1rem;
        font-weight: 800;
    }

    .asset-empty-state p {
        max-width: 440px;
        margin: 0 auto;
        color: var(--app-text-muted);
        font-size: 0.85rem;
        line-height: 1.7;
    }

    .asset-import-form-note {
        padding: 0.9rem 1rem;
        border-radius: 1rem;
        background: var(--app-surface-soft);
        border: 1px solid var(--app-border);
        color: var(--app-text-soft);
        font-size: 0.84rem;
        line-height: 1.7;
    }

    .asset-import-form-note strong {
        display: block;
        color: var(--app-text);
        font-size: 0.88rem;
        margin-bottom: 0.2rem;
    }

    .asset-table-card .card-header {
        padding-bottom: 1rem;
    }

    .asset-table-card .card-footer {
        padding-top: 1rem;
    }

    .asset-sheet-table {
        font-size: 0.78rem;
    }

    .asset-table-card .app-table-compact thead th {
        letter-spacing: 0;
        text-transform: none;
    }

    .asset-sheet-table th,
    .asset-sheet-table td {
        white-space: nowrap;
        vertical-align: middle;
    }

    @media (max-width: 991.98px) {
        .asset-hero-inner {
            grid-template-columns: 1fr;
        }

        .asset-filter-field,
        .asset-filter-field.is-search {
            grid-column: span 12;
        }

        .asset-toolbar-head {
            flex-direction: column;
        }

        .asset-toolbar-actions {
            justify-content: flex-start;
        }
    }

    @media (max-width: 575.98px) {
        .asset-hero-card,
        .asset-info-card {
            padding: 1rem;
        }

        .asset-hero-title {
            font-size: 1.45rem;
        }

        .asset-hero-btn,
        .asset-inline-btn {
            width: 100%;
        }
    }

    body.dark-mode .asset-hero-card {
        background:
            radial-gradient(circle at top right, rgba(96, 165, 250, 0.14), transparent 28%),
            linear-gradient(135deg, rgba(17, 24, 39, 0.98), rgba(15, 23, 42, 0.95));
    }

    body.dark-mode .asset-stat-card {
        background: rgba(15, 23, 42, 0.76);
        border-color: rgba(96, 165, 250, 0.12);
    }
</style>

<div class="asset-shell asset-dashboard">
    @include('shared.modal')

    <section class="asset-hero-card">
        <div class="asset-hero-inner">
            <div>
                <span class="asset-eyebrow">
                    <i class="{{ $assetEyebrowIcon }}"></i>
                    {{ $assetEyebrowLabel }}
                </span>
                <h2 class="asset-hero-title">{{ $assetPageTitle }}</h2>
                <p class="asset-hero-subtitle">
                    {{ $assetPageSubtitle }}
                </p>

                <div class="asset-hero-actions">
                    @if(!$isAssetMasterPage && $canAssetCreate)
                        @foreach($templateConfigs as $templateConfig)
                            <button
                                type="button"
                                class="asset-hero-btn is-primary js-open-asset-import"
                                data-category="{{ $templateConfig['category']->value }}"
                                data-title="{{ $templateConfig['title'] }}"
                                data-body="{{ $templateConfig['body'] }}"
                                data-note="{{ $templateConfig['note'] }}"
                                data-import-label="{{ $templateConfig['import_label'] }}"
                                data-download-label="{{ $templateConfig['download_label'] }}"
                                data-download-url="{{ $templateConfig['download_url'] }}"
                                data-icon="{{ $templateConfig['icon'] }}"
                            >
                                <i class="{{ $templateConfig['icon'] }}"></i>
                                <span>{{ $templateConfig['import_label'] }}</span>
                            </button>
                        @endforeach

                        <a href="{{ route('asset-management.register-form', $selectedCategory ? ['category' => $selectedCategory->value] : []) }}" class="asset-hero-btn">
                            <i class="fas fa-plus-circle"></i>
                            <span>{{ __('app.asset.add_new') }}</span>
                        </a>
                    @endif

                    @if(!$isAssetMasterPage)
                        @foreach($templateConfigs as $templateConfig)
                            <a href="{{ $templateConfig['download_url'] }}" class="asset-hero-btn">
                                <i class="fas fa-file-download"></i>
                                <span>{{ $templateConfig['download_label'] }}</span>
                            </a>
                        @endforeach
                    @endif

                    <a id="download-qr-anchor" href="#" class="d-none"></a>
                    <button id="download-qr-code-button" type="button" class="asset-hero-btn">
                        <i class="fas fa-qrcode"></i>
                        <span>{{ __('app.asset.download_all_qr') }}</span>
                    </button>
                </div>
            </div>

            <div class="asset-hero-stats">
                <article class="asset-stat-card">
                    <span class="asset-stat-label">{{ __('app.asset.total_data') }}</span>
                    <span class="asset-stat-value">{{ number_format($assets->total()) }}</span>
                    <span class="asset-stat-caption">{{ __('app.asset.total_data_caption') }}</span>
                </article>

                <article class="asset-stat-card">
                    <span class="asset-stat-label">{{ __('app.asset.displayed') }}</span>
                    <span class="asset-stat-value">{{ number_format($assets->count()) }}</span>
                    <span class="asset-stat-caption">{{ __('app.asset.displayed_caption') }}</span>
                </article>

                <article class="asset-stat-card">
                    <span class="asset-stat-label">{{ __('app.asset.active_filter') }}</span>
                    <span class="asset-stat-value">{{ $activeFilterCount }}</span>
                    <span class="asset-stat-caption">
                        {{ $selectedCategory?->label() ?? __('app.asset.all_categories_short') }} | {{ $selectedUnit?->name ?? __('app.asset.all_units_short') }}
                    </span>
                </article>
            </div>
        </div>
    </section>

    @if(!$isAssetMasterPage)
        <section class="asset-info-grid">
            @forelse($templateConfigs as $templateConfig)
                <article class="asset-info-card">
                    <span class="asset-info-icon">
                        <i class="{{ $templateConfig['icon'] }}"></i>
                    </span>
                    <h3>{{ $templateConfig['title'] }}</h3>
                    <p>{{ $templateConfig['body'] }}</p>

                    <div class="asset-chip-list">
                        <span class="asset-chip">
                            <i class="fas fa-table"></i>
                            {{ $templateConfig['sheet_note'] }}
                        </span>
                        <span class="asset-chip">
                            <i class="fas fa-file-excel"></i>
                            {{ __('app.asset.supported_formats') }}
                        </span>
                        <span class="asset-chip is-wide">
                            <i class="{{ $templateConfig['icon'] }}"></i>
                            {{ $templateConfig['note'] }}
                        </span>
                    </div>

                    <div class="asset-template-actions">
                        @if($canAssetCreate)
                            <button
                                type="button"
                                class="asset-inline-btn js-open-asset-import"
                                data-category="{{ $templateConfig['category']->value }}"
                                data-title="{{ $templateConfig['title'] }}"
                                data-body="{{ $templateConfig['body'] }}"
                                data-note="{{ $templateConfig['note'] }}"
                                data-import-label="{{ $templateConfig['import_label'] }}"
                                data-download-label="{{ $templateConfig['download_label'] }}"
                                data-download-url="{{ $templateConfig['download_url'] }}"
                                data-icon="{{ $templateConfig['icon'] }}"
                            >
                                <i class="{{ $templateConfig['icon'] }}"></i>
                                <span>{{ $templateConfig['import_label'] }}</span>
                            </button>
                        @endif

                        <a href="{{ $templateConfig['download_url'] }}" class="asset-inline-btn">
                            <i class="fas fa-cloud-download-alt"></i>
                            <span>{{ $templateConfig['download_label'] }}</span>
                        </a>
                    </div>
                </article>
            @empty
                <article class="asset-info-card">
                    <span class="asset-info-icon">
                        <i class="fas fa-folder-open"></i>
                    </span>
                    <h3>{{ $assetPageCategoryLabel }}</h3>
                    <p>{{ __('app.asset.category_no_template_note') }}</p>

                    <div class="asset-chip-list">
                        <span class="asset-chip">
                            <i class="fas fa-lock"></i>
                            {{ __('app.asset.category_locked') }}
                        </span>
                        <span class="asset-chip">
                            <i class="fas fa-calendar-day"></i>
                            {{ __('app.asset.filter_summary_caption') }}
                        </span>
                    </div>
                </article>
            @endforelse
        </section>
    @endif

    <form class="card asset-table-card" method="GET" action="{{ $assetFilterRoute }}">
        <div class="card-header">
            <div class="asset-toolbar">
                <div class="asset-toolbar-head">
                    <div>
                        <h3 class="asset-toolbar-title">{{ $assetPageTitle }}</h3>
                        <p class="asset-toolbar-subtitle">
                            {{ $assetToolbarSubtitle }}
                        </p>
                    </div>

                    <div class="asset-toolbar-actions">
                        @if(!$isAssetMasterPage && $canAssetCreate)
                            @foreach($templateConfigs as $templateConfig)
                                <button
                                    type="button"
                                    class="asset-inline-btn js-open-asset-import"
                                    data-category="{{ $templateConfig['category']->value }}"
                                    data-title="{{ $templateConfig['title'] }}"
                                    data-body="{{ $templateConfig['body'] }}"
                                    data-note="{{ $templateConfig['note'] }}"
                                    data-import-label="{{ $templateConfig['import_label'] }}"
                                    data-download-label="{{ $templateConfig['download_label'] }}"
                                    data-download-url="{{ $templateConfig['download_url'] }}"
                                    data-icon="{{ $templateConfig['icon'] }}"
                                >
                                    <i class="{{ $templateConfig['icon'] }}"></i>
                                    <span>{{ $templateConfig['import_label'] }}</span>
                                </button>
                            @endforeach
                        @endif

                        @if(!$isAssetMasterPage)
                            @foreach($templateConfigs as $templateConfig)
                                <a href="{{ $templateConfig['download_url'] }}" class="asset-inline-btn">
                                    <i class="fas fa-file-download"></i>
                                    <span>{{ $templateConfig['download_label'] }}</span>
                                </a>
                            @endforeach
                        @endif
                    </div>
                </div>

                <div class="asset-filter-grid">
                    <div class="asset-filter-field">
                        <label for="filter-unit-select" class="asset-filter-label">{{ __('app.asset.unit') }}</label>
                        <select name="unit" id="filter-unit-select" class="form-control">
                            <option value="">{{ __('app.asset.all_units') }}</option>
                            @foreach (AssetUnit::cases() as $unit)
                                <option value="{{ $unit->value }}" @selected(request('unit') == $unit->value)>{{ $unit->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    @if($isAssetCategoryPage && $selectedCategory)
                        <div class="asset-filter-field">
                            <label class="asset-filter-label">{{ __('app.asset.category') }}</label>
                            <div class="asset-locked-value">
                                <span>{{ $selectedCategory->label() }}</span>
                                <small>{{ __('app.asset.category_locked') }}</small>
                            </div>
                        </div>
                    @else
                        <div class="asset-filter-field">
                            <label for="filter-category-select" class="asset-filter-label">{{ __('app.asset.category') }}</label>
                            <select name="category" id="filter-category-select" class="form-control">
                                <option value="">{{ __('app.asset.all_categories') }}</option>
                                @foreach (AssetCategory::cases() as $category)
                                    <option value="{{ $category->value }}" @selected(request('category') == $category->value)>{{ $category->label() }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    <div class="asset-filter-field is-search">
                        <label for="asset-keyword-input" class="asset-filter-label">{{ __('app.asset.search_placeholder') }}</label>
                        <div class="input-group">
                            <input
                                id="asset-keyword-input"
                                type="text"
                                name="keyword"
                                value="{{ request('keyword') }}"
                                class="form-control"
                                placeholder="{{ __('app.asset.search_placeholder') }}"
                            />
                            <div class="input-group-append">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search mr-1"></i>
                                    {{ __('app.asset.search_button') }}
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="asset-filter-field">
                        <label for="asset-recorded-from-input" class="asset-filter-label">{{ __('app.asset.latest_recorded_from') }}</label>
                        <input
                            id="asset-recorded-from-input"
                            type="date"
                            name="recorded_from"
                            value="{{ request('recorded_from') }}"
                            class="form-control"
                        />
                    </div>

                    <div class="asset-filter-field">
                        <label for="asset-recorded-until-input" class="asset-filter-label">{{ __('app.asset.latest_recorded_until') }}</label>
                        <input
                            id="asset-recorded-until-input"
                            type="date"
                            name="recorded_until"
                            value="{{ request('recorded_until') }}"
                            class="form-control"
                        />
                    </div>

                    <div class="asset-filter-field">
                        <label for="asset-import-file-input" class="asset-filter-label">{{ __('app.asset.import_file_label') }}</label>
                        <input
                            id="asset-import-file-input"
                            type="text"
                            name="import_file"
                            value="{{ request('import_file') }}"
                            class="form-control"
                            placeholder="{{ __('app.asset.import_file_placeholder') }}"
                        />
                    </div>

                    <div class="asset-filter-field">
                        <label for="page-size-select" class="asset-filter-label">{{ __('app.asset.row_limit') }}</label>
                        <select
                            name="page_size"
                            id="page-size-select"
                            class="form-control"
                            onchange="this.form.submit()">
                            @foreach ([10, 25, 50, 100, 250, 500, 1000] as $size)
                                <option value="{{ $size }}" @selected((int) request('page_size', 10) === $size)>{{ $size }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="asset-filter-field">
                        <label class="asset-filter-label">{{ __('app.asset.summary') }}</label>
                        <div class="asset-chip-list mt-0">
                            <span class="asset-chip">
                                <i class="fas fa-filter"></i>
                                {{ $activeFilterCount > 0 ? __('app.asset.active_filters_count', ['count' => $activeFilterCount]) : __('app.asset.no_filter') }}
                            </span>
                            <span class="asset-chip">
                                <i class="fas fa-clock"></i>
                                {{ __('app.asset.filter_summary_caption') }}
                            </span>
                            @if($activeFilterCount > 0)
                                <a href="{{ $assetFilterRoute }}" class="asset-chip">
                                    <i class="fas fa-rotate-left"></i>
                                    {{ __('app.asset.clear_filters') }}
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="asset-summary-row">
            <span>
                {!! __('app.asset.showing_assets_summary', ['shown' => '<strong>'.number_format($assets->count()).'</strong>', 'total' => '<strong>'.number_format($assets->total()).'</strong>']) !!}
            </span>

            <span>
                {{ __('app.asset.category_summary') }}: <strong>{{ $selectedCategory?->label() ?? __('app.asset.all') }}</strong> |
                {{ __('app.asset.unit_summary') }}: <strong>{{ $selectedUnit?->name ?? __('app.asset.all') }}</strong>
            </span>

            <span>
                {{ __('app.asset.import_file_summary') }}: <strong>{{ request('import_file') ?: __('app.asset.all_files') }}</strong>
            </span>
        </div>

        <div class="card-body pt-0">
            <div class="table-responsive">
                @if($usesTemplateSheetTable)
                    <table class="table table-hover app-table-compact asset-sheet-table mb-0">
                        <thead>
                            <tr>
                                <th scope="col" style="width: 52px;">
                                    <input id="root-checkbox" type="checkbox">
                                </th>
                                @foreach($sheetTableColumns as $column)
                                    <th scope="col">{{ $column['label'] }}</th>
                                @endforeach
                                <th scope="col" class="text-center">{{ __('app.asset.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($assets as $asset)
                                <tr>
                                    <td><input class="child-checkbox" type="checkbox" value="{{ $asset->id }}"></td>
                                    @foreach($sheetTableColumns as $column)
                                        <td>{{ $formatSheetCell($asset, $column, (int) ($assets->firstItem() + $loop->parent->index)) }}</td>
                                    @endforeach
                                    <td class="text-center">
                                        <div class="app-table-actions">
                                            <a href="{{ \App\Support\AssetPublicUrl::detailUrl((string) $asset->id) }}" target="_blank" class="app-icon-btn is-info" title="{{ __('app.asset.view_detail') }}" aria-label="{{ __('app.asset.view_detail') }}">
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
                                            <a href="{{ route('asset-management.qr-code', $asset->id) }}" target="_blank" rel="noopener" class="app-icon-btn is-success" title="{{ __('app.asset.view_qr') }}" aria-label="{{ __('app.asset.view_qr') }}">
                                                <i class="fas fa-qrcode"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ count($sheetTableColumns) + 2 }}">
                                        <div class="asset-empty-state">
                                            <i class="fas fa-inbox"></i>
                                            <h4>{{ __('app.asset.empty') }}</h4>
                                            <p>
                                                {{ __('app.asset.empty_category_note', ['category' => $assetPageCategoryLabel]) }}
                                            </p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                @else
                    <table class="table table-hover app-table-compact mb-0">
                        <thead>
                            <tr>
                                <th scope="col" style="width: 52px;">
                                    <input id="root-checkbox" type="checkbox">
                                </th>
                                <th scope="col">{{ __('app.asset.category_upper') }}</th>
                                <th scope="col">{{ __('app.asset.account_code_upper') }}</th>
                                <th scope="col">{{ __('app.asset.location_upper') }}</th>
                                <th scope="col">{{ __('app.asset.price_upper') }}</th>
                                <th scope="col">{{ __('app.asset.latest_data_at') }}</th>
                                <th scope="col">{{ __('app.asset.latest_import_file') }}</th>
                                <th scope="col" class="text-center">{{ __('app.asset.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($assets as $asset)
                                <tr>
                                    <td><input class="child-checkbox" type="checkbox" value="{{ $asset->id }}"></td>
                                    <td>{{ $asset->category?->label() ?? $asset->category }}</td>
                                    <td>{{ $asset->account_code }}</td>
                                    <td>{{ $asset->location }}</td>
                                    <td>{{ $asset->purchase_price !== null ? 'Rp ' . number_format((float) $asset->purchase_price, 2, ',', '.') : '-' }}</td>
                                    <td>
                                        {{ optional($asset->last_imported_at ?? $asset->updated_at)->format('d M Y H:i') }}
                                    </td>
                                    <td>
                                        {{ $asset->last_import_file_name ?: __('app.asset.manual_entry_label') }}
                                    </td>
                                    <td class="text-center">
                                        <div class="app-table-actions">
                                            <a href="{{ \App\Support\AssetPublicUrl::detailUrl((string) $asset->id) }}" target="_blank" class="app-icon-btn is-info" title="{{ __('app.asset.view_detail') }}" aria-label="{{ __('app.asset.view_detail') }}">
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
                                            <a href="{{ route('asset-management.qr-code', $asset->id) }}" target="_blank" rel="noopener" class="app-icon-btn is-success" title="{{ __('app.asset.view_qr') }}" aria-label="{{ __('app.asset.view_qr') }}">
                                                <i class="fas fa-qrcode"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8">
                                        <div class="asset-empty-state">
                                            <i class="fas fa-inbox"></i>
                                            <h4>{{ __('app.asset.empty') }}</h4>
                                            <p>
                                                {{ $isAssetMasterPage ? __('app.asset.empty_master_note') : __('app.asset.empty_category_note', ['category' => $assetPageCategoryLabel]) }}
                                            </p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                @endif
            </div>
        </div>

        <div class="card-footer">
            <div class="d-flex flex-wrap justify-content-between align-items-center" style="gap: 0.75rem;">
                @if($canAssetDelete)
                    <button type="button" id="delete-bulk-button" class="btn btn-sm btn-danger" title="{{ __('app.asset.bulk_delete') }}">
                        <i class="fas fa-trash-alt mr-1"></i>
                        {{ __('app.asset.bulk_delete') }}
                    </button>
                @else
                    <span></span>
                @endif

                {{ $assets->appends(request()->query())->links() }}
            </div>
        </div>
    </form>
</div>
@stop

@section('js')
<script>
    const assetImportConfigs = @json($templateConfigPayload);

    function resetState()
    {
        $('#root-checkbox').prop('checked', false);
        $('.child-checkbox').prop('checked', false);
    }

    function getAssetImportConfig(category)
    {
        return assetImportConfigs.find(config => config.category === category) ?? assetImportConfigs[0] ?? null;
    }

    function constructAssetRegistrationViaFileForm(config)
    {
        return `
            <form id="asset-registration-via-file-form">
                <div class="asset-import-form-note mb-3">
                    <strong>${config.title}</strong>
                    ${config.body}
                    <div class="mt-2">
                        <a href="${config.download_url}" class="font-weight-bold">
                            ${config.download_label}
                        </a>
                    </div>
                </div>

                <input type="hidden" name="category" value="${config.category}">

                <div class="asset-import-form-note mb-3">
                    <strong>${@json(__('app.asset.selected_template'))}</strong>
                    ${config.note}
                </div>

                <div class="form-group mb-0">
                    <label for="asset-file-input">${@json(__('app.asset.choose_file'))} <span class="text-red">*</span></label>
                    <div class="custom-file">
                        <input type="file" id="asset-file-input" class="custom-file-input" name="file" accept=".xlsx,.xls,.csv" required>
                        <label class="custom-file-label" for="asset-file-input">${@json(__('app.asset.choose_file'))}</label>
                    </div>
                    <small class="form-text text-muted">
                        ${@json(__('app.asset.supported_formats'))}<br>
                        ${config.sheet_note}
                    </small>
                </div>
            </form>
        `;
    }

    $(function() {
        resetState();

        $('#filter-unit-select').on('change', function() {
            $(this).closest('form').submit();
        });

        $('#filter-category-select').on('change', function() {
            $(this).closest('form').submit();
        });

        $(document).on('click', '.js-open-asset-import', function() {
            const config = getAssetImportConfig($(this).data('category'));
            if (!config) {
                return;
            }

            const form = constructAssetRegistrationViaFileForm(config);
            const buttons = `
                <button id="register-asset-via-file-button" class="btn btn-sm btn-primary">
                    <i class="${config.icon} mr-1"></i>
                    ${config.import_label}
                </button>
            `;

            modal.show(`${@json(__('app.asset.upload_form_title'))} - ${config.title}`, form, buttons);
        });

        $('#root-checkbox').on('click', function() {
            const checkboxes = $('.child-checkbox');
            checkboxes.prop('checked', this.checked);
        });

        $(document).on('click', '#register-asset-via-file-button', async function() {
            const form = document.getElementById('asset-registration-via-file-form');
            if (!form.checkValidity()) {
                form.reportValidity();
                return;
            }

            Loading.show();
            $(this).prop('disabled', true);

            try
            {
                const formData = new FormData(form);
                const response = await Http.post(@json(route('asset-management.store-with-file')), formData);

                await Notification.success(response.message ?? 'Import aset berhasil.');
                refreshUI(150);
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

            const baseUrl = @json(route('asset-management.download-qr-code'));
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
                await Http.delete(@json(route('asset-management.bulk-delete')), { ids });
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
