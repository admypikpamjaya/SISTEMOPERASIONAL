<?php

namespace App\Models\Asset;

use App\Enums\Asset\AssetCategory;
use App\Enums\Asset\AssetUnit;
use App\Models\AssetDepreciation;
use App\Models\Log\MaintenanceLog;
use App\Services\Asset\AssetFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class Asset extends Model
{
    use HasFactory, HasUuids;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $guarded = ['id'];

    protected $casts = [
        'category' => AssetCategory::class,
        'unit' => AssetUnit::class
    ];

    protected function serialNumber(): Attribute
    {
        return Attribute::make(
            set: fn($value) => $value ? strtoupper(trim($value)) : null
        );
    }

    protected function location(): Attribute
    {
        return Attribute::make(
            set: fn($value) => $value ? strtoupper(trim($value)) : null
        );
    }

    public static function validateRegistrationPayload(array $data)
    {
        $validator = Validator::make($data, 
            [
                'category' => ['required', Rule::enum(AssetCategory::class)], 
                'unit' => ['required', Rule::enum(AssetUnit::class)],
                'account_code' => ['required', 'string', 'unique:assets,account_code'],
                'location' => ['required', 'string'],
                'serial_number' => ['nullable', 'string', 'unique:assets,serial_number'],
                'purchase_year'=> ['nullable', 'string'],
            ],
            [
                'category.required' => 'Kategori aset wajib diisi.',
                'category.*' => 'Kategori aset tidak valid.',

                'account_code.required' => 'Kode akun wajib diisi.',
                'account_code.string' => 'Kode akun tidak valid.',
                'account_code.unique' => 'Kode akun sudah terdaftar.',

                'unit.required' => 'Unit wajib diisi.',
                'unit.*' => 'Unit tidak valid.',

                'location.required' => 'Lokasi wajib diisi.',
                'location.string' => 'Lokasi tidak valid.',

                'serial_number.*' => 'Nomor seri tidak valid.',
                'serial_number.unique' => 'Nomor seri sudah terdaftar.',

                'purchase_year.string' => 'Input tahun pembelian harus berupa teks.',
            ]
        );

        if($validator->fails())
            throw new \Exception($validator->errors()->first(), 422);

        return $validator->validated();
    }

    public function airConditionerDetail()
    {
        return $this->hasOne(AirConditionerDetail::class);
    }

    public function loadWithRelation()
    {
        $handler = AssetFactory::createHandler($this->category);
        return $this->load([$handler->getRelationName(), 'maintenanceLogs.maintenanceDocumentations']);
    }

    public function generateQRCode()
    {
        $assetId = $this->id;
        $uniqueId = Str::uuid()->toString();
        $qr = QrCode::size(512)
            ->format('svg')
            ->generate(route('assets.detail', ['id' => $assetId]));

        $filePath = "asset_qr_codes/{$assetId}-{$uniqueId}.svg";
        Storage::disk('public')->put($filePath, $qr);

        return $filePath;
    }

    public function maintenanceLogs(): HasMany
    {
        return $this->hasMany(MaintenanceLog::class, 'asset_id');
    }

    public function assetDepreciations(): HasMany
    {
        return $this->hasMany(AssetDepreciation::class, 'asset_id');
    }
}
