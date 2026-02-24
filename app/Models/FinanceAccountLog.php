<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FinanceAccountLog extends Model
{
    use HasFactory;

    public const ACTION_CREATED = 'CREATED';
    public const ACTION_UPDATED = 'UPDATED';

    protected $table = 'finance_account_logs';

    protected $fillable = [
        'finance_account_id',
        'action',
        'before_data',
        'after_data',
        'actor_id',
    ];

    protected $casts = [
        'before_data' => 'array',
        'after_data' => 'array',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(FinanceAccount::class, 'finance_account_id');
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }
}
