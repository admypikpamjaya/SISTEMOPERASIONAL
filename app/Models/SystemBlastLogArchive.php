<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SystemBlastLogArchive extends Model
{
    protected $fillable = [
        'original_log_id',
        'blast_message_id',
        'blast_target_id',
        'channel',
        'target',
        'device_id',
        'status',
        'provider_status',
        'provider_reference',
        'provider_message_id',
        'provider_sender_phone',
        'message_snapshot',
        'response',
        'error_message',
        'payload',
        'archived_by',
        'archive_reason',
        'original_created_at',
        'original_updated_at',
    ];

    protected $casts = [
        'payload' => 'array',
        'original_created_at' => 'datetime',
        'original_updated_at' => 'datetime',
    ];

    public function archivedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'archived_by');
    }
}
