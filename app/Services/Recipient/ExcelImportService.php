<?php

namespace App\Services\Recipient;

use App\DataTransferObjects\Recipient\RecipientRowDTO;
use App\DataTransferObjects\Recipient\RecipientImportResultDTO;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ExcelImportService
{
    public function __construct(
        protected RecipientDetectionService $detector,
        protected RecipientValidationService $validator,
        protected RecipientDeduplicationService $deduper
    ) {}

    public function import(string $path): RecipientImportResultDTO
    {
        $sheet = IOFactory::load($path)->getActiveSheet()->toArray();

        $headers = array_shift($sheet);
        $map = $this->detector->detect($headers);

        $result = new RecipientImportResultDTO();

        foreach ($sheet as $row) {
            $dto = new RecipientRowDTO(
                email: $map->emailCol !== null ? trim($row[$map->emailCol] ?? '') : null,
                phone: $map->phoneCol !== null ? trim($row[$map->phoneCol] ?? '') : null,
                namaWali: $map->namaWaliCol !== null ? trim($row[$map->namaWaliCol] ?? '') : null,
                namaSiswa: $map->namaSiswaCol !== null ? trim($row[$map->namaSiswaCol] ?? '') : null,
                kelas: $map->kelasCol !== null ? trim($row[$map->kelasCol] ?? '') : null,
                isValid: false
            );

            $dto = $this->validator->validate($dto);

            if (!$dto->isValid) {
                $result->invalid[] = $dto;
                continue;
            }

            if ($this->deduper->isDuplicate($dto)) {
                $result->duplicate[] = $dto;
                continue;
            }

            if (!$dto->email && !$dto->phone) {
                $result->missing[] = $dto;
                continue;
            }

            $result->valid[] = $dto;
        }

        return $result;
    }
}
