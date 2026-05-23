<?php 

namespace App\Services\Asset;

use App\DTOs\Asset\AssetDataDTO;
use App\DTOs\Asset\RegisterAssetDTO;
use App\DTOs\Asset\RegisterAssetViaFileDTO;
use App\DTOs\File\DownloadFileDTO;
use App\Enums\Asset\AssetCategory;
use App\Enums\Asset\AssetUnit;
use App\Models\Asset\Asset;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use ZipArchive;

/**
 * Handles asset master data, CSV import, and QR code lifecycle.
 *
 * Important for future finance work:
 * - this service currently stores inventory master data only;
 * - depreciation policy fields such as acquisition cost, useful life,
 *   residual value, and depreciation start date are not saved here yet.
 */
class AssetService 
{
    private const CHUNK_SIZE = 50;
    private const AC_TEMPLATE_REQUIRED_HEADERS = [
        'account_code',
        'location',
        'dimension',
        'power_rating',
        'brand',
    ];

    private function extractRecordsFromCsv(AssetCategory $category, $file): array
    {
        // CSV import mirrors the same master-data fields as the manual asset
        // form. Finance depreciation policy fields are intentionally not
        // derived here yet because the current import contract does not carry
        // them.
        $records = [];

        $handle = fopen($file->getRealPath(), 'r');
        $headers = array_map('trim', fgetcsv($handle));

        $requiredHeaders = [
            'account_code',
            'unit',
            'location',
        ];
        foreach ($requiredHeaders as $required) 
        {
            if(!in_array($required, $headers)) 
                throw new \Exception("Format header CSV tidak valid");
        }

        $handler = AssetFactory::createHandler($category);
        $rowNumber = 1;

        while (($row = fgetcsv($handle)) !== false) 
        {
            $rowNumber++;
            $row = array_combine($headers, $row);

            if(empty($row['account_code'])) 
                throw new \Exception("Kode akun kosong di baris ke-{$rowNumber}");

            if(empty($row['location'])) 
                throw new \Exception("Lokasi kosong di baris ke-{$rowNumber}");

            $detail = $handler->extractDetailFromCsv($row);

            $records[] = [
                'dto' => new RegisterAssetDTO(
                    category: $category,
                    accountCode: $row['account_code'],
                    serialNumber: $row['serial_number'] ?? null,
                    unit: $row['unit'],
                    location: $row['location'],
                    purchaseYear: $row['purchase_year'] ?? null,
                    detail: $detail
                ),
                'source_label' => "baris ke-{$rowNumber}",
            ];
        }

        fclose($handle);

        return $records;
    }

    /**
     * @return array{
     *     records: array<int, array{dto: RegisterAssetDTO, source_label: string}>,
     *     sheet_names: array<int, string>
     * }
     */
    private function extractRecordsFromSpreadsheet(AssetCategory $category, $file): array
    {
        if ($category !== AssetCategory::AC) {
            throw new \Exception(
                'Import Excel multi-sheet saat ini khusus untuk kategori AC. Untuk kategori lain, silakan gunakan template CSV.',
                422
            );
        }

        if (!class_exists(IOFactory::class)) {
            throw new \Exception(
                'Library Excel belum tersedia di server. Jalankan composer install agar phpoffice/phpspreadsheet terpasang.',
                500
            );
        }

        $spreadsheet = IOFactory::load($file->getRealPath());
        $records = [];
        $sheetNames = [];

        foreach ($spreadsheet->getWorksheetIterator() as $sheet) {
            if ($sheet->getSheetState() !== Worksheet::SHEETSTATE_VISIBLE) {
                continue;
            }

            $rows = $sheet->toArray(null, true, true, false);
            if ($this->isSpreadsheetRowsEmpty($rows)) {
                continue;
            }

            $sheetTitle = trim((string) $sheet->getTitle());
            $sheetNames[] = $sheetTitle;

            $unit = $this->resolveUnitFromSheetTitle($sheetTitle);
            if (!$unit instanceof AssetUnit) {
                throw new \Exception(
                    "Sheet \"{$sheetTitle}\" tidak bisa dipetakan ke unit aset. Gunakan nama sheet yang mengandung TK, SD, atau YPIK/SEKRETARIAT.",
                    422
                );
            }

            $headerMap = [];

            foreach ($rows as $index => $row) {
                if (!is_array($row) || $this->isSpreadsheetRowEmpty($row)) {
                    continue;
                }

                $candidateHeaderMap = $this->buildAcSpreadsheetHeaderMap($row);
                if (!empty($candidateHeaderMap)) {
                    $this->assertAcHeaderRequirements($candidateHeaderMap, $sheetTitle);
                    $headerMap = $candidateHeaderMap;
                    continue;
                }

                if (empty($headerMap)) {
                    continue;
                }

                $payload = [
                    'account_code' => $this->resolveSpreadsheetCell($row, $headerMap, 'account_code'),
                    'location' => $this->resolveSpreadsheetCell($row, $headerMap, 'location'),
                    'dimension' => $this->resolveSpreadsheetCell($row, $headerMap, 'dimension'),
                    'power_rating' => $this->resolveSpreadsheetCell($row, $headerMap, 'power_rating'),
                    'brand' => $this->resolveSpreadsheetCell($row, $headerMap, 'brand'),
                    'serial_number' => $this->resolveSpreadsheetCell($row, $headerMap, 'serial_number'),
                    'purchase_year' => $this->resolveSpreadsheetCell($row, $headerMap, 'purchase_year'),
                ];

                if ($this->isSpreadsheetRowEmpty($payload)) {
                    continue;
                }

                if ($payload['account_code'] === null) {
                    continue;
                }

                $rowNumber = $index + 1;
                $this->assertAcSpreadsheetRowRequirements($payload, $sheetTitle, $rowNumber);

                $records[] = [
                    'dto' => new RegisterAssetDTO(
                        category: $category,
                        accountCode: $payload['account_code'],
                        serialNumber: $payload['serial_number'],
                        unit: $unit->value,
                        location: $payload['location'],
                        purchaseYear: $payload['purchase_year'],
                        detail: [
                            'brand' => $payload['brand'],
                            'dimension' => $payload['dimension'],
                            'power_rating' => $payload['power_rating'],
                        ]
                    ),
                    'source_label' => "sheet \"{$sheetTitle}\" baris ke-{$rowNumber}",
                ];
            }
        }

        if (empty($records)) {
            throw new \Exception('Tidak ada data aset AC yang berhasil dibaca dari template Excel.', 422);
        }

        return [
            'records' => $records,
            'sheet_names' => $sheetNames,
        ];
    }

