<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FinanceReconciliationSnapshot extends Model
{
    use HasFactory, HasUuids;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $table = 'finance_reconciliation_snapshots';

    protected $fillable = [
        'period_id',
        'depreciation_run_id',
        'income_total',
        'expense_total',
        'depreciation_total',
        'net_result',
        'generated_by',
        'generated_at',
        'notes',
    ];

    protected $casts = [
        'income_total' => 'decimal:2',
        'expense_total' => 'decimal:2',
        'depreciation_total' => 'decimal:2',
        'net_result' => 'decimal:2',
        'generated_at' => 'datetime',
    ];

    public function period(): BelongsTo
    {
        return $this->belongsTo(FinancePeriod::class, 'period_id');
    }

    public function depreciationRun(): BelongsTo
    {
        return $this->belongsTo(FinanceDepreciationRun::class, 'depreciation_run_id');
    }

    public function reports(): HasMany
    {
        return $this->hasMany(FinanceReport::class, 'reconciliation_snapshot_id');
    }
}
