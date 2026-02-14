<?php

namespace App\Jobs\Blast;

use App\DataTransferObjects\BlastPayload;
use App\Models\BlastLog;
use App\Services\Blast\WhatsappBlastService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendWhatsappBlastJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        protected string $phone,
        protected BlastPayload $payload
    ) {}

    public function handle(WhatsappBlastService $service): void
    {
        $blastLog = $this->resolveBlastLog();

        try {
            $sent = $service->send($this->phone, $this->payload);

            if ($blastLog) {
                $blastLog->update([
                    'status' => $sent ? 'SENT' : 'FAILED',
                    'error_message' => $sent ? null : 'WhatsApp provider returned false.',
                    'sent_at' => now(),
                    'attempt' => $this->attempts(),
                ]);
            }
        } catch (\Throwable $exception) {
            if ($blastLog) {
                $blastLog->update([
                    'status' => 'FAILED',
                    'error_message' => $exception->getMessage(),
                    'sent_at' => now(),
                    'attempt' => $this->attempts(),
                ]);
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
}
