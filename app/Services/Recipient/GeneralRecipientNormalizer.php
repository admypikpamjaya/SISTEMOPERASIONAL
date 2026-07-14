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
        $instansi = trim((string) ($row['instansi'] ?? ''));
        $email = trim((string) ($row['email'] ?? ''));
        $sertifikat = trim((string) ($row['sertifikat'] ?? ''));
        $eventName = trim((string) ($row['event_name'] ?? $row['event'] ?? ''));
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
            instansi: $instansi !== '' ? $instansi : null,
            email: $email !== '' ? strtolower($email) : null,
            sertifikat: $sertifikat !== '' ? $sertifikat : null,
            eventName: $eventName !== '' ? $eventName : null,
            catatan: $catatan !== '' ? $catatan : null,
            isValid: empty($errors),
            errors: $errors
        );
    }
}
