<?php

namespace App\Services\Recipient;

use App\Models\BlastEmployeeYpikRecipient;

class EmployeeYpikRecipientBulkSaver
{
    public function save($dtos): array
    {
        $inserted = 0;
        $duplicates = 0;
        $invalid = 0;

        foreach ($dtos as $dto) {
            $isValid = (bool) ($dto->isValid ?? false);
            $error = $isValid
                ? null
                : implode(', ', (array) ($dto->errors ?? []));

            $exists = false;
            if (!empty($dto->email) || !empty($dto->phone)) {
                $exists = BlastEmployeeYpikRecipient::query()
                    ->where(function ($query) use ($dto) {
                        if (!empty($dto->email)) {
                            $query->orWhere('email_karyawan', $dto->email);
                        }

                        if (!empty($dto->phone)) {
                            $query->orWhere('wa_karyawan', $dto->phone);
                        }
                    })
                    ->exists();
            }

            if ($exists) {
                $duplicates++;
                continue;
            }

            BlastEmployeeYpikRecipient::query()->create([
                'nama_karyawan' => $dto->namaKaryawan,
                'instansi' => $dto->instansi,
                'nama_wali' => $dto->namaWali,
                'wa_karyawan' => $dto->phone,
                'email_karyawan' => $dto->email,
                'catatan' => $dto->catatan,
                'source' => 'excel:datakaryawanypik',
                'is_valid' => $isValid,
                'validation_error' => $error,
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

