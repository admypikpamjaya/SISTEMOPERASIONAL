<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BlastLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'blast_message_id',
        'blast_target_id',
        'status',
        'message_snapshot',
        'response',
        'error_message',
        'attempt',
        'sent_at',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
    ];

    public function message()
    {
        return $this->belongsTo(BlastMessage::class, 'blast_message_id');
    }

    public function target()
    {
        return $this->belongsTo(BlastTarget::class, 'blast_target_id');
    }
}
