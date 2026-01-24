<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class BlastRecipient extends Model
{
    use HasFactory;

    protected $table = 'blast_recipients';

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id',
        'nama_siswa',
        'kelas',
        'nama_wali',
        'wa_wali',
        'email_wali',
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
        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid();
            }
        });
    }
}
