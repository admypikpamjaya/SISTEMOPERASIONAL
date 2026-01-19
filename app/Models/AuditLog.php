<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AuditLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'action',
        'entity',
        'entity_id',
        'payload',
    ];

    protected $casts = [
        'payload' => 'array',
    ];

    /* ================= RELATION ================= */

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
