<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class BlastPdamRecipient extends Model
{
    use HasFactory;

    protected $table = 'blast_pdam_recipients';

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id',
        'timestamp_excel',
        'nama_lengkap',
        'instansi_pekerjaan',
        'nomor_telpon',
        'email',
        'sertifikat',
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
