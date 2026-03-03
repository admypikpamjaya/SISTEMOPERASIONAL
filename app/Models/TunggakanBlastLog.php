<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class TunggakanBlastLog extends Model
{
    use HasFactory;

    protected $table = 'tunggakan_blast_logs';

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id',
        'blast_message_id',
        'triggered_by',
        'total_candidate_records',
        'total_candidate_groups',
        'total_processed_groups',
        'total_sent_groups',
        'total_failed_groups',
        'total_skipped_groups',
        'total_targets',
        'total_sent_targets',
        'total_failed_targets',
        'total_queued_targets',
        'details',
    ];

    protected $casts = [
        'total_candidate_records' => 'integer',
        'total_candidate_groups' => 'integer',
        'total_processed_groups' => 'integer',
        'total_sent_groups' => 'integer',
        'total_failed_groups' => 'integer',
        'total_skipped_groups' => 'integer',
        'total_targets' => 'integer',
        'total_sent_targets' => 'integer',
        'total_failed_targets' => 'integer',
        'total_queued_targets' => 'integer',
        'details' => 'array',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $model): void {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid();
            }
        });
    }

    public function blastMessage(): BelongsTo
    {
        return $this->belongsTo(BlastMessage::class, 'blast_message_id');
    }

    public function triggerUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'triggered_by');
    }
}

