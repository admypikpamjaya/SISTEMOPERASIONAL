<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BlastLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'channel',
        'target',
        'status',
        'reference_type',
        'reference_id',
        'response',
    ];

    /* ================= RELATION ================= */

    public function reference()
    {
        return $this->morphTo();
    }
}
