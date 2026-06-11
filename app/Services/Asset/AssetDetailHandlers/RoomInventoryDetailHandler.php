<?php

namespace App\Services\Asset\AssetDetailHandlers;

use App\Contracts\Asset\AssetDetailHandler;
use App\Models\Asset\RoomInventoryDetail;
use Illuminate\Support\Facades\Validator;

class RoomInventoryDetailHandler implements AssetDetailHandler
{
    public function validatePayload(array $data)
    {
        $validator = Validator::make($data, [
            'asset_code' => ['nullable', 'string', 'max:160'],
            'item_type' => ['nullable', 'string', 'max:160'],
            'item_name' => ['nullable', 'string', 'max:255'],
            'material' => ['nullable', 'string', 'max:160'],
            'size' => ['nullable', 'string', 'max:160'],
            'quantity' => ['nullable', 'string', 'max:120'],
            'acquisition_date' => ['nullable', 'date'],
            'unit_price' => ['nullable', 'numeric', 'min:0'],
            'asset_account_code' => ['nullable', 'string', 'max:160'],
            'useful_life_years' => ['nullable', 'integer', 'min:0'],
            'accumulated_depreciation' => ['nullable', 'numeric', 'min:0'],
            'book_value' => ['nullable', 'numeric', 'min:0'],
            'condition' => ['nullable', 'string', 'max:120'],
            'status' => ['nullable', 'string', 'max:120'],
            'notes' => ['nullable', 'string'],
            'source_data' => ['nullable', 'string', 'max:255'],
        ]);

        if ($validator->fails()) {
            throw new \Exception($validator->errors()->first(), 422);
        }

        return $validator->validated();
    }

    public function getRelationName(): string
    {
        return 'roomInventoryDetail';
    }

    public function insert(string $assetId, array $data): void
    {
        RoomInventoryDetail::create(array_merge($data, ['asset_id' => $assetId]));
    }

    public function update(string $assetId, array $data): void
    {
        RoomInventoryDetail::updateOrCreate(
            ['asset_id' => $assetId],
            $data
        );
    }

    public function extractDetailFromCsv(array $row): array
    {
        return [
            'asset_code' => $row['asset_code'] ?? null,
            'item_type' => $row['item_type'] ?? null,
            'item_name' => $row['item_name'] ?? null,
            'material' => $row['material'] ?? null,
            'size' => $row['size'] ?? null,
            'quantity' => $row['quantity'] ?? null,
            'acquisition_date' => $row['acquisition_date'] ?? null,
            'unit_price' => $row['unit_price'] ?? null,
            'asset_account_code' => $row['asset_account_code'] ?? null,
            'useful_life_years' => $row['useful_life_years'] ?? null,
            'accumulated_depreciation' => $row['accumulated_depreciation'] ?? null,
            'book_value' => $row['book_value'] ?? null,
            'condition' => $row['condition'] ?? null,
            'status' => $row['status'] ?? null,
            'notes' => $row['notes'] ?? null,
            'source_data' => $row['source_data'] ?? null,
        ];
    }
}
