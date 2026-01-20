<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BlastTarget extends Model
{
    protected $fillable = [
        'blast_message_id',
        'target',
    ];

    public function message()
    {
        return $this->belongsTo(BlastMessage::class);
    }
}
