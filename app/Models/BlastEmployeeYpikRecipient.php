<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class BlastEmployeeYpikRecipient extends Model
{
    use HasFactory;

    protected $table = 'blast_employee_ypik_recipients';

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
        'dataset',
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
