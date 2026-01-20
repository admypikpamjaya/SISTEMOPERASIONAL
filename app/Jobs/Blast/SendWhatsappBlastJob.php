<?php

namespace App\Jobs\Blast;

use App\DataTransferObjects\BlastPayload;
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
        $service->send($this->phone, $this->payload);
    }
}
