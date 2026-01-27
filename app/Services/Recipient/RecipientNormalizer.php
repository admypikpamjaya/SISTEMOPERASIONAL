<?php

namespace App\Services\Recipient;

use App\DataTransferObjects\Recipient\RecipientRowDTO;

class RecipientNormalizer
{
    public function normalize(array $row): RecipientRowDTO
    {
        $errors = [];

        $namaSiswa = trim($row['nama_siswa'] ?? '');
        $kelas     = trim($row['kelas'] ?? '');
        $namaWali  = trim($row['nama_wali'] ?? '');
        $email     = trim($row['email'] ?? '');
        $waRaw     = trim($row['wa'] ?? '');

        // ===== REQUIRED =====
        if ($namaSiswa === '') $errors[] = 'nama_siswa wajib diisi';
        if ($kelas === '')     $errors[] = 'kelas wajib diisi';
        if ($namaWali === '')  $errors[] = 'nama_wali wajib diisi';
        if ($email === '')     $errors[] = 'email_wali wajib diisi';
        if ($waRaw === '')     $errors[] = 'wa_wali wajib diisi';

        // ===== EMAIL =====
        if ($email && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'format email tidak valid';
        }

        // ===== WA NORMALIZATION =====
        $wa = $this->normalizeWa($waRaw);
        if (!$wa) {
            $errors[] = 'format WhatsApp tidak valid';
        }

        return new RecipientRowDTO(
            email: $email ?: null,
            phone: $wa,
            namaWali: $namaWali ?: null,
            namaSiswa: $namaSiswa ?: null,
            kelas: $kelas ?: null,
            isValid: empty($errors),
            errors: $errors
        );
    }

    private function normalizeWa(string $wa): ?string
    {
        $wa = preg_replace('/[^0-9]/', '', $wa);

        if (str_starts_with($wa, '0')) {
            $wa = '62' . substr($wa, 1);
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
