<?php

namespace App\Services\Blast;

use App\DTOs\BlastMessageDTO;

class BlastService
{
    public function send(BlastMessageDTO $dto): void
    {
        if ($dto->email) {
            app(EmailBlastService::class)->send($dto);
        }

        if ($dto->phone) {
            app(WhatsAppBlastService::class)->send($dto);
        }
    }
}
