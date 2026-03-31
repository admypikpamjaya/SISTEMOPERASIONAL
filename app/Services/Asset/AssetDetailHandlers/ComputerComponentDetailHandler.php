<?php 

namespace App\Services\Asset\AssetDetailHandlers;

use App\Contracts\Asset\AssetDetailHandler;
use App\Enums\Asset\ComputerComponent;
use App\Models\Asset\ComputerComponent as AssetComputerComponent;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ComputerComponentDetailHandler implements AssetDetailHandler
{
    public function validatePayload(array $data)
    {
        $validator = Validator::make($data, [
            'components' => ['required', 'array', 'min:1'],

            'components.*.component_type' => ['required', Rule::enum(ComputerComponent::class)],
            'components.*.brand' => ['nullable', 'string'],
            'components.*.specification' => ['nullable', 'string'],
            'components.*.serial_number' => ['nullable', 'string'],
        ],
        [
            'components.required' => 'Komponen komputer wajib diisi',
            'components.array' => 'Komponen komputer harus berupa array.',

            'components.*.component_type.required' => 'Jenis komponen wajib diisi',
            'components.*.component_type.*' => 'Jenis komponen tidak valid',
        ]);

        if ($validator->fails()) {
            throw new \Exception($validator->errors()->first(), 422);
        }

        return $validator->validate();
    }

    public function getRelationName(): string
    {
        return 'computerComponents';
    }

    public function insert(string $assetId, array $data): void
    {
        foreach($data['components'] as $component) {
            AssetComputerComponent::create(
                array_merge($component, ['asset_id' => $assetId])
            );
        }
    } 

    public function update(string $assetId, array $data): void
    {
        DB::transaction(function () use ($assetId, $data) {

            AssetComputerComponent::where('asset_id', $assetId)->delete();

            $components = $data['components'] ?? [];

            foreach ($components as $component) {

                if (
                    empty($component['brand']) &&
                    empty($component['specification']) &&
                    empty($component['serial_number'])
                ) {
                    continue;
                }

                // 4. insert ulang
                AssetComputerComponent::create([
                    'asset_id' => $assetId,
                    'component_type' => $component['component_type'],
                    'brand' => $component['brand'] ?? null,
                    'specification' => $component['specification'] ?? null,
                    'serial_number' => $component['serial_number'] ?? null,
                ]);
            }
        });
    }

    public function extractDetailFromCSV(array $row): array
    {
        $mapping = [
            ComputerComponent::MONITOR->value => [
                'brand' => 'monitor_brand',
                'spec' => 'monitor_spec',
                'sn' => 'monitor_sn'
            ],
            ComputerComponent::MOTHERBOARD->value => [
                'brand' => 'motherboard_brand',
                'spec' => 'motherboard_model',
                'sn' => 'motherboard_sn'
            ],
            ComputerComponent::PROCESSOR->value => [
                'brand' => 'processor_brand',
                'spec' => 'processor_spec',
                'sn' => 'processor_sn'
            ],
            ComputerComponent::RAM->value => [
                'brand' => 'ram_brand',
                'spec' => 'ram_spec',
                'sn' => 'ram_sn'
            ],
            ComputerComponent::STORAGE->value => [
                'brand' => 'storage_brand',
                'spec' => 'storage_spec',
                'sn' => 'storage_sn'
            ],
            ComputerComponent::GPU->value => [
                'brand' => 'gpu_brand',
                'spec' => 'gpu_spec',
                'sn' => 'gpu_sn'
            ],
            ComputerComponent::KEYBOARD_MOUSE->value => [
                'brand' => 'keyboard_mouse_brand',
                'spec' => 'keyboard_mouse_spec',
                'sn' => 'keyboard_mouse_sn'
            ],
        ];

        $components = [];

        foreach ($mapping as $type => $fields) {
            $brand = $row[$fields['brand']] ?? null;
            $spec = $row[$fields['spec']] ?? null;
            $sn = null;

            if (isset($fields['sn'])) {
                $sn = $row[$fields['sn']] ?? null;
            }

            if (empty($brand) && empty($spec) && empty($sn)) {
                continue;
            }

            $components[] = [
                'component_type' => $type,
                'brand' => $brand,
                'specification' => $spec,
                'serial_number' => $sn,
            ];
        }

        return [
            'components' => $components
        ];
    }
}