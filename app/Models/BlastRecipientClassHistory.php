<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BlastRecipientClassHistory extends Model
{
    use HasUuids;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'recipient_id',
        'previous_class',
        'new_class',
        'previous_education_level',
        'new_education_level',
        'previous_academic_year',
        'new_academic_year',
        'previous_status',
        'new_status',
        'change_type',
        'notes',
        'changed_by',
    ];

    public function recipient(): BelongsTo
    {
        return $this->belongsTo(BlastRecipient::class, 'recipient_id');
    }

    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
