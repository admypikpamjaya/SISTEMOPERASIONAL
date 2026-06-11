<?php

namespace App\Services\Asset\AssetDetailHandlers;

use App\Contracts\Asset\AssetDetailHandler;
use App\Models\Asset\ElectronicDetail;
use Illuminate\Support\Facades\Validator;

class ElectronicDetailHandler implements AssetDetailHandler
{
    public function validatePayload(array $data)
    {
        $validator = Validator::make($data, [
            'asset_code' => ['nullable', 'string', 'max:160'],
            'electronic_type' => ['nullable', 'string', 'max:160'],
            'asset_name' => ['nullable', 'string', 'max:255'],
            'brand' => ['nullable', 'string', 'max:160'],
            'model_type' => ['nullable', 'string', 'max:160'],
            'specification' => ['nullable', 'string'],
            'serial_number' => ['nullable', 'string', 'max:160'],
            'acquisition_date' => ['nullable', 'date'],
            'asset_account_code' => ['nullable', 'string', 'max:160'],
            'useful_life_years' => ['nullable', 'integer', 'min:0'],
            'accumulated_depreciation' => ['nullable', 'numeric', 'min:0'],
            'book_value' => ['nullable', 'numeric', 'min:0'],
            'condition' => ['nullable', 'string', 'max:120'],
            'status' => ['nullable', 'string', 'max:120'],
            'pic' => ['nullable', 'string', 'max:160'],
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
        return 'electronicDetail';
    }

    public function insert(string $assetId, array $data): void
    {
        ElectronicDetail::create(array_merge($data, ['asset_id' => $assetId]));
    }

    public function update(string $assetId, array $data): void
    {
        ElectronicDetail::updateOrCreate(
            ['asset_id' => $assetId],
            $data
        );
    }

    public function extractDetailFromCsv(array $row): array
    {
        return [
            'asset_code' => $row['asset_code'] ?? null,
            'electronic_type' => $row['electronic_type'] ?? null,
            'asset_name' => $row['asset_name'] ?? null,
            'brand' => $row['brand'] ?? null,
            'model_type' => $row['model_type'] ?? null,
            'specification' => $row['specification'] ?? null,
            'serial_number' => $row['serial_number'] ?? null,
            'acquisition_date' => $row['acquisition_date'] ?? null,
            'asset_account_code' => $row['asset_account_code'] ?? null,
            'useful_life_years' => $row['useful_life_years'] ?? null,
            'accumulated_depreciation' => $row['accumulated_depreciation'] ?? null,
            'book_value' => $row['book_value'] ?? null,
            'condition' => $row['condition'] ?? null,
            'status' => $row['status'] ?? null,
            'pic' => $row['pic'] ?? null,
            'notes' => $row['notes'] ?? null,
            'source_data' => $row['source_data'] ?? null,
        ];
    }
}
