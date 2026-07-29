<?php

namespace App\Models;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class BlastEmailAccount extends Model
{
    use HasFactory;

    protected $table = 'blast_email_accounts';

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id',
        'label',
        'provider',
        'email_address',
        'from_name',
        'reply_to_address',
        'host',
        'port',
        'encryption',
        'smtp_timeout',
        'username',
        'password',
        'is_active',
        'is_enabled',
        'daily_limit',
        'daily_sent_count',
        'daily_failed_count',
        'quota_reset_date',
        'last_used_at',
        'last_send_status',
        'last_send_message',
        'last_error_at',
        'last_tested_at',
        'last_test_status',
        'last_test_message',
        'metadata',
    ];

    protected $casts = [
        'password' => 'encrypted',
        'is_active' => 'boolean',
        'is_enabled' => 'boolean',
        'daily_limit' => 'integer',
        'daily_sent_count' => 'integer',
        'daily_failed_count' => 'integer',
        'quota_reset_date' => 'date',
        'last_used_at' => 'datetime',
        'last_error_at' => 'datetime',
        'last_tested_at' => 'datetime',
        'metadata' => 'array',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $model): void {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid();
            }
        });
    }

    public function scopeEnabled($query)
    {
        return $query->where('is_enabled', true);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)->where('is_enabled', true);
    }

    public function senderLabel(): string
    {
        $label = trim((string) $this->label);
        $email = trim((string) $this->email_address);

        return $label !== '' && $label !== $email
            ? $label . ' <' . $email . '>'
            : $email;
    }

    public function providerLabel(): string
    {
        return match ($this->providerKey()) {
            'gmail' => 'Gmail',
            default => 'SMTP Custom',
        };
    }

    public function providerKey(): string
    {
        $provider = strtolower(trim((string) ($this->provider ?? 'gmail')));

        return in_array($provider, ['gmail', 'custom'], true) ? $provider : 'custom';
    }

    public function isGmail(): bool
    {
        return $this->providerKey() === 'gmail';
    }

    public function usagePercent(): int
    {
        $limit = (int) ($this->daily_limit ?? 0);
        if ($limit <= 0) {
            return 0;
        }

        return min(100, (int) round(((int) $this->daily_sent_count / $limit) * 100));
    }

    public function usageLabel(): string
    {
        $sent = number_format((int) $this->daily_sent_count, 0, ',', '.');
        $limit = (int) ($this->daily_limit ?? 0);

        if ($limit <= 0) {
            return $sent . ' terkirim hari ini';
        }

        return $sent . ' / ' . number_format($limit, 0, ',', '.');
    }

    public function healthTone(): string
    {
        if (!$this->is_enabled) {
            return 'muted';
        }

        if ($this->last_test_status === 'failed' || $this->last_send_status === 'failed') {
            return 'danger';
        }

        if ($this->last_test_status === 'success' || $this->last_send_status === 'success') {
            return 'success';
        }

        return 'warning';
    }

    public function healthLabel(): string
    {
        if (!$this->is_enabled) {
            return 'Nonaktif';
        }

        if ($this->last_test_status === 'failed' || $this->last_send_status === 'failed') {
            return 'Perlu dicek';
        }

        if ($this->last_test_status === 'success' || $this->last_send_status === 'success') {
            return 'Siap pakai';
        }

        return 'Belum dites';
    }

    public function canSendToday(): bool
    {
        $limit = (int) ($this->daily_limit ?? 0);

        return $limit <= 0 || (int) $this->daily_sent_count < $limit;
    }

    public function resetDailyCountersIfNeeded(?CarbonInterface $today = null): void
    {
        if (!$this->hasEnhancedColumns()) {
            return;
        }

        $today ??= now('Asia/Jakarta');
        $todayDate = $today->toDateString();
        $currentDate = $this->quota_reset_date?->toDateString();

        if ($currentDate === $todayDate) {
            return;
        }

        $this->forceFill([
            'daily_sent_count' => 0,
            'daily_failed_count' => 0,
            'quota_reset_date' => $todayDate,
        ])->save();
    }

    public function recordSendSuccess(string $message = 'Email sent successfully.'): void
    {
        $this->recordSendResult('success', $message);
    }

    public function recordSendFailure(string $message): void
    {
        $this->recordSendResult('failed', $message);
    }

    private function recordSendResult(string $status, string $message): void
    {
        if (!$this->hasEnhancedColumns()) {
            return;
        }

        $this->resetDailyCountersIfNeeded();

        $updates = [
            'last_used_at' => now(),
            'last_send_status' => $status,
            'last_send_message' => Str::limit($message, 1000, ''),
            'last_error_at' => $status === 'failed' ? now() : null,
            'updated_at' => now(),
        ];

        if ($status === 'success') {
            $updates['daily_sent_count'] = DB::raw('daily_sent_count + 1');
        } else {
            $updates['daily_failed_count'] = DB::raw('daily_failed_count + 1');
        }

        static::query()
            ->whereKey($this->getKey())
            ->update($updates);
    }

    private function hasEnhancedColumns(): bool
    {
        return Schema::hasColumn($this->getTable(), 'daily_sent_count')
            && Schema::hasColumn($this->getTable(), 'last_send_status');
    }
}
