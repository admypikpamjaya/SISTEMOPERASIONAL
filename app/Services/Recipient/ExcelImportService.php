<?php

namespace App\Services\Recipient;

use App\DataTransferObjects\Recipient\RecipientImportResultDTO;
use App\DataTransferObjects\Recipient\RecipientRowDTO;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Collection;

class ExcelImportService
{
    public function __construct(
        protected RecipientNormalizer $normalizer
    ) {}

    public function import(string $path): RecipientImportResultDTO
    {
        if (!file_exists($path)) {
            throw new \Exception("File tidak ditemukan: {$path}");
        }

        $spreadsheet = IOFactory::load($path);
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray();

        $result = new RecipientImportResultDTO();

        foreach ($rows as $index => $row) {
            // skip header
            if ($index === 0) {
                continue;
            }

            // mapping fleksibel (phase 9.3)
            $raw = [
                'wa'    => $row[1] ?? null,
                'email' => $row[2] ?? null,
            ];

            // 🔥 NORMALIZE DI SINI (INI YANG HILANG)
            $dto = $this->normalizer->normalize($raw);

            if ($dto->isValid) {
                $result->valid[] = $dto;
            } else {
                $result->invalid[] = $dto;
            }
        }

        return $result;
    }
}
