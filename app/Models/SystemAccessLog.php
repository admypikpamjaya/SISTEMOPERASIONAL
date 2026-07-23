<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SystemAccessLog extends Model
{
    protected $fillable = [
        'user_id',
        'guard',
        'method',
        'route_name',
        'path',
        'status_code',
        'duration_ms',
        'ip_address',
        'user_agent',
        'browser',
        'platform',
        'device',
        'location_summary',
        'country',
        'region',
        'city',
        'latitude',
        'longitude',
        'metadata',
        'occurred_at',
    ];

    protected $casts = [
        'metadata' => 'array',
        'occurred_at' => 'datetime',
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
