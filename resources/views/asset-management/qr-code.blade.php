@extends('layouts.app')

@php
    use Illuminate\Support\Str;

    $basicFields = [
        __('app.asset.category') => $asset->category->label(),
        __('app.asset.account_code') => $asset->accountCode,
        __('app.asset.serial_number') => $asset->serialNumber ?: '-',
        __('app.asset.unit') => $asset->unit?->name ?? '-',
        __('app.asset.location') => $asset->location,
        __('app.asset.purchase_year') => $asset->purchaseYear ?: '-',
        __('app.asset.purchase_price') => $asset->purchasePrice !== null
            ? 'Rp ' . number_format((float) $asset->purchasePrice, 2, ',', '.')
            : '-',
    ];

    $normalizeDetailValue = static function (mixed $value): string {
        if ($value === null || $value === '') {
            return '-';
        }

        if (is_bool($value)) {
            return $value ? 'Ya' : 'Tidak';
        }

        if (is_array($value)) {
            return collect($value)
                ->filter(fn ($item) => $item !== null && $item !== '')
                ->implode(', ') ?: '-';
        }

        return (string) $value;
    };

    $detail = $asset->detail ?? [];
    $isListDetail = is_array($detail)
        && $detail !== []
        && array_keys($detail) === range(0, count($detail) - 1);
    $detailBlocks = [];

    if ($isListDetail) {
        foreach ($detail as $index => $item) {
            $detailBlocks[] = [
                'title' => __('app.asset.detail_item', ['number' => $index + 1]),
                'items' => is_array($item) ? $item : ['value' => $item],
            ];
        }
    } elseif (is_array($detail) && $detail !== []) {
        $detailBlocks[] = [
            'title' => __('app.maintenance.asset_detail_info'),
            'items' => $detail,
        ];
    }
@endphp

