<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class BlastGeneralRecipient extends Model
{
    use HasFactory;

    protected $table = 'blast_general_recipients';

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id',
        'nama',
        'whatsapp',
        'instansi',
        'email',
        'sertifikat',
        'event_name',
        'catatan',
        'source',
        'is_valid',
        'validation_error',
    ];

    protected $casts = [
        'is_valid' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $model): void {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid();
            }
        });
    }
}
