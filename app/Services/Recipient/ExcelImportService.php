<?php

namespace App\Services\Recipient;

use App\DataTransferObjects\Recipient\RecipientImportResultDTO;
use PhpOffice\PhpSpreadsheet\IOFactory;
use RuntimeException;

class ExcelImportService
{
    public function __construct(
        protected RecipientNormalizer $normalizer,
        protected EmployeeRecipientNormalizer $employeeNormalizer
    ) {}

    public function import(string $path): RecipientImportResultDTO
    {
        if (!file_exists($path)) {
            throw new RuntimeException("File tidak ditemukan: {$path}");
        }

        $result = new RecipientImportResultDTO();
        $allSheetRows = $this->loadSheetRows($path);

        foreach ($allSheetRows as $rows) {
            if (empty($rows)) {
                continue;
            }

            $this->appendStudentRowsToResult($rows, $result);
        }

        return $result;
    }

    public function importEmployees(string $path): RecipientImportResultDTO
    {
        if (!file_exists($path)) {
            throw new RuntimeException("File tidak ditemukan: {$path}");
        }

        $result = new RecipientImportResultDTO();
        $allSheetRows = $this->loadSheetRows($path);

        foreach ($allSheetRows as $rows) {
            if (empty($rows)) {
                continue;
            }

            $this->appendEmployeeRowsToResult($rows, $result);
        }

        return $result;
    }

    /**
     * @param array<int, array<int, mixed>> $rows
     */
    private function appendStudentRowsToResult(array $rows, RecipientImportResultDTO $result): void
    {
        [$headerMap, $headerIndex] = $this->resolveHeaderMap(
            $rows,
            fn (string $header): ?string => $this->canonicalStudentHeader($header),
            true
        );

        // Fallback posisi kolom hanya dipakai jika file benar-benar tidak punya header yang dikenali.
        $usePositionalFallback = empty($headerMap);

        foreach ($rows as $index => $row) {
            if ($index === $headerIndex) {
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
                'wa_2' => $this->resolveCell($row, $headerMap, 'wa_2', null, false),
                'email' => $this->resolveCell($row, $headerMap, 'email', 4, $usePositionalFallback),
                'catatan' => $this->resolveCell($row, $headerMap, 'catatan', 5, $usePositionalFallback),
            ];

            $dto = $this->normalizer->normalize($raw, true);

            if ($dto->isValid) {
                $result->valid[] = $dto;
                continue;
            }

            $result->invalid[] = $dto;
        }
    }

    /**
     * @param array<int, array<int, mixed>> $rows
     */
    private function appendEmployeeRowsToResult(array $rows, RecipientImportResultDTO $result): void
    {
        [$headerMap, $headerIndex] = $this->resolveHeaderMap(
            $rows,
            fn (string $header): ?string => $this->canonicalEmployeeHeader($header),
            false
        );

        // Fallback berdasarkan format file "recipent data koperasi tirta jatik utama".
        // Di file contoh, 2 kolom awal kosong lalu data mulai dari index 2.
        $usePositionalFallback = empty($headerMap);

        foreach ($rows as $index => $row) {
            if ($index === $headerIndex) {
                continue;
            }

            if (!is_array($row) || $this->isRowEmpty($row)) {
                continue;
            }

            $raw = [
                'nama_karyawan' => $this->resolveCell($row, $headerMap, 'nama_karyawan', 2, $usePositionalFallback),
                'instansi' => $this->resolveCell($row, $headerMap, 'instansi', 3, $usePositionalFallback),
                'nama_wali' => $this->resolveCell($row, $headerMap, 'nama_wali', 4, $usePositionalFallback),
                'wa' => $this->resolveCell($row, $headerMap, 'wa', 5, $usePositionalFallback),
                'email' => $this->resolveCell($row, $headerMap, 'email', 6, $usePositionalFallback),
                'catatan' => $this->resolveCell($row, $headerMap, 'catatan', 7, $usePositionalFallback),
            ];

            $dto = $this->employeeNormalizer->normalize($raw, true);

            if ($dto->isValid) {
                $result->valid[] = $dto;
                continue;
            }

            $result->invalid[] = $dto;
        }
    }

    /**
     * @param array<int, mixed> $headerRow
     * @return array<string, int>
     */
    private function buildHeaderMap(
        array $headerRow,
        callable $canonicalResolver,
        bool $allowSecondaryWa
    ): array {
        $map = [];

        foreach ($headerRow as $index => $header) {
            $canonicalHeader = $canonicalResolver((string) $header);
            if ($canonicalHeader === null) {
                continue;
            }

            if ($allowSecondaryWa && $canonicalHeader === 'wa' && array_key_exists('wa', $map)) {
                if (!array_key_exists('wa_2', $map)) {
                    $map['wa_2'] = $index;
                }
                continue;
            }

            if (!array_key_exists($canonicalHeader, $map)) {
                $map[$canonicalHeader] = $index;
            }
        }

        return $map;
    }

