<?php 

namespace App\Services\Asset;

use App\DTOs\Asset\AssetDataDTO;
use App\DTOs\Asset\RegisterAssetDTO;
use App\DTOs\Asset\RegisterAssetViaFileDTO;
use App\DTOs\File\DownloadFileDTO;
use App\Enums\Asset\AssetCategory;
use App\Enums\Asset\ComputerComponent;
use App\Enums\Asset\AssetUnit;
use App\Models\Asset\Asset;
use App\Models\Asset\AssetImportBatch;
use App\Support\AssetPublicUrl;
use Illuminate\Support\Collection;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as SpreadsheetDate;
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
    private const COMPUTER_TEMPLATE_REQUIRED_HEADERS = [
        'account_code',
        'location',
        'component_name',
        'brand',
    ];
    private const VEHICLE_TEMPLATE_REQUIRED_HEADERS = [
        'unit',
        'vehicle_type',
        'vehicle_name',
    ];
    private const ELECTRONIC_TEMPLATE_REQUIRED_HEADERS = [
        'unit',
        'electronic_type',
        'asset_name',
    ];
    private const ROOM_INVENTORY_TEMPLATE_REQUIRED_HEADERS = [
        'unit',
        'item_type',
        'item_name',
    ];
    private const BUILDING_INFRASTRUCTURE_TEMPLATE_REQUIRED_HEADERS = [
        'unit',
        'asset_name',
        'asset_type',
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
                    purchasePrice: $this->parseImportedPrice($row['purchase_price'] ?? null),
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
        return match ($category) {
            AssetCategory::AC => $this->extractAirConditionerRecordsFromSpreadsheet($file),
            AssetCategory::COMPUTER => $this->extractComputerRecordsFromSpreadsheet($file),
            AssetCategory::VEHICLE => $this->extractVehicleRecordsFromSpreadsheet($file),
            AssetCategory::ELECTRONIC => $this->extractElectronicRecordsFromSpreadsheet($file),
            AssetCategory::ROOM_INVENTORY => $this->extractRoomInventoryRecordsFromSpreadsheet($file),
            AssetCategory::BUILDING_INFRASTRUCTURE => $this->extractBuildingInfrastructureRecordsFromSpreadsheet($file),
            default => throw new \Exception(
                'Import Excel saat ini tersedia untuk kategori AC, COMPUTER, KENDARAAN, ELEKTRONIK, INVENTARIS RUANGAN, dan BANGUNAN SARANA PRASARANA. Untuk kategori lain, silakan gunakan template CSV.',
                422
            ),
        };
    }

    /**
     * @return array{
     *     records: array<int, array{dto: RegisterAssetDTO, source_label: string}>,
     *     sheet_names: array<int, string>
     * }
     */
    private function extractAirConditionerRecordsFromSpreadsheet($file): array
    {
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
                    'purchase_price' => $this->resolveSpreadsheetCell($row, $headerMap, 'purchase_price'),
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
                        category: AssetCategory::AC,
                        accountCode: $payload['account_code'],
                        serialNumber: $payload['serial_number'],
                        unit: $unit->value,
                        location: $payload['location'],
                        purchaseYear: $payload['purchase_year'],
                        purchasePrice: $this->parseImportedPrice($payload['purchase_price']),
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
     *     sheet_names: array<int, string>
     * }
     */
    private function extractComputerRecordsFromSpreadsheet($file): array
    {
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
                    "Sheet \"{$sheetTitle}\" tidak bisa dipetakan ke unit aset. Gunakan nama sheet yang mengandung TK, SD, atau YPIK.",
                    422
                );
            }

            $headerMap = [];
            $currentAsset = null;

            foreach ($rows as $index => $row) {
                if (!is_array($row) || $this->isSpreadsheetRowEmpty($row)) {
                    continue;
                }

                $candidateHeaderMap = $this->buildComputerSpreadsheetHeaderMap($row);
                if (!empty($candidateHeaderMap)) {
                    $this->assertComputerHeaderRequirements($candidateHeaderMap, $sheetTitle);

                    if ($currentAsset !== null) {
                        $records[] = $this->createComputerImportRecord($currentAsset, $sheetTitle);
                        $currentAsset = null;
                    }

                    $headerMap = $candidateHeaderMap;
                    continue;
                }

                if (empty($headerMap)) {
                    continue;
                }

                $accountCode = $this->resolveSpreadsheetCell($row, $headerMap, 'account_code');
                $componentName = $this->resolveSpreadsheetCell($row, $headerMap, 'component_name');

                if ($accountCode !== null) {
                    if ($currentAsset !== null) {
                        $records[] = $this->createComputerImportRecord($currentAsset, $sheetTitle);
                    }

                    $rowNumber = $index + 1;
                    $location = $this->resolveSpreadsheetCell($row, $headerMap, 'location');
                    if ($location === null) {
                        throw new \Exception(
                            "Lokasi kosong pada sheet \"{$sheetTitle}\" baris ke-{$rowNumber}.",
                            422
                        );
                    }

                    $currentAsset = [
                        'row_number' => $rowNumber,
                        'account_code' => $accountCode,
                        'location' => $location,
                        'serial_number' => $this->resolveSpreadsheetCell($row, $headerMap, 'serial_number'),
                        'purchase_year' => $this->resolveSpreadsheetCell($row, $headerMap, 'purchase_year'),
                        'purchase_price' => $this->resolveSpreadsheetCell($row, $headerMap, 'purchase_price'),
                        'unit' => $unit->value,
                        'components' => [],
                    ];
                }

                if ($currentAsset === null || $componentName === null) {
                    continue;
                }

                $component = $this->normalizeComputerSpreadsheetComponent(
                    $componentName,
                    $this->resolveSpreadsheetCell($row, $headerMap, 'brand'),
                    $this->resolveSpreadsheetCell($row, $headerMap, 'specification'),
                    $this->resolveSpreadsheetCell($row, $headerMap, 'serial_number')
                );

                if ($component !== null) {
                    $currentAsset['components'][] = $component;
                }
            }

            if ($currentAsset !== null) {
                $records[] = $this->createComputerImportRecord($currentAsset, $sheetTitle);
            }
        }

        if (empty($records)) {
            throw new \Exception('Tidak ada data aset komputer yang berhasil dibaca dari template Excel.', 422);
        }

        return [
            'records' => $records,
            'sheet_names' => $sheetNames,
        ];
    }

    /**
     * @return array{
     *     records: array<int, array{dto: RegisterAssetDTO, source_label: string}>,
     *     sheet_names: array<int, string>
     * }
     */
    private function extractVehicleRecordsFromSpreadsheet($file): array
    {
        if (!class_exists(IOFactory::class)) {
            throw new \Exception(
                'Library Excel belum tersedia di server. Jalankan composer install agar phpoffice/phpspreadsheet terpasang.',
                500
            );
        }

        $spreadsheet = IOFactory::load($file->getRealPath());
        $sheet = $spreadsheet->getSheetByName('Data Aset');
        if (!$sheet instanceof Worksheet) {
            throw new \Exception('Sheet "Data Aset" tidak ditemukan pada template kendaraan.', 422);
        }

        $rows = $sheet->toArray(null, true, true, false);
        $sheetTitle = trim((string) $sheet->getTitle());
        $headerMap = [];
        $records = [];

        foreach ($rows as $index => $row) {
            if (!is_array($row) || $this->isSpreadsheetRowEmpty($row)) {
                continue;
            }

            $candidateHeaderMap = $this->buildVehicleSpreadsheetHeaderMap($row);
            if (!empty($candidateHeaderMap)) {
                $this->assertVehicleHeaderRequirements($candidateHeaderMap, $sheetTitle);
                $headerMap = $candidateHeaderMap;
                continue;
            }

            if (empty($headerMap)) {
                continue;
            }

            $payload = [
                'account_code' => $this->resolveSpreadsheetCell($row, $headerMap, 'account_code'),
                'unit' => $this->resolveSpreadsheetCell($row, $headerMap, 'unit'),
                'vehicle_type' => $this->resolveSpreadsheetCell($row, $headerMap, 'vehicle_type'),
                'vehicle_name' => $this->resolveSpreadsheetCell($row, $headerMap, 'vehicle_name'),
                'brand' => $this->resolveSpreadsheetCell($row, $headerMap, 'brand'),
                'model_type' => $this->resolveSpreadsheetCell($row, $headerMap, 'model_type'),
                'vehicle_year' => $this->resolveSpreadsheetCell($row, $headerMap, 'vehicle_year'),
                'color' => $this->resolveSpreadsheetCell($row, $headerMap, 'color'),
                'license_plate' => $this->resolveSpreadsheetCell($row, $headerMap, 'license_plate'),
                'chassis_number' => $this->resolveSpreadsheetCell($row, $headerMap, 'chassis_number'),
                'engine_number' => $this->resolveSpreadsheetCell($row, $headerMap, 'engine_number'),
                'bpkb_name' => $this->resolveSpreadsheetCell($row, $headerMap, 'bpkb_name'),
                'stnk_valid_until' => $this->resolveSpreadsheetCell($row, $headerMap, 'stnk_valid_until'),
                'tax_valid_until' => $this->resolveSpreadsheetCell($row, $headerMap, 'tax_valid_until'),
                'kilometer' => $this->resolveSpreadsheetCell($row, $headerMap, 'kilometer'),
                'acquisition_date' => $this->resolveSpreadsheetCell($row, $headerMap, 'acquisition_date'),
                'purchase_year' => $this->resolveSpreadsheetCell($row, $headerMap, 'purchase_year'),
                'purchase_price' => $this->resolveSpreadsheetCell($row, $headerMap, 'purchase_price'),
                'asset_account_code' => $this->resolveSpreadsheetCell($row, $headerMap, 'asset_account_code'),
                'useful_life_years' => $this->resolveSpreadsheetCell($row, $headerMap, 'useful_life_years'),
                'accumulated_depreciation' => $this->resolveSpreadsheetCell($row, $headerMap, 'accumulated_depreciation'),
                'book_value' => $this->resolveSpreadsheetCell($row, $headerMap, 'book_value'),
                'pic' => $this->resolveSpreadsheetCell($row, $headerMap, 'pic'),
                'condition' => $this->resolveSpreadsheetCell($row, $headerMap, 'condition'),
                'status' => $this->resolveSpreadsheetCell($row, $headerMap, 'status'),
                'notes' => $this->resolveSpreadsheetCell($row, $headerMap, 'notes'),
                'source_data' => $this->resolveSpreadsheetCell($row, $headerMap, 'source_data'),
            ];

            if ($this->isSpreadsheetRowEmpty($payload)) {
                continue;
            }

            $rowNumber = $index + 1;
            $unit = $this->resolveAssetUnitFromValue($payload['unit']);
            if (!$unit instanceof AssetUnit) {
                throw new \Exception(
                    "Unit kendaraan tidak valid pada sheet \"{$sheetTitle}\" baris ke-{$rowNumber}. Gunakan TK, SD, atau YPIK/Yayasan.",
                    422
                );
            }

            $accountCode = $payload['account_code'] ?: $this->generateVehicleAccountCode($payload, $rowNumber);
            $location = $payload['vehicle_name']
                ?: $payload['license_plate']
                ?: $payload['pic']
                ?: $unit->value;

            $records[] = [
                'dto' => new RegisterAssetDTO(
                    category: AssetCategory::VEHICLE,
                    accountCode: $accountCode,
                    serialNumber: $payload['chassis_number'] ?: null,
                    unit: $unit->value,
                    location: $location,
                    purchaseYear: $payload['purchase_year'] ?: $payload['vehicle_year'],
                    purchasePrice: $this->parseImportedPrice($payload['purchase_price']),
                    detail: [
                        'vehicle_type' => $payload['vehicle_type'],
                        'vehicle_name' => $payload['vehicle_name'],
                        'brand' => $payload['brand'],
                        'model_type' => $payload['model_type'],
                        'vehicle_year' => $payload['vehicle_year'],
                        'color' => $payload['color'],
                        'license_plate' => $payload['license_plate'],
                        'chassis_number' => $payload['chassis_number'],
                        'engine_number' => $payload['engine_number'],
                        'bpkb_name' => $payload['bpkb_name'],
                        'stnk_valid_until' => $this->normalizeImportedDate($payload['stnk_valid_until']),
                        'tax_valid_until' => $this->normalizeImportedDate($payload['tax_valid_until']),
                        'kilometer' => $this->parseImportedInteger($payload['kilometer']),
                        'acquisition_date' => $this->normalizeImportedDate($payload['acquisition_date']),
                        'asset_account_code' => $payload['asset_account_code'],
                        'useful_life_years' => $this->parseImportedInteger($payload['useful_life_years']),
                        'accumulated_depreciation' => $this->parseImportedPrice($payload['accumulated_depreciation']),
                        'book_value' => $this->parseImportedPrice($payload['book_value']),
                        'pic' => $payload['pic'],
                        'condition' => $payload['condition'],
                        'status' => $payload['status'],
                        'notes' => $payload['notes'],
                        'source_data' => $payload['source_data'],
                    ]
                ),
                'source_label' => "sheet \"{$sheetTitle}\" baris ke-{$rowNumber}",
            ];
        }

        if (empty($records)) {
            throw new \Exception('Tidak ada data aset kendaraan yang berhasil dibaca dari sheet "Data Aset".', 422);
        }

        return [
            'records' => $records,
            'sheet_names' => [$sheetTitle],
        ];
    }

    /**
     * @return array{
     *     records: array<int, array{dto: RegisterAssetDTO, source_label: string}>,
     *     sheet_names: array<int, string>
     * }
     */
    private function extractElectronicRecordsFromSpreadsheet($file): array
    {
        if (!class_exists(IOFactory::class)) {
            throw new \Exception(
                'Library Excel belum tersedia di server. Jalankan composer install agar phpoffice/phpspreadsheet terpasang.',
                500
            );
        }

        $spreadsheet = IOFactory::load($file->getRealPath());
        $sheet = $spreadsheet->getSheetByName('Data Aset');
        if (!$sheet instanceof Worksheet) {
            throw new \Exception('Sheet "Data Aset" tidak ditemukan pada template elektronik.', 422);
        }

        $rows = $sheet->toArray(null, true, true, false);
        $sheetTitle = trim((string) $sheet->getTitle());
        $headerMap = [];
        $records = [];
        $seenAccountCodes = [];

        foreach ($rows as $index => $row) {
            if (!is_array($row) || $this->isSpreadsheetRowEmpty($row)) {
                continue;
            }

            $candidateHeaderMap = $this->buildElectronicSpreadsheetHeaderMap($row);
            if (!empty($candidateHeaderMap)) {
                $this->assertElectronicHeaderRequirements($candidateHeaderMap, $sheetTitle);
                $headerMap = $candidateHeaderMap;
                continue;
            }

            if (empty($headerMap)) {
                continue;
            }

            $payload = [
                'asset_code' => $this->resolveSpreadsheetCell($row, $headerMap, 'asset_code'),
                'unit' => $this->resolveSpreadsheetCell($row, $headerMap, 'unit'),
                'location' => $this->resolveSpreadsheetCell($row, $headerMap, 'location'),
                'electronic_type' => $this->resolveSpreadsheetCell($row, $headerMap, 'electronic_type'),
                'asset_name' => $this->resolveSpreadsheetCell($row, $headerMap, 'asset_name'),
                'brand' => $this->resolveSpreadsheetCell($row, $headerMap, 'brand'),
                'model_type' => $this->resolveSpreadsheetCell($row, $headerMap, 'model_type'),
                'specification' => $this->resolveSpreadsheetCell($row, $headerMap, 'specification'),
                'serial_number' => $this->resolveSpreadsheetCell($row, $headerMap, 'serial_number'),
                'acquisition_date' => $this->resolveSpreadsheetCell($row, $headerMap, 'acquisition_date'),
                'purchase_year' => $this->resolveSpreadsheetCell($row, $headerMap, 'purchase_year'),
                'purchase_price' => $this->resolveSpreadsheetCell($row, $headerMap, 'purchase_price'),
                'asset_account_code' => $this->resolveSpreadsheetCell($row, $headerMap, 'asset_account_code'),
                'useful_life_years' => $this->resolveSpreadsheetCell($row, $headerMap, 'useful_life_years'),
                'accumulated_depreciation' => $this->resolveSpreadsheetCell($row, $headerMap, 'accumulated_depreciation'),
                'book_value' => $this->resolveSpreadsheetCell($row, $headerMap, 'book_value'),
                'condition' => $this->resolveSpreadsheetCell($row, $headerMap, 'condition'),
                'status' => $this->resolveSpreadsheetCell($row, $headerMap, 'status'),
                'pic' => $this->resolveSpreadsheetCell($row, $headerMap, 'pic'),
                'notes' => $this->resolveSpreadsheetCell($row, $headerMap, 'notes'),
                'source_data' => $this->resolveSpreadsheetCell($row, $headerMap, 'source_data'),
            ];

            if ($this->isSpreadsheetRowEmpty($payload)) {
                continue;
            }

            $rowNumber = $index + 1;
            $unit = $this->resolveAssetUnitFromValue($payload['unit']);
            if (!$unit instanceof AssetUnit) {
                throw new \Exception(
                    "Unit elektronik tidak valid pada sheet \"{$sheetTitle}\" baris ke-{$rowNumber}. Gunakan TK, SD, atau YPIK/Yayasan.",
                    422
                );
            }

            $accountCode = $this->resolveElectronicAccountCode($payload, $rowNumber, $seenAccountCodes);
            $location = $payload['location']
                ?: $payload['asset_name']
                ?: $payload['electronic_type']
                ?: $unit->value;

            $records[] = [
                'dto' => new RegisterAssetDTO(
                    category: AssetCategory::ELECTRONIC,
                    accountCode: $accountCode,
                    serialNumber: null,
                    unit: $unit->value,
                    location: $location,
                    purchaseYear: $payload['purchase_year'],
                    purchasePrice: $this->parseImportedPrice($payload['purchase_price']),
                    detail: [
                        'asset_code' => $payload['asset_code'],
                        'electronic_type' => $payload['electronic_type'],
                        'asset_name' => $payload['asset_name'],
                        'brand' => $payload['brand'],
                        'model_type' => $payload['model_type'],
                        'specification' => $payload['specification'],
                        'serial_number' => $payload['serial_number'],
                        'acquisition_date' => $this->normalizeImportedDate($payload['acquisition_date']),
                        'asset_account_code' => $payload['asset_account_code'],
                        'useful_life_years' => $this->parseImportedInteger($payload['useful_life_years']),
                        'accumulated_depreciation' => $this->parseImportedPrice($payload['accumulated_depreciation']),
                        'book_value' => $this->parseImportedPrice($payload['book_value']),
                        'condition' => $payload['condition'],
                        'status' => $payload['status'],
                        'pic' => $payload['pic'],
                        'notes' => $payload['notes'],
                        'source_data' => $payload['source_data'],
                    ]
                ),
                'source_label' => "sheet \"{$sheetTitle}\" baris ke-{$rowNumber}",
            ];
        }

        if (empty($records)) {
            throw new \Exception('Tidak ada data aset elektronik yang berhasil dibaca dari sheet "Data Aset".', 422);
        }

        return [
            'records' => $records,
            'sheet_names' => [$sheetTitle],
        ];
    }

    /**
     * @return array{
     *     records: array<int, array{dto: RegisterAssetDTO, source_label: string}>,
     *     sheet_names: array<int, string>
     * }
     */
    private function extractRoomInventoryRecordsFromSpreadsheet($file): array
    {
        if (!class_exists(IOFactory::class)) {
            throw new \Exception(
                'Library Excel belum tersedia di server. Jalankan composer install agar phpoffice/phpspreadsheet terpasang.',
                500
            );
        }

        $spreadsheet = IOFactory::load($file->getRealPath());
        $sheet = $spreadsheet->getSheetByName('Data Aset');
        if (!$sheet instanceof Worksheet) {
            throw new \Exception('Sheet "Data Aset" tidak ditemukan pada template inventaris ruangan.', 422);
        }

        $rows = $sheet->toArray(null, true, true, false);
        $sheetTitle = trim((string) $sheet->getTitle());
        $headerMap = [];
        $records = [];
        $seenAccountCodes = [];

        foreach ($rows as $index => $row) {
            if (!is_array($row) || $this->isSpreadsheetRowEmpty($row)) {
                continue;
            }

            $candidateHeaderMap = $this->buildRoomInventorySpreadsheetHeaderMap($row);
            if (!empty($candidateHeaderMap)) {
                $this->assertRoomInventoryHeaderRequirements($candidateHeaderMap, $sheetTitle);
                $headerMap = $candidateHeaderMap;
                continue;
            }

            if (empty($headerMap)) {
                continue;
            }

            $payload = [
                'asset_code' => $this->resolveSpreadsheetCell($row, $headerMap, 'asset_code'),
                'unit' => $this->resolveSpreadsheetCell($row, $headerMap, 'unit'),
                'location' => $this->resolveSpreadsheetCell($row, $headerMap, 'location'),
                'item_type' => $this->resolveSpreadsheetCell($row, $headerMap, 'item_type'),
                'item_name' => $this->resolveSpreadsheetCell($row, $headerMap, 'item_name'),
                'material' => $this->resolveSpreadsheetCell($row, $headerMap, 'material'),
                'size' => $this->resolveSpreadsheetCell($row, $headerMap, 'size'),
                'quantity' => $this->resolveSpreadsheetCell($row, $headerMap, 'quantity'),
                'acquisition_date' => $this->resolveSpreadsheetCell($row, $headerMap, 'acquisition_date'),
                'purchase_year' => $this->resolveSpreadsheetCell($row, $headerMap, 'purchase_year'),
                'unit_price' => $this->resolveSpreadsheetCell($row, $headerMap, 'unit_price'),
                'purchase_price' => $this->resolveSpreadsheetCell($row, $headerMap, 'purchase_price'),
                'asset_account_code' => $this->resolveSpreadsheetCell($row, $headerMap, 'asset_account_code'),
                'useful_life_years' => $this->resolveSpreadsheetCell($row, $headerMap, 'useful_life_years'),
                'accumulated_depreciation' => $this->resolveSpreadsheetCell($row, $headerMap, 'accumulated_depreciation'),
                'book_value' => $this->resolveSpreadsheetCell($row, $headerMap, 'book_value'),
                'condition' => $this->resolveSpreadsheetCell($row, $headerMap, 'condition'),
                'status' => $this->resolveSpreadsheetCell($row, $headerMap, 'status'),
                'notes' => $this->resolveSpreadsheetCell($row, $headerMap, 'notes'),
                'source_data' => $this->resolveSpreadsheetCell($row, $headerMap, 'source_data'),
            ];

            if ($this->isSpreadsheetRowEmpty($payload)) {
                continue;
            }

            $rowNumber = $index + 1;
            $unit = $this->resolveAssetUnitFromValue($payload['unit']);
            if (!$unit instanceof AssetUnit) {
                throw new \Exception(
                    "Unit inventaris ruangan tidak valid pada sheet \"{$sheetTitle}\" baris ke-{$rowNumber}. Gunakan TK, SD, atau YPIK/Yayasan.",
                    422
                );
            }

            $accountCode = $this->resolveRoomInventoryAccountCode($payload, $rowNumber, $seenAccountCodes);
            $location = $payload['location']
                ?: $payload['item_name']
                ?: $payload['item_type']
                ?: $unit->value;

            $records[] = [
                'dto' => new RegisterAssetDTO(
                    category: AssetCategory::ROOM_INVENTORY,
                    accountCode: $accountCode,
                    serialNumber: null,
                    unit: $unit->value,
                    location: $location,
                    purchaseYear: $payload['purchase_year'],
                    purchasePrice: $this->parseImportedPrice($payload['purchase_price']),
                    detail: [
                        'asset_code' => $payload['asset_code'],
                        'item_type' => $payload['item_type'],
                        'item_name' => $payload['item_name'],
                        'material' => $payload['material'],
                        'size' => $payload['size'],
                        'quantity' => $payload['quantity'],
                        'acquisition_date' => $this->normalizeImportedDate($payload['acquisition_date']),
                        'unit_price' => $this->parseImportedPrice($payload['unit_price']),
                        'asset_account_code' => $payload['asset_account_code'],
                        'useful_life_years' => $this->parseImportedInteger($payload['useful_life_years']),
                        'accumulated_depreciation' => $this->parseImportedPrice($payload['accumulated_depreciation']),
                        'book_value' => $this->parseImportedPrice($payload['book_value']),
                        'condition' => $payload['condition'],
                        'status' => $payload['status'],
                        'notes' => $payload['notes'],
                        'source_data' => $payload['source_data'],
                    ]
                ),
                'source_label' => "sheet \"{$sheetTitle}\" baris ke-{$rowNumber}",
            ];
        }

        if (empty($records)) {
            throw new \Exception('Tidak ada data inventaris ruangan yang berhasil dibaca dari sheet "Data Aset".', 422);
        }

        return [
            'records' => $records,
            'sheet_names' => [$sheetTitle],
        ];
    }

    /**
     * @return array{
     *     records: array<int, array{dto: RegisterAssetDTO, source_label: string}>,
     *     sheet_names: array<int, string>
     * }
     */
    private function extractBuildingInfrastructureRecordsFromSpreadsheet($file): array
    {
        if (!class_exists(IOFactory::class)) {
            throw new \Exception(
                'Library Excel belum tersedia di server. Jalankan composer install agar phpoffice/phpspreadsheet terpasang.',
                500
            );
        }

        $spreadsheet = IOFactory::load($file->getRealPath());
        $sheet = $spreadsheet->getSheetByName('Data Aset');
        if (!$sheet instanceof Worksheet) {
            throw new \Exception('Sheet "Data Aset" tidak ditemukan pada template bangunan sarana prasarana.', 422);
        }

        $rows = $sheet->toArray(null, true, true, false);
        $sheetTitle = trim((string) $sheet->getTitle());
        $headerMap = [];
        $records = [];
        $seenAccountCodes = [];

        foreach ($rows as $index => $row) {
            if (!is_array($row) || $this->isSpreadsheetRowEmpty($row)) {
                continue;
            }

            $candidateHeaderMap = $this->buildBuildingInfrastructureSpreadsheetHeaderMap($row);
            if (!empty($candidateHeaderMap)) {
                $this->assertBuildingInfrastructureHeaderRequirements($candidateHeaderMap, $sheetTitle);
                $headerMap = $candidateHeaderMap;
                continue;
            }

            if (empty($headerMap)) {
                continue;
            }

            $payload = [
                'asset_code' => $this->resolveSpreadsheetCell($row, $headerMap, 'asset_code'),
                'unit' => $this->resolveSpreadsheetCell($row, $headerMap, 'unit'),
                'location' => $this->resolveSpreadsheetCell($row, $headerMap, 'location'),
                'asset_name' => $this->resolveSpreadsheetCell($row, $headerMap, 'asset_name'),
                'asset_type' => $this->resolveSpreadsheetCell($row, $headerMap, 'asset_type'),
                'land_area' => $this->resolveSpreadsheetCell($row, $headerMap, 'land_area'),
                'building_area' => $this->resolveSpreadsheetCell($row, $headerMap, 'building_area'),
                'volume_size' => $this->resolveSpreadsheetCell($row, $headerMap, 'volume_size'),
                'document_number' => $this->resolveSpreadsheetCell($row, $headerMap, 'document_number'),
                'acquisition_date' => $this->resolveSpreadsheetCell($row, $headerMap, 'acquisition_date'),
                'purchase_year' => $this->resolveSpreadsheetCell($row, $headerMap, 'purchase_year'),
                'purchase_price' => $this->resolveSpreadsheetCell($row, $headerMap, 'purchase_price'),
                'asset_account_code' => $this->resolveSpreadsheetCell($row, $headerMap, 'asset_account_code'),
                'useful_life_years' => $this->resolveSpreadsheetCell($row, $headerMap, 'useful_life_years'),
                'initial_accumulated_depreciation' => $this->resolveSpreadsheetCell($row, $headerMap, 'initial_accumulated_depreciation'),
                'current_year_depreciation' => $this->resolveSpreadsheetCell($row, $headerMap, 'current_year_depreciation'),
                'accumulated_depreciation' => $this->resolveSpreadsheetCell($row, $headerMap, 'accumulated_depreciation'),
                'book_value' => $this->resolveSpreadsheetCell($row, $headerMap, 'book_value'),
                'condition' => $this->resolveSpreadsheetCell($row, $headerMap, 'condition'),
                'status' => $this->resolveSpreadsheetCell($row, $headerMap, 'status'),
                'responsible_person' => $this->resolveSpreadsheetCell($row, $headerMap, 'responsible_person'),
                'notes' => $this->resolveSpreadsheetCell($row, $headerMap, 'notes'),
                'source_data' => $this->resolveSpreadsheetCell($row, $headerMap, 'source_data'),
            ];

            if ($this->isSpreadsheetRowEmpty($payload)) {
                continue;
            }

            $rowNumber = $index + 1;
            $unit = $this->resolveAssetUnitFromValue($payload['unit']);
            if (!$unit instanceof AssetUnit) {
                throw new \Exception(
                    "Unit bangunan sarana prasarana tidak valid pada sheet \"{$sheetTitle}\" baris ke-{$rowNumber}. Gunakan TK, SD, atau YPIK/Yayasan.",
                    422
                );
            }

            $accountCode = $this->resolveBuildingInfrastructureAccountCode($payload, $rowNumber, $seenAccountCodes);
            $location = $payload['location']
                ?: $payload['asset_name']
                ?: $payload['asset_type']
                ?: $unit->value;

            $records[] = [
                'dto' => new RegisterAssetDTO(
                    category: AssetCategory::BUILDING_INFRASTRUCTURE,
                    accountCode: $accountCode,
                    serialNumber: null,
                    unit: $unit->value,
                    location: $location,
                    purchaseYear: $payload['purchase_year'],
                    purchasePrice: $this->parseImportedPrice($payload['purchase_price']),
                    detail: [
                        'asset_code' => $payload['asset_code'],
                        'asset_name' => $payload['asset_name'],
                        'asset_type' => $payload['asset_type'],
                        'land_area' => $payload['land_area'],
                        'building_area' => $payload['building_area'],
                        'volume_size' => $payload['volume_size'],
                        'document_number' => $payload['document_number'],
                        'acquisition_date' => $this->normalizeImportedDate($payload['acquisition_date']),
                        'asset_account_code' => $payload['asset_account_code'],
                        'useful_life_years' => $this->parseImportedInteger($payload['useful_life_years']),
                        'initial_accumulated_depreciation' => $this->parseImportedPrice($payload['initial_accumulated_depreciation']),
                        'current_year_depreciation' => $this->parseImportedPrice($payload['current_year_depreciation']),
                        'accumulated_depreciation' => $this->parseImportedPrice($payload['accumulated_depreciation']),
                        'book_value' => $this->parseImportedPrice($payload['book_value']),
                        'condition' => $payload['condition'],
                        'status' => $payload['status'],
                        'responsible_person' => $payload['responsible_person'],
                        'notes' => $payload['notes'],
                        'source_data' => $payload['source_data'],
                    ]
                ),
                'source_label' => "sheet \"{$sheetTitle}\" baris ke-{$rowNumber}",
            ];
        }

        if (empty($records)) {
            throw new \Exception('Tidak ada data bangunan sarana prasarana yang berhasil dibaca dari sheet "Data Aset".', 422);
        }

        return [
            'records' => $records,
            'sheet_names' => [$sheetTitle],
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
            in_array($normalized, ['harga', 'price', 'purchaseprice', 'nilaiperolehan', 'nominal'], true) => 'purchase_price',
            default => null,
        };
    }

    private function canonicalComputerHeader(string $header): ?string
    {
        $normalized = $this->normalizeHeaderToken($header);

        return match (true) {
            in_array($normalized, ['accountcode', 'akuncode', 'kodeakun'], true) => 'account_code',
            in_array($normalized, ['lantairuang', 'lokasi', 'ruang'], true) => 'location',
            in_array($normalized, ['unit', 'komponen', 'component'], true) => 'component_name',
            in_array($normalized, ['merk', 'brand'], true) => 'brand',
            in_array($normalized, ['unitwatt', 'specification', 'spesifikasi', 'spek'], true) => 'specification',
            in_array($normalized, ['noserirangka', 'noseri', 'serialnumber', 'nomorseri'], true) => 'serial_number',
            in_array($normalized, ['tahunpembelian', 'purchaseyear', 'tahun'], true) => 'purchase_year',
            in_array($normalized, ['harga', 'price', 'purchaseprice', 'nilaiperolehan', 'nominal'], true) => 'purchase_price',
            default => null,
        };
    }

    private function canonicalVehicleHeader(string $header): ?string
    {
        $normalized = $this->normalizeHeaderToken($header);

        return match (true) {
            in_array($normalized, ['kodeaset', 'assetcode', 'kodeasset', 'accountcode'], true) => 'account_code',
            in_array($normalized, ['unit'], true) => 'unit',
            in_array($normalized, ['jeniskendaraan', 'vehicletype', 'jenis'], true) => 'vehicle_type',
            in_array($normalized, ['namakendaraan', 'vehiclename', 'nama'], true) => 'vehicle_name',
            in_array($normalized, ['merk', 'brand'], true) => 'brand',
            in_array($normalized, ['tipemodel', 'tipe', 'model', 'modeltype'], true) => 'model_type',
            in_array($normalized, ['tahunkendaraan', 'vehicleyear'], true) => 'vehicle_year',
            in_array($normalized, ['warna', 'color'], true) => 'color',
            in_array($normalized, ['nomorpolisi', 'nopol', 'platnomor', 'licenseplate'], true) => 'license_plate',
            in_array($normalized, ['nomorrangka', 'norangka', 'chassisnumber'], true) => 'chassis_number',
            in_array($normalized, ['nomormesin', 'nomesin', 'enginenumber'], true) => 'engine_number',
            in_array($normalized, ['bpkbatasnama', 'namabpkb', 'bpkbname'], true) => 'bpkb_name',
            in_array($normalized, ['stnkberlakusampai', 'stnkvaliduntil'], true) => 'stnk_valid_until',
            in_array($normalized, ['pajakberlakusampai', 'taxvaliduntil'], true) => 'tax_valid_until',
            in_array($normalized, ['kilometer', 'km', 'odometer'], true) => 'kilometer',
            in_array($normalized, ['tanggalperolehan', 'acquisitiondate'], true) => 'acquisition_date',
            in_array($normalized, ['tahunperolehan', 'tahunpembelian', 'purchaseyear'], true) => 'purchase_year',
            in_array($normalized, ['hargaperolehan', 'harga', 'purchaseprice', 'nilaiperolehan'], true) => 'purchase_price',
            in_array($normalized, ['kodeakunaset', 'assetaccountcode', 'kodeakun'], true) => 'asset_account_code',
            in_array($normalized, ['umurmanfaat', 'usefullifeyears', 'umurmanfaattahun'], true) => 'useful_life_years',
            in_array($normalized, ['akumpenyusutan', 'akumulasipenyusutan', 'accumulateddepreciation'], true) => 'accumulated_depreciation',
            in_array($normalized, ['nilaibuku', 'bookvalue'], true) => 'book_value',
            in_array($normalized, ['penggunapic', 'pic', 'pengguna'], true) => 'pic',
            in_array($normalized, ['kondisi', 'condition'], true) => 'condition',
            in_array($normalized, ['status'], true) => 'status',
            in_array($normalized, ['keterangan', 'notes', 'note'], true) => 'notes',
            in_array($normalized, ['sumberdata', 'sumber', 'sourcedata'], true) => 'source_data',
            default => null,
        };
    }

    private function canonicalElectronicHeader(string $header): ?string
    {
        $normalized = $this->normalizeHeaderToken($header);

        return match (true) {
            in_array($normalized, ['kodeaset', 'assetcode', 'kodeasset', 'accountcode'], true) => 'asset_code',
            in_array($normalized, ['unit'], true) => 'unit',
            in_array($normalized, ['lokasi', 'location', 'ruang', 'lantairuang'], true) => 'location',
            in_array($normalized, ['jeniselektronik', 'electronictype', 'jenis'], true) => 'electronic_type',
            in_array($normalized, ['namaaset', 'assetname', 'nama'], true) => 'asset_name',
            in_array($normalized, ['merk', 'brand'], true) => 'brand',
            in_array($normalized, ['modeltipe', 'tipemodel', 'tipe', 'model', 'modeltype'], true) => 'model_type',
            in_array($normalized, ['spesifikasi', 'specification', 'spek'], true) => 'specification',
            in_array($normalized, ['nomorseri', 'noseri', 'serialnumber'], true) => 'serial_number',
            in_array($normalized, ['tanggalperolehan', 'acquisitiondate'], true) => 'acquisition_date',
            in_array($normalized, ['tahunperolehan', 'tahunpembelian', 'purchaseyear'], true) => 'purchase_year',
            in_array($normalized, ['hargaperolehan', 'harga', 'purchaseprice', 'nilaiperolehan'], true) => 'purchase_price',
            in_array($normalized, ['kodeakunaset', 'assetaccountcode', 'kodeakun'], true) => 'asset_account_code',
            in_array($normalized, ['umurmanfaat', 'usefullifeyears', 'umurmanfaattahun'], true) => 'useful_life_years',
            in_array($normalized, ['akumpenyusutan', 'akumulasipenyusutan', 'accumulateddepreciation'], true) => 'accumulated_depreciation',
            in_array($normalized, ['nilaibuku', 'bookvalue'], true) => 'book_value',
            in_array($normalized, ['kondisi', 'condition'], true) => 'condition',
            in_array($normalized, ['status'], true) => 'status',
            in_array($normalized, ['picpengguna', 'penggunapic', 'pic', 'pengguna'], true) => 'pic',
            in_array($normalized, ['keterangan', 'notes', 'note'], true) => 'notes',
            in_array($normalized, ['sumberdata', 'sumber', 'sourcedata'], true) => 'source_data',
            default => null,
        };
    }

    private function canonicalRoomInventoryHeader(string $header): ?string
    {
        $normalized = $this->normalizeHeaderToken($header);

        return match (true) {
            in_array($normalized, ['kodeaset', 'assetcode', 'kodeasset', 'accountcode'], true) => 'asset_code',
            in_array($normalized, ['unit'], true) => 'unit',
            in_array($normalized, ['lokasiruangan', 'lokasi', 'ruangan', 'roomlocation', 'location'], true) => 'location',
            in_array($normalized, ['jenisbarang', 'itemtype', 'jenis'], true) => 'item_type',
            in_array($normalized, ['namabarang', 'itemname', 'nama'], true) => 'item_name',
            in_array($normalized, ['bahan', 'material'], true) => 'material',
            in_array($normalized, ['ukuran', 'size'], true) => 'size',
            in_array($normalized, ['jumlah', 'quantity', 'qty'], true) => 'quantity',
            in_array($normalized, ['tanggalperolehan', 'acquisitiondate'], true) => 'acquisition_date',
            in_array($normalized, ['tahunperolehan', 'tahunpembelian', 'purchaseyear'], true) => 'purchase_year',
            in_array($normalized, ['hargasatuan', 'unitprice'], true) => 'unit_price',
            in_array($normalized, ['totalharga', 'hargaperolehan', 'harga', 'purchaseprice', 'nilaiperolehan'], true) => 'purchase_price',
            in_array($normalized, ['kodeakunaset', 'assetaccountcode', 'kodeakun'], true) => 'asset_account_code',
            in_array($normalized, ['umurmanfaat', 'usefullifeyears', 'umurmanfaattahun'], true) => 'useful_life_years',
            in_array($normalized, ['akumpenyusutan', 'akumulasipenyusutan', 'accumulateddepreciation'], true) => 'accumulated_depreciation',
            in_array($normalized, ['nilaibuku', 'bookvalue'], true) => 'book_value',
            in_array($normalized, ['kondisi', 'condition'], true) => 'condition',
            in_array($normalized, ['status'], true) => 'status',
            in_array($normalized, ['keterangan', 'notes', 'note'], true) => 'notes',
            in_array($normalized, ['sumberdata', 'sumber', 'sourcedata'], true) => 'source_data',
            default => null,
        };
    }

    private function canonicalBuildingInfrastructureHeader(string $header): ?string
    {
        $normalized = $this->normalizeHeaderToken($header);

        return match (true) {
            in_array($normalized, ['kodeaset', 'assetcode', 'kodeasset', 'accountcode'], true) => 'asset_code',
            in_array($normalized, ['unit'], true) => 'unit',
            in_array($normalized, ['lokasi', 'location'], true) => 'location',
            in_array($normalized, ['namaaset', 'assetname', 'nama'], true) => 'asset_name',
            in_array($normalized, ['jenisaset', 'assettype', 'jenis'], true) => 'asset_type',
            in_array($normalized, ['luastanah', 'landarea'], true) => 'land_area',
            in_array($normalized, ['luasbangunan', 'buildingarea'], true) => 'building_area',
            in_array($normalized, ['volumeukuran', 'volume', 'ukuran', 'volumesize'], true) => 'volume_size',
            in_array($normalized, ['nomordokumen', 'nodokumen', 'documentnumber'], true) => 'document_number',
            in_array($normalized, ['tanggalperolehan', 'acquisitiondate'], true) => 'acquisition_date',
            in_array($normalized, ['tahunperolehan', 'tahunpembelian', 'purchaseyear'], true) => 'purchase_year',
            in_array($normalized, ['hargaperolehan', 'harga', 'purchaseprice', 'nilaiperolehan'], true) => 'purchase_price',
            in_array($normalized, ['kodeakunaset', 'assetaccountcode', 'kodeakun'], true) => 'asset_account_code',
            in_array($normalized, ['umurmanfaat', 'usefullifeyears', 'umurmanfaattahun'], true) => 'useful_life_years',
            in_array($normalized, ['akumpenyusutanawal', 'initialaccumulateddepreciation'], true) => 'initial_accumulated_depreciation',
            in_array($normalized, ['penyusutantahun2025', 'penyusutantahunberjalan', 'currentyeardepreciation'], true) => 'current_year_depreciation',
            in_array($normalized, ['akumpenyusutan', 'akumulasipenyusutan', 'accumulateddepreciation'], true) => 'accumulated_depreciation',
            in_array($normalized, ['nilaibuku', 'bookvalue'], true) => 'book_value',
            in_array($normalized, ['kondisi', 'condition'], true) => 'condition',
            in_array($normalized, ['status'], true) => 'status',
            in_array($normalized, ['penanggungjawab', 'responsibleperson', 'pic'], true) => 'responsible_person',
            in_array($normalized, ['keterangan', 'notes', 'note'], true) => 'notes',
            in_array($normalized, ['sumberdata', 'sumber', 'sourcedata'], true) => 'source_data',
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
     * @param array<int, mixed> $row
     * @return array<string, int>
     */
    private function buildComputerSpreadsheetHeaderMap(array $row): array
    {
        $headerMap = [];

        foreach ($row as $index => $value) {
            $canonicalHeader = $this->canonicalComputerHeader((string) $value);
            if ($canonicalHeader === null || array_key_exists($canonicalHeader, $headerMap)) {
                continue;
            }

            $headerMap[$canonicalHeader] = $index;
        }

        return $headerMap;
    }

    /**
     * @param array<int, mixed> $row
     * @return array<string, int>
     */
    private function buildVehicleSpreadsheetHeaderMap(array $row): array
    {
        $headerMap = [];

        foreach ($row as $index => $value) {
            $canonicalHeader = $this->canonicalVehicleHeader((string) $value);
            if ($canonicalHeader === null || array_key_exists($canonicalHeader, $headerMap)) {
                continue;
            }

            $headerMap[$canonicalHeader] = $index;
        }

        return $headerMap;
    }

    /**
     * @param array<int, mixed> $row
     * @return array<string, int>
     */
    private function buildElectronicSpreadsheetHeaderMap(array $row): array
    {
        $headerMap = [];

        foreach ($row as $index => $value) {
            $canonicalHeader = $this->canonicalElectronicHeader((string) $value);
            if ($canonicalHeader === null || array_key_exists($canonicalHeader, $headerMap)) {
                continue;
            }

            $headerMap[$canonicalHeader] = $index;
        }

        return $headerMap;
    }

    /**
     * @param array<int, mixed> $row
     * @return array<string, int>
     */
    private function buildRoomInventorySpreadsheetHeaderMap(array $row): array
    {
        $headerMap = [];

        foreach ($row as $index => $value) {
            $canonicalHeader = $this->canonicalRoomInventoryHeader((string) $value);
            if ($canonicalHeader === null || array_key_exists($canonicalHeader, $headerMap)) {
                continue;
            }

            $headerMap[$canonicalHeader] = $index;
        }

        return $headerMap;
    }

    /**
     * @param array<int, mixed> $row
     * @return array<string, int>
     */
    private function buildBuildingInfrastructureSpreadsheetHeaderMap(array $row): array
    {
        $headerMap = [];

        foreach ($row as $index => $value) {
            $canonicalHeader = $this->canonicalBuildingInfrastructureHeader((string) $value);
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
     * @param array<string, int> $headerMap
     */
    private function assertComputerHeaderRequirements(array $headerMap, string $sheetTitle): void
    {
        foreach (self::COMPUTER_TEMPLATE_REQUIRED_HEADERS as $requiredHeader) {
            if (!array_key_exists($requiredHeader, $headerMap)) {
                throw new \Exception(
                    "Header \"{$requiredHeader}\" tidak ditemukan pada sheet \"{$sheetTitle}\".",
                    422
                );
            }
        }
    }

    /**
     * @param array<string, int> $headerMap
     */
    private function assertVehicleHeaderRequirements(array $headerMap, string $sheetTitle): void
    {
        foreach (self::VEHICLE_TEMPLATE_REQUIRED_HEADERS as $requiredHeader) {
            if (!array_key_exists($requiredHeader, $headerMap)) {
                throw new \Exception(
                    "Header \"{$requiredHeader}\" tidak ditemukan pada sheet \"{$sheetTitle}\".",
                    422
                );
            }
        }
    }

    /**
     * @param array<string, int> $headerMap
     */
    private function assertElectronicHeaderRequirements(array $headerMap, string $sheetTitle): void
    {
        foreach (self::ELECTRONIC_TEMPLATE_REQUIRED_HEADERS as $requiredHeader) {
            if (!array_key_exists($requiredHeader, $headerMap)) {
                throw new \Exception(
                    "Header \"{$requiredHeader}\" tidak ditemukan pada sheet \"{$sheetTitle}\".",
                    422
                );
            }
        }
    }

    /**
     * @param array<string, int> $headerMap
     */
    private function assertRoomInventoryHeaderRequirements(array $headerMap, string $sheetTitle): void
    {
        foreach (self::ROOM_INVENTORY_TEMPLATE_REQUIRED_HEADERS as $requiredHeader) {
            if (!array_key_exists($requiredHeader, $headerMap)) {
                throw new \Exception(
                    "Header \"{$requiredHeader}\" tidak ditemukan pada sheet \"{$sheetTitle}\".",
                    422
                );
            }
        }
    }

    /**
     * @param array<string, int> $headerMap
     */
    private function assertBuildingInfrastructureHeaderRequirements(array $headerMap, string $sheetTitle): void
    {
        foreach (self::BUILDING_INFRASTRUCTURE_TEMPLATE_REQUIRED_HEADERS as $requiredHeader) {
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

        $normalizedValue = preg_replace('/\s+/u', ' ', trim((string) $value)) ?? trim((string) $value);
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

    private function normalizeComputerComponentName(string $componentName): ?ComputerComponent
    {
        $normalized = $this->normalizeHeaderToken($componentName);

        return match ($normalized) {
            'monitor' => ComputerComponent::MONITOR,
            'motherboard' => ComputerComponent::MOTHERBOARD,
            'processor' => ComputerComponent::PROCESSOR,
            'ram' => ComputerComponent::RAM,
            'storage' => ComputerComponent::STORAGE,
            'graphiccard', 'gpu', 'vga' => ComputerComponent::GPU,
            'keyboardmouse', 'keyboard', 'mouse' => ComputerComponent::KEYBOARD_MOUSE,
            'cpu' => null,
            default => null,
        };
    }

    private function normalizeComputerSpreadsheetComponent(
        string $componentName,
        ?string $brand,
        ?string $specification,
        ?string $serialNumber
    ): ?array {
        $componentType = $this->normalizeComputerComponentName($componentName);
        if (!$componentType instanceof ComputerComponent) {
            return null;
        }

        return [
            'component_type' => $componentType->value,
            'brand' => $brand,
            'specification' => $specification,
            'serial_number' => $serialNumber,
        ];
    }

    /**
     * @param array{
     *     row_number: int,
     *     account_code: string,
     *     location: string,
     *     serial_number: ?string,
     *     purchase_year: ?string,
     *     purchase_price: ?string,
     *     unit: string,
     *     components: array<int, array<string, ?string>>
     * } $asset
     * @return array{dto: RegisterAssetDTO, source_label: string}
     */
    private function createComputerImportRecord(array $asset, string $sheetTitle): array
    {
        if (empty($asset['components'])) {
            throw new \Exception(
                "Komponen komputer tidak ditemukan pada sheet \"{$sheetTitle}\" baris ke-{$asset['row_number']}.",
                422
            );
        }

        return [
            'dto' => new RegisterAssetDTO(
                category: AssetCategory::COMPUTER,
                accountCode: $asset['account_code'],
                serialNumber: $asset['serial_number'],
                unit: $asset['unit'],
                location: $asset['location'],
                purchaseYear: $asset['purchase_year'],
                purchasePrice: $this->parseImportedPrice($asset['purchase_price'] ?? null),
                detail: [
                    'components' => $asset['components'],
                ]
            ),
            'source_label' => "sheet \"{$sheetTitle}\" baris ke-{$asset['row_number']}",
        ];
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

    private function resolveAssetUnitFromValue(?string $value): ?AssetUnit
    {
        if (!filled($value)) {
            return null;
        }

        $normalized = strtoupper(trim((string) $value));

        if (preg_match('/\bTK\b|TKIA/', $normalized) === 1) {
            return AssetUnit::TK;
        }

        if (preg_match('/\bSD\b|SDIA/', $normalized) === 1) {
            return AssetUnit::SD;
        }

        if (
            str_contains($normalized, 'YPIK')
            || str_contains($normalized, 'YAYASAN')
            || str_contains($normalized, 'SEKRETARIAT')
            || str_contains($normalized, 'PAM JAYA')
        ) {
            return AssetUnit::YAYASAN;
        }

        return AssetUnit::tryFrom((string) $value);
    }

    /**
     * @param array<string, ?string> $payload
     */
    private function generateVehicleAccountCode(array $payload, int $rowNumber): string
    {
        $source = $payload['license_plate']
            ?: $payload['chassis_number']
            ?: $payload['engine_number']
            ?: $payload['vehicle_name']
            ?: (string) $rowNumber;

        $normalized = preg_replace('/[^A-Za-z0-9]+/', '', strtoupper((string) $source)) ?: (string) $rowNumber;

        return 'KENDARAAN-' . $normalized;
    }

    /**
     * @param array<string, ?string> $payload
     * @param array<string, bool> $seenAccountCodes
     */
    private function resolveElectronicAccountCode(array $payload, int $rowNumber, array &$seenAccountCodes): string
    {
        $source = $payload['asset_code']
            ?: $payload['serial_number']
            ?: $payload['asset_name']
            ?: $payload['electronic_type']
            ?: (string) $rowNumber;

        $normalized = preg_replace('/[^A-Za-z0-9]+/', '', strtoupper((string) $source)) ?: (string) $rowNumber;
        $normalized = substr($normalized, 0, 80);
        $base = 'ELEKTRONIK-' . $normalized;
        $candidate = $base;

        if (array_key_exists($candidate, $seenAccountCodes)) {
            $candidate = $base . '-ROW-' . $rowNumber;
        }

        $suffix = 2;
        while (array_key_exists($candidate, $seenAccountCodes)) {
            $candidate = $base . '-ROW-' . $rowNumber . '-' . $suffix;
            $suffix++;
        }

        $seenAccountCodes[$candidate] = true;

        return $candidate;
    }

    /**
     * @param array<string, ?string> $payload
     * @param array<string, bool> $seenAccountCodes
     */
    private function resolveRoomInventoryAccountCode(array $payload, int $rowNumber, array &$seenAccountCodes): string
    {
        $source = $payload['asset_code']
            ?: $payload['item_name']
            ?: $payload['item_type']
            ?: (string) $rowNumber;

        $normalized = preg_replace('/[^A-Za-z0-9]+/', '', strtoupper((string) $source)) ?: (string) $rowNumber;
        $normalized = substr($normalized, 0, 80);
        $base = 'INV-RUANGAN-' . $normalized;
        $candidate = $base;

        if (array_key_exists($candidate, $seenAccountCodes)) {
            $candidate = $base . '-ROW-' . $rowNumber;
        }

        $suffix = 2;
        while (array_key_exists($candidate, $seenAccountCodes)) {
            $candidate = $base . '-ROW-' . $rowNumber . '-' . $suffix;
            $suffix++;
        }

        $seenAccountCodes[$candidate] = true;

        return $candidate;
    }

    /**
     * @param array<string, ?string> $payload
     * @param array<string, bool> $seenAccountCodes
     */
    private function resolveBuildingInfrastructureAccountCode(array $payload, int $rowNumber, array &$seenAccountCodes): string
    {
        $source = $payload['asset_code']
            ?: $payload['document_number']
            ?: $payload['asset_name']
            ?: $payload['asset_type']
            ?: (string) $rowNumber;

        $normalized = preg_replace('/[^A-Za-z0-9]+/', '', strtoupper((string) $source)) ?: (string) $rowNumber;
        $normalized = substr($normalized, 0, 80);
        $base = 'BANGUNAN-PRASARANA-' . $normalized;
        $candidate = $base;

        if (array_key_exists($candidate, $seenAccountCodes)) {
            $candidate = $base . '-ROW-' . $rowNumber;
        }

        $suffix = 2;
        while (array_key_exists($candidate, $seenAccountCodes)) {
            $candidate = $base . '-ROW-' . $rowNumber . '-' . $suffix;
            $suffix++;
        }

        $seenAccountCodes[$candidate] = true;

        return $candidate;
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

    private function resolveUploadedFileName($file): string
    {
        if (method_exists($file, 'getClientOriginalName')) {
            $name = trim((string) $file->getClientOriginalName());
            if ($name !== '') {
                return $name;
            }
        }

        if (method_exists($file, 'getFilename')) {
            return (string) $file->getFilename();
        }

        return 'asset-import-file';
    }

    private function normalizeDateFilter(?string $value, bool $endOfDay = false): ?Carbon
    {
        if (!filled($value)) {
            return null;
        }

        try {
            $date = Carbon::parse($value);
        } catch (\Throwable) {
            return null;
        }

        return $endOfDay ? $date->endOfDay() : $date->startOfDay();
    }

    private function errorStatusCode(\Throwable $throwable): int
    {
        $code = (int) $throwable->getCode();

        return ($code >= 400 && $code <= 599) ? $code : 500;
    }

    private function parseImportedPrice(mixed $value): ?float
    {
        if ($value === null) {
            return null;
        }

        if (is_numeric($value)) {
            $numericValue = round((float) $value, 2);
            return $numericValue >= 0 ? $numericValue : null;
        }

        $normalized = trim((string) $value);
        if ($normalized === '') {
            return null;
        }

        $normalized = preg_replace('/[^0-9,\.\-]/', '', $normalized) ?? '';
        if ($normalized === '' || $normalized === '-') {
            return null;
        }

        $hasComma = str_contains($normalized, ',');
        $hasDot = str_contains($normalized, '.');

        if ($hasComma && $hasDot) {
            if (strrpos($normalized, ',') > strrpos($normalized, '.')) {
                $normalized = str_replace('.', '', $normalized);
                $normalized = str_replace(',', '.', $normalized);
            } else {
                $normalized = str_replace(',', '', $normalized);
            }
        } elseif ($hasComma) {
            $segments = explode(',', $normalized);
            $lastSegment = end($segments);

            if ($lastSegment !== false && strlen($lastSegment) <= 2) {
                $normalized = str_replace('.', '', $normalized);
                $normalized = str_replace(',', '.', $normalized);
            } else {
                $normalized = str_replace(',', '', $normalized);
            }
        } elseif ($hasDot) {
            $segments = explode('.', $normalized);
            $lastSegment = end($segments);

            if ($lastSegment !== false && strlen($lastSegment) > 2) {
                $normalized = str_replace('.', '', $normalized);
            }
        }

        if (!is_numeric($normalized)) {
            return null;
        }

        $numericValue = round((float) $normalized, 2);
        return $numericValue >= 0 ? $numericValue : null;
    }

    private function parseImportedInteger(mixed $value): ?int
    {
        $price = $this->parseImportedPrice($value);
        if ($price === null) {
            return null;
        }

        return max(0, (int) round($price));
    }

    private function normalizeImportedDate(mixed $value): ?string
    {
        if ($value === null || trim((string) $value) === '') {
            return null;
        }

        if (is_numeric($value)) {
            try {
                return Carbon::instance(SpreadsheetDate::excelToDateTimeObject((float) $value))->format('Y-m-d');
            } catch (\Throwable) {
                return null;
            }
        }

        try {
            return Carbon::parse((string) $value)->format('Y-m-d');
        } catch (\Throwable) {
            return null;
        }
    }

    private function storeImportBatch(
        RegisterAssetViaFileDTO $dto,
        array $import,
        int $importedRows,
        int $createdRows,
        int $updatedRows
    ): ?AssetImportBatch
    {
        if (!Schema::hasTable('asset_import_batches')) {
            return null;
        }

        return AssetImportBatch::create([
            'category' => $dto->category,
            'source_type' => $import['source_type'],
            'source_file_name' => $this->resolveUploadedFileName($dto->file),
            'processed_rows' => $importedRows,
            'imported_rows' => $importedRows,
            'sheet_count' => count($import['sheet_names']),
            'sheet_names' => $import['sheet_names'],
            'metadata' => [
                'category_label' => $dto->category->label(),
                'created_rows' => $createdRows,
                'updated_rows' => $updatedRows,
            ],
            'imported_by' => auth()->id(),
        ]);
    }

    private function resolveValidatedAssetPayload(
        RegisterAssetDTO $dto,
        ?Asset $asset = null,
        array $extraAttributes = []
    ): array
    {
        $assetData = Arr::except($dto->toArray(), 'detail');

        if (
            $asset !== null
            && ($assetData['purchase_price'] ?? null) === null
            && array_key_exists('last_imported_at', $extraAttributes)
            && $asset->purchase_price !== null
        ) {
            $assetData['purchase_price'] = (float) $asset->purchase_price;
        }

        return array_merge(
            Asset::validateRegistrationPayload($assetData, $asset?->id),
            $extraAttributes
        );
    }

    private function removeDetailByCategory(Asset $asset, AssetCategory $category): void
    {
        match ($category) {
            AssetCategory::AC,
            AssetCategory::OTHER => $asset->airConditionerDetail()->delete(),
            AssetCategory::COMPUTER => $asset->computerComponents()->delete(),
            AssetCategory::VEHICLE => $asset->vehicleDetail()->delete(),
            AssetCategory::ELECTRONIC => $asset->electronicDetail()->delete(),
            AssetCategory::ROOM_INVENTORY => $asset->roomInventoryDetail()->delete(),
            AssetCategory::BUILDING_INFRASTRUCTURE => $asset->buildingInfrastructureDetail()->delete(),
        };
    }

    private function persistAssetDetail(Asset $asset, RegisterAssetDTO $dto): void
    {
        $assetDetailHandler = AssetFactory::createHandler($dto->category);
        $relationName = $assetDetailHandler->getRelationName();

        if ($relationName === '') {
            return;
        }

        // Category-specific detail is kept separate from the asset master.
        // If automated depreciation is implemented later, prefer writing
        // finance policy records in a dedicated table rather than mixing
        // them into these operational detail tables.
        $validatedDetail = $assetDetailHandler->validatePayload($dto->detail);
        $asset->unsetRelation($relationName);
        $asset->loadMissing($relationName);

        $existingRelation = $asset->getRelation($relationName);
        $hasExistingDetail = $existingRelation instanceof Collection
            ? $existingRelation->isNotEmpty()
            : $existingRelation !== null;

        if ($hasExistingDetail) {
            $assetDetailHandler->update($asset->id, $validatedDetail);
            return;
        }

        $assetDetailHandler->insert($asset->id, $validatedDetail);
    }

    private function createRegisteredAsset(RegisterAssetDTO $dto, array $extraAttributes = []): Asset
    {
        Log::info($dto->toArray());

        // The first transaction writes the asset identity that is used by
        // inventory, maintenance, QR detail pages, and finance lookups.
        $validatedAssetData = $this->resolveValidatedAssetPayload($dto, extraAttributes: $extraAttributes);

        $asset = Asset::create($validatedAssetData);
        $asset->qr_code_path = $asset->generateQRCode();
        $asset->save();

        $this->persistAssetDetail($asset, $dto);

        return $asset->loadWithRelation();
    }

    private function upsertRegisteredAsset(RegisterAssetDTO $dto, array $extraAttributes = []): array
    {
        $existingAsset = Asset::where('account_code', $dto->accountCode)->first();
        if (!$existingAsset) {
            return [
                'asset' => $this->createRegisteredAsset($dto, $extraAttributes),
                'action' => 'created',
            ];
        }

        $validatedAssetData = $this->resolveValidatedAssetPayload($dto, $existingAsset, $extraAttributes);
        $previousCategory = $existingAsset->category;

        $existingAsset->update($validatedAssetData);

        if ($previousCategory !== $dto->category) {
            $this->removeDetailByCategory($existingAsset, $previousCategory);
        }

        $this->ensureQrCode($existingAsset);
        $this->persistAssetDetail($existingAsset->fresh(), $dto);

        return [
            'asset' => $existingAsset->fresh()->loadWithRelation(),
            'action' => 'updated',
        ];
    }

    private function makeSafeFilename(string $name): string
    {
        return preg_replace('/[^A-Za-z0-9_\-]/', '_', $name);
    }

    public function getAssets(
        ?string $keyword = null,
        ?AssetCategory $category = null,
        ?AssetUnit $unit = null,
        ?int $page = 1,
        ?int $pageSize = 10,
        ?string $recordedFrom = null,
        ?string $recordedUntil = null,
        ?string $importFile = null
    )
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
            $relationName = AssetFactory::createHandler($category)->getRelationName();
            if ($relationName !== '') {
                $query->with($relationName);
            }
        }

        if($unit)
        {
            $query->where('unit', $unit);
        }

        if (filled($importFile)) {
            $query->where('last_import_file_name', 'like', '%' . trim($importFile) . '%');
        }

        $recordedFromDate = $this->normalizeDateFilter($recordedFrom);
        if ($recordedFromDate) {
            $query->where(function ($q) use ($recordedFromDate) {
                $q->where(function ($subQuery) use ($recordedFromDate) {
                    $subQuery->whereNotNull('last_imported_at')
                        ->where('last_imported_at', '>=', $recordedFromDate);
                })->orWhere(function ($subQuery) use ($recordedFromDate) {
                    $subQuery->whereNull('last_imported_at')
                        ->where('updated_at', '>=', $recordedFromDate);
                });
            });
        }

        $recordedUntilDate = $this->normalizeDateFilter($recordedUntil, true);
        if ($recordedUntilDate) {
            $query->where(function ($q) use ($recordedUntilDate) {
                $q->where(function ($subQuery) use ($recordedUntilDate) {
                    $subQuery->whereNotNull('last_imported_at')
                        ->where('last_imported_at', '<=', $recordedUntilDate);
                })->orWhere(function ($subQuery) use ($recordedUntilDate) {
                    $subQuery->whereNull('last_imported_at')
                        ->where('updated_at', '<=', $recordedUntilDate);
                });
            });
        }

        return $query
            ->orderByDesc(DB::raw('COALESCE(last_imported_at, updated_at)'))
            ->orderBy('account_code', 'asc')
            ->paginate($pageSize, ['*'], 'page', $page)
            ->appends(array_filter([
                'keyword' => $keyword,
                'category' => $category?->value,
                'unit' => $unit?->value,
                'page_size' => $pageSize,
                'recorded_from' => $recordedFrom,
                'recorded_until' => $recordedUntil,
                'import_file' => $importFile,
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

    public function ensureQrCode(Asset $asset): Asset
    {
        $disk = Storage::disk('public');
        $path = $asset->qr_code_path;

        if (filled($path) && $disk->exists($path)) {
            return $asset;
        }

        $asset->qr_code_path = $asset->generateQRCode();
        $asset->save();

        return $asset;
    }

    public function getAssetQrCodeDetail(string $id): array
    {
        $asset = Asset::find($id);
        if(empty($asset))
            throw new \Exception('Asset tidak ditemukan', 404);

        $asset = $this->ensureQrCode($asset);
        $asset->loadWithRelation();

        return [
            'asset' => AssetDataDTO::fromModel($asset),
            'qr_svg' => Storage::disk('public')->get((string) $asset->qr_code_path),
            'qr_path' => (string) $asset->qr_code_path,
            'public_url' => AssetPublicUrl::detailUrl((string) $asset->id),
        ];
    }

    public function registerAsset(RegisterAssetDTO $dto)
    {
        DB::beginTransaction();
        try
        {
            $asset = $this->createRegisteredAsset($dto);

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
        $importBatch = null;
        $createdRows = 0;
        $updatedRows = 0;
        $importedAt = now();
        $importMetadata = [
            'last_import_file_name' => $this->resolveUploadedFileName($dto->file),
            'last_imported_at' => $importedAt,
        ];

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
                        $result = $this->upsertRegisteredAsset($record['dto'], $importMetadata);
                        if (($result['action'] ?? null) === 'created') {
                            $createdRows++;
                        } else {
                            $updatedRows++;
                        }
                    } 
                    catch (\Throwable $e) 
                    {
                        throw new \Exception(
                            'Gagal import pada ' . $record['source_label'] . ': ' . $e->getMessage(),
                            $this->errorStatusCode($e),
                            previous: $e
                        );
                    }
                }
            }

            $importBatch = $this->storeImportBatch($dto, $import, count($records), $createdRows, $updatedRows);
            DB::commit();

            return [
                'category' => $dto->category->value,
                'source_type' => $import['source_type'],
                'processed_rows' => count($records),
                'imported_rows' => count($records),
                'created_rows' => $createdRows,
                'updated_rows' => $updatedRows,
                'sheet_count' => count($import['sheet_names']),
                'sheet_names' => $import['sheet_names'],
                'batch_id' => $importBatch?->id,
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
            'purchase_year' => $dto->purchaseYear,
            'purchase_price' => $dto->purchasePrice,
        ]);

        $handler = AssetFactory::createHandler($asset->category);
        $validatedDetail = $handler->validatePayload($dto->detail);
        $handler->update($asset->id, $validatedDetail);
        $this->ensureQrCode($asset);

        return $asset->loadWithRelation();
    }

    public function deleteAsset(string $id)
    {
        $asset = Asset::find($id);
        if(empty($asset))
            throw new \Exception('Asset tidak ditemukan', 404);
        
        DB::transaction(function () use ($asset) {
            $disk = Storage::disk('public');

            if(filled($asset->qr_code_path) && $disk->exists($asset->qr_code_path))
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

        $query = Asset::query();

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
            $asset = $this->ensureQrCode($assets->first());

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
            $asset = $this->ensureQrCode($asset);
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
        $content = file_get_contents($tmpPath);
        @unlink($tmpPath);

        return new DownloadFileDTO(
            filename: 'qr-codes.zip',
            mimeType: 'application/zip',
            content: $content
        );
    }
}
