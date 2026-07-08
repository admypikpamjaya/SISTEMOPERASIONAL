<?php

namespace App\Services\Recipient;

use App\Models\BlastGeneralRecipient;

class GeneralRecipientBulkSaver
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
            if (!empty($dto->phone)) {
                $exists = BlastGeneralRecipient::query()
                    ->where('whatsapp', $dto->phone)
                    ->exists();
            }

            if ($exists) {
                $duplicates++;
                continue;
            }

            BlastGeneralRecipient::query()->create([
                'nama' => $dto->nama,
                'whatsapp' => $dto->phone,
                'catatan' => $dto->catatan,
                'source' => 'excel:penerima_umum',
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