    /**
     * @return array{
     *     records: array<int, array{dto: RegisterAssetDTO, source_label: string}>,
     *     source_type: string,
     *     sheet_names: array<int, string>
     * }
     */
    private function resolveImportRecords(RegisterAssetViaFileDTO $dto): array
    {
        $extension = $this->resolveUploadedFileExtension($dto->file);

        if (in_array($extension, ['xlsx', 'xls'], true)) {
            $spreadsheetRecords = $this->extractRecordsFromSpreadsheet($dto->category, $dto->file);

            return [
                'records' => $spreadsheetRecords['records'],
                'source_type' => 'excel',
                'sheet_names' => $spreadsheetRecords['sheet_names'],
            ];
        }

        if ($extension === 'csv') {
            return [
                'records' => $this->extractRecordsFromCsv($dto->category, $dto->file),
                'source_type' => 'csv',
                'sheet_names' => [],
            ];
        }

        throw new \Exception('Format file tidak didukung. Gunakan file xlsx, xls, atau csv.', 422);
    }

    private function normalizeHeaderToken(string $value): string
    {
        return preg_replace('/[^a-z0-9]+/', '', strtolower(trim($value))) ?? '';
    }

    private function canonicalAcHeader(string $header): ?string
    {
        $normalized = $this->normalizeHeaderToken($header);

        return match (true) {
            in_array($normalized, ['akuncodeacypik', 'akuncode', 'accountcode', 'kodeakun'], true) => 'account_code',
            in_array($normalized, ['lantairuang', 'lokasi', 'ruang'], true) => 'location',
            in_array($normalized, ['ukurandimensi', 'dimensi', 'dimension', 'kapasitaspk', 'kapasitas'], true) => 'dimension',
            in_array($normalized, ['unitwatt', 'voltase', 'tegangan', 'dayalistrik', 'powerrating', 'watt'], true) => 'power_rating',
            in_array($normalized, ['merk', 'brand'], true) => 'brand',
            in_array($normalized, ['noserirangka', 'noseri', 'nomorseri', 'serialnumber', 'norangka'], true) => 'serial_number',
            in_array($normalized, ['tahunpembelian', 'purchaseyear', 'tahun'], true) => 'purchase_year',
            default => null,
        };
    }

    /**
     * @param array<int, mixed> $row
     * @return array<string, int>
     */
    private function buildAcSpreadsheetHeaderMap(array $row): array
    {
        $headerMap = [];

        foreach ($row as $index => $value) {
            $canonicalHeader = $this->canonicalAcHeader((string) $value);
            if ($canonicalHeader === null || array_key_exists($canonicalHeader, $headerMap)) {
                continue;
            }

            $headerMap[$canonicalHeader] = $index;
        }

        return $headerMap;
    }

