<?php

namespace App\Jobs\Blast;

use App\DataTransferObjects\BlastPayload;
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
        $service->send(
            $this->email,
            $this->subject,
            $this->payload
        );
    }
}
