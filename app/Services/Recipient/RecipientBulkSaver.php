<?php

namespace App\Services\Recipient;

use App\Models\BlastRecipient;

class RecipientBulkSaver
{
    public function __construct(
        private RecipientGroupingService $groupingService
    ) {}

    public function save($dtos, array $context = []): array
    {
        $inserted = 0;
        $duplicates = 0;
        $invalid = 0;

        foreach ($dtos as $dto) {
            $isValid = true;
            $error = null;
            $phones = array_values(array_filter([
                $dto->phone ?? null,
                $dto->phoneSecondary ?? null,
            ]));

            // =========================
            // WAJIB: nama_siswa
            // =========================
            if (empty($dto->namaSiswa)) {
                $isValid = false;
                $error = 'nama_siswa wajib diisi';
            }

            // MINIMAL EMAIL / WA
            if (!$dto->email && empty($phones)) {
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
            if ($dto->phoneSecondary && strlen($dto->phoneSecondary) < 10) {
                $isValid = false;
                $error = 'Format WhatsApp 2 tidak valid';
            }

            // DUPLICATE CHECK
            $exists = false;
            if ($dto->email || !empty($phones)) {
                $exists = BlastRecipient::query()
                    ->where(function ($query) use ($dto, $phones) {
                        if ($dto->email) {
                            $query->orWhere('email_wali', $dto->email);
                        }

                        foreach ($phones as $phone) {
                            $query->orWhere('wa_wali', $phone)
                                ->orWhere('wa_wali_2', $phone);
                        }
                    })
                    ->exists();
            }

            if ($exists) {
                $duplicates++;
                continue;
            }

            BlastRecipient::create([
                'nama_siswa' => $dto->namaSiswa,
                'kelas' => $dto->kelas,
                'education_level' => ($context['education_level'] ?? null)
                    ?: $this->groupingService->inferEducationLevel($dto->kelas),
                'academic_year' => ($context['academic_year'] ?? null)
                    ?: $this->groupingService->currentAcademicYear(),
                'student_status' => ($context['student_status'] ?? null)
                    ?: RecipientGroupingService::STATUS_ACTIVE,
                'nama_wali' => $dto->namaWali,
                'wa_wali' => $dto->phone,
                'wa_wali_2' => $dto->phoneSecondary,
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
