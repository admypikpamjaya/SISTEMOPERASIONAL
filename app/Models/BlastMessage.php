<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class BlastMessage extends Model
{
    use HasFactory;

    protected $table = 'blast_messages';

    public $incrementing = false;     // ⬅️ WAJIB
    protected $keyType = 'string';    // ⬅️ WAJIB

    protected $fillable = [
        'id',
        'channel',
        'subject',
        'message',
        'meta',
        'campaign_status',
        'priority',
        'scheduled_at',
        'started_at',
        'paused_at',
        'completed_at',
        'attachment_path',
        'created_by',
    ];

    protected static function booted()
    {
        static::creating(function ($model) {
            if (! $model->id) {
                $model->id = (string) Str::uuid();
            }
        });
    }

    protected $casts = [
        'meta' => 'array',
        'scheduled_at' => 'datetime',
        'started_at' => 'datetime',
        'paused_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function logs()
    {
        return $this->hasMany(BlastLog::class, 'blast_message_id');
    }
}
