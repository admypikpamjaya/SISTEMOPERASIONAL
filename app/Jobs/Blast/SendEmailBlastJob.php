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

    public function __construct(
        protected string $email,
        protected BlastPayload $payload
    ) {}

    public function handle(EmailBlastService $service): void
    {
        $service->send($this->email, $this->payload);
    }
}
