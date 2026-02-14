<?php

namespace App\Jobs\Blast;

use App\DataTransferObjects\BlastPayload;
use App\Models\BlastLog;
use App\Services\Blast\EmailBlastService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
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

    public function handle(EmailBlastService $service): void
    {
        $blastLog = $this->resolveBlastLog();

        try {
            $sent = $service->send(
                $this->email,
                $this->subject,
                $this->payload
            );

            if ($blastLog) {
                $blastLog->update([
                    'status' => $sent ? 'SENT' : 'FAILED',
                    'error_message' => $sent ? null : 'Email provider returned false.',
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
