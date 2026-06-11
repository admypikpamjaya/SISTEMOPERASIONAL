<?php

namespace App\Models\Asset;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VehicleDetail extends Model
{
    use HasFactory;

    protected $primaryKey = 'asset_id';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $guarded = [];

    protected $casts = [
        'stnk_valid_until' => 'date:Y-m-d',
        'tax_valid_until' => 'date:Y-m-d',
        'acquisition_date' => 'date:Y-m-d',
        'kilometer' => 'integer',
        'useful_life_years' => 'integer',
        'accumulated_depreciation' => 'decimal:2',
        'book_value' => 'decimal:2',
    ];

    protected function licensePlate(): Attribute
    {
        return Attribute::make(
            set: fn ($value) => $value ? strtoupper(trim((string) $value)) : null
        );
    }

    protected function chassisNumber(): Attribute
    {
        return Attribute::make(
            set: fn ($value) => $value ? strtoupper(trim((string) $value)) : null
        );
    }

    protected function engineNumber(): Attribute
    {
        return Attribute::make(
            set: fn ($value) => $value ? strtoupper(trim((string) $value)) : null
        );
    }

    public function asset()
    {
        return $this->belongsTo(Asset::class);
    }
}
