<?php

namespace App\Jobs\Blast;

use App\DataTransferObjects\BlastPayload;
use App\Models\AnnouncementLog;
use App\Models\BlastLog;
use App\Models\BlastMessage;
use App\Services\Blast\WhatsAppBlastService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendWhatsappBlastJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        protected string $phone,
        protected BlastPayload $payload
    ) {}

    public function middleware(): array
    {
        return [];
    }

    public function tries(): int
    {
        // WhatsApp blast is executed synchronously in current flow,
        // so retries can leave logs stuck in PENDING.
        return 1;
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

    public function handle(WhatsAppBlastService $service): void
    {
        $blastLog = $this->resolveBlastLog();
        $announcementLog = $this->resolveAnnouncementLog();

        if ($this->isCampaignStopped($blastLog)) {
            $this->markStopped($blastLog, $announcementLog);
            return;
        }

        try {
            $sent = $service->send($this->phone, $this->payload);

            if (!$sent) {
                $providerError = trim((string) ($this->payload->meta['provider_error'] ?? ''));
                throw new \RuntimeException(
                    $providerError !== ''
                        ? $providerError
                        : 'WhatsApp provider returned false.'
                );
            }

            $this->markSuccess($blastLog, $announcementLog);
            $this->logSuccess($blastLog);
            $this->refreshCampaignCompletion($blastLog);
        } catch (\Throwable $exception) {
            $this->markFailure($blastLog, $announcementLog, $exception);
            $this->logFailure($blastLog, $exception);
            $this->refreshCampaignCompletion($blastLog);

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
        $providerMessage = trim(
            (string) ($this->payload->meta['provider_message'] ?? '')
        );
        $providerDeliveryStatus = strtolower(
            trim((string) ($this->payload->meta['provider_delivery_status'] ?? ''))
        );

        $responseMessage = $providerMessage !== ''
            ? $providerMessage
            : 'WhatsApp sent successfully.';

        if ($providerDeliveryStatus === 'pending' && $providerMessage === '') {
            $responseMessage = 'Message is pending and waiting to be processed';
        }

        if ($blastLog) {
            $blastLog->update([
                'status' => 'SENT',
                'error_message' => null,
                'response' => $responseMessage,
                'sent_at' => now(),
                'attempt' => $this->attempts(),
            ]);
        }

        if ($announcementLog) {
            $announcementLog->update([
                'status' => 'SENT',
                'response' => $responseMessage,
                'sent_at' => now(),
            ]);
        }
    }

    private function markFailure(
        ?BlastLog $blastLog,
        ?AnnouncementLog $announcementLog,
        \Throwable $exception
    ): void {
        $errorMessage = trim($exception->getMessage());
        if ($errorMessage === '') {
            $errorMessage = 'WhatsApp send failed.';
        }

        if ($blastLog) {
            $blastLog->update([
                'status' => 'FAILED',
                'error_message' => $errorMessage,
                'response' => $errorMessage,
                'sent_at' => now(),
                'attempt' => $this->attempts(),
            ]);
        }

        if ($announcementLog) {
            $announcementLog->update([
                'status' => 'FAILED',
                'response' => $errorMessage,
                'sent_at' => now(),
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

    private function logSuccess(?BlastLog $blastLog): void
    {
        $providerMessage = trim(
            (string) ($this->payload->meta['provider_message'] ?? '')
        );
        $deliveryStatus = trim(
            (string) ($this->payload->meta['provider_delivery_status'] ?? '')
        );

        Log::info('[WA BLAST SUCCESS]', [
            'phone' => $this->phone,
            'blast_log_id' => $blastLog?->id,
            'blast_message_id' => $blastLog?->blast_message_id,
            'provider_message' => $providerMessage !== '' ? $providerMessage : 'WhatsApp sent successfully.',
            'provider_delivery_status' => $deliveryStatus !== '' ? $deliveryStatus : null,
            'attachments' => count($this->payload->attachments ?? []),
        ]);
    }

    private function logFailure(?BlastLog $blastLog, \Throwable $exception): void
    {
        $providerError = trim(
            (string) ($this->payload->meta['provider_error'] ?? '')
        );
        $errorMessage = trim($exception->getMessage());

        Log::error('[WA BLAST FAILED]', [
            'phone' => $this->phone,
            'blast_log_id' => $blastLog?->id,
            'blast_message_id' => $blastLog?->blast_message_id,
            'error' => $errorMessage !== '' ? $errorMessage : 'WhatsApp send failed.',
            'provider_error' => $providerError !== '' ? $providerError : null,
            'attachments' => count($this->payload->attachments ?? []),
        ]);
    }
}
