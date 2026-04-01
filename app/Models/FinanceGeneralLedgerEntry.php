<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FinanceGeneralLedgerEntry extends Model
{
    use HasFactory, HasUuids;

    public const ROW_TYPE_OPENING = 'OPENING';
    public const ROW_TYPE_ENTRY = 'ENTRY';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $table = 'finance_general_ledger_entries';

    protected $fillable = [
        'batch_id',
        'row_type',
        'entry_date',
        'account_code',
        'account_name',
        'transaction_no',
        'communication',
        'partner_name',
        'currency',
        'label',
        'reference',
        'analytic_distribution',
        'opening_balance',
        'debit',
        'credit',
        'balance_amount',
        'sort_order',
        'sheet_row_number',
        'is_manual',
        'meta',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'entry_date' => 'date',
        'opening_balance' => 'decimal:2',
        'debit' => 'decimal:2',
        'credit' => 'decimal:2',
        'balance_amount' => 'decimal:2',
        'sort_order' => 'integer',
        'sheet_row_number' => 'integer',
        'is_manual' => 'boolean',
        'meta' => 'array',
    ];

    public function batch(): BelongsTo
    {
        return $this->belongsTo(FinanceGeneralLedgerBatch::class, 'batch_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