    /**
     * @param array<string, int> $headerMap
     */
    private function assertAcHeaderRequirements(array $headerMap, string $sheetTitle): void
    {
        foreach (self::AC_TEMPLATE_REQUIRED_HEADERS as $requiredHeader) {
            if (!array_key_exists($requiredHeader, $headerMap)) {
                throw new \Exception(
                    "Header \"{$requiredHeader}\" tidak ditemukan pada sheet \"{$sheetTitle}\".",
                    422
                );
            }
        }
    }

    /**
     * @param array<int, mixed> $row
     * @param array<string, int> $headerMap
     */
    private function resolveSpreadsheetCell(array $row, array $headerMap, string $field): ?string
    {
        if (!array_key_exists($field, $headerMap)) {
            return null;
        }

        $value = $row[$headerMap[$field]] ?? null;
        if ($value === null) {
            return null;
        }

        $normalizedValue = trim((string) $value);
        return $normalizedValue === '' ? null : $normalizedValue;
    }

    /**
     * @param array<string, ?string> $payload
     */
    private function assertAcSpreadsheetRowRequirements(array $payload, string $sheetTitle, int $rowNumber): void
    {
        $requiredFields = [
            'account_code' => 'Kode akun',
            'location' => 'Lokasi',
            'dimension' => 'Ukuran dimensi',
            'power_rating' => 'Unit / watt',
            'brand' => 'Merk',
        ];

        foreach ($requiredFields as $field => $label) {
            if (($payload[$field] ?? null) === null) {
                throw new \Exception(
                    "{$label} kosong pada sheet \"{$sheetTitle}\" baris ke-{$rowNumber}.",
                    422
                );
            }
        }
    }

    private function resolveUnitFromSheetTitle(string $sheetTitle): ?AssetUnit
    {
        $normalizedTitle = strtoupper($sheetTitle);

        if (preg_match('/\bTK\b|TKIA/', $normalizedTitle) === 1) {
            return AssetUnit::TK;
        }

        if (preg_match('/\bSD\b|SDIA/', $normalizedTitle) === 1) {
            return AssetUnit::SD;
        }

        if (str_contains($normalizedTitle, 'YPIK') || str_contains($normalizedTitle, 'SEKRETARIAT')) {
            return AssetUnit::YAYASAN;
        }

        return null;
    }

