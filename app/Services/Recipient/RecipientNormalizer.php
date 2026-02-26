<?php

namespace App\Services\Recipient;

use App\DataTransferObjects\Recipient\RecipientRowDTO;

class RecipientNormalizer
{
    public function normalize(array $row): RecipientRowDTO
    {
        $errors = [];

        $namaSiswa = trim((string) ($row['nama_siswa'] ?? ''));
        $kelas     = trim((string) ($row['kelas'] ?? ''));
        $namaWali  = trim((string) ($row['nama_wali'] ?? ''));
        $emailRaw  = trim((string) ($row['email'] ?? ''));
        $waRaw     = trim((string) ($row['wa'] ?? ''));
        $waRaw2    = trim((string) ($row['wa_2'] ?? $row['wa2'] ?? ''));
        $catatan   = trim((string) ($row['catatan'] ?? ''));

        // ===== REQUIRED =====
        if ($namaSiswa === '') $errors[] = 'nama_siswa wajib diisi';
        if ($kelas === '')     $errors[] = 'kelas wajib diisi';
        if ($namaWali === '')  $errors[] = 'nama_wali wajib diisi';

        // ===== MINIMAL CONTACT =====
        if ($emailRaw === '' && $waRaw === '' && $waRaw2 === '') {
            $errors[] = 'email atau WhatsApp wajib diisi';
        }

        // ===== EMAIL =====
        $email = null;
        if ($emailRaw !== '') {
            if (!filter_var($emailRaw, FILTER_VALIDATE_EMAIL)) {
                $errors[] = 'format email tidak valid';
            } else {
                $email = $emailRaw;
            }
        }

        // ===== WA NORMALIZATION =====
        $wa = null;
        if ($waRaw !== '') {
            $wa = $this->normalizeWa($waRaw);
            if (!$wa) {
                $errors[] = 'format WhatsApp tidak valid';
            }
        }

        $wa2 = null;
        if ($waRaw2 !== '') {
            $wa2 = $this->normalizeWa($waRaw2);
            if (!$wa2) {
                $errors[] = 'format WhatsApp 2 tidak valid';
            }
        }

        if ($wa !== null && $wa2 !== null && $wa === $wa2) {
            // Hindari menyimpan nomor sama di kolom WA utama & kedua.
            $wa2 = null;
        }

        return new RecipientRowDTO(
            email: $email,
            phone: $wa,
            phoneSecondary: $wa2,
            namaWali: $namaWali ?: null,
            namaSiswa: $namaSiswa ?: null,
            kelas: $kelas ?: null,
            catatan: $catatan ?: null,     // ✅ FIX UTAMA
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
