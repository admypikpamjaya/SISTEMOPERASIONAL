<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class BlastEmployeeRecipient extends Model
{
    use HasFactory;

    protected $table = 'recipent_data_koperasi_tirta_jatik_utama';

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id',
        'nama_karyawan',
        'instansi',
        'nama_wali',
        'wa_karyawan',
        'email_karyawan',
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
