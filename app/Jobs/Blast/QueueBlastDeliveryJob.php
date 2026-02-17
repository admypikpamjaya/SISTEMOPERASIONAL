<?php

namespace App\Jobs\Blast;

use App\DataTransferObjects\BlastPayload;
use App\Models\AnnouncementLog;
use App\Models\BlastLog;
use App\Models\BlastMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class QueueBlastDeliveryJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        protected string $channel,
        protected string $target,
        protected ?string $subject,
        protected BlastPayload $payload
    ) {}

    public int $tries = 100000;

    public function retryUntil(): \DateTimeInterface
    {
        return now()->addDays(30);
    }

    public function handle(): void
    {
        $message = $this->resolveBlastMessage();
        if ($message) {
            $status = strtoupper((string) $message->campaign_status);

            if ($status === 'PAUSED') {
                $this->release((int) config('blast.pause_poll_seconds', 30));
                return;
            }

            if ($status === 'STOPPED') {
                $this->markAsStopped();
                return;
            }

            if (in_array($status, ['QUEUED', 'SCHEDULED'], true)) {
                $message->update([
                    'campaign_status' => 'RUNNING',
                    'started_at' => $message->started_at ?? now(),
                ]);
            }
        }

        $queueName = trim((string) ($this->payload->meta['queue_name'] ?? ''));
        $normalizedChannel = strtoupper($this->channel);

        if ($normalizedChannel === 'EMAIL') {
            $job = new SendEmailBlastJob(
                $this->target,
                (string) ($this->subject ?? ''),
                $this->payload
            );
        } else {
            $job = new SendWhatsappBlastJob(
                $this->target,
                $this->payload
            );
        }

        if ($queueName !== '') {
            $job->onQueue($queueName);
        }

        if (in_array($normalizedChannel, ['WHATSAPP', 'EMAIL'], true)) {
            try {
                app(\Illuminate\Contracts\Bus\Dispatcher::class)->dispatchSync($job);
            } catch (\Throwable $exception) {
                Log::error('[BLAST SYNC DISPATCH FAILED]', [
                    'channel' => $normalizedChannel,
                    'target' => $this->target,
                    'error' => $exception->getMessage(),
                ]);
            }
            return;
        }

        dispatch($job);
    }

    private function resolveBlastLog(): ?BlastLog
    {
        $blastLogId = $this->payload->meta['blast_log_id'] ?? null;

        if ($blastLogId === null) {
            return null;
        }

        return BlastLog::query()->find($blastLogId);
    }

    private function resolveBlastMessage(): ?BlastMessage
    {
        $blastMessageId = $this->payload->meta['blast_message_id'] ?? null;

        if ($blastMessageId !== null) {
            $foundByMeta = BlastMessage::query()->find($blastMessageId);
            if ($foundByMeta) {
                return $foundByMeta;
            }
        }

        $blastLog = $this->resolveBlastLog();
        if ($blastLog === null) {
            return null;
        }

        return BlastMessage::query()->find($blastLog->blast_message_id);
    }

    private function markAsStopped(): void
    {
        $blastLog = $this->resolveBlastLog();
        if ($blastLog) {
            $blastLog->update([
                'status' => 'FAILED',
                'error_message' => 'Campaign stopped before dispatch.',
                'sent_at' => now(),
            ]);
        }

        $announcementLogId = $this->payload->meta['announcement_log_id'] ?? null;
        if ($announcementLogId !== null) {
            AnnouncementLog::query()
                ->whereKey($announcementLogId)
                ->update([
                    'status' => 'FAILED',
                    'response' => 'Campaign stopped before dispatch.',
                    'sent_at' => now(),
                ]);
        }
    }
}
