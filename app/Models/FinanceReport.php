<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FinanceReport extends Model
{
    use HasFactory, HasUuids;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $table = 'finance_report_snapshots';

    protected $fillable = [
        'period_id',
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
}
