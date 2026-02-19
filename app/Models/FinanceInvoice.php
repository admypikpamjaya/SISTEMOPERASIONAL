<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FinanceInvoice extends Model
{
    use HasFactory, HasUuids;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $table = 'finance_invoices';

    protected $fillable = [
        'invoice_no',
        'accounting_date',
        'entry_type',
        'journal_name',
        'reference',
        'status',
        'total_debit',
        'total_credit',
        'created_by',
        'updated_by',
        'posted_by',
        'posted_at',
        'meta',
    ];

    protected $casts = [
        'accounting_date' => 'date',
        'total_debit' => 'decimal:2',
        'total_credit' => 'decimal:2',
        'posted_at' => 'datetime',
        'meta' => 'array',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(FinanceInvoiceItem::class, 'invoice_id')
            ->orderBy('sort_order');
    }

    public function notes(): HasMany
    {
        return $this->hasMany(FinanceInvoiceNote::class, 'invoice_id')
            ->latest('created_at');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function poster(): BelongsTo
    {
        return $this->belongsTo(User::class, 'posted_by');
    }
}
