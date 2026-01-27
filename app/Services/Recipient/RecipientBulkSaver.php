<?php

namespace App\Services\Recipient;

use App\Models\BlastRecipient;

class RecipientBulkSaver
{
    public function save($dtos): array
    {
        $inserted = 0;
        $duplicates = 0;
        $invalid = 0;

        foreach ($dtos as $dto) {
            $isValid = true;
            $error = null;

            // =========================
            // WAJIB: nama_siswa
            // =========================
            if (empty($dto->namaSiswa)) {
                $isValid = false;
                $error = 'nama_siswa wajib diisi';
            }

            // MINIMAL EMAIL / WA
            if (!$dto->email && !$dto->phone) {
                $isValid = false;
                $error = 'Email dan WhatsApp kosong';
            }

            // VALIDASI EMAIL
            if ($dto->email && !filter_var($dto->email, FILTER_VALIDATE_EMAIL)) {
                $isValid = false;
                $error = 'Format email tidak valid';
            }

            // VALIDASI WA
            if ($dto->phone && strlen($dto->phone) < 10) {
                $isValid = false;
                $error = 'Format WhatsApp tidak valid';
            }

            // DUPLICATE CHECK
            $exists = BlastRecipient::query()
                ->when($dto->email, fn ($q) =>
                    $q->where('email_wali', $dto->email)
                )
                ->when($dto->phone, fn ($q) =>
                    $q->orWhere('wa_wali', $dto->phone)
                )
                ->exists();

            if ($exists) {
                $duplicates++;
                continue;
            }

            BlastRecipient::create([
                'nama_siswa' => $dto->namaSiswa,
                'kelas' => $dto->kelas,
                'nama_wali' => $dto->namaWali,
                'wa_wali' => $dto->phone,
                'email_wali' => $dto->email,
                'catatan' => $dto->catatan,
                'is_valid' => $isValid,
                'validation_error' => $isValid ? null : $error,
            ]);

            $isValid ? $inserted++ : $invalid++;
        }

        return [
            'inserted' => $inserted,
            'duplicates' => $duplicates,
            'invalid' => $invalid,
        ];
    }
}
