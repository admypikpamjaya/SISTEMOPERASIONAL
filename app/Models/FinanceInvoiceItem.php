<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FinanceInvoiceItem extends Model
{
    use HasFactory;

    protected $table = 'finance_invoice_items';

    protected $fillable = [
        'invoice_id',
        'asset_category',
        'account_code',
        'partner_name',
        'label',
        'analytic_distribution',
        'debit',
        'credit',
        'sort_order',
        'meta',
    ];

    protected $casts = [
        'debit' => 'decimal:2',
        'credit' => 'decimal:2',
        'sort_order' => 'integer',
        'meta' => 'array',
    ];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(FinanceInvoice::class, 'invoice_id');
    }
}
