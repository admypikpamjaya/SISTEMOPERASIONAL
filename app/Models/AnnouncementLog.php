<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AnnouncementLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'announcement_id',
        'channel',
        'target',
        'status',
        'response',
        'track_token',
        'opened_at',
        'open_count',
        'sent_at',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
        'opened_at' => 'datetime',
    ];

    public function announcement()
    {
        return $this->belongsTo(Announcement::class);
    }
}
