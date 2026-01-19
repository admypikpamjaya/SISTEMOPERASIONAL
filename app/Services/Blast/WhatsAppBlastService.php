<?php

namespace App\Services\Blast;

use App\DTOs\BlastMessageDTO;
use Illuminate\Support\Facades\Log;

class WhatsAppBlastService
{
    public function send(BlastMessageDTO $dto): void
    {
        Log::info('[WHATSAPP BLAST]', [
            'to' => $dto->phone,
            'message' => $dto->message,
        ]);
    }
}
