<?php

namespace App\Services\Recipient;

use App\DataTransferObjects\Recipient\RecipientImportResultDTO;
use PhpOffice\PhpSpreadsheet\IOFactory;

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
        $rows = $sheet->toArray(null, true, true, false);

        $result = new RecipientImportResultDTO();
        $headerMap = [];

        if (!empty($rows[0]) && is_array($rows[0])) {
            $headerMap = $this->buildHeaderMap($rows[0]);
        }

        // Fallback posisi kolom hanya dipakai jika file benar-benar tidak punya header yang dikenali.
        $usePositionalFallback = empty($headerMap);

        foreach ($rows as $index => $row) {
            if ($index === 0) {
                continue;
            }

            if (!is_array($row) || $this->isRowEmpty($row)) {
                continue;
            }

            $raw = [
                'nama_siswa' => $this->resolveCell($row, $headerMap, 'nama_siswa', 0, $usePositionalFallback),
                'kelas' => $this->resolveCell($row, $headerMap, 'kelas', 1, $usePositionalFallback),
                'nama_wali' => $this->resolveCell($row, $headerMap, 'nama_wali', 2, $usePositionalFallback),
                'wa' => $this->resolveCell($row, $headerMap, 'wa', 3, $usePositionalFallback),
                'email' => $this->resolveCell($row, $headerMap, 'email', 4, $usePositionalFallback),
                'catatan' => $this->resolveCell($row, $headerMap, 'catatan', 5, $usePositionalFallback),
            ];

            $dto = $this->normalizer->normalize($raw);

            if ($dto->isValid) {
                $result->valid[] = $dto;
                continue;
            }

            $result->invalid[] = $dto;
        }

        return $result;
    }

    /**
     * @param array<int, mixed> $headerRow
     * @return array<string, int>
     */
    private function buildHeaderMap(array $headerRow): array
    {
        $map = [];

        foreach ($headerRow as $index => $header) {
            $canonicalHeader = $this->canonicalHeader((string) $header);
            if ($canonicalHeader === null) {
                continue;
            }

            if (!array_key_exists($canonicalHeader, $map)) {
                $map[$canonicalHeader] = $index;
            }
        }

        return $map;
    }

    private function canonicalHeader(string $header): ?string
    {
        $normalized = strtolower(trim($header));
        $normalized = preg_replace('/[^a-z0-9]+/', '', $normalized);

        if ($normalized === '') {
            return null;
        }

        if (in_array($normalized, ['namasiswa', 'siswa'], true) || str_starts_with($normalized, 'namasiswa')) {
            return 'nama_siswa';
        }

        if (in_array($normalized, ['kelas', 'class'], true) || str_starts_with($normalized, 'kelas')) {
            return 'kelas';
        }

        if (
            in_array($normalized, ['namawali', 'wali', 'namaorangtua', 'orangtua', 'ortu'], true) ||
            str_starts_with($normalized, 'namawali') ||
            str_contains($normalized, 'orangtua')
        ) {
            return 'nama_wali';
        }

        if (
            in_array($normalized, ['whatsapp', 'whasapp', 'wa', 'nomorwa', 'nomorwhatsapp', 'nomorhp', 'nohp', 'nowa'], true) ||
            str_contains($normalized, 'whatsapp') ||
            str_contains($normalized, 'whasapp') ||
            str_ends_with($normalized, 'wa')
        ) {
            return 'wa';
        }

        if (
            in_array($normalized, ['email', 'emailwali', 'emailorangtua', 'emailortu'], true) ||
            str_contains($normalized, 'email')
        ) {
            return 'email';
        }

        if (
            in_array($normalized, ['catatan', 'keterangan', 'notes', 'note'], true) ||
            str_starts_with($normalized, 'catatan') ||
            str_starts_with($normalized, 'keterangan') ||
            str_starts_with($normalized, 'note')
        ) {
            return 'catatan';
        }

        return null;
    }

    /**
     * @param array<int, mixed> $row
     * @param array<string, int> $headerMap
     */
    private function resolveCell(
        array $row,
        array $headerMap,
        string $field,
        int $fallbackIndex,
        bool $useFallback
    ): ?string {
        if (array_key_exists($field, $headerMap)) {
            $index = $headerMap[$field];
        } elseif ($useFallback) {
            $index = $fallbackIndex;
        } else {
            return null;
        }

        $value = $row[$index] ?? null;

        if ($value === null) {
            return null;
        }

        $value = trim((string) $value);
        return $value === '' ? null : $value;
    }

    /**
     * @param array<int, mixed> $row
     */
    private function isRowEmpty(array $row): bool
    {
        foreach ($row as $value) {
            if ($value === null) {
                continue;
            }

            if (trim((string) $value) !== '') {
                return false;
            }
        }

        return true;
    }
}