    /**
     * @param array<int, array<int, mixed>> $rows
     */
    private function isSpreadsheetRowsEmpty(array $rows): bool
    {
        foreach ($rows as $row) {
            if (is_array($row) && !$this->isSpreadsheetRowEmpty($row)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param array<int|string, mixed> $row
     */
    private function isSpreadsheetRowEmpty(array $row): bool
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

    private function resolveUploadedFileExtension($file): string
    {
        if (method_exists($file, 'getClientOriginalExtension')) {
            return strtolower((string) $file->getClientOriginalExtension());
        }

        if (method_exists($file, 'getClientOriginalName')) {
            return strtolower((string) pathinfo($file->getClientOriginalName(), PATHINFO_EXTENSION));
        }

        return '';
    }

    private function persistRegisteredAsset(RegisterAssetDTO $dto)
    {
        Log::info($dto->toArray());

        // The first transaction writes the asset identity that is used by
        // inventory, maintenance, QR detail pages, and finance lookups.
        $assetData = Arr::except($dto->toArray(), 'detail');
        $validatedAssetData = Asset::validateRegistrationPayload($assetData);

        $asset = Asset::create($validatedAssetData);
        $asset->qr_code_path = $asset->generateQRCode();
        $asset->save();

        $assetDetailHandler = AssetFactory::createHandler($dto->category);

        // Category-specific detail is kept separate from the asset master.
        // If automated depreciation is implemented later, prefer writing
        // finance policy records in a dedicated table rather than mixing
        // them into these operational detail tables.
        $validatedDetail = $assetDetailHandler->validatePayload($dto->detail);
        $assetDetailHandler->insert($asset->id, $validatedDetail);

        return $asset->loadWithRelation();
    }

    private function makeSafeFilename(string $name): string
    {
        return preg_replace('/[^A-Za-z0-9_\-]/', '_', $name);
    }

    public function getAssets(?string $keyword = null, ?AssetCategory $category = null, ?AssetUnit $unit = null, ?int $page = 1, ?int $pageSize = 10)
    {
        $query = Asset::query();
        if($keyword)
        {
            $query->where(function($q) use ($keyword) {
                $q->where('account_code', 'like', "%{$keyword}%")
                ->orWhere('location', 'like', "%{$keyword}%");
            });
        }

        if($category)
        {
            $query->where('category', $category);
        }

        if($unit)
        {
            $query->where('unit', $unit);
        }

        return $query
            ->orderBy('account_code', 'asc')
            ->paginate($pageSize, ['*'], 'page', $page)
            ->appends(array_filter([
                'keyword' => $keyword,
                'category' => $category?->value,
            ]));
    }

    public function getAssetStatisticByUnit()
    {
        $statistics = Asset::select('unit', DB::raw('count(*) as total_assets'))
            ->groupBy('unit')
            ->get();

        return $statistics;
    }

    public function getAsset(string $id)
    {
        $asset = Asset::find($id);
        if(empty($asset))
            throw new \Exception('Asset tidak ditemukan', 404);
        
        $data = $asset->loadWithRelation();
        return AssetDataDTO::fromModel($data);
    }

    public function registerAsset(RegisterAssetDTO $dto)
    {
        DB::beginTransaction();
        try
        {
            $asset = $this->persistRegisteredAsset($dto);

            DB::commit();
            return $asset;
        }
        catch(\Throwable $e)
        {
            DB::rollback();
            throw $e;
        }
    }

    public function registerAssetViaFile(RegisterAssetViaFileDTO $dto)
    {
        $import = $this->resolveImportRecords($dto);
        $records = $import['records'];

        DB::beginTransaction();
        try
        {
            $chunks = array_chunk($records, self::CHUNK_SIZE);

            foreach ($chunks as $chunk) 
            {
                foreach ($chunk as $record) 
                {
                    try 
                    {
                        $this->persistRegisteredAsset($record['dto']);
                    } 
                    catch (\Throwable $e) 
                    {
                        throw new \Exception(
                            'Gagal import pada ' . $record['source_label'] . ': ' . $e->getMessage(),
                            previous: $e
                        );
                    }
                }
            }
            DB::commit();

            return [
                'category' => $dto->category->value,
                'source_type' => $import['source_type'],
                'processed_rows' => count($records),
                'imported_rows' => count($records),
                'sheet_count' => count($import['sheet_names']),
                'sheet_names' => $import['sheet_names'],
            ];
        }
        catch(\Throwable $e)
        {
            DB::rollback();
            throw $e;
        }
    }

    public function updateAsset(string $id, AssetDataDTO $dto)
    {
        $asset = Asset::find($id);
        if(empty($asset))
            throw new \Exception('Asset tidak ditemukan', 404);
        
        $asset->update([
            'account_code' => $dto->accountCode,
            'serial_number' => $dto->serialNumber,
            'unit' => $dto->unit,
            'location' => $dto->location,
            'purchase_year' => $dto->purchaseYear
        ]);

        $handler = AssetFactory::createHandler($asset->category);
        $validatedDetail = $handler->validatePayload($dto->detail);
        $handler->update($asset->id, $validatedDetail);

        return $asset->loadWithRelation();
    }

    public function deleteAsset(string $id)
    {
        $asset = Asset::find($id);
        if(empty($asset))
            throw new \Exception('Asset tidak ditemukan', 404);
        
        DB::transaction(function () use ($asset) {
            $disk = Storage::disk('public');

            if($disk->exists($asset->qr_code_path)) 
                $disk->delete($asset->qr_code_path);

            $asset->delete();
        });
    }

    public function bulkDelete(array $ids)
    {
        DB::transaction(function () use ($ids) {
            foreach ($ids as $id) {
                $this->deleteAsset($id);
            }
        });
    }

    public function downloadQrCode(array $ids)
    {
        $disk = Storage::disk('public');

        $query = Asset::whereNotNull('qr_code_path');

        if(!empty($ids))
            $query->whereIn('id', $ids);

        $assets = $query->get();

        if($assets->isEmpty()) 
            throw new \Exception('QR Code tidak ditemukan');

        /**
         * =============================
         * SINGLE FILE
         * =============================
         */
        if($assets->count() === 1) 
        {
            $asset = $assets->first();

            if(!$disk->exists($asset->qr_code_path))
                throw new \Exception('QR Code tidak ditemukan');

            $filename = $this->makeSafeFilename($asset->account_code) . '.svg';
            return new DownloadFileDTO(
                filename: $filename,
                mimeType: 'image/svg+xml',
                content: $disk->get($asset->qr_code_path)
            );
        }

        /**
         * =============================
         * MULTIPLE → ZIP
         * =============================
         */
        $zip = new ZipArchive();
        $tmpPath = tempnam(sys_get_temp_dir(), 'qr_');

        $zip->open($tmpPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);

        foreach ($assets as $asset) 
        {
            if($disk->exists($asset->qr_code_path)) 
            {
                $filename = $this->makeSafeFilename($asset->account_code) . '.svg';
                $zip->addFromString(
                    $filename,
                    $disk->get($asset->qr_code_path)
                );
            }
        }

        $zip->close();

        return new DownloadFileDTO(
            filename: 'qr-codes.zip',
            mimeType: 'application/zip',
            content: file_get_contents($tmpPath)
        );
    }
}
