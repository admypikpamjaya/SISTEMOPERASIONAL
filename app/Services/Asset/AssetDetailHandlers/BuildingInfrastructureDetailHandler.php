<?php

namespace App\Services\Asset\AssetDetailHandlers;

use App\Contracts\Asset\AssetDetailHandler;
use App\Models\Asset\BuildingInfrastructureDetail;
use Illuminate\Support\Facades\Validator;

class BuildingInfrastructureDetailHandler implements AssetDetailHandler
{
    public function validatePayload(array $data)
    {
        $validator = Validator::make($data, [
            'asset_code' => ['nullable', 'string', 'max:160'],
            'asset_name' => ['nullable', 'string', 'max:255'],
            'asset_type' => ['nullable', 'string', 'max:160'],
            'land_area' => ['nullable', 'string', 'max:160'],
            'building_area' => ['nullable', 'string', 'max:160'],
            'volume_size' => ['nullable', 'string', 'max:160'],
            'document_number' => ['nullable', 'string', 'max:160'],
            'acquisition_date' => ['nullable', 'date'],
            'asset_account_code' => ['nullable', 'string', 'max:160'],
            'useful_life_years' => ['nullable', 'integer', 'min:0'],
            'initial_accumulated_depreciation' => ['nullable', 'numeric', 'min:0'],
            'current_year_depreciation' => ['nullable', 'numeric', 'min:0'],
            'accumulated_depreciation' => ['nullable', 'numeric', 'min:0'],
            'book_value' => ['nullable', 'numeric', 'min:0'],
            'condition' => ['nullable', 'string', 'max:120'],
            'status' => ['nullable', 'string', 'max:120'],
            'responsible_person' => ['nullable', 'string', 'max:160'],
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
        return 'buildingInfrastructureDetail';
    }

    public function insert(string $assetId, array $data): void
    {
        BuildingInfrastructureDetail::create(array_merge($data, ['asset_id' => $assetId]));
    }

    public function update(string $assetId, array $data): void
    {
        BuildingInfrastructureDetail::updateOrCreate(
            ['asset_id' => $assetId],
            $data
        );
    }

    public function extractDetailFromCsv(array $row): array
    {
        return [
            'asset_code' => $row['asset_code'] ?? null,
            'asset_name' => $row['asset_name'] ?? null,
            'asset_type' => $row['asset_type'] ?? null,
            'land_area' => $row['land_area'] ?? null,
            'building_area' => $row['building_area'] ?? null,
            'volume_size' => $row['volume_size'] ?? null,
            'document_number' => $row['document_number'] ?? null,
            'acquisition_date' => $row['acquisition_date'] ?? null,
            'asset_account_code' => $row['asset_account_code'] ?? null,
            'useful_life_years' => $row['useful_life_years'] ?? null,
            'initial_accumulated_depreciation' => $row['initial_accumulated_depreciation'] ?? null,
            'current_year_depreciation' => $row['current_year_depreciation'] ?? null,
            'accumulated_depreciation' => $row['accumulated_depreciation'] ?? null,
            'book_value' => $row['book_value'] ?? null,
            'condition' => $row['condition'] ?? null,
            'status' => $row['status'] ?? null,
            'responsible_person' => $row['responsible_person'] ?? null,
            'notes' => $row['notes'] ?? null,
            'source_data' => $row['source_data'] ?? null,
        ];
    }
}
