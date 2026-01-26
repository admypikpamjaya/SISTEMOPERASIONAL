<?php

namespace App\Services\Recipient;

use App\DataTransferObjects\RecipientDTO;

class RecipientNormalizer
{
   private function normalizePhone(?string $raw): ?string
{
    if (!$raw) return null;

    // buang semua selain angka
    $phone = preg_replace('/\D/', '', $raw);

    // CASE 1: 08xxxx → 628xxxx
    if (str_starts_with($phone, '08') && strlen($phone) >= 10) {
        return '62' . substr($phone, 1);
    }

    // CASE 2: 8xxxxxxx (tanpa 0) → INVALID (ambigu)
    if (str_starts_with($phone, '8') && strlen($phone) < 10) {
        return null;
    }

    // CASE 3: 62xxxx → OK
    if (str_starts_with($phone, '62') && strlen($phone) >= 11) {
        return $phone;
    }

    return null;
}
    public function normalize(array $row): RecipientDTO
    {
         $email = isset($row['email']) ? trim(strtolower($row['email'])) : null;
         $phone = $this->normalizePhone($row['phone'] ?? null);
         $catatan = $row['catatan'] ?? null;
    
         return new RecipientDTO(
              email_wali: $email,
              wa_wali: $phone,
              catatan: $catatan,
              raw: $row
         );
    }
}
