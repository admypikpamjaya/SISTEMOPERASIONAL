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

$selectedCategory = request('category') ? AssetCategory::tryFrom((string) request('category')) : null;
$selectedUnit = request('unit') ? AssetUnit::tryFrom((string) request('unit')) : null;
$activeFilterCount = collect([
    request('keyword'),
    request('category'),
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
        'icon' => 'fas fa-desktop',
        'download_url' => route('asset-management.download-template', ['category' => AssetCategory::COMPUTER->value]),
    ],
];

$templateConfigPayload = array_map(static function (array $config): array {
    return [
        'category' => $config['category']->value,
        'title' => $config['title'],
        'body' => $config['body'],
        'note' => $config['note'],
        'import_label' => $config['import_label'],
        'download_label' => $config['download_label'],
        'download_url' => $config['download_url'],
        'icon' => $config['icon'],
    ];
}, $templateConfigs);
@endphp

@section('section_name', __('app.asset.title'))

@section('content')
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap">
<style>
    .asset-dashboard {
        display: grid;
        gap: 1.25rem;
    }

    .asset-hero-card,
    .asset-info-card,
    .asset-table-card {
        border: 1px solid var(--app-border);
        border-radius: 1.2rem;
        background: var(--app-surface);
        box-shadow: var(--app-shadow);
        overflow: hidden;
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
    }

    .asset-hero-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 0.75rem;
        margin-top: 1.1rem;
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
        transition: transform 0.16s ease, box-shadow 0.16s ease, border-color 0.16s ease;
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
    }

    .asset-info-card {
        padding: 1.25rem;
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
    }

    .asset-template-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 0.7rem;
        margin-top: 1rem;
    }

    .asset-template-actions .asset-inline-btn {
        flex: 1 1 180px;
    }

    .asset-chip-list {
        display: flex;
        flex-wrap: wrap;
        gap: 0.6rem;
        margin-top: 1rem;
    }

    .asset-chip {
        display: inline-flex;
        align-items: center;
        gap: 0.42rem;
        padding: 0.55rem 0.78rem;
        border-radius: 999px;
        background: var(--app-surface-soft);
        color: var(--app-text-soft);
        font-size: 0.8rem;
        font-weight: 700;
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
    }

    .asset-toolbar-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 0.65rem;
        justify-content: flex-end;
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
                    <i class="fas fa-layer-group"></i>
                    {{ __('app.asset.quick_actions') }}
                </span>
                <h2 class="asset-hero-title">{{ __('app.asset.title') }}</h2>
                <p class="asset-hero-subtitle">
                    {{ __('app.asset.info_body') }}
                </p>

                <div class="asset-hero-actions">
                    @if($canAssetCreate)
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

                        <a href="{{ route('asset-management.register-form') }}" class="asset-hero-btn">
                            <i class="fas fa-plus-circle"></i>
                            <span>{{ __('app.asset.add_new') }}</span>
                        </a>
                    @endif

                    @foreach($templateConfigs as $templateConfig)
                        <a href="{{ $templateConfig['download_url'] }}" class="asset-hero-btn">
                            <i class="fas fa-file-download"></i>
                            <span>{{ $templateConfig['download_label'] }}</span>
                        </a>
                    @endforeach

                    <a id="download-qr-anchor" href="#" class="d-none"></a>
                    <button id="download-qr-code-button" type="button" class="asset-hero-btn">
                        <i class="fas fa-qrcode"></i>
                        <span>{{ __('app.asset.download_all_qr') }}</span>
                    </button>
                </div>
            </div>

            <div class="asset-hero-stats">
                <article class="asset-stat-card">
                    <span class="asset-stat-label">Total Data</span>
                    <span class="asset-stat-value">{{ number_format($assets->total()) }}</span>
                    <span class="asset-stat-caption">Seluruh aset yang sesuai filter saat ini.</span>
                </article>

                <article class="asset-stat-card">
                    <span class="asset-stat-label">Ditampilkan</span>
                    <span class="asset-stat-value">{{ number_format($assets->count()) }}</span>
                    <span class="asset-stat-caption">Baris aktif pada halaman ini.</span>
                </article>

                <article class="asset-stat-card">
                    <span class="asset-stat-label">Filter Aktif</span>
                    <span class="asset-stat-value">{{ $activeFilterCount }}</span>
                    <span class="asset-stat-caption">
                        {{ $selectedCategory?->label() ?? 'Semua kategori' }} | {{ $selectedUnit?->name ?? 'Semua unit' }}
                    </span>
                </article>
            </div>
        </div>
    </section>

    <section class="asset-info-grid">
        @foreach($templateConfigs as $templateConfig)
            <article class="asset-info-card">
                <span class="asset-info-icon">
                    <i class="{{ $templateConfig['icon'] }}"></i>
                </span>
                <h3>{{ $templateConfig['title'] }}</h3>
                <p>{{ $templateConfig['body'] }}</p>

                <div class="asset-chip-list">
                    <span class="asset-chip">
                        <i class="fas fa-table"></i>
                        {{ __('app.asset.multi_sheet_note') }}
                    </span>
                    <span class="asset-chip">
                        <i class="fas fa-file-excel"></i>
                        {{ __('app.asset.supported_formats') }}
                    </span>
                    <span class="asset-chip">
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
        @endforeach
    </section>

    <form class="card asset-table-card">
        <div class="card-header">
            <div class="asset-toolbar">
                <div class="asset-toolbar-head">
                    <div>
                        <h3 class="asset-toolbar-title">{{ __('app.asset.title') }}</h3>
                        <p class="asset-toolbar-subtitle">
                            Filter data, import template AC atau komputer, dan kelola QR aset dari satu halaman.
                        </p>
                    </div>

                    <div class="asset-toolbar-actions">
                        @if($canAssetCreate)
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

                        @foreach($templateConfigs as $templateConfig)
                            <a href="{{ $templateConfig['download_url'] }}" class="asset-inline-btn">
                                <i class="fas fa-file-arrow-down"></i>
                                <span>{{ $templateConfig['download_label'] }}</span>
                            </a>
                        @endforeach
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

                    <div class="asset-filter-field">
                        <label for="filter-category-select" class="asset-filter-label">{{ __('app.asset.category') }}</label>
                        <select name="category" id="filter-category-select" class="form-control">
                            <option value="">{{ __('app.asset.all_categories') }}</option>
                            @foreach (AssetCategory::cases() as $category)
                                <option value="{{ $category->value }}" @selected(request('category') == $category->value)>{{ $category->label() }}</option>
                            @endforeach
                        </select>
                    </div>

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
                                    Cari
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
                        <label class="asset-filter-label">Ringkasan</label>
                        <div class="asset-chip-list mt-0">
                            <span class="asset-chip">
                                <i class="fas fa-filter"></i>
                                {{ $activeFilterCount > 0 ? $activeFilterCount . ' filter aktif' : 'Tanpa filter' }}
                            </span>
                            <span class="asset-chip">
                                <i class="fas fa-clock"></i>
                                {{ __('app.asset.filter_summary_caption') }}
                            </span>
                            @if($activeFilterCount > 0)
                                <a href="{{ route('asset-management.index') }}" class="asset-chip">
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
                Menampilkan <strong>{{ number_format($assets->count()) }}</strong> dari
                <strong>{{ number_format($assets->total()) }}</strong> aset.
            </span>

            <span>
                Kategori: <strong>{{ $selectedCategory?->label() ?? 'Semua' }}</strong> |
                Unit: <strong>{{ $selectedUnit?->name ?? 'Semua' }}</strong>
            </span>

            <span>
                File import: <strong>{{ request('import_file') ?: 'Semua file' }}</strong>
            </span>
        </div>

        <div class="card-body pt-0">
            <div class="table-responsive">
                <table class="table table-hover app-table-compact mb-0">
                    <thead>
                        <tr>
                            <th scope="col" style="width: 52px;">
                                <input id="root-checkbox" type="checkbox">
                            </th>
                            <th scope="col">KATEGORI</th>
                            <th scope="col">KODE AKUN</th>
                            <th scope="col">LOKASI</th>
                            <th scope="col">{{ strtoupper(__('app.asset.latest_data_at')) }}</th>
                            <th scope="col">{{ strtoupper(__('app.asset.latest_import_file')) }}</th>
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
                                <td>
                                    {{ optional($asset->last_imported_at ?? $asset->updated_at)->format('d M Y H:i') }}
                                </td>
                                <td>
                                    {{ $asset->last_import_file_name ?: __('app.asset.manual_entry_label') }}
                                </td>
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
                                <td colspan="7">
                                    <div class="asset-empty-state">
                                        <i class="fas fa-inbox"></i>
                                        <h4>{{ __('app.asset.empty') }}</h4>
                                        <p>
                                            Belum ada aset yang cocok dengan filter ini. Kamu bisa tambah manual
                                            atau import memakai template AC maupun komputer yang sudah disiapkan.
                                        </p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
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
        return assetImportConfigs.find(config => config.category === category) ?? assetImportConfigs[0];
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
                        ${@json(__('app.asset.multi_sheet_note'))}
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
