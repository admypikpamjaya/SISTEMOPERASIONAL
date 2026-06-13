<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>{{ __('app.finance.asset_depreciation_log_detail') }} #{{ $log->id }}</title>
    <style>
        body {
            font-family: DejaVu Sans, Arial, sans-serif;
            font-size: 12px;
            color: #0f172a;
        }
        h1 {
            font-size: 18px;
            margin: 0 0 4px;
            color: #1d4ed8;
        }
        .sub {
            margin: 0 0 14px;
            color: #475569;
            font-size: 11px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid #cbd5e1;
            padding: 8px 10px;
            vertical-align: top;
        }
        th {
            width: 36%;
            background: #eff6ff;
            color: #1e3a8a;
            text-align: left;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.03em;
        }
        td {
            font-size: 12px;
            font-weight: 600;
        }
    </style>
</head>
<body>
    @php
        $assetCategoryRaw = $log->asset?->category;
        if ($assetCategoryRaw instanceof \App\Enums\Asset\AssetCategory) {
            $assetCategoryLabel = $assetCategoryRaw->label();
        } elseif (is_string($assetCategoryRaw) && trim($assetCategoryRaw) !== '') {
            $assetCategoryLabel = \App\Enums\Asset\AssetCategory::tryFrom($assetCategoryRaw)?->label() ?? $assetCategoryRaw;
        } else {
            $assetCategoryLabel = '-';
        }

        $financeCategoryMeta = app(\App\Services\Finance\FinanceCategoryScopeService::class)
            ->describe($log->category_id ?? null);
    @endphp

    <h1>{{ __('app.finance.asset_depreciation_log_detail') }}</h1>
    <p class="sub">
        {{ __('app.finance.log_id') }}: #{{ $log->id }} | {{ __('app.finance.printed_at') }}: {{ now($timezone ?? config('app.timezone'))->format('d/m/Y H:i:s') }}
    </p>

    <table>
        <tbody>
            <tr>
                <th>{{ __('app.finance.calculated_at_wib') }}</th>
                <td>{{ $log->calculated_at?->timezone($timezone ?? config('app.timezone'))->format('d/m/Y H:i:s') ?? '-' }}</td>
            </tr>
            <tr>
                <th>{{ __('app.finance.asset_account_code') }}</th>
                <td>{{ $log->asset?->account_code ?? '-' }}</td>
            </tr>
            <tr>
                <th>{{ __('app.finance.asset_category_label') }}</th>
                <td>{{ $assetCategoryLabel }}</td>
            </tr>
            <tr>
                <th>{{ __('app.finance.asset_location') }}</th>
                <td>{{ $log->asset?->location ?? '-' }}</td>
            </tr>
            <tr>
                <th>Kategori Finance</th>
                <td>{{ $financeCategoryMeta['selected_name'] }}</td>
            </tr>
            <tr>
                <th>{{ __('app.finance.category_scope_label') }}</th>
                <td>{{ $financeCategoryMeta['scope_label'] }}</td>
            </tr>
            <tr>
                <th>{{ __('app.finance.benefit_period') }}</th>
                <td>{{ $periodLabel ?? '-' }}</td>
            </tr>
            <tr>
                <th>{{ __('app.finance.acquisition_cost') }}</th>
                <td>Rp {{ number_format((float) $log->acquisition_cost, 2, ',', '.') }}</td>
            </tr>
            <tr>
                <th>{{ __('app.finance.useful_life_months') }}</th>
                <td>{{ (int) $log->useful_life_months }}</td>
            </tr>
            <tr>
                <th>{{ __('app.finance.monthly_depreciation') }}</th>
                <td>Rp {{ number_format((float) $log->depreciation_per_month, 2, ',', '.') }}</td>
            </tr>
            <tr>
                <th>{{ __('app.finance.calculated_by') }}</th>
                <td>{{ $log->calculator?->name ?? '-' }}</td>
            </tr>
            <tr>
                <th>{{ __('app.finance.calculator_email') }}</th>
                <td>{{ $log->calculator?->email ?? '-' }}</td>
            </tr>
        </tbody>
    </table>
</body>
</html>
