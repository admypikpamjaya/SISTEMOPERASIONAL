<?php

namespace App\Services\Recipient;

use App\DataTransferObjects\Recipient\RecipientRowDTO;

class RecipientNormalizer
{
    public function __construct(
        private ContactValueNormalizer $contactValueNormalizer
    ) {}

    public function normalize(array $row, bool $autoCompleteEmailDomain = false): RecipientRowDTO
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

        // ===== WA NORMALIZATION =====
        $wa = null;
        if ($waRaw !== '') {
            $waResult = $this->contactValueNormalizer->normalizeWhatsapp($waRaw);
            $wa = $waResult['value'];

            if ($waResult['error'] !== null) {
                $errors[] = $waResult['error'];
            }
        }

        $wa2 = null;
        if ($waRaw2 !== '') {
            $waResult2 = $this->contactValueNormalizer->normalizeWhatsapp($waRaw2);
            $wa2 = $waResult2['value'];

            if ($waResult2['error'] !== null) {
                $errors[] = str_replace('WhatsApp', 'WhatsApp 2', $waResult2['error']);
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
}