@section('content')
<style>
    .asset-qr-page {
        font-family: 'Plus Jakarta Sans', 'Nunito', sans-serif;
        color: var(--text, #1f2937);
    }
    .asset-qr-hero {
        display: grid;
        grid-template-columns: minmax(240px, 360px) minmax(0, 1fr);
        gap: 20px;
        align-items: stretch;
    }
    .asset-qr-card {
        background: var(--card, #fff);
        border: 1px solid rgba(148, 163, 184, .24);
        border-radius: 8px;
        box-shadow: 0 18px 50px rgba(15, 23, 42, .08);
        overflow: hidden;
    }
    .asset-qr-panel {
        padding: 22px;
    }
    .asset-qr-box {
        display: flex;
        align-items: center;
        justify-content: center;
        min-height: 320px;
        padding: 22px;
        background: #f8fafc;
        border-bottom: 1px solid rgba(148, 163, 184, .24);
    }
    .asset-qr-box svg {
        width: min(100%, 280px);
        height: auto;
        display: block;
        background: #fff;
        border-radius: 8px;
        padding: 12px;
    }
    .asset-qr-title {
        margin: 0;
        font-size: 1.45rem;
        line-height: 1.2;
        font-weight: 800;
    }
    .asset-qr-subtitle {
        margin: 8px 0 0;
        color: #64748b;
        font-size: .92rem;
    }
    .asset-qr-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        margin-top: 18px;
    }
    .asset-qr-btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        height: 40px;
        padding: 0 14px;
        border-radius: 8px;
        font-weight: 700;
        border: 1px solid rgba(148, 163, 184, .35);
        color: #0f172a;
        background: #fff;
        text-decoration: none;
    }
    .asset-qr-btn.is-primary {
        color: #fff;
        background: #2563eb;
        border-color: #2563eb;
    }
    .asset-qr-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 12px;
        margin-top: 20px;
    }
    .asset-qr-field {
        padding: 13px 14px;
        border: 1px solid rgba(148, 163, 184, .22);
        border-radius: 8px;
        background: rgba(248, 250, 252, .74);
    }
    .asset-qr-label {
        display: block;
        color: #64748b;
        font-size: .72rem;
        font-weight: 800;
        letter-spacing: .03em;
        text-transform: uppercase;
    }
    .asset-qr-value {
        display: block;
        margin-top: 5px;
        font-size: .92rem;
        font-weight: 700;
        overflow-wrap: anywhere;
    }
    .asset-qr-section {
        margin-top: 18px;
    }
    .asset-qr-section-title {
        display: flex;
        align-items: center;
        gap: 8px;
        margin: 0 0 12px;
        font-size: 1rem;
        font-weight: 800;
    }
    .asset-qr-url {
        margin-top: 14px;
        padding: 12px;
        border-radius: 8px;
        background: #eff6ff;
        color: #1d4ed8;
        font-size: .82rem;
        font-weight: 700;
        overflow-wrap: anywhere;
    }
    body.dark-mode .asset-qr-card,
    body.dark-mode .asset-qr-btn {
        background: #111827;
        color: #e5e7eb;
        border-color: rgba(148, 163, 184, .24);
    }
    body.dark-mode .asset-qr-box,
    body.dark-mode .asset-qr-field {
        background: #0b1220;
    }
    body.dark-mode .asset-qr-url {
        background: rgba(37, 99, 235, .18);
        color: #bfdbfe;
    }
    @media (max-width: 900px) {
        .asset-qr-hero,
        .asset-qr-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="asset-qr-page">
    <div class="asset-qr-hero">
        <section class="asset-qr-card">
            <div class="asset-qr-box" aria-label="{{ __('app.asset.qr_image') }}">
                {!! $qr_svg !!}
            </div>
            <div class="asset-qr-panel">
                <div class="asset-qr-label">{{ __('app.asset.qr_target_url') }}</div>
                <div class="asset-qr-url">{{ $public_url }}</div>
                <div class="asset-qr-actions">
                    <a href="{{ $public_url }}" target="_blank" rel="noopener" class="asset-qr-btn is-primary">
                        <i class="fas fa-external-link-alt"></i>
                        <span>{{ __('app.asset.open_public_detail') }}</span>
                    </a>
                    <a href="{{ route('asset-management.download-qr-code', ['ids' => [$asset->id]]) }}" class="asset-qr-btn">
                        <i class="fas fa-download"></i>
                        <span>{{ __('app.asset.download_qr') }}</span>
                    </a>
                    <a href="{{ route('asset-management.qr-code.pdf', $asset->id) }}" class="asset-qr-btn">
                        <i class="far fa-file-pdf"></i>
                        <span>{{ __('app.asset.download_qr_pdf') }}</span>
                    </a>
                </div>
            </div>
        </section>

        <section class="asset-qr-card asset-qr-panel">
            <div>
                <h1 class="asset-qr-title">{{ __('app.asset.qr_detail_title') }}</h1>
                <p class="asset-qr-subtitle">{{ __('app.asset.qr_detail_subtitle') }}</p>
            </div>

            <div class="asset-qr-grid">
                @foreach($basicFields as $label => $value)
                    <div class="asset-qr-field">
                        <span class="asset-qr-label">{{ $label }}</span>
                        <span class="asset-qr-value">{{ $value }}</span>
                    </div>
                @endforeach
            </div>

            @foreach($detailBlocks as $block)
                <div class="asset-qr-section">
                    <h2 class="asset-qr-section-title">
                        <i class="fas fa-clipboard-list"></i>
                        <span>{{ $block['title'] }}</span>
                    </h2>
                    <div class="asset-qr-grid">
                        @foreach($block['items'] as $key => $value)
                            @continue(in_array($key, ['id', 'asset_id', 'created_at', 'updated_at'], true))
                            <div class="asset-qr-field">
                                <span class="asset-qr-label">{{ Str::headline((string) $key) }}</span>
                                <span class="asset-qr-value">{{ $normalizeDetailValue($value) }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </section>
    </div>
</div>
@endsection
