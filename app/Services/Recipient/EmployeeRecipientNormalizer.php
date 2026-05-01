<?php

namespace App\Services\Recipient;

use App\DataTransferObjects\Recipient\EmployeeRecipientRowDTO;

class EmployeeRecipientNormalizer
{
    public function __construct(
        private ContactValueNormalizer $contactValueNormalizer
    ) {}

    public function normalize(array $row, bool $autoCompleteEmailDomain = false): EmployeeRecipientRowDTO
    {
        $errors = [];

        $namaKaryawan = trim((string) ($row['nama_karyawan'] ?? ''));
        $instansi = trim((string) ($row['instansi'] ?? ''));
        $namaWali = trim((string) ($row['nama_wali'] ?? ''));
        $emailRaw = trim((string) ($row['email'] ?? ''));
        $waRaw = trim((string) ($row['wa'] ?? ''));
        $catatan = trim((string) ($row['catatan'] ?? ''));

        if ($namaKaryawan === '') {
            $errors[] = 'nama_karyawan wajib diisi';
        }

        if ($emailRaw === '' && $waRaw === '') {
            $errors[] = 'email atau WhatsApp wajib diisi';
        }

        $email = null;
        if ($emailRaw !== '') {
            $emailResult = $this->contactValueNormalizer->normalizeEmail(
                $emailRaw,
                $autoCompleteEmailDomain
            );

            if ($emailResult['error'] !== null) {
                $errors[] = $emailResult['error'];
            } else {
                $email = $emailResult['value'];
            }
        }

        $wa = null;
        if ($waRaw !== '') {
            $waResult = $this->contactValueNormalizer->normalizeWhatsapp($waRaw);
            $wa = $waResult['value'];

            if ($waResult['error'] !== null) {
                $errors[] = $waResult['error'];
            }
        }

        return new EmployeeRecipientRowDTO(
            email: $email,
            phone: $wa,
            namaKaryawan: $namaKaryawan !== '' ? $namaKaryawan : null,
            instansi: $instansi !== '' ? $instansi : null,
            namaWali: $namaWali !== '' ? $namaWali : null,
            catatan: $catatan !== '' ? $catatan : null,
            isValid: empty($errors),
            errors: $errors
        );
    }
}
