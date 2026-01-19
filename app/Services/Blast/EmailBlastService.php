<?php

namespace App\Services\Blast;

use App\DTOs\BlastMessageDTO;
use Illuminate\Support\Facades\Log;

class EmailBlastService
{
    public function send(BlastMessageDTO $dto): void
    {
        Log::info('[EMAIL BLAST]', [
            'to' => $dto->email,
            'message' => $dto->message,
        ]);
    }
}
