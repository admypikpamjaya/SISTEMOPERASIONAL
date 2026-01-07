<?php

namespace App\Models\Asset;

use App\Enums\Asset\AssetCategory;
use App\Services\Asset\AssetFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
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
        'category' => AssetCategory::class
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
                'account_code' => ['required', 'string', 'unique:assets,account_code'],
                'location' => ['required', 'string'],
                'serial_number' => ['nullable', 'string', 'unique:assets,serial_number'],
                'purchase_year'=> ['nullable', 'integer', 'min:2000', 'max:' . date('Y')],
            ],
            [
                'category.required' => 'Kategori aset wajib diisi.',
                'category.*' => 'Kategori aset tidak valid.',

                'account_code.required' => 'Kode akun wajib diisi.',
                'account_code.string' => 'Kode akun tidak valid.',
                'account_code.unique' => 'Kode akun sudah terdaftar.',

                'location.required' => 'Lokasi wajib diisi.',
                'location.string' => 'Lokasi tidak valid.',

                'serial_number.*' => 'Nomor seri tidak valid.',
                'serial_number.unique' => 'Nomor seri sudah terdaftar.',

                'purchase_year.integer' => 'Tahun pembelian harus berupa angka.',
                'purchase_year.min' => 'Tahun pembelian tidak valid.',
                'purchase_year.max' => 'Tahun pembelian tidak valid.',
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
        return $this->load($handler->getRelationName());
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
}
