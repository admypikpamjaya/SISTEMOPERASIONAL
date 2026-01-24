<?php

namespace App\Services\Recipient;

use App\Models\BlastRecipient;

class RecipientSaver
{
    public function save(array $dtos): array
    {
        $saved = [];

        foreach ($dtos as $dto) {
            $isValid = true;
            $error = null;

            // VALIDATION RULES
            if (!$dto->email_wali && !$dto->wa_wali) {
                $isValid = false;
                $error = 'email & wa kosong';
            }

            if ($dto->email_wali && !filter_var($dto->email_wali, FILTER_VALIDATE_EMAIL)) {
                $isValid = false;
                $error = 'format email tidak valid';
            }

            if ($dto->wa_wali && strlen($dto->wa_wali) < 10) {
                $isValid = false;
                $error = 'nomor wa tidak valid';
            }

            // DUPLICATE CHECK
            $exists = BlastRecipient::query()
                ->when($dto->email_wali, fn ($q) =>
                    $q->where('email_wali', $dto->email_wali)
                )
                ->when($dto->wa_wali, fn ($q) =>
                    $q->orWhere('wa_wali', $dto->wa_wali)
                )
                ->exists();

            if ($exists) {
                $isValid = false;
                $error = 'duplicate recipient';
            }

            $saved[] = BlastRecipient::create([
                'nama_siswa' => $dto->raw['nama_siswa'] ?? null,
                'kelas' => $dto->raw['kelas'] ?? null,
                'nama_wali' => $dto->raw['nama_wali'] ?? null,
                'wa_wali' => $dto->wa_wali,
                'email_wali' => $dto->email_wali,
                'catatan' => $dto->catatan,
                'is_valid' => $isValid,
                'validation_error' => $error,
            ]);
        }

        return $saved;
    }
}