    /**
     * @param array<int, array<int, mixed>> $rows
     * @return array{0: array<string, int>, 1: int}
     */
    private function resolveHeaderMap(
        array $rows,
        callable $canonicalResolver,
        bool $allowSecondaryWa
    ): array {
        foreach ($rows as $index => $row) {
            if (!is_array($row) || $this->isRowEmpty($row)) {
                continue;
            }

            $headerMap = $this->buildHeaderMap(
                $row,
                $canonicalResolver,
                $allowSecondaryWa
            );

            if (!empty($headerMap)) {
                return [$headerMap, $index];
            }
        }

        return [[], 0];
    }

    private function canonicalStudentHeader(string $header): ?string
    {
        $normalized = $this->normalizeHeaderToken($header);

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

    private function canonicalEmployeeHeader(string $header): ?string
    {
        $normalized = $this->normalizeHeaderToken($header);

        if ($normalized === '') {
            return null;
        }

        if (
            in_array($normalized, ['namakaryawan', 'karyawan', 'namapegawai', 'pegawai'], true) ||
            str_contains($normalized, 'karyawan') ||
            str_contains($normalized, 'pegawai')
        ) {
            return 'nama_karyawan';
        }

        if (
            in_array($normalized, ['instansi', 'perusahaan', 'lembaga', 'unit', 'divisi'], true) ||
            str_contains($normalized, 'instansi') ||
            str_contains($normalized, 'perusahaan') ||
            str_contains($normalized, 'divisi')
        ) {
            return 'instansi';
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
            in_array($normalized, ['email', 'emailkaryawan', 'emailpegawai'], true) ||
            str_contains($normalized, 'email')
        ) {
            return 'email';
        }

        if (
            in_array($normalized, ['catatan', 'keterangan', 'notes', 'note', 'catatanoptional'], true) ||
            str_starts_with($normalized, 'catatan') ||
            str_starts_with($normalized, 'keterangan') ||
            str_starts_with($normalized, 'note')
        ) {
            return 'catatan';
        }

        return null;
    }

    private function normalizeHeaderToken(string $header): string
    {
        $normalized = strtolower(trim($header));
        return preg_replace('/[^a-z0-9]+/', '', $normalized) ?? '';
    }

    /**
     * @param array<int, mixed> $row
     * @param array<string, int> $headerMap
     */
    private function resolveCell(
        array $row,
        array $headerMap,
        string $field,
        ?int $fallbackIndex,
        bool $useFallback
    ): ?string {
        if (array_key_exists($field, $headerMap)) {
            $index = $headerMap[$field];
        } elseif ($useFallback && $fallbackIndex !== null) {
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

    private function isCsvFile(string $path): bool
    {
        return strtolower((string) pathinfo($path, PATHINFO_EXTENSION)) === 'csv';
    }

    /**
     * @return array<int, array<int, array<int, mixed>>>
     */
    private function loadSheetRows(string $path): array
    {
        if (class_exists(IOFactory::class)) {
            $spreadsheet = IOFactory::load($path);
            $allRows = [];

            foreach ($spreadsheet->getAllSheets() as $sheet) {
                $allRows[] = $sheet->toArray(null, true, true, false);
            }

            return $allRows;
        }

        if ($this->isCsvFile($path)) {
            return [$this->readCsvRows($path)];
        }

        throw new RuntimeException(
            'Library Excel belum tersedia di server. Jalankan composer install agar phpoffice/phpspreadsheet terpasang.'
        );
    }

    /**
     * @return array<int, array<int, mixed>>
     */
    private function readCsvRows(string $path): array
    {
        if (!is_readable($path)) {
            throw new RuntimeException('File CSV tidak dapat dibaca.');
        }

        $rows = $this->parseCsvWithDelimiter($path, ',');
        if (!empty($rows) && count($rows[0]) === 1 && str_contains((string) $rows[0][0], ';')) {
            $rows = $this->parseCsvWithDelimiter($path, ';');
        }

        return $rows;
    }

    /**
     * @return array<int, array<int, mixed>>
     */
    private function parseCsvWithDelimiter(string $path, string $delimiter): array
    {
        $rows = [];
        $handle = fopen($path, 'r');

        if ($handle === false) {
            throw new RuntimeException('Gagal membuka file CSV.');
        }

        try {
            while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
                if (isset($row[0]) && is_string($row[0])) {
                    $row[0] = preg_replace('/^\xEF\xBB\xBF/', '', $row[0]) ?? $row[0];
                }
                $rows[] = $row;
            }
        } finally {
            fclose($handle);
        }

        return $rows;
    }
}
