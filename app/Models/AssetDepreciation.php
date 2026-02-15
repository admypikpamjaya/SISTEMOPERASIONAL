<?php

namespace App\Models;

use App\Models\Asset\Asset;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssetDepreciation extends Model
{
    use HasFactory, HasUuids;

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    protected $table = 'finance_depreciation_histories';

    protected $fillable = [
        'depreciation_run_id',
        'period_id',
        'asset_id',
        'policy_id',
        'method',
        'acquisition_cost_snapshot',
        'residual_value_snapshot',
        'useful_life_months_snapshot',
        'sequence_month',
        'accumulated_before',
        'depreciation_amount',
        'accumulated_after',
        'book_value_end',
    ];

    protected $casts = [
        'acquisition_cost_snapshot' => 'decimal:2',
        'residual_value_snapshot' => 'decimal:2',
        'accumulated_before' => 'decimal:2',
        'depreciation_amount' => 'decimal:2',
        'accumulated_after' => 'decimal:2',
        'book_value_end' => 'decimal:2',
        'useful_life_months_snapshot' => 'integer',
        'sequence_month' => 'integer',
        'created_at' => 'datetime',
    ];

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class, 'asset_id');
    }
}
