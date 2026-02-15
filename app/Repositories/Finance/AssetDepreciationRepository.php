<?php

namespace App\Repositories\Finance;

use App\Models\AssetDepreciation;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class AssetDepreciationRepository
{
    public function create(array $data): AssetDepreciation
    {
        return AssetDepreciation::query()->create($data);
    }

    public function bulkInsert(array $rows): bool
    {
        return AssetDepreciation::query()->insert($rows);
    }

    public function getById(string $id): ?AssetDepreciation
    {
        return AssetDepreciation::query()->find($id);
    }

    public function getByAsset(string $assetId): Collection
    {
        return AssetDepreciation::query()
            ->where('asset_id', $assetId)
            ->orderByDesc('created_at')
            ->get();
    }

    public function getByAssetAndPeriod(string $assetId, string $periodId): ?AssetDepreciation
    {
        return AssetDepreciation::query()
            ->where('asset_id', $assetId)
            ->where('period_id', $periodId)
            ->first();
    }

    public function paginateByAsset(string $assetId, int $page = 1, int $perPage = 20): LengthAwarePaginator
    {
        return AssetDepreciation::query()
            ->where('asset_id', $assetId)
            ->orderByDesc('created_at')
            ->paginate($perPage, ['*'], 'page', $page);
    }

    public function sumDepreciationByPeriod(string $periodId): float
    {
        return (float) AssetDepreciation::query()
            ->where('period_id', $periodId)
            ->sum('depreciation_amount');
    }

    public function getLatestSequenceMonthByAsset(string $assetId): int
    {
        $sequence = AssetDepreciation::query()
            ->where('asset_id', $assetId)
            ->max('sequence_month');

        return (int) ($sequence ?? 0);
    }

    public function getLatestAccumulatedByAsset(string $assetId): float
    {
        $record = AssetDepreciation::query()
            ->where('asset_id', $assetId)
            ->orderByDesc('sequence_month')
            ->first();

        if ($record === null) {
            return 0.0;
        }

        return (float) $record->accumulated_after;
    }

    public function getLatestBookValueByAsset(string $assetId): float
    {
        $record = AssetDepreciation::query()
            ->where('asset_id', $assetId)
            ->orderByDesc('sequence_month')
            ->first();

        if ($record === null) {
            return 0.0;
        }

        return (float) $record->book_value_end;
    }
}
