<?php 

namespace App\Services\Asset\AssetDetailHandlers;

use App\Contracts\Asset\AssetDetailHandler;
use App\Models\Asset\AirConditionerDetail;
use Illuminate\Support\Facades\Validator;

class AirConditionerDetailHandler implements AssetDetailHandler
{
    public function validatePayload(array $payload)
    {
        $validator = Validator::make($payload, 
            [
                'brand'        => ['required', 'string'],
                'dimension'    => ['required', 'string', 'max:100'],
                'power_rating' => ['required', 'string', 'max:100'],
            ],
            [
                'brand.required'        => 'Merk AC wajib diisi untuk kategori AC.',
                'dimension.required'    => 'Ukuran dimensi AC wajib diisi.',
                'dimension.string'      => 'Ukuran dimensi AC tidak valid.',
                'dimension.max'         => 'Ukuran dimensi AC terlalu panjang.',

                'power_rating.required' => 'Unit / watt AC wajib diisi.',
                'power_rating.string'   => 'Unit / watt AC tidak valid.',
                'power_rating.max'      => 'Unit / watt AC terlalu panjang.',
            ]
        );

        if($validator->fails())
            throw new \Exception($validator->errors()->first(), 422);

        return $validator->validated();
    }

    public function getRelationName(): string 
    {
        return 'airConditionerDetail';
    }

    public function insert(string $assetId, array $data): void
    {
        AirConditionerDetail::create(
            array_merge($data, ['asset_id' => $assetId])
        );
    }

    public function update(string $assetId, array $data): void 
    {
        $detail = AirConditionerDetail::where('asset_id', $assetId)->first();
        if (!$detail)
            throw new \Exception('Detail AC tidak ditemukan', 404);

        $detail->update($data);
    }

    public function extractDetailFromCsv(array $row): array
    {
        return [
            'dimension' => $row['dimension'] ?? null,
            'power_rating' => $row['power_rating'] ?? null,
            'brand'     => $row['brand'] ?? null,
        ];
    }
}
