<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class TunggakanImportBatch extends Model
{
    use HasFactory;

    protected $table = 'tunggakan_import_batches';

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id',
        'source_type',
        'source_reference',
        'notes',
        'imported_by',
        'total_rows',
        'matched_rows',
        'unmatched_rows',
    ];

    protected $casts = [
        'total_rows' => 'integer',
        'matched_rows' => 'integer',
        'unmatched_rows' => 'integer',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $model): void {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid();
            }
        });
    }

    public function records(): HasMany
    {
        return $this->hasMany(TunggakanRecord::class, 'batch_id');
    }

    public function importer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'imported_by');
    }
}
