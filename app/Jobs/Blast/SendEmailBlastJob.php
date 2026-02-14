<?php

namespace App\Jobs\Blast;

use App\DataTransferObjects\BlastPayload;
use App\Models\AnnouncementLog;
use App\Models\BlastLog;
use App\Models\BlastMessage;
use App\Services\Blast\EmailBlastService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\RateLimited;
use Illuminate\Queue\SerializesModels;

class SendEmailBlastJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected string $email;
    protected string $subject;
    protected BlastPayload $payload;

    public function __construct(
        string $email,
        string $subject,
        BlastPayload $payload
    ) {
        $this->email   = $email;
        $this->subject = $subject;
        $this->payload = $payload;
    }

    public function middleware(): array
    {
        return [
            new RateLimited('blast-email'),
        ];
    }

    public function tries(): int
    {
        return max(
            1,
            (int) (
                $this->payload->meta['retry_attempts']
                ?? config('blast.retry.max_attempts', 3)
            )
        );
    }

    /**
     * @return int[]
     */
    public function backoff(): array
    {
        $configured = $this->payload->meta['retry_backoff_seconds']
            ?? config('blast.retry.backoff_seconds', [30, 120, 300]);

        if (!is_array($configured)) {
            $configured = [$configured];
        }

        $normalized = [];
        foreach ($configured as $seconds) {
            $seconds = (int) $seconds;
            if ($seconds < 0) {
                continue;
            }

            $normalized[] = $seconds;
        }

        return $normalized === [] ? [30, 120, 300] : $normalized;
    }

    public function handle(EmailBlastService $service): void
    {
        $blastLog = $this->resolveBlastLog();
        $announcementLog = $this->resolveAnnouncementLog();

        if ($this->isCampaignStopped($blastLog)) {
            $this->markStopped($blastLog, $announcementLog);
            return;
        }

        try {
            $sent = $service->send(
                $this->email,
                $this->subject,
                $this->payload
            );

            if (!$sent) {
                throw new \RuntimeException('Email provider returned false.');
            }

            $this->markSuccess($blastLog, $announcementLog);
            $this->refreshCampaignCompletion($blastLog);
        } catch (\Throwable $exception) {
            $isFinalAttempt = $this->attempts() >= $this->tries();
            $this->markFailure($blastLog, $announcementLog, $exception, $isFinalAttempt);
            if ($isFinalAttempt) {
                $this->refreshCampaignCompletion($blastLog);
            }

            throw $exception;
        }
    }

    private function resolveBlastLog(): ?BlastLog
    {
        $blastLogId = $this->payload->meta['blast_log_id'] ?? null;

        if ($blastLogId === null) {
            return null;
        }

        return BlastLog::query()->find($blastLogId);
    }

    private function resolveAnnouncementLog(): ?AnnouncementLog
    {
        $announcementLogId = $this->payload->meta['announcement_log_id'] ?? null;

        if ($announcementLogId === null) {
            return null;
        }

        return AnnouncementLog::query()->find($announcementLogId);
    }

    private function markSuccess(
        ?BlastLog $blastLog,
        ?AnnouncementLog $announcementLog
    ): void {
        if ($blastLog) {
            $blastLog->update([
                'status' => 'SENT',
                'error_message' => null,
                'response' => 'Email sent successfully.',
                'sent_at' => now(),
                'attempt' => $this->attempts(),
            ]);
        }

        if ($announcementLog) {
            $announcementLog->update([
                'status' => 'SENT',
                'response' => 'Email sent successfully.',
                'sent_at' => now(),
            ]);
        }
    }

    private function markFailure(
        ?BlastLog $blastLog,
        ?AnnouncementLog $announcementLog,
        \Throwable $exception,
        bool $isFinalAttempt
    ): void {
        if ($blastLog) {
            $blastLog->update([
                'status' => $isFinalAttempt ? 'FAILED' : 'PENDING',
                'error_message' => $exception->getMessage(),
                'response' => $isFinalAttempt
                    ? 'Email send failed.'
                    : 'Retrying email send.',
                'sent_at' => $isFinalAttempt ? now() : null,
                'attempt' => $this->attempts(),
            ]);
        }

        if ($announcementLog) {
            $announcementLog->update([
                'status' => $isFinalAttempt ? 'FAILED' : 'PENDING',
                'response' => $isFinalAttempt
                    ? $exception->getMessage()
                    : 'Retrying: ' . $exception->getMessage(),
                'sent_at' => $isFinalAttempt ? now() : null,
            ]);
        }
    }

    private function isCampaignStopped(?BlastLog $blastLog): bool
    {
        $blastMessageId = $this->payload->meta['blast_message_id']
            ?? $blastLog?->blast_message_id;

        if ($blastMessageId === null) {
            return false;
        }

        $campaignStatus = BlastMessage::query()
            ->where('id', $blastMessageId)
            ->value('campaign_status');

        return strtoupper((string) $campaignStatus) === 'STOPPED';
    }

    private function markStopped(
        ?BlastLog $blastLog,
        ?AnnouncementLog $announcementLog
    ): void {
        if ($blastLog) {
            $blastLog->update([
                'status' => 'FAILED',
                'error_message' => 'Campaign stopped before send.',
                'response' => 'Campaign stopped before send.',
                'sent_at' => now(),
                'attempt' => $this->attempts(),
            ]);
        }

        if ($announcementLog) {
            $announcementLog->update([
                'status' => 'FAILED',
                'response' => 'Campaign stopped before send.',
                'sent_at' => now(),
            ]);
        }
    }

    private function refreshCampaignCompletion(?BlastLog $blastLog): void
    {
        $blastMessageId = $this->payload->meta['blast_message_id']
            ?? $blastLog?->blast_message_id;

        if ($blastMessageId === null) {
            return;
        }

        $pendingExists = BlastLog::query()
            ->where('blast_message_id', $blastMessageId)
            ->where('status', 'PENDING')
            ->exists();

        if ($pendingExists) {
            return;
        }

        BlastMessage::query()
            ->where('id', $blastMessageId)
            ->whereNotIn('campaign_status', ['PAUSED', 'STOPPED'])
            ->update([
                'campaign_status' => 'COMPLETED',
                'completed_at' => now(),
            ]);
    }
}
