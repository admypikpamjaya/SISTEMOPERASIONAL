<?php

namespace App\Models;

use App\Models\Asset\Asset;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FinanceDepreciationCalculationLog extends Model
{
    use HasFactory;

    protected $table = 'finance_depreciation_calculation_logs';

    protected $fillable = [
        'asset_id',
        'period_month',
        'period_year',
        'acquisition_cost',
        'useful_life_months',
        'depreciation_per_month',
        'calculated_by',
        'calculated_at',
    ];

    protected $casts = [
        'period_month' => 'integer',
        'period_year' => 'integer',
        'acquisition_cost' => 'decimal:2',
        'useful_life_months' => 'integer',
        'depreciation_per_month' => 'decimal:2',
        'calculated_at' => 'datetime',
    ];

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class, 'asset_id');
    }

    public function calculator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'calculated_by');
    }
}
