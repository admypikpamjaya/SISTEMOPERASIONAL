<?php

namespace App\Services\Recipient;

use App\DataTransferObjects\Recipient\EmployeeRecipientRowDTO;

class EmployeeRecipientNormalizer
{
    public function normalize(array $row): EmployeeRecipientRowDTO
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
            if (!filter_var($emailRaw, FILTER_VALIDATE_EMAIL)) {
                $errors[] = 'format email tidak valid';
            } else {
                $email = $emailRaw;
            }
        }

        $wa = null;
        if ($waRaw !== '') {
            $wa = $this->normalizeWa($waRaw);
            if ($wa === null) {
                $errors[] = 'format WhatsApp tidak valid';
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

    private function normalizeWa(string $wa): ?string
    {
        $wa = preg_replace('/[^0-9]/', '', $wa) ?? '';

        if ($wa === '') {
            return null;
        }

        if (str_starts_with($wa, '0')) {
            $wa = '62' . substr($wa, 1);
        }

        if (str_starts_with($wa, '8')) {
            $wa = '62' . $wa;
        }

        if (!str_starts_with($wa, '62')) {
            return null;
        }

        if (strlen($wa) < 10 || strlen($wa) > 15) {
            return null;
        }

        return $wa;
    }
}

