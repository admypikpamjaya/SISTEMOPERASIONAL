<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FinanceDepreciationRun extends Model
{
    use HasFactory, HasUuids;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $table = 'finance_depreciation_runs';

    protected $fillable = [
        'period_id',
        'run_no',
        'status',
        'assets_count',
        'total_depreciation',
        'generated_by',
        'generated_at',
        'notes',
    ];

    protected $casts = [
        'run_no' => 'integer',
        'assets_count' => 'integer',
        'total_depreciation' => 'decimal:2',
        'generated_at' => 'datetime',
    ];

    public function period(): BelongsTo
    {
        return $this->belongsTo(FinancePeriod::class, 'period_id');
    }

    public function reconciliationSnapshots(): HasMany
    {
        return $this->hasMany(FinanceReconciliationSnapshot::class, 'depreciation_run_id');
    }
}
