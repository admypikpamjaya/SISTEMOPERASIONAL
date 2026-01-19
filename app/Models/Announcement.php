<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Announcement extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'message',
        'attachment_path',
        'created_by',
    ];

    /* ================= RELATION ================= */

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function blastLogs()
    {
        return $this->morphMany(BlastLog::class, 'reference');
    }
    
}
