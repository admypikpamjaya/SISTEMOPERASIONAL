<?php

namespace App\Models;

use App\Models\Asset\Asset;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FinanceAssetPolicy extends Model
{
    use HasFactory, HasUuids;

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    protected $table = 'finance_asset_policies';

    protected $fillable = [
        'asset_id',
        'revision_no',
        'method',
        'acquisition_cost',
        'residual_value',
        'useful_life_months',
        'depreciation_start_date',
        'effective_period_id',
        'notes',
        'created_by',
        'created_at',
    ];

    protected $casts = [
        'revision_no' => 'integer',
        'acquisition_cost' => 'decimal:2',
        'residual_value' => 'decimal:2',
        'useful_life_months' => 'integer',
        'depreciation_start_date' => 'date',
        'created_at' => 'datetime',
    ];

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class, 'asset_id');
    }

    public function effectivePeriod(): BelongsTo
    {
        return $this->belongsTo(FinancePeriod::class, 'effective_period_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
