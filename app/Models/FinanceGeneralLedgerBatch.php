<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FinanceGeneralLedgerBatch extends Model
{
    use HasFactory, HasUuids;

    public const SOURCE_IMPORT = 'IMPORT';
    public const SOURCE_MANUAL = 'MANUAL';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $table = 'finance_general_ledger_batches';

    protected $fillable = [
        'source_type',
        'batch_name',
        'source_filename',
        'sheet_name',
        'imported_year',
        'notes',
        'meta',
        'imported_at',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'imported_year' => 'integer',
        'meta' => 'array',
        'imported_at' => 'datetime',
    ];

    public function entries(): HasMany
    {
        return $this->hasMany(FinanceGeneralLedgerEntry::class, 'batch_id');
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
