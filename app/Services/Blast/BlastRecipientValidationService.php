<?php

namespace App\Services\Blast;

use App\DataTransferObjects\Blast\BlastRecipientDTO;
use App\Models\BlastRecipient;

class BlastRecipientValidationService
{
    /**
     * VALIDASI UTAMA
     */
    public function validate(BlastRecipientDTO $dto): array
    {
        $errors = [];

        // WA & EMAIL minimal salah satu harus ada
        if (empty($dto->wa_wali) && empty($dto->email_wali)) {
            $errors[] = 'Nomor WhatsApp dan Email kosong';
        }

        // Validasi WA
        if ($dto->wa_wali && ! $this->isValidWhatsapp($dto->wa_wali)) {
            $errors[] = 'Format nomor WhatsApp tidak valid';
        }

        // Validasi Email
        if ($dto->email_wali && ! filter_var($dto->email_wali, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Format email tidak valid';
        }

        // Duplikasi WA
        if ($dto->wa_wali && $this->isDuplicateWa($dto->wa_wali)) {
            $errors[] = 'Nomor WhatsApp sudah terdaftar';
        }

        // Duplikasi Email
        if ($dto->email_wali && $this->isDuplicateEmail($dto->email_wali)) {
            $errors[] = 'Email sudah terdaftar';
        }

        return [
            'is_valid' => empty($errors),
            'errors'   => $errors,
        ];
    }

    /**
     * NORMALISASI + VALIDASI WA
     */
    protected function isValidWhatsapp(string $wa): bool
    {
        $wa = $this->normalizeWhatsapp($wa);

        // Harus numeric dan panjang minimal
        return preg_match('/^62[0-9]{8,15}$/', $wa) === 1;
    }

    /**
     * FORMAT WA KE +62
     */
    public function normalizeWhatsapp(string $wa): string
    {
        $wa = trim($wa);
        $wa = str_replace([' ', '-', '+'], '', $wa);

        if (str_starts_with($wa, '0')) {
            $wa = '62' . substr($wa, 1);
        }

        return $wa;
    }

    protected function isDuplicateWa(string $wa): bool
    {
        $wa = $this->normalizeWhatsapp($wa);

        return BlastRecipient::where('wa_wali', $wa)->exists();
    }

    protected function isDuplicateEmail(string $email): bool
    {
        return BlastRecipient::where('email_wali', $email)->exists();
    }
}
