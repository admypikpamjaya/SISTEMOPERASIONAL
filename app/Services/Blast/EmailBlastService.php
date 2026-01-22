<?php

namespace App\Services\Blast;

use App\Contracts\Messaging\EmailProviderInterface;
use App\DataTransferObjects\BlastPayload;
use App\Mail\BlastMail;
use Illuminate\Support\Facades\Mail;

class EmailBlastService
{
    public function __construct(
        protected EmailProviderInterface $provider
    ) {}

    /**
     * KIRIM EMAIL BLAST (SYNC)
     */
    public function send(string $to, string $subject, BlastPayload $payload): bool
    {
        // PAKAI PROVIDER (DUMMY / REAL)
        return $this->provider->send(
            $to,
            $subject,
            $payload
        );
    }
}
