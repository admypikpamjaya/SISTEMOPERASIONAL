<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FinanceReportItem extends Model
{
    use HasFactory;

    protected $table = 'finance_report_snapshot_items';

    protected $fillable = [
        'report_snapshot_id',
        'line_code',
        'line_label',
        'amount',
        'sort_order',
        'meta',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'sort_order' => 'integer',
        'meta' => 'array',
    ];

    public function report(): BelongsTo
    {
        return $this->belongsTo(FinanceReport::class, 'report_snapshot_id');
    }
}
