<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DiscussionMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'channel_id',
        'user_id',
        'message',
        'attachment_path',
        'attachment_name',
        'attachment_size',
        'voice_note_path',
        'voice_note_name',
        'voice_note_size',
        'pinned_at',
        'pin_expires_at',
        'pinned_by',
    ];

    protected $casts = [
        'pinned_at' => 'datetime',
        'pin_expires_at' => 'datetime',
    ];

    public function channel(): BelongsTo
    {
        return $this->belongsTo(DiscussionChannel::class, 'channel_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function pinnedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pinned_by');
    }
}
