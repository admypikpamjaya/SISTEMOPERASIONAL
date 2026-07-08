<?php

namespace App\Services\Recipient;

use App\DataTransferObjects\Recipient\GeneralRecipientRowDTO;

class GeneralRecipientNormalizer
{
    public function __construct(
        private ContactValueNormalizer $contactValueNormalizer
    ) {}

    public function normalize(array $row): GeneralRecipientRowDTO
    {
        $errors = [];

        $nama = trim((string) ($row['nama'] ?? ''));
        $waRaw = trim((string) ($row['wa'] ?? ''));
        $catatan = trim((string) ($row['catatan'] ?? ''));

        if ($nama === '') {
            $errors[] = 'nama wajib diisi';
        }

        if ($waRaw === '') {
            $errors[] = 'WhatsApp wajib diisi';
        }

        $wa = null;
        if ($waRaw !== '') {
            $waResult = $this->contactValueNormalizer->normalizeWhatsapp($waRaw);
            $wa = $waResult['value'];

            if ($waResult['error'] !== null) {
                $errors[] = $waResult['error'];
            }
        }

        return new GeneralRecipientRowDTO(
            nama: $nama !== '' ? $nama : null,
            phone: $wa,
            catatan: $catatan !== '' ? $catatan : null,
            isValid: empty($errors),
            errors: $errors
        );
    }
}
