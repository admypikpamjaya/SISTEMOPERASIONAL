<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class TunggakanRecord extends Model
{
    use HasFactory;

    protected $table = 'tunggakan_records';

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id',
        'batch_id',
        'no_urut',
        'kelas',
        'nama_murid',
        'bulan',
        'nilai',
        'recipient_source',
        'recipient_id',
        'match_status',
        'match_notes',
        'blast_status',
        'blasted_at',
        'last_blast_log_id',
        'raw_payload',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'no_urut' => 'integer',
        'nilai' => 'decimal:2',
        'blasted_at' => 'datetime',
        'raw_payload' => 'array',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $model): void {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid();
            }
        });
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(TunggakanImportBatch::class, 'batch_id');
    }

    public function lastBlastLog(): BelongsTo
    {
        return $this->belongsTo(BlastLog::class, 'last_blast_log_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
