<?php

namespace App\Models\Asset;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ComputerComponent extends Model
{
    use HasFactory;

    protected $primaryKey = 'asset_id';
    
    public $incrementing = false;

    protected $keyType = 'string';

    protected $guarded = [];

    public function asset()
    {
        return $this->belongsTo(Asset::class);
    }
}
