<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoginHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'ip_address',
        'user_agent',
        'session_id',
        'locale',
        'logged_in_at',
    ];

    protected $casts = [
        'logged_in_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    public function getLocaleNameAttribute(): string
    {
        return config('locales')[$this->locale] ?? 'Unknown';
    }
    public function getUserAgentInfoAttribute(): string
    {
        $agent = $this->user_agent;
        if (str_contains($agent, 'Firefox')) {
            return 'Firefox';
        } elseif (str_contains($agent, 'Chrome')) {
            return 'Chrome';
        } elseif (str_contains($agent, 'Safari')) {
            return 'Safari';
        } elseif (str_contains($agent, 'Edge')) {
            return 'Edge';
        } else {
            return 'Unknown';
        }
    }
    public function getIpLocationAttribute(): string
    {
        // Placeholder for IP geolocation logic
        return 'Unknown Location';
    }
}
