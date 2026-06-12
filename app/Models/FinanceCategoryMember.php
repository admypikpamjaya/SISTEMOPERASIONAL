<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\Pivot;

class FinanceCategoryMember extends Pivot
{
    use HasUuids;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $table = 'finance_category_members';

    protected $fillable = [
        'parent_category_id',
        'member_category_id',
    ];
}
