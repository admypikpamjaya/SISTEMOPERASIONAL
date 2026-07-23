<?php

namespace App\Services\SystemManagement;

use App\Models\BlastLog;
use App\Models\SystemBlastLogArchive;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class BlastLogArchiveService
{
    public function archive(BlastLog $log, string $reason = 'deleted'): void
    {
        if (!Schema::hasTable('system_blast_log_archives')) {
            return;
        }

        $message = $log->relationLoaded('message') ? $log->message : $log->message()->first();
        $target = $log->relationLoaded('target') ? $log->target : $log->target()->first();

        SystemBlastLogArchive::query()->create([
            'original_log_id' => $log->id,
            'blast_message_id' => $log->blast_message_id,
            'blast_target_id' => $log->blast_target_id,
            'channel' => $message?->channel,
            'target' => $target?->target,
            'device_id' => $log->device_id,
            'status' => $log->status,
            'provider_status' => $log->provider_status,
            'provider_reference' => $log->provider_reference,
            'provider_message_id' => $log->provider_message_id,
            'provider_sender_phone' => $log->provider_sender_phone,
            'message_snapshot' => $log->message_snapshot,
            'response' => $log->response,
            'error_message' => $log->error_message,
            'payload' => [
                'attempt' => $log->attempt,
                'sent_at' => optional($log->sent_at)->toDateTimeString(),
                'provider_checked_at' => optional($log->provider_checked_at)->toDateTimeString(),
            ],
            'archived_by' => Auth::id(),
            'archive_reason' => $reason,
            'original_created_at' => $log->created_at,
            'original_updated_at' => $log->updated_at,
        ]);
    }
}
