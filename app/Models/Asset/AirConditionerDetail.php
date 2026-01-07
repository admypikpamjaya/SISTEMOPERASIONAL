<?php

namespace App\Models\Asset;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AirConditionerDetail extends Model
{
    use HasFactory;

    protected $primaryKey = 'asset_id';
    
    public $incrementing = false;

    protected $keyType = 'string';

    protected $guarded = [];

    protected function brand(): Attribute
    {
        return Attribute::make(
            set: fn($value) => $value ? strtoupper(trim($value)) : null
        );
    }

    public function asset()
    {
        return $this->belongsTo(Asset::class);
    }
}
