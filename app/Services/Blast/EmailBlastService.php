<?php

namespace App\Services\Blast;

use App\Contracts\Messaging\EmailProviderInterface;
use App\DataTransferObjects\BlastPayload;

class EmailBlastService
{
    public function __construct(
        protected EmailProviderInterface $provider
    ) {}

    /**
     * BARU (Phase 8.4.1)
     * - Support SYNC execution
     * - Payload lengkap (message + attachment)
     */
    public function send(
        string $to,
        string $subject,
        BlastPayload $payload
    ): bool {
        return $this->provider->send(
            $to,
            $subject,
            $payload
        );
    }
}
