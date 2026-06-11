<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FinanceReport extends Model
{
    use HasFactory, HasUuids;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $table = 'finance_report_snapshots';

    protected $fillable = [
        'period_id',
        'category_id',
        'report_type',
        'version_no',
        'reconciliation_snapshot_id',
        'summary',
        'generated_by',
        'generated_at',
        'is_read_only',
    ];

    protected $casts = [
        'summary' => 'array',
        'generated_at' => 'datetime',
        'is_read_only' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'generated_by');
    }

    public function period(): BelongsTo
    {
        return $this->belongsTo(FinancePeriod::class, 'period_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(FinanceCategory::class, 'category_id');
    }

    public function reconciliationSnapshot(): BelongsTo
    {
        return $this->belongsTo(FinanceReconciliationSnapshot::class, 'reconciliation_snapshot_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(FinanceReportItem::class, 'report_snapshot_id');
    }
}
