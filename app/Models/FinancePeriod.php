<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FinancePeriod extends Model
{
    use HasFactory, HasUuids;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $table = 'finance_periods';

    protected $fillable = [
        'period_type',
        'year',
        'month',
        'day',
        'start_date',
        'end_date',
        'opening_balance',
        'closing_balance',
        'status',
        'locked_by',
        'locked_at',
    ];

    protected $casts = [
        'year' => 'integer',
        'month' => 'integer',
        'day' => 'integer',
        'start_date' => 'date',
        'end_date' => 'date',
        'opening_balance' => 'decimal:2',
        'closing_balance' => 'decimal:2',
        'locked_at' => 'datetime',
    ];

    public function reports(): HasMany
    {
        return $this->hasMany(FinanceReport::class, 'period_id');
    }
}
