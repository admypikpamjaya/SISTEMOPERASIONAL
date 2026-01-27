<?php

namespace App\Services\Recipient;

use App\Models\BlastRecipient;

class RecipientSaver
{
    public function save(array $dtos): array
    {
        $inserted = 0;
        $duplicate = 0;
        $invalid = 0;

        foreach ($dtos as $dto) {

            if (!$dto->isValid) {
                $invalid++;
                continue;
            }

            $exists = BlastRecipient::query()
                ->where('email_wali', $dto->email)
                ->orWhere('wa_wali', $dto->phone)
                ->exists();

            if ($exists) {
                $duplicate++;
                continue;
            }

            BlastRecipient::create([
                'nama_siswa' => $dto->namaSiswa,
                'kelas' => $dto->kelas,
                'nama_wali' => $dto->namaWali,
                'wa_wali' => $dto->phone,
                'email_wali' => $dto->email,
                'catatan' => null,
                'is_valid' => true,
                'validation_error' => null,
            ]);

            $inserted++;
        }

        return compact('inserted', 'duplicate', 'invalid');
    }
}
