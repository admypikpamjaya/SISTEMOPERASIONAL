<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
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
        'email_address',
        'from_name',
        'host',
        'port',
        'encryption',
        'username',
        'password',
        'is_active',
        'is_enabled',
        'last_tested_at',
        'last_test_status',
        'last_test_message',
    ];

    protected $casts = [
        'password' => 'encrypted',
        'is_active' => 'boolean',
        'is_enabled' => 'boolean',
        'last_tested_at' => 'datetime',
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
}
