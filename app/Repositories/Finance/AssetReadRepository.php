<?php

namespace App\Repositories\Finance;

use App\Models\Asset\Asset;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class AssetReadRepository
{
    public function getById(string $id): ?Asset
    {
        return Asset::query()->find($id);
    }

    public function getAll(): Collection
    {
        return Asset::query()
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function paginate(?string $keyword = null, int $page = 1, int $perPage = 20): LengthAwarePaginator
    {
        $query = Asset::query();

        if (!empty($keyword)) {
            $query->where(function ($builder) use ($keyword) {
                $builder->where('account_code', 'like', '%' . $keyword . '%')
                    ->orWhere('serial_number', 'like', '%' . $keyword . '%')
                    ->orWhere('location', 'like', '%' . $keyword . '%');
            });
        }

        return $query->orderBy('created_at', 'desc')
            ->paginate($perPage, ['*'], 'page', $page);
    }
}
