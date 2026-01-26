<?php

namespace App\Services\Recipient;

use App\Models\Recipient;
use App\DataTransferObjects\Recipient\RecipientRowDTO;

class RecipientDeduplicationService
{
    public function isDuplicate(RecipientRowDTO $row): bool
    {
        return Recipient::query()
            ->when($row->email, fn($q) => $q->orWhere('email', $row->email))
            ->when($row->phone, fn($q) => $q->orWhere('phone', $row->phone))
            ->exists();
    }
}
