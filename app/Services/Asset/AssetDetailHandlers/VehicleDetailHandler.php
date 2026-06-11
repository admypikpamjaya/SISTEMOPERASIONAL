<?php

namespace App\Services\Asset\AssetDetailHandlers;

use App\Contracts\Asset\AssetDetailHandler;
use App\Models\Asset\VehicleDetail;
use Illuminate\Support\Facades\Validator;

class VehicleDetailHandler implements AssetDetailHandler
{
    public function validatePayload(array $data)
    {
        $validator = Validator::make($data, [
            'vehicle_type' => ['nullable', 'string', 'max:120'],
            'vehicle_name' => ['nullable', 'string', 'max:160'],
            'brand' => ['nullable', 'string', 'max:120'],
            'model_type' => ['nullable', 'string', 'max:160'],
            'vehicle_year' => ['nullable', 'string', 'max:20'],
            'color' => ['nullable', 'string', 'max:80'],
            'license_plate' => ['nullable', 'string', 'max:80'],
            'chassis_number' => ['nullable', 'string', 'max:120'],
            'engine_number' => ['nullable', 'string', 'max:120'],
            'bpkb_name' => ['nullable', 'string', 'max:160'],
            'stnk_valid_until' => ['nullable', 'date'],
            'tax_valid_until' => ['nullable', 'date'],
            'kilometer' => ['nullable', 'integer', 'min:0'],
            'acquisition_date' => ['nullable', 'date'],
            'asset_account_code' => ['nullable', 'string', 'max:120'],
            'useful_life_years' => ['nullable', 'integer', 'min:0'],
            'accumulated_depreciation' => ['nullable', 'numeric', 'min:0'],
            'book_value' => ['nullable', 'numeric', 'min:0'],
            'pic' => ['nullable', 'string', 'max:160'],
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
        return 'vehicleDetail';
    }

    public function insert(string $assetId, array $data): void
    {
        VehicleDetail::create(array_merge($data, ['asset_id' => $assetId]));
    }

    public function update(string $assetId, array $data): void
    {
        VehicleDetail::updateOrCreate(
            ['asset_id' => $assetId],
            $data
        );
    }

    public function extractDetailFromCsv(array $row): array
    {
        return [
            'vehicle_type' => $row['vehicle_type'] ?? null,
            'vehicle_name' => $row['vehicle_name'] ?? null,
            'brand' => $row['brand'] ?? null,
            'model_type' => $row['model_type'] ?? null,
            'vehicle_year' => $row['vehicle_year'] ?? null,
            'color' => $row['color'] ?? null,
            'license_plate' => $row['license_plate'] ?? null,
            'chassis_number' => $row['chassis_number'] ?? null,
            'engine_number' => $row['engine_number'] ?? null,
            'bpkb_name' => $row['bpkb_name'] ?? null,
            'stnk_valid_until' => $row['stnk_valid_until'] ?? null,
            'tax_valid_until' => $row['tax_valid_until'] ?? null,
            'kilometer' => $row['kilometer'] ?? null,
            'acquisition_date' => $row['acquisition_date'] ?? null,
            'asset_account_code' => $row['asset_account_code'] ?? null,
            'useful_life_years' => $row['useful_life_years'] ?? null,
            'accumulated_depreciation' => $row['accumulated_depreciation'] ?? null,
            'book_value' => $row['book_value'] ?? null,
            'pic' => $row['pic'] ?? null,
            'condition' => $row['condition'] ?? null,
            'status' => $row['status'] ?? null,
            'notes' => $row['notes'] ?? null,
            'source_data' => $row['source_data'] ?? null,
        ];
    }
}
