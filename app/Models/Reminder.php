<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Reminder extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'remind_at',
        'alert_before_minutes',
        'type',
        'announcement_id',
        'is_active',
        'created_by',
        'deactivated_at',
        'deactivated_by',
    ];

    protected $casts = [
        'remind_at' => 'datetime',
        'is_active' => 'boolean',
        'alert_before_minutes' => 'integer',
        'deactivated_at' => 'datetime',
    ];

    public function announcement(): BelongsTo
    {
        return $this->belongsTo(Announcement::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function deactivator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deactivated_by');
    }

    public function isAnnouncementType(): bool
    {
        return strtoupper((string) $this->type) === 'ANNOUNCEMENT';
    }

    public function alertState(?Carbon $now = null): ?string
    {
        if (! $this->is_active) {
            return null;
        }

        $now ??= now(config('app.timezone'));
        $remindAt = $this->remind_at?->copy()->timezone(config('app.timezone'));
        if ($remindAt === null) {
            return null;
        }

        if ($now->greaterThanOrEqualTo($remindAt)) {
            return 'due';
        }

        $alertStart = $remindAt->copy()->subMinutes(max(1, (int) $this->alert_before_minutes));

        if ($now->greaterThanOrEqualTo($alertStart)) {
            return 'upcoming';
        }

        return null;
    }
}
